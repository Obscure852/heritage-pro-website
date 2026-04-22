<?php

namespace App\Services\Crm\Imports;

use App\Exports\Crm\ArrayRowsExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CrmImportTemplateService
{
    public function __construct(
        private readonly CrmImportDefinitionRegistry $definitionRegistry
    ) {
    }

    public function download(string $entity): BinaryFileResponse
    {
        $definition = $this->definitionRegistry->entity($entity);

        return Excel::download(
            new ArrayRowsExport($definition['headings']),
            $definition['template_filename']
        );
    }
}
