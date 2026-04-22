<?php

namespace App\Services\Crm\Imports;

use App\Services\Crm\Imports\Contracts\CrmImportEntityProcessor;
use App\Services\Crm\Imports\Processors\ContactImportProcessor;
use App\Services\Crm\Imports\Processors\LeadImportProcessor;
use App\Services\Crm\Imports\Processors\UserImportProcessor;
use InvalidArgumentException;

class CrmImportProcessorResolver
{
    public function __construct(
        private readonly UserImportProcessor $userImportProcessor,
        private readonly LeadImportProcessor $leadImportProcessor,
        private readonly ContactImportProcessor $contactImportProcessor
    ) {
    }

    public function for(string $entity): CrmImportEntityProcessor
    {
        return match ($entity) {
            'users' => $this->userImportProcessor,
            'leads' => $this->leadImportProcessor,
            'contacts' => $this->contactImportProcessor,
            default => throw new InvalidArgumentException('Unknown CRM import entity [' . $entity . '].'),
        };
    }
}
