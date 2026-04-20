<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactTag;
use App\Services\Contacts\ContactManagementService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    public function __construct(private readonly ContactManagementService $contactManagementService)
    {
    }

    public function index(Request $request)
    {
        $contacts = Contact::query()
            ->with(['primaryPerson', 'tags'])
            ->withCount(['assets', 'maintenances'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . trim((string) $request->search) . '%';

                $query->where(function ($contactQuery) use ($search) {
                    $contactQuery->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhereHas('people', function ($peopleQuery) use ($search) {
                            $peopleQuery->where('name', 'like', $search)
                                ->orWhere('email', 'like', $search)
                                ->orWhere('phone', 'like', $search)
                                ->orWhere('title', 'like', $search);
                        });
                });
            })
            ->when($request->filled('tag_id'), function ($query) use ($request) {
                $query->whereHas('tags', fn ($tagQuery) => $tagQuery->whereKey($request->integer('tag_id')));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->string('status')->lower()->value() === 'active');
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $tags = ContactTag::query()->active()->orderBy('sort_order')->orderBy('name')->get();

        return view('contacts.index', compact('contacts', 'tags'));
    }

    public function create()
    {
        $contact = new Contact([
            'is_active' => true,
        ]);

        $tags = ContactTag::query()->active()->orderBy('sort_order')->orderBy('name')->get();
        $peopleRows = $this->buildPeopleRows();

        return view('contacts.create', compact('contact', 'tags', 'peopleRows'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        try {
            $contact = $this->contactManagementService->save(
                null,
                $this->extractContactData($validated, $request),
                $validated['people'] ?? [],
                $validated['tag_ids'] ?? []
            );
        } catch (ValidationException $exception) {
            throw $exception;
        }

        return redirect()
            ->route('contacts.show', $contact)
            ->with('message', 'Business contact created successfully.');
    }

    public function show(Contact $contact)
    {
        $contact->load([
            'people',
            'primaryPerson',
            'tags',
            'assets.category',
            'maintenances.asset',
        ]);

        return view('contacts.show', compact('contact'));
    }

    public function edit(Contact $contact)
    {
        $contact->load(['people', 'tags']);
        $tags = ContactTag::query()->active()->orderBy('sort_order')->orderBy('name')->get();
        $peopleRows = $this->buildPeopleRows($contact);

        return view('contacts.edit', compact('contact', 'tags', 'peopleRows'));
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $this->validatePayload($request);

        $contact = $this->contactManagementService->save(
            $contact,
            $this->extractContactData($validated, $request),
            $validated['people'] ?? [],
            $validated['tag_ids'] ?? []
        );

        return redirect()
            ->route('contacts.show', $contact)
            ->with('message', 'Business contact updated successfully.');
    }

    public function destroy(Contact $contact)
    {
        try {
            $this->contactManagementService->delete($contact);
        } catch (ValidationException $exception) {
            return redirect()
                ->back()
                ->withErrors($exception->errors())
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()
            ->route('contacts.index')
            ->with('message', 'Business contact deleted successfully.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:contact_tags,id',
            'people' => 'required|array|min:1',
            'people.*.name' => 'nullable|string|max:255',
            'people.*.title' => 'nullable|string|max:255',
            'people.*.email' => 'nullable|email|max:255',
            'people.*.phone' => 'nullable|string|max:50',
            'people.*.is_primary' => 'nullable|boolean',
        ]);
    }

    private function extractContactData(array $validated, Request $request): array
    {
        return [
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function buildPeopleRows(?Contact $contact = null): array
    {
        $rows = old('people');

        if (is_array($rows)) {
            return $this->padPeopleRows($rows);
        }

        $rows = $contact
            ? $contact->people->map(fn ($person) => [
                'name' => $person->name,
                'title' => $person->title,
                'email' => $person->email,
                'phone' => $person->phone,
                'is_primary' => $person->is_primary,
            ])->all()
            : [];

        return $this->padPeopleRows($rows);
    }

    private function padPeopleRows(array $rows): array
    {
        $rows = array_values($rows);
        $minimumRows = 3;

        while (count($rows) < $minimumRows) {
            $rows[] = [
                'name' => null,
                'title' => null,
                'email' => null,
                'phone' => null,
                'is_primary' => count($rows) === 0,
            ];
        }

        if (!collect($rows)->contains(fn ($row) => !empty($row['is_primary']))) {
            $rows[0]['is_primary'] = true;
        }

        return $rows;
    }
}
