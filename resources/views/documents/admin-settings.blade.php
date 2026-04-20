@extends('layouts.master')
@section('title')
    Document Administration
@endsection
@section('css')
    <style>
        .admin-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .admin-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .admin-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .admin-header p {
            margin: 4px 0 0;
            opacity: 0.85;
            font-size: 14px;
        }

        .admin-body {
            padding: 24px;
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

        .tab-content {
            padding-top: 24px;
        }

        /* Card border reset */
        .card {
            border: none;
            box-shadow: none;
        }

        /* Common table styles */
        .table th {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
        }

        .table td {
            vertical-align: middle;
            font-size: 14px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        /* Common button styles */
        .btn {
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
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

        .btn-sm {
            padding: 5px 10px;
            font-size: 13px;
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

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .action-btns .btn {
            padding: 4px 8px;
            font-size: 12px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 8px;
        }

        /* Categories & Tags badges */
        .color-swatch {
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
            margin-right: 4px;
        }

        .child-category {
            color: #6b7280;
        }

        .badge-active { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .badge-inactive { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .badge-yes { background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .badge-no { background: #f3f4f6; color: #6b7280; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .badge-official { background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .badge-user { background: #f3f4f6; color: #6b7280; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }

        .usage-badge {
            background: #f0fdf4;
            color: #166534;
            padding: 3px 8px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .usage-badge.zero {
            background: #f3f4f6;
            color: #9ca3af;
        }

        .btn-warning-custom {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-warning-custom:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: white;
        }

        .merge-arrow {
            font-size: 24px;
            color: #6b7280;
            text-align: center;
            padding: 10px 0;
        }

        /* Retention tab */
        .policy-table th {
            background: #f8f9fa;
        }

        .badge-action { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .badge-archive { background: #fef3c7; color: #92400e; }
        .badge-delete { background: #fee2e2; color: #991b1b; }
        .badge-notify { background: #dbeafe; color: #1e40af; }
        .badge-status { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }

        /* Quotas tab */
        .search-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-bar .form-control {
            max-width: 300px;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
        }

        .sort-link {
            color: #374151;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .sort-link:hover {
            color: #3b82f6;
        }

        .sort-link .sort-icon {
            font-size: 10px;
            opacity: 0.4;
        }

        .sort-link.active .sort-icon {
            opacity: 1;
            color: #3b82f6;
        }

        .progress {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            min-width: 100px;
        }

        .progress-bar {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }

        .user-info { display: flex; flex-direction: column; }
        .user-name { font-weight: 500; color: #1f2937; }
        .user-dept { font-size: 12px; color: #9ca3af; }

        .edit-form {
            display: none;
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            margin-top: 6px;
        }

        .edit-form.show { display: block; }

        .edit-form .form-group {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .edit-form .form-control {
            width: 120px;
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 13px;
        }

        .bulk-bar {
            display: none;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 10px 16px;
            margin-bottom: 16px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        .bulk-bar.show { display: flex; }

        .bulk-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        /* Audit tab */
        .filter-bar {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 3px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
        }

        .filter-bar .form-label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .filter-bar .form-control,
        .filter-bar .form-select {
            font-size: 13px;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        .btn-filter {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 7px 16px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-filter:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }

        .btn-clear {
            background: white;
            color: #6b7280;
            border: 1px solid #d1d5db;
            padding: 7px 16px;
            border-radius: 3px;
            font-size: 13px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-clear:hover {
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
        }

        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 12px;
        }

        .filter-chip {
            background: #e0e7ff;
            color: #3730a3;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .filter-chip a {
            color: #3730a3;
            font-weight: 700;
            text-decoration: none;
            margin-left: 2px;
        }

        .filter-chip a:hover { color: #1e1b4b; }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
            color: white;
            flex-shrink: 0;
        }

        .audit-table th {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
            padding: 12px;
        }

        .audit-table td {
            padding: 12px;
            vertical-align: middle;
            font-size: 13px;
            border-bottom: 1px solid #f3f4f6;
        }

        .btn-detail-toggle {
            background: none;
            border: none;
            color: #9ca3af;
            padding: 4px 8px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .btn-detail-toggle:hover { color: #3b82f6; }
        .btn-detail-toggle.expanded { color: #3b82f6; }

        .audit-detail-row { display: none; }

        .audit-detail-content {
            background: #f8f9fa;
            padding: 16px 20px;
            border-left: 3px solid #3b82f6;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
        }

        .detail-item { font-size: 12px; }
        .detail-item .detail-label { font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px; }
        .detail-item .detail-value { color: #374151; word-break: break-all; }

        .metadata-list { margin-top: 10px; }
        .metadata-list dt { font-size: 12px; font-weight: 600; color: #6b7280; }
        .metadata-list dd { font-size: 12px; color: #374151; margin-bottom: 6px; }

        .doc-link { color: #3b82f6; text-decoration: none; font-weight: 500; }
        .doc-link:hover { color: #1d4ed8; text-decoration: underline; }

        .audit-date { font-size: 13px; color: #374151; }
        .audit-date-relative { font-size: 11px; color: #9ca3af; }

        .pagination-wrapper { padding: 16px 0 0; }

        /* General Settings tab */
        .settings-section {
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid #f3f4f6;
        }

        .settings-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i { color: #4e73df; }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title { font-weight: 600; color: #374151; margin-bottom: 4px; }
        .help-text .help-content { color: #6b7280; font-size: 13px; line-height: 1.4; }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 16px;
            margin-top: 16px;
        }

        .extension-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }

        @media (max-width: 768px) {
            .extension-grid { grid-template-columns: repeat(2, 1fr); }
        }

        .extension-grid label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #374151;
            padding: 6px 8px;
            background: #f9fafb;
            border-radius: 3px;
            cursor: pointer;
            margin-bottom: 0;
        }

        .extension-grid label:hover { background: #f3f4f6; }

        .form-switch .form-check-input { width: 3em; height: 1.5em; }
        .form-switch .form-check-label { padding-top: 2px; }

        @media (max-width: 768px) {
            .admin-body { padding: 16px; }
            .search-bar { flex-direction: column; align-items: stretch; }
            .search-bar .form-control { max-width: 100%; }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('documents.index') }}">Documents</a>
        @endslot
        @slot('title')
            Documents Settings
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="admin-container">
            {{-- Gradient Header --}}
            <div class="admin-header">
                <h4><i class="fas fa-cogs me-2"></i>Documents Settings</h4>
                <p>Manage categories, tags, retention policies, quotas, audit logs, and system settings</p>
            </div>

            <div class="admin-body">
                {{-- Tab Navigation --}}
                <ul class="nav nav-tabs nav-tabs-custom mb-0" id="adminTabs" role="tablist">
                    @can('manage-document-categories')
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories"
                                    type="button" role="tab" aria-controls="categories" aria-selected="false">
                                <i class="fas fa-folder-tree me-1"></i> Categories
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tags-tab" data-bs-toggle="tab" data-bs-target="#tags"
                                    type="button" role="tab" aria-controls="tags" aria-selected="false">
                                <i class="fas fa-tags me-1"></i> Tags
                            </button>
                        </li>
                    @endcan

                    @if(\App\Policies\DocumentPolicy::isAdmin(auth()->user()))
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="retention-tab" data-bs-toggle="tab" data-bs-target="#retention"
                                    type="button" role="tab" aria-controls="retention" aria-selected="false">
                                <i class="fas fa-clock me-1"></i> Retention Policies
                            </button>
                        </li>
                    @endif

                    @can('manage-document-quotas')
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="quotas-tab" data-bs-toggle="tab" data-bs-target="#quotas"
                                    type="button" role="tab" aria-controls="quotas" aria-selected="false">
                                <i class="fas fa-hdd me-1"></i> Storage Quotas
                            </button>
                        </li>
                    @endcan

                    @can('view-document-audit')
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit"
                                    type="button" role="tab" aria-controls="audit" aria-selected="false">
                                <i class="fas fa-history me-1"></i> Audit Logs
                            </button>
                        </li>
                    @endcan

                    @can('manage-document-settings')
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="general-tab" data-bs-toggle="tab" data-bs-target="#general"
                                    type="button" role="tab" aria-controls="general" aria-selected="false">
                                <i class="fas fa-cog me-1"></i> General Settings
                            </button>
                        </li>
                    @endcan
                </ul>

                {{-- Tab Content --}}
                <div class="tab-content" id="adminTabsContent">
                    @can('manage-document-categories')
                        <div class="tab-pane fade" id="categories" role="tabpanel" aria-labelledby="categories-tab">
                            @include('documents.admin-settings._categories')
                        </div>
                        <div class="tab-pane fade" id="tags" role="tabpanel" aria-labelledby="tags-tab">
                            @include('documents.admin-settings._tags')
                        </div>
                    @endcan

                    @if(\App\Policies\DocumentPolicy::isAdmin(auth()->user()))
                        <div class="tab-pane fade" id="retention" role="tabpanel" aria-labelledby="retention-tab">
                            @include('documents.admin-settings._retention-policies')
                        </div>
                    @endif

                    @can('manage-document-quotas')
                        <div class="tab-pane fade" id="quotas" role="tabpanel" aria-labelledby="quotas-tab">
                            @include('documents.admin-settings._storage-quotas')
                        </div>
                    @endcan

                    @can('view-document-audit')
                        <div class="tab-pane fade" id="audit" role="tabpanel" aria-labelledby="audit-tab">
                            @include('documents.admin-settings._audit-logs')
                        </div>
                    @endcan

                    @can('manage-document-settings')
                        <div class="tab-pane fade" id="general" role="tabpanel" aria-labelledby="general-tab">
                            @include('documents.admin-settings._general-settings')
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(function() {
        // ===================== Tab Persistence =====================
        var urlParams = new URLSearchParams(window.location.search);
        var urlTab = urlParams.get('tab');
        var storageKey = 'doc-admin-active-tab';

        // Determine which tab to activate
        var targetTab = urlTab || localStorage.getItem(storageKey) || null;

        // Activate the target tab (or first available)
        var activated = false;
        if (targetTab) {
            var tabBtn = document.getElementById(targetTab + '-tab');
            if (tabBtn) {
                new bootstrap.Tab(tabBtn).show();
                activated = true;
            }
        }

        if (!activated) {
            var firstTab = document.querySelector('#adminTabs .nav-link');
            if (firstTab) {
                new bootstrap.Tab(firstTab).show();
            }
        }

        // Save tab selection to localStorage on manual click
        document.querySelectorAll('#adminTabs .nav-link').forEach(function(tab) {
            tab.addEventListener('shown.bs.tab', function(e) {
                var tabId = e.target.getAttribute('data-bs-target').replace('#', '');
                localStorage.setItem(storageKey, tabId);
            });
        });

        // ===================== Categories JS =====================
        @can('manage-document-categories')
        var categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
        var editingCategoryId = null;

        // Color picker sync
        document.getElementById('catColorPicker').addEventListener('input', function() {
            document.getElementById('catColor').value = this.value;
        });
        document.getElementById('catColor').addEventListener('input', function() {
            if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                document.getElementById('catColorPicker').value = this.value;
            }
        });

        document.getElementById('btnNewCategory').addEventListener('click', function() {
            openCreateCategoryModal();
        });

        window.openCreateCategoryModal = function() {
            editingCategoryId = null;
            document.getElementById('categoryModalLabel').textContent = 'New Category';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryId').value = '';
            document.getElementById('catIsActive').checked = true;
            document.getElementById('catColorPicker').value = '#3b82f6';
            document.querySelectorAll('#catParent option').forEach(function(opt) { opt.style.display = ''; });
            categoryModal.show();
        };

        window.openEditCategoryModal = function(id, category) {
            editingCategoryId = id;
            document.getElementById('categoryModalLabel').textContent = 'Edit Category';
            document.getElementById('categoryId').value = id;
            document.getElementById('catName').value = category.name || '';
            document.getElementById('catDescription').value = category.description || '';
            document.getElementById('catParent').value = category.parent_id || '';
            document.getElementById('catIcon').value = category.icon || '';
            document.getElementById('catColor').value = category.color || '';
            document.getElementById('catColorPicker').value = category.color || '#3b82f6';
            document.getElementById('catSortOrder').value = category.sort_order || 0;
            document.getElementById('catRetention').value = category.retention_days || '';
            document.getElementById('catRequiresApproval').checked = !!category.requires_approval;
            document.getElementById('catIsActive').checked = category.is_active !== false;
            document.querySelectorAll('#catParent option').forEach(function(opt) {
                opt.style.display = (parseInt(opt.value) === id) ? 'none' : '';
            });
            categoryModal.show();
        };

        document.getElementById('categoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            var submitBtn = form.querySelector('button[type="submit"].btn-loading');
            if (submitBtn) { submitBtn.classList.add('loading'); submitBtn.disabled = true; }

            var formData = {
                name: document.getElementById('catName').value,
                description: document.getElementById('catDescription').value || null,
                parent_id: document.getElementById('catParent').value || null,
                icon: document.getElementById('catIcon').value || null,
                color: document.getElementById('catColor').value || null,
                sort_order: parseInt(document.getElementById('catSortOrder').value) || 0,
                retention_days: document.getElementById('catRetention').value ? parseInt(document.getElementById('catRetention').value) : null,
                requires_approval: document.getElementById('catRequiresApproval').checked ? 1 : 0,
                is_active: document.getElementById('catIsActive').checked ? 1 : 0,
                _token: '{{ csrf_token() }}',
            };

            var isEdit = !!editingCategoryId;
            var url = isEdit ? '{{ url("documents/categories") }}/' + editingCategoryId : '{{ route("documents.categories.store") }}';
            if (isEdit) { formData._method = 'PUT'; }

            $.ajax({
                url: url, type: 'POST', data: formData,
                success: function(response) {
                    categoryModal.hide();
                    Swal.fire({ icon: 'success', title: 'Success', text: response.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 }).then(function() { window.location.reload(); });
                },
                error: function(xhr) {
                    if (submitBtn) { submitBtn.classList.remove('loading'); submitBtn.disabled = false; }
                    var errors = xhr.responseJSON?.errors;
                    var errorText = errors ? Object.values(errors).flat().join('\n') : (xhr.responseJSON?.message || 'An error occurred.');
                    Swal.fire({ icon: 'error', title: 'Error', text: errorText });
                }
            });
        });

        window.deleteCategory = function(id, name, docCount, childCount) {
            if (docCount > 0) { Swal.fire({ icon: 'warning', title: 'Cannot Delete', text: 'This category has ' + docCount + ' document(s). Remove or reassign them first.' }); return; }
            if (childCount > 0) { Swal.fire({ icon: 'warning', title: 'Cannot Delete', text: 'This category has ' + childCount + ' child category(ies). Remove them first.' }); return; }

            Swal.fire({
                title: 'Delete "' + name + '"?', text: 'This action cannot be undone.', icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete it', cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("documents/categories") }}/' + id, type: 'POST', data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                        success: function(response) { Swal.fire({ icon: 'success', title: 'Deleted', text: response.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 }).then(function() { window.location.reload(); }); },
                        error: function(xhr) { Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to delete category.' }); }
                    });
                }
            });
        };

        // ===================== Tags JS =====================
        var tagModal = new bootstrap.Modal(document.getElementById('tagModal'));
        var mergeModal = new bootstrap.Modal(document.getElementById('mergeModal'));
        var editingTagId = null;

        document.getElementById('tagColorPicker').addEventListener('input', function() {
            document.getElementById('tagColor').value = this.value;
        });
        document.getElementById('tagColor').addEventListener('input', function() {
            if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                document.getElementById('tagColorPicker').value = this.value;
            }
        });

        document.getElementById('btnNewTag').addEventListener('click', function() { openCreateTagModal(); });

        var btnMerge = document.getElementById('btnMergeTags');
        if (btnMerge) {
            btnMerge.addEventListener('click', function() { document.getElementById('mergeForm').reset(); mergeModal.show(); });
        }

        window.openCreateTagModal = function() {
            editingTagId = null;
            document.getElementById('tagModalLabel').textContent = 'New Tag';
            document.getElementById('tagForm').reset();
            document.getElementById('tagId').value = '';
            document.getElementById('tagIsOfficial').checked = true;
            document.getElementById('tagColorPicker').value = '#3b82f6';
            tagModal.show();
        };

        window.openEditTagModal = function(id, tag) {
            editingTagId = id;
            document.getElementById('tagModalLabel').textContent = 'Edit Tag';
            document.getElementById('tagId').value = id;
            document.getElementById('tagName').value = tag.name || '';
            document.getElementById('tagDescription').value = tag.description || '';
            document.getElementById('tagColor').value = tag.color || '';
            document.getElementById('tagColorPicker').value = tag.color || '#3b82f6';
            document.getElementById('tagIsOfficial').checked = !!tag.is_official;
            tagModal.show();
        };

        document.getElementById('tagForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            var submitBtn = form.querySelector('button[type="submit"].btn-loading');
            if (submitBtn) { submitBtn.classList.add('loading'); submitBtn.disabled = true; }

            var formData = {
                name: document.getElementById('tagName').value,
                description: document.getElementById('tagDescription').value || null,
                color: document.getElementById('tagColor').value || null,
                is_official: document.getElementById('tagIsOfficial').checked ? 1 : 0,
                _token: '{{ csrf_token() }}',
            };

            var isEdit = !!editingTagId;
            var url = isEdit ? '{{ url("documents/tags") }}/' + editingTagId : '{{ route("documents.tags.store") }}';
            if (isEdit) { formData._method = 'PUT'; }

            $.ajax({
                url: url, type: 'POST', data: formData,
                success: function(response) {
                    tagModal.hide();
                    Swal.fire({ icon: 'success', title: 'Success', text: response.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 }).then(function() { window.location.reload(); });
                },
                error: function(xhr) {
                    if (submitBtn) { submitBtn.classList.remove('loading'); submitBtn.disabled = false; }
                    var errors = xhr.responseJSON?.errors;
                    var errorText = errors ? Object.values(errors).flat().join('\n') : (xhr.responseJSON?.message || 'An error occurred.');
                    Swal.fire({ icon: 'error', title: 'Error', text: errorText });
                }
            });
        });

        document.getElementById('mergeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var sourceId = document.getElementById('mergeSource').value;
            var targetId = document.getElementById('mergeTarget').value;
            if (!sourceId || !targetId) { Swal.fire({ icon: 'warning', title: 'Select Both Tags', text: 'Please select both source and target tags.' }); return; }
            if (sourceId === targetId) { Swal.fire({ icon: 'warning', title: 'Invalid Selection', text: 'Source and target must be different tags.' }); return; }

            var sourceName = document.querySelector('#mergeSource option[value="' + sourceId + '"]').textContent.split(' (')[0];
            var targetName = document.querySelector('#mergeTarget option[value="' + targetId + '"]').textContent.split(' (')[0];
            var sourceUsage = document.querySelector('#mergeSource option[value="' + sourceId + '"]').dataset.usage || 0;

            Swal.fire({
                title: 'Confirm Merge',
                html: 'This will move all <strong>' + sourceUsage + '</strong> document(s) from <strong>"' + sourceName + '"</strong> to <strong>"' + targetName + '"</strong> and delete <strong>"' + sourceName + '"</strong>.<br><br>This cannot be undone.',
                icon: 'warning', showCancelButton: true, confirmButtonColor: '#f59e0b', confirmButtonText: 'Yes, merge them', cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    var submitBtn = document.getElementById('btnConfirmMerge');
                    if (submitBtn) { submitBtn.classList.add('loading'); submitBtn.disabled = true; }
                    $.ajax({
                        url: '{{ route("documents.tags.merge") }}', type: 'POST', data: { source_id: sourceId, target_id: targetId, _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            mergeModal.hide();
                            Swal.fire({ icon: 'success', title: 'Merged', text: response.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 }).then(function() { window.location.reload(); });
                        },
                        error: function(xhr) {
                            if (submitBtn) { submitBtn.classList.remove('loading'); submitBtn.disabled = false; }
                            Swal.fire({ icon: 'error', title: 'Merge Failed', text: xhr.responseJSON?.message || 'Failed to merge tags.' });
                        }
                    });
                }
            });
        });

        window.deleteTag = function(id, name, usageCount) {
            if (usageCount > 0) { Swal.fire({ icon: 'warning', title: 'Cannot Delete', text: 'This tag is used by ' + usageCount + ' document(s). Use "Merge Tags" to merge it into another tag instead.' }); return; }
            Swal.fire({
                title: 'Delete "' + name + '"?', text: 'This action cannot be undone.', icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete it', cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("documents/tags") }}/' + id, type: 'POST', data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                        success: function(response) { Swal.fire({ icon: 'success', title: 'Deleted', text: response.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 }).then(function() { window.location.reload(); }); },
                        error: function(xhr) { Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to delete tag.' }); }
                    });
                }
            });
        };
        @endcan

        // ===================== Retention JS =====================
        @if(\App\Policies\DocumentPolicy::isAdmin(auth()->user()))
        document.querySelectorAll('.delete-policy-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Delete Policy?', text: 'This action cannot be undone.', icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Yes, delete it'
                }).then(function(result) { if (result.isConfirmed) { form.submit(); } });
            });
        });
        @endif

        // ===================== Quotas JS =====================
        @can('manage-document-quotas')
        var selectedUserIds = new Set();

        window.toggleEditForm = function(userId) {
            var editRow = document.getElementById('edit-row-' + userId);
            if (editRow) { editRow.style.display = editRow.style.display === 'none' ? '' : 'none'; }
        };

        window.toggleUserSelect = function(checkbox) {
            var userId = parseInt(checkbox.dataset.userId);
            if (checkbox.checked) { selectedUserIds.add(userId); } else { selectedUserIds.delete(userId); }
            updateBulkBar();
        };

        window.toggleSelectAllUsers = function(masterCheckbox) {
            document.querySelectorAll('.user-checkbox').forEach(function(cb) {
                cb.checked = masterCheckbox.checked;
                var userId = parseInt(cb.dataset.userId);
                if (masterCheckbox.checked) { selectedUserIds.add(userId); } else { selectedUserIds.delete(userId); }
            });
            updateBulkBar();
        };

        function updateBulkBar() {
            var bulkBar = document.getElementById('bulk-bar');
            var bulkCount = document.getElementById('bulk-count');
            var bulkUserIds = document.getElementById('bulk-user-ids');
            if (selectedUserIds.size > 0) {
                bulkBar.classList.add('show');
                bulkCount.textContent = selectedUserIds.size;
                bulkUserIds.innerHTML = '';
                selectedUserIds.forEach(function(id) {
                    var input = document.createElement('input');
                    input.type = 'hidden'; input.name = 'user_ids[]'; input.value = id;
                    bulkUserIds.appendChild(input);
                });
            } else {
                bulkBar.classList.remove('show');
            }
        }

        window.confirmBulk = function(actionLabel) {
            return confirm(actionLabel + ' ' + selectedUserIds.size + ' user(s)?');
        };

        window.recalculateUser = function(userId, btn) {
            var originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;
            $.ajax({
                url: '/documents/quotas/' + userId + '/recalculate', method: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Usage recalculated: ' + response.used_formatted, showConfirmButton: false, timer: 2000 });
                    setTimeout(function() { location.reload(); }, 1500);
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON.message : 'Recalculation failed.', 'error');
                    btn.innerHTML = originalHtml; btn.disabled = false;
                }
            });
        };

        // Loading state for edit forms
        document.querySelectorAll('.edit-form-inner').forEach(function(form) {
            form.addEventListener('submit', function() {
                var submitBtn = form.querySelector('button[type="submit"].btn-loading');
                if (submitBtn) { submitBtn.classList.add('loading'); submitBtn.disabled = true; }
            });
        });
        @endcan

        // ===================== Audit JS =====================
        @can('view-document-audit')
        $('.btn-detail-toggle').on('click', function() {
            var targetId = $(this).data('target');
            var $detailRow = $('#' + targetId);
            var $icon = $(this).find('i');
            var $btn = $(this);
            $detailRow.toggle();
            $btn.toggleClass('expanded');
            if ($btn.hasClass('expanded')) {
                $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            } else {
                $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            }
        });
        @endcan

        // ===================== General Settings JS =====================
        @can('manage-document-settings')
        document.querySelectorAll('.settings-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var btn = form.querySelector('button[type="submit"].btn-loading');
                if (btn) { btn.classList.add('loading'); btn.disabled = true; }

                $.ajax({
                    url: form.action, method: 'POST', data: $(form).serialize(),
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        Swal.fire({ icon: 'success', title: 'Saved', text: response.message || 'Settings updated successfully.', timer: 2000, showConfirmButton: false });
                    },
                    error: function(xhr) {
                        var msg = 'Failed to save settings.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) { msg = Object.values(xhr.responseJSON.errors).flat().join('\n'); }
                        else if (xhr.responseJSON && xhr.responseJSON.message) { msg = xhr.responseJSON.message; }
                        Swal.fire('Error', msg, 'error');
                    },
                    complete: function() { if (btn) { btn.classList.remove('loading'); btn.disabled = false; } }
                });
            });
        });
        @endcan
    });
</script>
@endsection
