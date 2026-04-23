<?php

namespace App\Services\Crm\Imports;

use App\Models\CrmImportRun;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CrmImportPreviewService
{
    public function __construct(
        private readonly CrmImportDefinitionRegistry $definitionRegistry,
        private readonly CrmImportProcessorResolver $processorResolver
    ) {
    }

    public function createPreview(string $entity, UploadedFile $file, User $initiator): CrmImportRun
    {
        $definition = $this->definitionRegistry->entity($entity);
        $checksum = hash_file('sha256', $file->getRealPath());
        $storedPath = $file->storeAs(
            'crm/imports/' . $entity . '/preview',
            Str::uuid() . '.' . $file->getClientOriginalExtension(),
            'documents'
        );

        try {
            return DB::transaction(function () use ($entity, $definition, $checksum, $storedPath, $file, $initiator) {
                $run = CrmImportRun::query()->create([
                    'entity' => $entity,
                    'status' => 'draft',
                    'initiated_by_id' => $initiator->id,
                    'disk' => 'documents',
                    'path' => $storedPath,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_checksum' => $checksum,
                ]);

                [$headers, $rows] = $this->readSpreadsheet(Storage::disk($run->disk)->path($run->path));
                $this->assertValidHeaders($headers, $definition['headings']);

                $processor = $this->processorResolver->for($entity);
                $rowPayloads = [];
                $summary = [
                    'create' => 0,
                    'update' => 0,
                    'skip' => 0,
                    'error' => 0,
                ];

                foreach ($rows as $rowNumber => $row) {
                    $mapped = $this->mapRow($headers, $row);

                    if ($this->rowIsBlank($mapped)) {
                        $rowPayloads[] = [
                            'row_number' => $rowNumber,
                            'normalized_key' => null,
                            'action' => 'skip',
                            'payload' => $mapped,
                            'validation_errors' => ['Blank row skipped.'],
                        ];
                        $summary['skip']++;
                        continue;
                    }

                    $preview = $processor->previewRow($mapped, $initiator);
                    $rowPayloads[] = [
                        'row_number' => $rowNumber,
                        'normalized_key' => $preview['normalized_key'],
                        'action' => $preview['action'],
                        'payload' => $preview['payload'],
                        'validation_errors' => $preview['validation_errors'] === [] ? null : array_values($preview['validation_errors']),
                    ];
                    $summary[$preview['action']] = ($summary[$preview['action']] ?? 0) + 1;
                }

                if ($rowPayloads !== []) {
                    $run->rows()->createMany($rowPayloads);
                }

                $run->update([
                    'status' => 'validated',
                    'preview_summary' => $summary,
                    'total_count' => count($rowPayloads),
                    'created_count' => $summary['create'],
                    'updated_count' => $summary['update'],
                    'skipped_count' => $summary['skip'],
                    'failed_count' => $summary['error'],
                ]);

                return $run->fresh(['rows']);
            });
        } catch (\Throwable $exception) {
            Storage::disk('documents')->delete($storedPath);

            throw $exception;
        }
    }

    private function readSpreadsheet(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray('', false, false, false);

        if ($rows === []) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded spreadsheet is empty.',
            ]);
        }

        $headerRow = $this->trimTrailingEmptyCells(array_shift($rows));
        $headers = array_map(fn ($header) => Str::lower(trim((string) $header)), $headerRow);
        $indexedRows = [];
        $headerCount = count($headers);
        $rows = $this->trimTrailingEmptyRows($rows, $headerCount);

        foreach ($rows as $index => $row) {
            $indexedRows[$index + 2] = array_slice($row, 0, $headerCount);
        }

        return [$headers, $indexedRows];
    }

    private function trimTrailingEmptyCells(array $row): array
    {
        while ($row !== [] && trim((string) end($row)) === '') {
            array_pop($row);
        }

        return $row;
    }

    private function trimTrailingEmptyRows(array $rows, int $columnCount): array
    {
        while ($rows !== []) {
            $row = end($rows);

            if ($this->rowHasContent(is_array($row) ? array_slice($row, 0, $columnCount) : [])) {
                break;
            }

            array_pop($rows);
        }

        return $rows;
    }

    private function rowHasContent(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }

    private function assertValidHeaders(array $headers, array $expectedHeadings): void
    {
        $normalizedExpected = array_map(fn ($header) => Str::lower(trim((string) $header)), $expectedHeadings);
        $missing = array_values(array_diff($normalizedExpected, $headers));
        $unexpected = array_values(array_diff($headers, $normalizedExpected));

        if ($missing !== [] || $unexpected !== []) {
            $messages = [];

            if ($missing !== []) {
                $messages[] = 'Missing required columns: ' . implode(', ', $missing) . '.';
            }

            if ($unexpected !== []) {
                $messages[] = 'Unexpected columns: ' . implode(', ', $unexpected) . '.';
            }

            throw ValidationException::withMessages([
                'file' => implode(' ', $messages),
            ]);
        }
    }

    private function mapRow(array $headers, array $row): array
    {
        $mapped = [];

        foreach ($headers as $index => $header) {
            $mapped[$header] = $row[$index] ?? null;
        }

        return $mapped;
    }

    private function rowIsBlank(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
