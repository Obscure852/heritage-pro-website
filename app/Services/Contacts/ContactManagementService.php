<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use App\Models\ContactPerson;
use App\Models\ContactTag;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContactManagementService
{
    public function save(?Contact $contact, array $contactData, array $peopleRows = [], array $tagIds = []): Contact
    {
        return DB::transaction(function () use ($contact, $contactData, $peopleRows, $tagIds): Contact {
            $contact = $contact
                ? Contact::query()->whereKey($contact->id)->lockForUpdate()->firstOrFail()
                : new Contact();

            $normalizedPeople = $this->normalizePeopleRows($peopleRows);

            if ($normalizedPeople->isEmpty()) {
                throw ValidationException::withMessages([
                    'people' => ['Add at least one contact person.'],
                ]);
            }

            $contact->fill([
                'name' => trim((string) ($contactData['name'] ?? '')),
                'email' => $this->normalizeNullableString($contactData['email'] ?? null),
                'phone' => $this->normalizeNullableString($contactData['phone'] ?? null),
                'address' => $this->normalizeNullableString($contactData['address'] ?? null),
                'notes' => $this->normalizeNullableString($contactData['notes'] ?? null),
                'is_active' => (bool) ($contactData['is_active'] ?? false),
            ]);

            try {
                $contact->save();
            } catch (QueryException $exception) {
                throw $this->normalizeWriteException($exception);
            }

            $validatedTagIds = ContactTag::query()
                ->whereIn('id', $tagIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $contact->tags()->sync($validatedTagIds);

            ContactPerson::query()->where('contact_id', $contact->id)->delete();

            $normalizedPeople
                ->values()
                ->each(function (array $person, int $index) use ($contact): void {
                    $contact->people()->create([
                        'name' => $person['name'],
                        'title' => $person['title'],
                        'email' => $person['email'],
                        'phone' => $person['phone'],
                        'is_primary' => $person['is_primary'],
                        'sort_order' => $index,
                    ]);
                });

            return $contact->fresh(['people', 'primaryPerson', 'tags']);
        }, 3);
    }

    public function delete(Contact $contact): void
    {
        DB::transaction(function () use ($contact): void {
            $contact = Contact::query()->whereKey($contact->id)->lockForUpdate()->firstOrFail();

            if ($contact->assets()->exists()) {
                throw ValidationException::withMessages([
                    'contact' => ['Cannot delete a business contact that is linked to assets.'],
                ]);
            }

            if ($contact->maintenances()->exists()) {
                throw ValidationException::withMessages([
                    'contact' => ['Cannot delete a business contact that is linked to maintenance records.'],
                ]);
            }

            $contact->tags()->detach();
            $contact->people()->delete();
            $contact->delete();
        }, 3);
    }

    public function resolveImportContactByName(string $name): Contact
    {
        return DB::transaction(function () use ($name): Contact {
            $normalizedName = trim($name);

            $contact = Contact::query()
                ->where('name', $normalizedName)
                ->lockForUpdate()
                ->first();

            if ($contact) {
                return $contact;
            }

            try {
                $contact = Contact::query()->create([
                    'name' => $normalizedName,
                    'is_active' => true,
                ]);
            } catch (QueryException $exception) {
                $message = strtolower((string) $exception->getMessage());

                if (str_contains($message, 'contacts_name_unique') || str_contains($message, 'duplicate')) {
                    return Contact::query()->where('name', $normalizedName)->firstOrFail();
                }

                throw $exception;
            }

            $vendorTag = ContactTag::query()
                ->where('slug', ContactTag::DEFAULT_VENDOR_SLUG)
                ->lockForUpdate()
                ->first();

            if ($vendorTag) {
                $contact->tags()->syncWithoutDetaching([$vendorTag->id]);
            }

            return $contact->fresh(['tags']);
        }, 3);
    }

    private function normalizePeopleRows(array $peopleRows): Collection
    {
        $normalized = collect($peopleRows)
            ->map(function (array $row): array {
                return [
                    'name' => trim((string) ($row['name'] ?? '')),
                    'title' => $this->normalizeNullableString($row['title'] ?? null),
                    'email' => $this->normalizeNullableString($row['email'] ?? null),
                    'phone' => $this->normalizeNullableString($row['phone'] ?? null),
                    'is_primary' => (bool) ($row['is_primary'] ?? false),
                ];
            })
            ->filter(function (array $row): bool {
                return $row['name'] !== '' || $row['title'] !== null || $row['email'] !== null || $row['phone'] !== null;
            })
            ->values();

        if ($normalized->isEmpty()) {
            return collect();
        }

        // Ensure exactly one primary person survives every write.
        $primaryIndex = $normalized->search(fn (array $row): bool => $row['is_primary']);
        $primaryIndex = $primaryIndex === false ? 0 : (int) $primaryIndex;

        return $normalized->values()->map(function (array $row, int $index) use ($primaryIndex): array {
            $row['name'] = $row['name'] === '' ? 'Unnamed Contact' : $row['name'];
            $row['is_primary'] = $index === $primaryIndex;

            return $row;
        });
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
                'name' => ['That name is already in use.'],
            ]);
        }

        return ValidationException::withMessages([
            'contact' => ['Unable to save the contact right now. Please try again.'],
        ]);
    }
}
