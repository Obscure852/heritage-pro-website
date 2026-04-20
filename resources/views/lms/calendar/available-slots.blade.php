@extends('layouts.master')

@section('title', 'Book Appointment - ' . $schedule->title)

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ url()->previous() }}" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-8">
            <h4 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>{{ $schedule->title }}</h4>
            <p class="text-muted mb-0">with {{ $schedule->user->name }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <!-- Schedule Info -->
            <div class="card shadow-sm">
                <div class="card-body">
                    @if($schedule->description)
                        <p>{{ $schedule->description }}</p>
                    @endif

                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-clock me-2 text-primary"></i>
                            {{ $schedule->slot_duration }} minute sessions
                        </li>
                        @if($schedule->location)
                            <li class="mb-2">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                {{ $schedule->location }}
                            </li>
                        @endif
                        @if($schedule->meeting_url)
                            <li class="mb-2">
                                <i class="fas fa-video me-2 text-primary"></i>
                                Virtual meeting available
                            </li>
                        @endif
                        @if($schedule->course)
                            <li>
                                <i class="fas fa-book me-2 text-primary"></i>
                                {{ $schedule->course->title }}
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Available Slots -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Available Time Slots</h6>
                </div>
                <div class="card-body">
                    @if(empty($dates))
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                            <p>No available slots in the next 14 days.</p>
                        </div>
                    @else
                        @foreach($dates as $dateStr => $slots)
                            @php $dateObj = \Carbon\Carbon::parse($dateStr); @endphp
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2">
                                    <i class="fas fa-calendar-day me-2"></i>
                                    {{ $dateObj->format('l, F j, Y') }}
                                </h6>
                                <div class="row">
                                    @foreach($slots as $slot)
                                        <div class="col-md-4 col-sm-6 mb-2">
                                            <button class="btn btn-outline-primary w-100 slot-btn"
                                                    data-start="{{ $slot['start']->toIso8601String() }}"
                                                    data-display="{{ $slot['start']->format('g:i A') }} - {{ $slot['end']->format('g:i A') }}">
                                                {{ $slot['start']->format('g:i A') }}
                                                @if($slot['available'] < $schedule->max_bookings_per_slot)
                                                    <span class="badge bg-warning ms-1">{{ $slot['available'] }} left</span>
                                                @endif
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('lms.calendar.book-appointment', $schedule) }}" method="POST">
                @csrf
                <input type="hidden" name="start_time" id="selectedStartTime">

                <div class="modal-header">
                    <h5 class="modal-title">Confirm Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Selected Time:</strong>
                        <span id="selectedTimeDisplay"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes for instructor (optional)</label>
                        <textarea name="student_notes" class="form-control" rows="3"
                                  placeholder="Let the instructor know what you'd like to discuss..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-1"></i>Confirm Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.slot-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('selectedStartTime').value = this.dataset.start;
        document.getElementById('selectedTimeDisplay').textContent = this.dataset.display;
        new bootstrap.Modal(document.getElementById('bookingModal')).show();
    });
});
</script>
@endpush
@endsection
