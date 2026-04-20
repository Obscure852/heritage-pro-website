@extends('layouts.master')
@section('title', 'Create Asset Audit')

@section('css')
    <style>
        .audit-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .audit-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .audit-header h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .audit-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .audit-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            border-left: 4px solid #4e73df;
            padding: 16px 20px;
            margin-bottom: 24px;
            border-radius: 0 3px 3px 0;
        }

        .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
        }

        .help-content {
            color: #6b7280;
            font-size: 14px;
            margin: 0;
        }

        .form-section {
            margin-bottom: 24px;
        }

        .form-section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
        }

        .form-section-title i {
            margin-right: 10px;
            color: #4e73df;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-text {
            font-size: 12px;
            color: #6b7280;
        }

        .summary-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            padding: 16px;
        }

        .summary-card-header {
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #bfdbfe;
            font-size: 14px;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-item-label {
            color: #374151;
            font-weight: 500;
        }

        .summary-item-value {
            color: #1e40af;
            font-weight: 600;
        }

        .assets-selection-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .assets-selection-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #374151;
        }

        .search-input {
            position: relative;
        }

        .search-input input {
            padding-left: 36px;
        }

        .search-input i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .assets-list-container {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            max-height: 400px;
            overflow-y: auto;
            background: #f9fafb;
        }

        .category-group {
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .category-group:last-child {
            border-bottom: none;
        }

        .category-header {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        .category-header input[type="checkbox"] {
            margin-right: 10px;
        }

        .category-name {
            font-weight: 600;
            color: #4e73df;
            font-size: 14px;
        }

        .category-count {
            background: #dbeafe;
            color: #1e40af;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 8px;
        }

        .asset-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 12px;
            margin-bottom: 8px;
            margin-left: 24px;
            display: flex;
            align-items: flex-start;
        }

        .asset-item:last-child {
            margin-bottom: 0;
        }

        .asset-item input[type="checkbox"] {
            margin-right: 12px;
            margin-top: 4px;
        }

        .asset-details {
            flex: 1;
        }

        .asset-name {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .asset-code {
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .asset-badges {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .asset-badge {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
        }

        .asset-badge.available {
            background: #dcfce7;
            color: #166534;
        }

        .asset-badge.assigned {
            background: #dbeafe;
            color: #1e40af;
        }

        .asset-badge.maintenance {
            background: #fef3c7;
            color: #b45309;
        }

        .asset-badge.location {
            background: #f3f4f6;
            color: #374151;
        }

        .empty-assets {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-assets i {
            font-size: 48px;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-back {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
            padding: 10px 20px;
            border-radius: 3px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .btn-create {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .btn-create:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        @media (max-width: 768px) {
            .audit-header {
                padding: 20px;
            }

            .assets-selection-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .asset-item {
                margin-left: 0;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('audits.index') }}">Back</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('audits.index') }}">Audits</a>
        @endslot
        @slot('title')
            Create New Audit
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach
    @endif

    <div class="audit-container">
        <div class="audit-header">
            <h4><i class="bx bx-plus-circle me-2"></i>Create New Audit</h4>
            <p>Schedule a new asset audit and select which assets to include</p>
        </div>

        <div class="audit-body">
            <!-- Help Text -->
            <div class="help-text">
                <div class="help-title"><i class="fas fa-info-circle me-2"></i>Getting Started</div>
                <p class="help-content">Select the assets you want to audit. You can start the audit process after creating it. Group selection by category is available for convenience.</p>
            </div>

            <form action="{{ route('audits.store') }}" method="POST" id="auditForm">
                @csrf

                <div class="row">
                    <div class="col-md-5">
                        <!-- Audit Details Section -->
                        <div class="form-section">
                            <h6 class="form-section-title"><i class="bx bx-calendar"></i>Audit Details</h6>

                            <div class="mb-3">
                                <label for="audit_date" class="form-label">Audit Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="audit_date"
                                    name="audit_date" value="{{ old('audit_date', date('Y-m-d')) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="next_audit_date" class="form-label">Next Audit Date</label>
                                <input type="date" class="form-control" id="next_audit_date"
                                    name="next_audit_date" value="{{ old('next_audit_date') }}">
                                <small class="form-text">When should the next audit be scheduled?</small>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Audit Notes</label>
                                <textarea class="form-control" id="notes" name="notes"
                                    rows="4" placeholder="Any special instructions or notes for this audit">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <!-- Summary Card -->
                        <div class="summary-card">
                            <div class="summary-card-header"><i class="bx bx-info-circle me-2"></i>Audit Summary</div>
                            <div class="summary-item">
                                <span class="summary-item-label">Selected Assets:</span>
                                <span class="summary-item-value" id="selected-count">0</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-item-label">Audit Code:</span>
                                <span class="summary-item-value text-muted" style="font-weight: normal;">Auto-generated</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-item-label">Conducted By:</span>
                                <span class="summary-item-value">{{ auth()->user()->firstname }} {{ auth()->user()->lastname }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <!-- Asset Selection Section -->
                        <div class="assets-selection-header">
                            <h5><i class="bx bx-package me-2"></i>Select Assets to Audit</h5>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="selectNone">Select None</button>
                            </div>
                        </div>

                        <div class="mb-3 search-input">
                            <i class="bx bx-search"></i>
                            <input type="text" class="form-control" id="assetSearch"
                                placeholder="Search assets by name, code, or category...">
                        </div>

                        <div class="assets-list-container">
                            @if($assets->count() > 0)
                                @foreach($assets->groupBy('category.name') as $categoryName => $categoryAssets)
                                    <div class="category-group">
                                        <div class="category-header">
                                            <input type="checkbox" class="form-check-input category-checkbox" data-category="{{ $categoryName }}">
                                            <span class="category-name">{{ $categoryName ?: 'Uncategorized' }}</span>
                                            <span class="category-count">{{ $categoryAssets->count() }}</span>
                                        </div>

                                        @foreach($categoryAssets as $asset)
                                            <div class="asset-item" data-category="{{ $categoryName }}"
                                                 data-search="{{ strtolower($asset->name . ' ' . $asset->asset_code . ' ' . ($asset->category->name ?? '')) }}">
                                                <input class="form-check-input asset-checkbox" type="checkbox"
                                                       name="asset_ids[]" value="{{ $asset->id }}" id="asset_{{ $asset->id }}">
                                                <div class="asset-details">
                                                    <div class="asset-name">{{ $asset->name }}</div>
                                                    <div class="asset-code">{{ $asset->asset_code }}</div>
                                                    <div class="asset-badges">
                                                        <span class="asset-badge {{ $asset->status == 'Available' ? 'available' : ($asset->status == 'Assigned' ? 'assigned' : 'maintenance') }}">
                                                            {{ $asset->status }}
                                                        </span>
                                                        @if($asset->venue)
                                                            <span class="asset-badge location">{{ $asset->venue->name }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            @else
                                <div class="empty-assets">
                                    <i class="bx bx-package"></i>
                                    <p>No assets available for audit</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="{{ route('audits.index') }}" class="btn-back">
                        <i class="bx bx-arrow-back me-1"></i> Back
                    </a>
                    <button type="submit" class="btn-create">
                        <i class="bx bx-plus me-1"></i> Create Audit
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const assetCheckboxes = document.querySelectorAll('.asset-checkbox');
        const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
        const selectedCountElement = document.getElementById('selected-count');
        const selectAllBtn = document.getElementById('selectAll');
        const selectNoneBtn = document.getElementById('selectNone');
        const assetSearch = document.getElementById('assetSearch');
        const assetItems = document.querySelectorAll('.asset-item');
        const form = document.getElementById('auditForm');

        function updateSelectedCount() {
            const selectedCount = document.querySelectorAll('.asset-checkbox:checked').length;
            selectedCountElement.textContent = selectedCount;
        }

        function updateCategoryCheckbox(categoryName) {
            const categoryCheckbox = document.querySelector(`[data-category="${categoryName}"].category-checkbox`);
            if (!categoryCheckbox) return;

            const categoryAssets = document.querySelectorAll(`.asset-item[data-category="${categoryName}"]`);
            const categoryAssetCheckboxes = Array.from(categoryAssets)
                .filter(item => item.style.display !== 'none')
                .map(item => item.querySelector('.asset-checkbox'));

            const checkedCount = categoryAssetCheckboxes.filter(cb => cb && cb.checked).length;

            if (checkedCount === 0) {
                categoryCheckbox.indeterminate = false;
                categoryCheckbox.checked = false;
            } else if (checkedCount === categoryAssetCheckboxes.length) {
                categoryCheckbox.indeterminate = false;
                categoryCheckbox.checked = true;
            } else {
                categoryCheckbox.indeterminate = true;
                categoryCheckbox.checked = false;
            }
        }

        assetCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedCount();
                const categoryName = this.closest('.asset-item').dataset.category;
                updateCategoryCheckbox(categoryName);
            });
        });

        categoryCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const categoryName = this.dataset.category;
                const categoryAssets = document.querySelectorAll(`.asset-item[data-category="${categoryName}"]`);

                categoryAssets.forEach(item => {
                    if (item.style.display !== 'none') {
                        const assetCheckbox = item.querySelector('.asset-checkbox');
                        if (assetCheckbox) assetCheckbox.checked = this.checked;
                    }
                });

                updateSelectedCount();
            });
        });

        selectAllBtn.addEventListener('click', function() {
            assetItems.forEach(item => {
                if (item.style.display !== 'none') {
                    const checkbox = item.querySelector('.asset-checkbox');
                    if (checkbox) checkbox.checked = true;
                }
            });

            categoryCheckboxes.forEach(cb => {
                cb.checked = true;
                cb.indeterminate = false;
            });

            updateSelectedCount();
        });

        selectNoneBtn.addEventListener('click', function() {
            assetCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            categoryCheckboxes.forEach(cb => {
                cb.checked = false;
                cb.indeterminate = false;
            });

            updateSelectedCount();
        });

        assetSearch.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();

            assetItems.forEach(item => {
                const searchData = item.dataset.search;
                if (searchData.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });

            categoryCheckboxes.forEach(cb => {
                updateCategoryCheckbox(cb.dataset.category);
            });
        });

        form.addEventListener('submit', function(e) {
            const selectedAssets = document.querySelectorAll('.asset-checkbox:checked').length;

            if (selectedAssets === 0) {
                e.preventDefault();
                alert('Please select at least one asset to audit.');
                return false;
            }
        });

        const auditDateInput = document.getElementById('audit_date');
        const nextAuditDateInput = document.getElementById('next_audit_date');

        auditDateInput.addEventListener('change', function() {
            if (nextAuditDateInput.value === '') {
                const auditDate = new Date(this.value);
                if (!isNaN(auditDate)) {
                    const nextAuditDate = new Date(auditDate);
                    nextAuditDate.setFullYear(nextAuditDate.getFullYear() + 1);
                    const year = nextAuditDate.getFullYear();
                    const month = String(nextAuditDate.getMonth() + 1).padStart(2, '0');
                    const day = String(nextAuditDate.getDate()).padStart(2, '0');
                    nextAuditDateInput.value = `${year}-${month}-${day}`;
                }
            }
        });

        updateSelectedCount();
    });
</script>
@endsection
