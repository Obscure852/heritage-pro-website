@extends('layouts.master')

@section('title', $course->title . ' - Gradebook Settings')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.gradebook.index', $course) }}">{{ Str::limit($course->title, 20) }} Gradebook</a>
        @endslot
        @slot('title')
            Settings
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Gradebook Settings: {{ $course->title }}</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- General Settings -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>General Settings</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('lms.gradebook.settings.update', $course) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Grading Method</label>
                                <select name="grading_method" class="form-select">
                                    @foreach(\App\Models\Lms\GradebookSettings::$gradingMethods as $key => $label)
                                        <option value="{{ $key }}" {{ $settings->grading_method === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">How final grades are calculated</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Grade Scale</label>
                                <select name="grade_scale_id" class="form-select">
                                    <option value="">-- Default Scale --</option>
                                    @foreach($gradeScales as $scale)
                                        <option value="{{ $scale->id }}" {{ $settings->grade_scale_id == $scale->id ? 'selected' : '' }}>{{ $scale->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Passing Grade (%)</label>
                                <input type="number" name="passing_grade" class="form-control" value="{{ $settings->passing_grade }}" min="0" max="100">
                            </div>
                        </div>

                        <h6 class="mt-4 mb-3">Display Options</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="show_grade_to_students" class="form-check-input" value="1" {{ $settings->show_grade_to_students ? 'checked' : '' }}>
                                    <label class="form-check-label">Show grades to students</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="show_rank_to_students" class="form-check-input" value="1" {{ $settings->show_rank_to_students ? 'checked' : '' }}>
                                    <label class="form-check-label">Show class rank to students</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="show_statistics" class="form-check-input" value="1" {{ $settings->show_statistics ? 'checked' : '' }}>
                                    <label class="form-check-label">Show class statistics</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="drop_lowest" class="form-check-input" value="1" {{ $settings->drop_lowest ? 'checked' : '' }}>
                                    <label class="form-check-label">Drop lowest grades</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="include_incomplete" class="form-check-input" value="1" {{ $settings->include_incomplete ? 'checked' : '' }}>
                                    <label class="form-check-label">Include incomplete items as zero</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Grade Categories -->
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-folder me-2"></i>Grade Categories</h6>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fas fa-plus me-1"></i>Add Category
                    </button>
                </div>
                <div class="card-body p-0">
                    @php $totalWeight = $categories->sum('weight'); @endphp
                    @if($totalWeight != 100 && $settings->grading_method === 'weighted')
                        <div class="alert alert-warning m-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Category weights total {{ $totalWeight }}% (should be 100%)
                        </div>
                    @endif

                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th class="text-center">Weight</th>
                                <th class="text-center">Items</th>
                                <th class="text-center">Drop Lowest</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td>
                                        <span class="badge me-2" style="background-color: {{ $category->color }}">{{ substr($category->name, 0, 1) }}</span>
                                        {{ $category->name }}
                                        @if($category->is_extra_credit)
                                            <span class="badge bg-info ms-1">Extra Credit</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $category->weight }}%</td>
                                    <td class="text-center">{{ $category->items->count() }}</td>
                                    <td class="text-center">
                                        @if($category->drop_lowest)
                                            {{ $category->drop_lowest_count }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editCategory({{ json_encode($category) }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('lms.gradebook.categories.destroy', $category) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this category?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No categories defined. Add categories for weighted grading.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('lms.gradebook.recalculate', $course) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-info w-100">
                            <i class="fas fa-sync me-1"></i>Recalculate All Grades
                        </button>
                    </form>
                    <form action="{{ route('lms.gradebook.finalize', $course) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-success w-100" onclick="return confirm('Finalize all grades? This marks them as official.')">
                            <i class="fas fa-check-double me-1"></i>Finalize Grades
                        </button>
                    </form>
                </div>
            </div>

            <!-- Weight Distribution -->
            @if($categories->count())
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Weight Distribution</h6>
                    </div>
                    <div class="card-body">
                        @foreach($categories as $category)
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>{{ $category->name }}</span>
                                    <span>{{ $category->weight }}%</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar" style="width: {{ $category->weight }}%; background-color: {{ $category->color }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('lms.gradebook.categories.store', $course) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Grade Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Weight (%)</label>
                                <input type="number" name="weight" class="form-control" value="0" min="0" max="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Color</label>
                                <input type="color" name="color" class="form-control form-control-color" value="#6366f1">
                            </div>
                        </div>
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" name="drop_lowest" class="form-check-input" value="1">
                        <label class="form-check-label">Drop lowest grades in this category</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_extra_credit" class="form-check-input" value="1">
                        <label class="form-check-label">Extra credit category</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editCategory(category) {
    // TODO: Implement edit modal
    alert('Edit category: ' + category.name);
}
</script>
@endpush
@endsection
