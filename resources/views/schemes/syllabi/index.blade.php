@extends('layouts.master')
@section('title')
    Syllabi Management
@endsection
@section('css')
    @include('schemes.partials.schemes-styles')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('schemes.index') }}">Schemes of Work</a>
        @endslot
        @slot('title')
            Syllabi Management
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

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

    <div class="syllabi-container">
        <div class="header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin: 0;">Syllabi Management</h3>
                    <p style="margin: 6px 0 0 0; opacity: .9;">Manage syllabus records, topics and objectives</p>
                </div>
                <div class="col-md-4 text-end">
                </div>
            </div>
        </div>

        <div class="form-container">
            <div class="help-text">
                <div class="help-title">Syllabi Management</div>
                <div class="help-content">
                    Create and manage syllabus records with topics and objectives. Syllabi are linked to schemes of work for curriculum coverage tracking.
                </div>
            </div>

            <div class="text-end mb-3">
                <a href="{{ route('syllabi.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> New Syllabus
                </a>
            </div>

            @if ($syllabi->isEmpty())
                <div class="placeholder-message">
                    <i class="bx bx-book-open"></i>
                    <p>No syllabi have been created yet.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject</th>
                                <th>Grade</th>
                                <th>Level</th>
                                <th>Status</th>
                                <th>Remote</th>
                                <th>Document</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($syllabi as $index => $syllabus)
                                <tr class="animate-in" style="--i: {{ $index }}">
                                    <td>{{ ($syllabi->currentPage() - 1) * $syllabi->perPage() + $index + 1 }}</td>
                                    <td>{{ $syllabus->subject->name ?? '—' }}</td>
                                    <td>{{ $syllabus->grades_label }}</td>
                                    <td>{{ $syllabus->level }}</td>
                                    <td>
                                        @if ($syllabus->is_active)
                                            <span class="badge-active">Active</span>
                                        @else
                                            <span class="badge-inactive">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($syllabus->source_url)
                                            <div style="font-size: 12px;">
                                                <span class="badge-active" style="display: inline-block; margin-bottom: 4px;">
                                                    Source Set
                                                </span><br>
                                                @if ($syllabus->cached_at)
                                                    Cached {{ $syllabus->cached_at->format('d M Y') }}
                                                @else
                                                    Not cached yet
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted" style="font-size: 12px;">No remote source</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($syllabus->document)
                                            <a href="{{ route('documents.show', $syllabus->document) }}" target="_blank">
                                                {{ $syllabus->document->title ?? $syllabus->document->original_name }}
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="action-buttons">
                                            @if ($syllabus->document)
                                                <button class="btn btn-sm btn-outline-secondary btn-view-pdf"
                                                        data-pdf-url="{{ route('syllabi.document.preview', $syllabus) }}"
                                                        data-pdf-title="{{ $syllabus->document->title ?? $syllabus->document->original_name }}"
                                                        title="View PDF"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#pdfViewerModal">
                                                    <i class="fas fa-file-pdf"></i>
                                                </button>
                                            @endif
                                            <a href="{{ route('syllabi.edit', $syllabus) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="{{ auth()->user()?->can('edit-syllabi') ? 'Edit Syllabus' : 'View Syllabus' }}">
                                                <i class="fas {{ auth()->user()?->can('edit-syllabi') ? 'fa-edit' : 'fa-eye' }}"></i>
                                            </a>
                                            @can('edit-syllabi')
                                                <button class="btn btn-sm btn-outline-danger btn-delete-syllabus"
                                                        data-id="{{ $syllabus->id }}"
                                                        title="Delete Syllabus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>

                                        @can('edit-syllabi')
                                            <form id="delete-form-{{ $syllabus->id }}"
                                                  action="{{ route('syllabi.destroy', $syllabus) }}"
                                                  method="POST"
                                                  style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $syllabi->links() }}
                </div>
            @endif
        </div>
    </div>
    {{-- PDF Viewer Modal --}}
    <div class="modal fade" id="pdfViewerModal" tabindex="-1" aria-labelledby="pdfViewerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfViewerModalLabel">
                        <i class="fas fa-file-pdf me-2"></i><span id="pdfViewerTitle">Syllabus</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="pdfViewerIframe" src="" style="width: 100%; height: 70vh; border: none;"
                        title="Syllabus PDF"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // PDF Viewer Modal: set iframe src dynamically
            var pdfModal = document.getElementById('pdfViewerModal');
            if (pdfModal) {
                pdfModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var pdfUrl = button.getAttribute('data-pdf-url');
                    var pdfTitle = button.getAttribute('data-pdf-title');
                    document.getElementById('pdfViewerIframe').src = pdfUrl;
                    document.getElementById('pdfViewerTitle').textContent = pdfTitle;
                });
                pdfModal.addEventListener('hidden.bs.modal', function () {
                    document.getElementById('pdfViewerIframe').src = '';
                });
            }

            document.querySelectorAll('.btn-delete-syllabus').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const syllabusId = this.dataset.id;

                    Swal.fire({
                        title: 'Delete Syllabus?',
                        text: 'This will permanently delete the syllabus along with all its topics and objectives. This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it',
                        cancelButtonText: 'Cancel',
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            document.getElementById('delete-form-' + syllabusId).submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
