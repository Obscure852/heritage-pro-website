@extends('layouts.master')
@section('title')
    Students Transcripts - {{ $reportData['graduation_year'] }}
@endsection
@section('css')
    <style>
        :root {
            --primary-blue: #003366;
            --secondary-blue: #0056b3;
            --accent-gold: #c5a900;
            --success-green: #28a745;
            --warning-orange: #fd7e14;
            --danger-red: #dc3545;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #333;
            background: #f5f5f5;
        }

        .transcript-container {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 0;
            background: white;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            border-radius: 0;
            page-break-after: always;
            overflow: hidden;
        }

        .transcript-container:last-child {
            page-break-after: auto;
        }

        /* Header Section */
        .transcript-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: white;
            padding: 25px 30px;
            position: relative;
        }

        .transcript-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-gold) 0%, #ffd700 100%);
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-logo {
            flex: 0 0 auto;
        }

        .header-logo img {
            height: 80px;
            filter: brightness(0) invert(1);
        }

        .header-text {
            flex: 1;
            text-align: center;
            padding: 0 20px;
        }

        .header-text h1 {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 5px 0;
            letter-spacing: 1px;
            color: white;
        }

        .header-text h2 {
            font-size: 14px;
            font-weight: 400;
            margin: 0;
            opacity: 0.9;
            color: white;
        }

        .document-badge {
            background: var(--accent-gold);
            color: var(--primary-blue);
            padding: 8px 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-radius: 3px;
        }

        /* Body Section */
        .transcript-body {
            padding: 25px 30px;
        }

        /* Student Info Card */
        .info-card {
            background: var(--light-bg);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }

        .info-card-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--primary-blue);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--accent-gold);
            display: inline-block;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .info-row {
            display: flex;
            align-items: center;
        }

        .info-label {
            font-weight: 600;
            color: #555;
            width: 130px;
            font-size: 12px;
        }

        .info-value {
            flex: 1;
            color: #333;
            font-weight: 500;
        }

        /* Results Table */
        .results-section {
            margin-top: 20px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--primary-blue);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--accent-gold);
            display: inline-block;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .results-table thead th {
            background: var(--primary-blue);
            color: white;
            padding: 12px 10px;
            text-align: center;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .results-table thead th:first-child {
            border-radius: 6px 0 0 0;
        }

        .results-table thead th:last-child {
            border-radius: 0 6px 0 0;
        }

        .results-table tbody td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .results-table tbody tr:hover {
            background: rgba(0, 51, 102, 0.03);
        }

        .results-table tbody tr:last-child td:first-child {
            border-radius: 0 0 0 6px;
        }

        .results-table tbody tr:last-child td:last-child {
            border-radius: 0 0 6px 0;
        }

        .subject-name {
            text-align: left !important;
            font-weight: 500;
        }

        /* Grade Badges */
        .grade-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 11px;
            min-width: 35px;
        }

        .grade-merit,
        .grade-a {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .grade-b {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
            color: white;
        }

        .grade-c {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: #333;
        }

        .grade-d {
            background: linear-gradient(135deg, #fd7e14 0%, #e83e8c 100%);
            color: white;
        }

        .grade-e,
        .grade-u {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .status-pass {
            color: var(--success-green);
            font-weight: 700;
        }

        .status-fail {
            color: var(--danger-red);
            font-weight: 700;
        }

        /* Overall Result Card */
        .overall-result {
            background: linear-gradient(135deg, var(--light-bg) 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            margin-top: 20px;
            border: 2px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .overall-result.result-pass {
            border-color: var(--success-green);
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }

        .overall-result.result-fail {
            border-color: var(--danger-red);
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        }

        .overall-result::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-gold);
        }

        .result-pass::before {
            background: var(--success-green);
        }

        .result-fail::before {
            background: var(--danger-red);
        }

        .overall-grade-display {
            font-size: 48px;
            font-weight: 800;
            color: var(--primary-blue);
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .result-pass .overall-grade-display {
            color: var(--success-green);
        }

        .result-fail .overall-grade-display {
            color: var(--danger-red);
        }

        .overall-status {
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .overall-points {
            font-size: 14px;
            color: #555;
        }

        .overall-points strong {
            font-size: 18px;
            color: var(--primary-blue);
        }

        /* Control Panel */
        .control-panel {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .control-panel .btn {
            padding: 8px 20px;
            font-size: 13px;
        }

        /* Print Styles */
        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }

            body {
                font-size: 11px;
                background: white;
            }

            .transcript-container {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
                page-break-after: always;
            }

            .transcript-container:last-child {
                page-break-after: auto;
            }

            .no-print {
                display: none !important;
            }

            .transcript-header {
                padding: 15px 20px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .grade-badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .overall-result {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
@endsection

@section('content')
    <div class="no-print">
        @component('components.breadcrumb')
            @slot('li_1')
                <a href="{{ route('finals.students.index') }}">Finals</a>
            @endslot
            @slot('title')
                Student Transcripts - {{ $reportData['graduation_year'] }}
            @endslot
        @endcomponent

        <div class="control-panel">
            <div>
                <span class="text-muted">Showing {{ count($reportData['transcripts']) }} transcripts for graduation year
                    {{ $reportData['graduation_year'] }}</span>
            </div>
            <div>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bx bx-printer me-2"></i>Print All
                </button>
            </div>
        </div>
    </div>

    @forelse($reportData['transcripts'] as $index => $transcript)
        @php
            // Get student with relationships for grade calculation
            $student = \App\Models\FinalStudent::with([
                'externalExamResults' => function ($q) {
                    $q->with([
                        'externalExam',
                        'subjectResults' => function ($sq) {
                            $sq->orderBy('subject_name');
                        },
                    ]);
                },
                'graduationGrade',
            ])->find($transcript['student_id']);

            $subjects = [];
            $overallGrade = $transcript['overall_grade'];
            $overallPoints = $transcript['overall_points'];

            if ($student && $student->externalExamResults->isNotEmpty()) {
                $latestResult = $student->externalExamResults->first();
                $subjects = $latestResult->subjectResults
                    ->map(function ($result) {
                        return [
                            'subject_name' => $result->subject_name,
                            'subject_code' => $result->subject_code,
                            'grade' => $result->grade,
                            'grade_points' => $result->grade_points,
                            'is_pass' => $result->is_pass,
                        ];
                    })
                    ->toArray();

                // Use calculated grade if stored grade is empty
                if (empty($overallGrade)) {
                    $overallGrade = $latestResult->calculated_overall_grade;
                }
            }

            $isPass = in_array($overallGrade, ['A', 'B', 'C', 'Merit']);
        @endphp

        <div class="transcript-container">
            <!-- Header -->
            <div class="transcript-header">
                <div class="header-content">
                    <div class="header-logo">
                        <img src="{{ asset('assets/images/bec_logo.png') }}" alt="BEC Logo"
                            onerror="this.style.display='none'">
                    </div>
                    <div class="header-text">
                        <h1>BOTSWANA EXAMINATIONS COUNCIL</h1>
                        <h2>Junior Certificate Examination Results</h2>
                    </div>
                    <div class="document-badge">
                        Official Transcript
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="transcript-body">
                <!-- Student Information -->
                <div class="info-card">
                    <div class="info-card-title">Student Information</div>
                    <div class="info-grid">
                        <div>
                            <div class="info-row">
                                <span class="info-label">Full Name:</span>
                                <span class="info-value">{{ $transcript['full_name'] }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Gender:</span>
                                <span class="info-value">{{ $transcript['gender_full'] }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">ID Number:</span>
                                <span class="info-value">{{ $transcript['formatted_id_number'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="info-row">
                                <span class="info-label">Exam Number:</span>
                                <span class="info-value">{{ $transcript['exam_number'] ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Class:</span>
                                <span class="info-value">{{ $transcript['class_name'] }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Graduation Year:</span>
                                <span class="info-value">{{ $transcript['graduation_year'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subject Results -->
                <div class="results-section">
                    <div class="section-title">Subject Results</div>
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 40%; text-align: left;">Subject</th>
                                <th style="width: 15%;">Grade</th>
                                <th style="width: 15%;">Points</th>
                                <th style="width: 15%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subjects as $subIndex => $subject)
                                <tr>
                                    <td>{{ $subIndex + 1 }}</td>
                                    <td class="subject-name">{{ $subject['subject_name'] }}</td>
                                    <td>
                                        <span class="grade-badge grade-{{ strtolower($subject['grade']) }}">
                                            {{ $subject['grade'] }}
                                        </span>
                                    </td>
                                    <td><strong>{{ number_format($subject['grade_points'], 1) }}</strong></td>
                                    <td>
                                        @if ($subject['is_pass'])
                                            <span class="status-pass">PASS</span>
                                        @else
                                            <span class="status-fail">FAIL</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        style="text-align: center; color: #666; font-style: italic; padding: 30px;">
                                        No subject results available
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Overall Result -->
                <div class="overall-result {{ $isPass ? 'result-pass' : 'result-fail' }}">
                    <div class="overall-grade-display">{{ $overallGrade ?? 'N/A' }}</div>
                    <div class="overall-status">
                        Overall Result: {{ $overallGrade ?? 'N/A' }}
                    </div>
                    <div class="overall-points">
                        Total Points: <strong>{{ number_format($overallPoints, 1) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="transcript-container">
            <div style="text-align: center; padding: 100px 50px; color: #666;">
                <i class="bx bx-file-blank" style="font-size: 60px; opacity: 0.3;"></i>
                <h4 style="margin-top: 20px;">No Transcripts Available</h4>
                <p>No student transcripts found for graduation year {{ $reportData['graduation_year'] }}</p>
            </div>
        </div>
    @endforelse
@endsection
