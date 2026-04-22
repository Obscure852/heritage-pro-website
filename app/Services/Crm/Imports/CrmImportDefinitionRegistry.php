<?php

namespace App\Services\Crm\Imports;

use InvalidArgumentException;

class CrmImportDefinitionRegistry
{
    public function entities(): array
    {
        return config('heritage_crm.imports.entities', []);
    }

    public function entity(string $entity): array
    {
        $definition = $this->entities()[$entity] ?? null;

        if ($definition === null) {
            throw new InvalidArgumentException('Unknown CRM import entity [' . $entity . '].');
        }

        return $definition;
    }

    public function entityKeys(): array
    {
        return array_keys($this->entities());
    }
}
