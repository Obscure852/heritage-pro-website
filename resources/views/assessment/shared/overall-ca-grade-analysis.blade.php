@extends('layouts.master')
@section('title')
    Overall CA Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="#"
                onclick="event.preventDefault(); 
            if (document.referrer) {
                history.back();
            } else {
                window.location = '{{ $gradebookBackUrl }}';
            }">Back
            </a>
        @endslot
        @slot('title')
            Grade Overall CA Analysis
        @endslot
    @endcomponent
@section('css')
    <style>
        .report-card {
            margin-top: 0mm;
            margin-bottom: 20mm;
        }

        body {
            font-size: 12px;
        }

        textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #333;
            padding: 5px;
            margin: 10px 0;
        }

        @media print {
            @page {
                size: landscape;
                margin: 5mm;
            }

            body {
                width: 100%;
                margin: 0;
                padding: 0;
                font-size: 12px;
                line-height: 1.1;
            }

            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            canvas,
            .graph-container {
                display: none !important;
            }

            .printable {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 5mm;
            }

            .card {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }

            .card-header {
                padding: 5mm;
                margin-bottom: 3mm;
            }

            .card-header .row {
                display: flex;
                align-items: center;
            }

            .card-header .col-md-6,
            .card-header .col-lg-6 {
                display: flex;
                align-items: center;
            }

            .card-header img {
                height: 30px;
                width: auto;
                visibility: hidden;
                margin-top: -40px;
            }

            .table {
                width: 100%;
                margin-bottom: 3mm;
                margin-top: 10px;
                page-break-inside: avoid;
                font-size: 10px;
            }

            .table th,
            .table td {
                padding: 1mm;
                white-space: nowrap;
            }

            .table-sm td,
            .table-sm th {
                padding: 0.5mm 1mm;
            }

            h5 {
                margin: 2mm 0;
                font-size: 9px;
            }

            .form-group {
                font-size: 12px !important;
                line-height: 1.2;
            }

            .table-responsive {
                margin-bottom: 3mm;
            }

            .report-card {
                margin: 0;
                padding: 0;
                page-break-before: avoid;
                page-break-after: avoid;
            }

            .row {
                page-break-inside: avoid;
            }

            .card-body {
                page-break-before: avoid;
                page-break-after: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
@endsection

<div class="row">
    <div class="col-md-12 col-lg-12 d-flex justify-content-end">
        <i onclick="window.location.href='{{ request()->fullUrlWithQuery(['export' => 'true']) }}'"
            style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-download text-muted me-2"></i>
        <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;"
            class="bx bx-printer text-muted me-2"></i>
    </div>
</div>
{{-- Print from here to the bottom only --}}
<div class="row printable">
    <div class="col-md-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6 col-lg-6 align-items-start">
                        <div style="font-size:14px;" class="form-group">
                            <strong>{{ $school_data->school_name }}</strong>
                            <br>
                            <span style="maring:0;padding:0;"> {{ $school_data->physical_address }}</span>
                            <br>
                            <span style="maring:0;padding:0;"> {{ $school_data->postal_address }}</span>
                            <br>
                            <span>Tel: {{ $school_data->telephone . ' Fax: ' . $school_data->fax }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 d-flex justify-content-end">
                        <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="report-card">
                    <div class="row">
                        <h5>{{ $klass->grade->name }} End of {{ $test->name ?? 'Month' }} Grade Overall Analysis - Term
                            {{ $test->term->term ?? '' }}, {{ $test->term->year ?? '' }}</h5>
                        <div class="col-md-12 col-lg-12">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <td>#</td>
                                            <th>Name</th>
                                            <th>Class</th>
                                            <th>Sex</th>
                                            <th>PSLE</th>
                                            @foreach ($allSubjects as $subject)
                                                <th title="{{ $subject }}" colspan="2"
                                                    style="text-align:center">
                                                    {{ substr($subject, 0, 3) }}
                                                </th>
                                            @endforeach
                                            <th>TP</th>
                                            <th>OG</th>
                                            <th>Pos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($reportCards as $index => $reportCard)
                                            @php
                                                $studentClass = $reportCard['student']->currentClass();
                                                $studentClassSubjects =
                                                    $studentClass && $studentClass->subjectClasses
                                                        ? $studentClass->subjectClasses
                                                            ->pluck('subject.subject.name')
                                                            ->toArray()
                                                        : [];

                                                $studentOptionalSubjects = $reportCard['student']->optionalSubjects
                                                    ? $reportCard['student']->optionalSubjects
                                                        ->pluck('gradeSubject.subject.name')
                                                        ->toArray()
                                                    : [];

                                                $studentSubjects = array_merge(
                                                    $studentClassSubjects,
                                                    $studentOptionalSubjects,
                                                );
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $reportCard['student']->fullName ?? '' }}</td>
                                                <td>{{ $reportCard['class_name'] ?? '' }}</td>
                                                <td>{{ $reportCard['student']->gender ?? '' }}</td>
                                                <td>{{ optional($reportCard['student']->psle)->overall_grade ?? '' }}
                                                </td>

                                                @foreach ($allSubjects as $subject)
                                                    @php
                                                        $subjectData = $reportCard['scores'][$subject] ?? null;
                                                        $subjectScore = $subjectData['percentage'] ?? null;
                                                        $subjectGrade = $subjectData['grade'] ?? 'X';
                                                    @endphp

                                                    @if (in_array($subject, $studentSubjects))
                                                        {{-- Student takes this subject --}}
                                                        @if (is_null($subjectScore) && $subjectGrade === 'X')
                                                            {{-- No scores recorded, show X --}}
                                                            <td>X</td>
                                                            <td>X</td>
                                                        @else
                                                            <td>{{ is_numeric($subjectScore) ? round($subjectScore) : $subjectScore }}
                                                            </td>
                                                            <td>{{ $subjectGrade }}</td>
                                                        @endif
                                                    @else
                                                        {{-- Student does not take this subject --}}
                                                        <td></td>
                                                        <td></td>
                                                    @endif
                                                @endforeach

                                                <td>{{ is_numeric($reportCard['totalPoints']) ? $reportCard['totalPoints'] : 'X' }}
                                                </td>
                                                <td>{{ $reportCard['grade'] ?? 'X' }}</td>
                                                <td>{{ $reportCard['position'] ?? '' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ count($allSubjects) * 2 + 6 }}"
                                                    style="text-align:center">No Students Found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="row">
                                <h5>{{ $klass->grade->name }} Overall Grade Analysis</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th rowspan="3">Grade</th>

                                                <!-- Individual Grades: now colspan="3" -->
                                                <th style="text-align: center;" colspan="3">M</th>
                                                <th style="text-align: center;" colspan="3">A</th>
                                                <th style="text-align: center;" colspan="3">B</th>
                                                <th style="text-align: center;" colspan="3">C</th>
                                                <th style="text-align: center;" colspan="3">D</th>
                                                <th style="text-align: center;" colspan="3">E</th>
                                                <th style="text-align: center;" colspan="3">U</th>

                                                <!-- Combined Metrics -->
                                                <th style="text-align: center;" colspan="6">MAB</th>
                                                <th style="text-align: center;" colspan="6">MABC</th>
                                                <th style="text-align: center;" colspan="6">MABCD</th>
                                                <th style="text-align: center;" colspan="6">DEU</th>
                                                <th style="text-align: center;" colspan="6">X</th>
                                            </tr>
                                            <tr>
                                                <!-- Individual Grades placeholders -->
                                                <th colspan="3"></th>
                                                <th colspan="3"></th>
                                                <th colspan="3"></th>
                                                <th colspan="3"></th>
                                                <th colspan="3"></th>
                                                <th colspan="3"></th>
                                                <th colspan="3"></th>

                                                <!-- Combined metrics second-level headers (Raw and %) -->
                                                <th style="text-align: center;" colspan="3">Raw</th>
                                                <th style="text-align: center;" colspan="3">%</th>
                                                <th style="text-align: center;" colspan="3">Raw</th>
                                                <th style="text-align: center;" colspan="3">%</th>
                                                <th style="text-align: center;" colspan="3">Raw</th>
                                                <th style="text-align: center;" colspan="3">%</th>
                                                <th style="text-align: center;" colspan="3">Raw</th>
                                                <th style="text-align: center;" colspan="3">%</th>
                                                <th style="text-align: center;" colspan="3">Raw</th>
                                                <th style="text-align: center;" colspan="3">%</th>
                                            </tr>
                                            <tr>
                                                <!-- Individual Grades (M, F, T) -->
                                                <th>M</th>
                                                <th>F</th>
                                                <th>T</th>
                                                <th>M</th>
                                                <th>F</th>
                                                <th>T</th>
                                                <th>M</th>
                                                <th>F</th>
                                                <th>T</th>
                                                <th>M</th>
                                                <th>F</th>
                                                <th>T</th>
                                                <th>M</th>
                                                <th>F</th>
                                                <th>T</th>
                                                <th>M</th>
                                                <th>F</th>
                                                <th>T</th>
                                                <th>M</th>
                                                <th>F</th>
                                                <th>T</th>

                                                <!-- MAB Raw (M,F,T) and % (M,F,T) -->
                                                @foreach (['MAB', 'MAB%', 'MABC', 'MABC%', 'MABCD', 'MABCD%', 'DEU', 'DEU%', 'X', 'X%'] as $block)
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                @endforeach
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr>
                                                <td>Total</td>

                                                <!-- Individual Grades totals with T = M+F -->
                                                <td>{{ $gradeCounts['M']['M'] }}</td>
                                                <td>{{ $gradeCounts['M']['F'] }}</td>
                                                <td>{{ $gradeCounts['M']['M'] + $gradeCounts['M']['F'] }}</td>

                                                <td>{{ $gradeCounts['A']['M'] }}</td>
                                                <td>{{ $gradeCounts['A']['F'] }}</td>
                                                <td>{{ $gradeCounts['A']['M'] + $gradeCounts['A']['F'] }}</td>

                                                <td>{{ $gradeCounts['B']['M'] }}</td>
                                                <td>{{ $gradeCounts['B']['F'] }}</td>
                                                <td>{{ $gradeCounts['B']['M'] + $gradeCounts['B']['F'] }}</td>

                                                <td>{{ $gradeCounts['C']['M'] }}</td>
                                                <td>{{ $gradeCounts['C']['F'] }}</td>
                                                <td>{{ $gradeCounts['C']['M'] + $gradeCounts['C']['F'] }}</td>

                                                <td>{{ $gradeCounts['D']['M'] }}</td>
                                                <td>{{ $gradeCounts['D']['F'] }}</td>
                                                <td>{{ $gradeCounts['D']['M'] + $gradeCounts['D']['F'] }}</td>

                                                <td>{{ $gradeCounts['E']['M'] }}</td>
                                                <td>{{ $gradeCounts['E']['F'] }}</td>
                                                <td>{{ $gradeCounts['E']['M'] + $gradeCounts['E']['F'] }}</td>

                                                <td>{{ $gradeCounts['U']['M'] }}</td>
                                                <td>{{ $gradeCounts['U']['F'] }}</td>
                                                <td>{{ $gradeCounts['U']['M'] + $gradeCounts['U']['F'] }}</td>

                                                <!-- MAB Raw -->
                                                <td>{{ $mab_M }}</td>
                                                <td>{{ $mab_F }}</td>
                                                <td>{{ $mab_T }}</td>
                                                <!-- MAB % -->
                                                <td>{{ $mab_M_Percentage }}</td>
                                                <td>{{ $mab_F_Percentage }}</td>
                                                <td>{{ $mab_T_percentage }}</td>

                                                <!-- MABC Raw -->
                                                <td>{{ $mabc_M }}</td>
                                                <td>{{ $mabc_F }}</td>
                                                <td>{{ $mabc_T }}</td>
                                                <!-- MABC % -->
                                                <td>{{ $mabc_M_Percentage }}</td>
                                                <td>{{ $mabc_F_Percentage }}</td>
                                                <td>{{ $mabc_T_percentage }}</td>

                                                <!-- MABCD Raw -->
                                                <td>{{ $mabcd_M }}</td>
                                                <td>{{ $mabcd_F }}</td>
                                                <td>{{ $mabcd_T }}</td>
                                                <!-- MABCD % -->
                                                <td>{{ $mabcd_M_Percentage }}</td>
                                                <td>{{ $mabcd_F_Percentage }}</td>
                                                <td>{{ $mabcd_T_percentage }}</td>

                                                <!-- DEU Raw -->
                                                <td>{{ $deu_M }}</td>
                                                <td>{{ $deu_F }}</td>
                                                <td>{{ $deu_T }}</td>
                                                <!-- DEU % -->
                                                <td>{{ $deu_M_Percentage }}</td>
                                                <td>{{ $deu_F_Percentage }}</td>
                                                <td>{{ $deu_T_percentage }}</td>

                                                <!-- X Raw -->
                                                <td>{{ $x_M }}</td>
                                                <td>{{ $x_F }}</td>
                                                <td>{{ $x_T }}</td>
                                                <!-- X % -->
                                                <td>{{ $x_M_Percentage }}</td>
                                                <td>{{ $x_F_Percentage }}</td>
                                                <td>{{ $x_T_Percentage }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 col-md-12">
                                    <canvas id="gradeDistributionLineChart" width="400" height="100"></canvas>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <h5>{{ $klass->grade->name }} PSLE Performance Analysis</h5>
                                <div class="col-md-12 col-lg-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th rowspan="3">Grade</th>
                                                    <!-- Individual Grades now colspan=3 -->
                                                    <th style="text-align: center;" colspan="3">A</th>
                                                    <th style="text-align: center;" colspan="3">B</th>
                                                    <th style="text-align: center;" colspan="3">C</th>
                                                    <th style="text-align: center;" colspan="3">D</th>
                                                    <th style="text-align: center;" colspan="3">E</th>
                                                    <th style="text-align: center;" colspan="3">U</th>
                                                    <!-- Combined Metrics -->
                                                    <th style="text-align: center;" colspan="6">AB</th>
                                                    <th style="text-align: center;" colspan="6">ABC</th>
                                                    <th style="text-align: center;" colspan="6">ABCD</th>
                                                    <th style="text-align: center;" colspan="6">DEU</th>
                                                </tr>
                                                <tr>
                                                    <!-- placeholders for individual grades -->
                                                    <th colspan="3"></th>
                                                    <th colspan="3"></th>
                                                    <th colspan="3"></th>
                                                    <th colspan="3"></th>
                                                    <th colspan="3"></th>
                                                    <th colspan="3"></th>

                                                    <!-- AB -->
                                                    <th style="text-align: center;" colspan="3">Raw</th>
                                                    <th style="text-align: center;" colspan="3">%</th>
                                                    <!-- ABC -->
                                                    <th style="text-align: center;" colspan="3">Raw</th>
                                                    <th style="text-align: center;" colspan="3">%</th>
                                                    <!-- ABCD -->
                                                    <th style="text-align: center;" colspan="3">Raw</th>
                                                    <th style="text-align: center;" colspan="3">%</th>
                                                    <!-- DEU -->
                                                    <th style="text-align: center;" colspan="3">Raw</th>
                                                    <th style="text-align: center;" colspan="3">%</th>
                                                </tr>
                                                <tr>
                                                    <!-- Individual Grades headers -->
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>

                                                    <!-- AB -->
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                    <!-- ABC -->
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                    <!-- ABCD -->
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                    <!-- DEU -->
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Total</td>
                                                    <!-- Individual Grades with T = M + F -->
                                                    <td>{{ $psleGradeCounts['A']['M'] }}</td>
                                                    <td>{{ $psleGradeCounts['A']['F'] }}</td>
                                                    <td>{{ $psleGradeCounts['A']['M'] + $psleGradeCounts['A']['F'] }}
                                                    </td>

                                                    <td>{{ $psleGradeCounts['B']['M'] }}</td>
                                                    <td>{{ $psleGradeCounts['B']['F'] }}</td>
                                                    <td>{{ $psleGradeCounts['B']['M'] + $psleGradeCounts['B']['F'] }}
                                                    </td>

                                                    <td>{{ $psleGradeCounts['C']['M'] }}</td>
                                                    <td>{{ $psleGradeCounts['C']['F'] }}</td>
                                                    <td>{{ $psleGradeCounts['C']['M'] + $psleGradeCounts['C']['F'] }}
                                                    </td>

                                                    <td>{{ $psleGradeCounts['D']['M'] }}</td>
                                                    <td>{{ $psleGradeCounts['D']['F'] }}</td>
                                                    <td>{{ $psleGradeCounts['D']['M'] + $psleGradeCounts['D']['F'] }}
                                                    </td>

                                                    <td>{{ $psleGradeCounts['E']['M'] }}</td>
                                                    <td>{{ $psleGradeCounts['E']['F'] }}</td>
                                                    <td>{{ $psleGradeCounts['E']['M'] + $psleGradeCounts['E']['F'] }}
                                                    </td>

                                                    <td>{{ $psleGradeCounts['U']['M'] }}</td>
                                                    <td>{{ $psleGradeCounts['U']['F'] }}</td>
                                                    <td>{{ $psleGradeCounts['U']['M'] + $psleGradeCounts['U']['F'] }}
                                                    </td>

                                                    <!-- AB Raw -->
                                                    <td>{{ $psleAB_M }}</td>
                                                    <td>{{ $psleAB_F }}</td>
                                                    <td>{{ $psleAB_T }}</td>
                                                    <!-- AB % -->
                                                    <td>{{ $psleAB_M_Percentage }}</td>
                                                    <td>{{ $psleAB_F_Percentage }}</td>
                                                    <td>{{ $psleAB_T_Percentage ?? '' }}</td>

                                                    <!-- ABC Raw -->
                                                    <td>{{ $psleABC_M }}</td>
                                                    <td>{{ $psleABC_F }}</td>
                                                    <td>{{ $psleABC_T }}</td>
                                                    <!-- ABC % -->
                                                    <td>{{ $psleABC_M_Percentage }}</td>
                                                    <td>{{ $psleABC_F_Percentage }}</td>
                                                    <td>{{ $psleABC_T_Percentage }}</td>

                                                    <!-- ABCD Raw -->
                                                    <td>{{ $psleABCD_M }}</td>
                                                    <td>{{ $psleABCD_F }}</td>
                                                    <td>{{ $psleABCD_T }}</td>
                                                    <!-- ABCD % -->
                                                    <td>{{ $psleABCD_M_Percentage }}</td>
                                                    <td>{{ $psleABCD_F_Percentage }}</td>
                                                    <td>{{ $psleABCD_T_Percentage }}</td>

                                                    <!-- DEU Raw -->
                                                    <td>{{ $psleDEU_M }}</td>
                                                    <td>{{ $psleDEU_F }}</td>
                                                    <td>{{ $psleDEU_T }}</td>
                                                    <!-- DEU % -->
                                                    <td>{{ $psleDEU_M_Percentage }}</td>
                                                    <td>{{ $psleDEU_F_Percentage }}</td>
                                                    <td>{{ $psleDEU_T_Percentage }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 col-lg-12">
                                    <canvas id="psleGradeLineChart" width="400" height="100"></canvas>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-lg-12 col-md-12">
                                    <h5>{{ $klass->grade->name }} Subjects Analysis</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <th style="text-align:center" colspan="3">A</th>
                                                    <th style="text-align:center" colspan="3">B</th>
                                                    <th style="text-align:center" colspan="3">C</th>
                                                    <th style="text-align:center" colspan="3">D</th>
                                                    <th style="text-align:center" colspan="3">E</th>
                                                    <th style="text-align:center" colspan="3">U</th>
                                                    <th style="text-align:center" colspan="3">X</th>
                                                    <th style="text-align:center" colspan="3">AB%</th>
                                                    <th style="text-align:center" colspan="3">ABC%</th>
                                                    <th style="text-align:center" colspan="3">ABCD%</th>
                                                    <th style="text-align:center" colspan="3">DEU%</th>
                                                    <th style="text-align:center" colspan="3">X%</th>
                                                </tr>
                                                <tr>
                                                    <th>Sex</th>
                                                    {{-- Raw grade headings --}}
                                                    @foreach (['A', 'B', 'C', 'D', 'E', 'U', 'X'] as $g)
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th>
                                                    @endforeach

                                                    {{-- Percentage headings --}}
                                                    @foreach (['AB%', 'ABC%', 'ABCD%', 'DEU%', 'X%'] as $p)
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th>
                                                    @endforeach
                                                </tr>
                                            </thead>

                                            <tbody>
                                                {{-- per-subject rows --}}
                                                @foreach ($subjectGradeCounts as $subject => $counts)
                                                    <tr>
                                                        <td>{{ $subject }}</td>

                                                        {{-- Raw grade counts with T = M+F --}}
                                                        @foreach (['A', 'B', 'C', 'D', 'E', 'U', 'X'] as $g)
                                                            <td>{{ $counts[$g]['M'] }}</td>
                                                            <td>{{ $counts[$g]['F'] }}</td>
                                                            <td>{{ $counts[$g]['M'] + $counts[$g]['F'] }}</td>
                                                        @endforeach

                                                        {{-- Percentage metrics with correctly calculated T --}}
                                                        @foreach (['AB%', 'ABC%', 'ABCD%', 'DEU%', 'X%'] as $p)
                                                            <td>{{ $counts[$p]['M'] }}%</td>
                                                            <td>{{ $counts[$p]['F'] }}%</td>
                                                            <td>
                                                                @php
                                                                    $totalEnrolledSubject =
                                                                        $counts['enrolled']['M'] +
                                                                        $counts['enrolled']['F'];

                                                                    if ($totalEnrolledSubject > 0) {
                                                                        if ($p === 'AB%') {
                                                                            $combinedCount =
                                                                                $counts['A']['M'] +
                                                                                $counts['A']['F'] +
                                                                                $counts['B']['M'] +
                                                                                $counts['B']['F'];
                                                                        } elseif ($p === 'ABC%') {
                                                                            $combinedCount =
                                                                                $counts['A']['M'] +
                                                                                $counts['A']['F'] +
                                                                                $counts['B']['M'] +
                                                                                $counts['B']['F'] +
                                                                                $counts['C']['M'] +
                                                                                $counts['C']['F'];
                                                                        } elseif ($p === 'ABCD%') {
                                                                            $combinedCount =
                                                                                $counts['A']['M'] +
                                                                                $counts['A']['F'] +
                                                                                $counts['B']['M'] +
                                                                                $counts['B']['F'] +
                                                                                $counts['C']['M'] +
                                                                                $counts['C']['F'] +
                                                                                $counts['D']['M'] +
                                                                                $counts['D']['F'];
                                                                        } elseif ($p === 'DEU%') {
                                                                            $combinedCount =
                                                                                $counts['D']['M'] +
                                                                                $counts['D']['F'] +
                                                                                $counts['E']['M'] +
                                                                                $counts['E']['F'] +
                                                                                $counts['U']['M'] +
                                                                                $counts['U']['F'];
                                                                        } elseif ($p === 'X%') {
                                                                            $combinedCount =
                                                                                $counts['X']['M'] + $counts['X']['F'];
                                                                        }

                                                                        $subjectTotalPercentage = round(
                                                                            ($combinedCount / $totalEnrolledSubject) *
                                                                                100,
                                                                            2,
                                                                        );
                                                                    } else {
                                                                        $subjectTotalPercentage = 0;
                                                                    }
                                                                @endphp
                                                                {{ $subjectTotalPercentage }}%
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach

                                                {{-- grand-totals row --}}
                                                <tr style="font-weight:600;background:#f3f3f3;">
                                                    <td>Totals</td>

                                                    {{-- Raw grade totals --}}
                                                    @foreach (['A', 'B', 'C', 'D', 'E', 'U', 'X'] as $g)
                                                        <td>{{ $subjectTotals[$g]['M'] }}</td>
                                                        <td>{{ $subjectTotals[$g]['F'] }}</td>
                                                        <td>{{ $subjectTotals[$g]['M'] + $subjectTotals[$g]['F'] }}
                                                        </td>
                                                    @endforeach

                                                    {{-- Correctly calculated percentage totals using pre-calculated values --}}
                                                    @foreach (['AB%', 'ABC%', 'ABCD%', 'DEU%', 'X%'] as $p)
                                                        <td>{{ $subjectTotals[$p]['M'] }}%</td>
                                                        <td>{{ $subjectTotals[$p]['F'] }}%</td>
                                                        <td>{{ $subjectTotals[$p]['T'] }}%</td>
                                                    @endforeach
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 col-md-12">
                                    <canvas id="gradeDistributionChart" width="400" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    var subjectGradeCounts = @json($subjectGradeCounts);
    var gradeCounts = @json($gradeCounts);

    function printContent() {
        window.print();
    }

    var ctx = document.getElementById('gradeDistributionChart').getContext('2d');
    var cty = document.getElementById('gradeDistributionLineChart').getContext('2d');

    var gradeDistributionChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(subjectGradeCounts),
            datasets: [{
                    label: 'Male - A Grades',
                    data: Object.values(subjectGradeCounts).map(counts => counts['A']['M']),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                },
                {
                    label: 'Female - A Grades',
                    data: Object.values(subjectGradeCounts).map(counts => counts['A']['F']),
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                },
                {
                    label: 'Male - B Grades',
                    data: Object.values(subjectGradeCounts).map(counts => counts['B']['M']),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                },
                {
                    label: 'Female - B Grades',
                    data: Object.values(subjectGradeCounts).map(counts => counts['B']['F']),
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                },
                {
                    label: 'Male - C Grades',
                    data: Object.values(subjectGradeCounts).map(counts => counts['C']['M']),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                },
                {
                    label: 'Female - C Grades',
                    data: Object.values(subjectGradeCounts).map(counts => counts['C']['F']),
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                },
                {
                    label: 'Male - D Grades',
                    data: Object.values(subjectGradeCounts).map(counts => counts['D']['M']),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                },
                {
                    label: 'Female - D Grades',
                    data: Object.values(subjectGradeCounts).map(counts => counts['D']['F']),
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    var gradeDistributionLineChart = new Chart(cty, {
        type: 'line',
        data: {
            labels: ['M', 'A', 'B', 'C', 'D', 'E', 'U'],
            datasets: [{
                label: 'Male',
                data: [
                    gradeCounts['M']['M'],
                    gradeCounts['A']['M'],
                    gradeCounts['B']['M'],
                    gradeCounts['C']['M'],
                    gradeCounts['D']['M'],
                    gradeCounts['E']['M'],
                    gradeCounts['U']['M']
                ],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                fill: false
            }, {
                label: 'Female',
                data: [
                    gradeCounts['M']['F'],
                    gradeCounts['A']['F'],
                    gradeCounts['B']['F'],
                    gradeCounts['C']['F'],
                    gradeCounts['D']['F'],
                    gradeCounts['E']['F'],
                    gradeCounts['U']['F']
                ],
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2,
                fill: false
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });


    var psleGradeCounts = @json($psleGradeCounts);
    var grades = ['A', 'B', 'C', 'D', 'E', 'U'];
    var maleCounts = grades.map(grade => psleGradeCounts[grade]['M']);
    var femaleCounts = grades.map(grade => psleGradeCounts[grade]['F']);

    const ctz = document.getElementById('psleGradeLineChart').getContext('2d');
    const psleGradeLineChart = new Chart(ctz, {
        type: 'line',
        data: {
            labels: grades,
            datasets: [{
                label: 'Male Students',
                data: maleCounts,
                fill: false,
                borderColor: 'rgb(54, 162, 235)',
                tension: 0.1
            }, {
                label: 'Female Students',
                data: femaleCounts,
                fill: false,
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Students'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'PSLE Grade'
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'PSLE Grade Distribution by Gender'
                }
            }
        }
    });
</script>
@endsection
