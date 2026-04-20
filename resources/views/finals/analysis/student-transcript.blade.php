@extends('layouts.master')
@section('title')
    Student Transcript - {{ $transcriptData['student']['full_name'] }}
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
            overflow: hidden;
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

        /* Info Cards */
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

        /* Exam Info Card */
        .exam-info-card {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border: 1px solid #90caf9;
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
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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

        .results-table tbody td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .results-table tbody tr:hover {
            background: rgba(0, 51, 102, 0.03);
        }

        .results-table tbody tr:nth-child(even) {
            background: rgba(0, 0, 0, 0.02);
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
            padding: 30px;
            text-align: center;
            margin-top: 25px;
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
            font-size: 56px;
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
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .overall-points {
            font-size: 14px;
            color: #555;
        }

        .overall-points strong {
            font-size: 20px;
            color: var(--primary-blue);
        }

        /* Summary Stats */
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-blue);
        }

        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Footer */
        .transcript-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid var(--border-color);
            text-align: center;
            font-size: 11px;
            color: #666;
        }

        .transcript-footer strong {
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

            .info-card {
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
                {{ $transcriptData['student']['full_name'] }} - Transcript
            @endslot
        @endcomponent

        <div class="control-panel">
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-light">
                    <i class="bx bx-arrow-back me-2"></i>Back
                </a>
            </div>
            <div>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bx bx-printer me-2"></i>Print Transcript
                </button>
            </div>
        </div>
    </div>

    <div class="transcript-container">
        <!-- Header -->
        <div class="transcript-header">
            <div class="header-content">
                <div class="header-logo">
                    <img src="{{ asset('assets/images/bec_logo.png') }}" alt="BEC Logo" onerror="this.style.display='none'">
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
                            <span class="info-value">{{ $transcriptData['student']['full_name'] }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Gender:</span>
                            <span class="info-value">{{ $transcriptData['student']['gender_full'] }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date of Birth:</span>
                            <span class="info-value">
                                @if ($transcriptData['student']['date_of_birth'])
                                    {{ \Carbon\Carbon::parse($transcriptData['student']['date_of_birth'])->format('d F Y') }}
                                @else
                                    Not Available
                                @endif
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Nationality:</span>
                            <span
                                class="info-value">{{ $transcriptData['student']['nationality'] ?? 'Not Specified' }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="info-row">
                            <span class="info-label">Exam Number:</span>
                            <span class="info-value">{{ $transcriptData['student']['exam_number'] ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">ID Number:</span>
                            <span
                                class="info-value">{{ $transcriptData['student']['formatted_id_number'] ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Class:</span>
                            <span class="info-value">{{ $transcriptData['student']['class_name'] }}
                                ({{ $transcriptData['student']['grade_name'] }})</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Graduation Year:</span>
                            <span class="info-value">{{ $transcriptData['student']['graduation_year'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Examination Information -->
            <div class="info-card exam-info-card">
                <div class="info-card-title">Examination Details</div>
                <div class="info-grid">
                    <div>
                        <div class="info-row">
                            <span class="info-label">Exam Type:</span>
                            <span class="info-value">{{ $transcriptData['exam_info']['exam_type'] }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Session:</span>
                            <span class="info-value">{{ $transcriptData['exam_info']['exam_session'] }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="info-row">
                            <span class="info-label">Exam Year:</span>
                            <span class="info-value">{{ $transcriptData['exam_info']['exam_year'] }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Centre:</span>
                            <span class="info-value">
                                @if ($transcriptData['exam_info']['centre_code'])
                                    {{ $transcriptData['exam_info']['centre_code'] }} -
                                    {{ $transcriptData['exam_info']['centre_name'] }}
                                @else
                                    {{ $school_data->school_name }}
                                @endif
                            </span>
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
                            <th style="width: 35%; text-align: left;">Subject</th>
                            <th style="width: 15%;">Code</th>
                            <th style="width: 15%;">Grade</th>
                            <th style="width: 15%;">Points</th>
                            <th style="width: 15%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transcriptData['subjects'] as $index => $subject)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="subject-name">{{ $subject['subject_name'] }}</td>
                                <td>{{ $subject['subject_code'] ?? '-' }}</td>
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
                                <td colspan="6"
                                    style="text-align: center; color: #666; font-style: italic; padding: 30px;">
                                    No subject results available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Overall Result -->
            <div class="overall-result {{ $transcriptData['exam_info']['is_pass'] ? 'result-pass' : 'result-fail' }}">
                <div class="overall-grade-display">{{ $transcriptData['exam_info']['overall_grade'] ?? 'N/A' }}</div>
                <div class="overall-status">
                    Overall Result: {{ $transcriptData['exam_info']['overall_grade'] ?? 'N/A' }}
                </div>
                <div class="overall-points">
                    Total Points: <strong>{{ number_format($transcriptData['exam_info']['overall_points'], 1) }}</strong>
                </div>

                <!-- Summary Stats -->
                <div class="summary-stats">
                    <div class="stat-item">
                        <div class="stat-value">{{ $transcriptData['summary']['total_subjects'] }}</div>
                        <div class="stat-label">Total Subjects</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" style="color: var(--success-green);">
                            {{ $transcriptData['summary']['passed_subjects'] }}</div>
                        <div class="stat-label">Subjects Passed</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $transcriptData['summary']['pass_percentage'] }}%</div>
                        <div class="stat-label">Pass Rate</div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="transcript-footer">
                <p><strong>{{ $school_data->school_name }}</strong></p>
                <p>{{ $school_data->physical_address }} | Tel: {{ $school_data->telephone }}</p>
                <p style="margin-top: 10px;">This is an official transcript generated on
                    {{ now()->format('d F Y \a\t H:i') }}</p>
                <p>For verification purposes, please contact the school administration.</p>
            </div>
        </div>
    </div>
@endsection
