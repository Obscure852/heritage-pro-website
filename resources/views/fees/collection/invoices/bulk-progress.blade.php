@extends('layouts.master')
@section('title')
    Bulk Invoice Progress
@endsection
@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text.processing {
            background: #fffbeb;
            border-left-color: #f59e0b;
        }

        .help-text.completed {
            background: #f0fdf4;
            border-left-color: #10b981;
        }

        .progress-card {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .progress-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .progress-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .stat-item h4 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-item small {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }

        .progress-bar-container {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            height: 20px;
            margin-top: 20px;
            overflow: hidden;
        }

        .progress-bar {
            background: white;
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .progress-text {
            text-align: center;
            margin-top: 8px;
            font-size: 14px;
        }

        .messages-list {
            max-height: 300px;
            overflow-y: auto;
            background: #f9fafb;
            border-radius: 6px;
            padding: 12px;
        }

        .message-item {
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }

        .message-item:last-child {
            border-bottom: none;
        }

        .message-generated {
            color: #059669;
        }

        .message-skipped {
            color: #d97706;
        }

        .message-error {
            color: #dc2626;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('fees.collection.invoices.bulk') }}">Bulk Invoices</a>
        @endslot
        @slot('title')
            Generation Progress
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="form-container">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-tasks me-2"></i>
                Bulk Invoice Generation Progress
            </h1>
        </div>

        @php
            $isComplete = ($progress['batch_finished'] ?? false) || ($progress['batch_cancelled'] ?? false);
            $total = $progress['total'] ?? 0;
            $processed = ($progress['generated'] ?? 0) + ($progress['skipped'] ?? 0) + ($progress['errors'] ?? 0);
            $percentComplete = $total > 0 ? round(($processed / $total) * 100) : 0;
        @endphp

        <div class="help-text {{ $isComplete ? 'completed' : 'processing' }}">
            @if ($progress['batch_cancelled'] ?? false)
                <strong><i class="fas fa-ban me-1"></i> Generation Cancelled</strong>
                <p class="mb-0 mt-1">The bulk invoice generation was cancelled.</p>
            @elseif ($isComplete)
                <strong><i class="fas fa-check-circle me-1"></i> Generation Complete</strong>
                <p class="mb-0 mt-1">Bulk invoice generation has finished. Review the results below.</p>
            @else
                <strong><i class="fas fa-spinner fa-spin me-1"></i> Generation In Progress</strong>
                <p class="mb-0 mt-1">Invoices are being generated in the background. This page will update automatically.</p>
            @endif
        </div>

        <div class="progress-card">
            <div class="progress-stats">
                <div class="stat-item">
                    <h4 id="stat-total">{{ $total }}</h4>
                    <small>Total Students</small>
                </div>
                <div class="stat-item">
                    <h4 id="stat-generated">{{ $progress['generated'] ?? 0 }}</h4>
                    <small>Generated</small>
                </div>
                <div class="stat-item">
                    <h4 id="stat-skipped">{{ $progress['skipped'] ?? 0 }}</h4>
                    <small>Skipped</small>
                </div>
                <div class="stat-item">
                    <h4 id="stat-errors">{{ $progress['errors'] ?? 0 }}</h4>
                    <small>Errors</small>
                </div>
            </div>

            <div class="progress-bar-container">
                <div class="progress-bar" id="progress-bar" style="width: {{ $percentComplete }}%"></div>
            </div>
            <div class="progress-text" id="progress-text">{{ $percentComplete }}% complete</div>
        </div>

        @if (!empty($progress['messages']))
            <h5 class="mb-3"><i class="fas fa-list-alt me-2"></i>Activity Log</h5>
            <div class="messages-list" id="messages-list">
                @foreach (array_reverse($progress['messages']) as $msg)
                    <div class="message-item message-{{ $msg['status'] }}">
                        <i class="fas fa-{{ $msg['status'] === 'generated' ? 'check' : ($msg['status'] === 'skipped' ? 'forward' : 'exclamation-triangle') }} me-1"></i>
                        <strong>{{ $msg['student'] }}</strong>: {{ $msg['message'] }}
                        <small class="text-muted float-end">{{ $msg['time'] }}</small>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="form-actions">
            <a class="btn btn-secondary" href="{{ route('fees.collection.invoices.index') }}">
                <i class="bx bx-arrow-back me-1"></i> Back to Invoices
            </a>
            <div class="d-flex gap-2">
                @if (!$isComplete)
                    <form action="{{ route('fees.collection.invoices.bulk.cancel') }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Are you sure you want to cancel this batch?')">
                        @csrf
                        <input type="hidden" name="batch_key" value="{{ $batchKey }}">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-stop me-1"></i> Cancel Generation
                        </button>
                    </form>
                @endif
                <a href="{{ route('fees.collection.invoices.bulk') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> New Batch
                </a>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        @if (!$isComplete)
        // Auto-refresh progress every 3 seconds
        const batchKey = '{{ $batchKey }}';
        const refreshInterval = setInterval(fetchProgress, 3000);

        function fetchProgress() {
            fetch(`{{ route('fees.collection.invoices.bulk.progress') }}?batch_key=${batchKey}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                updateUI(data);

                if (data.batch_finished || data.batch_cancelled) {
                    clearInterval(refreshInterval);
                    // Reload page to show final state
                    setTimeout(() => window.location.reload(), 1000);
                }
            })
            .catch(error => {
                console.error('Error fetching progress:', error);
            });
        }

        function updateUI(data) {
            const total = data.total || 0;
            const generated = data.generated || 0;
            const skipped = data.skipped || 0;
            const errors = data.errors || 0;
            const processed = generated + skipped + errors;
            const percent = total > 0 ? Math.round((processed / total) * 100) : 0;

            document.getElementById('stat-generated').textContent = generated;
            document.getElementById('stat-skipped').textContent = skipped;
            document.getElementById('stat-errors').textContent = errors;
            document.getElementById('progress-bar').style.width = percent + '%';
            document.getElementById('progress-text').textContent = percent + '% complete';

            // Update messages if new ones exist
            if (data.messages && data.messages.length > 0) {
                const messagesList = document.getElementById('messages-list');
                messagesList.innerHTML = '';
                data.messages.reverse().forEach(msg => {
                    const iconClass = msg.status === 'generated' ? 'check' : (msg.status === 'skipped' ? 'forward' : 'exclamation-triangle');
                    messagesList.innerHTML += `
                        <div class="message-item message-${msg.status}">
                            <i class="fas fa-${iconClass} me-1"></i>
                            <strong>${msg.student}</strong>: ${msg.message}
                            <small class="text-muted float-end">${msg.time}</small>
                        </div>
                    `;
                });
            }
        }
        @endif
    </script>
@endsection
