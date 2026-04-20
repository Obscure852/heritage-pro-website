<?php

namespace App\Services\Messaging;

use App\Models\StaffUserPresence;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class StaffPresenceService
{
    public function __construct(
        protected StaffMessagingFeatureService $featureService
    ) {
    }

    public function heartbeat(User $user, string $sessionId, ?string $path = null): StaffUserPresence
    {
        return StaffUserPresence::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => $user->id,
                'last_seen_at' => now(),
                'last_path' => $path ? mb_substr($path, 0, 255) : null,
            ]
        );
    }

    public function getOnlineUsersFor(User $currentUser, ?string $search = null, int $limit = 8): Collection
    {
        $threshold = now()->subMinutes($this->featureService->onlineWindowMinutes());

        $latestPresence = StaffUserPresence::query()
            ->selectRaw('user_id, MAX(last_seen_at) as last_seen_at')
            ->where('last_seen_at', '>=', $threshold)
            ->groupBy('user_id');

        $query = User::query()
            ->select([
                'users.id',
                'users.firstname',
                'users.lastname',
                'users.avatar',
                'users.position',
                'users.department',
                'presence_latest.last_seen_at as presence_last_seen_at',
            ])
            ->joinSub($latestPresence, 'presence_latest', function ($join) {
                $join->on('presence_latest.user_id', '=', 'users.id');
            })
            ->where('users.id', '!=', $currentUser->id)
            ->where('users.active', true)
            ->whereNull('users.deleted_at')
            ->orderByDesc('presence_latest.last_seen_at')
            ->orderBy('users.firstname')
            ->orderBy('users.lastname');

        if ($search !== null && $search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->whereRaw("CONCAT(COALESCE(users.firstname, ''), ' ', COALESCE(users.lastname, '')) LIKE ?", ["%{$search}%"])
                    ->orWhere('users.position', 'like', "%{$search}%")
                    ->orWhere('users.department', 'like', "%{$search}%");
            });
        }

        return $query
            ->limit($limit)
            ->get()
            ->each(function (User $user) {
                $user->presence_last_seen_at = Carbon::parse($user->presence_last_seen_at);
            });
    }

    public function getOnlineUsersCountFor(User $currentUser): int
    {
        $threshold = now()->subMinutes($this->featureService->onlineWindowMinutes());

        return StaffUserPresence::query()
            ->join('users', 'users.id', '=', 'staff_user_presence.user_id')
            ->where('staff_user_presence.last_seen_at', '>=', $threshold)
            ->where('staff_user_presence.user_id', '!=', $currentUser->id)
            ->where('users.active', true)
            ->whereNull('users.deleted_at')
            ->distinct('staff_user_presence.user_id')
            ->count('staff_user_presence.user_id');
    }

    public function isUserOnline(User $user): bool
    {
        return StaffUserPresence::query()
            ->join('users', 'users.id', '=', 'staff_user_presence.user_id')
            ->where('staff_user_presence.user_id', $user->id)
            ->where('staff_user_presence.last_seen_at', '>=', now()->subMinutes($this->featureService->onlineWindowMinutes()))
            ->where('users.active', true)
            ->whereNull('users.deleted_at')
            ->exists();
    }

    public function presenceStateFor(User $user): array
    {
        $lastSeenAt = StaffUserPresence::query()
            ->where('user_id', $user->id)
            ->max('last_seen_at');

        $lastSeenAt = $lastSeenAt ? Carbon::parse($lastSeenAt) : null;
        $isOnline = !$user->trashed()
            && (bool) $user->active
            && $lastSeenAt !== null
            && $lastSeenAt->greaterThanOrEqualTo(now()->subMinutes($this->featureService->onlineWindowMinutes()));

        return [
            'is_online' => $isOnline,
            'last_seen_at' => $lastSeenAt,
            'last_seen_label' => $isOnline
                ? 'Active now'
                : ($lastSeenAt ? 'Last seen ' . $lastSeenAt->diffForHumans() : 'Offline'),
        ];
    }
}
