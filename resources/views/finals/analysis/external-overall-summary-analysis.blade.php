@extends('layouts.master')
@section('title')
    Performance Analysis - {{ $exam_type }} {{ $year }}
@endsection
@section('css')
<style>
    .modal-backdrop.show {
        opacity: 0.4 !important;
    }

    .readonly-input {
        background-color: white !important;
        color: black;
        cursor: default;
    }

    .btn-fixed-width {
        width: 150px;
    }

    .nav-tabs-custom {
        position: sticky;
        top: 0;
        background-color: #fff;
        z-index: 1000;
        padding-top: 10px;
        padding-bottom: 10px;
    }

    .row,
    .col-12,
    .card,
    .card-body {
        overflow: visible;
    }

    .step-indicator {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-right: 1rem;
    }

    .step-active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .step-complete {
        background: #28a745;
        color: white;
    }
    .step-pending {
        background: #f8f9fa;
        color: #6c757d;
        border: 2px solid #dee2e6;
    }

    .progress-thin {
        height: 4px;
        border-radius: 2px;
    }

    .performance-card {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
        height: 100%;
        min-height: 220px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .performance-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .performance-card.high-achievement {
        border-color: #007bff;
        background: linear-gradient(135deg, #f8fbff 0%, #e3f2fd 100%);
    }

    .performance-card.pass-rate {
        border-color: #28a745;
        background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%);
    }

    .performance-card.non-failure {
        border-color: #ffc107;
        background: linear-gradient(135deg, #fffef8 0%, #fff3cd 100%);
    }

    .performance-card.total-students {
        border-color: #6f42c1;
        background: linear-gradient(135deg, #f8f7ff 0%, #f3f0ff 100%);
    }

    .chart-container {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .chart-wrapper {    
        position: relative;
        height: 350px !important;
        width: 100% !important;
        overflow: hidden;
    }

    .chart-header {
        text-align: center;
        margin-bottom: 1rem;
        padding: 0.75rem 1.5rem;
        border-radius: 0.25rem;
        font-weight: 600;
        color: white;
    }

    .chart-wrapper canvas {
        max-width: 100% !important;
        max-height: 100% !important;
    }

    .chart-header.scatter {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    }

    .chart-header.bar {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    .variance-positive {
        color: #28a745;
        font-weight: bold;
    }

    .variance-negative {
        color: #dc3545;
        font-weight: bold;
    }

    .grade-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        margin: 0.125rem;
    }

    .grade-merit { background: #6f42c1; color: white; }
    .grade-a { background: #007bff; color: white; }
    .grade-b { background: #28a745; color: white; }
    .grade-c { background: #17a2b8; color: white; }
    .grade-d { background: #ffc107; color: black; }
    .grade-e { background: #fd7e14; color: white; }
    .grade-u { background: #dc3545; color: white; }

    .section-card {
        transition: all 0.2s ease;
        margin-bottom: 1.5rem;
    }

    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
    }

    .export-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
</style>
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
            {{ $exam_type }} {{ $year }} Performance Analysis
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-end">
                    <div class="text-end">
                        <span class="badge bg-info">{{ $school_data->type ?? 'Unknown' }} School</span>
                        <span class="badge bg-secondary ms-2">{{ $total_students_analyzed }} Students</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start" role="tablist" id="analysisTabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#overview" role="tab" data-tab-id="overview">
                                    <span class="d-block d-sm-none"><i class="bi bi-graph-up"></i></span>
                                    <span class="d-none d-sm-block">Performance Overview</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#charts" role="tab" data-tab-id="charts">
                                    <span class="d-block d-sm-none"><i class="bi bi-bar-chart"></i></span>
                                    <span class="d-none d-sm-block">Analysis Charts</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#breakdown" role="tab" data-tab-id="breakdown">
                                    <span class="d-block d-sm-none"><i class="bi bi-table"></i></span>
                                    <span class="d-none d-sm-block">Class Breakdown</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#grades" role="tab" data-tab-id="grades">
                                    <span class="d-block d-sm-none"><i class="bi bi-award"></i></span>
                                    <span class="d-none d-sm-block">Grade Distribution</span>
                                </a>
                            </li>
                        </ul>
                        
                        <!-- Tab panes -->
                        <div class="tab-content p-3 text-muted">
                            <!-- Performance Overview Tab -->
                            <div class="tab-pane active" id="overview" role="tabpanel">
                                <!-- Performance Summary Cards -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-3">
                                        <div class="performance-card total-students">
                                            <div class="mb-3">
                                                <h6 class="text-primary mb-1">Total Students</h6>
                                                <small class="text-muted">Analyzed in {{ $year }}</small>
                                            </div>
                                            <div class="display-4 fw-bold text-primary">{{ $total_students_analyzed }}</div>
                                            <small class="text-muted mt-2 d-block">Across {{ $total_classes }} classes</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="performance-card high-achievement">
                                            <div class="mb-3">
                                                <h6 class="text-primary mb-1">{{ $targets['high_achievement']['label'] }}</h6>
                                                <small class="text-muted">High Achievement</small>
                                            </div>
                                            <div class="display-5 fw-bold text-primary">{{ $school_wide_metrics['high_achievement_percent'] }}%</div>
                                            <div class="mt-2">
                                                <small class="text-muted">Target: {{ $targets['high_achievement']['target'] }}%</small>
                                                <div class="mt-1">
                                                    <span class="badge {{ $performance_comparison['high_achievement']['status'] === 'achieved' ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $performance_comparison['high_achievement']['status'] === 'achieved' ? '✓ Target Met' : '✗ Below Target' }}
                                                    </span>
                                                </div>
                                                <small class="d-block mt-1 {{ $performance_comparison['high_achievement']['variance'] >= 0 ? 'variance-positive' : 'variance-negative' }}">
                                                    {{ $performance_comparison['high_achievement']['variance'] >= 0 ? '+' : '' }}{{ number_format($performance_comparison['high_achievement']['variance'], 1) }}% vs target
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="performance-card pass-rate">
                                            <div class="mb-3">
                                                <h6 class="text-success mb-1">{{ $targets['pass_rate']['label'] }}</h6>
                                                <small class="text-muted">Pass Rate</small>
                                            </div>
                                            <div class="display-5 fw-bold text-success">{{ $school_wide_metrics['pass_rate_percent'] }}%</div>
                                            <div class="mt-2">
                                                <small class="text-muted">Target: {{ $targets['pass_rate']['target'] }}%</small>
                                                <div class="mt-1">
                                                    <span class="badge {{ $performance_comparison['pass_rate']['status'] === 'achieved' ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $performance_comparison['pass_rate']['status'] === 'achieved' ? '✓ Target Met' : '✗ Below Target' }}
                                                    </span>
                                                </div>
                                                <small class="d-block mt-1 {{ $performance_comparison['pass_rate']['variance'] >= 0 ? 'variance-positive' : 'variance-negative' }}">
                                                    {{ $performance_comparison['pass_rate']['variance'] >= 0 ? '+' : '' }}{{ number_format($performance_comparison['pass_rate']['variance'], 1) }}% vs target
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="performance-card non-failure">
                                            <div class="mb-3">
                                                <h6 class="text-warning mb-1">{{ $targets['non_failure']['label'] }}</h6>
                                                <small class="text-muted">Non-Failure Rate</small>
                                            </div>
                                            <div class="display-5 fw-bold text-warning">{{ $school_wide_metrics['non_failure_percent'] }}%</div>
                                            <div class="mt-2">
                                                <small class="text-muted">Target: {{ $targets['non_failure']['target'] }}%</small>
                                                <div class="mt-1">
                                                    <span class="badge {{ $performance_comparison['non_failure']['status'] === 'achieved' ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $performance_comparison['non_failure']['status'] === 'achieved' ? '✓ Target Met' : '✗ Below Target' }}
                                                    </span>
                                                </div>
                                                <small class="d-block mt-1 {{ $performance_comparison['non_failure']['variance'] >= 0 ? 'variance-positive' : 'variance-negative' }}">
                                                    {{ $performance_comparison['non_failure']['variance'] >= 0 ? '+' : '' }}{{ number_format($performance_comparison['non_failure']['variance'], 1) }}% vs target
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
        
                                <!-- Top and Bottom Performing Classes -->
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="section-card card">
                                            <div class="card-header bg-success text-white">
                                                <h6 style="color:white;"><i class="fas fa-trophy me-2"></i>Top Performing Classes</h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-sm mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Class</th>
                                                                <th>Teacher</th>
                                                                <th>Pass Rate</th>
                                                                <th>Students</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($top_performing_classes as $class)
                                                            <tr>
                                                                <td><strong>{{ $class['name'] }}</strong></td>
                                                                <td>{{ $class['teacher'] }}</td>
                                                                <td>
                                                                    <span class="badge bg-success">{{ $class['percentage_analysis']['ABC']['T'] }}%</span>
                                                                </td>
                                                                <td>{{ $class['total_with_results'] }}</td>
                                                            </tr>
                                                            @empty
                                                            <tr>
                                                                <td colspan="4" class="text-center text-muted py-3">
                                                                    <i class="bi bi-info-circle me-2"></i>No class data available
                                                                </td>
                                                            </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="section-card card">
                                            <div class="card-header bg-secondary text-white">
                                                <h6 style="color:white;"><i class="fas fa-exclamation-triangle me-2"></i>Classes Needing Support</h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-sm mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Class</th>
                                                                <th>Teacher</th>
                                                                <th>Pass Rate</th>
                                                                <th>Students</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($classes_needing_intervention as $class)
                                                            <tr>
                                                                <td><strong>{{ $class['name'] }}</strong></td>
                                                                <td>{{ $class['teacher'] }}</td>
                                                                <td>
                                                                    <span class="badge bg-danger">{{ $class['percentage_analysis']['ABC']['T'] }}%</span>
                                                                </td>
                                                                <td>{{ $class['total_with_results'] }}</td>
                                                            </tr>
                                                            @empty
                                                            <tr>
                                                                <td colspan="4" class="text-center text-muted py-3">
                                                                    <i class="bi bi-check-circle me-2"></i>All classes performing well
                                                                </td>
                                                            </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
        
                            <!-- Analysis Charts Tab -->
                            <div class="tab-pane" id="charts" role="tabpanel">
                                <div class="row g-3">
                                    <!-- Scatter Chart: Targets vs Actual -->
                                    <div class="col-lg-6">
                                        <div class="chart-container">
                                            <div class="chart-header scatter">
                                                {{ $exam_type }} {{ $year }}: Overall Performance against Set Targets
                                            </div>
                                            <div class="chart-wrapper" style="position: relative; height: 350px; width: 100%;">
                                                <canvas id="scatterChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Bar Chart: Performance Percentages -->
                                    <div class="col-lg-6">
                                        <div class="chart-container">
                                            <div class="chart-header bar">
                                                {{ strtoupper($exam_type) }} - {{ $year }}
                                            </div>
                                            <div class="chart-wrapper" style="position: relative; height: 350px; width: 100%;">
                                                <canvas id="barChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
        
                            <!-- Class Breakdown Tab -->
                            <div class="tab-pane" id="breakdown" role="tabpanel">
                                <div class="section-card card">
                                    <div class="card-header bg-primary">
                                        <h6 style="color:white;" class="mb-0"><i class="fas fa-table me-2"></i>Class-by-Class Performance Breakdown</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th>Class</th>
                                                        <th>Teacher</th>
                                                        <th>Grade</th>
                                                        <th>Students</th>
                                                        <th>{{ $targets['high_achievement']['label'] }}</th>
                                                        <th>{{ $targets['pass_rate']['label'] }}</th>
                                                        <th>{{ $targets['non_failure']['label'] }}</th>
                                                        <th>Performance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($classes as $class)
                                                    <tr>
                                                        <td><strong>{{ $class['name'] }}</strong></td>
                                                        <td>{{ $class['teacher'] }}</td>
                                                        <td>{{ $class['grade_name'] }}</td>
                                                        <td>{{ $class['total_with_results'] }}</td>
                                                        <td>
                                                            <span class="badge {{ $class['percentage_analysis']['MAB']['T'] >= $targets['high_achievement']['target'] ? 'bg-success' : 'bg-secondary' }}">
                                                                {{ $class['percentage_analysis']['MAB']['T'] }}%
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $class['percentage_analysis']['ABC']['T'] >= $targets['pass_rate']['target'] ? 'bg-success' : 'bg-warning' }}">
                                                                {{ $class['percentage_analysis']['ABC']['T'] }}%
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @php
                                                                $nonFailurePercent = 100 - $class['percentage_analysis']['DEU']['T'];
                                                            @endphp
                                                            <span class="badge {{ $nonFailurePercent >= $targets['non_failure']['target'] ? 'bg-success' : 'bg-danger' }}">
                                                                {{ number_format($nonFailurePercent, 1) }}%
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if($class['percentage_analysis']['ABC']['T'] >= $targets['pass_rate']['target'])
                                                                <span class="badge bg-success">Excellent</span>
                                                            @elseif($class['percentage_analysis']['ABC']['T'] >= 50)
                                                                <span class="badge bg-warning">Good</span>
                                                            @else
                                                                <span class="badge bg-danger">Needs Support</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center text-muted py-4">
                                                            <i class="fas fa-info-circle me-2"></i>No class performance data available
                                                        </td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
        
                            <!-- Grade Distribution Tab -->
                            <div class="tab-pane" id="grades" role="tabpanel">
                                <div class="section-card card">
                                    <div class="card-header bg-info">
                                        <h6 style="color:white;" class="mb-0"><i class="fas fa-award me-2"></i>Grade Distribution Analysis</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-4">
                                            <div class="col-12">
                                                <div class="text-center">
                                                    <h5>Grade Distribution Overview</h5>
                                                    <div class="mt-3">
                                                        @foreach($grade_distribution as $grade => $count)
                                                            @php
                                                                $percentage = $total_students_analyzed > 0 ? round(($count / $total_students_analyzed) * 100, 1) : 0;
                                                                $gradeClass = '';
                                                                switch($grade) {
                                                                    case 'Merit': $gradeClass = 'grade-merit'; break;
                                                                    case 'A': $gradeClass = 'grade-a'; break;
                                                                    case 'B': $gradeClass = 'grade-b'; break;
                                                                    case 'C': $gradeClass = 'grade-c'; break;
                                                                    case 'D': $gradeClass = 'grade-d'; break;
                                                                    case 'E': $gradeClass = 'grade-e'; break;
                                                                    case 'U': $gradeClass = 'grade-u'; break;
                                                                }
                                                            @endphp
                                                            <span class="grade-badge {{ $gradeClass }}">
                                                                {{ $grade }}: {{ $count }} ({{ $percentage }}%)
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th>Grade</th>
                                                        <th>Count</th>
                                                        <th>Percentage</th>
                                                        <th>Category</th>
                                                        <th>Performance Level</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($grade_distribution as $grade => $count)
                                                    @php
                                                        $percentage = $total_students_analyzed > 0 ? round(($count / $total_students_analyzed) * 100, 1) : 0;
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            @php
                                                                $gradeClass = '';
                                                                switch($grade) {
                                                                    case 'Merit': $gradeClass = 'grade-merit'; break;
                                                                    case 'A': $gradeClass = 'grade-a'; break;
                                                                    case 'B': $gradeClass = 'grade-b'; break;
                                                                    case 'C': $gradeClass = 'grade-c'; break;
                                                                    case 'D': $gradeClass = 'grade-d'; break;
                                                                    case 'E': $gradeClass = 'grade-e'; break;
                                                                    case 'U': $gradeClass = 'grade-u'; break;
                                                                }
                                                            @endphp
                                                            <span class="grade-badge {{ $gradeClass }}">{{ $grade }}</span>
                                                        </td>
                                                        <td><strong>{{ $count }}</strong></td>
                                                        <td>{{ $percentage }}%</td>
                                                        <td>
                                                            @if(in_array($grade, ['Merit', 'A', 'B']))
                                                                <span class="text-success">High Achievement</span>
                                                            @elseif($grade === 'C')
                                                                <span class="text-primary">Pass</span>
                                                            @elseif($grade === 'D')
                                                                <span class="text-warning">Borderline</span>
                                                            @else
                                                                <span class="text-danger">Failure</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if(in_array($grade, ['Merit', 'A', 'B']))
                                                                Excellence
                                                            @elseif($grade === 'C')
                                                                Satisfactory
                                                            @elseif($grade === 'D')
                                                                Needs Improvement
                                                            @else
                                                                Requires Intervention
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = @json($chart_data);
    const year = @json($year);
    const examType = @json($exam_type);
    
    initializeScatterChart();
    initializeBarChart();

    function initializeScatterChart() {
        const scatterCtx = document.getElementById('scatterChart').getContext('2d');
        new Chart(scatterCtx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Targets',
                    data: chartData.scatter_data.map(item => ({
                        x: item.category,
                        y: item.target
                    })),
                    backgroundColor: '#6b7280',
                    borderColor: '#6b7280',
                    pointRadius: 8,
                    pointHoverRadius: 10,
                    pointBorderWidth: 2,
                    pointHoverBorderWidth: 2
                }, {
                    label: 'Actual',
                    data: chartData.scatter_data.map(item => ({
                        x: item.category,
                        y: item.actual
                    })),
                    backgroundColor: '#f59e0b',
                    borderColor: '#f59e0b',
                    pointRadius: 8,
                    pointHoverRadius: 10,
                    pointBorderWidth: 2,
                    pointHoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'point'
                },
                layout: {
                    padding: {
                        top: 20,
                        bottom: 20,
                        left: 20,
                        right: 20
                    }
                },
                scales: {
                    x: {
                        type: 'category',
                        labels: chartData.scatter_data.map(item => item.category),
                        title: {
                            display: true,
                            text: 'Performance Categories',
                            font: { size: 12, weight: 'bold' }
                        },
                        grid: {
                            display: true,
                            color: '#e9ecef'
                        }
                    },
                    y: {
                        min: 0,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Percentage (%)',
                            font: { size: 12, weight: 'bold' }
                        },
                        grid: {
                            color: '#e9ecef'
                        },
                        ticks: {
                            stepSize: 10
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 11 },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: '#dee2e6',
                        borderWidth: 1,
                        cornerRadius: 6,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                animation: {
                    duration: 300
                }
            }
        });
    }

    function initializeBarChart() {
        const barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: chartData.bar_data.map(item => item.category),
                datasets: [{
                    label: 'Percentage',
                    data: chartData.bar_data.map(item => item.percentage),
                    backgroundColor: ['#374151', '#10b981', '#f59e0b'],
                    borderColor: ['#1f2937', '#047857', '#d97706'],
                    borderWidth: 2,
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                layout: {
                    padding: {
                        top: 30,
                        bottom: 20,
                        left: 20,
                        right: 20
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Percentage (%)',
                            font: { size: 12, weight: 'bold' }
                        },
                        grid: {
                            color: '#e9ecef'
                        },
                        ticks: {
                            stepSize: 10
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Performance Categories',
                            font: { size: 12, weight: 'bold' }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: '#dee2e6',
                        borderWidth: 1,
                        cornerRadius: 6,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + '%';
                            }
                        }
                    }
                },
                animation: {
                    duration: 300
                }
            },
            plugins: [{
                afterDatasetsDraw: function(chart) {
                    const ctx = chart.ctx;
                    chart.data.datasets.forEach(function(dataset, i) {
                        const meta = chart.getDatasetMeta(i);
                        meta.data.forEach(function(bar, index) {
                            const data = dataset.data[index];
                            ctx.fillStyle = '#000';
                            ctx.font = 'bold 14px Arial';
                            ctx.textAlign = 'center';
                            ctx.fillText(data + '%', bar.x, bar.y - 8);
                        });
                    });
                }
            }]
        });
    }    
    const tabList = document.getElementById('analysisTabs');
    const tabs = tabList.querySelectorAll('.nav-link');
    const storageKey = 'performanceAnalysisActiveTab';

    function setActiveTab(tabId) {
        tabs.forEach(tab => {
            if (tab.getAttribute('data-tab-id') === tabId) {
                tab.classList.add('active');
                document.querySelector(tab.getAttribute('href')).classList.add('active', 'show');
            } else {
                tab.classList.remove('active');
                document.querySelector(tab.getAttribute('href')).classList.remove('active', 'show');
            }
        });
    }

    const storedTabId = localStorage.getItem(storageKey);
    if (storedTabId) {
        setActiveTab(storedTabId);
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            const tabId = this.getAttribute('data-tab-id');
            localStorage.setItem(storageKey, tabId);
        });
    });
});
</script>
@endsection