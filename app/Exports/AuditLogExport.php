<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AuditLogExport implements FromQuery, WithHeadings, WithMapping {
    private Builder $queryBuilder;

    public function __construct(Builder $queryBuilder) {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Return the query for streaming export.
     */
    public function query(): Builder {
        return $this->queryBuilder;
    }

    /**
     * Define the column headings.
     */
    public function headings(): array {
        return [
            'Date',
            'User',
            'Action',
            'Document',
            'IP Address',
            'User Agent',
            'Details',
        ];
    }

    /**
     * Map each audit row to export columns.
     *
     * @param mixed $audit
     */
    public function map($audit): array {
        return [
            $audit->created_at->format('Y-m-d H:i:s'),
            $audit->user?->name ?? 'Anonymous',
            ucfirst(str_replace('_', ' ', $audit->action)),
            $audit->document?->title ?? 'Deleted Document',
            $audit->ip_address ?? '',
            $audit->user_agent ?? '',
            json_encode($audit->metadata ?? []),
        ];
    }
}
