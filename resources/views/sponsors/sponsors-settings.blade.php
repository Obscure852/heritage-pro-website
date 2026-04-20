@extends('layouts.master')
@section('title')
    Sponsors Settings
@endsection
@section('css')
    <style>
        .sponsors-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
        }

        .sponsors-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .sponsors-body {
            padding: 24px;
        }

        /* Card Border */
        .card {
            border: none;
            box-shadow: none;
        }

        /* Tab Styling */
        .nav-tabs-custom {
            border-bottom: 1px solid #e5e7eb;
            gap: 8px;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            background: transparent;
            color: #6b7280;
            font-weight: 500;
            padding: 12px 20px;
            transition: all 0.2s ease;
            border-radius: 0;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #4e73df;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            border-bottom-color: #4e73df;
            background: transparent;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #4e73df;
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
            line-height: 1.5;
            margin: 0;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select,
        textarea.form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus,
        textarea.form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-light {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-light:hover {
            background: #e9ecef;
            color: #495057;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            color: white;
        }

        /* Table Styling */
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

        /* Action Buttons (Table) */
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

        .modal-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            border-radius: 3px 3px 0 0;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-title {
            font-weight: 600;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            border-top: 1px solid #f3f4f6;
            padding: 16px 24px;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('sponsors.index') }}">Sponsors</a>
        @endslot
        @slot('title')
            Settings
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div id="messageContainer"></div>

    <div class="sponsors-container">
        <div class="sponsors-header">
            <h4 class="mb-1 text-white"><i class="fas fa-cog me-2"></i>Sponsors Settings</h4>
            <p class="mb-0 opacity-75">Manage filters and general settings for parents/sponsors</p>
        </div>
        <div class="sponsors-body">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#filters" role="tab">
                                <i class="fas fa-filter me-2 text-muted"></i>Filters
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#settings" role="tab">
                                <i class="fas fa-sliders-h me-2 text-muted"></i>General Settings
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        <div class="tab-pane active" id="filters" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Custom Filters</div>
                                <div class="help-content">
                                    Create custom filters to organize and categorize parents/sponsors (e.g., "PTA Committee", "Board Members").
                                </div>
                            </div>

                            <form action="{{ route('sponsors.store-filter') }}" method="POST" class="mb-4">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="name" placeholder="Enter filter name (e.g., PTA Committee)" required>
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-plus"></i> Add Filter
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;">#</th>
                                            <th>Filter Name</th>
                                            <th style="width: 100px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (!empty($filters))
                                            @foreach ($filters as $index => $filter)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $filter->name ?? '' }}</td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <button type="button" class="btn btn-outline-info edit-filter"
                                                                data-bs-toggle="modal" data-bs-target="#editFilterModal"
                                                                data-id="{{ $filter->id }}" data-name="{{ $filter->name }}">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <form method="POST" action="{{ route('sponsors.destroy-filter', $filter->id) }}" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-outline-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this filter?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted">
                                                    <i class="fas fa-filter" style="font-size: 32px; opacity: 0.3;"></i>
                                                    <p class="mt-2 mb-0">No filters created yet</p>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane" id="settings" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">General Settings</div>
                                <div class="help-content">
                                    Configure general settings for the sponsors module.
                                </div>
                            </div>
                            <p class="text-muted text-center py-5">
                                <i class="fas fa-cog" style="font-size: 48px; opacity: 0.2;"></i><br>
                                No general settings available at this time.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Filter Modal -->
    <div class="modal fade" id="editFilterModal" tabindex="-1" aria-labelledby="editFilterModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFilterModalLabel">Edit Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editFilterForm">
                        <div class="mb-3">
                            <label for="editFilterName" class="form-label">Filter Name</label>
                            <input type="text" class="form-control" id="editFilterName" name="filterName" required>
                        </div>
                        <input type="hidden" id="editFilterId" name="filterId">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Message display function
            function displayMessage(message, type = 'success') {
                const messageContainer = document.getElementById('messageContainer');
                messageContainer.innerHTML = `
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="alert alert-${type} alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                            <i class="mdi mdi-check-all label-icon"></i>
                            <strong>${message}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                </div>`;
            }

            const updateMessage = localStorage.getItem('updateMessage');
            if (updateMessage) {
                displayMessage(updateMessage);
                localStorage.removeItem('updateMessage');
            }

            // Edit Filter Modal
            const editFilterLinks = document.querySelectorAll('.edit-filter');
            editFilterLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    const filterId = this.getAttribute('data-id');
                    const filterName = this.getAttribute('data-name');

                    document.getElementById('editFilterId').value = filterId;
                    document.getElementById('editFilterName').value = filterName;
                });
            });

            document.getElementById('editFilterForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const filterId = document.getElementById('editFilterId').value;
                const filterName = document.getElementById('editFilterName').value;
                const updateFilterUrl =
                    `{{ route('sponsors.update-filter', ['id' => ':tempFilterId']) }}`.replace(
                        ':tempFilterId', filterId);

                fetch(updateFilterUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            name: filterName
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            localStorage.setItem('updateMessage', data.message);
                            var editFilterModal = bootstrap.Modal.getInstance(document.getElementById(
                                'editFilterModal'));
                            editFilterModal.hide();
                            location.reload();
                        } else {
                            alert('Error updating filter');
                        }
                    })
                    .catch(error => {
                        console.error('Error updating filter:', error);
                        alert('Error updating filter');
                    });
            });

            // Tab persistence
            const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
            tabLinks.forEach(tabLink => {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    const activeTabHref = event.target.getAttribute('href');
                    localStorage.setItem('sponsorSettingsActiveTab', activeTabHref);
                });
            });

            const activeTab = localStorage.getItem('sponsorSettingsActiveTab');
            if (activeTab) {
                const tabTriggerEl = document.querySelector(`.nav-link[href="${activeTab}"]`);
                if (tabTriggerEl) {
                    const tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                }
            }
        });
    </script>
@endsection
