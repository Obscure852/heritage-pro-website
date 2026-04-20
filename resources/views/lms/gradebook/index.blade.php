@extends('layouts.master')

@section('title', $course->title . ' - Gradebook')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.courses.show', $course) }}">{{ Str::limit($course->title, 30) }}</a>
        @endslot
        @slot('title')
            Gradebook
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="help-text mb-4" style="background: #f8f9fa; padding: 12px 16px; border-left: 4px solid #3b82f6; border-radius: 0 3px 3px 0;">
        <div class="help-title" style="font-weight: 600; color: #374151; margin-bottom: 4px;">Course Gradebook</div>
        <div class="help-content" style="color: #6b7280; font-size: 13px; line-height: 1.5;">
            View and manage student grades for this course. Use the settings to configure grading weights and categories. Export grades for reporting purposes.
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0"><i class="fas fa-book-open me-2"></i>Gradebook: {{ $course->title }}</h4>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('lms.gradebook.settings', $course) }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-cog me-1"></i>Settings
            </a>
            <a href="{{ route('lms.gradebook.export', $course) }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-download me-1"></i>Export
            </a>
            <form action="{{ route('lms.gradebook.recalculate', $course) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-info">
                    <i class="fas fa-sync me-1"></i>Recalculate
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="h4 mb-0 text-primary">{{ $statistics['count'] }}</div>
                    <small class="text-muted">Students</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="h4 mb-0 text-success">{{ $statistics['average'] ?? '-' }}%</div>
                    <small class="text-muted">Average</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="h4 mb-0 text-info">{{ $statistics['median'] ?? '-' }}%</div>
                    <small class="text-muted">Median</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="h4 mb-0 text-success">{{ $statistics['highest'] ?? '-' }}%</div>
                    <small class="text-muted">Highest</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="h4 mb-0 text-danger">{{ $statistics['lowest'] ?? '-' }}%</div>
                    <small class="text-muted">Lowest</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="h4 mb-0 text-warning">{{ $statistics['passing_rate'] ?? '-' }}%</div>
                    <small class="text-muted">Passing</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gradebook Table -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Grade Sheet</h6>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="fas fa-plus me-1"></i>Add Grade Item
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="sticky-col">Student</th>
                            @foreach($categories as $category)
                                @foreach($category->items as $item)
                                    <th class="text-center" style="min-width: 100px;">
                                        <a href="{{ route('lms.gradebook.items.grade', $item) }}" class="text-decoration-none">
                                            {{ Str::limit($item->name, 15) }}
                                        </a>
                                        <br>
                                        <small class="text-muted">({{ $item->max_points }})</small>
                                    </th>
                                @endforeach
                            @endforeach
                            <th class="text-center bg-light">Total</th>
                            <th class="text-center bg-light">%</th>
                            <th class="text-center bg-light">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrollments as $enrollment)
                            <tr>
                                <td class="sticky-col">
                                    <strong>{{ $enrollment->student->name }}</strong>
                                </td>
                                @foreach($categories as $category)
                                    @foreach($category->items as $item)
                                        @php $grade = $grades[$enrollment->student_id][$item->id] ?? null; @endphp
                                        <td class="text-center">
                                            @if($grade && $grade->status === 'graded')
                                                <input type="number"
                                                       class="form-control form-control-sm text-center quick-grade"
                                                       value="{{ $grade->score }}"
                                                       data-item-id="{{ $item->id }}"
                                                       data-student-id="{{ $enrollment->student_id }}"
                                                       data-max="{{ $item->max_points }}"
                                                       style="width: 70px; display: inline-block;">
                                            @elseif($grade && $grade->status === 'excused')
                                                <span class="badge bg-info">EX</span>
                                            @else
                                                <input type="number"
                                                       class="form-control form-control-sm text-center quick-grade"
                                                       value=""
                                                       placeholder="-"
                                                       data-item-id="{{ $item->id }}"
                                                       data-student-id="{{ $enrollment->student_id }}"
                                                       data-max="{{ $item->max_points }}"
                                                       style="width: 70px; display: inline-block;">
                                            @endif
                                        </td>
                                    @endforeach
                                @endforeach
                                @php $courseGrade = $course_grades[$enrollment->student_id] ?? null; @endphp
                                <td class="text-center bg-light">
                                    <strong>{{ $courseGrade?->total_points_earned ?? 0 }}/{{ $courseGrade?->total_points_possible ?? 0 }}</strong>
                                </td>
                                <td class="text-center bg-light">
                                    <strong>{{ $courseGrade?->percentage ?? 0 }}%</strong>
                                </td>
                                <td class="text-center bg-light">
                                    <span class="badge {{ $courseGrade?->is_passing ? 'bg-success' : 'bg-danger' }}">
                                        {{ $courseGrade?->letter_grade ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Grade Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('lms.gradebook.items.store', $course) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Grade Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- No Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->weight }}%)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    @foreach(\App\Models\Lms\GradeItem::$types as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Max Points</label>
                                <input type="number" name="max_points" class="form-control" value="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.sticky-col {
    position: sticky;
    left: 0;
    background: white;
    z-index: 1;
}
thead .sticky-col {
    z-index: 2;
}
</style>

@push('scripts')
<script>
document.querySelectorAll('.quick-grade').forEach(input => {
    input.addEventListener('change', function() {
        const itemId = this.dataset.itemId;
        const studentId = this.dataset.studentId;
        const score = this.value;
        const max = this.dataset.max;

        if (score === '' || parseFloat(score) > parseFloat(max)) return;

        fetch('{{ route("lms.gradebook.quick-grade") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                grade_item_id: itemId,
                student_id: studentId,
                score: score
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                this.classList.add('is-valid');
                setTimeout(() => this.classList.remove('is-valid'), 2000);
            }
        });
    });
});
</script>
@endpush
@endsection
