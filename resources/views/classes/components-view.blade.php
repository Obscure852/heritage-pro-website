@extends('layouts.master')

@section('title', 'Subject Components | Academic Management')

@section('css')
    <style>
        /* Main Container */
        .settings-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .settings-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .settings-body {
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

        /* Actions Row */
        .actions-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 16px;
        }

        .btn-add-new {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-add-new:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        /* Table Styling */
        .components-table {
            width: 100%;
            border-collapse: collapse;
        }

        .components-table thead th {
            background: #f9fafb;
            padding: 12px 16px;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }

        .components-table tbody td {
            padding: 12px 16px;
            color: #4b5563;
            font-size: 14px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .components-table tbody tr:hover {
            background: #f9fafb;
        }

        .components-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Action Button Styles */
        .table-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 3px;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .table-action-btn.edit {
            background: #fef3c7;
            color: #d97706;
        }

        .table-action-btn.edit:hover {
            background: #d97706;
            color: white;
        }

        .table-action-btn.delete {
            background: #fee2e2;
            color: #dc2626;
        }

        .table-action-btn.delete:hover {
            background: #dc2626;
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            color: #374151;
            margin-bottom: 8px;
        }

        .empty-state p {
            margin: 0;
            font-size: 14px;
        }

        /* Text Muted Style */
        .text-muted-custom {
            color: #9ca3af;
            font-style: italic;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .settings-header {
                padding: 20px;
            }

            .settings-body {
                padding: 16px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('subjects.index') }}">Subjects</a>
        @endslot
        @slot('title')
            {{ $subject->subject->name }} Components
        @endslot
    @endcomponent

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

    <div class="settings-container">
        <div class="settings-header">
            <h3><i class="fas fa-puzzle-piece me-2"></i>{{ $subject->subject->name }} - Components</h3>
            <p>Manage assessment components for this subject</p>
        </div>

        <div class="settings-body">
            <div class="help-text">
                <div class="help-title">About Subject Components</div>
                <div class="help-content">
                    Components are subdivisions of a subject used for assessment purposes.
                    Each component can be graded separately and contributes to the overall subject grade.
                </div>
            </div>

            <div class="actions-row">
                @unless(session('is_past_term'))
                    <a href="{{ route('subject.create-component', ['subjectId' => $subject->id]) }}" class="btn-add-new">
                        <i class="bx bx-plus"></i> New Component
                    </a>
                @endunless
            </div>

            <div class="table-responsive">
                @if ($subject->components && $subject->components->count() > 0)
                    <table class="components-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th style="width: 100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subject->components as $loopIndex => $component)
                                <tr>
                                    <td>{{ $loopIndex + 1 }}</td>
                                    <td><strong>{{ $component->name }}</strong></td>
                                    <td>
                                        @if(!empty($component->description))
                                            {{ $component->description }}
                                        @else
                                            <span class="text-muted-custom">No description</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('subject.edit-component', [$subject->id, $component->id]) }}"
                                                class="table-action-btn edit" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Edit Component">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <a href="{{ route('subject.delete-component', $component->id) }}"
                                                class="table-action-btn delete" data-bs-toggle="tooltip"
                                                data-bs-placement="top" onclick="return confirmDeleteComponent()"
                                                title="Delete Component">
                                                <i class="bx bx-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <i class="bx bx-puzzle"></i>
                        <h5>No Components Found</h5>
                        <p>This subject doesn't have any components yet. Add a new component to get started.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function confirmDeleteComponent() {
        return confirm('Are you sure you want to delete this component? This action cannot be undone.');
    }

    $(document).ready(function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
