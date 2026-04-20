{{-- Teacher Room Assignment Tab Content (CONST-10) --}}
<style>
    .room-assign-table {
        width: 100%;
        border-collapse: collapse;
    }

    .room-assign-table thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 10px 12px;
    }

    .room-assign-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        font-size: 13px;
        color: #374151;
    }

    .room-assign-table tbody tr:hover {
        background: #f0f5ff;
    }

    .room-assign-table .teacher-name {
        font-weight: 600;
        color: #1f2937;
    }

    .room-assign-table .venue-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 500;
        background: #eff6ff;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }

    .room-assign-table .row-num {
        color: #9ca3af;
        font-size: 12px;
        font-weight: 500;
    }

    .room-assign-table tfoot td {
        padding: 14px 12px;
        background: #f9fafb;
        border-top: 2px solid #e5e7eb;
        font-size: 13px;
        color: #6b7280;
    }
</style>

<div class="help-text">
    <div class="help-title">Teacher Home Room Assignments</div>
    <div class="help-content">
        Assign a teacher to a fixed "home room" so students rotate to them. This eliminates venue allocation from the generator for every teacher with a home room, dramatically reducing conflicts. Home rooms are overridden by explicit subject-level venue assignments and room requirement constraints (e.g. labs). This is a hard constraint.
    </div>
</div>

<div class="settings-section">
    {{-- Add New Assignment --}}
    <h6 class="section-title"><i class="fas fa-plus-circle me-2"></i>Assign Home Room</h6>
    <div class="row g-3 mb-4 align-items-end">
        <div class="col-md-4">
            <label class="form-label" for="roomAssignTeacherSelect">Teacher</label>
            <select class="form-select" id="roomAssignTeacherSelect">
                <option value="">-- Select Teacher --</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="roomAssignVenueSelect">Home Room</label>
            <select class="form-select" id="roomAssignVenueSelect">
                <option value="">-- Select Venue --</option>
                @foreach($venues as $venue)
                    <option value="{{ $venue->id }}">{{ $venue->name }} ({{ $venue->type }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <button type="button" class="btn btn-primary btn-sm" id="addTeacherRoomAssignmentBtn">
                <i class="fas fa-plus me-1"></i> Assign Room
            </button>
        </div>
    </div>

    {{-- Existing Assignments --}}
    <h6 class="section-title"><i class="fas fa-home me-2"></i>Current Home Room Assignments</h6>
    <div id="teacherRoomAssignmentContainer">
        @php
            $roomAssignConstraints = $constraints->get('teacher_room_assignment', collect());
        @endphp
        @if($roomAssignConstraints->count() > 0)
            <div class="table-responsive">
                <table class="room-assign-table" id="teacherRoomAssignmentTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Teacher</th>
                            <th>Home Room</th>
                            <th style="width: 80px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="teacherRoomAssignmentBody">
                        @foreach($roomAssignConstraints as $idx => $assignment)
                            @php
                                $assignTeacherId = $assignment->constraint_config['teacher_id'] ?? null;
                                $assignVenueId = $assignment->constraint_config['venue_id'] ?? null;
                                $assignTeacher = $teachers->firstWhere('id', $assignTeacherId);
                                $assignVenue = $venues->firstWhere('id', $assignVenueId);
                            @endphp
                            <tr data-constraint-id="{{ $assignment->id }}" data-teacher-id="{{ $assignTeacherId }}">
                                <td><span class="row-num">{{ $idx + 1 }}</span></td>
                                <td><span class="teacher-name">{{ $assignTeacher ? $assignTeacher->firstname . ' ' . $assignTeacher->lastname : 'Unknown' }}</span></td>
                                <td><span class="venue-badge"><i class="fas fa-home"></i> {{ $assignVenue ? $assignVenue->name . ' (' . $assignVenue->type . ')' : 'Unknown' }}</span></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-room-assign-btn" data-constraint-id="{{ $assignment->id }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>{{ $roomAssignConstraints->count() }}</strong> assignment{{ $roomAssignConstraints->count() !== 1 ? 's' : '' }} configured
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div id="roomAssignEmptyState" class="text-center text-muted py-4">
                <i class="fas fa-home mb-2" style="font-size: 24px; opacity: 0.4;"></i>
                <p class="mb-0">No home room assignments configured yet.</p>
            </div>
            <div class="table-responsive" style="display: none;">
                <table class="room-assign-table" id="teacherRoomAssignmentTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Teacher</th>
                            <th>Home Room</th>
                            <th style="width: 80px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="teacherRoomAssignmentBody"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong id="roomAssignCount">0</strong> assignment(s) configured
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
