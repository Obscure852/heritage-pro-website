{{-- Fee Types Tab Content --}}
<div class="help-text">
    <div class="help-title">Fee Types Directory</div>
    <div class="help-content">
        Fee types define the different categories of fees that can be charged to students.
        Each fee type has a unique code and can be marked as optional or required.
        Active fee types can be used when creating fee structures.
    </div>
</div>

{{-- Inline Add Form --}}
<form id="addFeeTypeForm" class="mb-4">
    @csrf
    <div class="row g-2 align-items-end">
        <div class="col-lg-2 col-md-3">
            <label class="form-label small">Code <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="code" placeholder="e.g., TUI" required maxlength="10">
        </div>
        <div class="col-lg-3 col-md-4">
            <label class="form-label small">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="name" placeholder="e.g., Tuition Fee" required>
        </div>
        <div class="col-lg-2 col-md-3">
            <label class="form-label small">Category <span class="text-danger">*</span></label>
            <select class="form-select" name="category" required>
                <option value="">Select...</option>
                @foreach ($categories ?? [] as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-2">
            <div class="form-check mb-2">
                <input type="checkbox" class="form-check-input" name="is_optional" id="addFeeTypeOptional" value="1">
                <label class="form-check-label small" for="addFeeTypeOptional">Optional</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="is_active" id="addFeeTypeActive" value="1" checked>
                <label class="form-check-label small" for="addFeeTypeActive">Active</label>
            </div>
        </div>
        <div class="col-lg-3 col-md-12">
            <button type="submit" class="btn btn-primary btn-loading w-100 justify-content-center">
                <span class="btn-text"><i class="fas fa-plus me-1"></i> Add Fee Type</span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-2"></span>Adding...
                </span>
            </button>
        </div>
    </div>
</form>

{{-- Filters --}}
<div class="row align-items-center mb-3">
    <div class="col-lg-12">
        <div class="row g-2 align-items-center">
            <div class="col-lg-4 col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" placeholder="Search by name or code..." id="feeTypeSearchInput">
                </div>
            </div>
            <div class="col-lg-3 col-md-3">
                <select class="form-select" id="feeTypeCategoryFilter">
                    <option value="">All Categories</option>
                    @foreach ($categories ?? [] as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-2">
                <select class="form-select" id="feeTypeStatusFilter">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-3">
                <button type="button" class="btn btn-light w-100" id="feeTypeResetFilters">Reset Filters</button>
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th style="width: 100px;">Code</th>
                <th>Name</th>
                <th style="width: 120px;">Category</th>
                <th style="width: 100px;">Optional</th>
                <th style="width: 100px;">Active</th>
                <th style="width: 100px;" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($feeTypes ?? [] as $index => $feeType)
                <tr class="fee-type-row"
                    data-name="{{ strtolower($feeType->name) }}"
                    data-code="{{ strtolower($feeType->code) }}"
                    data-category="{{ strtolower($feeType->category) }}"
                    data-status="{{ $feeType->is_active ? 'active' : 'inactive' }}">
                    <td>{{ $index + 1 }}</td>
                    <td><code>{{ $feeType->code }}</code></td>
                    <td>{{ $feeType->name }}</td>
                    <td>
                        @php
                            $categoryClass = 'category-' . strtolower($feeType->category ?? 'other');
                        @endphp
                        <span class="category-badge {{ $categoryClass }}">{{ $categories[$feeType->category] ?? 'Other' }}</span>
                    </td>
                    <td>
                        @if ($feeType->is_optional)
                            <span class="status-badge status-optional">Optional</span>
                        @else
                            <span class="status-badge status-required">Required</span>
                        @endif
                    </td>
                    <td>
                        @if ($feeType->is_active)
                            <span class="status-badge status-active">Active</span>
                        @else
                            <span class="status-badge status-inactive">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="action-buttons">
                            <button type="button"
                                class="btn btn-sm btn-outline-info edit-fee-type"
                                data-bs-toggle="modal"
                                data-bs-target="#editFeeTypeModal"
                                data-id="{{ $feeType->id }}"
                                data-code="{{ $feeType->code }}"
                                data-name="{{ $feeType->name }}"
                                data-category="{{ $feeType->category }}"
                                data-description="{{ $feeType->description }}"
                                data-optional="{{ $feeType->is_optional ? '1' : '0' }}"
                                data-active="{{ $feeType->is_active ? '1' : '0' }}"
                                title="Edit Fee Type">
                                <i class="bx bx-edit-alt"></i>
                            </button>
                            <form action="{{ route('fees.setup.types.destroy', $feeType->id) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirmDelete('fee type')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Fee Type">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="text-center text-muted" style="padding: 40px 0;">
                            <i class="fas fa-tags" style="font-size: 48px; opacity: 0.3;"></i>
                            <p class="mt-3 mb-0" style="font-size: 15px;">No Fee Types</p>
                            <p class="text-muted" style="font-size: 13px;">Use the form above to create your first fee type</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
