<?php

namespace App\Services\Leave;

use App\Models\Leave\LeaveType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing leave types.
 *
 * Handles CRUD operations for leave types with transaction safety.
 */
class LeaveTypeService {
    /**
     * Get all leave types ordered by sort_order, then name.
     *
     * @return Collection
     */
    public function getAll(): Collection {
        return LeaveType::ordered()->get();
    }

    /**
     * Get only active leave types.
     *
     * @return Collection
     */
    public function getActive(): Collection {
        return LeaveType::active()->ordered()->get();
    }

    /**
     * Find a leave type by ID or fail.
     *
     * @param int $id
     * @return LeaveType
     * @throws ModelNotFoundException
     */
    public function findById(int $id): LeaveType {
        return LeaveType::findOrFail($id);
    }

    /**
     * Create a new leave type.
     *
     * @param array $data
     * @return LeaveType
     */
    public function create(array $data): LeaveType {
        return DB::transaction(function () use ($data) {
            // Set sort_order if not provided
            if (!isset($data['sort_order'])) {
                $data['sort_order'] = $this->getNextSortOrder();
            }

            return LeaveType::create($data);
        });
    }

    /**
     * Update an existing leave type.
     *
     * @param LeaveType $leaveType
     * @param array $data
     * @return LeaveType
     */
    public function update(LeaveType $leaveType, array $data): LeaveType {
        return DB::transaction(function () use ($leaveType, $data) {
            $leaveType->update($data);

            return $leaveType->fresh();
        });
    }

    /**
     * Toggle the active status of a leave type.
     *
     * @param LeaveType $leaveType
     * @return LeaveType
     */
    public function toggleStatus(LeaveType $leaveType): LeaveType {
        return DB::transaction(function () use ($leaveType) {
            $leaveType->update([
                'is_active' => !$leaveType->is_active,
            ]);

            return $leaveType->fresh();
        });
    }

    /**
     * Get the next sort order value for new leave types.
     *
     * @return int
     */
    public function getNextSortOrder(): int {
        $maxSortOrder = LeaveType::max('sort_order');

        return ($maxSortOrder ?? 0) + 1;
    }

    /**
     * Get counts for dashboard statistics.
     *
     * @return array
     */
    public function getCounts(): array {
        $total = LeaveType::count();
        $active = LeaveType::active()->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
        ];
    }
}
