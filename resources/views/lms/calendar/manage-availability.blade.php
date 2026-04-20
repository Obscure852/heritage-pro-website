@extends('layouts.master')

@section('title', 'Manage Availability')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h4 class="mb-0"><i class="fas fa-clock me-2"></i>Manage Availability</h4>
            <p class="text-muted mb-0">Set up office hours and appointment slots for students</p>
        </div>
        <div class="col-md-4 text-md-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createScheduleModal">
                <i class="fas fa-plus me-1"></i>Create Schedule
            </button>
        </div>
    </div>

    <div class="row">
        @forelse($schedules as $schedule)
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            {{ $schedule->title }}
                            @if(!$schedule->is_active)
                                <span class="badge bg-secondary ms-1">Inactive</span>
                            @endif
                        </h6>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#overrideModal{{ $schedule->id }}">Add Override</a></li>
                                <li><a class="dropdown-item text-danger" href="#">Delete</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($schedule->description)
                            <p class="small text-muted">{{ $schedule->description }}</p>
                        @endif

                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Duration</small><br>
                                <strong>{{ $schedule->slot_duration }} min</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Buffer</small><br>
                                <strong>{{ $schedule->buffer_time }} min</strong>
                            </div>
                        </div>

                        @if($schedule->location || $schedule->meeting_url)
                            <div class="mb-3">
                                @if($schedule->location)
                                    <small><i class="fas fa-map-marker-alt me-1"></i>{{ $schedule->location }}</small><br>
                                @endif
                                @if($schedule->meeting_url)
                                    <small><i class="fas fa-video me-1"></i>Virtual meeting enabled</small>
                                @endif
                            </div>
                        @endif

                        <h6 class="border-bottom pb-2">Weekly Schedule</h6>
                        <ul class="list-unstyled small">
                            @foreach($schedule->windows->groupBy('day_of_week') as $day => $windows)
                                <li class="mb-1">
                                    <strong>{{ \App\Models\Lms\AvailabilityWindow::$daysOfWeek[$day] }}:</strong>
                                    @foreach($windows as $window)
                                        {{ $window->formatted_time_range }}@if(!$loop->last), @endif
                                    @endforeach
                                </li>
                            @endforeach
                        </ul>

                        @if($schedule->course)
                            <div class="mt-3">
                                <span class="badge bg-info">
                                    <i class="fas fa-book me-1"></i>{{ $schedule->course->title }}
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-white">
                        <small class="text-muted">
                            {{ $schedule->appointments()->where('status', 'confirmed')->where('start_time', '>', now())->count() }} upcoming appointments
                        </small>
                    </div>
                </div>
            </div>

            <!-- Override Modal for this schedule -->
            <div class="modal fade" id="overrideModal{{ $schedule->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('lms.calendar.store-override', $schedule) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Add Schedule Override</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="date" class="form-control" required min="{{ now()->toDateString() }}">
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_available" class="form-check-input" value="1" checked id="isAvailable{{ $schedule->id }}">
                                        <label class="form-check-label">Available (custom hours)</label>
                                    </div>
                                    <small class="text-muted">Uncheck to block this day entirely</small>
                                </div>
                                <div class="row custom-hours">
                                    <div class="col-6">
                                        <label class="form-label">Start Time</label>
                                        <input type="time" name="start_time" class="form-control">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">End Time</label>
                                        <input type="time" name="end_time" class="form-control">
                                    </div>
                                </div>
                                <div class="mb-3 mt-3">
                                    <label class="form-label">Reason (optional)</label>
                                    <input type="text" name="reason" class="form-control" placeholder="e.g., Holiday, Out of office">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Override</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-clock fa-4x text-muted mb-3"></i>
                        <h5>No Availability Schedules</h5>
                        <p class="text-muted">Create a schedule to let students book appointments with you.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createScheduleModal">
                            <i class="fas fa-plus me-1"></i>Create Schedule
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Create Schedule Modal -->
<div class="modal fade" id="createScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('lms.calendar.store-availability') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create Availability Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g., Office Hours">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Course (optional)</label>
                            <select name="course_id" class="form-select">
                                <option value="">All Courses</option>
                                <!-- Courses would be populated here -->
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Slot Duration (minutes)</label>
                            <input type="number" name="slot_duration" class="form-control" value="30" min="15" max="120">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Buffer Time (minutes)</label>
                            <input type="number" name="buffer_time" class="form-control" value="0" min="0" max="60">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Max per Slot</label>
                            <input type="number" name="max_bookings_per_slot" class="form-control" value="1" min="1" max="20">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Meeting URL</label>
                            <input type="url" name="meeting_url" class="form-control" placeholder="https://...">
                        </div>
                    </div>

                    <h6 class="mt-4 mb-3">Weekly Availability</h6>
                    <div id="windowsContainer">
                        <div class="row mb-2 window-row">
                            <div class="col-md-4">
                                <select name="windows[0][day_of_week]" class="form-select">
                                    @foreach(\App\Models\Lms\AvailabilityWindow::$daysOfWeek as $day => $name)
                                        <option value="{{ $day }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="time" name="windows[0][start_time]" class="form-control" value="09:00">
                            </div>
                            <div class="col-md-3">
                                <input type="time" name="windows[0][end_time]" class="form-control" value="17:00">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger remove-window" disabled>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="addWindow()">
                        <i class="fas fa-plus me-1"></i>Add Time Window
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let windowIndex = 1;

function addWindow() {
    const container = document.getElementById('windowsContainer');
    const html = `
        <div class="row mb-2 window-row">
            <div class="col-md-4">
                <select name="windows[${windowIndex}][day_of_week]" class="form-select">
                    @foreach(\App\Models\Lms\AvailabilityWindow::$daysOfWeek as $day => $name)
                        <option value="{{ $day }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="time" name="windows[${windowIndex}][start_time]" class="form-control" value="09:00">
            </div>
            <div class="col-md-3">
                <input type="time" name="windows[${windowIndex}][end_time]" class="form-control" value="17:00">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger remove-window" onclick="this.closest('.window-row').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    windowIndex++;
}
</script>
@endpush
@endsection
