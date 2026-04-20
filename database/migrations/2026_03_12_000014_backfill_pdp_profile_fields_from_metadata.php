<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('user_profile_metadata')) {
            return;
        }

        $metadataByUser = DB::table('user_profile_metadata')
            ->whereIn('key', ['payroll_no', 'grade'])
            ->orderBy('user_id')
            ->get()
            ->groupBy('user_id');

        foreach ($metadataByUser as $userId => $records) {
            $user = DB::table('users')->where('id', $userId)->first();

            if (!$user) {
                continue;
            }

            $updates = [];

            if (empty($user->personal_payroll_number)) {
                $payroll = $this->decodeMetadataValue($records->firstWhere('key', 'payroll_no')?->value);
                if ($payroll !== null && $payroll !== '') {
                    $updates['personal_payroll_number'] = $payroll;
                }
            }

            if (empty($user->earning_band)) {
                $earningBand = $this->decodeMetadataValue($records->firstWhere('key', 'grade')?->value);
                if ($earningBand !== null && $earningBand !== '') {
                    $updates['earning_band'] = $earningBand;
                }
            }

            if ($updates !== []) {
                $updates['updated_at'] = now();
                DB::table('users')->where('id', $userId)->update($updates);
            }
        }
    }

    public function down(): void
    {
        // Backfill is intentionally irreversible.
    }

    private function decodeMetadataValue(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }
};
