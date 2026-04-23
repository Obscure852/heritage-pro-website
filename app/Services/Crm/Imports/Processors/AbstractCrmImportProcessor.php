<?php

namespace App\Services\Crm\Imports\Processors;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;

abstract class AbstractCrmImportProcessor
{
    protected function normalizeString(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : (is_numeric($value) ? trim((string) $value) : null);

        return $value === '' ? null : $value;
    }

    protected function normalizeBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = Str::lower(trim((string) $value));

        return match ($normalized) {
            '1', 'true', 'yes', 'y', 'active', 'primary' => true,
            '0', 'false', 'no', 'n', 'inactive', 'secondary' => false,
            default => null,
        };
    }

    protected function normalizeDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(SpreadsheetDate::excelToDateTimeObject((float) $value))->toDateString();
            }

            $normalized = trim((string) $value);
            $date = Carbon::createFromFormat('!d/m/Y', $normalized);

            if ($date === false || $date->format('d/m/Y') !== $normalized) {
                return null;
            }

            return $date->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function normalizeDelimitedList(mixed $value, string $delimiter = '|'): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        return collect(explode($delimiter, (string) $value))
            ->map(fn ($item) => $this->normalizeString($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function allowedOwnerByEmail(?string $email): ?User
    {
        if ($email === null) {
            return null;
        }

        return User::query()
            ->where('email', $email)
            ->where('active', true)
            ->whereIn('role', array_keys(config('heritage_crm.roles', [])))
            ->first();
    }

    protected function validationErrors(array $payload, array $rules, array $messages = []): array
    {
        $validator = Validator::make($payload, $rules, $messages);

        return $validator->fails()
            ? $validator->errors()->all()
            : [];
    }
}
