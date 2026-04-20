{{-- Discount Types Tab Content --}}
<div class="help-text">
    <div class="help-title">Discount Types Directory</div>
    <div class="help-content">
        Discount types define percentage-based fee reductions that can be assigned to students.
        Common examples include sibling discounts, staff discounts, and scholarship discounts.
        Discounts can apply to all fees or tuition fees only.
    </div>
</div>

{{-- Inline Add Form --}}
<form id="addDiscountTypeForm" class="mb-4">
    @csrf
    <div class="row g-2 align-items-end">
        <div class="col-lg-2 col-md-3">
            <label class="form-label small">Code <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="code" placeholder="e.g., SIB" required maxlength="10">
        </div>
        <div class="col-lg-2 col-md-3">
            <label class="form-label small">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="name" placeholder="e.g., Sibling Discount" required>
        </div>
        <div class="col-lg-2 col-md-2">
            <label class="form-label small">Percentage <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="number" class="form-control" name="percentage" placeholder="10" required min="0" max="100" step="0.01">
                <span class="input-group-text">%</span>
            </div>
        </div>
        <div class="col-lg-2 col-md-2">
            <label class="form-label small">Applies To <span class="text-danger">*</span></label>
            <select class="form-select" name="applies_to" required>
                @foreach ($appliesOptions ?? [] as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-1 col-md-2">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="is_active" id="addDiscountActive" value="1" checked>
                <label class="form-check-label small" for="addDiscountActive">Active</label>
            </div>
        </div>
        <div class="col-lg-3 col-md-12">
            <button type="submit" class="btn btn-primary btn-loading w-100 justify-content-center">
                <span class="btn-text"><i class="fas fa-plus me-1"></i> Add Discount Type</span>
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
                    <input type="text" class="form-control" placeholder="Search by name or code..." id="discountSearchInput">
                </div>
            </div>
            <div class="col-lg-3 col-md-3">
                <select class="form-select" id="discountAppliesToFilter">
                    <option value="">All Applies To</option>
                    @foreach ($appliesOptions ?? [] as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-2">
                <select class="form-select" id="discountStatusFilter">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-3">
                <button type="button" class="btn btn-light w-100" id="discountResetFilters">Reset Filters</button>
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
                <th style="width: 120px;">Percentage</th>
                <th style="width: 130px;">Applies To</th>
                <th style="width: 100px;">Active</th>
                <th style="width: 100px;" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($discountTypes ?? [] as $index => $discountType)
                <tr class="discount-type-row"
                    data-name="{{ strtolower($discountType->name) }}"
                    data-code="{{ strtolower($discountType->code) }}"
                    data-applies-to="{{ $discountType->applies_to }}"
                    data-status="{{ $discountType->is_active ? 'active' : 'inactive' }}">
                    <td>{{ $index + 1 }}</td>
                    <td><code>{{ $discountType->code }}</code></td>
                    <td>{{ $discountType->name }}</td>
                    <td>
                        <span class="percentage-value">{{ number_format($discountType->percentage, 2) }}%</span>
                    </td>
                    <td>
                        @if ($discountType->applies_to === 'all')
                            <span class="applies-badge applies-all">All Fees</span>
                        @else
                            <span class="applies-badge applies-tuition">Tuition Only</span>
                        @endif
                    </td>
                    <td>
                        @if ($discountType->is_active)
                            <span class="status-badge status-active">Active</span>
                        @else
                            <span class="status-badge status-inactive">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="action-buttons">
                            <button type="button"
                                class="btn btn-sm btn-outline-info edit-discount-type"
                                data-bs-toggle="modal"
                                data-bs-target="#editDiscountTypeModal"
                                data-id="{{ $discountType->id }}"
                                data-code="{{ $discountType->code }}"
                                data-name="{{ $discountType->name }}"
                                data-percentage="{{ $discountType->percentage }}"
                                data-applies-to="{{ $discountType->applies_to }}"
                                data-description="{{ $discountType->description }}"
                                data-active="{{ $discountType->is_active ? '1' : '0' }}"
                                title="Edit Discount Type">
                                <i class="bx bx-edit-alt"></i>
                            </button>
                            <form action="{{ route('fees.setup.discount-types.destroy', $discountType->id) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirmDelete('discount type')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Discount Type">
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
                            <i class="fas fa-percent" style="font-size: 48px; opacity: 0.3;"></i>
                            <p class="mt-3 mb-0" style="font-size: 15px;">No Discount Types</p>
                            <p class="text-muted" style="font-size: 13px;">Use the form above to create your first discount type</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
