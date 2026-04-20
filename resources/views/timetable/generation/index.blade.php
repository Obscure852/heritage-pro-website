@extends('layouts.master')
@section('title')
    Generate Timetable - {{ $timetable->name }}
@endsection
@section('css')
    <style>
        .generation-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .generation-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .generation-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .generation-header p {
            margin: 4px 0 0 0;
            opacity: 0.85;
            font-size: 14px;
        }

        .generation-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        .checklist-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .checklist-item:last-child {
            border-bottom: none;
        }

        .checklist-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .checklist-icon.success {
            background: #dcfce7;
            color: #16a34a;
        }

        .checklist-icon.danger {
            background: #fef2f2;
            color: #dc2626;
        }

        .checklist-icon.warning {
            background: #fefce8;
            color: #ca8a04;
        }

        .checklist-icon.info {
            background: #eff6ff;
            color: #2563eb;
        }

        .checklist-text {
            flex: 1;
        }

        .checklist-text strong {
            display: block;
            color: #1f2937;
            font-size: 14px;
        }

        .checklist-text small {
            color: #6b7280;
            font-size: 12px;
        }

        .btn-generate {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 14px 28px;
            border-radius: 3px;
            font-size: 16px;
            font-weight: 500;
            border: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-generate:hover:not(:disabled) {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-generate:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-stop {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 14px 28px;
            border-radius: 3px;
            font-size: 16px;
            font-weight: 500;
            border: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-stop:hover:not(:disabled) {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .btn-stop:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-generate.loading .btn-text {
            display: none;
        }

        .btn-generate.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .progress-section {
            margin-top: 24px;
        }

        .generation-stage {
            position: relative;
            margin-top: 12px;
            padding-top: 6px;
        }

        .generation-stage .stage-line {
            position: absolute;
            left: 0;
            right: 0;
            top: 20px;
            height: 2px;
            background: #e5e7eb;
            z-index: 0;
        }

        .generation-stage .stage-points {
            display: flex;
            justify-content: space-between;
            position: relative;
            z-index: 1;
        }

        .generation-stage .stage-point {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            min-width: 120px;
        }

        .generation-stage .stage-point .dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid #9ca3af;
            background: #fff;
            transition: all 0.2s ease;
        }

        .generation-stage .stage-point small {
            color: #6b7280;
            font-size: 12px;
        }

        .generation-stage .stage-point.active .dot {
            border-color: #2563eb;
            background: #2563eb;
            animation: stagePulse 1.1s ease-in-out infinite;
        }

        .generation-stage .stage-point.active small {
            color: #1d4ed8;
            font-weight: 600;
        }

        .generation-stage .stage-point.done .dot {
            border-color: #16a34a;
            background: #16a34a;
            animation: none;
        }

        .generation-stage .stage-point.done small {
            color: #166534;
        }

        .generation-stage .stage-activity {
            margin-top: 8px;
            font-size: 12px;
            color: #4b5563;
        }

        @keyframes stagePulse {
            0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.45); }
            100% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
        }

        .progress {
            height: 30px;
            border-radius: 3px;
        }

        .progress-bar {
            font-size: 13px;
            font-weight: 500;
            line-height: 30px;
            transition: width 0.5s ease;
        }

        .elapsed-time {
            margin-top: 8px;
            color: #4b5563;
            font-size: 13px;
            font-weight: 500;
        }

        .result-section {
            margin-top: 20px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Conflict details toggle */
        .conflict-toggle {
            color: #92400e;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .conflict-toggle:hover {
            color: #78350f;
            text-decoration: underline;
        }

        .conflict-toggle .fa-chevron-down {
            transition: transform 0.2s;
            font-size: 10px;
        }

        .conflict-toggle[aria-expanded="true"] .fa-chevron-down {
            transform: rotate(180deg);
        }

        #conflict-details ul {
            max-height: 300px;
            overflow-y: auto;
            padding-left: 20px;
            margin: 0;
        }

        #conflict-details li {
            font-size: 13px;
            color: #92400e;
            padding: 3px 0;
            line-height: 1.4;
        }

        #conflict-details li:first-child {
            font-weight: 600;
            color: #78350f;
        }

    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="generation-container">
            <div class="generation-header">
                <h4>Generate Timetable</h4>
                <p>{{ $timetable->name }}</p>
            </div>

            <div class="generation-body">
                {{-- Help Text --}}
                <div class="help-text">
                    <div class="help-title">How it works</div>
                    <div class="help-content">
                        The system uses a genetic algorithm to automatically generate a complete timetable.
                        This typically takes 30-120 seconds depending on the number of classes and constraints.
                        Existing non-locked slots will be replaced. Locked slots are preserved.
                    </div>
                </div>

                {{-- Readiness Checklist --}}
                <h5 class="section-title">Readiness Checklist</h5>

                <div class="checklist-item">
                    @if ($hasAllocations)
                        <div class="checklist-icon success"><i class="fas fa-check"></i></div>
                        <div class="checklist-text">
                            <strong>Block Allocations</strong>
                            <small>Configured — ready for generation</small>
                        </div>
                    @else
                        <div class="checklist-icon danger"><i class="fas fa-times"></i></div>
                        <div class="checklist-text">
                            <strong>Block Allocations</strong>
                            <small>Not configured — <a href="{{ route('timetable.period-settings.index') }}">set up in Period Settings</a></small>
                        </div>
                    @endif
                </div>

                <div class="checklist-item">
                    @if ($hasConstraints)
                        <div class="checklist-icon success"><i class="fas fa-check"></i></div>
                        <div class="checklist-text">
                            <strong>Constraints</strong>
                            <small>Configured — teacher availability, preferences, etc.</small>
                        </div>
                    @else
                        <div class="checklist-icon warning"><i class="fas fa-exclamation"></i></div>
                        <div class="checklist-text">
                            <strong>Constraints</strong>
                            <small>Not configured (optional) — <a href="{{ route('timetable.constraints.index', $timetable) }}">add constraints</a> for better results</small>
                        </div>
                    @endif
                </div>

                @if ($existingSlotCount > 0)
                    <div class="checklist-item">
                        <div class="checklist-icon info"><i class="fas fa-info"></i></div>
                        <div class="checklist-text">
                            <strong>Existing Slots</strong>
                            <small>{{ $existingSlotCount }} unlocked slot(s) will be replaced by the generated timetable</small>
                        </div>
                    </div>
                @endif

                @if ($lockedSlotCount > 0)
                    <div class="checklist-item">
                        <div class="checklist-icon info"><i class="fas fa-lock"></i></div>
                        <div class="checklist-text">
                            <strong>Locked Slots</strong>
                            <small>{{ $lockedSlotCount }} locked slot(s) will be preserved during generation</small>
                        </div>
                    </div>
                @endif

                {{-- Generate / Stop Buttons --}}
                <div class="mt-4 text-center d-flex justify-content-center gap-3">
                    <button type="button"
                            id="btn-generate"
                            class="btn btn-generate"
                            {{ !$hasAllocations ? 'disabled' : '' }}
                            onclick="startGeneration()"
                            title="{{ !$hasAllocations ? 'Configure block allocations first' : 'Generate a complete timetable' }}">
                        <span class="btn-text"><i class="fas fa-magic"></i> Generate Timetable</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            <span id="btn-spinner-text">Starting..</span>
                        </span>
                    </button>
                    <button type="button"
                            id="btn-stop"
                            class="btn btn-stop"
                            style="display: none;"
                            onclick="stopGeneration()">
                        <i class="fas fa-stop-circle"></i> Stop Generation
                    </button>
                </div>

                {{-- Progress Section (hidden by default) --}}
                <div id="progress-section" class="progress-section" style="display: none;">
                    <h5 class="section-title">
                        Generation Progress
                        <span id="generation-status-badge" class="badge bg-secondary ms-2" style="font-size: 12px;">idle</span>
                    </h5>
                    <div class="progress">
                        <div id="progress-bar"
                             class="progress-bar bg-primary"
                             role="progressbar"
                             style="width: 0%"
                             aria-valuenow="0"
                             aria-valuemin="0"
                             aria-valuemax="100">
                            0%
                        </div>
                    </div>
                    <p id="progress-message" class="mt-2 text-muted mb-0" style="font-size: 13px;"></p>
                    <div id="generation-stage" class="generation-stage" style="display: none;">
                        <div class="stage-line"></div>
                        <div class="stage-points">
                            <div class="stage-point" data-phase="starting">
                                <span class="dot"></span>
                                <small>Starting..</small>
                            </div>
                            <div class="stage-point" data-phase="generating">
                                <span class="dot"></span>
                                <small>Generating</small>
                            </div>
                            <div class="stage-point" data-phase="almost">
                                <span class="dot"></span>
                                <small>Almost there</small>
                            </div>
                        </div>
                        <div id="generation-activity-text" class="stage-activity">Preparing data and constraints...</div>
                    </div>
                    <div class="elapsed-time">
                        Elapsed time: <span id="elapsed-time">00:00</span>
                    </div>
                </div>

                {{-- Result Section (hidden by default) --}}
                <div id="result-section" class="result-section" style="display: none;">
                    {{-- Full Success --}}
                    <div id="result-success" style="display: none;">
                        <div class="alert alert-success">
                            <h6 class="alert-heading"><i class="fas fa-check-circle me-1"></i> Timetable Generated Successfully</h6>
                            <p id="result-message" class="mb-2"></p>
                            <a href="{{ route('timetable.slots.grid', $timetable) }}" class="btn btn-sm btn-success">
                                <i class="fas fa-th me-1"></i> View Timetable Grid
                            </a>
                        </div>
                    </div>

                    {{-- Partial Success (completed with conflicts) --}}
                    <div id="result-partial" style="display: none;">
                        <div class="alert alert-warning" style="border-left: 4px solid #f59e0b;">
                            <h6 class="alert-heading" style="color: #92400e;">
                                <i class="fas fa-exclamation-circle me-1"></i> Timetable Generated with Warnings
                            </h6>
                            <p id="partial-message" class="mb-2" style="color: #92400e;"></p>

                            {{-- Collapsible conflict details --}}
                            <a class="conflict-toggle" data-bs-toggle="collapse" href="#conflict-details"
                               role="button" aria-expanded="false" aria-controls="conflict-details">
                                Show conflict details <i class="fas fa-chevron-down"></i>
                            </a>
                            <div class="collapse mt-2" id="conflict-details">
                                <ul id="partial-error-list"></ul>
                            </div>

                            <div class="help-text mt-3" style="background: #fffbeb; border-left-color: #f59e0b;">
                                <div class="help-content" style="color: #92400e;">
                                    All slots have been saved to the grid, including those with conflicts.
                                    Open the grid to review, lock down the good slots, then adjust constraints
                                    and regenerate to resolve the remaining conflicts.
                                </div>
                            </div>

                            <div class="mt-3 d-flex gap-2 flex-wrap">
                                <a href="{{ route('timetable.slots.grid', $timetable) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-th me-1"></i> View Timetable Grid
                                </a>
                                <a href="{{ route('timetable.constraints.index', $timetable) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-sliders-h me-1"></i> Adjust Constraints
                                </a>
                                <a href="{{ route('timetable.period-settings.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-cog me-1"></i> Block Allocations
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Cancelled (stopped mid-way) --}}
                    <div id="result-cancelled" style="display: none;">
                        <div class="alert alert-warning" style="border-left: 4px solid #f59e0b;">
                            <h6 class="alert-heading" style="color: #92400e;">
                                <i class="fas fa-stop-circle me-1"></i> Generation Stopped
                            </h6>
                            <p id="cancelled-message" class="mb-2" style="color: #92400e;"></p>
                            <div class="help-text mt-3" style="background: #fffbeb; border-left-color: #f59e0b;">
                                <div class="help-content" style="color: #92400e;">
                                    The best solution found so far has been saved. You can view the grid to review and
                                    manually adjust, or make corrections and regenerate.
                                </div>
                            </div>
                            <div class="mt-3 d-flex gap-2 flex-wrap">
                                <a href="{{ route('timetable.slots.grid', $timetable) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-th me-1"></i> View Timetable Grid
                                </a>
                                <a href="{{ route('timetable.constraints.index', $timetable) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-sliders-h me-1"></i> Adjust Constraints
                                </a>
                                <a href="{{ route('timetable.period-settings.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-cog me-1"></i> Block Allocations
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Full Failure (pre-flight or exception) --}}
                    <div id="result-failure" style="display: none;">
                        <div class="alert alert-danger">
                            <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-1"></i> Generation Failed</h6>
                            <ul id="error-list" class="mb-2"></ul>
                            <div class="help-text mt-2" style="background: #fff5f5; border-left-color: #dc2626;">
                                <div class="help-content">
                                    Review your block allocations and constraints to resolve these issues.
                                    Ensure teacher workloads don't exceed available time slots and that block allocations
                                    don't exceed the total periods available per cycle.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        var timetableId = {{ $timetable->id }};
        var pollInterval = null;
        var timerInterval = null;
        var isGenerating = false;
        var generationStartMs = null;
        var timerStorageKey = 'timetable_generation_started_' + timetableId;

        // Check if generation is already in progress on page load
        $(document).ready(function () {
            var initialStatus = @json($status);
            if (initialStatus && initialStatus.status) {
                var activeStatuses = ['queued', 'loading', 'generating', 'saving'];
                if (activeStatuses.indexOf(initialStatus.status) !== -1) {
                    isGenerating = true;
                    $('#progress-section').show();
                    $('#btn-generate').prop('disabled', true).addClass('loading');
                    $('#btn-stop').show();
                    updateProgressUI(initialStatus);
                    restoreOrStartTimer();
                    startPolling();
                } else if (initialStatus.status === 'cancelled') {
                    $('#progress-section').show();
                    updateProgressUI(initialStatus);
                    showCancelled(initialStatus);
                } else if (initialStatus.status === 'completed') {
                    $('#progress-section').show();
                    updateProgressUI(initialStatus);
                    showSuccess(initialStatus);
                } else if (initialStatus.status === 'completed_with_conflicts') {
                    $('#progress-section').show();
                    updateProgressUI(initialStatus);
                    showPartial(initialStatus);
                } else if (initialStatus.status === 'failed') {
                    $('#progress-section').show();
                    updateProgressUI(initialStatus);
                    showFailure(initialStatus);
                }
            }
        });

        function startGeneration() {
            if (isGenerating) return;
            var clickStartMs = Date.now();

            var existingCount = {{ $existingSlotCount }};
            if (existingCount > 0) {
                Swal.fire({
                    title: 'Replace Existing Slots?',
                    html: 'This will replace <strong>' + existingCount + '</strong> existing non-locked slots.<br>Locked slots will be preserved.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Generate',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#3b82f6'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        doGenerate(clickStartMs);
                    }
                });
            } else {
                doGenerate(clickStartMs);
            }
        }

        function doGenerate(startMs) {
            isGenerating = true;
            $('#progress-section').show();
            $('#result-section').hide();
            $('#result-success, #result-partial, #result-failure, #result-cancelled').hide();
            $('#btn-generate').prop('disabled', true).addClass('loading');
            $('#btn-stop').show();
            $('#progress-bar').css('width', '0%').text('0%')
                .removeClass('bg-success bg-danger bg-warning')
                .addClass('bg-primary progress-bar-striped progress-bar-animated');
            $('#progress-message').text('');
            $('#generation-status-badge').text('Starting..').attr('class', 'badge bg-info ms-2').css('font-size', '12px');
            $('#btn-spinner-text').text('Starting..');
            applyGenerationPhase('starting');
            startTimer(startMs || Date.now());

            $.ajax({
                url: '{{ route('timetable.generation.generate') }}',
                method: 'POST',
                data: {timetable_id: timetableId},
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function (response) {
                    if (response.success) {
                        startPolling();
                    }
                },
                error: function (xhr) {
                    isGenerating = false;
                    stopTimer(true);
                    $('#btn-generate').prop('disabled', false).removeClass('loading');
                    $('#btn-stop').hide();
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to start generation';
                    Swal.fire('Error', msg, 'error');
                }
            });
        }

        function startPolling() {
            pollInterval = setInterval(function () {
                $.get('{{ route("timetable.generation.status", ["timetable" => "__ID__"]) }}'.replace('__ID__', timetableId), function (data) {
                    updateProgressUI(data);

                    if (data.status === 'completed') {
                        clearInterval(pollInterval);
                        isGenerating = false;
                        showSuccess(data);
                    } else if (data.status === 'completed_with_conflicts') {
                        clearInterval(pollInterval);
                        isGenerating = false;
                        showPartial(data);
                    } else if (data.status === 'cancelled') {
                        clearInterval(pollInterval);
                        isGenerating = false;
                        showCancelled(data);
                    } else if (data.status === 'failed') {
                        clearInterval(pollInterval);
                        isGenerating = false;
                        showFailure(data);
                    }
                }).fail(function () {
                    // Ignore transient poll failures
                });
            }, 2000);
        }

        function updateProgressUI(data) {
            var pct = data.percent || 0;
            $('#progress-bar').css('width', pct + '%').text(pct + '%');
            $('#progress-message').text(data.message || '');

            var phase = resolveGenerationPhase(data.status, pct);
            if (phase) {
                $('#generation-status-badge')
                    .text(phase.badgeText)
                    .attr('class', 'badge bg-' + phase.badgeColor + ' ms-2')
                    .css('font-size', '12px');
                $('#btn-spinner-text').text(phase.buttonText);
                applyGenerationPhase(phase.key);
            } else {
                var displayStatus = data.status === 'completed_with_conflicts' ? 'partial' : data.status;
                $('#generation-status-badge').text(displayStatus)
                    .attr('class', 'badge bg-' + getStatusColor(data.status) + ' ms-2')
                    .css('font-size', '12px');
            }

            var animatedStatuses = ['generating', 'loading', 'saving', 'queued'];
            if (animatedStatuses.indexOf(data.status) !== -1) {
                $('#progress-bar').addClass('progress-bar-striped progress-bar-animated');
            }
        }

        function resolveGenerationPhase(status, percent) {
            var pct = parseInt(percent || 0);

            if (status === 'queued' || status === 'loading') {
                return {
                    key: 'starting',
                    badgeText: 'Starting..',
                    buttonText: 'Starting..',
                    badgeColor: 'info'
                };
            }

            if (status === 'saving' || (status === 'generating' && pct >= 80)) {
                return {
                    key: 'almost',
                    badgeText: 'Almost there',
                    buttonText: 'Almost there',
                    badgeColor: 'warning'
                };
            }

            if (status === 'generating') {
                return {
                    key: 'generating',
                    badgeText: 'Generating',
                    buttonText: 'Generating',
                    badgeColor: 'primary'
                };
            }

            return null;
        }

        function applyGenerationPhase(phaseKey) {
            var order = ['starting', 'generating', 'almost'];
            var phaseIndex = order.indexOf(phaseKey);
            if (phaseIndex < 0) {
                return;
            }

            $('#generation-stage').show();

            $('.stage-point').each(function () {
                var point = $(this);
                var idx = order.indexOf(point.data('phase'));
                point.removeClass('active done');
                if (idx < phaseIndex) {
                    point.addClass('done');
                } else if (idx === phaseIndex) {
                    point.addClass('active');
                }
            });

            var activityText = 'Preparing data and constraints...';
            if (phaseKey === 'generating') {
                activityText = 'Exploring timetable combinations and resolving conflicts...';
            } else if (phaseKey === 'almost') {
                activityText = 'Finalizing the best solution and writing results...';
            }
            $('#generation-activity-text').text(activityText);
        }

        function showSuccess(data) {
            $('#progress-bar').removeClass('progress-bar-striped progress-bar-animated bg-primary').addClass('bg-success');
            $('#result-section').show();
            $('#result-success').show();
            $('#result-partial, #result-failure, #result-cancelled').hide();
            $('#result-message').text(data.message || 'Timetable generated successfully!');
            applyGenerationPhase('almost');
            $('.stage-point[data-phase="almost"]').removeClass('active').addClass('done');
            stopTimer(false);
            $('#btn-generate').prop('disabled', false).removeClass('loading');
            $('#btn-stop').hide();
        }

        function showPartial(data) {
            $('#progress-bar').removeClass('progress-bar-striped progress-bar-animated bg-primary').addClass('bg-warning');
            $('#result-section').show();
            $('#result-partial').show();
            $('#result-success, #result-failure, #result-cancelled').hide();
            $('#partial-message').text(data.message || 'Timetable partially generated with some conflicts.');

            var errorList = $('#partial-error-list').empty();
            if (data.errors && data.errors.length > 0) {
                data.errors.forEach(function (err) {
                    errorList.append('<li>' + $('<span>').text(err).html() + '</li>');
                });
            }
            applyGenerationPhase('almost');
            stopTimer(false);
            $('#btn-generate').prop('disabled', false).removeClass('loading');
            $('#btn-stop').hide();
        }

        function showFailure(data) {
            $('#progress-bar').removeClass('progress-bar-striped progress-bar-animated bg-primary').addClass('bg-danger');
            $('#result-section').show();
            $('#result-failure').show();
            $('#result-success, #result-partial, #result-cancelled').hide();
            var errorList = $('#error-list').empty();
            if (data.errors && data.errors.length > 0) {
                data.errors.forEach(function (err) {
                    errorList.append('<li class="mb-1">' + $('<span>').text(err).html() + '</li>');
                });
            } else {
                errorList.append('<li>An unexpected error occurred. Check the server logs.</li>');
            }
            stopTimer(false);
            $('#btn-generate').prop('disabled', false).removeClass('loading');
            $('#btn-stop').hide();
        }

        function showCancelled(data) {
            $('#progress-bar').removeClass('progress-bar-striped progress-bar-animated bg-primary').addClass('bg-warning');
            $('#result-section').show();
            $('#result-cancelled').show();
            $('#result-success, #result-partial, #result-failure').hide();
            $('#cancelled-message').text(data.message || 'Generation was stopped. The best partial solution has been saved.');
            $('#generation-status-badge').text('Stopped').attr('class', 'badge bg-warning ms-2').css('font-size', '12px');
            stopTimer(false);
            $('#btn-generate').prop('disabled', false).removeClass('loading');
            $('#btn-stop').hide();
        }

        function stopGeneration() {
            Swal.fire({
                title: 'Stop Generation?',
                text: 'The algorithm will stop and save the best solution found so far. You can review the result and regenerate later.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Stop Now',
                cancelButtonText: 'Keep Running',
                confirmButtonColor: '#dc2626'
            }).then(function (result) {
                if (result.isConfirmed) {
                    $('#btn-stop').prop('disabled', true);
                    $.ajax({
                        url: '{{ route("timetable.generation.cancel", ["timetable" => "__ID__"]) }}'.replace('__ID__', timetableId),
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        success: function () {
                            $('#btn-spinner-text').text('Stopping..');
                        },
                        error: function () {
                            $('#btn-stop').prop('disabled', false);
                            Swal.fire('Error', 'Failed to send cancellation request.', 'error');
                        }
                    });
                }
            });
        }

        function restoreOrStartTimer() {
            var stored = parseInt(localStorage.getItem(timerStorageKey), 10);
            if (!isNaN(stored) && stored > 0) {
                startTimer(stored);
                return;
            }

            startTimer(Date.now());
        }

        function startTimer(startMs) {
            generationStartMs = startMs;
            localStorage.setItem(timerStorageKey, String(startMs));

            if (timerInterval) {
                clearInterval(timerInterval);
            }

            updateElapsedTime();
            timerInterval = setInterval(updateElapsedTime, 1000);
        }

        function stopTimer(resetDisplay) {
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }

            generationStartMs = null;
            localStorage.removeItem(timerStorageKey);

            if (resetDisplay) {
                $('#elapsed-time').text('00:00');
            }
        }

        function updateElapsedTime() {
            if (!generationStartMs) {
                $('#elapsed-time').text('00:00');
                return;
            }

            var elapsedSeconds = Math.max(0, Math.floor((Date.now() - generationStartMs) / 1000));
            var hours = Math.floor(elapsedSeconds / 3600);
            var minutes = Math.floor((elapsedSeconds % 3600) / 60);
            var seconds = elapsedSeconds % 60;

            if (hours > 0) {
                $('#elapsed-time').text(
                    String(hours).padStart(2, '0') + ':' +
                    String(minutes).padStart(2, '0') + ':' +
                    String(seconds).padStart(2, '0')
                );
                return;
            }

            $('#elapsed-time').text(
                String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0')
            );
        }

        function getStatusColor(status) {
            switch (status) {
                case 'queued':                    return 'secondary';
                case 'loading':                   return 'info';
                case 'generating':                return 'primary';
                case 'saving':                    return 'warning';
                case 'completed':                 return 'success';
                case 'completed_with_conflicts':  return 'warning';
                case 'cancelled':                 return 'warning';
                case 'failed':                    return 'danger';
                default:                          return 'light';
            }
        }

    </script>
@endsection
