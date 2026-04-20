<?php

namespace App\Services\Fee;

use App\Models\Fee\FeeAuditLog;
use App\Models\Fee\FeeStructure;
use App\Models\Fee\FeeType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing fee types and fee structures.
 *
 * Fee structures are defined per grade per year (annual fees).
 */
class FeeStructureService
{
    /**
     * Create a new fee type.
     */
    public function createFeeType(array $data): FeeType
    {
        return DB::transaction(function () use ($data) {
            $feeType = FeeType::create($data);

            FeeAuditLog::log(
                $feeType,
                FeeAuditLog::ACTION_CREATE,
                null,
                $feeType->toArray(),
                'Fee type created'
            );

            return $feeType;
        });
    }

    /**
     * Update an existing fee type.
     */
    public function updateFeeType(FeeType $type, array $data): FeeType
    {
        return DB::transaction(function () use ($type, $data) {
            $oldValues = $type->toArray();

            $type->update($data);
            $type->refresh();

            FeeAuditLog::log(
                $type,
                FeeAuditLog::ACTION_UPDATE,
                $oldValues,
                $type->toArray(),
                'Fee type updated'
            );

            return $type;
        });
    }

    /**
     * Delete a fee type (soft delete).
     */
    public function deleteFeeType(FeeType $type): bool
    {
        return DB::transaction(function () use ($type) {
            $oldValues = $type->toArray();

            FeeAuditLog::log(
                $type,
                FeeAuditLog::ACTION_DELETE,
                $oldValues,
                null,
                'Fee type deleted'
            );

            return $type->delete();
        });
    }

    /**
     * Create a new fee structure.
     */
    public function createFeeStructure(array $data, User $user): FeeStructure
    {
        return DB::transaction(function () use ($data, $user) {
            $data['created_by'] = $user->id;

            $feeStructure = FeeStructure::create($data);

            FeeAuditLog::log(
                $feeStructure,
                FeeAuditLog::ACTION_CREATE,
                null,
                $feeStructure->toArray(),
                'Fee structure created'
            );

            return $feeStructure;
        });
    }

    /**
     * Update an existing fee structure.
     */
    public function updateFeeStructure(FeeStructure $structure, array $data): FeeStructure
    {
        return DB::transaction(function () use ($structure, $data) {
            $oldValues = $structure->toArray();

            $structure->update($data);
            $structure->refresh();

            FeeAuditLog::log(
                $structure,
                FeeAuditLog::ACTION_UPDATE,
                $oldValues,
                $structure->toArray(),
                'Fee structure updated'
            );

            return $structure;
        });
    }

    /**
     * Delete a fee structure (soft delete).
     */
    public function deleteFeeStructure(FeeStructure $structure): bool
    {
        return DB::transaction(function () use ($structure) {
            $oldValues = $structure->toArray();

            FeeAuditLog::log(
                $structure,
                FeeAuditLog::ACTION_DELETE,
                $oldValues,
                null,
                'Fee structure deleted'
            );

            return $structure->delete();
        });
    }

    /**
     * Copy fee structures from one year to another.
     *
     * @return int Number of structures copied
     */
    public function copyStructuresToYear(int $fromYear, int $toYear, User $user): int
    {
        return DB::transaction(function () use ($fromYear, $toYear, $user) {
            $sourceStructures = FeeStructure::forYear($fromYear)->get();

            $copiedCount = 0;

            foreach ($sourceStructures as $source) {
                // Check if structure already exists for target year
                $exists = FeeStructure::where('fee_type_id', $source->fee_type_id)
                    ->where('grade_id', $source->grade_id)
                    ->where('year', $toYear)
                    ->exists();

                if (!$exists) {
                    $newStructure = FeeStructure::create([
                        'fee_type_id' => $source->fee_type_id,
                        'grade_id' => $source->grade_id,
                        'year' => $toYear,
                        'amount' => $source->amount,
                        'created_by' => $user->id,
                    ]);

                    FeeAuditLog::log(
                        $newStructure,
                        FeeAuditLog::ACTION_CREATE,
                        null,
                        $newStructure->toArray(),
                        "Copied from year: {$fromYear}"
                    );

                    $copiedCount++;
                }
            }

            return $copiedCount;
        });
    }

    /**
     * Get all fee structures for a specific grade and year.
     */
    public function getFeeStructuresForGrade(int $gradeId, int $year): Collection
    {
        return FeeStructure::with(['feeType', 'grade'])
            ->forGrade($gradeId)
            ->forYear($year)
            ->active()
            ->get();
    }

    /**
     * Get total fees for a grade in a specific year.
     *
     * @return array{total: string, mandatory: string, optional: string, by_category: array}
     */
    public function getTotalFeesForGrade(int $gradeId, int $year): array
    {
        $structures = $this->getFeeStructuresForGrade($gradeId, $year);

        $mandatory = '0.00';
        $optional = '0.00';
        $byCategory = [
            FeeType::CATEGORY_TUITION => '0.00',
            FeeType::CATEGORY_LEVY => '0.00',
            FeeType::CATEGORY_OPTIONAL => '0.00',
        ];

        foreach ($structures as $structure) {
            $amount = $structure->amount;
            $category = $structure->feeType->category;

            // Update category totals
            $byCategory[$category] = bcadd($byCategory[$category], $amount, 2);

            // Update mandatory/optional totals
            if ($structure->feeType->is_optional) {
                $optional = bcadd($optional, $amount, 2);
            } else {
                $mandatory = bcadd($mandatory, $amount, 2);
            }
        }

        return [
            'total' => bcadd($mandatory, $optional, 2),
            'mandatory' => $mandatory,
            'optional' => $optional,
            'by_category' => $byCategory,
        ];
    }

    /**
     * Check if a year is historical (in the past and should be locked).
     */
    public function isHistoricalYear(int $year): bool
    {
        return $year < (int) date('Y');
    }

    /**
     * Get all years that have fee structures defined.
     *
     * @return array<int>
     */
    public function getAvailableYears(): array
    {
        return FeeStructure::query()
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    }
}
