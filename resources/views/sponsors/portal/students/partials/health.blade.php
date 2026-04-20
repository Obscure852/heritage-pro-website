{{-- Health Records Partial --}}
<div class="help-text mb-4">
    <div class="help-title">Health Information</div>
    <div class="help-content">
        View your child's health records including blood type, allergies, and medical history.
        Contact the school nurse to update any health information.
    </div>
</div>

@if($student->studentMedicals)
    <div class="row g-4">
        {{-- Blood Group --}}
        <div class="col-md-6">
            <div class="card h-100 border">
                <div class="card-body">
                    <h6 class="d-flex align-items-center gap-2 text-primary mb-3">
                        <i class="bx bx-droplet"></i> Blood Group
                    </h6>
                    @php
                        $bloodGroups = [];
                        if ($student->studentMedicals->a_positive) $bloodGroups[] = 'A+';
                        if ($student->studentMedicals->a_negative) $bloodGroups[] = 'A-';
                        if ($student->studentMedicals->b_positive) $bloodGroups[] = 'B+';
                        if ($student->studentMedicals->b_negative) $bloodGroups[] = 'B-';
                        if ($student->studentMedicals->ab_positive) $bloodGroups[] = 'AB+';
                        if ($student->studentMedicals->ab_negative) $bloodGroups[] = 'AB-';
                        if ($student->studentMedicals->o_positive) $bloodGroups[] = 'O+';
                        if ($student->studentMedicals->o_negative) $bloodGroups[] = 'O-';
                    @endphp
                    @if(count($bloodGroups) > 0)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($bloodGroups as $bg)
                                <span class="badge bg-danger fs-6">{{ $bg }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">Not recorded</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Allergies --}}
        <div class="col-md-6">
            <div class="card h-100 border">
                <div class="card-body">
                    <h6 class="d-flex align-items-center gap-2 text-warning mb-3">
                        <i class="bx bx-shield-x"></i> Allergies & Dietary Requirements
                    </h6>
                    @php
                        $allergies = [];
                        if ($student->studentMedicals->peanuts) $allergies[] = 'Peanuts';
                        if ($student->studentMedicals->red_meat) $allergies[] = 'Red Meat';
                        if ($student->studentMedicals->vegetarian) $allergies[] = 'Vegetarian';
                        if ($student->studentMedicals->other_allergies) $allergies[] = $student->studentMedicals->other_allergies;
                    @endphp
                    @if(count($allergies) > 0)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($allergies as $allergy)
                                <span class="badge bg-warning text-dark">{{ $allergy }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">None recorded</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Medical History --}}
        <div class="col-12">
            <div class="card border">
                <div class="card-body">
                    <h6 class="d-flex align-items-center gap-2 text-info mb-3">
                        <i class="bx bx-file-blank"></i> Medical History
                    </h6>
                    @if($student->studentMedicals->health_history)
                        <p class="mb-0">{{ $student->studentMedicals->health_history }}</p>
                    @else
                        <p class="text-muted mb-0">No medical history recorded.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Emergency Contact Note --}}
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-start gap-3 mb-0">
                <i class="bx bx-info-circle fs-4"></i>
                <div>
                    <strong>Important:</strong> Please ensure the school has up-to-date emergency contact information.
                    If your child has any new allergies, medical conditions, or requires medication, please inform
                    the school nurse immediately.
                </div>
            </div>
        </div>
    </div>
@else
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bx bx-plus-medical"></i>
        </div>
        <h5>No Medical Information</h5>
        <p>Medical records have not been added for this student. Please contact the school to provide health information.</p>
    </div>
@endif
