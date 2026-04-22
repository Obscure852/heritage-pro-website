@php
    $attachments = $attachments ?? collect();
    $allowDelete = $allowDelete ?? false;
@endphp

<section class="crm-card">
    <div class="crm-card-title">
        <div>
            <p class="crm-kicker">Attachments</p>
            <h2>{{ $title ?? 'Supporting files' }}</h2>
            <p>{{ $subtitle ?? 'Open CRM attachments directly from the workspace, including PDF and DOCX files.' }}</p>
        </div>
    </div>

    @if ($attachments->isEmpty())
        <div class="crm-attachment-empty">No attachments have been added to this request yet.</div>
    @else
        <div class="crm-attachments-grid">
            @foreach ($attachments as $attachment)
                <article class="crm-attachment-card">
                    <div class="crm-attachment-head">
                        <span class="crm-attachment-icon">
                            <i class="{{ $attachment->iconClass() }}"></i>
                        </span>
                        <div class="crm-attachment-copy">
                            <strong>{{ $attachment->original_name }}</strong>
                            <span>
                                {{ $attachment->extensionLabel() }} · {{ $attachment->formattedSize() }}
                                @if ($attachment->uploadedBy?->name)
                                    · Added by {{ $attachment->uploadedBy->name }}
                                @endif
                                @if ($attachment->created_at)
                                    · {{ $attachment->created_at->format('d M Y H:i') }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="crm-action-row crm-attachment-actions">
                        <a href="{{ route('crm.requests.attachments.open', [$crmRequest, $attachment]) }}"
                            class="btn btn-light crm-btn-light" target="_blank" rel="noopener">
                            <i class="bx bx-link-external"></i> Open
                        </a>
                        <a href="{{ route('crm.requests.attachments.download', [$crmRequest, $attachment]) }}"
                            class="btn btn-light crm-btn-light">
                            <i class="bx bx-download"></i> Download
                        </a>
                        @if ($allowDelete)
                            @include('crm.partials.delete-button', [
                                'action' => route('crm.requests.attachments.destroy', [$crmRequest, $attachment]),
                                'message' => 'Are you sure you want to permanently delete this attachment?',
                                'label' => 'Delete attachment',
                            ])
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>
