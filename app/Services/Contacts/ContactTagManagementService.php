<?php

namespace App\Services\Contacts;

use App\Models\ContactTag;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContactTagManagementService
{
    public function save(?ContactTag $contactTag, array $data): ContactTag
    {
        return DB::transaction(function () use ($contactTag, $data): ContactTag {
            $contactTag = $contactTag
                ? ContactTag::query()->whereKey($contactTag->id)->lockForUpdate()->firstOrFail()
                : new ContactTag();

            $name = trim((string) ($data['name'] ?? ''));
            $slug = trim((string) ($data['slug'] ?? ''));

            $contactTag->fill([
                'name' => $name,
                'slug' => $slug !== '' ? $slug : ContactTag::buildSlug($name),
                'description' => $this->normalizeNullableString($data['description'] ?? null),
                'color' => $this->normalizeNullableString($data['color'] ?? null),
                'is_active' => (bool) ($data['is_active'] ?? false),
                'usable_in_assets' => (bool) ($data['usable_in_assets'] ?? false),
                'usable_in_maintenance' => (bool) ($data['usable_in_maintenance'] ?? false),
                'sort_order' => max(0, (int) ($data['sort_order'] ?? 0)),
            ]);

            try {
                $contactTag->save();
            } catch (QueryException $exception) {
                throw $this->normalizeWriteException($exception);
            }

            return $contactTag->fresh();
        }, 3);
    }

    public function delete(ContactTag $contactTag): void
    {
        DB::transaction(function () use ($contactTag): void {
            $contactTag = ContactTag::query()->whereKey($contactTag->id)->lockForUpdate()->firstOrFail();

            if ($contactTag->contacts()->exists()) {
                throw ValidationException::withMessages([
                    'tag' => ['Cannot delete a tag that is already assigned to contacts.'],
                ]);
            }

            $contactTag->delete();
        }, 3);
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normalizeWriteException(QueryException $exception): ValidationException
    {
        $message = strtolower((string) $exception->getMessage());

        if (str_contains($message, 'contact_tags_slug_unique') || str_contains($message, 'slug')) {
            return ValidationException::withMessages([
                'slug' => ['That tag slug is already in use.'],
            ]);
        }

        if (str_contains($message, 'contact_tags_name_unique') || str_contains($message, 'name')) {
            return ValidationException::withMessages([
                'name' => ['That tag name is already in use.'],
            ]);
        }

        return ValidationException::withMessages([
            'tag' => ['Unable to save the contact tag right now. Please try again.'],
        ]);
    }
}
