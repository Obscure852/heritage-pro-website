@extends('layouts.master')
@section('title')
    Continuous Assessment Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="#" onclick="event.preventDefault(); 
                if (document.referrer) {
                history.back();
                } else {
                window.location = '{{ $gradebookBackUrl }}';
                }   
            ">Back</a>
        @endslot
        @slot('title')
            {{ $klass->name }} CA Class Analysis
        @endslot
    @endcomponent
    <style>
        .card {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }
        
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
            body {
                width: 100%;
                margin: 0;
                padding: 0;
                font-size: 10px;
                line-height: normal;
            }

            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            .printable {
                position: absolute;
                left: 50%;
                top: 0;
                transform: translateX(-50%);
                width: 350mm;
                height: 297mm;
                margin-left: 250px;
                margin-top: 80px;
                padding: 0;
                page-break-after: avoid;
            }


            .card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0 10mm;
            }

            .card-header img {
                width: 300px;
                height: 120px;
            }

            .table {
                width: 100%;
                table-layout: fixed;
            }

            .table th,
            .table td {
                width: auto;
                overflow: visible;
                word-wrap: break-word;
            }

            textarea {
                border: none;
            }

            .card {
                box-shadow: none;
            }
        }
    </style>
    <div class="row">
        <div class="col-12 d-flex justify-content-end">
            <i onclick="window.location.href='{{ request()->fullUrlWithQuery(['export' => 'true']) }}'"
                style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-download me-2 text-muted"></i>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer text-muted"></i>
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
                            <h5>{{ $klass->name }} CA Class Analysis</h5>
                            <div class="col-12">
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
                                    <h5>{{ $klass->name }} Class Analysis</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th rowspan="3">Grade</th>
                                                    <!-- Individual Grades -->
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
                                                    <!-- Individual grades second-level headers -->
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
                                                    <!-- Individual Grades (M,F,T) -->
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
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                        
                                                    <!-- MABC Raw (M,F,T) and % (M,F,T) -->
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                        
                                                    <!-- MABCD Raw (M,F,T) and % (M,F,T) -->
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                        
                                                    <!-- DEU Raw (M,F,T) and % (M,F,T) -->
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                                    <th>M</th>
                                                    <th>F</th>
                                                    <th>T</th>
                                        
                                                    <!-- X Raw (M,F,T) and % (M,F,T) -->
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
                                                    <!-- Individual Grades with Totals -->
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
                                        <div id="gradeDistributionChart" style="width: 100%; height: 600px;"></div> 
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <h5>{{ $klass->name }} PSLE Performance Analysis</h5>
                                    <div class="col-md-12 col-lg-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="3">Grade</th>
                                                        <!-- Individual Grades -->
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
                                                        <!-- Individual grades have no sub-headers other than M,F,T -->
                                                        <th colspan="3"></th>
                                                        <th colspan="3"></th>
                                                        <th colspan="3"></th>
                                                        <th colspan="3"></th>
                                                        <th colspan="3"></th>
                                                        <th colspan="3"></th>
                                            
                                                        <!-- AB: Raw and % -->
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
                                                        <!-- Individual Grades -->
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th> <!-- A -->
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th> <!-- B -->
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th> <!-- C -->
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th> <!-- D -->
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th> <!-- E -->
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th> <!-- U -->
                                            
                                                        <!-- AB Raw -->
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th>
                                                        <!-- AB % -->
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th>
                                            
                                                        <!-- ABC Raw -->
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th>
                                                        <!-- ABC % -->
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th>
                                            
                                                        <!-- ABCD Raw -->
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th>
                                                        <!-- ABCD % -->
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th>
                                            
                                                        <!-- DEU Raw -->
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th>
                                                        <!-- DEU % -->
                                                        <th>M</th>
                                                        <th>F</th>
                                                        <th>T</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Total</td>
                                                        <!-- Individual Grades with Totals -->
                                                        <td>{{ $psleGradeCounts['A']['M'] }}</td>
                                                        <td>{{ $psleGradeCounts['A']['F'] }}</td>
                                                        <td>{{ $psleGradeCounts['A']['M'] + $psleGradeCounts['A']['F'] }}</td>
                                                        <td>{{ $psleGradeCounts['B']['M'] }}</td>
                                                        <td>{{ $psleGradeCounts['B']['F'] }}</td>
                                                        <td>{{ $psleGradeCounts['B']['M'] + $psleGradeCounts['B']['F'] }}</td>
                                                        <td>{{ $psleGradeCounts['C']['M'] }}</td>
                                                        <td>{{ $psleGradeCounts['C']['F'] }}</td>
                                                        <td>{{ $psleGradeCounts['C']['M'] + $psleGradeCounts['C']['F'] }}</td>
                                                        <td>{{ $psleGradeCounts['D']['M'] }}</td>
                                                        <td>{{ $psleGradeCounts['D']['F'] }}</td>
                                                        <td>{{ $psleGradeCounts['D']['M'] + $psleGradeCounts['D']['F'] }}</td>
                                                        <td>{{ $psleGradeCounts['E']['M'] }}</td>
                                                        <td>{{ $psleGradeCounts['E']['F'] }}</td>
                                                        <td>{{ $psleGradeCounts['E']['M'] + $psleGradeCounts['E']['F'] }}</td>
                                                        <td>{{ $psleGradeCounts['U']['M'] }}</td>
                                                        <td>{{ $psleGradeCounts['U']['F'] }}</td>
                                                        <td>{{ $psleGradeCounts['U']['M'] + $psleGradeCounts['U']['F'] }}</td>
                                            
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
                                        <div id="psleGradeLineChart" style="width: 100%; height: 400px;"></div> 
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-lg-12 col-md-12">
                                        <h5>{{ $klass->name }} Subjects Analysis</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2">Subject</th>
                                                        <th rowspan="2" style="text-align: center;">Enrolled</th>
                                                        <th style="text-align: center;" colspan="3">A</th>
                                                        <th style="text-align: center;" colspan="3">B</th>
                                                        <th style="text-align: center;" colspan="3">C</th>
                                                        <th style="text-align: center;" colspan="3">D</th>
                                                        <th style="text-align: center;" colspan="3">E</th>
                                                        <th style="text-align: center;" colspan="3">U</th>
                                                        <th style="text-align: center;" colspan="3">NS</th>
                                                        <th style="text-align: center;" colspan="3">AB%</th>
                                                        <th style="text-align: center;" colspan="3">ABC%</th>
                                                        <th style="text-align: center;" colspan="3">ABCD%</th>
                                                        <th style="text-align: center;" colspan="3">DEU%</th>
                                                        <th style="text-align: center;" colspan="3">NS%</th>
                                                    </tr>
                                                    <tr>
                                                        <th>M/F</th>
                                                        {{-- Raw grade headings --}}
                                                        <th>M</th><th>F</th><th>T</th>
                                                        <th>M</th><th>F</th><th>T</th>
                                                        <th>M</th><th>F</th><th>T</th>
                                                        <th>M</th><th>F</th><th>T</th>
                                                        <th>M</th><th>F</th><th>T</th>
                                                        <th>M</th><th>F</th><th>T</th>
                                                        <th>M</th><th>F</th><th>T</th>
                                
                                                        {{-- Percentage headings --}}
                                                        <th>M</th><th>F</th><th>T</th> {{-- AB% --}}
                                                        <th>M</th><th>F</th><th>T</th> {{-- ABC% --}}
                                                        <th>M</th><th>F</th><th>T</th> {{-- ABCD% --}}
                                                        <th>M</th><th>F</th><th>T</th> {{-- DEU% --}}
                                                        <th>M</th><th>F</th><th>T</th> {{-- NS% --}}
                                                    </tr>
                                                </thead>
                                
                                                <tbody>
                                                    {{-- Per-subject rows --}}
                                                    @foreach ($subjectGradeCounts as $subject => $counts)
                                                        <tr>
                                                            <td>{{ $subject }}</td>
                                                            {{-- Enrolled counts --}}
                                                            <td style="text-align: center;">{{ $counts['enrolled']['M'] }}/{{ $counts['enrolled']['F'] }}</td>
                                                            
                                                            {{-- Raw grade counts with totals --}}
                                                            <td>{{ $counts['A']['M'] }}</td>
                                                            <td>{{ $counts['A']['F'] }}</td>
                                                            <td>{{ $counts['A']['M'] + $counts['A']['F'] }}</td>
                                                            
                                                            <td>{{ $counts['B']['M'] }}</td>
                                                            <td>{{ $counts['B']['F'] }}</td>
                                                            <td>{{ $counts['B']['M'] + $counts['B']['F'] }}</td>
                                                            
                                                            <td>{{ $counts['C']['M'] }}</td>
                                                            <td>{{ $counts['C']['F'] }}</td>
                                                            <td>{{ $counts['C']['M'] + $counts['C']['F'] }}</td>
                                                            
                                                            <td>{{ $counts['D']['M'] }}</td>
                                                            <td>{{ $counts['D']['F'] }}</td>
                                                            <td>{{ $counts['D']['M'] + $counts['D']['F'] }}</td>
                                                            
                                                            <td>{{ $counts['E']['M'] }}</td>
                                                            <td>{{ $counts['E']['F'] }}</td>
                                                            <td>{{ $counts['E']['M'] + $counts['E']['F'] }}</td>
                                                            
                                                            <td>{{ $counts['U']['M'] }}</td>
                                                            <td>{{ $counts['U']['F'] }}</td>
                                                            <td>{{ $counts['U']['M'] + $counts['U']['F'] }}</td>
                                                            
                                                            <td>{{ $counts['NS']['M'] }}</td>
                                                            <td>{{ $counts['NS']['F'] }}</td>
                                                            <td>{{ $counts['NS']['M'] + $counts['NS']['F'] }}</td>
                                
                                                            {{-- Percentage metrics with totals --}}
                                                            <td>{{ $counts['AB%']['M'] }}%</td>
                                                            <td>{{ $counts['AB%']['F'] }}%</td>
                                                            <td>{{ 
                                                                ($counts['enrolled']['M'] + $counts['enrolled']['F'] > 0) 
                                                                ? round(100 * ($counts['A']['M'] + $counts['A']['F'] + $counts['B']['M'] + $counts['B']['F']) / 
                                                                    ($counts['enrolled']['M'] + $counts['enrolled']['F']), 1)
                                                                : 0
                                                            }}%</td>
                                                            
                                                            <td>{{ $counts['ABC%']['M'] }}%</td>
                                                            <td>{{ $counts['ABC%']['F'] }}%</td>
                                                            <td>{{ 
                                                                ($counts['enrolled']['M'] + $counts['enrolled']['F'] > 0) 
                                                                ? round(100 * ($counts['A']['M'] + $counts['A']['F'] + $counts['B']['M'] + $counts['B']['F'] + $counts['C']['M'] + $counts['C']['F']) / 
                                                                    ($counts['enrolled']['M'] + $counts['enrolled']['F']), 1)
                                                                : 0
                                                            }}%</td>
                                                            
                                                            <td>{{ $counts['ABCD%']['M'] }}%</td>
                                                            <td>{{ $counts['ABCD%']['F'] }}%</td>
                                                            <td>{{ 
                                                                ($counts['enrolled']['M'] + $counts['enrolled']['F'] > 0) 
                                                                ? round(100 * ($counts['A']['M'] + $counts['A']['F'] + $counts['B']['M'] + $counts['B']['F'] + $counts['C']['M'] + $counts['C']['F'] + $counts['D']['M'] + $counts['D']['F']) / 
                                                                    ($counts['enrolled']['M'] + $counts['enrolled']['F']), 1)
                                                                : 0
                                                            }}%</td>
                                                            
                                                            <td>{{ $counts['DEU%']['M'] }}%</td>
                                                            <td>{{ $counts['DEU%']['F'] }}%</td>
                                                            <td>{{ 
                                                                ($counts['enrolled']['M'] + $counts['enrolled']['F'] > 0) 
                                                                ? round(100 * ($counts['D']['M'] + $counts['D']['F'] + $counts['E']['M'] + $counts['E']['F'] + $counts['U']['M'] + $counts['U']['F']) / 
                                                                    ($counts['enrolled']['M'] + $counts['enrolled']['F']), 1)
                                                                : 0
                                                            }}%</td>
                                                            
                                                            <td>{{ $counts['NS%']['M'] }}%</td>
                                                            <td>{{ $counts['NS%']['F'] }}%</td>
                                                            <td>{{ 
                                                                ($counts['enrolled']['M'] + $counts['enrolled']['F'] > 0) 
                                                                ? round(100 * ($counts['NS']['M'] + $counts['NS']['F']) / 
                                                                    ($counts['enrolled']['M'] + $counts['enrolled']['F']), 1)
                                                                : 0
                                                            }}%</td>
                                                        </tr>
                                                    @endforeach
                                
                                                    {{-- Grand Totals row --}}
                                                    <tr style="font-weight:600; background:#f3f3f3;">
                                                        <td>Totals</td>
                                                        {{-- Total enrolled --}}
                                                        <td style="text-align: center;">{{ $subjectTotals['enrolled']['M'] }}/{{ $subjectTotals['enrolled']['F'] }}</td>
                                                        
                                                        {{-- Raw grade totals --}}
                                                        <td>{{ $subjectTotals['A']['M'] }}</td>
                                                        <td>{{ $subjectTotals['A']['F'] }}</td>
                                                        <td>{{ $subjectTotals['A']['M'] + $subjectTotals['A']['F'] }}</td>
                                                        
                                                        <td>{{ $subjectTotals['B']['M'] }}</td>
                                                        <td>{{ $subjectTotals['B']['F'] }}</td>
                                                        <td>{{ $subjectTotals['B']['M'] + $subjectTotals['B']['F'] }}</td>
                                                        
                                                        <td>{{ $subjectTotals['C']['M'] }}</td>
                                                        <td>{{ $subjectTotals['C']['F'] }}</td>
                                                        <td>{{ $subjectTotals['C']['M'] + $subjectTotals['C']['F'] }}</td>
                                                        
                                                        <td>{{ $subjectTotals['D']['M'] }}</td>
                                                        <td>{{ $subjectTotals['D']['F'] }}</td>
                                                        <td>{{ $subjectTotals['D']['M'] + $subjectTotals['D']['F'] }}</td>
                                                        
                                                        <td>{{ $subjectTotals['E']['M'] }}</td>
                                                        <td>{{ $subjectTotals['E']['F'] }}</td>
                                                        <td>{{ $subjectTotals['E']['M'] + $subjectTotals['E']['F'] }}</td>
                                                        
                                                        <td>{{ $subjectTotals['U']['M'] }}</td>
                                                        <td>{{ $subjectTotals['U']['F'] }}</td>
                                                        <td>{{ $subjectTotals['U']['M'] + $subjectTotals['U']['F'] }}</td>
                                                        
                                                        <td>{{ $subjectTotals['NS']['M'] }}</td>
                                                        <td>{{ $subjectTotals['NS']['F'] }}</td>
                                                        <td>{{ $subjectTotals['NS']['M'] + $subjectTotals['NS']['F'] }}</td>
                                
                                                        {{-- Averaged percentage totals --}}
                                                        <td>{{ $subjectTotals['AB%']['M'] }}%</td>
                                                        <td>{{ $subjectTotals['AB%']['F'] }}%</td>
                                                        <td>{{ 
                                                            ($subjectTotals['enrolled']['M'] + $subjectTotals['enrolled']['F'] > 0)
                                                            ? round(100 * ($subjectTotals['A']['M'] + $subjectTotals['A']['F'] + $subjectTotals['B']['M'] + $subjectTotals['B']['F']) / 
                                                                ($subjectTotals['enrolled']['M'] + $subjectTotals['enrolled']['F']), 1)
                                                            : 0
                                                        }}%</td>
                                                        
                                                        <td>{{ $subjectTotals['ABC%']['M'] }}%</td>
                                                        <td>{{ $subjectTotals['ABC%']['F'] }}%</td>
                                                        <td>{{ 
                                                            ($subjectTotals['enrolled']['M'] + $subjectTotals['enrolled']['F'] > 0)
                                                            ? round(100 * ($subjectTotals['A']['M'] + $subjectTotals['A']['F'] + $subjectTotals['B']['M'] + $subjectTotals['B']['F'] + $subjectTotals['C']['M'] + $subjectTotals['C']['F']) / 
                                                                ($subjectTotals['enrolled']['M'] + $subjectTotals['enrolled']['F']), 1)
                                                            : 0
                                                        }}%</td>
                                                        
                                                        <td>{{ $subjectTotals['ABCD%']['M'] }}%</td>
                                                        <td>{{ $subjectTotals['ABCD%']['F'] }}%</td>
                                                        <td>{{ 
                                                            ($subjectTotals['enrolled']['M'] + $subjectTotals['enrolled']['F'] > 0)
                                                            ? round(100 * ($subjectTotals['A']['M'] + $subjectTotals['A']['F'] + $subjectTotals['B']['M'] + $subjectTotals['B']['F'] + $subjectTotals['C']['M'] + $subjectTotals['C']['F'] + $subjectTotals['D']['M'] + $subjectTotals['D']['F']) / 
                                                                ($subjectTotals['enrolled']['M'] + $subjectTotals['enrolled']['F']), 1)
                                                            : 0
                                                        }}%</td>
                                                        
                                                        <td>{{ $subjectTotals['DEU%']['M'] }}%</td>
                                                        <td>{{ $subjectTotals['DEU%']['F'] }}%</td>
                                                        <td>{{ 
                                                            ($subjectTotals['enrolled']['M'] + $subjectTotals['enrolled']['F'] > 0)
                                                            ? round(100 * ($subjectTotals['D']['M'] + $subjectTotals['D']['F'] + $subjectTotals['E']['M'] + $subjectTotals['E']['F'] + $subjectTotals['U']['M'] + $subjectTotals['U']['F']) / 
                                                                ($subjectTotals['enrolled']['M'] + $subjectTotals['enrolled']['F']), 1)
                                                            : 0
                                                        }}%</td>
                                                        
                                                        <td>{{ $subjectTotals['NS%']['M'] }}%</td>
                                                        <td>{{ $subjectTotals['NS%']['F'] }}%</td>
                                                        <td>{{ 
                                                            ($subjectTotals['enrolled']['M'] + $subjectTotals['enrolled']['F'] > 0)
                                                            ? round(100 * ($subjectTotals['NS']['M'] + $subjectTotals['NS']['F']) / 
                                                                ($subjectTotals['enrolled']['M'] + $subjectTotals['enrolled']['F']), 1)
                                                            : 0
                                                        }}%</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12 col-md-12">
                                        <div id="gradeDistributionLineChart" style="width: 100%; height: 400px;"></div> 
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
    <script src="{{ URL::asset('/assets/libs/pristinejs/pristinejs.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/pages/form-validation.init.js') }}"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>

    <script>
        // Global store for chart instances if complex print logic is needed for all
        window.echartsInstances = {};

        function printContent() {
            const mainChartInstance = window.echartsInstances['gradeDistributionChart'];
            if (mainChartInstance && mainChartInstance.getDom()) { // Check if DOM element exists
                try {
                    var img = new Image();
                    img.src = mainChartInstance.getDataURL({
                        type: 'png',
                        pixelRatio: 2,
                        backgroundColor: '#fff'
                    });
                    
                    var chartDom = document.getElementById('gradeDistributionChart');
                    var tempImgContainer = document.createElement('div');
                    tempImgContainer.className = 'print-only-chart-image-container'; // For potential styling
                    img.style.width = '100%'; 
                    img.style.maxWidth = '800px'; // Max width for the image
                    img.style.display = 'block'; // Ensure it's a block element
                    img.style.margin = '0 auto 20px auto'; // Center and add margin
                    tempImgContainer.appendChild(img);
                    
                    var originalChartDisplay = chartDom.style.display;
                    chartDom.style.display = 'none';
                    // Insert before the original chart div for better flow in print
                    chartDom.parentNode.insertBefore(tempImgContainer, chartDom);

                    window.print();

                    // Cleanup after print dialog is closed
                    chartDom.style.display = originalChartDisplay;
                    if (tempImgContainer.parentNode) {
                        tempImgContainer.parentNode.removeChild(tempImgContainer);
                    }
                } catch (e) {
                    console.error("Error generating chart image for print:", e);
                    window.print(); // Fallback to simple print
                }
            } else {
                window.print(); // Fallback if main chart not ready or found
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const echartsColors = {
                gradeA: '#91cc75', gradeB: '#5470c6', gradeC: '#fac858',
                gradeD: '#fc8452', gradeE: '#ee6666', gradeU: '#909399', gradeX: '#777777',
                lineAB: '#5470c6', lineABC: '#91cc75', lineABCD: '#9a60b4',
                lineDEU: '#ee6666', lineX: '#6e7079',
                male: '#5470c6', female: '#ee6666',
            };

            function getSafeNumericValue(obj, key, defaultValue = 0) {
                if (obj && typeof obj[key] !== 'undefined') {
                    const val = parseFloat(obj[key]);
                    return isNaN(val) ? defaultValue : val;
                }
                return defaultValue;
            }

            function getSafeNestedNumericValue(baseObj, key1, key2, defaultValue = 0) {
                if (baseObj && baseObj[key1] && typeof baseObj[key1][key2] !== 'undefined') {
                    const val = parseFloat(baseObj[key1][key2]);
                    return isNaN(val) ? defaultValue : val;
                }
                return defaultValue;
            }

            // --- Chart 1: Subject Grade Distribution (Combined Bar/Line) ---
            const subjectGradeCounts = @json($subjectGradeCounts ?? []); // CORRECTED
            const subjectAnalysisChartDom = document.getElementById('gradeDistributionChart');

            if (subjectAnalysisChartDom && Object.keys(subjectGradeCounts).length > 0) {
                const subjectAnalysisChart = echarts.init(subjectAnalysisChartDom);
                window.echartsInstances['gradeDistributionChart'] = subjectAnalysisChart;

                const subjects = Object.keys(subjectGradeCounts);
                const gradesForBar = ['A', 'B', 'C', 'D', 'E', 'U', 'X'];
                const percentMetricsForLine = ['AB%', 'ABC%', 'ABCD%', 'DEU%', 'X%'];

                const barChartData = gradesForBar.map(grade => ({
                    name: grade,
                    type: 'bar',
                    stack: 'totalStudents',
                    emphasis: { focus: 'series' },
                    color: echartsColors['grade' + grade.toUpperCase()] || '#ccc', // Ensure uppercase for key
                    data: subjects.map(subject => {
                        const maleCount = getSafeNestedNumericValue(subjectGradeCounts[subject], grade, 'M');
                        const femaleCount = getSafeNestedNumericValue(subjectGradeCounts[subject], grade, 'F');
                        return maleCount + femaleCount;
                    })
                }));

                const lineChartData = percentMetricsForLine.map(metric => ({
                    name: metric,
                    type: 'line',
                    yAxisIndex: 1,
                    smooth: true,
                    connectNulls: true, // Important for missing data points
                    color: echartsColors['line' + metric.replace('%','').toUpperCase()] || '#333', // Ensure uppercase
                    data: subjects.map(subject => {
                        const malePercent = getSafeNestedNumericValue(subjectGradeCounts[subject], metric, 'M');
                        const femalePercent = getSafeNestedNumericValue(subjectGradeCounts[subject], metric, 'F');
                        
                        // Check if data for M and F actually exists for this metric in this subject
                        const M_exists = subjectGradeCounts[subject] && subjectGradeCounts[subject][metric] && typeof subjectGradeCounts[subject][metric]['M'] !== 'undefined';
                        const F_exists = subjectGradeCounts[subject] && subjectGradeCounts[subject][metric] && typeof subjectGradeCounts[subject][metric]['F'] !== 'undefined';

                        if (!M_exists && !F_exists) return null; // If neither exists, treat as null for connectNulls
                        if (!M_exists) return parseFloat(femalePercent.toFixed(1)); // Only F exists
                        if (!F_exists) return parseFloat(malePercent.toFixed(1)); // Only M exists
                        
                        return parseFloat(((malePercent + femalePercent) / 2).toFixed(1));
                    })
                }));
                
                const subjectAnalysisOption = {
                    title: { text: '{{ addslashes($klass->name) }} Subjects Analysis', left: 'center', subtext: 'Grade counts and percentage metrics per subject' },
                    tooltip: { trigger: 'axis', axisPointer: { type: 'cross' }, confine: true },
                    legend: { top: 50, type: 'scroll', data: [...gradesForBar, ...percentMetricsForLine] },
                    grid: { top: '22%', bottom: '15%', left: '5%', right: '5%', containLabel: true },
                    xAxis: { type: 'category', data: subjects, axisLabel: { interval: 0, rotate: 30, formatter: val => val.length > 15 ? val.substr(0,12)+'...' : val } },
                    yAxis: [
                        { type: 'value', name: 'Number of Students', splitLine: { lineStyle: { type: 'dashed' } } },
                        { type: 'value', name: 'Performance (%)', min: 0, max: 100, splitLine: { show: false }, axisLabel: { formatter: '{value}%' } }
                    ],
                    series: [...barChartData, ...lineChartData],
                    dataZoom: [{ type: 'slider', show: subjects.length > 8, start: 0, end: subjects.length > 8 ? (8/subjects.length*100) : 100, bottom: 10 }, { type: 'inside' }],
                    toolbox: { right: 20, feature: { saveAsImage: {title: 'Save Image'}, dataView: {readOnly: true, title: 'View Data'}, magicType: {type: ['line', 'bar', 'stack'], title:{line:'Line', bar:'Bar', stack:'Stack'}}, restore: {title: 'Restore'} } }
                };
                subjectAnalysisChart.setOption(subjectAnalysisOption);
                window.addEventListener('resize', () => { if(subjectAnalysisChart && !subjectAnalysisChart.isDisposed()){ subjectAnalysisChart.resize(); } });
            } else if (subjectAnalysisChartDom) {
                 subjectAnalysisChartDom.innerHTML = '<p style="text-align:center; padding:20px;">No subject grade data to display chart.</p>';
            }

            // --- Chart 2: Overall Class Grade Distribution (Line/Bar Chart) ---
            const overallGradeCounts = @json($gradeCounts ?? []); // CORRECTED
            const classDistChartDom = document.getElementById('gradeDistributionLineChart');

            if (classDistChartDom && Object.keys(overallGradeCounts).length > 0) {
                const classDistChart = echarts.init(classDistChartDom);
                window.echartsInstances['gradeDistributionLineChart'] = classDistChart;

                const gradeCategories = ['M', 'A', 'B', 'C', 'D', 'E', 'U'];

                const maleOverallCounts = gradeCategories.map(g => getSafeNestedNumericValue(overallGradeCounts, g, 'M'));
                const femaleOverallCounts = gradeCategories.map(g => getSafeNestedNumericValue(overallGradeCounts, g, 'F'));

                const classDistOption = {
                    title: { text: '{{ addslashes($klass->name) }} Class Grade Distribution', left: 'center' },
                    tooltip: { trigger: 'axis', confine: true },
                    legend: { data: ['Male', 'Female'], top: 30 },
                    grid: { top: 70, bottom: 30, left: '5%', right: '5%', containLabel: true },
                    xAxis: { type: 'category', data: gradeCategories },
                    yAxis: { type: 'value', name: 'Number of Students' },
                    series: [
                        { name: 'Male', type: 'bar', smooth: true, data: maleOverallCounts, color: echartsColors.male, emphasis: {focus: 'series'} },
                        { name: 'Female', type: 'bar', smooth: true, data: femaleOverallCounts, color: echartsColors.female, emphasis: {focus: 'series'} }
                    ],
                    toolbox: { right: 20, feature: { saveAsImage: {}, dataView: {readOnly: true}, magicType: {type: ['line', 'bar']}, restore: {} } }
                };
                classDistChart.setOption(classDistOption);
                window.addEventListener('resize', () => { if(classDistChart && !classDistChart.isDisposed()){ classDistChart.resize(); } });
            } else if (classDistChartDom) {
                classDistChartDom.innerHTML = '<p style="text-align:center; padding:20px;">No overall grade data to display chart.</p>';
            }

            // --- Chart 3: PSLE Grade Distribution (Line/Bar Chart) ---
            const psleOverallGradeCounts = @json($psleGradeCounts ?? []); // CORRECTED
            const psleDistChartDom = document.getElementById('psleGradeLineChart');

            if (psleDistChartDom && Object.keys(psleOverallGradeCounts).length > 0) {
                const psleDistChart = echarts.init(psleDistChartDom);
                window.echartsInstances['psleGradeLineChart'] = psleDistChart;

                const psleGradeCategories = ['A', 'B', 'C', 'D', 'E', 'U'];

                const malePsleCounts = psleGradeCategories.map(g => getSafeNestedNumericValue(psleOverallGradeCounts, g, 'M'));
                const femalePsleCounts = psleGradeCategories.map(g => getSafeNestedNumericValue(psleOverallGradeCounts, g, 'F'));
                
                const psleDistOption = {
                    title: { text: '{{ addslashes($klass->name) }} PSLE Performance Analysis', left: 'center' },
                    tooltip: { trigger: 'axis', confine: true },
                    legend: { data: ['Male Students', 'Female Students'], top: 30 },
                    grid: { top: 70, bottom: 30, left: '5%', right: '5%', containLabel: true },
                    xAxis: { type: 'category', data: psleGradeCategories, name: 'PSLE Grade', nameLocation: 'middle', nameGap: 25 },
                    yAxis: { type: 'value', name: 'Number of Students' },
                    series: [
                        { name: 'Male Students', type: 'bar', smooth: true, data: malePsleCounts, color: echartsColors.male, emphasis: {focus: 'series'} },
                        { name: 'Female Students', type: 'bar', smooth: true, data: femalePsleCounts, color: echartsColors.female, emphasis: {focus: 'series'} }
                    ],
                    toolbox: { right: 20, feature: { saveAsImage: {}, dataView: {readOnly: true}, magicType: {type: ['line', 'bar']}, restore: {} } }
                };
                psleDistChart.setOption(psleDistOption);
                window.addEventListener('resize', () => { if(psleDistChart && !psleDistChart.isDisposed()){ psleDistChart.resize(); } });
            } else if (psleDistChartDom) {
                 psleDistChartDom.innerHTML = '<p style="text-align:center; padding:20px;">No PSLE grade data to display chart.</p>';
            }
        });
    </script>
@endsection