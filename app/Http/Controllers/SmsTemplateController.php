<?php

namespace App\Http\Controllers;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display listing of templates
     */
    public function index(Request $request)
    {
        $query = array_filter([
            'sms_template_category' => $request->input('category'),
            'sms_template_search' => $request->input('search'),
            'sms_templates_page' => $request->input('page'),
        ], static fn ($value) => $value !== null && $value !== '');

        return redirect()->to($this->settingsTabUrl($query));
    }

    /**
     * Store a new template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', array_keys(SmsTemplate::CATEGORIES)),
            'content' => 'required|string|max:480',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['placeholders'] = $this->extractPlaceholders($validated['content']);

        $template = SmsTemplate::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Template created successfully.',
                'template' => $template
            ]);
        }

        return redirect()->to($this->settingsTabUrl())
            ->with('message', 'Template created successfully.');
    }

    /**
     * Update a template
     */
    public function update(Request $request, SmsTemplate $smsTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', array_keys(SmsTemplate::CATEGORIES)),
            'content' => 'required|string|max:480',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $validated['placeholders'] = $this->extractPlaceholders($validated['content']);

        $smsTemplate->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully.',
                'template' => $smsTemplate->fresh()
            ]);
        }

        return redirect()->to($this->settingsTabUrl())
            ->with('message', 'Template updated successfully.');
    }

    /**
     * Delete a template
     */
    public function destroy(Request $request, SmsTemplate $smsTemplate)
    {
        $smsTemplate->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully.'
            ]);
        }

        return redirect()->to($this->settingsTabUrl())
            ->with('message', 'Template deleted successfully.');
    }

    /**
     * Toggle template active status
     */
    public function toggleActive(SmsTemplate $smsTemplate)
    {
        $smsTemplate->update(['is_active' => !$smsTemplate->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $smsTemplate->is_active,
            'message' => $smsTemplate->is_active ? 'Template activated.' : 'Template deactivated.'
        ]);
    }

    /**
     * API endpoint to list active templates (for use in bulk SMS form)
     */
    public function apiList(Request $request)
    {
        $query = SmsTemplate::active()
            ->select('id', 'name', 'category', 'content', 'description')
            ->orderBy('category')
            ->orderBy('name');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $templates = $query->get()->map(function ($template) {
            return [
                'id' => $template->id,
                'name' => $template->name,
                'category' => $template->category,
                'category_label' => SmsTemplate::CATEGORIES[$template->category] ?? $template->category,
                'content' => $template->content,
                'description' => $template->description,
                'character_count' => strlen($template->content),
                'sms_units' => ceil(strlen($template->content) / 160),
            ];
        });

        return response()->json([
            'success' => true,
            'templates' => $templates,
            'categories' => SmsTemplate::CATEGORIES,
        ]);
    }

    /**
     * API endpoint to get a single template
     */
    public function apiShow(SmsTemplate $smsTemplate)
    {
        $smsTemplate->incrementUsage();

        return response()->json([
            'success' => true,
            'template' => [
                'id' => $smsTemplate->id,
                'name' => $smsTemplate->name,
                'category' => $smsTemplate->category,
                'content' => $smsTemplate->content,
                'description' => $smsTemplate->description,
                'placeholders' => $smsTemplate->placeholders,
                'character_count' => strlen($smsTemplate->content),
                'sms_units' => ceil(strlen($smsTemplate->content) / 160),
            ],
            'available_placeholders' => SmsTemplate::AVAILABLE_PLACEHOLDERS,
        ]);
    }

    /**
     * Extract placeholders from content
     */
    private function extractPlaceholders(string $content): array
    {
        preg_match_all('/\{([^}]+)\}/', $content, $matches);
        return $matches[0] ?? [];
    }

    private function settingsTabUrl(array $query = []): string
    {
        return route('setup.communications-setup', $query) . '#sms-templates-settings';
    }
}
