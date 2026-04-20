<?php

namespace App\Http\Controllers;

use App\Models\NotificationTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationTemplateController extends Controller
{
    /**
     * Display a listing of templates
     */
    public function index(Request $request)
    {
        $type = $request->get('type'); // Filter by email or sms

        $templates = NotificationTemplate::with('creator')
            ->when($type, function ($query, $type) {
                return $query->ofType($type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('notifications.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        return view('notifications.templates.create');
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:email,sms',
            'subject' => 'nullable|required_if:type,email|string|max:255',
            'body' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            // Extract variables from body and subject
            $variables = NotificationTemplate::extractVariables(
                $validated['body'],
                $validated['subject'] ?? null
            );

            $template = NotificationTemplate::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'subject' => $validated['subject'] ?? null,
                'body' => $validated['body'],
                'variables' => $variables,
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'created_by' => auth()->id(),
            ]);

            return redirect()
                ->route('templates.index')
                ->with('message', 'Template created successfully! Found ' . count($variables) . ' variable(s).');

        } catch (\Exception $e) {
            Log::error('Error creating template', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to create template. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Display the specified template
     */
    public function show(NotificationTemplate $template)
    {
        $template->load('creator');
        return view('notifications.templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified template
     */
    public function edit(NotificationTemplate $template)
    {
        return view('notifications.templates.edit', compact('template'));
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, NotificationTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:email,sms',
            'subject' => 'nullable|required_if:type,email|string|max:255',
            'body' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            // Extract variables from body and subject
            $variables = NotificationTemplate::extractVariables(
                $validated['body'],
                $validated['subject'] ?? null
            );

            $template->update([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'subject' => $validated['subject'] ?? null,
                'body' => $validated['body'],
                'variables' => $variables,
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? $template->is_active,
            ]);

            return redirect()
                ->route('templates.index')
                ->with('message', 'Template updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating template', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to update template. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified template
     */
    public function destroy(NotificationTemplate $template)
    {
        try {
            $template->delete();

            return redirect()
                ->route('templates.index')
                ->with('message', 'Template deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting template', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to delete template. Please try again.']);
        }
    }

    /**
     * Toggle template active status
     */
    public function toggleStatus(NotificationTemplate $template)
    {
        try {
            $template->update(['is_active' => !$template->is_active]);

            return response()->json([
                'success' => true,
                'is_active' => $template->is_active,
                'message' => 'Template status updated successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update template status.',
            ], 500);
        }
    }

    /**
     * Get templates for AJAX calls (for use in send forms)
     */
    public function getTemplates(Request $request)
    {
        $type = $request->get('type', 'email');

        $templates = NotificationTemplate::active()
            ->ofType($type)
            ->select('id', 'name', 'subject', 'body', 'variables')
            ->orderBy('name')
            ->get();

        return response()->json($templates);
    }
}
