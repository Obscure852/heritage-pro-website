{{-- Assignment Modal: click-to-assign a subject + teacher to a timetable slot --}}
<div class="modal fade" id="assignmentModal" tabindex="-1" aria-labelledby="assignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fa; border-bottom: 1px solid #e5e7eb;">
                <h5 class="modal-title" id="assignmentModalLabel">
                    <i class="bx bx-calendar-edit me-2 text-primary"></i>
                    <span id="modalTitle">Assign Lesson</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Hidden context fields set by JavaScript when modal opens --}}
                <input type="hidden" id="modalTimetableId" value="">
                <input type="hidden" id="modalKlassId" value="">
                <input type="hidden" id="modalDayOfCycle" value="">
                <input type="hidden" id="modalPeriodNumber" value="">

                {{-- 1. Subject dropdown --}}
                <div class="mb-3">
                    <label for="modalSubjectSelect" class="form-label">Subject</label>
                    <select id="modalSubjectSelect" class="form-select">
                        <option value="">-- Select Subject --</option>
                    </select>
                </div>

                {{-- 2. Allocation status display --}}
                <div id="allocationStatus" class="help-text mb-3" style="display: none;">
                    <div class="help-title">Allocation Status</div>
                    <div class="help-content" id="allocationStatusContent"></div>
                </div>

                {{-- 3. Teacher dropdown --}}
                <div class="mb-3">
                    <label for="modalTeacherSelect" class="form-label">Teacher</label>
                    <select id="modalTeacherSelect" class="form-select">
                        <option value="">-- Select Teacher --</option>
                    </select>
                </div>

                {{-- 4. Block type radio buttons --}}
                <div class="mb-3">
                    <label class="form-label d-block">Block Type</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="block_type" id="blockSingle" value="1" checked>
                            <label class="form-check-label" for="blockSingle">Single</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="block_type" id="blockDouble" value="2">
                            <label class="form-check-label" for="blockDouble">Double</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="block_type" id="blockTriple" value="3">
                            <label class="form-check-label" for="blockTriple">Triple</label>
                        </div>
                    </div>
                    <div id="blockTypeWarning" class="form-hint text-danger mt-1" style="display: none;"></div>
                </div>

                {{-- 5. Conflict indicator --}}
                <div id="conflictIndicator" style="display: none;"></div>

                {{-- 6. Soft constraint warnings --}}
                <div id="softWarningIndicator" style="display: none;"></div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e5e7eb;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="saveSlotBtn" class="btn btn-primary btn-loading" disabled>
                    <span class="btn-text"><i class="fas fa-check me-1"></i> Assign Slot</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Assigning...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
