@extends('layouts.master')

@section('title')
    Edit Communication
@endsection

@section('css')
    <style>
        /* Communication Container */
        .communication-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .communication-header {
            background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .communication-body {
            padding: 24px;
        }

        .info-badge {
            background: #f3f4f6;
            padding: 8px 12px;
            border-radius: 3px;
            font-size: 13px;
            color: #374151;
            border: 1px solid #e5e7eb;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .info-badge strong {
            color: #6b7280;
            font-weight: 600;
        }

        /* Cards */
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .card-body {
            padding: 20px;
        }

        /* Form Elements */
        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Buttons */
        .btn {
            padding: 10px 16px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            color: #495057;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        /* Direction and Method Badges */
        .direction-badge {
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
        }

        .direction-badge.outbound {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }

        .direction-badge.inbound {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .method-badge {
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            background: #e0e7ff;
            color: #3730a3;
            border: 1px solid #a5b4fc;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('welfare.communications.index') }}">Parent Communications</a>
        @endslot
        @slot('title')
            Edit Communication
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-all me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle-outline me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-block-helper me-2"></i>{{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endforeach
    @endif

    <!-- Communication Header -->
    <div class="communication-container">
        <div class="communication-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 style="margin:0; font-weight:600;">{{ $communication->subject }}</h4>
                    <div class="d-flex flex-wrap gap-2 align-items-center mt-3">
                        <span class="info-badge">
                            <strong>Student:</strong> {{ $communication->student->full_name ?? 'N/A' }}
                        </span>
                        <span class="info-badge">
                            <strong>Parent:</strong> {{ $communication->parent_guardian_name }}
                        </span>
                        <span class="info-badge">
                            <strong>Date:</strong> {{ $communication->communication_date->format('d M Y') }}
                        </span>
                        <span class="info-badge">
                            <strong>By:</strong> {{ $communication->staffMember->full_name ?? 'N/A' }}
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <span class="direction-badge {{ $communication->direction }}">
                        <i class="fas fa-{{ $communication->direction === 'outbound' ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ ucfirst($communication->direction) }}
                    </span>
                    <span class="method-badge ms-2">
                        {{ ucfirst(str_replace('_', ' ', $communication->method)) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap gap-2">
                        @if ($communication->follow_up_required && !$communication->follow_up_completed)
                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#completeFollowUpModal">
                                <i class="fas fa-check"></i> Complete Follow-up
                            </button>
                        @endif

                        <form action="{{ route('welfare.communications.destroy', $communication) }}" method="POST"
                            class="d-inline" onsubmit="return confirm('Are you sure you want to delete this communication?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Follow-up Status -->
    @if ($communication->follow_up_required)
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-{{ $communication->follow_up_completed ? 'success' : 'warning' }}">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0">
                            <i class="fas fa-clock me-1"></i> Follow-up Status
                            @if ($communication->follow_up_completed)
                                <span class="badge bg-success ms-2">Completed</span>
                            @else
                                <span class="badge bg-warning ms-2">Pending</span>
                            @endif
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($communication->follow_up_date)
                            <p class="mb-1"><strong>Due Date:</strong>
                                <span class="{{ \Carbon\Carbon::parse($communication->follow_up_date)->isPast() && !$communication->follow_up_completed ? 'text-danger' : '' }}">
                                    {{ \Carbon\Carbon::parse($communication->follow_up_date)->format('d M Y') }}
                                </span>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Form -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Communication Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('welfare.communications.update', $communication) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Type</label>
                                    <select name="type" class="form-select" required>
                                        <option value="welfare_update" {{ $communication->type === 'welfare_update' ? 'selected' : '' }}>Welfare Update</option>
                                        <option value="concern" {{ $communication->type === 'concern' ? 'selected' : '' }}>Concern</option>
                                        <option value="positive_feedback" {{ $communication->type === 'positive_feedback' ? 'selected' : '' }}>Positive Feedback</option>
                                        <option value="meeting" {{ $communication->type === 'meeting' ? 'selected' : '' }}>Meeting</option>
                                        <option value="incident_notification" {{ $communication->type === 'incident_notification' ? 'selected' : '' }}>Incident Notification</option>
                                        <option value="general" {{ $communication->type === 'general' ? 'selected' : '' }}>General</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Method</label>
                                    <select name="method" class="form-select" required>
                                        <option value="phone" {{ $communication->method === 'phone' ? 'selected' : '' }}>Phone Call</option>
                                        <option value="email" {{ $communication->method === 'email' ? 'selected' : '' }}>Email</option>
                                        <option value="sms" {{ $communication->method === 'sms' ? 'selected' : '' }}>SMS</option>
                                        <option value="in_person" {{ $communication->method === 'in_person' ? 'selected' : '' }}>In Person</option>
                                        <option value="video_call" {{ $communication->method === 'video_call' ? 'selected' : '' }}>Video Call</option>
                                        <option value="letter" {{ $communication->method === 'letter' ? 'selected' : '' }}>Letter</option>
                                        <option value="home_visit" {{ $communication->method === 'home_visit' ? 'selected' : '' }}>Home Visit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Direction</label>
                                    <select name="direction" class="form-select" required>
                                        <option value="outbound" {{ $communication->direction === 'outbound' ? 'selected' : '' }}>Outbound</option>
                                        <option value="inbound" {{ $communication->direction === 'inbound' ? 'selected' : '' }}>Inbound</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="communication_date" class="form-control"
                                        value="{{ $communication->communication_date->format('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Parent/Guardian Name</label>
                                    <input type="text" name="parent_guardian_name" class="form-control"
                                        value="{{ $communication->parent_guardian_name }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Relationship</label>
                                    <select name="relationship" class="form-select">
                                        <option value="">Select</option>
                                        <option value="mother" {{ $communication->relationship === 'mother' ? 'selected' : '' }}>Mother</option>
                                        <option value="father" {{ $communication->relationship === 'father' ? 'selected' : '' }}>Father</option>
                                        <option value="guardian" {{ $communication->relationship === 'guardian' ? 'selected' : '' }}>Guardian</option>
                                        <option value="grandparent" {{ $communication->relationship === 'grandparent' ? 'selected' : '' }}>Grandparent</option>
                                        <option value="other" {{ $communication->relationship === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Contact Used</label>
                                    <input type="text" name="contact_used" class="form-control"
                                        value="{{ $communication->contact_used }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control"
                                value="{{ $communication->subject }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Summary</label>
                            <textarea name="summary" class="form-control" rows="4" required>{{ $communication->summary }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Outcome</label>
                            <textarea name="outcome" class="form-control" rows="2">{{ $communication->outcome }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Action Items</label>
                            <textarea name="action_items" class="form-control" rows="2">{{ $communication->action_items }}</textarea>
                        </div>

                        <hr>
                        <h6>Follow-up</h6>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="follow_up_required"
                                            value="1" id="followUpRequired" {{ $communication->follow_up_required ? 'checked' : '' }}>
                                        <label class="form-check-label" for="followUpRequired">
                                            Follow-up Required
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Follow-up Date</label>
                                    <input type="date" name="follow_up_date" class="form-control"
                                        value="{{ $communication->follow_up_date ? \Carbon\Carbon::parse($communication->follow_up_date)->format('Y-m-d') : '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('welfare.communications.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Follow-up Modal -->
    <div class="modal fade" id="completeFollowUpModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('welfare.communications.follow-up', $communication) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Complete Follow-up</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Outcome <span class="text-danger">*</span></label>
                            <textarea name="outcome" class="form-control" rows="3" required
                                placeholder="Describe the outcome of the follow-up..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Complete Follow-up</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
