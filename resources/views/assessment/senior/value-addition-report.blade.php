@extends('layouts.master')
@section('title')
    Value Addition Report
@endsection

@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .report-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .report-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .report-header p {
            margin: 4px 0 0;
            opacity: 0.9;
        }

        .subject-card {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .subject-card-header {
            background: #f8f9fa;
            padding: 12px 20px;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .subject-card-header h6 {
            margin: 0;
            font-weight: 600;
            color: #1f2937;
            font-size: 15px;
        }

        .subject-card-header .badge {
            font-size: 11px;
        }

        .jce-input-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            padding: 12px 16px;
            margin: 16px 20px;
        }

        .jce-input-box .label {
            font-weight: 600;
            color: #1e40af;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .jce-table {
            width: auto;
            font-size: 12px;
        }

        .jce-table th,
        .jce-table td {
            padding: 4px 12px;
            text-align: center;
            border: 1px solid #93c5fd;
        }

        .jce-table th {
            background: #dbeafe;
            color: #1e40af;
            font-weight: 600;
        }

        .perf-table {
            width: 100%;
            font-size: 12px;
            border-collapse: collapse;
        }

        .perf-table th,
        .perf-table td {
            border: 1px solid #ddd;
            padding: 5px 8px;
            text-align: center;
        }

        .perf-table th {
            background-color: #f2f2f2;
            font-weight: 600;
            font-size: 11px;
        }

        .perf-table .test-name {
            text-align: left;
            font-weight: 500;
        }

        .perf-table .term-separator td {
            background-color: #4e73df !important;
            color: #fff;
            font-weight: 600;
            font-size: 12px;
            text-align: left;
            padding: 5px 10px;
            border-color: #4e73df;
        }

        .va-positive {
            color: #059669;
            font-weight: 700;
            background-color: #d1fae5 !important;
        }

        .va-negative {
            color: #dc2626;
            font-weight: 700;
            background-color: #fee2e2 !important;
        }

        .va-zero {
            color: #6b7280;
            font-weight: 600;
        }

        .no-data-msg {
            padding: 16px 20px;
            color: #6b7280;
            font-style: italic;
        }

        @media print {
            @page {
                size: landscape;
                margin: 10mm;
            }

            body {
                font-size: 10pt;
            }

            .no-print {
                display: none !important;
            }

            .subject-card {
                page-break-inside: avoid;
                box-shadow: none;
            }

            .report-header {
                background: #4e73df !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .va-positive {
                background-color: #d1fae5 !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .va-negative {
                background-color: #fee2e2 !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .jce-input-box {
                background: #eff6ff !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .perf-table .term-separator td {
                background-color: #4e73df !important;
                color: #fff !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            Value Addition Report
        @endslot
    @endcomponent

    <div class="row no-print mb-2">
        <div class="col-12 d-flex justify-content-end gap-2">
            <a href="{{ route('assessment.value-addition-export', ['classId' => $classId]) }}"
                title="Export to Excel" style="font-size: 20px; cursor: pointer;">
                <i class="bx bx-download text-muted"></i>
            </a>
            <i onclick="window.print()" title="Print"
                style="font-size: 20px; cursor: pointer;" class="bx bx-printer text-muted"></i>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="report-header">
                <h4>Value Addition Report &mdash; {{ $gradeName }}</h4>
                <p>{{ $school_data->school_name }} | {{ $year }}</p>
            </div>
        </div>
    </div>

    @if (count($subjects) === 0)
        <div class="row mt-3">
            <div class="col-12">
                <div class="alert alert-info">No subject data available for this grade.</div>
            </div>
        </div>
    @else
        @foreach ($subjects as $subject)
            <div class="row mt-3">
                <div class="col-12">
                    <div class="subject-card">
                        <div class="subject-card-header">
                            <h6>{{ $subject['subjectName'] }}</h6>
                            <span class="badge bg-{{ $subject['sourceKey'] ? 'primary' : 'secondary' }}">
                                JCE Source: {{ $subject['jceInput']['label'] }}
                            </span>
                        </div>

                        {{-- JCE Input Box --}}
                        @if ($subject['jceInput']['total'] > 0)
                            <div class="jce-input-box">
                                <div class="label">JC INPUT ({{ $subject['jceInput']['label'] }})</div>
                                <table class="jce-table">
                                    <thead>
                                        <tr>
                                            @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                <th>{{ $grade }}</th>
                                            @endforeach
                                            <th>Total</th>
                                            <th>%(A-C)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            @foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade)
                                                <td>{{ $subject['jceInput']['grades'][$grade] }}</td>
                                            @endforeach
                                            <td><strong>{{ $subject['jceInput']['total'] }}</strong></td>
                                            <td><strong>{{ $subject['jceInput']['percentAC'] }}%</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="jce-input-box">
                                <div class="label">JC INPUT</div>
                                <span class="text-muted" style="font-size: 12px;">No JCE data available for baseline</span>
                            </div>
                        @endif

                        {{-- Performance Table --}}
                        @if (count($subject['termGroups']) > 0)
                            <div class="table-responsive" style="padding: 0 20px 16px;">
                                <table class="perf-table">
                                    <thead>
                                        <tr>
                                            <th>Test/Exam</th>
                                            @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U', 'X'] as $grade)
                                                <th>{{ $grade }}</th>
                                            @endforeach
                                            <th>Total</th>
                                            <th>%(A-C)</th>
                                            <th>%(A-E)</th>
                                            <th>JC</th>
                                            <th>VA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($subject['termGroups'] as $group)
                                            <tr class="term-separator">
                                                <td colspan="16">{{ $group['termLabel'] }}</td>
                                            </tr>
                                            @foreach ($group['testRows'] as $row)
                                                <tr>
                                                    <td class="test-name">{{ $row['testName'] }}</td>
                                                    @foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U', 'X'] as $grade)
                                                        <td>{{ $row['grades'][$grade] }}</td>
                                                    @endforeach
                                                    <td><strong>{{ $row['total'] }}</strong></td>
                                                    <td>{{ $row['percentAC'] }}%</td>
                                                    <td>{{ $row['percentAE'] }}%</td>
                                                    <td>{{ $row['jcePercentAC'] }}%</td>
                                                    <td class="{{ $row['va'] > 0 ? 'va-positive' : ($row['va'] < 0 ? 'va-negative' : 'va-zero') }}">
                                                        {{ $row['va'] > 0 ? '+' : '' }}{{ $row['va'] }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="no-data-msg">No assessment data available for this subject.</div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection

@section('script')
    <script>
        // No additional scripts needed
    </script>
@endsection
