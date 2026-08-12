<?php

namespace App\Services\ClientSetup\Contracts;

use App\Models\CrmClientSetupMigrationUpload;

interface ClientSetupMigrationImporter
{
    /**
     * Import one approved upload into the configured college system.
     *
     * Implementations must use the upload UUID as an idempotency key and
     * return counts/references only, never raw student or staff records.
     */
    public function import(CrmClientSetupMigrationUpload $upload): array;
}
