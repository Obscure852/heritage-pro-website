<?php

namespace App\Http\Controllers;

use App\Models\ContactTag;
use App\Services\Contacts\ContactTagManagementService;
use Illuminate\Http\Request;

class ContactTagController extends Controller
{
    public function __construct(private readonly ContactTagManagementService $contactTagManagementService)
    {
    }

    public function index()
    {
        $contactTags = ContactTag::query()
            ->withCount('contacts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('contacts.settings', compact('contactTags'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $this->contactTagManagementService->save(null, $this->extractData($validated, $request));

        return redirect()
            ->route('contacts.settings')
            ->with('message', 'Contact tag created successfully.');
    }

    public function update(Request $request, ContactTag $contactTag)
    {
        $validated = $this->validatePayload($request);

        $this->contactTagManagementService->save($contactTag, $this->extractData($validated, $request));

        return redirect()
            ->route('contacts.settings')
            ->with('message', 'Contact tag updated successfully.');
    }

    public function destroy(ContactTag $contactTag)
    {
        try {
            $this->contactTagManagementService->delete($contactTag);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return redirect()
                ->back()
                ->withErrors($exception->errors())
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()
            ->route('contacts.settings')
            ->with('message', 'Contact tag deleted successfully.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'usable_in_assets' => 'nullable|boolean',
            'usable_in_maintenance' => 'nullable|boolean',
        ]);
    }

    private function extractData(array $validated, Request $request): array
    {
        return [
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
            'usable_in_assets' => $request->boolean('usable_in_assets'),
            'usable_in_maintenance' => $request->boolean('usable_in_maintenance'),
        ];
    }
}
