<?php

namespace App\Services\ClientSetup;

use App\Models\CrmClientSetupInvitation;
use App\Models\CrmClientSetupMigrationUpload;
use App\Models\CrmClientSetupSubmission;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;
use RuntimeException;
use Throwable;

class ClientSetupMigrationService
{
    public function __construct(
        private readonly ClientSetupAttachmentService $attachmentService,
        private readonly ClientSetupAuditService $auditService,
        private readonly ClientSetupNotificationService $notificationService,
        private readonly ClientSetupTemplateCompatibilityService $compatibilityService
    ) {
    }

    public function template(string $kind): array
    {
        $template = config("client_setup.migration_templates.{$kind}");

        if (! is_array($template)) {
            throw new RuntimeException('Unknown migration template.');
        }

        return $template;
    }

    public function templatePath(string $kind): string
    {
        $path = resource_path($this->template($kind)['path']);

        if (! is_file($path)) {
            throw new RuntimeException('The requested migration template is not available.');
        }

        return $path;
    }

    public function validateAndStore(
        CrmClientSetupSubmission $submission,
        CrmClientSetupInvitation $invitation,
        string $kind,
        UploadedFile $file
    ): CrmClientSetupMigrationUpload {
        $definition = $this->template($kind);
        [$headers, $rows, $headerErrors, $compatibility] = $this->readRows($file, $kind);
        $errors = $headerErrors;
        $rowCount = 0;
        $validRowCount = 0;
        $seenKeys = [];

        foreach ($rows as $rowNumber => $row) {
            if ($this->rowIsBlank($row)) {
                continue;
            }

            $rowCount++;
            $rowErrors = [];

            foreach ($definition['required'] as $required) {
                if (! array_key_exists($required, $row) || trim((string) $row[$required]) === '') {
                    $rowErrors[] = "Missing required value: {$required}.";
                }
            }

            foreach ($definition['allowed'] as $field => $allowed) {
                $value = trim((string) ($row[$field] ?? ''));

                if ($value !== '' && ! in_array($value, $allowed, true)) {
                    $rowErrors[] = "{$field} must be one of: " . implode(', ', $allowed) . '.';
                }
            }

            foreach (['email', 'reporting_to_email', 'next_of_kin_email'] as $emailField) {
                $value = trim((string) ($row[$emailField] ?? ''));

                if ($value !== '' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $rowErrors[] = "{$emailField} must be a valid email address.";
                }
            }

            if (($date = trim((string) ($row['date_of_birth'] ?? ''))) !== '' && ! $this->validDate($date)) {
                $rowErrors[] = 'date_of_birth must be a valid DD/MM/YYYY date.';
            }

            if ($kind === 'staff' && ($year = trim((string) ($row['start_year'] ?? ''))) !== ''
                && (! ctype_digit($year) || (int) $year < 1900 || (int) $year > 2100)) {
                $rowErrors[] = 'start_year must be a four-digit year.';
            }

            $uniqueField = $kind === 'staff' ? 'email' : 'student_number';
            $uniqueValue = Str::lower(trim((string) ($row[$uniqueField] ?? '')));

            if ($uniqueValue !== '') {
                if (isset($seenKeys[$uniqueField][$uniqueValue])) {
                    $rowErrors[] = "{$uniqueField} is duplicated in row {$seenKeys[$uniqueField][$uniqueValue]}.";
                } else {
                    $seenKeys[$uniqueField][$uniqueValue] = $rowNumber;
                }
            }

            if ($rowErrors === []) {
                $validRowCount++;
            } else {
                $errors[] = ['row' => $rowNumber, 'messages' => array_values(array_unique($rowErrors))];
            }
        }

        $attachment = $this->attachmentService->store(
            $submission,
            $invitation,
            $file,
            'migration_' . $kind,
            'if_migrating'
        );

        $upload = DB::transaction(function () use (
            $submission,
            $invitation,
            $attachment,
            $kind,
            $definition,
            $compatibility,
            $headers,
            $errors,
            $rowCount,
            $validRowCount
        ): CrmClientSetupMigrationUpload {
            $upload = $submission->migrationUploads()->create([
                'invitation_id' => $invitation->id,
                'attachment_id' => $attachment->id,
                'kind' => $kind,
                'template_version' => $compatibility['matched_version'] ?: 'unknown',
                'template_compatibility_status' => $compatibility['status'],
                'template_fingerprint' => $compatibility['fingerprint'],
                'original_name' => $attachment->original_name,
                'row_count' => $rowCount,
                'valid_row_count' => $validRowCount,
                'error_count' => count($errors),
                'validation_status' => $errors === [] ? 'validated' : 'has_errors',
                'validation_errors' => array_slice($errors, 0, 250),
                'headers' => $headers,
                'uploaded_at' => now(),
            ]);

            if ($errors !== []) {
                $upload->migrationErrors()->createMany(array_map(
                    fn (array $error): array => [
                        'row_number' => (int) ($error['row'] ?? 1),
                        'messages' => array_values($error['messages'] ?? []),
                    ],
                    $errors
                ));
            }

            $submission->forceFill([
                'last_activity_at' => now(),
                'status' => $this->optionalStatus($submission),
            ])->save();

            $this->auditService->record($submission, 'migration_upload_validated', [
                'invitation' => $invitation,
                'metadata' => [
                    'kind' => $kind,
                    'template_version' => config('client_setup.template_version', '1.0'),
                    'template_compatibility_status' => $upload->template_compatibility_status,
                    'template_fingerprint' => $upload->template_fingerprint,
                    'row_count' => $rowCount,
                    'valid_row_count' => $validRowCount,
                    'error_count' => count($errors),
                    'validation_status' => $upload->validation_status,
                ],
            ]);

            return $upload->fresh(['attachment']);
        });

        if ($upload->validation_status === 'has_errors') {
            $this->notificationService->notifySubmission(
                $submission->fresh(['invitations', 'assignedTo']),
                'migration_validation_failed',
                [
                    'audiences' => ['crm'],
                    'context_key' => 'migration-upload:' . $upload->uuid,
                    'details' => [
                        ucfirst($kind) . ' workbook: ' . $upload->error_count . ' validation issue(s).',
                    ],
                ]
            );
        }

        return $upload;
    }

    public function approveForImport(CrmClientSetupMigrationUpload $upload, User $actor, ?string $note = null): CrmClientSetupMigrationUpload
    {
        return DB::transaction(function () use ($upload, $actor, $note): CrmClientSetupMigrationUpload {
            $locked = CrmClientSetupMigrationUpload::query()
                ->with(['submission', 'attachment'])
                ->lockForUpdate()
                ->findOrFail($upload->id);

            if ($locked->validation_status !== 'validated') {
                throw new RuntimeException('Only a workbook with no validation errors can be approved for import.');
            }

            if ($locked->template_compatibility_status !== 'compatible') {
                throw new RuntimeException('The workbook must match the current approved template version before it can be imported.');
            }

            if (! $locked->attachment || $locked->attachment->scan_status !== 'approved') {
                throw new RuntimeException('The workbook must pass security scanning before it can be approved for import.');
            }

            $locked->forceFill([
                'crm_approval_status' => 'approved',
                'crm_approved_by_id' => $actor->id,
                'crm_approved_at' => now(),
                'crm_approval_note' => $note ? trim($note) : null,
            ])->save();

            $this->auditService->record($locked->submission, 'migration_upload_approved_for_import', [
                'user' => $actor,
                'actor_type' => 'crm_user',
                'metadata' => [
                    'migration_upload_uuid' => $locked->uuid,
                    'kind' => $locked->kind,
                    'template_version' => $locked->template_version,
                ],
            ]);

            return $locked->fresh(['attachment', 'crmApprovedBy']);
        });
    }

    private function readRows(UploadedFile $file, string $kind): array
    {
        try {
            $sheet = IOFactory::load($file->getRealPath())->getActiveSheet();
            $rawRows = $sheet->toArray('', false, false, false);
        } catch (Throwable $exception) {
            throw new RuntimeException('The spreadsheet could not be read. Please use the supplied XLSX template.');
        }

        if ($rawRows === []) {
            return [[], [], ['The spreadsheet is empty.'], [
                'status' => 'incompatible',
                'matched_version' => null,
                'fingerprint' => $this->compatibilityService->fingerprint([]),
            ]];
        }

        $headerRow = array_shift($rawRows);
        $headers = array_map(fn ($header): string => Str::lower(trim((string) $header)), $this->trimTrailingEmptyCells($headerRow));
        $compatibility = $this->compatibilityService->inspect($kind, $headers);
        $headerErrors = $compatibility['errors'];

        $rows = [];

        foreach ($rawRows as $index => $rawRow) {
            $row = [];

            foreach ($headers as $columnIndex => $header) {
                $row[$header] = $rawRow[$columnIndex] ?? null;
            }

            $rows[$index + 2] = $row;
        }

        return [$headers, $rows, $headerErrors, $compatibility];
    }

    private function trimTrailingEmptyCells(array $row): array
    {
        while ($row !== [] && trim((string) end($row)) === '') {
            array_pop($row);
        }

        return $row;
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

    private function validDate(string $value): bool
    {
        if (is_numeric($value)) {
            try {
                SpreadsheetDate::excelToDateTimeObject((float) $value);
                return true;
            } catch (Throwable) {
                return false;
            }
        }

        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y'] as $format) {
            $date = \DateTime::createFromFormat('!' . $format, $value);

            if ($date && $date->format($format) === $value) {
                return true;
            }
        }

        return false;
    }

    private function optionalStatus(CrmClientSetupSubmission $submission): string
    {
        return $submission->status === 'academic_submitted'
            ? 'supplemental_in_progress'
            : $submission->status;
    }
}
