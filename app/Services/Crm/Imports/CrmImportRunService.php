<?php

namespace App\Services\Crm\Imports;

use App\Models\CrmImportEntityLock;
use App\Models\CrmImportRun;
use App\Models\CrmImportRunRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CrmImportRunService
{
    public function __construct(
        private readonly CrmImportProcessorResolver $processorResolver
    ) {
    }

    public function queue(CrmImportRun $run): array
    {
        return DB::transaction(function () use ($run) {
            $run = CrmImportRun::query()->lockForUpdate()->findOrFail($run->id);
            CrmImportEntityLock::query()->whereKey($run->entity)->lockForUpdate()->firstOrFail();

            if (in_array($run->status, ['queued', 'processing', 'completed', 'completed_with_errors', 'failed', 'cancelled'], true)) {
                return ['run' => $run, 'queued' => false];
            }

            if ($run->status !== 'validated') {
                throw new RuntimeException('Only validated import previews can be queued.');
            }

            if ($run->rows()->whereIn('action', ['create', 'update'])->count() === 0) {
                throw new RuntimeException('This preview has no valid rows to queue.');
            }

            $activeRun = CrmImportRun::query()
                ->where('entity', $run->entity)
                ->whereKeyNot($run->id)
                ->whereIn('status', ['queued', 'processing'])
                ->lockForUpdate()
                ->first();

            if ($activeRun) {
                throw new RuntimeException('An import for this entity is already queued or processing.');
            }

            $run->update([
                'status' => 'queued',
            ]);

            return ['run' => $run->fresh(), 'queued' => true];
        });
    }

    public function process(CrmImportRun $run): array
    {
        ['run' => $run, 'should_process' => $shouldProcess] = DB::transaction(function () use ($run) {
            $run = CrmImportRun::query()->lockForUpdate()->findOrFail($run->id);
            CrmImportEntityLock::query()->whereKey($run->entity)->lockForUpdate()->firstOrFail();

            if ($run->status === 'processing') {
                return [
                    'run' => $run->fresh(),
                    'should_process' => false,
                ];
            }

            if (in_array($run->status, ['completed', 'completed_with_errors', 'failed', 'cancelled'], true)) {
                return [
                    'run' => $run->fresh(),
                    'should_process' => false,
                ];
            }

            if (! in_array($run->status, ['validated', 'queued'], true)) {
                throw new RuntimeException('Only validated import previews can be imported.');
            }

            if ($run->rows()->whereIn('action', ['create', 'update'])->count() === 0) {
                throw new RuntimeException('This preview has no valid rows to import.');
            }

            $otherActiveRun = CrmImportRun::query()
                ->where('entity', $run->entity)
                ->whereKeyNot($run->id)
                ->where('status', 'processing')
                ->lockForUpdate()
                ->first();

            if ($otherActiveRun) {
                throw new RuntimeException('Another import run for this entity is already processing.');
            }

            $run->update([
                'status' => 'processing',
                'started_at' => $run->started_at ?: now(),
                'last_error' => null,
            ]);

            return [
                'run' => $run->fresh(),
                'should_process' => true,
            ];
        });

        if (! $shouldProcess) {
            return [
                'run' => $run,
                'processed' => false,
            ];
        }

        $processor = $this->processorResolver->for($run->entity);
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $failed = 0;
        $passwordResults = [];

        foreach ($run->rows()->orderBy('row_number')->get() as $row) {
            if ($row->action === 'skip') {
                $skipped++;
                continue;
            }

            if (! empty($row->validation_errors)) {
                $failed++;
                $row->forceFill([
                    'action' => 'error',
                    'processed_at' => now(),
                ])->save();
                continue;
            }

            try {
                $result = DB::transaction(function () use ($processor, $run, $row) {
                    return $processor->processRow($run, $row);
                });

                $row->forceFill([
                    'action' => $result['action'],
                    'record_id' => $result['record_id'] ?? null,
                    'processed_at' => now(),
                    'validation_errors' => null,
                ])->save();

                if ($result['action'] === 'create') {
                    $created++;
                } elseif ($result['action'] === 'update') {
                    $updated++;
                } else {
                    $skipped++;
                }

                if (isset($result['password_result'])) {
                    $passwordResults[] = $result['password_result'];
                }
            } catch (\Throwable $exception) {
                $failed++;
                $row->forceFill([
                    'action' => 'error',
                    'validation_errors' => [$exception->getMessage()],
                    'processed_at' => now(),
                ])->save();
            }
        }

        $run->update([
            'status' => $failed > 0 ? 'completed_with_errors' : 'completed',
            'created_count' => $created,
            'updated_count' => $updated,
            'skipped_count' => $skipped,
            'failed_count' => $failed,
            'passwords_payload' => $passwordResults !== [] ? Crypt::encryptString(json_encode($passwordResults, JSON_THROW_ON_ERROR)) : null,
            'completed_at' => now(),
        ]);

        return [
            'run' => $run->fresh(),
            'processed' => true,
        ];
    }

    public function fail(CrmImportRun $run, \Throwable $exception): void
    {
        $run->update([
            'status' => 'failed',
            'last_error' => $exception->getMessage(),
            'completed_at' => now(),
        ]);
    }

    public function passwordRows(CrmImportRun $run): Collection
    {
        if (! $run->hasPasswordResults()) {
            return collect();
        }

        return collect(json_decode(Crypt::decryptString($run->passwords_payload), true, 512, JSON_THROW_ON_ERROR));
    }

    public function consumePasswords(CrmImportRun $run): Collection
    {
        return DB::transaction(function () use ($run) {
            $run = CrmImportRun::query()->lockForUpdate()->findOrFail($run->id);

            if (! $run->hasPasswordResults()) {
                return collect();
            }

            $rows = $this->passwordRows($run);

            $run->update([
                'passwords_downloaded_at' => now(),
                'passwords_payload' => null,
            ]);

            return $rows;
        });
    }

    public function failureRows(CrmImportRun $run): Collection
    {
        return $run->rows()
            ->where(function ($query) {
                $query->where('action', 'error')
                    ->orWhereNotNull('validation_errors');
            })
            ->orderBy('row_number')
            ->get();
    }

    public function deleteStoredFile(CrmImportRun $run): void
    {
        Storage::disk($run->disk)->delete($run->path);
    }
}
