@extends('layouts.master')
@section('title') Expiring Documents @endsection
@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .header h3 {
            margin: 0;
            font-weight: 600;
        }

        .header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
        }

        .content-area {
            padding: 24px;
        }

        .doc-table {
            width: 100%;
            border-collapse: collapse;
        }

        .doc-table th {
            background: #f8f9fa;
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }

        .doc-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
            color: #374151;
        }

        .doc-table tr:hover {
            background: #f9fafb;
        }

        .doc-table a {
            color: #3b82f6;
            text-decoration: none;
        }

        .doc-table a:hover {
            text-decoration: underline;
        }

        .days-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .days-red { background: #fee2e2; color: #991b1b; }
        .days-yellow { background: #fef3c7; color: #92400e; }
        .days-green { background: #d1fae5; color: #065f46; }

        .btn-renew {
            padding: 4px 12px;
            border-radius: 3px;
            font-size: 13px;
            background: white;
            border: 1px solid #3b82f6;
            color: #3b82f6;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-renew:hover {
            background: #3b82f6;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
            color: #d1fae5;
        }

        .empty-state h4 {
            color: #6b7280;
            margin-bottom: 8px;
        }

        .modal-header {
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
        }

        .btn-loading.loading .btn-text { display: none; }
        .btn-loading.loading .btn-spinner { display: inline-flex !important; align-items: center; }
        .btn-loading:disabled { opacity: 0.7; cursor: not-allowed; }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="form-container">
                    <div class="header">
                        <h3><i class="fas fa-hourglass-half me-2"></i>Expiring Documents</h3>
                        <p>Documents expiring within the next 30 days</p>
                    </div>

                    <div class="content-area">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($documents->isEmpty())
                            <div class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <h4>No Expiring Documents</h4>
                                <p>No documents are expiring within the next 30 days.</p>
                            </div>
                        @else
                            <table class="doc-table">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Owner</th>
                                        <th>Category</th>
                                        <th>Expiry Date</th>
                                        <th>Days Remaining</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($documents as $document)
                                        @php
                                            $daysRemaining = (int) now()->diffInDays($document->expiry_date, false);
                                            $badgeClass = $daysRemaining <= 7 ? 'days-red' : ($daysRemaining <= 14 ? 'days-yellow' : 'days-green');
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('documents.show', $document) }}">
                                                    {{ $document->title }}
                                                </a>
                                            </td>
                                            <td>{{ $document->owner->full_name ?? 'Unknown' }}</td>
                                            <td>{{ $document->category->name ?? '—' }}</td>
                                            <td>{{ $document->expiry_date->format('d M Y') }}</td>
                                            <td>
                                                <span class="days-badge {{ $badgeClass }}">
                                                    {{ $daysRemaining }} day{{ $daysRemaining !== 1 ? 's' : '' }}
                                                </span>
                                            </td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $document->status)) }}</td>
                                            <td>
                                                <button type="button" class="btn-renew"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#renewModal"
                                                        data-document-id="{{ $document->id }}"
                                                        data-document-title="{{ $document->title }}"
                                                        data-current-expiry="{{ $document->expiry_date->format('Y-m-d') }}">
                                                    <i class="fas fa-redo-alt"></i> Renew
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="mt-3">
                                {{ $documents->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Renew Expiry Modal -->
    <div class="modal fade" id="renewModal" tabindex="-1" aria-labelledby="renewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="renewForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="renewModalLabel">Renew Document Expiry</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Set a new expiry date for <strong id="renewDocTitle"></strong>.</p>
                        <div class="mb-3">
                            <label for="new_expiry_date" class="form-label">New Expiry Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="new_expiry_date" name="new_expiry_date" required
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-save"></i> Renew Expiry</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Saving...
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
        document.getElementById('renewModal').addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const docId = button.getAttribute('data-document-id');
            const docTitle = button.getAttribute('data-document-title');
            const currentExpiry = button.getAttribute('data-current-expiry');

            document.getElementById('renewDocTitle').textContent = docTitle;
            document.getElementById('renewForm').action = '/documents/' + docId + '/renew-expiry';

            // Set min date to tomorrow and default to 1 year from current expiry
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('new_expiry_date').min = tomorrow.toISOString().split('T')[0];

            const defaultDate = new Date(currentExpiry);
            defaultDate.setFullYear(defaultDate.getFullYear() + 1);
            document.getElementById('new_expiry_date').value = defaultDate.toISOString().split('T')[0];
        });

        document.getElementById('renewForm').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"].btn-loading');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }
        });
    </script>
@endsection
