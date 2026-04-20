<?php

namespace App\Services\StaffAttendance;

use App\Models\StaffAttendance\BiometricIdMapping;
use App\Models\StaffAttendance\UnmappedBiometricId;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing biometric ID to user mappings.
 *
 * Provides auto-mapping logic, manual mapping creation, and
 * tracking of unmapped biometric IDs for admin attention.
 */
class StaffMappingService
{
    // ==================== MAPPING LOOKUP ====================

    /**
     * Find or create a mapping for an employee number.
     *
     * Checks for existing mapping first, then attempts auto-match
     * via user id_number. If no match found, tracks as unmapped.
     *
     * @param string $employeeNumber The employee number from the device
     * @param \DateTime|null $eventTimestamp Optional timestamp for unmapped tracking
     * @return int|null The user_id if mapped, null if no match
     */
    public function findOrCreateMapping(string $employeeNumber, ?\DateTime $eventTimestamp = null): ?int
    {
        // Check for existing mapping first
        $mapping = BiometricIdMapping::forEmployeeNumber($employeeNumber)->first();

        if ($mapping) {
            return $mapping->user_id;
        }

        // Try auto-match via user id_number
        $user = $this->findUserByEmployeeNumber($employeeNumber);

        if ($user) {
            // Create auto-mapping
            BiometricIdMapping::create([
                'employee_number' => $employeeNumber,
                'user_id' => $user->id,
                'source' => BiometricIdMapping::SOURCE_AUTO,
            ]);

            return $user->id;
        }

        // Track as unmapped
        $this->trackUnmapped($employeeNumber, $eventTimestamp ?? new \DateTime());

        return null;
    }

    /**
     * Find a user by their employee number.
     *
     * Step 1: Exact match on User.id_number
     * Step 2: Normalized match (trim whitespace, remove leading zeros)
     *
     * @param string $employeeNumber The employee number from the device
     * @return User|null The matched user or null if not found
     */
    public function findUserByEmployeeNumber(string $employeeNumber): ?User
    {
        // Step 1: Exact match on id_number
        $user = User::where('id_number', $employeeNumber)->first();

        if ($user) {
            return $user;
        }

        // Step 2: Normalized match (trim whitespace, remove leading zeros)
        $normalized = ltrim(trim($employeeNumber), '0');

        if (empty($normalized)) {
            return null;
        }

        // Try matching with normalized employee number
        return User::where('id_number', $normalized)
            ->orWhereRaw("LTRIM(TRIM(id_number)) = ?", [$normalized])
            ->orWhereRaw("LTRIM(TRIM(LEADING '0' FROM id_number)) = ?", [$normalized])
            ->first();
    }

    // ==================== UNMAPPED TRACKING ====================

    /**
     * Track an unmapped biometric ID.
     *
     * Uses updateOrCreate to maintain aggregated counts and timestamps.
     * Sets first_seen_at on create, updates last_seen_at always,
     * and increments event_count.
     *
     * @param string $employeeNumber The unmapped employee number
     * @param \DateTime $eventTimestamp When the event occurred
     * @return void
     */
    public function trackUnmapped(string $employeeNumber, \DateTime $eventTimestamp): void
    {
        $existing = UnmappedBiometricId::where('employee_number', $employeeNumber)->first();

        if ($existing) {
            // Update existing record
            $existing->last_seen_at = $eventTimestamp;
            $existing->event_count = DB::raw('event_count + 1');
            $existing->save();
        } else {
            // Create new record
            UnmappedBiometricId::create([
                'employee_number' => $employeeNumber,
                'first_seen_at' => $eventTimestamp,
                'last_seen_at' => $eventTimestamp,
                'event_count' => 1,
            ]);
        }
    }

    // ==================== MANUAL MAPPING ====================

    /**
     * Create a manual mapping between employee number and user.
     *
     * Uses database transaction to atomically create the mapping
     * and remove from unmapped tracking.
     *
     * @param string $employeeNumber The employee number from the device
     * @param int $userId The user ID to map to
     * @param int $createdBy The user ID who created this mapping
     * @return BiometricIdMapping The created mapping
     */
    public function createManualMapping(string $employeeNumber, int $userId, int $createdBy): BiometricIdMapping
    {
        return DB::transaction(function () use ($employeeNumber, $userId, $createdBy) {
            // Create or update the mapping
            $mapping = BiometricIdMapping::updateOrCreate(
                ['employee_number' => $employeeNumber],
                [
                    'user_id' => $userId,
                    'source' => BiometricIdMapping::SOURCE_MANUAL,
                    'created_by' => $createdBy,
                ]
            );

            // Remove from unmapped tracking
            UnmappedBiometricId::where('employee_number', $employeeNumber)->delete();

            return $mapping;
        });
    }

    /**
     * Delete a biometric ID mapping.
     *
     * @param BiometricIdMapping $mapping The mapping to delete
     * @return void
     */
    public function deleteMapping(BiometricIdMapping $mapping): void
    {
        $mapping->delete();
    }

    // ==================== QUERIES ====================

    /**
     * Get unmapped biometric IDs with recent events.
     *
     * Returns IDs that have had events within the specified number of days,
     * ordered by event count descending (highest activity first).
     *
     * @param int $days Number of days to look back (default 30)
     * @return Collection Collection of UnmappedBiometricId models
     */
    public function getUnmappedBiometricIds(int $days = 30): Collection
    {
        return UnmappedBiometricId::withRecentEvents($days)
            ->orderByEventCount()
            ->get();
    }

    /**
     * Get staff members who don't have a biometric ID mapping.
     *
     * Returns current staff (status = 'Current') who are not in
     * the biometric_id_mappings table, ordered by first name.
     *
     * @return Collection Collection of User models
     */
    public function getStaffWithoutMapping(): Collection
    {
        $mappedUserIds = BiometricIdMapping::pluck('user_id');

        return User::where('status', 'Current')
            ->whereNotIn('id', $mappedUserIds)
            ->orderBy('firstname')
            ->get();
    }

    /**
     * Get mapping statistics.
     *
     * Returns counts for total mapped, auto-mapped, manual-mapped,
     * unmapped IDs, and staff without mapping.
     *
     * @return array{total_mapped: int, auto_mapped: int, manual_mapped: int, unmapped: int, staff_without: int}
     */
    public function getMappingStats(): array
    {
        return [
            'total_mapped' => BiometricIdMapping::count(),
            'auto_mapped' => BiometricIdMapping::autoMapped()->count(),
            'manual_mapped' => BiometricIdMapping::manualMapped()->count(),
            'unmapped' => UnmappedBiometricId::count(),
            'staff_without' => $this->getStaffWithoutMapping()->count(),
        ];
    }
}
