@extends('layouts.master-student-portal')
@section('title')
    Report Cards
@endsection

@section('css')
<style>
    .portal-container {
        background: white;
        border-radius: 3px;
        padding: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .portal-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 28px;
        border-radius: 3px 3px 0 0;
    }

    .portal-body {
        padding: 24px;
    }

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

    .term-list {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
    }

    .term-list-header {
        background: #f8fafc;
        padding: 12px 16px;
        font-weight: 600;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
    }

    .term-list-item {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .term-list-item:last-child {
        border-bottom: none;
    }

    .term-list-item:hover {
        background: #f8fafc;
    }

    .term-list-item.active {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
    }

    .term-list-item.active .term-year {
        color: rgba(255, 255, 255, 0.8);
    }

    .term-label {
        font-weight: 500;
    }

    .term-year {
        font-size: 12px;
        color: #6b7280;
    }

    .preview-container {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
        background: #f1f5f9;
    }

    .preview-header {
        background: #f8fafc;
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .preview-title {
        font-weight: 600;
        color: #374151;
        margin: 0;
    }

    .preview-actions {
        display: flex;
        gap: 8px;
    }

    .preview-frame {
        width: 100%;
        height: 600px;
        border: none;
        background: white;
    }

    .btn-action {
        padding: 6px 12px;
        border-radius: 3px;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-download {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        color: white;
    }

    .btn-download:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: white;
    }

    .btn-open {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: white;
    }

    .btn-open:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white;
    }

    .no-preview {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 400px;
        color: #6b7280;
    }

    .no-preview i {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .portal-header {
            padding: 20px;
        }

        .portal-body {
            padding: 16px;
        }

        .preview-frame {
            height: 400px;
        }
    }
</style>
@endsection

@section('content')
    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="portal-container">
        <div class="portal-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin:0;">
                        <i class="bx bx-file me-2"></i> Report Cards
                    </h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        {{ $student->currentClass?->name ?? 'No Class Assigned' }} -
                        {{ $student->first_name }} {{ $student->last_name }}
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-md-0 mt-3">
                    <span class="badge bg-light text-dark">
                        {{ $reportTerms->count() }} Report{{ $reportTerms->count() != 1 ? 's' : '' }} Available
                    </span>
                </div>
            </div>
        </div>

        <div class="portal-body">
            <div class="help-text">
                <div class="help-title">Report Cards</div>
                <div class="help-content">
                    Select a term from the list to preview your report card. You can also download the PDF or open it in a new tab.
                </div>
            </div>

            @if($reportTerms->count() > 0)
                @php
                    $mostRecentTerm = $reportTerms->first();
                    $initialPdfUrl = route('student.academic.report-card-pdf') . '?term_id=' . $mostRecentTerm->id;
                @endphp

                <div class="row">
                    <!-- Term List Sidebar -->
                    <div class="col-md-3 mb-4 mb-md-0">
                        <div class="term-list">
                            <div class="term-list-header">
                                <i class="bx bx-calendar me-1"></i> Select Term
                            </div>
                            @foreach($reportTerms as $index => $term)
                                <div class="term-list-item {{ $index === 0 ? 'active' : '' }}"
                                     data-term-id="{{ $term->id }}"
                                     data-pdf-url="{{ route('student.academic.report-card-pdf') }}?term_id={{ $term->id }}"
                                     onclick="selectTerm(this)">
                                    <div>
                                        <div class="term-label">Term {{ $term->term }}</div>
                                        <div class="term-year">{{ $term->year }}</div>
                                    </div>
                                    @if($index === 0)
                                        <span class="badge bg-light text-dark" style="font-size: 10px;">Latest</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- PDF Preview -->
                    <div class="col-md-9">
                        <div class="preview-container">
                            <div class="preview-header">
                                <h6 class="preview-title">
                                    <i class="bx bx-file me-1"></i>
                                    <span id="preview-term-label">Term {{ $mostRecentTerm->term }}, {{ $mostRecentTerm->year }}</span>
                                </h6>
                                <div class="preview-actions">
                                    <a href="{{ $initialPdfUrl }}" target="_blank" class="btn-action btn-open" id="btn-open-tab">
                                        <i class="bx bx-link-external"></i> Open
                                    </a>
                                    <a href="{{ $initialPdfUrl }}" download class="btn-action btn-download" id="btn-download">
                                        <i class="bx bx-download"></i> Download
                                    </a>
                                </div>
                            </div>
                            <iframe id="pdf-preview" class="preview-frame" src="{{ $initialPdfUrl }}"></iframe>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bx bx-file text-muted display-4"></i>
                    <p class="text-muted mt-3">No report cards available yet</p>
                    <p class="text-muted small">Report cards will appear here once your academic results are recorded.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
<script>
    function selectTerm(element) {
        // Remove active class from all items
        document.querySelectorAll('.term-list-item').forEach(item => {
            item.classList.remove('active');
        });

        // Add active class to clicked item
        element.classList.add('active');

        // Get PDF URL and term info
        const pdfUrl = element.dataset.pdfUrl;
        const termLabel = element.querySelector('.term-label').textContent;
        const termYear = element.querySelector('.term-year').textContent;

        // Update preview
        document.getElementById('pdf-preview').src = pdfUrl;
        document.getElementById('preview-term-label').textContent = termLabel + ', ' + termYear;
        document.getElementById('btn-open-tab').href = pdfUrl;
        document.getElementById('btn-download').href = pdfUrl;
    }
</script>
@endsection
