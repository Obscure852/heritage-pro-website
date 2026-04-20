@extends('layouts.master')
@section('title') Retention Policies @endsection
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .btn-add {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.4);
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-add:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .policy-table {
            width: 100%;
            border-collapse: collapse;
        }

        .policy-table th {
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

        .policy-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
            color: #374151;
        }

        .policy-table tr:hover {
            background: #f9fafb;
        }

        .badge-action {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-archive { background: #fef3c7; color: #92400e; }
        .badge-delete { background: #fee2e2; color: #991b1b; }
        .badge-notify { background: #dbeafe; color: #1e40af; }

        .badge-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-active { background: #d1fae5; color: #065f46; }
        .badge-inactive { background: #f3f4f6; color: #6b7280; }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .action-btns a,
        .action-btns button {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 13px;
            text-decoration: none;
            border: 1px solid #d1d5db;
            background: white;
            color: #374151;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-btns a:hover { background: #f3f4f6; }
        .action-btns .btn-delete { color: #dc2626; border-color: #fca5a5; }
        .action-btns .btn-delete:hover { background: #fef2f2; }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
        }

        .empty-state h4 {
            color: #6b7280;
            margin-bottom: 8px;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="form-container">
                    <div class="header">
                        <div>
                            <h3><i class="fas fa-clock me-2"></i>Retention Policies</h3>
                            <p>Manage automated document retention and lifecycle rules</p>
                        </div>
                        <a href="{{ route('documents.retention-policies.create') }}" class="btn-add">
                            <i class="fas fa-plus"></i> New Policy
                        </a>
                    </div>

                    <div class="content-area">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($policies->isEmpty())
                            <div class="empty-state">
                                <i class="fas fa-clock"></i>
                                <h4>No Retention Policies</h4>
                                <p>Create a retention policy to automate document lifecycle management.</p>
                            </div>
                        @else
                            <table class="policy-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Retention</th>
                                        <th>Grace Period</th>
                                        <th>Action</th>
                                        <th>Status</th>
                                        <th>Last Run</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($policies as $policy)
                                        <tr>
                                            <td>
                                                <strong>{{ $policy->name }}</strong>
                                                @if ($policy->description)
                                                    <br><small class="text-muted">{{ Str::limit($policy->description, 60) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if (!empty($policy->conditions['category_id']))
                                                    {{ \App\Models\DocumentCategory::find($policy->conditions['category_id'])?->name ?? 'Unknown' }}
                                                @else
                                                    <span class="text-muted">All Categories</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($policy->retention_days) }} days</td>
                                            <td>{{ $policy->grace_period_days }} days</td>
                                            <td>
                                                @switch($policy->action)
                                                    @case('archive')
                                                        <span class="badge-action badge-archive">Archive</span>
                                                        @break
                                                    @case('delete')
                                                        <span class="badge-action badge-delete">Delete</span>
                                                        @break
                                                    @case('notify_owner')
                                                        <span class="badge-action badge-notify">Notify Owner</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                <span class="badge-status {{ $policy->is_active ? 'badge-active' : 'badge-inactive' }}">
                                                    {{ $policy->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($policy->last_run_at)
                                                    <span title="{{ $policy->last_run_at->format('d M Y H:i') }}">
                                                        {{ $policy->last_run_at->diffForHumans() }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Never</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="action-btns">
                                                    <a href="{{ route('documents.retention-policies.edit', $policy) }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('documents.retention-policies.destroy', $policy) }}" method="POST" class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-delete" title="Delete">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Delete Policy?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Yes, delete it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
