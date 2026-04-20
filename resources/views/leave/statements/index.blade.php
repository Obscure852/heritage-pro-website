@extends('layouts.master')
@section('title')
    Leave Statements
@endsection
@section('css')
    <style>
        .statements-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .header h3 {
            margin: 0;
            font-weight: 600;
        }

        .header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .form-container {
            background: white;
            border-radius: 0 0 3px 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .help-text {
            background: #f8f9fa;
            padding: 16px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            padding-top: 16px;
            border-top: 1px solid #f3f4f6;
            margin-top: 24px;
        }

        .year-option {
            padding: 10px 0;
        }

        .info-card {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .info-card h5 {
            margin: 0 0 8px 0;
            color: #0369a1;
            font-size: 14px;
            font-weight: 600;
        }

        .info-card ul {
            margin: 0;
            padding-left: 20px;
            color: #0284c7;
            font-size: 13px;
        }

        .info-card ul li {
            margin-bottom: 4px;
        }
    </style>
@endsection
@section('content')
    <div class="statements-container">
        <div class="header">
            <h3><i class="fas fa-file-pdf me-2"></i>Leave Statements</h3>
            <p>Download your official leave statement for any year</p>
        </div>

        <div class="form-container">
            <div class="help-text">
                <div class="help-title"><i class="fas fa-info-circle me-1"></i> About Leave Statements</div>
                <div class="help-content">
                    A leave statement is an official document showing your leave balance summary and complete request history for a specific year.
                    You can download this PDF for your records or for official purposes.
                </div>
            </div>

            <div class="info-card">
                <h5><i class="fas fa-file-alt me-1"></i> Statement Includes</h5>
                <ul>
                    <li>Leave balance summary (entitled, used, pending, available)</li>
                    <li>Complete leave request history with approval status</li>
                    <li>Breakdown by leave type</li>
                    <li>School branding and official formatting</li>
                </ul>
            </div>

            <form action="{{ route('leave.statements.download') }}" method="GET" id="statementForm">
                <div class="form-group">
                    <label for="year"><i class="fas fa-calendar me-1"></i> Select Leave Year</label>
                    <select name="year" id="year" class="form-select" required>
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                {{ $year }} {{ $year == $currentYear ? '(Current Year)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-actions">
                    <a href="{{ route('leave.balances.dashboard') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-download"></i> Download PDF Statement</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Generating...
                        </span>
                    </button>
                    <a href="{{ route('leave.reports.personal-history.export', ['year' => $currentYear]) }}" class="btn btn-success" id="exportExcelBtn" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border: none;">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.getElementById('statementForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"].btn-loading');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;

                // Re-enable after 5 seconds (PDF generation may take a moment)
                setTimeout(function() {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                }, 5000);
            }
        });

        // Update Excel export link when year changes
        document.getElementById('year').addEventListener('change', function() {
            const selectedYear = this.value;
            const exportBtn = document.getElementById('exportExcelBtn');
            if (exportBtn) {
                exportBtn.href = "{{ route('leave.reports.personal-history.export') }}?year=" + selectedYear;
            }
        });
    </script>
@endsection
