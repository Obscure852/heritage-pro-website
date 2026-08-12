<?php

namespace App\Services\ClientSetup;

use App\Models\CrmClientSetupSubmission;
use Carbon\Carbon;

class ClientSetupAcademicService
{
    public function fields(string $stageKey): array
    {
        return config("client_setup_academic.stages.{$stageKey}", []);
    }

    public function hasStructuredSchema(string $stageKey): bool
    {
        return $this->fields($stageKey) !== [];
    }

    public function normalize(string $stageKey, array $payload): array
    {
        return $this->normalizeFields($this->fields($stageKey), $payload);
    }

    public function validateStage(string $stageKey, array $payload): array
    {
        $normalized = $this->normalize($stageKey, $payload);
        $errors = [];
        $errorDetails = [];

        $this->validateFields($this->fields($stageKey), $normalized, '', $errors, $errorDetails);
        $this->validateStageInvariants($stageKey, $normalized, $errors, $errorDetails);

        return [
            'payload' => $normalized,
            'errors' => array_values(array_unique($errors)),
            'error_details' => $this->uniqueErrorDetails($errorDetails),
        ];
    }

    public function readiness(CrmClientSetupSubmission $submission): array
    {
        $payload = $submission->payloadArray();
        $progress = $submission->stageProgress->keyBy('stage_key');
        $missingStages = [];
        $missingFields = [];
        $summary = [];

        foreach (config('client_setup.stages', []) as $stage) {
            if (! $stage['required_for_academic']) {
                continue;
            }

            $result = $this->validateStage($stage['key'], $payload[$stage['key']] ?? []);
            $stageProgress = $progress->get($stage['key']);
            $stageComplete = $stageProgress?->status === 'complete';

            if (! $stageComplete || $result['errors'] !== []) {
                $missingStages[] = $stage['key'];
                foreach ($result['errors'] as $error) {
                    $missingFields[] = [
                        'stage' => $stage['key'],
                        'label' => $stage['label'],
                        'message' => $error,
                    ];
                }
            }

            $summary[$stage['key']] = [
                'label' => $stage['label'],
                'complete' => $stageComplete && $result['errors'] === [],
                'status' => $stageProgress?->status ?: 'not_started',
                'errors' => $result['errors'],
            ];
        }

        $warnings = [];

        if (($payload['finance']['finance_scope_decision'] ?? null) === null) {
            $warnings[] = 'Finance scope has not been configured; it will not block academic submission.';
        }

        if (($payload['integrations_access']['integration_scope'] ?? null) === null) {
            $warnings[] = 'Integrations and access requirements are still outstanding; they will not block academic submission.';
        }

        if (($payload['migration']['migration_scope'] ?? null) === null) {
            $warnings[] = 'Migration has not been scoped; staff and student templates can be handled later.';
        }

        $ready = $missingStages === [];

        return [
            'ready' => $ready,
            'missing_stages' => array_values(array_unique($missingStages)),
            'missing_fields' => $missingFields,
            'warnings' => $warnings,
            'summary' => $summary,
        ];
    }

    public function syncAcademicStatus(CrmClientSetupSubmission $submission): array
    {
        $readiness = $this->readiness($submission->fresh(['stageProgress']));

        if (! in_array($submission->academic_status, ['submitted', 'approved'], true)) {
            $hasPayload = $submission->payloadArray() !== [];
            $nextStatus = $readiness['ready'] ? 'ready' : ($hasPayload ? 'in_progress' : 'not_started');

            if ($submission->academic_status !== $nextStatus) {
                $submission->forceFill(['academic_status' => $nextStatus])->save();
            }
        }

        return $readiness;
    }

    public function isAcademicStage(string $stageKey): bool
    {
        foreach (config('client_setup.stages', []) as $stage) {
            if ($stage['key'] === $stageKey) {
                return (bool) $stage['required_for_academic'];
            }
        }

        return false;
    }

    private function normalizeFields(array $fields, array $payload): array
    {
        $normalized = [];

        foreach ($fields as $field) {
            $key = $field['key'];
            $value = $payload[$key] ?? ($field['default'] ?? null);

            if ($field['type'] === 'repeatable') {
                $rows = is_array($value) ? $value : [];
                $normalized[$key] = [];

                foreach ($rows as $row) {
                    if (! is_array($row)) {
                        continue;
                    }

                    $normalizedRow = $this->normalizeFields($field['fields'] ?? [], $row);

                    if ($this->hasMeaningfulValue($normalizedRow)) {
                        $normalized[$key][] = $normalizedRow;
                    }
                }

                continue;
            }

            if ($field['type'] === 'multiselect') {
                $normalized[$key] = is_array($value) ? array_values(array_filter($value, static fn ($item): bool => $item !== '')) : [];
            } elseif ($field['type'] === 'boolean') {
                $normalized[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            } elseif (is_string($value)) {
                $normalized[$key] = trim($value);
            } elseif ($value !== null) {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    private function validateFields(array $fields, array $payload, string $path, array &$errors, array &$errorDetails): void
    {
        foreach ($fields as $field) {
            $fieldPath = $path === '' ? $field['key'] : $path . '.' . $field['key'];
            $value = $payload[$field['key']] ?? null;
            $required = $this->isRequired($field, $payload);
            $label = $this->fieldLabel($fieldPath, $field['label']);

            if ($field['type'] === 'repeatable') {
                $rows = is_array($value) ? $value : [];

                if ($required && count($rows) < (int) ($field['min_items'] ?? 1)) {
                    $this->addError($errors, $errorDetails, $fieldPath, $label . ' requires at least ' . ($field['min_items'] ?? 1) . ' row(s).');
                }

                foreach ($rows as $index => $row) {
                    if (is_array($row)) {
                        $this->validateFields($field['fields'] ?? [], $row, $fieldPath . '.' . $index, $errors, $errorDetails);
                    }
                }

                continue;
            }

            if ($required && $this->isEmpty($value)) {
                $this->addError($errors, $errorDetails, $fieldPath, $label . ' is required.');
                continue;
            }

            if ($field['type'] === 'boolean' && ($field['must_be_true'] ?? false) && $value !== true) {
                $this->addError($errors, $errorDetails, $fieldPath, $label . ' must be confirmed.');
            }

            if ($field['type'] === 'multiselect' && $required && count((array) $value) < (int) ($field['min_items'] ?? 1)) {
                $this->addError($errors, $errorDetails, $fieldPath, $label . ' requires at least ' . ($field['min_items'] ?? 1) . ' selection(s).');
            }

            if ($field['type'] === 'email' && ! $this->isEmpty($value) && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->addError($errors, $errorDetails, $fieldPath, $label . ' must be a valid email address.');
            }

            if (isset($field['min_length']) && ! $this->isEmpty($value) && mb_strlen((string) $value) < $field['min_length']) {
                $this->addError($errors, $errorDetails, $fieldPath, $label . ' must be at least ' . $field['min_length'] . ' characters.');
            }

            if (isset($field['max_length']) && ! $this->isEmpty($value) && mb_strlen((string) $value) > $field['max_length']) {
                $this->addError($errors, $errorDetails, $fieldPath, $label . ' must be no more than ' . $field['max_length'] . ' characters.');
            }

            if ($field['type'] === 'number' && ! $this->isEmpty($value) && ! is_numeric($value)) {
                $this->addError($errors, $errorDetails, $fieldPath, $label . ' must be a number.');
            }

            if ($field['type'] === 'number' && is_numeric($value)) {
                if (isset($field['min']) && (float) $value < $field['min']) {
                    $this->addError($errors, $errorDetails, $fieldPath, $label . ' is below the allowed minimum.');
                }

                if (isset($field['max']) && (float) $value > $field['max']) {
                    $this->addError($errors, $errorDetails, $fieldPath, $label . ' is above the allowed maximum.');
                }
            }

            if (($field['not_future'] ?? false) && ! $this->isEmpty($value) && Carbon::parse($value)->isFuture()) {
                $this->addError($errors, $errorDetails, $fieldPath, $label . ' cannot be in the future.');
            }
        }
    }

    private function validateStageInvariants(string $stageKey, array $payload, array &$errors, array &$errorDetails): void
    {
        if ($stageKey === 'institution') {
            $this->validateUniqueRows($payload['campuses'] ?? [], 'code', 'Campus codes', $errors, $errorDetails, 'campuses');
            $this->validateUniqueRows($payload['programmes'] ?? [], 'code', 'Campus programme references', $errors, $errorDetails, 'programmes');

            $contacts = $payload['responsible_contacts'] ?? [];
            if ($contacts !== [] && ! collect($contacts)->contains(static fn (array $contact): bool => ! empty($contact['is_primary']))) {
                $this->addError($errors, $errorDetails, 'responsible_contacts', 'Responsible contacts must include one primary contact.');
            }

            if ($contacts !== [] && ! collect($contacts)->contains(static fn (array $contact): bool => ($contact['role'] ?? null) === 'academic_lead')) {
                $this->addError($errors, $errorDetails, 'responsible_contacts', 'Responsible contacts must include an academic lead.');
            }
        }

        if ($stageKey === 'programmes') {
            $programmes = $payload['programmes'] ?? [];
            $this->validateUniqueRows($programmes, 'code', 'Programme codes', $errors, $errorDetails, 'programmes');

            if ($programmes !== [] && ! collect($programmes)->contains(static fn (array $programme): bool => ! empty($programme['active']))) {
                $this->addError($errors, $errorDetails, 'programmes', 'Programme register must include at least one active programme.');
            }
        }

        if ($stageKey === 'curriculum' && ($payload['curriculum_in_scope'] ?? false)) {
            $this->validateUniqueRows($payload['curriculum_versions'] ?? [], 'code', 'Curriculum version codes', $errors, $errorDetails, 'curriculum_versions');

            foreach ($payload['curriculum_versions'] ?? [] as $index => $version) {
                $this->validateUniqueRows($version['modules'] ?? [], 'code', 'Module codes in curriculum version ' . ($index + 1), $errors, $errorDetails, 'curriculum_versions.' . $index . '.modules');
            }
        }

        if ($stageKey === 'assessment') {
            $weights = array_sum(array_map(static fn (array $component): float => (float) ($component['weight_percent'] ?? 0), $payload['assessment_components'] ?? []));

            if (($payload['assessment_components'] ?? []) !== [] && abs($weights - 100) > 0.01) {
                $this->addError($errors, $errorDetails, 'assessment_components', 'Assessment component weights must total 100% (currently ' . number_format($weights, 2) . '%).');
            }

            $this->validateRanges($payload['grade_bands'] ?? [], 'minimum_mark', 'maximum_mark', 'Grade bands', $errors, $errorDetails, 'grade_bands', true);
        }

        if ($stageKey === 'progression') {
            $this->validateUniqueRows($payload['progression_rules'] ?? [], 'decision_code', 'Progression decision codes', $errors, $errorDetails, 'progression_rules');
            $this->validateRanges($payload['classification_bands'] ?? [], 'minimum', 'maximum', 'Classification bands', $errors, $errorDetails, 'classification_bands', false);
        }
    }

    private function validateUniqueRows(array $rows, string $key, string $label, array &$errors, array &$errorDetails, string $path): void
    {
        $values = array_values(array_filter(array_map(static fn (array $row): string => trim((string) ($row[$key] ?? '')), $rows)));

        if (count($values) !== count(array_unique($values))) {
            $this->addError($errors, $errorDetails, $path, $label . ' must be unique.');
        }
    }

    private function validateRanges(array $rows, string $minimumKey, string $maximumKey, string $label, array &$errors, array &$errorDetails, string $path, bool $mustCoverMarks): void
    {
        if ($rows === []) {
            return;
        }

        usort($rows, static fn (array $left, array $right): int => (float) ($left[$minimumKey] ?? 0) <=> (float) ($right[$minimumKey] ?? 0));
        $previousMaximum = null;

        foreach ($rows as $row) {
            $minimum = (float) ($row[$minimumKey] ?? 0);
            $maximum = (float) ($row[$maximumKey] ?? 0);

            if ($maximum < $minimum) {
                $this->addError($errors, $errorDetails, $path, $label . ' contain a range where the maximum is below the minimum.');
            }

            if ($previousMaximum !== null && $minimum <= $previousMaximum) {
                $this->addError($errors, $errorDetails, $path, $label . ' must not overlap.');
            }

            $previousMaximum = $maximum;
        }

        if ($mustCoverMarks && (float) ($rows[0][$minimumKey] ?? 0) !== 0.0) {
            $this->addError($errors, $errorDetails, $path, $label . ' must start at 0.');
        }

        if ($mustCoverMarks && (float) ($rows[count($rows) - 1][$maximumKey] ?? 0) !== 100.0) {
            $this->addError($errors, $errorDetails, $path, $label . ' must cover the configured mark range through 100.');
        }
    }

    private function addError(array &$errors, array &$errorDetails, ?string $path, string $message): void
    {
        $errors[] = $message;
        $errorDetails[] = [
            'path' => $path,
            'message' => $message,
        ];
    }

    private function uniqueErrorDetails(array $errorDetails): array
    {
        $seen = [];

        return array_values(array_filter($errorDetails, static function (array $detail) use (&$seen): bool {
            $key = ($detail['path'] ?? '') . '|' . ($detail['message'] ?? '');

            if (isset($seen[$key])) {
                return false;
            }

            $seen[$key] = true;

            return true;
        }));
    }

    private function isRequired(array $field, array $payload): bool
    {
        if (($field['requirement'] ?? 'O') === 'R') {
            return true;
        }

        if (! isset($field['required_when'])) {
            return false;
        }

        $condition = $field['required_when'];
        $actual = $payload[$condition['field']] ?? null;

        if (array_key_exists('equals', $condition)) {
            return $actual === $condition['equals'];
        }

        if (array_key_exists('not_equals', $condition)) {
            return $actual !== $condition['not_equals'] && $actual !== null && $actual !== '';
        }

        return false;
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    private function hasMeaningfulValue(array $value): bool
    {
        foreach ($value as $item) {
            if (is_array($item) && $this->hasMeaningfulValue($item)) {
                return true;
            }

            if ($item !== null && $item !== '' && $item !== []) {
                return true;
            }
        }

        return false;
    }

    private function fieldLabel(string $path, string $label): string
    {
        return $path === '' ? $label : $label . ' (' . str_replace('.', ' › ', $path) . ')';
    }
}
