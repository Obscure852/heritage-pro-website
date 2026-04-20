<?php

namespace App\Http\Controllers;

use App\Models\WhatsappTemplate;
use App\Services\Messaging\TwilioWhatsAppService;
use Illuminate\Http\Request;

class WhatsappTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $templates = WhatsappTemplate::query()
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('name')
            ->paginate(20);

        return view('communications.whatsapp-templates', [
            'templates' => $templates,
        ]);
    }

    public function apiList()
    {
        $templates = WhatsappTemplate::approved()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'language',
                'category',
                'status',
                'body_preview',
                'variables',
                'external_id',
            ]);

        return response()->json([
            'success' => true,
            'templates' => $templates,
        ]);
    }

    public function sync(Request $request, TwilioWhatsAppService $service)
    {
        $result = $service->syncTemplates($request->integer('limit'));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Synced {$result['count']} WhatsApp templates.",
                'result' => $result,
            ]);
        }

        return redirect()->back()->with('message', "Synced {$result['count']} WhatsApp templates.");
    }
}
