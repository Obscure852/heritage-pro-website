@extends('layouts.master-student-portal')
@section('title', 'Completed Subjects - Student Learning Management Portal')

@section('css')
    <style>
        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            width: 2px;
            background: #3498db;
            top: 0;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }

        .timeline-item:nth-child(odd) .timeline-content {
            margin-left: 60px;
            text-align: left;
        }

        .timeline-item:nth-child(even) .timeline-content {
            margin-right: 60px;
            text-align: right;
        }

        .timeline-dot {
            width: 16px;
            height: 16px;
            background: #3498db;
            border-radius: 50%;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
        }

        .timeline-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 45%;
        }

        .timeline-content h5 {
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .timeline-content small {
            color: #6b7280;
        }

        .subject-group {
            margin-bottom: 40px;
        }

        @media (max-width: 767px) {
            .timeline::before {
                left: 20px;
            }

            .timeline-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .timeline-item:nth-child(odd) .timeline-content,
            .timeline-item:nth-child(even) .timeline-content {
                margin-left: 40px;
                margin-right: 0;
                text-align: left;
                width: 100%;
            }

            .timeline-dot {
                left: 20px;
                transform: translateX(0);
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Completed Topic Resources</h4>
                        <p class="text-muted">View all your completed topic resources grouped by subject.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @forelse ($completedBySubject as $group)
                    <div class="subject-group">
                        <h5 class="mb-3">{{ $group['subject']->title ?? '' }}</h5>
                        <div class="timeline">
                            @foreach ($group['resources'] as $resource)
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <h5>{{ $resource->title }}</h5>
                                        <small>Topic: {{ $resource->topic->title }}</small><br>
                                        <small>Completed:
                                            {{ $resource->studentProgress->first()->completed_at->format('M d, Y') }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="bx bx-book-bookmark text-muted display-4"></i>
                        <p class="text-muted mt-3">No completed topic resources yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
