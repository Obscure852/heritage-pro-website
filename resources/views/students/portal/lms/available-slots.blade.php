@extends('layouts.master-student-portal')
@section('title', 'Book Appointment')
@section('css')
    <style>
        :root {
            --primary: #4e73df;
            --primary-dark: #3a5bc7;
            --secondary: #36b9cc;
            --success: #1cc88a;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --purple: #6f42c1;
            --gray-100: #f8f9fc;
            --gray-200: #eaecf4;
            --gray-600: #6c757d;
            --gray-800: #2d3748;
        }

        .slots-container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Content Cards */
        .content-card {
            background: #fff;
            border-radius: 3px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .content-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .content-card-header h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-800);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .content-card-header h3 i {
            color: var(--primary);
        }

        .content-card-body {
            padding: 1.5rem;
        }

        /* Schedule Info */
        .schedule-info {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff;
            padding: 1.5rem;
            border-radius: 3px;
            margin-bottom: 1.5rem;
        }

        .schedule-info h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .schedule-info .meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .schedule-info .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .schedule-info .meta-item i {
            opacity: 0.8;
        }

        /* Date Cards */
        .date-section {
            margin-bottom: 1.5rem;
        }

        .date-section:last-child {
            margin-bottom: 0;
        }

        .date-header {
            background: var(--gray-100);
            padding: 0.75rem 1rem;
            border-radius: 3px;
            margin-bottom: 1rem;
            font-weight: 600;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .date-header i {
            color: var(--primary);
        }

        /* Time Slots */
        .slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 0.75rem;
        }

        .slot-btn {
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 3px;
            background: #fff;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .slot-btn:hover {
            border-color: var(--primary);
            background: rgba(78, 115, 223, 0.05);
        }

        .slot-btn.selected {
            border-color: var(--primary);
            background: var(--primary);
            color: #fff;
        }

        .slot-btn .time {
            font-weight: 600;
            font-size: 1rem;
            display: block;
        }

        .slot-btn .available {
            font-size: 0.75rem;
            color: var(--gray-600);
        }

        .slot-btn.selected .available {
            color: rgba(255, 255, 255, 0.8);
        }

        /* Help Text */
        .help-text {
            background: var(--gray-100);
            border-radius: 3px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            color: var(--gray-600);
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .help-text i {
            color: var(--primary);
            margin-top: 2px;
        }

        /* Empty State */
        .empty-state {
            padding: 3rem 2rem;
            text-align: center;
        }

        .empty-state-icon {
            width: 64px;
            height: 64px;
            border-radius: 3px;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: var(--gray-600);
        }

        .empty-state h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin: 0;
        }

        /* Booking Form */
        .booking-form {
            background: var(--gray-100);
            border-radius: 3px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            display: none;
        }

        .booking-form.active {
            display: block;
        }

        .selected-slot-info {
            background: #fff;
            border-radius: 3px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .selected-slot-info .icon {
            width: 48px;
            height: 48px;
            border-radius: 3px;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .selected-slot-info .details h5 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.25rem;
        }

        .selected-slot-info .details p {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin: 0;
        }

        @media (max-width: 576px) {
            .slots-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid slots-container">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="{{ route('student.lms.appointments.index') }}" class="btn btn-outline-secondary">
                <i class="mdi mdi-arrow-left"></i> Back to Appointments
            </a>
        </div>

        <!-- Schedule Info -->
        <div class="schedule-info">
            <h2>{{ $schedule->title ?: 'Office Hours' }}</h2>
            <div class="meta">
                <div class="meta-item">
                    <i class="mdi mdi-account"></i>
                    <span>{{ $schedule->user->name ?? 'Instructor' }}</span>
                </div>
                @foreach($schedule->windows as $window)
                    <div class="meta-item">
                        <i class="mdi mdi-calendar"></i>
                        <span>{{ ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$window->day_of_week] }}s</span>
                    </div>
                    <div class="meta-item">
                        <i class="mdi mdi-clock-outline"></i>
                        <span>{{ \Carbon\Carbon::parse($window->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($window->end_time)->format('H:i') }}</span>
                    </div>
                @endforeach
                <div class="meta-item">
                    <i class="mdi mdi-timer-outline"></i>
                    <span>{{ $schedule->slot_duration }} min slots</span>
                </div>
                @if($schedule->meeting_url)
                    <div class="meta-item">
                        <i class="mdi mdi-video"></i>
                        <span>Virtual Meeting</span>
                    </div>
                @elseif($schedule->location)
                    <div class="meta-item">
                        <i class="mdi mdi-map-marker"></i>
                        <span>{{ $schedule->location }}</span>
                    </div>
                @endif
            </div>
        </div>

        @if($schedule->description)
            <div class="help-text">
                <i class="mdi mdi-information-outline"></i>
                <div>{{ $schedule->description }}</div>
            </div>
        @endif

        <!-- Available Slots -->
        <div class="content-card">
            <div class="content-card-header">
                <h3>
                    <i class="mdi mdi-calendar-clock"></i>
                    Available Time Slots
                </h3>
            </div>
            <div class="content-card-body">
                @if(count($slots) > 0)
                    @foreach($slots as $dateKey => $dayData)
                        <div class="date-section">
                            <div class="date-header">
                                <i class="mdi mdi-calendar"></i>
                                {{ $dayData['formatted'] }}
                            </div>
                            <div class="slots-grid">
                                @foreach($dayData['slots'] as $slot)
                                    <button type="button"
                                            class="slot-btn"
                                            data-datetime="{{ $slot['start']->toIso8601String() }}"
                                            data-date="{{ $slot['start']->format('l, F j, Y') }}"
                                            data-time="{{ $slot['start']->format('H:i') }} - {{ $slot['end']->format('H:i') }}">
                                        <span class="time">{{ $slot['start']->format('H:i') }}</span>
                                        <span class="available">{{ $slot['available'] }} slot{{ $slot['available'] > 1 ? 's' : '' }} left</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <!-- Booking Form -->
                    <div class="booking-form" id="bookingForm">
                        <h5 class="mb-3">Confirm Your Booking</h5>

                        <div class="selected-slot-info">
                            <div class="icon">
                                <i class="mdi mdi-clock-outline"></i>
                            </div>
                            <div class="details">
                                <h5 id="selectedDate">-</h5>
                                <p id="selectedTime">-</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes for the Instructor (optional)</label>
                            <textarea class="form-control" id="appointmentNotes" rows="3"
                                      placeholder="What would you like to discuss? Any specific questions?"></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" id="confirmBookingBtn">
                                <i class="mdi mdi-check"></i> Confirm Booking
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="cancelSelectionBtn">
                                Cancel
                            </button>
                        </div>
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="mdi mdi-calendar-blank"></i>
                        </div>
                        <h4>No Available Slots</h4>
                        <p>There are no available time slots within the next 14 days. Please check back later.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selectedDatetime = null;
            const bookingForm = document.getElementById('bookingForm');
            const slotBtns = document.querySelectorAll('.slot-btn');

            // Handle slot selection
            slotBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    // Remove selection from all buttons
                    slotBtns.forEach(b => b.classList.remove('selected'));

                    // Select this button
                    this.classList.add('selected');

                    // Store selected datetime
                    selectedDatetime = this.dataset.datetime;

                    // Update booking form
                    document.getElementById('selectedDate').textContent = this.dataset.date;
                    document.getElementById('selectedTime').textContent = this.dataset.time;

                    // Show booking form
                    bookingForm.classList.add('active');

                    // Scroll to booking form
                    bookingForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            });

            // Handle cancel selection
            document.getElementById('cancelSelectionBtn')?.addEventListener('click', function() {
                slotBtns.forEach(b => b.classList.remove('selected'));
                bookingForm.classList.remove('active');
                selectedDatetime = null;
            });

            // Handle booking confirmation
            document.getElementById('confirmBookingBtn')?.addEventListener('click', function() {
                if (!selectedDatetime) {
                    alert('Please select a time slot first.');
                    return;
                }

                const notes = document.getElementById('appointmentNotes').value;
                const btn = this;

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Booking...';

                fetch('{{ route('student.lms.appointments.book', $schedule->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        datetime: selectedDatetime,
                        notes: notes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message and redirect
                        alert('Appointment booked successfully!');
                        window.location.href = '{{ route('student.lms.appointments.index') }}';
                    } else {
                        alert(data.message || 'Failed to book appointment. Please try again.');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="mdi mdi-check"></i> Confirm Booking';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="mdi mdi-check"></i> Confirm Booking';
                });
            });
        });
    </script>
@endsection
