<style>
    .help-text {
        background: #f8f9fa;
        padding: 12px;
        border-left: 4px solid #3b82f6;
        border-radius: 0 3px 3px 0;
        margin-bottom: 20px;
    }

    .help-text .help-title {
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }

    .help-text .help-content {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.4;
    }

    .report-card-item {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 20px;
        transition: all 0.2s ease;
    }

    .report-card-item:hover {
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }

    .term-badge {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 8px 16px;
        border-radius: 3px;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 12px;
    }

    .btn-view {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: white;
        font-weight: 500;
        padding: 8px 16px;
        border-radius: 3px;
        transition: all 0.2s ease;
    }

    .btn-view:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }

    .btn-download {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        color: white;
        font-weight: 500;
        padding: 8px 16px;
        border-radius: 3px;
        transition: all 0.2s ease;
    }

    .btn-download:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        color: white;
    }
</style>

<div class="help-text">
    <div class="help-title">Report Cards</div>
    <div class="help-content">
        Access and download your official report cards for each term. Click "View" to see the report
        in your browser or "Download PDF" to save a copy.
    </div>
</div>

@if($terms->count() > 0)
    <div class="row">
        @foreach($terms as $term)
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="report-card-item h-100">
                    <div class="term-badge">
                        <i class="bx bx-calendar me-1"></i> Term {{ $term->term }}
                    </div>
                    <h5 class="mb-2">{{ $term->year }}</h5>
                    <p class="text-muted small mb-3">
                        Academic Year {{ $term->year }}
                    </p>
                    <div class="d-flex gap-2 flex-wrap">
                        @php
                            $pdfRoute = match(strtolower($schoolType)) {
                                'primary' => 'assessment.primary-pdf-report-card',
                                'senior' => 'assessment.pdf-report-card-senior',
                                default => 'assessment.junior-pdf-report-card'
                            };
                            $htmlRoute = match(strtolower($schoolType)) {
                                'primary' => 'assessment.primary-html-report-card',
                                'senior' => 'assessment.html-report-card-senior',
                                default => 'assessment.html-report-card-junior'
                            };
                        @endphp

                        @if(Route::has($htmlRoute))
                            <a href="{{ route($htmlRoute, $student->id) }}?term_id={{ $term->id }}"
                               class="btn btn-view btn-sm" target="_blank">
                                <i class="bx bx-show me-1"></i> View
                            </a>
                        @endif

                        @if(Route::has($pdfRoute))
                            <a href="{{ route($pdfRoute, $student->id) }}?term_id={{ $term->id }}"
                               class="btn btn-download btn-sm" target="_blank">
                                <i class="bx bx-download me-1"></i> Download PDF
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-5">
        <i class="bx bx-file text-muted display-4"></i>
        <p class="text-muted mt-3">No report cards available yet</p>
        <p class="text-muted small">Report cards will appear here once your academic results are recorded.</p>
    </div>
@endif
