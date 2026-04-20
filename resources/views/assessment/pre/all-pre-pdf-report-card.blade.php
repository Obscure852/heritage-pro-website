<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Report Cards</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body,
        html {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
        }

        .container {
            width: 100%;
            max-width: none;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #838383 !important;
            padding: 4px;
            text-align: start;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .page-break {
            page-break-before: always;
        }

        .page-cover {
            page-break-after: always;
        }

        .text-center {
            text-align: center;
        }

        .mt-5 {
            margin-top: 3rem;
        }
    </style>
</head>

<body>
    @foreach ($reportCards as $reportCard)
        @if (!$loop->first)
            <div class="page-break"></div>
        @endif
        <!-- Cover Page -->
        <div style="margin-top:20px;" class="container page-cover">
            <div class="row">
                <div class="col-12 text-center">
                    <img src="{{ public_path($school_setup->logo_path) }}" alt="School Logo" style="height: 120px;">
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-center mt-5">
                    <strong>{{ $school_setup->school_name }}</strong><br>
                    <span>Tel: {{ $school_setup->telephone }} Fax: {{ $school_setup->fax }}</span>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <p><strong>Firstname:</strong> {{ $reportCard['student']->fullName ?? '' }}</p>
                    <p><strong>Gender:</strong> {{ $reportCard['student']->gender }}</p>
                    <p><strong>Date:</strong> {{ now()->format('Y-m-d') }}</p>
                    <p><strong>Absent Days:</strong>
                        @php
                            $currentTermId = session('selected_term_id', \App\Helpers\TermHelper::getCurrentTerm()->id);
                            $manualEntry = $reportCard['student']
                                ->manualAttendanceEntries()
                                ->where('term_id', $currentTermId)
                                ->first();

                            if ($manualEntry && $manualEntry->days_absent !== null) {
                                $absentDays = $manualEntry->days_absent;
                            } else {
                                $absentDays = $reportCard['student']
                                    ->absentDays()
                                    ->where('term_id', $currentTermId)
                                    ->count();
                            }
                        @endphp
                        {{ $absentDays }}
                    </p>
                    <p><strong>Class:</strong> {{ $reportCard['class']->name }}</p>
                    <p><strong>School Re-opens:</strong> {{ $nextTermStartDate ?? 'N/A' }}</p>
                    <p><strong>Term Start:</strong> {{ $currentTerm->start_date ?? '' }}</p>
                    <p><strong>Term End:</strong> {{ $currentTerm->end_date ?? '' }}</p>
                </div>
            </div>
        </div>
        <div class="container">
            @foreach ($reportCard['gradeSubjects'] as $gradeSubject)
                @if ($gradeSubject->components->count() > 0 && $gradeSubject->criteriaBasedTests->count() > 0)
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th><strong>{{ $gradeSubject->subject->name }}</strong></th>
                                @foreach ($gradeSubject->criteriaBasedTests->where('type', 'Exam')->sortBy('sequence') as $test)
                                    @foreach ($gradeSubject->gradeOptionSets->first()->gradeOptions as $option)
                                        <th class="text-center">{{ $option->label }}</th>
                                    @endforeach
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($gradeSubject->components as $component)
                                <tr>
                                    <td>{{ $component->name }}</td>
                                    @foreach ($gradeSubject->criteriaBasedTests->where('type', 'Exam')->sortBy('sequence') as $test)
                                        @foreach ($gradeSubject->gradeOptionSets->first()->gradeOptions as $option)
                                            <td class="text-center">
                                                @php
                                                    $assessment = $reportCard['student']->criteriaBasedStudentTests
                                                        ->where('grade_subject_id', $gradeSubject->id)
                                                        ->where('component_id', $component->id)
                                                        ->where('criteria_based_test_id', $test->id)
                                                        ->where('grade_option_id', $option->id)
                                                        ->first();
                                                @endphp
                                                @if ($assessment)
                                                    <img src="{{ public_path('assets/images/check.png') }}" width="12px" height="12px" alt="tick">
                                                @else
                                                    <span>&nbsp;</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endforeach
        </div>
        @php
            $currentTermId = session('selected_term_id', \App\Helpers\TermHelper::getCurrentTerm()->id);
            $manualEntry = $reportCard['student']->manualAttendanceEntries()->where('term_id', $currentTermId)->first();

            $school_fees = null;
            if ($manualEntry && $manualEntry->school_fees_owing !== null) {
                $school_fees = $manualEntry->school_fees_owing;
            }
        @endphp
        <table class="table table-borderless">
            <tbody>
                <tr>
                    <td style="width: 50%;">
                        <p class="remarks"><strong>Other Information: </strong>
                            @if ($manualEntry && !empty($manualEntry->other_info))
                                {{ $manualEntry->other_info }}
                            @else
                                N/A
                            @endif
                        </p>
                    </td>
                    <td style="width: 50%;">
                        <p class="remarks"><strong>School Fees Owing: </strong>
                            @if ($school_fees !== null)
                                BWP: {{ number_format($school_fees, 2) }}
                            @else
                                N/A
                            @endif
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- Comments Page -->
        <div class="container">
            <table class="table table-bordered table-sm">
                <tr>
                    <th style="width: 50%;">Class Teacher's Remarks</th>
                    <th style="text-align: left; width: 50%;">
                        Teacher's Name: {{ $reportCard['class']->teacher->fullName ?? 'N/A' }}</th>
                </tr>
                <tr>
                    <td colspan="2">
                        <div style="min-height: 20px; padding: 2px;">
                            {{ $reportCard['classTeacherRemarks'] }}</div>
                    </td>
                </tr>
            </table>
            <br>
            <table class="table table-bordered table-sm">
                <tr>
                    <th style="width: 50%;">Head Teacher's Remarks</th>
                    <th style="text-align: left; width: 50%;">
                        Head Teacher's Name: {{ $school_head->fullName ?? 'N/A' }}</th>
                </tr>
                <tr>
                    <td colspan="2">
                        <div style="min-height: 20px; padding: 2px;">
                            {{ $reportCard['headTeachersRemarks'] }}</div>
                    </td>
                </tr>
            </table>
            <!-- Signatures -->
            <br>
            <table class="remarks" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 33%; text-align: left; vertical-align: top;">
                        <strong>Class Teacher's signature:</strong><br>
                        @if (!empty($reportCard['class']->teacher->signature_path))
                            <img src="{{ public_path($reportCard['class']->teacher->signature_path) }}"
                                alt="{{ $reportCard['class']->teacher->fullName }}'s signature" style="height: 60px;">
                        @else
                            <p style="margin-top:10px;">.....................</p>
                        @endif
                    </td>
                    <td style="width: 34%; text-align: center; vertical-align: top;">
                    </td>
                    <td style="width: 33%; text-align: left; vertical-align: top;">
                        <strong>Head Teacher's signature:</strong><br>
                        @if (!empty($school_head->signature_path))
                            <img src="{{ public_path($school_head->signature_path) }}"
                                alt="{{ $school_head->fullName }}'s signature" style="height: 60px;">
                        @else
                            <p>.....................</p>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endforeach

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
</body>

</html>
