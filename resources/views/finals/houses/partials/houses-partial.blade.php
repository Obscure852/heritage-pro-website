@if($finalHouses->count() > 0)
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Final Year Houses List 
                    <span class="text-muted fw-normal ms-2">({{ $finalHouses->count() }} houses)</span>
                </h5>
            </div>
            <div class="card-body">
                <!-- Graduation Terms Filter -->
                @if($graduationTerms->count() > 1)
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card border-0 bg-light">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <strong>Filter by Graduation Term:</strong>
                                    <div class="btn-group" role="group" aria-label="Graduation Terms">
                                        <input type="radio" class="btn-check" name="termFilter" id="termAll" value="all" checked>
                                        <label class="btn btn-outline-primary btn-sm" for="termAll">
                                            All Terms ({{ $finalHouses->count() }} houses)
                                        </label>
                                        
                                        @foreach($graduationTerms as $term)
                                        @php
                                            $termHousesCount = $finalHouses->where('graduation_term_id', $term->id)->count();
                                        @endphp
                                        <input type="radio" class="btn-check" name="termFilter" id="term{{ $term->id }}" value="{{ $term->id }}">
                                        <label class="btn btn-outline-primary btn-sm" for="term{{ $term->id }}">
                                            {{ $term->name }} ({{ $termHousesCount }} houses)
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Houses Grid -->
                <div class="row" id="housesGrid">
                    @foreach($finalHouses->groupBy('graduation_term_id') as $termId => $termHouses)
                        @php
                            $term = $graduationTerms->firstWhere('id', $termId);
                        @endphp
                        
                        <div class="col-12 mb-4 term-section" data-term="{{ $termId }}">
                            @if($graduationTerms->count() > 1)
                            <div class="d-flex align-items-center mb-3">
                                <h6 class="mb-0 me-3">
                                    <i class="bx bxs-calendar me-2 text-primary"></i>
                                    {{ $term ? $term->name : 'Unknown Term' }}
                                </h6>
                                <span class="badge bg-primary graduation-term-badge">
                                    {{ $termHouses->count() }} {{ Str::plural('house', $termHouses->count()) }}
                                </span>
                            </div>
                            @endif
                            
                            <div class="row">
                                @foreach($termHouses as $house)
                                <div class="col-md-6 col-lg-4 mb-3 house-item" data-term="{{ $termId }}">
                                    <div class="card house-card h-100">
                                        <div class="card-header bg-white border-bottom">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="card-title mb-1">
                                                        <i class="bx bxs-home me-2 text-primary"></i>
                                                        {{ $house->name }}
                                                    </h6>
                                                    <small class="text-muted">
                                                        Graduation Year: {{ $house->graduation_year }}
                                                    </small>
                                                </div>
                                                <span class="badge bg-success student-count-badge" 
                                                      data-bs-toggle="tooltip" 
                                                      title="Total Students in House">
                                                    {{ $house->finalStudents->count() }}
                                                    <i class="bx bxs-group ms-1"></i>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="card-body">
                                            <!-- House Leadership -->
                                            <div class="mb-3">
                                                <h6 class="text-muted mb-2">
                                                    <i class="bx bxs-user-account me-1"></i>Leadership
                                                </h6>
                                                
                                                <div class="row text-sm">
                                                    <div class="col-6">
                                                        <strong>House Head:</strong><br>
                                                        @if($house->houseHead)
                                                            <span class="text-primary">{{ $house->houseHead->full_name }}</span>
                                                        @else
                                                            <span class="text-muted">Not assigned</span>
                                                        @endif
                                                    </div>
                                                    <div class="col-6">
                                                        <strong>Assistant:</strong><br>
                                                        @if($house->houseAssistant)
                                                            <span class="text-primary">{{ $house->houseAssistant->full_name }}</span>
                                                        @else
                                                            <span class="text-muted">Not assigned</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Student Statistics -->
                                            @if($house->finalStudents->count() > 0)
                                            <div class="mb-3">
                                                <h6 class="text-muted mb-2">
                                                    <i class="bx bxs-pie-chart-alt me-1"></i>Student Statistics
                                                </h6>
                                                
                                                @php
                                                    $genderStats = $house->finalStudents->groupBy('gender');
                                                    $maleCount = $genderStats->get('M', collect())->count();
                                                    $femaleCount = $genderStats->get('F', collect())->count();
                                                    $totalStudents = $house->finalStudents->count();
                                                @endphp
                                                
                                                <div class="row text-sm">
                                                    <div class="col-4 text-center">
                                                        <div class="text-primary fw-bold">{{ $maleCount }}</div>
                                                        <small class="text-muted">Male</small>
                                                    </div>
                                                    <div class="col-4 text-center">
                                                        <div class="text-danger fw-bold">{{ $femaleCount }}</div>
                                                        <small class="text-muted">Female</small>
                                                    </div>
                                                    <div class="col-4 text-center">
                                                        <div class="text-success fw-bold">{{ $totalStudents }}</div>
                                                        <small class="text-muted">Total</small>
                                                    </div>
                                                </div>
                                                
                                                <!-- Gender Distribution Bar -->
                                                @if($totalStudents > 0)
                                                <div class="mt-2">
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar bg-primary" 
                                                             style="width: {{ ($maleCount / $totalStudents) * 100 }}%"
                                                             data-bs-toggle="tooltip" 
                                                             title="Male: {{ round(($maleCount / $totalStudents) * 100, 1) }}%"></div>
                                                        <div class="progress-bar bg-danger" 
                                                             style="width: {{ ($femaleCount / $totalStudents) * 100 }}%"
                                                             data-bs-toggle="tooltip" 
                                                             title="Female: {{ round(($femaleCount / $totalStudents) * 100, 1) }}%"></div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            @endif

                                            <!-- Recent Students Sample -->
                                            @if($house->finalStudents->count() > 0)
                                            <div class="mb-3">
                                                <h6 class="text-muted mb-2">
                                                    <i class="bx bxs-graduation me-1"></i>Sample Students
                                                </h6>
                                                <div class="list-group list-group-flush">
                                                    @foreach($house->finalStudents->take(3) as $student)
                                                    <div class="list-group-item px-0 py-2 border-0 bg-transparent">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div class="d-flex align-items-center">
                                                                @if($student->photo_path)
                                                                    <img src="{{ asset('storage/' . $student->photo_path) }}" 
                                                                         alt="{{ $student->full_name }}" 
                                                                         class="rounded-circle me-2" 
                                                                         width="24" height="24">
                                                                @else
                                                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2" 
                                                                         style="width: 24px; height: 24px;">
                                                                        <i class="bx bxs-user font-size-12 text-white"></i>
                                                                    </div>
                                                                @endif
                                                                <div>
                                                                    <span class="fw-medium">{{ $student->full_name }}</span>
                                                                    @if($student->exam_number)
                                                                        <br><small class="text-muted">{{ $student->exam_number }}</small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <span class="badge bg-{{ $student->gender == 'M' ? 'primary' : 'danger' }}">
                                                                {{ $student->gender == 'M' ? 'M' : 'F' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                    
                                                    @if($house->finalStudents->count() > 3)
                                                    <div class="list-group-item px-0 py-2 border-0 bg-transparent">
                                                        <small class="text-muted">
                                                            <i class="bx bx-dots-horizontal-rounded me-1"></i>
                                                            and {{ $house->finalStudents->count() - 3 }} more students
                                                        </small>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif
                                        </div>

                                        <div class="card-footer bg-light border-top">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="bx bxs-calendar me-1"></i>
                                                    Graduated: {{ $house->graduation_year }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Empty State for Filtered Results -->
                <div id="noResultsMessage" class="alert alert-info text-center py-5 d-none">
                    <i class="bx bx-filter-alt fa-3x mb-3 text-muted"></i>
                    <h5>No Houses Match Filter</h5>
                    <p class="mb-0">No houses found for the selected graduation term.</p>
                </div>
            </div>
        </div>
    </div>

@else
    <div class="col-md-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="bx bx-home display-1 text-muted" style="opacity: 0.5;"></i>
                </div>
                <h5 class="text-muted">Year rollover not done yet.</h5>
                <p class="text-muted mb-4">
                    There are no final year houses for the selected year. Houses are moved to the finals module during year rollover.
                </p>
            </div>
        </div>
    </div>
@endif

<script>
$(document).ready(function() {
    // Term filter functionality
    $('input[name="termFilter"]').on('change', function() {
        const selectedTerm = $(this).val();
        
        if (selectedTerm === 'all') {
            $('.term-section').show();
            $('.house-item').show();
            $('#noResultsMessage').addClass('d-none');
        } else {
            $('.term-section').hide();
            $('.house-item').hide();
            
            // Show only selected term
            $(`.term-section[data-term="${selectedTerm}"]`).show();
            $(`.house-item[data-term="${selectedTerm}"]`).show();
            
            // Check if any houses are visible
            const visibleHouses = $(`.house-item[data-term="${selectedTerm}"]:visible`).length;
            if (visibleHouses === 0) {
                $('#noResultsMessage').removeClass('d-none');
            } else {
                $('#noResultsMessage').addClass('d-none');
            }
        }
    });
    
    // Initialize tooltips for new content
    $('[data-bs-toggle="tooltip"]').tooltip();
});

// Function to view house students (can be implemented later)
function viewHouseStudents(houseId, houseName) {
    // This function can be implemented to show a modal or navigate to a detailed view
    console.log(`View students for house: ${houseName} (ID: ${houseId})`);
    
    // Example implementation - show alert for now
    // You can replace this with actual functionality later
    alert(`Feature to view students for "${houseName}" will be implemented soon.`);
}
</script>