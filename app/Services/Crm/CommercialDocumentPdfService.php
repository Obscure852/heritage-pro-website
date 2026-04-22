<?php

namespace App\Services\Crm;

use App\Models\CrmCommercialDocumentArtifact;
use App\Models\CrmInvoice;
use App\Models\CrmQuote;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CommercialDocumentPdfService
{
    public function ensureQuoteArtifact(CrmQuote $quote, ?User $generatedBy = null): CrmCommercialDocumentArtifact
    {
        return $this->ensureArtifact('quote', $quote, $generatedBy);
    }

    public function ensureInvoiceArtifact(CrmInvoice $invoice, ?User $generatedBy = null): CrmCommercialDocumentArtifact
    {
        return $this->ensureArtifact('invoice', $invoice, $generatedBy);
    }

    public function openResponse(CrmCommercialDocumentArtifact $artifact): BinaryFileResponse
    {
        return response()->file(
            $this->absolutePath($artifact),
            [
                'Content-Type' => $artifact->mime_type ?: 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . addslashes($artifact->original_name) . '"',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function downloadResponse(CrmCommercialDocumentArtifact $artifact): BinaryFileResponse
    {
        return response()->download(
            $this->absolutePath($artifact),
            $artifact->original_name,
            [
                'Content-Type' => $artifact->mime_type ?: 'application/pdf',
            ]
        );
    }

    public function absolutePath(CrmCommercialDocumentArtifact $artifact): string
    {
        return Storage::disk($artifact->disk ?: 'documents')->path($artifact->path);
    }

    private function ensureArtifact(string $type, CrmQuote|CrmInvoice $document, ?User $generatedBy = null): CrmCommercialDocumentArtifact
    {
        $document = $this->loadDocument($type, $document);
        $artifact = $this->currentArtifact($type, $document);

        if ($artifact !== null && $this->artifactIsCurrent($artifact, $document)) {
            return $artifact;
        }

        $binary = Pdf::loadView(
            $type === 'quote' ? 'pdf.crm.commercial.quote' : 'pdf.crm.commercial.invoice',
            [
                'document' => $document,
                'accountName' => $document->customer?->company_name ?: $document->lead?->company_name ?: 'Unassigned account',
            ]
        )->setPaper('a4')->output();

        $disk = 'documents';
        $path = $this->artifactPath($type, $document);
        $originalName = $this->originalFileName($type, $document);

        if ($artifact !== null && $artifact->path !== '' && $artifact->path !== $path) {
            Storage::disk($artifact->disk ?: $disk)->delete($artifact->path);
        }

        Storage::disk($disk)->put($path, $binary);

        $artifact ??= new CrmCommercialDocumentArtifact();
        $artifact->fill([
            'owner_id' => $document->owner_id,
            'quote_id' => $type === 'quote' ? $document->id : null,
            'invoice_id' => $type === 'invoice' ? $document->id : null,
            'generated_by_id' => $generatedBy?->id,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $originalName,
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => strlen($binary),
            'source_updated_at' => $document->updated_at,
            'generated_at' => now(),
        ])->save();

        return $artifact->fresh();
    }

    private function currentArtifact(string $type, CrmQuote|CrmInvoice $document): ?CrmCommercialDocumentArtifact
    {
        return CrmCommercialDocumentArtifact::query()
            ->when($type === 'quote', function ($query) use ($document) {
                $query->where('quote_id', $document->id);
            })
            ->when($type === 'invoice', function ($query) use ($document) {
                $query->where('invoice_id', $document->id);
            })
            ->first();
    }

    private function artifactIsCurrent(CrmCommercialDocumentArtifact $artifact, CrmQuote|CrmInvoice $document): bool
    {
        if (! Storage::disk($artifact->disk ?: 'documents')->exists($artifact->path)) {
            return false;
        }

        if ($artifact->source_updated_at === null || $document->updated_at === null) {
            return false;
        }

        return $artifact->source_updated_at->greaterThanOrEqualTo($document->updated_at);
    }

    private function loadDocument(string $type, CrmQuote|CrmInvoice $document): CrmQuote|CrmInvoice
    {
        $document->loadMissing([
            'owner',
            'lead',
            'customer',
            'contact',
            'request',
            'items.product',
        ]);

        return $document;
    }

    private function artifactPath(string $type, CrmQuote|CrmInvoice $document): string
    {
        $directory = $type === 'quote'
            ? 'crm/commercial/quotes/' . $document->id
            : 'crm/commercial/invoices/' . $document->id;

        return $directory . '/' . $this->originalFileName($type, $document);
    }

    private function originalFileName(string $type, CrmQuote|CrmInvoice $document): string
    {
        $documentNumber = $type === 'quote' ? $document->quote_number : $document->invoice_number;
        $fallback = $type === 'quote' ? 'quote' : 'invoice';
        $slug = Str::slug((string) ($documentNumber ?: $fallback));

        return ($slug !== '' ? $slug : $fallback) . '.pdf';
    }
}
