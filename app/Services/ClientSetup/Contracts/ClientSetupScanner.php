<?php

namespace App\Services\ClientSetup\Contracts;

use App\Models\CrmClientSetupAttachment;

interface ClientSetupScanner
{
    /**
     * Return a terminal scan result for the supplied private attachment.
     *
     * The status must be approved, rejected or failed. The adapter must not
     * return the file contents or a raw storage path in the result.
     */
    public function scan(CrmClientSetupAttachment $attachment): array;
}
