<?php

namespace App\Services;

use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Support\Collection;

class RecipientService
{
    /**
     * Get email recipients based on filters
     *
     * @param array $filters
     * @return Collection
     */
    public function getEmailRecipients(array $filters): Collection
    {
        if ($filters['recipient_type'] === 'sponsor') {
            return $this->getSponsorsByEmail($filters);
        } elseif ($filters['recipient_type'] === 'user') {
            return $this->getUsersByEmail($filters);
        }

        return collect();
    }

    /**
     * Get SMS recipients based on filters
     *
     * @param array $filters
     * @return Collection
     */
    public function getSmsRecipients(array $filters): Collection
    {
        if ($filters['recipient_type'] === 'sponsors') {
            return $this->getSponsorsByPhone($filters);
        } else {
            return $this->getUsersByPhone($filters);
        }
    }

    /**
     * Get sponsors with valid email addresses
     *
     * @param array $filters
     * @return Collection
     */
    protected function getSponsorsByEmail(array $filters): Collection
    {
        $query = Sponsor::query()->whereNotNull('email');

        // Filter by grade
        if (!empty($filters['grade'])) {
            $query->whereHas('students.currentClassRelation', function ($q) use ($filters) {
                $q->where('klasses.grade_id', $filters['grade']);
            });
        }

        // Filter by sponsor filter ID
        if (!empty($filters['sponsorFilter'])) {
            $query->where('sponsor_filter_id', $filters['sponsorFilter']);
        }

        return $query->get();
    }

    /**
     * Get sponsors with valid phone numbers
     *
     * @param array $filters
     * @return Collection
     */
    protected function getSponsorsByPhone(array $filters): Collection
    {
        $query = Sponsor::query()->whereNotNull('phone');

        // Filter by grade
        if (!empty($filters['grade'])) {
            $query->whereHas('students.currentClassRelation', function ($q) use ($filters) {
                $q->where('klasses.grade_id', $filters['grade']);
            });
        }

        // Filter by sponsor filter ID
        if (!empty($filters['sponsorFilter'])) {
            $query->where('sponsor_filter_id', $filters['sponsorFilter']);
        }

        return $query->get();
    }

    /**
     * Get users with valid email addresses
     *
     * @param array $filters
     * @return Collection
     */
    protected function getUsersByEmail(array $filters): Collection
    {
        $query = User::query()->whereNotNull('email');

        // Apply filters
        $this->applyUserFilters($query, $filters);

        return $query->get();
    }

    /**
     * Get users with valid phone numbers
     *
     * @param array $filters
     * @return Collection
     */
    protected function getUsersByPhone(array $filters): Collection
    {
        $query = User::query()->whereNotNull('phone');

        // Apply filters
        $this->applyUserFilters($query, $filters);

        return $query->get();
    }

    /**
     * Apply user filters to query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return void
     */
    protected function applyUserFilters($query, array $filters): void
    {
        if (!empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        if (!empty($filters['area_of_work'])) {
            $query->where('area_of_work', $filters['area_of_work']);
        }

        if (!empty($filters['position'])) {
            $query->where('position', $filters['position']);
        }

        if (!empty($filters['filter'])) {
            $query->where('user_filter_id', $filters['filter']);
        }
    }

    /**
     * Count email recipients
     *
     * @param array $filters
     * @return int
     */
    public function countEmailRecipients(array $filters): int
    {
        if ($filters['recipient_type'] === 'sponsor') {
            return $this->countSponsors($filters, 'email');
        } elseif ($filters['recipient_type'] === 'user') {
            return $this->countUsers($filters, 'email');
        }

        return 0;
    }

    /**
     * Count SMS recipients
     *
     * @param array $filters
     * @return int
     */
    public function countSmsRecipients(array $filters): int
    {
        if ($filters['recipient_type'] === 'sponsors') {
            return $this->countSponsors($filters, 'phone');
        } else {
            return $this->countUsers($filters, 'phone');
        }
    }

    /**
     * Count sponsors with valid contact info
     *
     * @param array $filters
     * @param string $contactField 'email' or 'phone'
     * @return int
     */
    protected function countSponsors(array $filters, string $contactField): int
    {
        $query = Sponsor::query()->whereNotNull($contactField);

        // Filter by grade
        if (!empty($filters['grade'])) {
            $query->whereHas('students.currentClassRelation', function ($q) use ($filters) {
                $q->where('klasses.grade_id', $filters['grade']);
            });
        }

        // Filter by sponsor filter ID
        if (!empty($filters['sponsorFilter'])) {
            $query->where('sponsor_filter_id', $filters['sponsorFilter']);
        }

        return $query->count();
    }

    /**
     * Count users with valid contact info
     *
     * @param array $filters
     * @param string $contactField 'email' or 'phone'
     * @return int
     */
    protected function countUsers(array $filters, string $contactField): int
    {
        $query = User::query()->whereNotNull($contactField);

        // Apply filters
        $this->applyUserFilters($query, $filters);

        return $query->count();
    }

    /**
     * Get recipients using cursor for memory efficiency (large datasets)
     *
     * @param array $filters
     * @param string $type 'email' or 'sms'
     * @return \Illuminate\Support\LazyCollection
     */
    public function getRecipientsLazy(array $filters, string $type = 'email')
    {
        if ($type === 'email') {
            if ($filters['recipient_type'] === 'sponsor') {
                $query = Sponsor::query()->whereNotNull('email');
            } else {
                $query = User::query()->whereNotNull('email');
                $this->applyUserFilters($query, $filters);
            }
        } else {
            if ($filters['recipient_type'] === 'sponsors') {
                $query = Sponsor::query()->whereNotNull('phone');
            } else {
                $query = User::query()->whereNotNull('phone');
                $this->applyUserFilters($query, $filters);
            }
        }

        return $query->cursor();
    }

    /**
     * Get SMS recipients formatted for bulk sending
     *
     * @param array $filters
     * @return array Array of recipients with phone, id, senderType, type
     */
    public function getSmsRecipientsFormatted(array $filters): array
    {
        $recipientType = $filters['recipient_type'] ?? '';

        if ($recipientType === 'sponsors') {
            $sponsors = $this->getSponsorsByPhone($filters);
            return $sponsors->map(function ($sponsor) {
                return [
                    'phone' => $sponsor->phone,
                    'id' => $sponsor->id,
                    'senderType' => 'sponsor',
                    'type' => 'bulk',
                ];
            })->toArray();
        } else {
            $users = $this->getUsersByPhone($filters);
            return $users->map(function ($user) {
                return [
                    'phone' => $user->phone,
                    'id' => $user->id,
                    'senderType' => 'user',
                    'type' => 'bulk',
                ];
            })->toArray();
        }
    }

    /**
     * Get notification recipients (staff users) based on filters
     * Used for internal staff notifications
     *
     * @param array $filters
     * @return Collection
     */
    public function getNotificationRecipients(array $filters): Collection
    {
        $isGeneral = $filters['is_general'] ?? false;

        if ($isGeneral) {
            // Return all users for general notifications
            return User::all();
        }

        $query = User::query();

        // Department and area of work filtering
        $departmentId = $filters['department_id'] ?? null;
        $areaOfWork = $filters['area_of_work'] ?? null;

        if ($departmentId && $areaOfWork) {
            $query->where(function ($q) use ($departmentId, $areaOfWork) {
                $department = \App\Models\Department::findOrFail($departmentId);
                $q->where('department', $department->name)
                    ->orWhere('area_of_work', $areaOfWork);
            });
        } elseif ($departmentId) {
            $department = \App\Models\Department::findOrFail($departmentId);
            $query->where('department', $department->name);
        } elseif ($areaOfWork) {
            $query->where('area_of_work', $areaOfWork);
        }

        return $query->get();
    }

    /**
     * Validate that recipients exist for given filters
     *
     * @param array $filters
     * @param string $channel 'email' or 'sms'
     * @return bool
     * @throws \Exception
     */
    public function validateRecipients(array $filters, string $channel = 'email'): bool
    {
        if ($channel === 'email') {
            $count = $this->countEmailRecipients($filters);
        } else {
            $count = $this->countSmsRecipients($filters);
        }

        if ($count === 0) {
            throw new \Exception('No recipients found for the selected criteria.');
        }

        return true;
    }
}
