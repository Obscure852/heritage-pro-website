@extends('layouts.master-student-portal')

@section('title')
    My Profile
@endsection

@section('css')
    <style>
        .portal-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .portal-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .portal-header h3 {
            margin: 0 0 6px 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .portal-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: 3px solid rgba(255, 255, 255, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .portal-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 14px 16px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
        }

        .help-text .help-title {
            font-weight: 600;
            font-size: 0.95rem;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 0.85rem;
            line-height: 1.4;
        }

        .section-card {
            background: #f9fafb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #3b82f6;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }

        .info-item {
            padding: 12px;
            background: white;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
        }

        .info-item .label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .info-item .value {
            font-size: 0.95rem;
            font-weight: 500;
            color: #1f2937;
        }

        .info-item .value.empty {
            color: #9ca3af;
            font-style: italic;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 500;
            color: #374151;
        }

        .form-control {
            border-radius: 3px;
            font-size: 0.9rem;
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-save {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-save:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-save.loading .btn-text {
            display: none;
        }

        .btn-save.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-save:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-current {
            background: #d1fae5;
            color: #065f46;
        }

        .status-left {
            background: #fee2e2;
            color: #991b1b;
        }

        .readonly-notice {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 3px;
            padding: 10px 14px;
            font-size: 0.85rem;
            color: #92400e;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .readonly-notice i {
            font-size: 1rem;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-left: 40px;
            padding-right: 40px;
        }

        .password-wrapper .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.9rem;
            pointer-events: none;
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 0;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }

        .toggle-password:hover {
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .portal-header {
                padding: 20px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('script')
    <script>
        document.querySelectorAll('.toggle-password').forEach(function(button) {
            button.addEventListener('click', function() {
                var targetId = this.getAttribute('data-target');
                var input = document.getElementById(targetId);
                var icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Loading animation on form submit
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() {
                var submitBtn = form.querySelector('button[type="submit"].btn-save');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });
        });

        // Auto-dismiss alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(function(alert) {
            setTimeout(function() {
                var dismissButton = alert.querySelector('.btn-close');
                if (dismissButton) {
                    dismissButton.click();
                } else {
                    alert.classList.remove('show');
                    alert.classList.add('fade');
                }
            }, 5000);
        });
    </script>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Portal
        @endslot
        @slot('title')
            My Profile
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') }}</strong>
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

    <div class="portal-container">
        <div class="portal-header">
            <div class="d-flex align-items-center gap-4">
                <div class="profile-avatar">
                    @if($student->photo_path)
                        <img src="{{ Storage::url($student->photo_path) }}" alt="{{ $student->full_name }}">
                    @else
                        {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                    @endif
                </div>
                <div>
                    <h3>{{ $student->full_name }}</h3>
                    <p>
                        {{ $student->currentClass?->name ?? 'No Class Assigned' }}
                        @if($student->currentGrade)
                            &bull; {{ $student->currentGrade->description }}
                        @endif
                    </p>
                    <span class="status-badge status-{{ strtolower($student->status) }}">
                        {{ $student->status }}
                    </span>
                </div>
            </div>
        </div>

        <div class="portal-body">
            <div class="help-text">
                <div class="help-title">Your Profile Information</div>
                <div class="help-content">
                    View your personal information and update your email or password. Contact the school administration to update other details.
                </div>
            </div>

            <!-- Personal Information (Read-only) -->
            <div class="section-card">
                <h5 class="section-title">
                    <i class="fas fa-user"></i> Personal Information
                </h5>
                <div class="readonly-notice">
                    <i class="fas fa-info-circle"></i>
                    Personal information can only be updated by the school administration.
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="label">First Name</div>
                        <div class="value">{{ $student->first_name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Last Name</div>
                        <div class="value">{{ $student->last_name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">ID Number</div>
                        <div class="value">{{ $student->id_number }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Date of Birth</div>
                        <div class="value">{{ $student->formatted_date_of_birth ?: '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Gender</div>
                        <div class="value">{{ $student->gender === 'M' ? 'Male' : 'Female' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Nationality</div>
                        <div class="value">{{ $student->nationality ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Exam Number</div>
                        <div class="value {{ !$student->exam_number ? 'empty' : '' }}">
                            {{ $student->exam_number ?? 'Not assigned' }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="label">Year Enrolled</div>
                        <div class="value">{{ $student->year }}</div>
                    </div>
                </div>
            </div>

            <!-- Parent/Guardian Information (Read-only) -->
            @if($student->sponsor)
                <div class="section-card">
                    <h5 class="section-title">
                        <i class="fas fa-users"></i> Parent/Guardian Information
                    </h5>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="label">Name</div>
                            <div class="value">{{ $student->sponsor->title }} {{ $student->sponsor->full_name }}</div>
                        </div>
                        <div class="info-item">
                            <div class="label">Relationship</div>
                            <div class="value">{{ $student->sponsor->relation ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="label">Phone</div>
                            <div class="value {{ !$student->sponsor->phone ? 'empty' : '' }}">
                                {{ $student->sponsor->phone ?? 'Not provided' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="label">Email</div>
                            <div class="value {{ !$student->sponsor->email ? 'empty' : '' }}">
                                {{ $student->sponsor->email ?? 'Not provided' }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <!-- Update Email -->
                <div class="col-lg-6">
                    <div class="section-card h-100">
                        <h5 class="section-title">
                            <i class="fas fa-envelope"></i> Update Email
                        </h5>
                        <form action="{{ route('student.profile.update') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ old('email', $student->email) }}"
                                    placeholder="Enter your email address">
                                <small class="text-muted">Used for password recovery and notifications.</small>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn-save">
                                    <span class="btn-text"><i class="fas fa-save me-1"></i> Update Email</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Updating...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="col-lg-6">
                    <div class="section-card h-100">
                        <h5 class="section-title">
                            <i class="fas fa-lock"></i> Change Password
                        </h5>
                        <form action="{{ route('student.profile.password') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <div class="password-wrapper">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" class="form-control" id="password" name="password" required minlength="8" placeholder="Enter new password">
                                    <button class="toggle-password" type="button" data-target="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Minimum 8 characters.</small>
                            </div>
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <div class="password-wrapper">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Confirm new password">
                                    <button class="toggle-password" type="button" data-target="password_confirmation">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn-save">
                                    <span class="btn-text"><i class="fas fa-key me-1"></i> Change Password</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
