@extends('layouts.master')
@section('title')
    Optional Subject Remarks
@endsection
@section('css')
    <style>
        /* Main Container */
        .settings-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .settings-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .settings-body {
            padding: 24px;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        /* Form Controls */
        .form-control, .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Score Badges */
        .score-badge {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            margin-left: 8px;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-back:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .btn-next {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: #6b7280;
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-next:hover {
            background: #4b5563;
        }

        .btn-save {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-save:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
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

        .btn-next:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-next.loading .btn-text {
            display: none;
        }

        .btn-next.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Subject Teacher's Remarks
        @endslot
        @if (!empty($student))
            @slot('title')
                {{ $student->first_name . ' ' . $student->last_name . ' Remarks' }}
            @endslot
        @endif
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="settings-container">
                <div class="settings-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><i class="fas fa-comment-alt me-2"></i>Optional Subject Remarks</h3>
                            <p>Add remarks for {{ $student->first_name . ' ' . $student->last_name }}</p>
                        </div>
                        @if (isset($subjectExam->pivot) && ($subjectExam->pivot->score || $subjectExam->pivot->grade))
                            @php $studentNumber = $index + 1; @endphp
                            <div class="d-flex">
                                @if (!empty($subjectExam->pivot->score))
                                    <span class="score-badge">Score: {{ $subjectExam->pivot->score }}%</span>
                                @endif
                                @if (!empty($subjectExam->pivot->grade))
                                    <span class="score-badge">Grade: {{ $subjectExam->pivot->grade }}</span>
                                @endif
                                <span class="score-badge">{{ $studentNumber }} / {{ $klass->students->count() ?? '' }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="settings-body">
                    <form class="needs-validation" method="post" action="{{ route('assessment.new-remark-optional') }}" novalidate>
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                        <input type="hidden" name="grade_subject_id" value="{{ $klass->gradeSubject->id }}">
                        <input type="hidden" name="term_id" value="{{ $klass->term->id }}">
                        <input type="hidden" name="year" value="{{ $klass->term->year }}">
                        <input type="hidden" name="user_id" value="{{ $klass->teacher->id }}">
                        <input type="hidden" name="klass_id" value="{{ $klass->id }}">
                        <input type="hidden" name="student_ids" value="{{ $studentIds }}">
                        <input type="hidden" name="index" value="{{ $index }}">
                        <input type="hidden" name="context" value="{{ $markbookCurrentContext }}">

                        <div class="mb-3">
                            <label class="form-label">Subject Teacher's Remarks</label>
                            <select class="form-select" id="choices-single-groups-1">
                                <option value="">Select from comment bank ...</option>
                                @if (!empty($comments))
                                    @foreach ($comments as $comment)
                                        <option value="{{ $comment->comment }}">{{ $comment->comment }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <textarea name="remarks" id="subject_teacher" rows="5" class="form-control mt-2">{{ old('remarks',\App\Models\SubjectComment::where('student_id', $student->id)->where('grade_subject_id', $klass->gradeSubject->id)->where('term_id', session('selected_term_id', $klass->term->id))->first()->remarks ?? '') }}</textarea>
                        </div>

                        <div class="form-actions">
                            <a href="{{ $markbookBackUrl }}" class="btn-back">
                                <i class="bx bx-arrow-back"></i> Back
                            </a>
                            @if ($index < count(explode(',', $studentIds)) - 1)
                                <button type="submit" name="action" value="option_save_and_next" class="btn-next">
                                    <span class="btn-text"><i class="fas fa-arrow-right"></i> Save & Next</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                            @endif
                            @if (!session('is_past_term'))
                                <button type="submit" class="btn-save">
                                    <span class="btn-text"><i class="fas fa-save"></i> Save</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Saving...
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
        document.getElementById('choices-single-groups-1').addEventListener('change', function() {
            var selectedValue = this.options[this.selectedIndex].value;
            document.getElementById('subject_teacher').value = selectedValue;
        });

        // Form submission loading animation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const clickedButton = document.activeElement;
                    if (clickedButton && (clickedButton.classList.contains('btn-save') || clickedButton.classList.contains('btn-next'))) {
                        clickedButton.classList.add('loading');
                        clickedButton.disabled = true;
                    }
                });
            }
        });
    </script>
@endsection
