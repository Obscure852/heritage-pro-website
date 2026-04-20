@extends('layouts.master')

@section('title', 'Edit Criterion')

@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            line-height: 1.4;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .form-grid-3 {
            display: grid;
            grid-template-columns: 2fr 2fr 1fr auto;
            gap: 12px;
            align-items: end;
        }

        @media (max-width: 768px) {

            .form-grid,
            .form-grid-3 {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            width: 100%;
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

        .form-control::placeholder {
            color: #9ca3af;
        }

        .text-danger {
            color: #dc2626;
        }

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
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
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

        .btn-danger {
            background: #dc2626;
            color: white;
            padding: 10px 12px;
        }

        .btn-danger:hover {
            background: #b91c1c;
            color: white;
        }

        .btn-sm {
            padding: 8px 14px;
            font-size: 13px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        /* Level Card */
        .level-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .level-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .level-number {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
        }

        .add-level-btn {
            width: 100%;
            padding: 14px;
            border: 1px dashed #d1d5db;
            background: transparent;
            border-radius: 3px;
            color: #6b7280;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .add-level-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #eff6ff;
        }

        /* Summary Card */
        .summary-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .summary-card h3 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 12px 0;
        }

        .summary-stat {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .summary-stat:last-child {
            border-bottom: none;
        }

        .summary-stat .label {
            color: #6b7280;
            font-size: 14px;
        }

        .summary-stat .value {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .rubric-info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 16px;
            border-radius: 3px;
            margin-bottom: 24px;
        }

        .rubric-info h4 {
            font-size: 12px;
            font-weight: 500;
            opacity: 0.9;
            margin: 0 0 4px 0;
        }

        .rubric-info .rubric-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        /* Danger Zone */
        .danger-zone {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 3px;
            padding: 20px;
        }

        .danger-zone h3 {
            font-size: 14px;
            font-weight: 600;
            color: #991b1b;
            margin: 0 0 12px 0;
        }

        .danger-zone p {
            color: #991b1b;
            font-size: 13px;
            margin: 0 0 16px 0;
        }

        .alert {
            border-radius: 3px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('title')
            Edit Criterion
        @endslot
    @endcomponent

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('lms.rubrics.criteria.update', [$rubric, $criterion]) }}" method="POST" id="criterionForm">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <div class="form-container">
                    <div class="page-header">
                        <h1 class="page-title">Edit Criterion</h1>
                    </div>

                    <div class="help-text">
                        <div class="help-title">Modify Grading Criterion</div>
                        <div class="help-content">
                            Update this criterion and its performance levels. Changes will affect
                            how this criterion is graded in assignments using this rubric.
                        </div>
                    </div>

                    <h3 class="section-title">Criterion Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Criterion Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $criterion->title) }}" placeholder="e.g., Content Quality" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Max Points <span class="text-danger">*</span></label>
                            <input type="number" name="max_points" id="maxPoints"
                                class="form-control @error('max_points') is-invalid @enderror"
                                value="{{ old('max_points', $criterion->max_points) }}" min="1" max="1000"
                                required>
                            @error('max_points')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="What does this criterion evaluate?">{{ old('description', $criterion->description) }}</textarea>
                    </div>

                    <div class="section-header"
                        style="display: flex; justify-content: space-between; align-items: center; margin: 24px 0 16px 0; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #1f2937; margin: 0;">Performance Levels</h3>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addLevel()">
                            <i class="fas fa-plus"></i> Add Level
                        </button>
                    </div>

                    <div id="levelsContainer">
                        <!-- Existing levels will be populated by JS -->
                    </div>

                    <button type="button" class="add-level-btn" onclick="addLevel()">
                        <i class="fas fa-plus"></i> Add Another Level
                    </button>

                    <div class="form-actions">
                        <a href="{{ route('lms.rubrics.edit', $rubric) }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="rubric-info">
                    <h4>Editing Criterion in</h4>
                    <p class="rubric-title">{{ $rubric->title }}</p>
                </div>

                <div class="summary-card">
                    <h3><i class="fas fa-info-circle me-2"></i>Criterion Info</h3>
                    <div class="summary-stat">
                        <span class="label">Current Levels</span>
                        <span class="value">{{ $criterion->levels->count() }}</span>
                    </div>
                    <div class="summary-stat">
                        <span class="label">Max Points</span>
                        <span class="value">{{ number_format($criterion->max_points) }}</span>
                    </div>
                </div>

                <div class="summary-card">
                    <h3><i class="fas fa-lightbulb me-2"></i>Tips</h3>
                    <ul class="text-muted small mb-0" style="padding-left: 20px;">
                        <li class="mb-2">Use descriptive level titles</li>
                        <li class="mb-2">Keep at least 2 performance levels</li>
                        <li class="mb-2">Points should decrease with quality</li>
                        <li>Add descriptions to clarify expectations</li>
                    </ul>
                </div>

                <div class="danger-zone">
                    <h3><i class="fas fa-exclamation-triangle me-2"></i>Delete Criterion</h3>
                    <p>Remove this criterion from the rubric. This action cannot be undone.</p>
                    <form action="{{ route('lms.rubrics.criteria.destroy', [$rubric, $criterion]) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this criterion? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash"></i> Delete Criterion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script>
        let levelCount = 0;
        const existingLevels = @json($criterion->levels);

        document.addEventListener('DOMContentLoaded', function() {
            // Load existing levels
            existingLevels.forEach(level => {
                addLevel(level.title, level.description || '', level.points, level.id);
            });
        });

        function addLevel(title = '', description = '', points = '', id = null) {
            const container = document.getElementById('levelsContainer');
            const index = levelCount++;

            const html = `
                <div class="level-card" data-level-index="${index}">
                    <div class="level-card-header">
                        <span class="level-number">${container.children.length + 1}</span>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeLevel(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    ${id ? `<input type="hidden" name="levels[${index}][id]" value="${id}">` : ''}
                    <div class="form-grid-3">
                        <div class="form-group mb-0">
                            <label class="form-label">Level Title <span class="text-danger">*</span></label>
                            <input type="text" name="levels[${index}][title]" class="form-control"
                                placeholder="e.g., Excellent" value="${title}" required>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label">Description</label>
                            <input type="text" name="levels[${index}][description]" class="form-control"
                                placeholder="What this level means" value="${description}">
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label">Points <span class="text-danger">*</span></label>
                            <input type="number" name="levels[${index}][points]" class="form-control"
                                placeholder="0" value="${points}" min="0" max="1000" required>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', html);
            updateLevelNumbers();
        }

        function removeLevel(index) {
            const container = document.getElementById('levelsContainer');
            if (container.children.length <= 2) {
                alert('Each criterion must have at least 2 performance levels.');
                return;
            }

            const level = container.querySelector(`[data-level-index="${index}"]`);
            if (level) {
                level.remove();
                updateLevelNumbers();
            }
        }

        function updateLevelNumbers() {
            const levels = document.querySelectorAll('#levelsContainer .level-card');
            levels.forEach((level, i) => {
                level.querySelector('.level-number').textContent = i + 1;
            });
        }

        // Validate before submit
        document.getElementById('criterionForm').addEventListener('submit', function(e) {
            const levels = document.querySelectorAll('#levelsContainer .level-card');
            if (levels.length < 2) {
                e.preventDefault();
                alert('Please add at least 2 performance levels.');
            }
        });
    </script>
@endsection
