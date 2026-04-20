@extends('layouts.master')

@section('title', 'Upcoming Deadlines')

@section('css')
    <style>
        .deadlines-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .deadlines-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .deadlines-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .deadlines-header p {
            margin: 8px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .deadlines-body {
            padding: 24px;
        }

        .deadline-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            margin-bottom: 12px;
            transition: all 0.2s ease;
        }

        .deadline-card:hover {
            border-color: #f59e0b;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.15);
        }

        .deadline-card:last-child {
            margin-bottom: 0;
        }

        .deadline-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .deadline-course {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .deadline-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 13px;
        }

        .deadline-due {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #374151;
        }

        .deadline-due.urgent {
            color: #dc2626;
            font-weight: 500;
        }

        .deadline-due.soon {
            color: #f59e0b;
            font-weight: 500;
        }

        .deadline-type {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .deadline-type.assignment {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .deadline-type.quiz {
            background: #fce7f3;
            color: #be185d;
        }

        .deadline-type.discussion {
            background: #d1fae5;
            color: #047857;
        }

        .deadline-type.custom {
            background: #e5e7eb;
            color: #374151;
        }

        .late-badge {
            font-size: 11px;
            color: #dc2626;
            background: #fef2f2;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            color: #6b7280;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #9ca3af;
            font-size: 14px;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Upcoming Deadlines</h4>
                    <div class="page-title-right">
                        <a href="{{ route('lms.calendar.index') }}" class="btn btn-secondary btn-sm">
                            <i class="ri-calendar-line me-1"></i> Back to Calendar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-10 mx-auto">
                <div class="deadlines-container">
                    <div class="deadlines-header">
                        <h4><i class="ri-alarm-warning-line me-2"></i>Upcoming Deadlines</h4>
                        <p>Your upcoming assignments, quizzes, and other deadlines</p>
                    </div>

                    <div class="deadlines-body">
                        @if($deadlines->count() > 0)
                            @foreach($deadlines as $deadline)
                                @php
                                    $hoursUntilDue = now()->diffInHours($deadline->due_date, false);
                                    $urgencyClass = '';
                                    if ($hoursUntilDue < 24) {
                                        $urgencyClass = 'urgent';
                                    } elseif ($hoursUntilDue < 72) {
                                        $urgencyClass = 'soon';
                                    }
                                @endphp
                                <div class="deadline-card">
                                    <div class="deadline-title">
                                        <span class="deadline-type {{ $deadline->type }}">
                                            {{ $deadline->type }}
                                        </span>
                                        {{ $deadline->title }}
                                    </div>

                                    @if($deadline->course)
                                        <div class="deadline-course">
                                            <i class="ri-book-line me-1"></i>{{ $deadline->course->title }}
                                        </div>
                                    @endif

                                    <div class="deadline-meta">
                                        <div class="deadline-due {{ $urgencyClass }}">
                                            <i class="ri-time-line"></i>
                                            Due: {{ $deadline->due_date->format('M j, Y \a\t g:i A') }}
                                            @if($hoursUntilDue < 24 && $hoursUntilDue > 0)
                                                <span>({{ $deadline->due_date->diffForHumans() }})</span>
                                            @endif
                                        </div>

                                        @if($deadline->allows_late && $deadline->late_penalty_percent > 0)
                                            <span class="late-badge">
                                                Late penalty: {{ $deadline->late_penalty_percent }}%
                                            </span>
                                        @endif
                                    </div>

                                    @if($deadline->description)
                                        <p class="text-muted small mt-2 mb-0">{{ Str::limit($deadline->description, 150) }}</p>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state">
                                <i class="ri-checkbox-circle-line"></i>
                                <h5>No Upcoming Deadlines</h5>
                                <p>You're all caught up! No deadlines are due at the moment.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
