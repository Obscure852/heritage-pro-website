@extends('layouts.master-student-portal')

@section('title')
    New Message
@endsection

@section('css')
    <style>
        .compose-wrapper {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .compose-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            padding: 20px 28px;
            color: white;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .compose-header .back-btn {
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: background 0.2s;
        }

        .compose-header .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .compose-header h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .compose-body {
            padding: 28px;
        }

        .form-section {
            margin-bottom: 24px;
        }

        .form-section-title {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .teacher-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 12px;
        }

        .teacher-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .teacher-card:hover {
            border-color: #4e73df;
            background: #f8faff;
        }

        .teacher-card.selected {
            border-color: #4e73df;
            background: #eff6ff;
        }

        .teacher-card input {
            display: none;
        }

        .teacher-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
            flex-shrink: 0;
        }

        .teacher-info {
            flex: 1;
            min-width: 0;
        }

        .teacher-name {
            font-weight: 600;
            font-size: 14px;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .teacher-courses {
            font-size: 12px;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .teacher-card .check-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .teacher-card.selected .check-icon {
            background: #4e73df;
            border-color: #4e73df;
            color: white;
        }

        .teacher-card .check-icon i {
            font-size: 12px;
            opacity: 0;
        }

        .teacher-card.selected .check-icon i {
            opacity: 1;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-label .optional {
            color: #9ca3af;
            font-weight: 400;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #4e73df;
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
        }

        .form-textarea {
            min-height: 160px;
            resize: vertical;
            line-height: 1.6;
        }

        /* Custom File Input */
        .custom-file-input {
            position: relative;
            display: block;
            width: 100%;
        }

        .custom-file-input input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            background: #fff;
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
        }

        .file-input-label:hover {
            border-color: #4e73df;
            background: #f0f9ff;
        }

        .file-input-icon {
            width: 40px;
            height: 40px;
            background: #4e73df;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            flex-shrink: 0;
        }

        .file-input-text {
            flex: 1;
        }

        .file-input-text .file-label {
            font-weight: 500;
            color: #374151;
            display: block;
            margin-bottom: 2px;
        }

        .file-input-text .file-hint {
            font-size: 13px;
            color: #6b7280;
        }

        .file-input-text .file-selected {
            font-size: 13px;
            color: #4e73df;
            font-weight: 500;
        }

        .file-list {
            margin-top: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .file-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #f3f4f6;
            border-radius: 6px;
            font-size: 13px;
            color: #374151;
        }

        .file-item i {
            color: #4e73df;
        }

        .compose-footer {
            padding: 20px 28px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-cancel {
            padding: 10px 20px;
            background: white;
            color: #6b7280;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-send {
            padding: 10px 24px;
            background: #4e73df;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-send:hover {
            background: #3a5bc7;
        }

        .btn-send.loading .btn-text {
            display: none;
        }

        .btn-send.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-send:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .empty-teachers {
            text-align: center;
            padding: 40px 20px;
            background: #f9fafb;
            border-radius: 8px;
        }

        .empty-teachers i {
            font-size: 40px;
            color: #d1d5db;
            margin-bottom: 12px;
        }

        .empty-teachers p {
            color: #6b7280;
            margin: 0;
        }

        .empty-teachers a {
            color: #4e73df;
        }

        .error-text {
            color: #ef4444;
            font-size: 12px;
            margin-top: 6px;
        }

        .row-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 768px) {
            .row-inputs {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('student.lms.messages.inbox') }}">Messages</a>
        @endslot
        @slot('title')
            New Message
        @endslot
    @endcomponent

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="compose-wrapper">
        <div class="compose-header">
            <a href="{{ route('student.lms.messages.inbox') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4><i class="fas fa-pen me-2"></i>New Message</h4>
        </div>

        @if($instructors->isEmpty())
            <div class="compose-body">
                <div class="empty-teachers">
                    <i class="fas fa-user-slash"></i>
                    <p>You need to be enrolled in a course to message teachers.<br>
                    <a href="{{ route('student.lms.courses') }}">Browse courses</a> to get started.</p>
                </div>
            </div>
        @else
            <form action="{{ route('student.lms.messages.send') }}" method="POST" enctype="multipart/form-data" id="composeForm">
                @csrf
                <div class="compose-body">
                    <div class="form-section">
                        <div class="form-section-title">Select Teacher</div>
                        <div class="teacher-grid">
                            @foreach($instructors as $instructor)
                                @php
                                    $instructorCourses = $enrolledCourses->where('instructor_id', $instructor->id);
                                    $courseNames = $instructorCourses->pluck('title')->join(', ');
                                @endphp
                                <label class="teacher-card {{ $selectedInstructorId == $instructor->id ? 'selected' : '' }}">
                                    <input type="radio" name="instructor_id" value="{{ $instructor->id }}"
                                           {{ $selectedInstructorId == $instructor->id ? 'checked' : '' }} required>
                                    <div class="teacher-avatar">
                                        {{ strtoupper(substr($instructor->firstname ?? $instructor->full_name, 0, 1)) }}
                                    </div>
                                    <div class="teacher-info">
                                        <div class="teacher-name">{{ $instructor->full_name }}</div>
                                        <div class="teacher-courses">{{ $courseNames }}</div>
                                    </div>
                                    <div class="check-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('instructor_id')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-section">
                        <div class="form-section-title">Message Details</div>
                        <div class="row-inputs mb-3">
                            <div>
                                <label class="form-label">Course <span class="optional">(Optional)</span></label>
                                <select name="course_id" id="course_id" class="form-select">
                                    <option value="">Select a course...</option>
                                    @foreach($enrolledCourses as $course)
                                        <option value="{{ $course->id }}" data-instructor="{{ $course->instructor_id }}"
                                                {{ $selectedCourseId == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Subject <span class="optional">(Optional)</span></label>
                                <input type="text" name="subject" id="subject" class="form-control"
                                       placeholder="e.g., Question about Assignment 3" maxlength="255"
                                       value="{{ old('subject') }}">
                                @error('subject')
                                    <div class="error-text">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="body" id="body" class="form-control form-textarea"
                                      placeholder="Write your message here..." required minlength="2" maxlength="10000">{{ old('body') }}</textarea>
                            @error('body')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Attachments <span class="optional">(Optional)</span></label>
                            <div class="custom-file-input">
                                <input type="file" name="attachments[]" id="attachmentInput" multiple
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip">
                                <label for="attachmentInput" class="file-input-label">
                                    <div class="file-input-icon">
                                        <i class="fas fa-paperclip"></i>
                                    </div>
                                    <div class="file-input-text">
                                        <span class="file-label">Choose Files to Attach</span>
                                        <span class="file-hint" id="fileHint">PDF, Word, Excel, Images, ZIP (max 10MB each, up to 5 files)</span>
                                        <span class="file-selected d-none" id="fileNames"></span>
                                    </div>
                                </label>
                            </div>
                            <div class="file-list" id="fileList"></div>
                            @error('attachments.*')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="compose-footer">
                    <a href="{{ route('student.lms.messages.inbox') }}" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-send">
                        <span class="btn-text"><i class="fas fa-paper-plane"></i> Send Message</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Sending...
                        </span>
                    </button>
                </div>
            </form>
        @endif
    </div>
@endsection

@section('script')
    <script>
        // Teacher card selection
        document.querySelectorAll('.teacher-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.teacher-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
            });
        });

        // Course selection auto-selects teacher
        document.getElementById('course_id')?.addEventListener('change', function() {
            const instructorId = this.options[this.selectedIndex].dataset.instructor;
            if (instructorId) {
                const radio = document.querySelector(`input[name="instructor_id"][value="${instructorId}"]`);
                if (radio) {
                    radio.checked = true;
                    document.querySelectorAll('.teacher-card').forEach(c => c.classList.remove('selected'));
                    radio.closest('.teacher-card').classList.add('selected');
                }
            }
        });

        // File upload handling
        const attachmentInput = document.getElementById('attachmentInput');
        const fileList = document.getElementById('fileList');
        const fileHint = document.getElementById('fileHint');
        const fileNames = document.getElementById('fileNames');

        attachmentInput?.addEventListener('change', function() {
            fileList.innerHTML = '';
            const files = Array.from(this.files);

            if (files.length === 0) {
                fileHint.classList.remove('d-none');
                fileNames.classList.add('d-none');
                fileNames.textContent = '';
                return;
            }

            if (files.length > 5) {
                alert('You can only attach up to 5 files.');
                this.value = '';
                fileHint.classList.remove('d-none');
                fileNames.classList.add('d-none');
                return;
            }

            // Update label to show selected files
            fileHint.classList.add('d-none');
            fileNames.classList.remove('d-none');
            fileNames.textContent = files.length === 1 ? files[0].name : `${files.length} files selected`;

            files.forEach(file => {
                if (file.size > 10 * 1024 * 1024) {
                    alert(`File "${file.name}" exceeds 10MB limit.`);
                    return;
                }

                const item = document.createElement('div');
                item.className = 'file-item';
                item.innerHTML = `<i class="fas fa-file"></i> ${file.name.length > 30 ? file.name.substring(0, 30) + '...' : file.name}`;
                fileList.appendChild(item);
            });
        });

        // Form submit loading state
        document.getElementById('composeForm')?.addEventListener('submit', function() {
            const btn = this.querySelector('.btn-send');
            btn.classList.add('loading');
            btn.disabled = true;
        });
    </script>
@endsection
