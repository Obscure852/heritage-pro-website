<style>
        .status-indicator {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            position: absolute;
            bottom: 1.25rem;
            right: 1.25rem;
        }

        .status-text {
            font-size: 0.8rem;
            color: #6c757d;
            margin-right: 0.5rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .status-dot.active {
            background-color: #198754;
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(25, 135, 84, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
            }
        }
</style>
@if($groupedData->count() > 0)
    <div class="col-md-12">
        <div class="accordion" id="subjectsAccordion">
            @foreach($groupedData as $index => $subjectGroup)
                @php
                    $subjectId = 'subject_' . $subjectGroup['subject_id'];
                    $isFirst = $loop->first;
                @endphp
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading{{ $subjectId }}">
                        <button class="accordion-button {{ !$isFirst ? 'collapsed' : '' }}" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse{{ $subjectId }}" 
                                aria-expanded="{{ $isFirst ? 'true' : 'false' }}" 
                                aria-controls="collapse{{ $subjectId }}">
                            <div class="d-flex align-items-center w-100">
                                <div class="me-3">
                                    <div class="fw-bold">{{ $subjectGroup['subject_name'] }}</div>
                                    @if($subjectGroup['subject_code'])
                                        <small class="text-muted">Code: {{ $subjectGroup['subject_code'] }}</small>
                                    @endif
                                </div>
                                <div class="ms-auto me-3">
                                    <span class="badge bg-primary me-2">{{ $subjectGroup['total_classes'] }} classes</span>
                                    <span class="badge bg-success me-2">{{ $subjectGroup['active_classes'] }} active</span>
                                    <small class="text-muted">{{ $subjectGroup['department'] }}</small>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse{{ $subjectId }}" 
                            class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}" 
                            aria-labelledby="heading{{ $subjectId }}" 
                            data-bs-parent="#subjectsAccordion">
                        <div class="accordion-body">
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($subjectGroup['klass_subjects'] as $klassSubject)
                                    <div class="card" style="width: 320px; height: 220px;">
                                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                                            <div>
                                                <h6 class="card-title mb-0 fw-bold">{{ $klassSubject['klass_name'] }}</h6>
                                                <span class="badge bg-info badge-sm">{{ $klassSubject['grade_name'] }}</span>
                                            </div>
                                            <div class="text-end">
                                                @if($klassSubject['mandatory'])
                                                    <span class="badge bg-danger badge-sm">Mandatory</span>
                                                @else
                                                    <span class="badge bg-warning badge-sm">Optional</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    @if($klassSubject['teacher_name'] !== 'Not Assigned')
                                                        <i class="bx bxs-user text-primary me-2"></i>
                                                        <span class="text-primary">{{ $klassSubject['teacher_name'] }}</span>
                                                    @else
                                                        <i class="bx bxs-user-x text-warning me-2"></i>
                                                        <span class="text-warning">Not Assigned</span>
                                                    @endif
                                                </div>
                                                
                                                <div class="d-flex align-items-center mb-2">
                                                    @if($klassSubject['venue_name'] !== 'Not Assigned')
                                                        <i class="bx bxs-map-pin text-info me-2"></i>
                                                        <span class="text-info">{{ $klassSubject['venue_name'] }}</span>
                                                    @else
                                                        <i class="bx bxs-map-pin text-muted me-2"></i>
                                                        <span class="text-muted">Not Assigned</span>
                                                    @endif
                                                </div>

                                                <div class="d-flex align-items-center mb-2">
                                                    @if($klassSubject['student_count'] > 0)
                                                        <i class="bx bxs-group text-success me-2"></i>
                                                        <span class="text-success">{{ $klassSubject['student_count'] }} students</span>
                                                    @else
                                                        <i class="bx bxs-group text-muted me-2"></i>
                                                        <span class="text-muted">No Students</span>
                                                    @endif
                                                </div>
                                                <!-- Status Indicator -->
                                                <div class="status-indicator">
                                                    @if($klassSubject['student_count'] > 0)
                                                        <span class="status-text">Active</span>
                                                        <div class="status-dot active"></div>
                                                    @else
                                                        <span class="status-text">No enrollment</span>
                                                        <div class="status-dot inactive"></div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="col-md-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="bx bx-chalkboard display-1 text-muted" style="opacity: 0.5;"></i>
                </div>
                <h5 class="text-muted">No Core Subjects Found</h5>
                <p class="text-muted mb-4">
                    There are no core subjects for the selected year. Core subjects are created during year rollover.
                </p>
                <button disabled type="button" class="btn btn-sm btn-primary" onclick="$('#graduationYear').val('').trigger('change')">
                    <i class="bx bx-refresh me-1"></i>View All Years
                </button>
            </div>
        </div>
    </div>
@endif

<script>
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>