@extends('layouts.master')
@section('title')
    Team Attendance Dashboard
@endsection
@section('css')
    <style>
        .attendance-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .attendance-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .attendance-body {
            padding: 24px;
        }

        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        /* List cards */
        .list-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .list-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .list-card-header h5 {
            margin: 0;
            font-weight: 600;
            color: #374151;
        }

        .list-card-body {
            padding: 0;
            max-height: 320px;
            overflow-y: auto;
        }

        .staff-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            border-bottom: 1px solid #f3f4f6;
        }

        .staff-item:last-child {
            border-bottom: none;
        }

        .staff-item:hover {
            background-color: #f9fafb;
        }

        .staff-info {
            flex: 1;
        }

        .staff-info strong {
            color: #374151;
            display: block;
            margin-bottom: 2px;
        }

        .staff-info small {
            color: #6b7280;
        }

        .staff-meta {
            text-align: right;
            color: #6b7280;
            font-size: 13px;
        }

        .staff-meta .time {
            font-weight: 500;
            color: #374151;
        }

        .staff-meta .late-minutes {
            color: #dc2626;
            font-size: 12px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.3;
            margin-bottom: 12px;
            display: block;
        }

        .empty-state p {
            margin: 0;
        }

        /* Alert card */
        .alert-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .alert-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .alert-card-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .alert-card-body {
            padding: 0;
        }

        .alert-item {
            padding: 12px 20px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
        }

        .alert-item:last-child {
            border-bottom: none;
        }

        .alert-item:hover {
            background-color: #fffbeb;
        }

        /* Chart card */
        .chart-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .chart-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .chart-card-header h5 {
            margin: 0;
            font-weight: 600;
            color: #374151;
        }

        .chart-card-body {
            padding: 20px;
        }

        /* Badge styles */
        .badge {
            font-weight: 500;
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .attendance-header {
                padding: 20px;
            }

            .attendance-body {
                padding: 16px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="javascript:history.back()">Back</a>
        @endslot
        @slot('title')
            Team Attendance
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="attendance-container">
        <div class="attendance-header">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <h3 style="margin:0;"><i class="fas fa-users me-2"></i>Team Attendance</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Today's attendance overview for your team ({{ $dashboardData['team_size'] }} members)</p>
                </div>
                <div class="col-md-7">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $dashboardData['summary']['present'] }}</h4>
                                <small class="opacity-75">Present</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $dashboardData['summary']['absent'] }}</h4>
                                <small class="opacity-75">Absent</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $dashboardData['summary']['late'] }}</h4>
                                <small class="opacity-75">Late</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $dashboardData['summary']['on_leave'] }}</h4>
                                <small class="opacity-75">On Leave</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="attendance-body">
            <div class="help-text">
                <div class="help-title">Dashboard Overview</div>
                <div class="help-content">
                    View your team's attendance at a glance. The statistics above show today's attendance summary.
                    Review absent and late staff below, and check alerts for staff requiring attention.
                </div>
            </div>

            <!-- Weekly Trends Chart (ECharts) -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="chart-card">
                        <div class="chart-card-header">
                            <h5><i class="fas fa-chart-bar me-2"></i>Weekly Attendance Trends</h5>
                        </div>
                        <div class="chart-card-body">
                            <div id="weeklyTrendsChart" style="height: 300px; width: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Absent and Late Staff Lists -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="list-card">
                        <div class="list-card-header">
                            <h5><i class="fas fa-user-times text-danger me-2"></i>Absent Today</h5>
                            <span class="badge bg-danger">{{ count($dashboardData['absent_list']) }}</span>
                        </div>
                        <div class="list-card-body">
                            @forelse($dashboardData['absent_list'] as $record)
                                <div class="staff-item">
                                    <div class="staff-info">
                                        <strong>{{ $record->user->firstname }} {{ $record->user->lastname }}</strong>
                                        <small class="d-block">{{ $record->user->department ?? 'No department' }}</small>
                                    </div>
                                    @if($record->notes)
                                        <div class="staff-meta">
                                            <small title="{{ $record->notes }}">{{ Str::limit($record->notes, 30) }}</small>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="empty-state">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <p>No absent staff today</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="list-card">
                        <div class="list-card-header">
                            <h5><i class="fas fa-clock text-warning me-2"></i>Late Arrivals</h5>
                            <span class="badge bg-warning text-dark">{{ count($dashboardData['late_list']) }}</span>
                        </div>
                        <div class="list-card-body">
                            @forelse($dashboardData['late_list'] as $record)
                                <div class="staff-item">
                                    <div class="staff-info">
                                        <strong>{{ $record->user->firstname }} {{ $record->user->lastname }}</strong>
                                        <small class="d-block">{{ $record->user->department ?? 'No department' }}</small>
                                    </div>
                                    <div class="staff-meta">
                                        @if($record->clock_in)
                                            <span class="time">{{ \Carbon\Carbon::parse($record->clock_in)->format('H:i') }}</span>
                                        @endif
                                        @if($record->late_minutes)
                                            <span class="late-minutes d-block">{{ $record->late_minutes }} min late</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <p>No late arrivals today</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Absenteeism Alerts -->
            @if(count($dashboardData['alerts']) > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert-card">
                        <div class="alert-card-header bg-warning text-dark">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Absenteeism Alerts</h5>
                        </div>
                        <div class="alert-card-body">
                            @foreach($dashboardData['alerts'] as $alert)
                                <div class="alert-item">
                                    <i class="fas fa-user-clock text-warning me-2"></i>
                                    <strong>{{ $alert['name'] }}</strong>&nbsp;has been absent for
                                    <span class="fw-bold text-danger">&nbsp;{{ $alert['days'] }} consecutive days</span>
                                    &nbsp;(since {{ $alert['start_date']->format('M j') }})
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeAlertDismissal();
            initializeWeeklyTrendsChart();
        });

        function initializeAlertDismissal() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    }
                }, 5000);
            });
        }

        function initializeWeeklyTrendsChart() {
            var weeklyTrendsData = @json($dashboardData['weekly_trends']);
            var chartDom = document.getElementById('weeklyTrendsChart');

            if (!chartDom) return;

            if (weeklyTrendsData && weeklyTrendsData.labels && weeklyTrendsData.labels.length > 0) {
                var weeklyChart = echarts.init(chartDom);

                var option = {
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        },
                        formatter: function(params) {
                            var result = params[0].name + '<br/>';
                            params.forEach(function(item) {
                                result += item.marker + ' ' + item.seriesName + ': ' + item.value + ' staff<br/>';
                            });
                            return result;
                        }
                    },
                    legend: {
                        data: ['Present', 'Absent', 'Late', 'On Leave'],
                        top: 0,
                        left: 0,
                        textStyle: {
                            fontSize: 12
                        }
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        top: '15%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        data: weeklyTrendsData.labels,
                        axisLine: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        }
                    },
                    yAxis: {
                        type: 'value',
                        name: 'Staff Count',
                        nameLocation: 'middle',
                        nameGap: 40,
                        splitLine: {
                            lineStyle: {
                                type: 'dashed',
                                color: '#e5e7eb'
                            }
                        }
                    },
                    series: [
                        {
                            name: 'Present',
                            type: 'bar',
                            stack: 'total',
                            barWidth: '50%',
                            itemStyle: {
                                color: '#10b981',
                                borderRadius: [0, 0, 0, 0]
                            },
                            emphasis: {
                                itemStyle: {
                                    color: '#059669'
                                }
                            },
                            data: weeklyTrendsData.present || []
                        },
                        {
                            name: 'Absent',
                            type: 'bar',
                            stack: 'total',
                            itemStyle: {
                                color: '#ef4444'
                            },
                            emphasis: {
                                itemStyle: {
                                    color: '#dc2626'
                                }
                            },
                            data: weeklyTrendsData.absent || []
                        },
                        {
                            name: 'Late',
                            type: 'bar',
                            stack: 'total',
                            itemStyle: {
                                color: '#f59e0b'
                            },
                            emphasis: {
                                itemStyle: {
                                    color: '#d97706'
                                }
                            },
                            data: weeklyTrendsData.late || []
                        },
                        {
                            name: 'On Leave',
                            type: 'bar',
                            stack: 'total',
                            itemStyle: {
                                color: '#3b82f6',
                                borderRadius: [3, 3, 0, 0]
                            },
                            emphasis: {
                                itemStyle: {
                                    color: '#2563eb'
                                }
                            },
                            data: weeklyTrendsData.on_leave || []
                        }
                    ]
                };

                weeklyChart.setOption(option);

                // Resize chart when window resizes
                window.addEventListener('resize', function() {
                    weeklyChart.resize();
                });
            } else {
                chartDom.innerHTML =
                    '<div class="empty-state" style="height: 300px; display: flex; align-items: center; justify-content: center; flex-direction: column;">' +
                    '<i class="fas fa-chart-bar" style="font-size: 48px; opacity: 0.3; margin-bottom: 12px;"></i>' +
                    '<p class="text-muted">No attendance data available for this week</p>' +
                    '</div>';
            }
        }
    </script>
@endsection
