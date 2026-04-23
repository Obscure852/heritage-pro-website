<?php

namespace App\Services\Crm;

use App\Models\CrmUserPresence;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class CrmPresenceService
{
    public function __construct(
        private readonly DiscussionDeliveryService $discussionDeliveryService
    ) {
    }

    public function heartbeat(User $user, ?string $path = null): void
    {
        if (! $user->canAccessCrm()) {
            return;
        }

        CrmUserPresence::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'last_seen_at' => now(),
                'last_path' => $path,
            ]
        );
    }

    public function launcherPayload(User $currentUser, ?string $search = null): array
    {
        $search = trim((string) $search);
        $limit = (int) config('heritage_crm.presence.launcher_limit', 8);

        $users = $this->onlineUserQuery($currentUser, $search)
            ->limit($limit)
            ->get()
            ->map(fn (User $user) => $this->mapUser($user))
            ->values()
            ->all();

        return [
            'online_count' => $this->onlineUserQuery($currentUser)->count(),
            'poll_seconds' => (int) config('heritage_crm.presence.launcher_poll_seconds', 45),
            'users' => $users,
        ];
    }

    public function unreadPayload(User $currentUser): array
    {
        return $this->discussionDeliveryService->unreadPayload($currentUser) + [
            'discussion_sound_enabled' => (bool) $currentUser->crm_discussion_sound_enabled,
        ];
    }

    public function updateDiscussionSoundPreference(User $currentUser, bool $enabled): array
    {
        $currentUser->forceFill([
            'crm_discussion_sound_enabled' => $enabled,
        ])->save();

        return [
            'ok' => true,
            'discussion_sound_enabled' => (bool) $currentUser->crm_discussion_sound_enabled,
        ];
    }

    private function onlineUserQuery(User $currentUser, ?string $search = null): Builder
    {
        $threshold = now()->subMinutes((int) config('heritage_crm.presence.online_window_minutes', 3));
        $userColumns = ['users.id', 'users.email', 'users.role'];

        foreach (['name', 'firstname', 'lastname', 'username'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                $userColumns[] = 'users.' . $column;
            }
        }

        return User::query()
            ->select([
                ...$userColumns,
                'crm_user_presence.last_seen_at as crm_presence_last_seen_at',
                'crm_user_presence.last_path as crm_presence_last_path',
            ])
            ->join('crm_user_presence', 'crm_user_presence.user_id', '=', 'users.id')
            ->where('users.active', true)
            ->whereIn('users.role', array_keys(config('heritage_crm.roles', [])))
            ->where('users.id', '!=', $currentUser->id)
            ->where('crm_user_presence.last_seen_at', '>=', $threshold)
            ->when($search !== null && $search !== '', function (Builder $builder) use ($search) {
                $columns = [];

                if (Schema::hasColumn('users', 'name')) {
                    $columns[] = 'users.name';
                }

                if (Schema::hasColumn('users', 'firstname')) {
                    $columns[] = 'users.firstname';
                }

                if (Schema::hasColumn('users', 'lastname')) {
                    $columns[] = 'users.lastname';
                }

                if (Schema::hasColumn('users', 'username')) {
                    $columns[] = 'users.username';
                }

                $columns[] = 'users.email';

                $builder->where(function (Builder $searchQuery) use ($search, $columns) {
                    foreach ($columns as $index => $column) {
                        $method = $index === 0 ? 'where' : 'orWhere';
                        $searchQuery->{$method}($column, 'like', "%{$search}%");
                    }
                });
            })
            ->orderByDesc('crm_user_presence.last_seen_at');
    }

    private function mapUser(User $user): array
    {
        $lastSeenAt = $user->crm_presence_last_seen_at instanceof CarbonInterface
            ? $user->crm_presence_last_seen_at
            : Carbon::parse($user->crm_presence_last_seen_at);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'role' => config('heritage_crm.roles.' . $user->role, ucfirst($user->role)),
            'initials' => $this->initials($user->name),
            'last_seen_at' => $lastSeenAt->toIso8601String(),
            'last_seen_label' => $lastSeenAt->diffForHumans(),
            'discussion_url' => route('crm.discussions.app.direct.start', [
                'recipient_user_id' => $user->id,
            ]),
        ];
    }

    private function initials(string $name): string
    {
        $segments = preg_split('/\s+/', trim($name)) ?: [];
        $initials = collect($segments)
            ->filter()
            ->take(2)
            ->map(fn (string $segment) => strtoupper(substr($segment, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'CU';
    }
}
