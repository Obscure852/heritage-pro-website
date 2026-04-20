@extends('layouts.master')

@section('title')
    {{ isset($comment) ? 'Edit Subject Comment' : 'New Subject Comment' }} | Assessment
@endsection

@section('css')
    <style>
        .settings-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-header h3 {
            margin: 0 0 8px 0;
            font-size: 22px;
            font-weight: 600;
        }

        .settings-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .settings-body {
            padding: 24px;
        }

        /* Help Text */
        .help-text {
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 16px;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
        }

        .help-text p {
            margin: 0;
            color: #1e40af;
            font-size: 14px;
            line-height: 1.5;
        }

        .help-text p i {
            margin-right: 8px;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-grid .full-width {
            grid-column: 1 / -1;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            font-weight: 500;
            color: #374151;
            font-size: 14px;
            margin-bottom: 8px;
            display: block;
        }

        .form-group label .required {
            color: #dc2626;
            margin-left: 2px;
        }

        .form-group .form-control {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 10px 14px;
            font-size: 14px;
            transition: all 0.2s ease;
            width: 100%;
        }

        .form-group .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .form-group textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .form-group .form-control::placeholder {
            color: #9ca3af;
        }

        .form-group .form-hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: 6px;
        }

        /* Buttons */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            margin-top: 24px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            color: #4b5563;
            font-weight: 500;
            font-size: 14px;
            background: white;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: #f9fafb;
            color: #374151;
            border-color: #d1d5db;
        }

        .btn-save {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            border: none;
            border-radius: 3px;
            color: white;
            font-weight: 500;
            font-size: 14px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-save:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .btn-save:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-save.loading .btn-text {
            display: none;
        }

        .btn-save.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .settings-body {
                padding: 16px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-back,
            .btn-save {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assessment.comment-bank') }}">Comments & Venues</a>
        @endslot
        @slot('title')
            {{ isset($comment) ? 'Edit Subject Comment' : 'New Subject Comment' }}
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-12">
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

            @if (session('error'))
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                            <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="row mb-3">
                    <div class="col-md-12">
                        @foreach ($errors->all() as $error)
                            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                                <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="settings-container">
                <div class="settings-header">
                    <h3>
                        <i class="fas fa-graduation-cap me-2"></i>
                        {{ isset($comment) ? 'Edit Subject Comment' : 'New Subject Comment' }}
                    </h3>
                    <p>{{ isset($comment) ? 'Update the score range and comment text' : 'Create a new comment for subject score ranges' }}</p>
                </div>
                <div class="settings-body">
                    <div class="help-text">
                        <p><i class="bx bx-info-circle"></i>Subject comments are automatically displayed on reports based on student scores. Define a score range (e.g., 70-85) and the corresponding feedback comment.</p>
                    </div>

                    <form class="needs-validation" method="POST"
                        action="{{ isset($comment) ? route('assessment.update-subject-comment', $comment->id) : route('assessment.store-subject-comment') }}"
                        id="subjectCommentForm" novalidate>
                        @csrf
                        @if (isset($comment))
                            @method('PUT')
                        @endif

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="min_score">Minimum Score <span class="required">*</span></label>
                                <input type="number" name="min_score" id="min_score" class="form-control"
                                    placeholder="e.g., 70" value="{{ isset($comment) ? $comment->min_score : old('min_score') }}"
                                    min="0" max="100" required>
                                <div class="form-hint">Enter the lower bound of the score range (0-100)</div>
                            </div>

                            <div class="form-group">
                                <label for="max_score">Maximum Score <span class="required">*</span></label>
                                <input type="number" name="max_score" id="max_score" class="form-control"
                                    placeholder="e.g., 85" value="{{ isset($comment) ? $comment->max_score : old('max_score') }}"
                                    min="0" max="100" required>
                                <div class="form-hint">Enter the upper bound of the score range (0-100)</div>
                            </div>

                            <div class="form-group full-width">
                                <label for="comment">Comment <span class="required">*</span></label>
                                <textarea class="form-control" id="comment" name="comment" rows="4"
                                    placeholder="Enter the feedback comment for this score range..." required>{{ isset($comment) ? $comment->comment : old('comment') }}</textarea>
                                <div class="form-hint">This comment will appear on student reports when their score falls within the defined range</div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('assessment.comment-bank') }}" class="btn-back">
                                <i class="bx bx-arrow-back"></i>
                                Back to List
                            </a>
                            @if (!session('is_past_term'))
                                <button type="submit" class="btn-save">
                                    <span class="btn-text"><i class="fas fa-save"></i> {{ isset($comment) ? 'Update Comment' : 'Save Comment' }}</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        {{ isset($comment) ? 'Updating...' : 'Saving...' }}
                                    </span>
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('subjectCommentForm');
            form.addEventListener('submit', function(e) {
                if (form.checkValidity()) {
                    const btn = form.querySelector('.btn-save');
                    if (btn) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                }
            });
        });
    </script>
@endsection
