@extends('layouts.master')
@section('title')
    Trash - Documents
@endsection
@section('css')
    <style>
        .documents-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .documents-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .documents-body {
            padding: 24px;
        }

        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .table th {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
            padding: 12px;
        }

        .table td {
            padding: 12px;
            vertical-align: middle;
            font-size: 14px;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
        }

        .table tbody tr:hover {
            background: #f9fafb;
        }

        .doc-title {
            font-weight: 500;
            color: #1f2937;
        }

        .doc-title i {
            color: #6b7280;
            margin-right: 8px;
            width: 16px;
            text-align: center;
        }

        .days-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .days-green { background: #d1fae5; color: #065f46; }
        .days-yellow { background: #fef3c7; color: #92400e; }
        .days-red { background: #fee2e2; color: #991b1b; }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state .empty-icon {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state .empty-title {
            font-size: 18px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .empty-state .empty-text {
            font-size: 14px;
            color: #9ca3af;
            margin-bottom: 20px;
        }

        .pagination-wrapper {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .documents-header {
                padding: 20px;
            }

            .documents-body {
                padding: 16px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('documents.index') }}">Documents</a>
        @endslot
        @slot('title')
            Trash
        @endslot
    @endcomponent

    <div class="documents-container">
        <div class="documents-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;"><i class="fas fa-trash-alt me-2"></i>Trash</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Documents are permanently deleted after 30 days</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center justify-content-end">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $documents->count() }}</h4>
                                <small class="opacity-75">In Trash</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="documents-body">

        <div class="help-text">
            <div class="help-title">Trash</div>
            <div class="help-content">
                Deleted documents are kept here for 30 days before being permanently removed. You can restore documents back to your library at any time.
            </div>
        </div>

        @if($documents->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <div class="empty-title">Trash is empty</div>
                <div class="empty-text">Deleted documents will appear here for 30 days before being permanently removed.</div>
            </div>
        @else
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Owner</th>
                            <th>Deleted</th>
                            <th>Days Remaining</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                            @php
                                $daysRemaining = max(0, 30 - $doc->deleted_at->diffInDays(now()));
                                $dayClass = $daysRemaining > 14 ? 'days-green' : ($daysRemaining >= 7 ? 'days-yellow' : 'days-red');
                            @endphp
                            <tr>
                                <td>
                                    <span class="doc-title">
                                        <i class="fas fa-file"></i>
                                        {{ $doc->title }}
                                    </span>
                                </td>
                                <td>{{ $doc->owner->full_name ?? 'Unknown' }}</td>
                                <td>{{ $doc->deleted_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <span class="days-badge {{ $dayClass }}">
                                        {{ $daysRemaining }} {{ Str::plural('day', $daysRemaining) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('documents.restore', $doc->id) }}"
                                          method="POST"
                                          class="d-inline restore-form">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-undo"></i> Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($documents->hasPages())
                <div class="pagination-wrapper">
                    {{ $documents->links() }}
                </div>
            @endif
        @endif
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Confirm before restore
            $('.restore-form').on('submit', function(e) {
                e.preventDefault();
                var form = this;
                Swal.fire({
                    title: 'Restore this document?',
                    text: 'The document will be moved back to your documents.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, restore it'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>

    @if(session('success'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ session("success") }}',
                showConfirmButton: false,
                timer: 3000
            });
        </script>
    @endif
@endsection
