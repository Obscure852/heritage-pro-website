@extends('layouts.master')

@section('title', 'Calendar Preferences')

@section('css')
    <style>
        .preferences-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .preferences-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .preferences-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
        }

        /* Section Styling */
        .settings-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .settings-section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .settings-section-title i {
            color: #3b82f6;
        }

        /* Form Styling */
        .form-label {
            font-weight: 500;
            color: #374151;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-check {
            padding: 10px 0;
            padding-left: 1.75em;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin-top: 0;
        }

        .form-check-label {
            font-size: 14px;
            color: #374151;
            margin-left: 4px;
        }

        /* Event Type Grid */
        .event-type-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        @media (max-width: 768px) {
            .event-type-grid {
                grid-template-columns: 1fr;
            }
        }

        .event-type-item {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            transition: all 0.2s;
        }

        .event-type-item:hover {
            border-color: #d1d5db;
            background: #f9fafb;
        }

        .event-type-item .form-check-input {
            margin-right: 10px;
        }

        .event-type-dot {
            width: 14px;
            height: 14px;
            border-radius: 3px;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .event-type-label {
            font-size: 14px;
            color: #374151;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
            color: white;
        }

        .btn-outline {
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-outline:hover {
            background: #f9fafb;
            border-color: #9ca3af;
            color: #1f2937;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            margin-top: 24px;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.calendar.index') }}">Calendar</a>
        @endslot
        @slot('title')
            Preferences
        @endslot
    @endcomponent

    <div class="preferences-container">
        <div class="preferences-header">
            <div class="row align-items-center">
                <div class="col-12">
                    <h3 style="margin:0;"><i class="fas fa-cog me-2"></i>Calendar Preferences</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Customize how your calendar looks and behaves</p>
                </div>
            </div>
        </div>

        <div class="preferences-body">
            <div class="help-text">
                <div class="help-title">Personalize Your Calendar</div>
                <div class="help-content">
                    Configure your preferred calendar view, week start day, and choose which event types to display. These settings will be saved and applied every time you visit the calendar.
                </div>
            </div>

            <form action="{{ route('lms.calendar.update-preferences') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Display Settings -->
                <div class="settings-section">
                    <h4 class="settings-section-title">
                        <i class="fas fa-desktop"></i>
                        Display Settings
                    </h4>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Default View</label>
                            <select name="default_view" class="form-select">
                                @foreach(\App\Models\Lms\CalendarPreference::$views as $key => $label)
                                    <option value="{{ $key }}" {{ $preferences->default_view === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Choose which view to show when opening the calendar</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Week Starts On</label>
                            <select name="week_start" class="form-select">
                                @foreach(\App\Models\Lms\CalendarPreference::$weekStarts as $key => $label)
                                    <option value="{{ $key }}" {{ $preferences->week_start === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">First day of the week in calendar views</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Timezone</label>
                            <select name="timezone" class="form-select">
                                <option value="">Use system default</option>
                                @foreach(['Africa/Gaborone', 'Africa/Johannesburg', 'UTC'] as $tz)
                                    <option value="{{ $tz }}" {{ $preferences->timezone === $tz ? 'selected' : '' }}>
                                        {{ $tz }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Timezone for displaying event times</small>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="form-check">
                            <input type="checkbox" name="show_weekends" class="form-check-input" id="showWeekends" value="1"
                                   {{ $preferences->show_weekends ? 'checked' : '' }}>
                            <label class="form-check-label" for="showWeekends">
                                <strong>Show weekends</strong>
                                <span class="text-muted d-block" style="font-size: 12px;">Display Saturday and Sunday in calendar views</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Event Visibility -->
                <div class="settings-section">
                    <h4 class="settings-section-title">
                        <i class="fas fa-eye-slash"></i>
                        Hide Event Types
                    </h4>
                    <p class="text-muted mb-3" style="font-size: 13px;">
                        Select event types you want to hide from your calendar. Hidden events won't appear in any calendar view.
                    </p>

                    <div class="event-type-grid">
                        @foreach(\App\Models\Lms\CalendarEvent::$eventTypes as $key => $label)
                            <label class="event-type-item">
                                <input type="checkbox" name="hidden_event_types[]" class="form-check-input"
                                       value="{{ $key }}"
                                       {{ $preferences->isEventTypeHidden($key) ? 'checked' : '' }}>
                                <span class="event-type-dot" style="background-color: {{ \App\Models\Lms\CalendarEvent::$colors[$key] }};"></span>
                                <span class="event-type-label">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('lms.calendar.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Save Preferences</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                if (submitBtn && form.checkValidity()) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });
        });
    </script>
@endsection
