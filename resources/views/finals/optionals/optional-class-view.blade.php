@extends('layouts.master')
@section('title', 'Students - ' . $optionalSubject->name)
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') <a href="{{ route('finals.optionals.index') }}">Back</a> @endslot
        @slot('title') Optional Class Students @endslot
    @endcomponent
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="header-content">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                    <i class="bi bi-book text-white"></i>
                                </div>
                                <div>
                                    <h1 class="h3 mb-1">{{ $optionalSubject->name }}</h1>
                                    <span class="badge bg-primary">{{ $optionalSubject->code ?? 'OS' . $optionalSubject->id }}</span>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <small class="text-muted d-block">Teacher</small>
                                            <span class="fw-medium">{{ $optionalSubject->teacher->full_name ?? 'Not Assigned' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2">
                        <i class="bi bi-people"></i>
                    </div>
                    <h4 class="card-title">{{ $totalStudents }}</h4>
                    <p class="card-text text-muted">Total Students</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h4 class="card-title">{{ $activeStudents }}</h4>
                    <p class="card-text text-muted">Active Students</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <div class="display-6 text-info mb-2">
                        <i class="bi bi-gender-male"></i>
                    </div>
                    <h4 class="card-title">{{ $maleStudents }}</h4>
                    <p class="card-text text-muted">Male Students</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2">
                        <i class="bi bi-gender-female"></i>
                    </div>
                    <h4 class="card-title">{{ $femaleStudents }}</h4>
                    <p class="card-text text-muted">Female Students</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-list-ul me-2"></i>Enrolled Students
                        </h5>
                        <div class="d-flex gap-2">
                            <div class="input-group" style="width: 250px;">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Search students..." id="studentSearch">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    @if($students->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="8%">Photo</th>
                                        <th width="25%">Student Name</th>
                                        <th width="12%">ID Number</th>
                                        <th width="10%">Class</th>
                                        <th width="8%">Gender</th>
                                        <th width="12%">Date of Birth</th>
                                        <th width="10%">Status</th>
                                        {{-- <th width="10%">Actions</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $index => $student)
                                        <tr class="student-row" data-student-name="{{ strtolower($student->full_name) }}">
                                            <td class="text-muted">{{ $index + 1 }}</td>
                                            <td>
                                                @if($student->photo_path)
                                                    <img src="{{ asset($student->photo_path) }}" alt="{{ $student->full_name }}" class="rounded-circle" width="32" height="32">
                                                @else
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">
                                                        {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $student->full_name }}</strong>
                                                    @if($student->exam_number)
                                                        <br><small class="text-muted">Exam #: {{ $student->exam_number }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $student->formatted_id_number ?? 'N/A' }}</td>
                                            <td>
                                                @if($student->finalKlasses->isNotEmpty())
                                                    <span class="badge bg-light text-dark">{{ $student->finalKlasses->first()->name }}</span>
                                                @else
                                                    <span class="text-muted">Not Assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="d-flex align-items-center">
                                                    <i class="bi bi-{{ $student->gender === 'M' ? 'gender-male text-info' : 'gender-female text-warning' }} me-1"></i>
                                                    {{ $student->gender === 'M' ? 'Male' : 'Female' }}
                                                </span>
                                            </td>
                                            <td>{{ $student->date_of_birth ? $student->date_of_birth->format('d M Y') : 'N/A' }}</td>
                                            <td>
                                                @if($student->status === 'Alumni')
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $student->status }}</span>
                                                @endif
                                            </td>
                                            {{-- <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary btn-sm" 
                                                            onclick="viewStudent({{ $student->id }})" 
                                                            title="View Student">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-secondary btn-sm" 
                                                            onclick="editStudent({{ $student->id }})" 
                                                            title="Edit Student">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                </div>
                                            </td> --}}
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="display-1 text-muted mb-3">
                                <i class="bi bi-person-x"></i>
                            </div>
                            <h5 class="text-muted">No Students Enrolled</h5>
                            <p class="text-muted mb-4">This optional subject has no students enrolled yet.</p>
                            <button type="button" class="btn btn-primary" onclick="addStudents()">
                                <i class="bi bi-person-plus me-1"></i>Add Students
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('studentSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('.student-row');
                
                rows.forEach(row => {
                    const studentName = row.getAttribute('data-student-name');
                    if (studentName.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
@endsection