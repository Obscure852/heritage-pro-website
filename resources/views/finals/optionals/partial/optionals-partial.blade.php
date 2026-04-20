@if($groupedData->isNotEmpty())
    <div class="p-2">
        <div class="accordion" id="optionalSubjectsAccordion">
            @foreach($groupedData as $groupName => $groupData)
                @php
                    $accordionId = 'accordion-' . $loop->index;
                    $isFirst = $loop->first;
                @endphp
                
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-{{ $accordionId }}">
                        <button class="accordion-button {{ $isFirst ? '' : 'collapsed' }}" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse-{{ $accordionId }}" 
                                aria-expanded="{{ $isFirst ? 'true' : 'false' }}" 
                                aria-controls="collapse-{{ $accordionId }}">
                            <div class="d-flex align-items-center w-100">
                                <div class="me-3">
                                    <div class="fw-bold">{{ $groupName }}</div>
                                </div>
                                <div class="ms-auto me-3">
                                    <span class="badge bg-primary me-2">{{ $groupData['total_subjects'] }} class{{ $groupData['total_subjects'] !== 1 ? 'es' : '' }}</span>
                                    <span class="badge bg-success me-2">{{ $groupData['total_students'] }} students</span>
                                    <small class="text-muted">Optional Subject</small>
                                </div>
                            </div>
                        </button>
                    </h2>
                    
                    <div id="collapse-{{ $accordionId }}" 
                         class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}" 
                         aria-labelledby="heading-{{ $accordionId }}" 
                         data-bs-parent="#optionalSubjectsAccordion">
                        <div class="accordion-body">
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($groupData['optional_subjects'] as $index => $subject)
                                    <div class="card" style="width: 320px; height: 220px;">
                                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                                            <div>
                                                <h6 class="card-title mb-0 fw-bold">{{ $subject['name'] }}</h6>
                                                <span class="badge bg-info badge-sm">{{ $groupData['grade_subject']->grade->name ?? 'Unknown' }}</span>
                                            </div>
                                            <div class="text-end">
                                                    <span class="badge bg-warning badge-sm">Optional</span>
                                            </div>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    @if($subject['teacher'] && $subject['teacher'] !== 'Not Assigned')
                                                        <i class="bx bxs-user text-primary me-2"></i>
                                                        <span class="text-primary">{{ $subject['teacher'] }}</span>
                                                    @else
                                                        <i class="bx bxs-user-x text-warning me-2"></i>
                                                        <span class="text-warning">Not Assigned</span>
                                                    @endif
                                                </div>
                                                
                                                <div class="d-flex align-items-center mb-2">
                                                    @if(isset($subject['venue']) && $subject['venue'] !== 'Not Assigned')
                                                        <i class="bx bxs-map-pin text-info me-2"></i>
                                                        <span class="text-info">{{ $subject['venue'] }}</span>
                                                    @else
                                                        <i class="bx bxs-map-pin text-muted me-2"></i>
                                                        <span class="text-muted">Not Assigned</span>
                                                    @endif
                                                </div>

                                                <div class="d-flex align-items-center mb-2">
                                                    @if($subject['total_students'] > 0)
                                                        <i class="bx bxs-group text-success me-2"></i>
                                                        <span class="text-success">{{ $subject['total_students'] }} students</span>
                                                    @else
                                                        <i class="bx bxs-group text-muted me-2"></i>
                                                        <span class="text-muted">No Students</span>
                                                    @endif
                                                </div>

                                                <div class="d-flex align-items-center gap-2 mt-3">
                                                    <a href="{{ route('finals.optionals.show-students', ['id' => $subject['id'], 'finals_context' => $finalsDefinition->context]) }}" 
                                                       class="btn btn-sm btn-primary"
                                                       title="View Details">
                                                        <i class="bx bx-show me-1"></i>
                                                    </a>
                                                </div>

                                                <!-- Status Indicator -->
                                                <div class="status-indicator">
                                                    @if($subject['total_students'] > 0)
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
    <div class="text-center py-5">
        <div class="mb-3">
            <i class="bx bx-book-open display-1 text-muted" style="opacity: 0.5;"></i>
        </div>
        <h5 class="text-muted">No Optional Subjects Found</h5>
        <p class="text-muted mb-4">
            @if(request('year'))
                No optional subjects found for {{ request('year') }}.
            @else
                No optional subjects have been set up yet.
            @endif
        </p>
        <button type="button" class="btn btn-sm btn-primary" disabled>
            <i class="bx bx-plus me-1"></i>Setup Optional Subjects
        </button>
    </div>
@endif

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

        .status-dot.inactive {
            background-color: #fd7e14;
            animation: pulse-orange 2s infinite;
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

        @keyframes pulse-orange {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(253, 126, 20, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(253, 126, 20, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(253, 126, 20, 0);
            }
        }
</style>
