{{-- Fee Structures Tab Content --}}
<div class="help-text">
    <div class="help-title">Fee Structures Directory</div>
    <div class="help-content">
        Fee structures define the specific amounts charged for each fee type, grade, and year.
        Historical years (past calendar years) are locked and cannot be modified.
        Use the "Copy Structures" feature to quickly create fee structures for a new year based on existing ones.
    </div>
</div>

{{-- Filters & Actions --}}
<div class="row align-items-center mb-3">
    <div class="col-lg-8 col-md-12">
        <div class="row g-2 align-items-center">
            <div class="col-lg-3 col-md-3">
                <select class="form-select" id="structureGradeFilter">
                    <option value="">All Grades</option>
                    @foreach ($grades ?? [] as $grade)
                        <option value="{{ strtolower($grade->name) }}">{{ $grade->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-3">
                <select class="form-select" id="structureYearFilter">
                    <option value="">All Years</option>
                    @foreach ($years ?? [] as $year)
                        <option value="{{ $year }}" {{ $year == ($currentTermYear ?? '') ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" placeholder="Search fee type..." id="structureSearchInput">
                </div>
            </div>
            <div class="col-lg-2 col-md-2">
                <button type="button" class="btn btn-light w-100" id="structureResetFilters">Reset</button>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
        <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#copyStructuresModal">
                <i class="fas fa-copy me-1"></i> Copy Structures
            </button>
            <a href="{{ route('fees.setup.structures.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Structure
            </a>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th>Fee Type</th>
                <th style="width: 120px;">Grade</th>
                <th style="width: 100px;">Year</th>
                <th style="width: 130px;" class="text-end">Amount</th>
                <th style="width: 100px;" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($feeStructures ?? [] as $index => $structure)
                @php
                    $isHistorical = $structure->year < date('Y');
                @endphp
                <tr class="fee-structure-row {{ $isHistorical ? 'locked-row' : '' }}"
                    data-fee-type="{{ strtolower($structure->feeType->name ?? '') }}"
                    data-grade="{{ strtolower($structure->grade->name ?? '') }}"
                    data-year="{{ $structure->year }}">
                    <td>
                        @if ($isHistorical)
                            <i class="fas fa-lock locked-icon" title="Historical year - locked"></i>
                        @endif
                        {{ $index + 1 }}
                    </td>
                    <td>{{ $structure->feeType->name ?? 'N/A' }}</td>
                    <td>
                        <span class="grade-badge">{{ $structure->grade->name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <span class="year-badge {{ $isHistorical ? 'historical' : '' }}">
                            @if ($isHistorical)
                                <i class="fas fa-lock" style="font-size: 10px;"></i>
                            @endif
                            {{ $structure->year }}
                        </span>
                    </td>
                    <td class="text-end amount-cell">{{ format_currency($structure->amount) }}</td>
                    <td class="text-end">
                        <div class="action-buttons">
                            @if (!$isHistorical)
                                <button type="button"
                                    class="btn btn-sm btn-outline-info edit-fee-structure"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editFeeStructureModal"
                                    data-id="{{ $structure->id }}"
                                    data-fee-type-id="{{ $structure->fee_type_id }}"
                                    data-grade-id="{{ $structure->grade_id }}"
                                    data-year="{{ $structure->year }}"
                                    data-amount="{{ $structure->amount }}"
                                    title="Edit Fee Structure">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                                <form action="{{ route('fees.setup.structures.destroy', $structure->id) }}"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirmDelete('fee structure')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Fee Structure">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Historical year - locked">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Historical year - locked">
                                    <i class="bx bx-trash"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="text-center text-muted" style="padding: 40px 0;">
                            <i class="fas fa-layer-group" style="font-size: 48px; opacity: 0.3;"></i>
                            <p class="mt-3 mb-0" style="font-size: 15px;">No Fee Structures</p>
                            <p class="text-muted" style="font-size: 13px;">Click "Add Structure" to create your first fee structure</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
