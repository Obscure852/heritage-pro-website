@extends('layouts.master')

@section('title')
    Record Parent Communication
@endsection

@section('css')
    <link href="{{ URL::asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet">
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
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .communication-body {
            padding: 24px;
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
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('welfare.communications.index') }}">Back</a>
        @endslot
        @slot('title')
            Record Communication
        @endslot
    @endcomponent

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-block-helper me-2"></i>{{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endforeach
    @endif

    <div class="communication-container">
        <div class="communication-header">
            <h3 style="margin:0;">Record Parent Communication</h3>
            <p style="margin:6px 0 0 0; opacity:.9;">Document communication with parent or guardian</p>
        </div>
        <div class="communication-body">
            <form method="POST" action="{{ route('welfare.communications.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Student <span class="text-danger">*</span></label>
                                    <select name="student_id" id="student-select" class="form-control" required>
                                        <option value="">Select Student</option>
                                        @foreach ($students as $student)
                                            <option value="{{ $student->id }}"
                                                {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                                {{ $student->full_name }} ({{ $student->admission_number ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Link to Welfare Case</label>
                                    <select name="welfare_case_id" id="case-select" class="form-control">
                                        <option value="">None</option>
                                        @foreach ($cases as $case)
                                            <option value="{{ $case->id }}"
                                                {{ old('welfare_case_id') == $case->id ? 'selected' : '' }}>
                                                {{ $case->case_number }} - {{ $case->student->full_name ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option value="welfare_update"
                                            {{ old('type') === 'welfare_update' ? 'selected' : '' }}>Welfare Update
                                        </option>
                                        <option value="concern" {{ old('type') === 'concern' ? 'selected' : '' }}>Concern
                                        </option>
                                        <option value="positive_feedback"
                                            {{ old('type') === 'positive_feedback' ? 'selected' : '' }}>Positive Feedback
                                        </option>
                                        <option value="meeting" {{ old('type') === 'meeting' ? 'selected' : '' }}>Meeting
                                        </option>
                                        <option value="incident_notification"
                                            {{ old('type') === 'incident_notification' ? 'selected' : '' }}>Incident
                                            Notification</option>
                                        <option value="general" {{ old('type') === 'general' ? 'selected' : '' }}>General
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Method <span class="text-danger">*</span></label>
                                    <select name="method" class="form-select" required>
                                        <option value="">Select Method</option>
                                        <option value="phone" {{ old('method') === 'phone' ? 'selected' : '' }}>Phone Call
                                        </option>
                                        <option value="email" {{ old('method') === 'email' ? 'selected' : '' }}>Email
                                        </option>
                                        <option value="sms" {{ old('method') === 'sms' ? 'selected' : '' }}>SMS</option>
                                        <option value="in_person" {{ old('method') === 'in_person' ? 'selected' : '' }}>In
                                            Person</option>
                                        <option value="video_call" {{ old('method') === 'video_call' ? 'selected' : '' }}>
                                            Video Call</option>
                                        <option value="letter" {{ old('method') === 'letter' ? 'selected' : '' }}>Letter
                                        </option>
                                        <option value="home_visit" {{ old('method') === 'home_visit' ? 'selected' : '' }}>
                                            Home Visit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Direction <span class="text-danger">*</span></label>
                                    <select name="direction" class="form-select" required>
                                        <option value="">Select Direction</option>
                                        <option value="outbound" {{ old('direction') === 'outbound' ? 'selected' : '' }}>
                                            Outbound (School to Parent)</option>
                                        <option value="inbound" {{ old('direction') === 'inbound' ? 'selected' : '' }}>
                                            Inbound (Parent to School)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="communication_date" class="form-control"
                                        value="{{ old('communication_date', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Parent/Guardian Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="parent_guardian_name" class="form-control"
                                        value="{{ old('parent_guardian_name') }}" placeholder="Name of parent/guardian"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Relationship</label>
                                    <select name="relationship" class="form-select">
                                        <option value="">Select</option>
                                        <option value="mother" {{ old('relationship') === 'mother' ? 'selected' : '' }}>
                                            Mother</option>
                                        <option value="father" {{ old('relationship') === 'father' ? 'selected' : '' }}>
                                            Father</option>
                                        <option value="guardian"
                                            {{ old('relationship') === 'guardian' ? 'selected' : '' }}>Guardian</option>
                                        <option value="grandparent"
                                            {{ old('relationship') === 'grandparent' ? 'selected' : '' }}>Grandparent
                                        </option>
                                        <option value="other" {{ old('relationship') === 'other' ? 'selected' : '' }}>
                                            Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Contact Used</label>
                                    <input type="text" name="contact_used" class="form-control"
                                        value="{{ old('contact_used') }}" placeholder="Phone/email used">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" value="{{ old('subject') }}"
                                placeholder="Brief subject of the communication" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Summary <span class="text-danger">*</span></label>
                            <textarea name="summary" class="form-control" rows="4" required placeholder="Summary of the communication...">{{ old('summary') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Outcome</label>
                            <textarea name="outcome" class="form-control" rows="2" placeholder="What was the outcome?">{{ old('outcome') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Action Items</label>
                            <textarea name="action_items" class="form-control" rows="2" placeholder="Any action items agreed upon?">{{ old('action_items') }}</textarea>
                        </div>

                        <hr>
                        <h6>Follow-up</h6>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="follow_up_required"
                                            value="1" id="followUpRequired"
                                            {{ old('follow_up_required') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="followUpRequired">
                                            Follow-up Required
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8 d-flex justify-content-end">
                                <div class="mb-3">
                                    <label class="form-label">Follow-up Date</label>
                                    <input type="date" name="follow_up_date" class="form-control"
                                        value="{{ old('follow_up_date') }}">
                                </div>
                            </div>
                        </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('welfare.communications.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Record Communication
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Choices('#student-select', {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Select student'
            });

            new Choices('#case-select', {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Link to case'
            });
        });
    </script>
@endsection
