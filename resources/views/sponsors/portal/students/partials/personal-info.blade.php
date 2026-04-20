{{-- Personal Information Partial --}}
<div class="help-text mb-4">
    <div class="help-title">Personal Information</div>
    <div class="help-content">
        View your child's personal details and academic information. Contact the school administration
        if any information needs to be updated.
    </div>
</div>

<div class="section-divider">
    <i class="bx bx-id-card me-2"></i> Basic Information
</div>

<div class="info-grid mb-4">
    <div class="info-item">
        <div class="label">First Name</div>
        <div class="value">{{ $student->first_name ?? 'N/A' }}</div>
    </div>
    <div class="info-item">
        <div class="label">Last Name</div>
        <div class="value">{{ $student->last_name ?? 'N/A' }}</div>
    </div>
    <div class="info-item">
        <div class="label">Gender</div>
        <div class="value">
            @if($student->gender === 'M')
                <i class="bx bx-male text-primary me-1"></i> Male
            @elseif($student->gender === 'F')
                <i class="bx bx-female text-danger me-1"></i> Female
            @else
                {{ $student->gender ?? 'N/A' }}
            @endif
        </div>
    </div>
    <div class="info-item">
        <div class="label">Date of Birth</div>
        <div class="value">{{ $student->formatted_date_of_birth ?: 'N/A' }}</div>
    </div>
    <div class="info-item">
        <div class="label">Age</div>
        <div class="value">
            @if($student->date_of_birth)
                {{ $student->date_of_birth->age }} years
            @else
                N/A
            @endif
        </div>
    </div>
    <div class="info-item">
        <div class="label">Nationality</div>
        <div class="value">{{ $student->nationality ?? 'N/A' }}</div>
    </div>
    <div class="info-item">
        <div class="label">ID / Passport Number</div>
        <div class="value">{{ $student->id_number ?? 'N/A' }}</div>
    </div>
    <div class="info-item">
        <div class="label">Email</div>
        <div class="value">{{ $student->email ?? 'N/A' }}</div>
    </div>
</div>

<div class="section-divider">
    <i class="bx bx-school me-2"></i> Academic Information
</div>

<div class="info-grid mb-4">
    <div class="info-item">
        <div class="label">Student Number</div>
        <div class="value">{{ $student->student_number ?? 'N/A' }}</div>
    </div>
    <div class="info-item">
        <div class="label">Current Class</div>
        <div class="value">{{ $currentClass ? $currentClass->name : 'Not Assigned' }}</div>
    </div>
    <div class="info-item">
        <div class="label">Grade</div>
        <div class="value">{{ $currentClass && $currentClass->grade ? $currentClass->grade->name : 'N/A' }}</div>
    </div>
    <div class="info-item">
        <div class="label">Class Teacher</div>
        <div class="value">{{ $currentClass && $currentClass->teacher ? $currentClass->teacher->full_name : 'N/A' }}</div>
    </div>
    <div class="info-item">
        <div class="label">Status</div>
        <div class="value">
            @php
                $statusColors = [
                    'Current' => 'success',
                    'Left' => 'danger',
                    'Graduated' => 'primary',
                    'Suspended' => 'warning',
                    'Transferred' => 'info',
                ];
                $statusColor = $statusColors[$student->status] ?? 'secondary';
            @endphp
            <span class="badge bg-{{ $statusColor }}">{{ $student->status ?? 'Current' }}</span>
        </div>
    </div>
    <div class="info-item">
        <div class="label">Year Enrolled</div>
        <div class="value">{{ $student->year ?? 'N/A' }}</div>
    </div>
</div>

<div class="section-divider">
    <i class="bx bx-calendar-x me-2"></i> Attendance Summary
</div>

<div class="info-grid">
    <div class="info-item">
        <div class="label">Days Absent (Current Term)</div>
        <div class="value">
            <span class="badge bg-{{ $student->absentDays->count() > 5 ? 'warning' : 'success' }} fs-6">
                {{ $student->absentDays->count() }} day(s)
            </span>
        </div>
    </div>
</div>
