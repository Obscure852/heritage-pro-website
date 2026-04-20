<?php

use App\Helpers\TermHelper;
use App\Models\SchoolSetup;
use App\Services\SchoolModeProvisioner;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

return new class extends Migration {
    public function up(): void
    {
        $mode = SchoolSetup::normalizeType(SchoolSetup::value('type'));

        if (!in_array($mode, [SchoolSetup::TYPE_PRE_F3, SchoolSetup::TYPE_JUNIOR_SENIOR, SchoolSetup::TYPE_K12], true)) {
            return;
        }

        $term = TermHelper::getCurrentTerm();

        if (!$term) {
            Log::warning('Skipping combined mode academic structure backfill because no current term was found.', [
                'mode' => $mode,
            ]);

            return;
        }

        app(SchoolModeProvisioner::class)->provisionMode($mode, $term);
    }

    public function down(): void
    {
        // Data backfill only. Do not delete academic structure on rollback.
    }
};
