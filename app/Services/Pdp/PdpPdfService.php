<?php

namespace App\Services\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpPlanSignature;
use App\Models\SchoolSetup;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PdpPdfService
{
    public function __construct(
        private readonly PdpPlanViewService $viewService
    ) {
    }

    public function buildViewData(PdpPlan $plan, ?User $actor = null): array
    {
        $plan = $plan->fresh([
            'template.sections.fields.childFields',
            'template.periods',
            'template.ratingSchemes',
            'template.approvalSteps',
            'reviews',
            'sectionEntries.childEntries',
            'signatures.review',
            'signatures.signer',
            'user',
            'supervisor',
        ]);

        $school = SchoolSetup::query()->first();

        return $this->viewService->buildPlanViewModel($plan, $actor) + [
            'viewService' => $this->viewService,
            'pdfService' => $this,
            'school' => $school,
            'logoBase64' => $this->encodeImage($school?->logo_path),
            'showLogo' => (bool) data_get($plan->template->settings_json, 'pdf.show_logo', false),
            'pdfTitle' => data_get($plan->template->settings_json, 'pdf.title', 'Staff Performance Development Plan'),
            'generatedAt' => now(),
        ];
    }

    public function download(PdpPlan $plan, ?User $actor = null): Response
    {
        $data = $this->buildViewData($plan, $actor);

        return Pdf::loadView('pdp.pdf.plan', $data)
            ->setPaper('a4', 'portrait')
            ->download($this->fileName($plan));
    }

    public function fileName(PdpPlan $plan): string
    {
        $employeeName = trim((string) ($plan->user?->full_name ?? $plan->user?->firstname . ' ' . $plan->user?->lastname));
        $employeeSlug = Str::slug($employeeName ?: 'employee');

        return sprintf(
            'pdp-%s-%s-%s.pdf',
            $employeeSlug,
            Str::slug($plan->template->code),
            $plan->plan_period_start->format('Y')
        );
    }

    public function signatureDataUri(PdpPlanSignature $signature): ?string
    {
        return $this->encodeImage($signature->resolved_signature_path);
    }

    private function encodeImage(?string $path): ?string
    {
        $filePath = $this->resolveImagePath($path);
        if (!$filePath) {
            return null;
        }

        $contents = @file_get_contents($filePath);
        if ($contents === false) {
            return null;
        }

        $mimeType = mime_content_type($filePath) ?: 'image/png';

        return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
    }

    private function resolveImagePath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $candidates = [
            $path,
            public_path($path),
            storage_path('app/public/' . ltrim($path, '/')),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
