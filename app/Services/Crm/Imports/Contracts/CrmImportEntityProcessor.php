<?php

namespace App\Services\Crm\Imports\Contracts;

use App\Models\CrmImportRun;
use App\Models\CrmImportRunRow;
use App\Models\User;

interface CrmImportEntityProcessor
{
    public function entity(): string;

    public function previewRow(array $row, User $initiator): array;

    public function processRow(CrmImportRun $run, CrmImportRunRow $row): array;
}
