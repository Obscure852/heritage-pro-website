<?php

namespace App\Services\ClientSetup;

use App\Models\CrmClientSetupRevision;
use App\Models\CrmClientSetupSubmission;
use RuntimeException;

class ClientSetupRevisionComparisonService
{
    public function compare(
        CrmClientSetupSubmission $submission,
        int $fromRevisionNumber,
        int $toRevisionNumber
    ): array {
        if ($fromRevisionNumber === $toRevisionNumber) {
            throw new RuntimeException('Choose two different revisions to compare.');
        }

        $revisions = $submission->revisions()
            ->with('user')
            ->whereIn('revision_number', [$fromRevisionNumber, $toRevisionNumber])
            ->get()
            ->keyBy('revision_number');

        if (! $revisions->has($fromRevisionNumber) || ! $revisions->has($toRevisionNumber)) {
            throw new RuntimeException('One or both selected revisions could not be found.');
        }

        /** @var CrmClientSetupRevision $from */
        $from = $revisions->get($fromRevisionNumber);
        /** @var CrmClientSetupRevision $to */
        $to = $revisions->get($toRevisionNumber);
        $fromPayload = is_array($from->payload) ? $from->payload : [];
        $toPayload = is_array($to->payload) ? $to->payload : [];
        $fromFlat = $this->flatten($fromPayload);
        $toFlat = $this->flatten($toPayload);
        $keys = array_values(array_unique(array_merge(array_keys($fromFlat), array_keys($toFlat))));
        sort($keys, SORT_NATURAL);
        $changes = [];

        foreach ($keys as $key) {
            $fromExists = array_key_exists($key, $fromFlat);
            $toExists = array_key_exists($key, $toFlat);
            $fromValue = $fromExists ? $fromFlat[$key] : null;
            $toValue = $toExists ? $toFlat[$key] : null;

            if ($fromExists && $toExists && $this->valuesEqual($fromValue, $toValue)) {
                continue;
            }

            $changes[] = [
                'key' => $key,
                'from' => $fromExists ? $this->displayValue($fromValue) : '(not provided)',
                'to' => $toExists ? $this->displayValue($toValue) : '(removed)',
                'from_exists' => $fromExists,
                'to_exists' => $toExists,
            ];
        }

        return [
            'from' => $from,
            'to' => $to,
            'changes' => $changes,
            'changed_count' => count($changes),
        ];
    }

    private function flatten(array $value, string $prefix = ''): array
    {
        if ($value === []) {
            return $prefix === '' ? [] : [$prefix => []];
        }

        $flat = [];

        foreach ($value as $key => $child) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (is_array($child)) {
                $flat += $this->flatten($child, $path);
                continue;
            }

            $flat[$path] = $child;
        }

        return $flat;
    }

    private function valuesEqual(mixed $left, mixed $right): bool
    {
        return $this->canonicalValue($left) === $this->canonicalValue($right);
    }

    private function canonicalValue(mixed $value): string
    {
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) ($value ?? '');
    }

    private function displayValue(mixed $value): string
    {
        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return $value === null || $value === '' ? '(blank)' : (string) $value;
    }
}
