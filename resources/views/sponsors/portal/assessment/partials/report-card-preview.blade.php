{{-- Report Card Preview Modal --}}
@php
    $gradeClass = function($grade) {
        $grade = strtoupper(trim($grade ?? ''));
        return match($grade) {
            'A', 'A+', 'A-' => 'success',
            'B', 'B+', 'B-' => 'primary',
            'C', 'C+', 'C-' => 'info',
            'D', 'D+', 'D-' => 'warning',
            default => 'danger',
        };
    };

    $currentClass = $child->currentClassRelation ? $child->currentClassRelation->first() : null;
    $bestSubjects = $termData['bestSubjects'] ?? [];
    $reportDriver = $termData['driver'] ?? 'junior';
@endphp

<div class="modal fade" id="reportCardModal-{{ $child->id }}" tabindex="-1" aria-labelledby="reportCardModalLabel-{{ $child->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportCardModalLabel-{{ $child->id }}">
                    <i class="bx bx-file-blank me-2"></i>
                    Report Card Preview - {{ $child->full_name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- PDF-like Report Card Preview --}}
                <div class="report-card-preview">
                    {{-- School Header --}}
                    <div class="report-header">
                        <div class="report-header-content">
                            <div class="report-coat-arms">
                                <img src="{{ asset('assets/images/coat_of_arms.jpg') }}" alt="Coat of Arms">
                            </div>
                            <div class="report-school-info">
                                <h3>{{ $school_data->school_name }}</h3>
                                <p>{{ $school_data->physical_address }}</p>
                                <p>{{ $school_data->postal_address }}</p>
                                <p>Tel: {{ $school_data->telephone }} @if($school_data->fax) Fax: {{ $school_data->fax }} @endif</p>
                            </div>
                            <div class="report-logo">
                                @if($school_data->logo_path)
                                    <img src="{{ asset($school_data->logo_path) }}" alt="School Logo">
                                @endif
                            </div>
                        </div>
                        <div class="report-title">
                            <h4>STUDENT PROGRESS REPORT</h4>
                            <p>Term {{ $currentTerm->term }}, {{ $currentTerm->year }}</p>
                        </div>
                    </div>

                    {{-- Student Info Section --}}
                    <div class="report-student-info">
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">First Name:</span>
                                <span class="info-value">{{ $child->first_name }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Last Name:</span>
                                <span class="info-value">{{ $child->last_name }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Gender:</span>
                                <span class="info-value">{{ $child->gender }}</span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">Class:</span>
                                <span class="info-value">{{ $currentClass->name ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Grade:</span>
                                <span class="info-value">{{ $currentClass && $currentClass->grade ? $currentClass->grade->name : 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Student #:</span>
                                <span class="info-value">{{ $child->student_number ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Subject Scores Table --}}
                    <div class="report-scores">
                        <h5 class="report-section-title">Academic Performance</h5>

                        @if($reportDriver === 'junior')
                            {{-- Junior Table --}}
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th class="text-center">%</th>
                                        <th class="text-center">Points</th>
                                        <th class="text-center">Grade</th>
                                        <th>Comments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($examTests as $test)
                                        @php
                                            $comment = $child->getSubjectComment($selectedTermId, $test->grade_subject_id ?? 0)->first();
                                            $subjectName = ($test->subject && $test->subject->subject) ? $test->subject->subject->name : 'Unknown';
                                        @endphp
                                        <tr>
                                            <td>{{ $subjectName }}</td>
                                            <td class="text-center">{{ $test->pivot->percentage ?? 0 }}%</td>
                                            <td class="text-center">{{ $test->pivot->points ?? 0 }}</td>
                                            <td class="text-center">{{ $test->pivot->grade ?? '-' }}</td>
                                            <td class="small">{{ Str::limit($comment->remarks ?? '-', 40) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="summary-row">
                                        <td colspan="2" class="text-end"><strong>Total Points:</strong></td>
                                        <td class="text-center"><strong>{{ $termData['totalPoints'] ?? 0 }}</strong></td>
                                        <td class="text-center">
                                            @if($termData['overallGrade'] ?? null)
                                                <strong>{{ $termData['overallGrade'] }}</strong>
                                            @endif
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>

                        @elseif($reportDriver === 'senior')
                            {{-- Senior Table with Best 6 --}}
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th class="text-center">%</th>
                                        <th class="text-center">Points</th>
                                        <th class="text-center">Grade</th>
                                        <th>Comments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($examTests as $test)
                                        @php
                                            $comment = $child->getSubjectComment($selectedTermId, $test->grade_subject_id ?? 0)->first();
                                            $subjectName = ($test->subject && $test->subject->subject) ? $test->subject->subject->name : 'Unknown';
                                            $isInBest6 = collect($bestSubjects)->contains(function ($best) use ($subjectName) {
                                                return strtolower($best['subject']) === strtolower($subjectName);
                                            });
                                        @endphp
                                        <tr class="{{ $isInBest6 ? 'best-subject' : '' }}">
                                            <td>
                                                {{ $subjectName }}
                                                @if($isInBest6) <span class="best-6-star">*</span> @endif
                                            </td>
                                            <td class="text-center">{{ $test->pivot->percentage ?? 0 }}%</td>
                                            <td class="text-center">{{ $test->pivot->points ?? 0 }}</td>
                                            <td class="text-center">{{ $test->pivot->grade ?? '-' }}</td>
                                            <td class="small">{{ Str::limit($comment->remarks ?? '-', 40) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="summary-row">
                                        <td colspan="2" class="text-end"><strong>Best 6 Total Points:</strong></td>
                                        <td class="text-center"><strong>{{ $termData['totalPoints'] ?? 0 }}</strong></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <p class="report-note"><span class="best-6-star">*</span> Subjects contributing to Best 6 calculation</p>

                        @elseif($reportDriver === 'primary')
                            {{-- Primary Table --}}
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th class="text-center">Possible</th>
                                        <th class="text-center">Actual</th>
                                        <th class="text-center">%</th>
                                        <th class="text-center">Grade</th>
                                        <th>Comments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($examTests as $test)
                                        @php
                                            $comment = $child->getSubjectComment($selectedTermId, $test->grade_subject_id ?? 0)->first();
                                            $subjectName = ($test->subject && $test->subject->subject) ? $test->subject->subject->name : 'Unknown';
                                        @endphp
                                        <tr>
                                            <td>{{ $subjectName }}</td>
                                            <td class="text-center">{{ $test->out_of ?? 100 }}</td>
                                            <td class="text-center">{{ $test->pivot->score ?? 0 }}</td>
                                            <td class="text-center">{{ $test->pivot->percentage ?? 0 }}%</td>
                                            <td class="text-center">{{ $test->pivot->grade ?? '-' }}</td>
                                            <td class="small">{{ Str::limit($comment->remarks ?? '-', 40) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="summary-row">
                                        <td class="text-end"><strong>Total:</strong></td>
                                        <td class="text-center"><strong>{{ $termData['totalOutOf'] ?? 0 }}</strong></td>
                                        <td class="text-center"><strong>{{ $termData['totalScore'] ?? 0 }}</strong></td>
                                        <td class="text-center"><strong>{{ $termData['averagePercentage'] ?? 0 }}%</strong></td>
                                        <td class="text-center">
                                            @if($termData['overallGrade'] ?? null)
                                                <strong>{{ $termData['overallGrade']->grade ?? '-' }}</strong>
                                            @endif
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        @endif
                    </div>

                    {{-- Remarks Section --}}
                    <div class="report-remarks">
                        <div class="remarks-box">
                            <div class="remarks-header">
                                <span class="remarks-title">Class Teacher's Remarks</span>
                                <span class="teacher-name">{{ $currentClass && $currentClass->teacher ? $currentClass->teacher->full_name : 'N/A' }}</span>
                            </div>
                            <div class="remarks-content">
                                @php
                                    // Try to get class teacher remarks from manual entry
                                    $manualEntry = $child->manualAttendanceEntries()
                                        ->where('term_id', $selectedTermId)
                                        ->first();
                                @endphp
                                {{ $manualEntry->class_teacher_remarks ?? 'No remarks provided.' }}
                            </div>
                        </div>

                        <div class="remarks-box mt-3">
                            <div class="remarks-header">
                                <span class="remarks-title">Head Teacher's Remarks</span>
                            </div>
                            <div class="remarks-content">
                                {{ $manualEntry->head_teacher_remarks ?? 'No remarks provided.' }}
                            </div>
                        </div>
                    </div>

                    {{-- Footer Note --}}
                    <div class="report-footer-note">
                        <p class="text-muted small">
                            <i class="bx bx-info-circle me-1"></i>
                            This is a preview of the report card. For the official document, please contact the school administration.
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
