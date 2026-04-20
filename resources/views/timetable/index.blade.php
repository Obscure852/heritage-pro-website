@extends('layouts.master')
@section('title')
    Timetable Management
@endsection
@section('css')
    <style>
        .timetable-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .timetable-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .timetable-body {
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

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-draft { background: #fef3c7; color: #92400e; }
        .status-published { background: #d1fae5; color: #065f46; }
        .status-archived { background: #f3f4f6; color: #4b5563; }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            font-size: 16px;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
@endsection
@section('content')
    <div class="timetable-container">
        <div class="timetable-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Timetable Management</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Create and manage school timetables</p>
                </div>
                <div class="col-md-6 text-end">
                    <span class="text-white opacity-75">{{ $timetables->count() }} timetable(s)</span>
                </div>
            </div>
        </div>
        <div class="timetable-body">
            <div class="help-text">
                <div class="help-title">Getting Started</div>
                <div class="help-content">
                    Create a timetable for a term, then use the Grid view to assign subjects and teachers to time slots.
                    Configure period settings (bell schedule, breaks, block allocations) before building the grid.
                </div>
            </div>

            @can('manage-timetable')
                <div class="d-flex justify-content-end mb-3">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTimetableModal">
                        <i class="fas fa-plus me-1"></i> New Timetable
                    </button>
                </div>
            @endcan

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Term</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created</th>
                            <th>Published</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($timetables as $index => $timetable)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $timetable->name }}</strong></td>
                                <td>Term {{ $timetable->term->term ?? '?' }} ({{ $timetable->term->year ?? '?' }})</td>
                                <td>
                                    <span class="status-badge status-{{ $timetable->status }}">{{ $timetable->status }}</span>
                                </td>
                                <td>{{ $timetable->creator->firstname ?? '' }} {{ $timetable->creator->lastname ?? '' }}</td>
                                <td>{{ $timetable->created_at->format('M d, Y') }}</td>
                                <td>
                                    @if ($timetable->isPublished() && $timetable->published_at)
                                        {{ $timetable->published_at->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        @can('manage-timetable')
                                            @if ($timetable->isDraft())
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-success publish-btn"
                                                    data-id="{{ $timetable->id }}"
                                                    data-name="{{ $timetable->name }}"
                                                    title="Publish">
                                                    <i class="bx bx-upload"></i>
                                                </button>
                                            @elseif ($timetable->isPublished())
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-warning unpublish-btn"
                                                    data-id="{{ $timetable->id }}"
                                                    data-name="{{ $timetable->name }}"
                                                    title="Unpublish (revert to draft)">
                                                    <i class="bx bx-download"></i>
                                                </button>
                                            @endif
                                            <a href="{{ route('timetable.publishing.versions', $timetable) }}"
                                                class="btn btn-sm btn-outline-info"
                                                title="Version History">
                                                <i class="bx bx-history"></i>
                                            </a>
                                            <a href="{{ route('timetable.slots.grid', $timetable) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Manage Grid">
                                                <i class="bx bx-grid-alt"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger delete-timetable-btn"
                                                data-id="{{ $timetable->id }}"
                                                data-name="{{ $timetable->name }}"
                                                title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        @else
                                            <a href="{{ route('timetable.slots.grid', $timetable) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="View Grid">
                                                <i class="bx bx-grid-alt"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="bx bx-calendar-alt" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0" style="font-size: 15px;">No timetables yet. Create one to get started.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Timetable Modal -->
    <div class="modal fade" id="createTimetableModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Timetable</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createTimetableForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="timetableName" class="form-label">Timetable Name</label>
                            <input type="text" class="form-control" id="timetableName" name="name"
                                placeholder="e.g. Term 1 2026 Timetable" required>
                        </div>
                        <div class="mb-3">
                            <label for="timetableTermId" class="form-label">Term</label>
                            <select class="form-select" id="timetableTermId" name="term_id" required>
                                <option value="">Select a term...</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" {{ $term->id == session('selected_term_id') ? 'selected' : '' }}>
                                        Term {{ $term->term }} ({{ $term->year }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-plus me-1"></i> Create</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Creating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.getElementById('createTimetableForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const submitBtn = form.querySelector('button[type="submit"].btn-loading');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }

            const formData = new FormData(form);

            fetch('{{ route("timetable.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name: formData.get('name'),
                    term_id: formData.get('term_id'),
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.data) {
                    window.location.reload();
                } else {
                    Swal.fire('Error', data.message || 'Failed to create timetable', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                }
            });
        });

        // Publish timetable
        document.querySelectorAll('.publish-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;

                Swal.fire({
                    title: 'Publish Timetable?',
                    html: `Publishing "<strong>${name}</strong>" will make it the active schedule visible to all users.<br><br>Any currently published timetable for this term will be archived.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Yes, publish it',
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/timetable/publishing/${id}/publish`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        })
                        .then(r => {
                            if (!r.ok) return r.json().then(d => Promise.reject(d));
                            return r.json();
                        })
                        .then(data => {
                            Swal.fire('Published!', data.message, 'success')
                                .then(() => window.location.reload());
                        })
                        .catch(err => {
                            Swal.fire('Error', err.message || 'Failed to publish timetable.', 'error');
                        });
                    }
                });
            });
        });

        // Unpublish timetable
        document.querySelectorAll('.unpublish-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;

                Swal.fire({
                    title: 'Unpublish Timetable?',
                    html: `Unpublishing "<strong>${name}</strong>" will revert it to draft status.<br><br>It will no longer be visible to teachers and HODs.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    confirmButtonText: 'Yes, unpublish it',
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/timetable/publishing/${id}/unpublish`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        })
                        .then(r => {
                            if (!r.ok) return r.json().then(d => Promise.reject(d));
                            return r.json();
                        })
                        .then(data => {
                            Swal.fire('Unpublished!', data.message, 'success')
                                .then(() => window.location.reload());
                        })
                        .catch(err => {
                            Swal.fire('Error', err.message || 'Failed to unpublish timetable.', 'error');
                        });
                    }
                });
            });
        });

        document.querySelectorAll('.delete-timetable-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;

                Swal.fire({
                    title: 'Delete Timetable?',
                    text: `Are you sure you want to delete "${name}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete it',
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/timetable/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire('Deleted!', data.message, 'success')
                                .then(() => window.location.reload());
                        })
                        .catch(() => {
                            Swal.fire('Error', 'Failed to delete timetable.', 'error');
                        });
                    }
                });
            });
        });
    </script>
@endsection
