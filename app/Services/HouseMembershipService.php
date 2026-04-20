<?php

namespace App\Services;

use App\Helpers\CacheHelper;
use App\Models\House;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HouseMembershipService
{
    public function allocateUsers(House $house, array $userIds, int $termId): array
    {
        $this->assertHouseBelongsToSelectedTerm($house, $termId);

        $eligibleUserIds = User::query()
            ->where('status', 'Current')
            ->whereIn('id', $userIds)
            ->pluck('id')
            ->all();

        if ($eligibleUserIds === []) {
            throw ValidationException::withMessages([
                'users' => 'No eligible current users were selected for house allocation.',
            ]);
        }

        $alreadyAllocated = DB::table('user_house')
            ->where('term_id', $termId)
            ->whereIn('user_id', $eligibleUserIds)
            ->pluck('user_id')
            ->all();

        $newUserIds = array_values(array_diff($eligibleUserIds, $alreadyAllocated));

        if ($newUserIds === []) {
            throw ValidationException::withMessages([
                'users' => 'All selected users are already allocated to a house for this term.',
            ]);
        }

        DB::transaction(function () use ($house, $newUserIds, $termId): void {
            $now = now();
            $payload = [];

            foreach ($newUserIds as $userId) {
                $payload[$userId] = [
                    'term_id' => $termId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $house->users()->attach($payload);
            CacheHelper::forgetUnallocatedHouseUsers($termId);
        });

        return [
            'allocated_count' => count($newUserIds),
            'skipped_count' => count($alreadyAllocated),
        ];
    }

    public function removeUsers(House $house, array $userIds): int
    {
        $deletedCount = DB::table('user_house')
            ->where('house_id', $house->id)
            ->whereIn('user_id', $userIds)
            ->delete();

        CacheHelper::forgetUnallocatedHouseUsers($house->term_id);

        return $deletedCount;
    }

    public function removeUser(House $house, int $userId): void
    {
        $exists = DB::table('user_house')
            ->where('house_id', $house->id)
            ->where('user_id', $userId)
            ->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'user' => 'User is not part of this house.',
            ]);
        }

        $this->removeUsers($house, [$userId]);
    }

    private function assertHouseBelongsToSelectedTerm(House $house, int $termId): void
    {
        if ((int) $house->term_id !== $termId) {
            throw ValidationException::withMessages([
                'house' => 'Cannot allocate users to a house from a different term.',
            ]);
        }
    }
}
