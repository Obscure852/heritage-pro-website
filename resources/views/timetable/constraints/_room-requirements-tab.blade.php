{{-- Room Requirements Tab Content (CONST-03) --}}
<style>
    .room-req-table {
        width: 100%;
        border-collapse: collapse;
    }

    .room-req-table thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 10px 12px;
    }

    .room-req-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        font-size: 13px;
        color: #374151;
    }

    .room-req-table tbody tr:hover {
        background: #f0f5ff;
    }

    .room-req-table .subject-name {
        font-weight: 600;
        color: #1f2937;
    }

    .room-req-table .row-num {
        color: #9ca3af;
        font-size: 12px;
        font-weight: 500;
    }

    .room-req-table .venue-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 500;
    }

    .room-req-table .venue-type-badge.type-classroom {
        background: #eff6ff;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }

    .room-req-table .venue-type-badge.type-laboratory,
    .room-req-table .venue-type-badge.type-lab {
        background: #faf5ff;
        color: #6b21a8;
        border: 1px solid #e9d5ff;
    }

    .room-req-table .venue-type-badge.type-computer-lab {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .room-req-table .venue-type-badge.type-workshop {
        background: #fffbeb;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .room-req-table .venue-type-badge.type-hall {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .room-req-table .venue-type-badge.type-field,
    .room-req-table .venue-type-badge.type-sports {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .room-req-table .venue-type-badge.type-library {
        background: #fff7ed;
        color: #9a3412;
        border: 1px solid #fed7aa;
    }

    .room-req-table .venue-type-badge.type-default {
        background: #f9fafb;
        color: #6b7280;
        border: 1px solid #e5e7eb;
    }

    .room-req-table .action-btns {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .room-req-table .room-req-venue-select {
        width: 160px;
        padding: 5px 8px;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        font-size: 13px;
        transition: all 0.2s;
    }

    .room-req-table .room-req-venue-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .room-req-table tfoot td {
        padding: 14px 12px;
        background: #f9fafb;
        border-top: 2px solid #e5e7eb;
        font-size: 13px;
        color: #6b7280;
    }
</style>

@php
    $venueTypeIcons = [
        'Classroom' => 'fas fa-chalkboard',
        'Laboratory' => 'fas fa-flask',
        'Lab' => 'fas fa-flask',
        'Computer Lab' => 'fas fa-desktop',
        'Workshop' => 'fas fa-tools',
        'Hall' => 'fas fa-archway',
        'Library' => 'fas fa-book',
        'Sports' => 'fas fa-running',
        'Field' => 'fas fa-running',
    ];
@endphp

<div class="help-text">
    <div class="help-title">Room Requirements</div>
    <div class="help-content">
        Define which venue type each subject requires (e.g., Science requires a Laboratory, PE requires a Field). This is a hard constraint -- the system will only schedule a subject in a venue of the required type.
    </div>
</div>

<div class="settings-section">
    {{-- Add New Requirement --}}
    <h6 class="section-title"><i class="fas fa-plus-circle me-2"></i>Add Room Requirement</h6>
    <div class="row g-3 mb-4 align-items-end">
        <div class="col-md-4">
            <label class="form-label" for="roomReqSubjectSelect">Subject</label>
            <select class="form-select" id="roomReqSubjectSelect">
                <option value="">-- Select Subject --</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="roomReqVenueTypeSelect">Required Venue Type</label>
            <select class="form-select" id="roomReqVenueTypeSelect">
                <option value="">-- Select Venue Type --</option>
                @foreach($venueTypes as $vt)
                    <option value="{{ $vt }}">{{ $vt }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <button type="button" class="btn btn-primary btn-sm" id="addRoomRequirementBtn">
                <i class="fas fa-plus me-1"></i> Add Requirement
            </button>
        </div>
    </div>

    {{-- Existing Requirements --}}
    <h6 class="section-title"><i class="fas fa-door-open me-2"></i>Current Requirements</h6>
    <div id="roomRequirementsContainer">
        @php
            $roomReqConstraints = $constraints->get('room_requirement', collect());
        @endphp
        @if($roomReqConstraints->count() > 0)
            <div class="table-responsive">
                <table class="room-req-table" id="roomRequirementsTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Subject</th>
                            <th>Current Venue Type</th>
                            <th style="width: 200px;">Change To</th>
                            <th style="width: 120px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="roomRequirementsBody">
                        @foreach($roomReqConstraints as $idx => $req)
                            @php
                                $subjectId = $req->constraint_config['subject_id'] ?? null;
                                $subjectName = $subjects->firstWhere('id', $subjectId)->name ?? 'Unknown';
                                $venueType = $req->constraint_config['required_venue_type'] ?? '';
                                $vtIcon = $venueTypeIcons[$venueType] ?? 'fas fa-door-open';
                                $vtClass = 'type-' . strtolower(str_replace(' ', '-', $venueType ?: 'default'));
                            @endphp
                            <tr data-constraint-id="{{ $req->id }}" data-subject-id="{{ $subjectId }}">
                                <td><span class="row-num">{{ $idx + 1 }}</span></td>
                                <td><span class="subject-name">{{ $subjectName }}</span></td>
                                <td><span class="venue-type-badge {{ $vtClass }}"><i class="{{ $vtIcon }}"></i> {{ $venueType }}</span></td>
                                <td>
                                    <select class="room-req-venue-select room-req-venue-type">
                                        @foreach($venueTypes as $vt)
                                            <option value="{{ $vt }}" @if($vt === $venueType) selected @endif>{{ $vt }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="action-btns justify-content-center">
                                        <button type="button" class="btn btn-sm btn-primary btn-loading save-room-req-btn" data-subject-id="{{ $subjectId }}">
                                            <span class="btn-text"><i class="fas fa-save"></i></span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            </span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-room-req-btn" data-constraint-id="{{ $req->id }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>{{ $roomReqConstraints->count() }}</strong> requirement{{ $roomReqConstraints->count() !== 1 ? 's' : '' }} configured
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div id="roomReqEmptyState" class="text-center text-muted py-4">
                <i class="fas fa-door-open mb-2" style="font-size: 24px; opacity: 0.4;"></i>
                <p class="mb-0">No room requirements configured yet.</p>
            </div>
            <div class="table-responsive" style="display: none;">
                <table class="room-req-table" id="roomRequirementsTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Subject</th>
                            <th>Current Venue Type</th>
                            <th style="width: 200px;">Change To</th>
                            <th style="width: 120px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="roomRequirementsBody"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong id="roomReqRuleCount">0</strong> requirement(s) configured
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
