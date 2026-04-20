<style>
    /* Dashboard Container */
    .dashboard-container {
        background: white;
        border-radius: 3px;
        padding: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .dashboard-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 28px;
        border-radius: 3px 3px 0 0;
    }

    .dashboard-header h3 {
        margin: 0;
        font-weight: 600;
    }

    .dashboard-header p {
        margin: 6px 0 0 0;
        opacity: 0.9;
        font-size: 14px;
    }

    .dashboard-body {
        padding: 24px;
    }

    .dashboard-stat-item {
        padding: 10px 0;
        text-align: center;
    }

    .dashboard-stat-item h4 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0;
    }

    .dashboard-stat-item small {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.75;
    }

    /* Table Styling */
    .dashboard-table thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
    }

    .dashboard-table tbody tr:hover {
        background-color: #f9fafb;
    }

    /* Enhanced Notification Styles - Custom Blue */
    .notification-container {
        background: #4549A2;
        border-radius: 3px;
        padding: 1px;
    }

    .notification-inner {
        background: white;
        border-radius: 3px;
        height: 100%;
    }

    .notification-header {
        background: #4549A2;
        color: white;
        border-radius: 3px 3px 0 0;
        padding: 1.25rem;
    }

    .notification-scroll {
        scrollbar-width: thin;
        scrollbar-color: #4549A2 transparent;
        max-height: 600px;
        overflow-y: auto;
    }

    .notification-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .notification-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .notification-scroll::-webkit-scrollbar-thumb {
        background: #4549A2;
        border-radius: 3px;
    }

    .notification-item {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 3px;
        margin: 0.75rem;
        padding: 1rem;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        position: relative;
        overflow: hidden;
    }

    .notification-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: #4549A2;
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .notification-item:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(69, 73, 162, 0.15);
        border-color: #4549A2;
    }

    .notification-item:hover::before {
        transform: scaleY(1);
    }

    .notification-item.pinned {
        background: #fffbeb;
        border-color: #f59e0b;
    }

    .notification-item.pinned::before {
        background: #f59e0b;
        transform: scaleY(1);
    }

    .notification-item.pinned:hover {
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
        border-color: #d97706;
    }

    .badge-pinned {
        background: #fef3c7;
        color: #b45309;
    }

    .notification-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
        transition: color 0.3s ease;
    }

    .notification-title:hover {
        color: #4549A2;
    }

    .notification-meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
    }

    .meta-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 3px;
        font-size: 0.75rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .badge-time {
        background: #f3f4f6;
        color: #6b7280;
    }

    .badge-department {
        background: #e8e9f3;
        color: #4549A2;
    }

    .badge-expires {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-attachments {
        background: #f3e8ff;
        color: #6b21a8;
    }

    .notification-content {
        color: #4b5563;
        line-height: 1.6;
        margin-bottom: 0.75rem;
    }

    .notification-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.75rem;
        border-top: 1px solid #f3f4f6;
        font-size: 0.875rem;
    }

    .notification-stats {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .notif-stat-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        color: #6b7280;
        transition: color 0.3s ease;
    }

    .notif-stat-item:hover {
        color: #4549A2;
    }

    .empty-state {
        padding: 3rem;
        text-align: center;
    }

    .empty-icon {
        width: 120px;
        height: 120px;
        margin: 0 auto 1.5rem;
        background: #4549A2;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.05);
            opacity: 0.8;
        }
    }

    .comment-section {
        background: #f9fafb;
        border-radius: 3px;
        padding: 0.75rem;
        margin-top: 0.75rem;
    }

    .comment-item {
        background: white;
        border-radius: 3px;
        padding: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .header-badge {
        background: white;
        color: #4549A2;
        padding: 0.25rem 0.75rem;
        border-radius: 3px;
        font-weight: 600;
    }

    /* Chart Animation */
    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    @media (max-width: 768px) {
        .dashboard-header {
            padding: 20px;
        }

        .dashboard-body {
            padding: 16px;
        }

        .dashboard-stat-item h4 {
            font-size: 1.25rem;
        }

        .dashboard-stat-item small {
            font-size: 0.75rem;
        }
    }
</style>

<div class="row">
    <!-- Chart Section -->
    <div class="col-xxl-8 col-md-8">
        <div class="dashboard-container">
            <div class="dashboard-body">
                <div id="main" style="width: 100%; height: 500px;"></div>
                <div class="table-responsive mt-3">
                    <table class="table table-sm table-bordered dashboard-table">
                        <thead>
                            <tr>
                                <th>Grade Level</th>
                                @foreach ($grades as $grade)
                                    <th colspan="2" class="text-center">{{ $grade->name }}</th>
                                @endforeach
                            </tr>
                            <tr>
                                <th>Gender Distribution</th>
                                @foreach ($grades as $grade)
                                    <th class="text-center">
                                        <span style="color: #3b82f6;">♂</span> M
                                    </th>
                                    <th class="text-center">
                                        <span style="color: #ec4899;">♀</span> F
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($students) > 0)
                                <tr>
                                    <td>Total Students</td>
                                    @foreach ($grades as $grade)
                                        <td class="text-center">{{ $analysis[$grade->name]['M'] }}</td>
                                        <td class="text-center">{{ $analysis[$grade->name]['F'] }}</td>
                                    @endforeach
                                </tr>
                            @else
                                <tr>
                                    <td colspan="{{ count($grades) * 2 + 1 }}" class="text-center py-4">
                                        <i class="bx bx-info-circle" style="font-size: 24px; color: #9ca3af;"></i>
                                        <p class="text-muted mb-0 mt-2">No data available</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Section -->
    <div class="col-xl-4 col-md-4">
        {{-- Self-Service Clock Widget - Only show if module is visible AND enabled in settings --}}
        @auth
            @php
                $moduleVisible = app(\App\Services\ModuleVisibilityService::class)->isModuleVisible('staff_attendance');
                $selfClockInSetting = \App\Models\StaffAttendance\StaffAttendanceSetting::where('key', 'self_clock_in_enabled')->first();
                $selfClockInEnabled = $selfClockInSetting ? ($selfClockInSetting->value['enabled'] ?? true) : true;
            @endphp
            @if($moduleVisible && $selfClockInEnabled)
                @include('staff-attendance.self-service.clock-widget')
            @endif
        @endauth

        @if ($notifications->isEmpty())
            <div class="notification-container">
                <div class="notification-inner">
                    <div class="notification-header">
                        <h4 class="m-0 fw-bold text-white">
                            <i class="bx bx-bell-plus me-2"></i>Notifications & News
                        </h4>
                    </div>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="bx bx-bell-off" style="font-size: 48px; color: white;"></i>
                        </div>
                        <h5 class="fw-bold text-gray-800">No Notifications Yet</h5>
                        <p class="text-muted">
                            Check back here for important updates,<br>news and announcements.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="notification-container">
                <div class="notification-inner">
                    <div class="notification-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="m-0 fw-bold text-white">
                                <i class="bx bx-bell me-2"></i>Notifications & News
                            </h4>
                            <div class="d-flex align-items-center gap-2">
                                @if (collect($notifications)->where('is_pinned', true)->count() > 0)
                                    <span class="header-badge" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24;">
                                        <i class="fas fa-thumbtack me-1"></i>{{ collect($notifications)->where('is_pinned', true)->count() }} Pinned
                                    </span>
                                @endif
                                <span class="header-badge">
                                    {{ count($notifications) }} New
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="notification-scroll">
                        @foreach ($notifications as $notification)
                            <div class="notification-item {{ !empty($notification['is_pinned']) ? 'pinned' : '' }}">
                                <h6 class="notification-title">
                                    <a href="{{ route('notification.details', $notification['id']) }}"
                                        class="text-decoration-none">
                                        @if (!empty($notification['is_pinned']))
                                            <i class="fas fa-thumbtack text-warning me-1"></i>
                                        @endif
                                        {{ $notification['title'] }}
                                    </a>
                                </h6>

                                <div class="notification-meta">
                                    @if (!empty($notification['is_pinned']))
                                        <span class="meta-badge badge-pinned">
                                            <i class="fas fa-thumbtack"></i>
                                            Pinned
                                        </span>
                                    @endif
                                    <span class="meta-badge badge-time">
                                        <i class="bx bx-time-five"></i>
                                        {{ $notification['dates']['created_human'] }}
                                    </span>

                                    @if (isset($notification['department']) && $notification['department'])
                                        <span class="meta-badge badge-department">
                                            <i class="bx bx-building"></i>
                                            {{ $notification['department']['name'] }}
                                        </span>
                                    @endif

                                    @if ($notification['dates']['end'])
                                        <span class="meta-badge badge-expires">
                                            <i class="bx bx-calendar-x"></i>
                                            Expires
                                            {{ \Carbon\Carbon::parse($notification['dates']['end'])->diffForHumans() }}
                                        </span>
                                    @endif

                                    @if (!empty($notification['attachments']))
                                        <span class="meta-badge badge-attachments">
                                            <i class="bx bx-paperclip"></i>
                                            {{ count($notification['attachments']) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="notification-content">
                                    @if (strlen($notification['body']) > 200)
                                        {!! Str::limit($notification['body'], 200) !!}
                                        <a href="{{ route('notification.details', $notification['id']) }}"
                                            class="text-decoration-none fw-semibold" style="color: #4549A2;">
                                            Read more →
                                        </a>
                                    @else
                                        {!! $notification['body'] !!}
                                    @endif
                                </div>

                                <div class="notification-footer">
                                    <div class="text-muted">
                                        <i class="bx bx-user-circle"></i>
                                        {{ $notification['creator']['name'] }}
                                    </div>

                                    <div class="notification-stats">
                                        @if (!empty($notification['recipients']))
                                            <span class="notif-stat-item">
                                                <i class="bx bx-group"></i>
                                                {{ count($notification['recipients']) }}
                                            </span>
                                        @endif

                                        @if ($notification['allow_comments'])
                                            <span class="notif-stat-item">
                                                <i class="bx bx-comment-dots"></i>
                                                {{ count($notification['comments']) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if (!empty($notification['comments']) && count($notification['comments']) > 0)
                                    <div class="comment-section">
                                        <small class="text-muted fw-semibold d-block mb-2">Recent Comments</small>
                                        @foreach (array_slice($notification['comments'], 0, 1) as $comment)
                                            <div class="comment-item">
                                                <div class="d-flex justify-content-between">
                                                    <small class="fw-semibold">{{ $comment['user']['name'] }}</small>
                                                    <small class="text-muted">{{ $comment['created_at'] }}</small>
                                                </div>
                                                <small
                                                    class="text-muted">{{ Str::limit($comment['comment'], 60) }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    var myChart = echarts.init(document.getElementById('main'));
    var grades = @json($grades->pluck('name'));
    var maleData = [];
    var femaleData = [];
    var totalData = [];
    var malePercentage = [];
    var femalePercentage = [];

    @if (count($students) > 0)
        @foreach ($grades as $grade)
            var male = {{ $analysis[$grade->name]['M'] }};
            var female = {{ $analysis[$grade->name]['F'] }};
            var total = male + female;
            maleData.push(male);
            femaleData.push(female);
            totalData.push(total);
            malePercentage.push(total > 0 ? ((male / total) * 100).toFixed(1) : 0);
            femalePercentage.push(total > 0 ? ((female / total) * 100).toFixed(1) : 0);
        @endforeach
    @endif

    var option = {
        backgroundColor: 'transparent',
        title: {
            text: 'Student Distribution Analytics',
            subtext: 'Interactive Gender & Grade Analysis',
            left: 'center',
            top: 10,
            textStyle: {
                fontSize: 24,
                fontWeight: 'bold',
                color: '#1f2937'
            },
            subtextStyle: {
                fontSize: 14,
                color: '#6b7280'
            }
        },
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'cross',
                crossStyle: {
                    color: '#999'
                },
                label: {
                    backgroundColor: '#4549A2'
                }
            },
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            borderColor: '#4549A2',
            borderWidth: 1,
            borderRadius: 3,
            textStyle: {
                color: '#333'
            },
            formatter: function(params) {
                var result = '<div style="font-weight:bold;margin-bottom:5px;">' + params[0].name + '</div>';
                params.forEach(function(item) {
                    var value = item.value;
                    var icon =
                        '<span style="display:inline-block;margin-right:5px;border-radius:3px;width:10px;height:10px;background-color:' +
                        item.color + ';"></span>';

                    if (item.seriesName === 'Male' || item.seriesName === 'Female') {
                        var total = totalData[item.dataIndex];
                        var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                        result += icon + item.seriesName + ': <strong>' + value + '</strong> (' +
                            percentage + '%)<br/>';
                    } else if (item.seriesName === 'Total') {
                        result += icon + '<strong>' + item.seriesName + ': ' + value + '</strong><br/>';
                    } else if (item.seriesName === 'Gender Ratio') {
                        result += icon + item.seriesName + ': <strong>' + value +
                            '%</strong> Male<br/>';
                    } else if (item.seriesName === 'Average') {
                        result += icon + item.seriesName + ': <strong>' + value.toFixed(0) +
                            '</strong><br/>';
                    }
                });
                return result;
            }
        },
        toolbox: {
            feature: {
                dataView: {
                    show: true,
                    readOnly: false,
                    title: 'Data View',
                    lang: ['Data View', 'Close', 'Refresh']
                },
                magicType: {
                    show: true,
                    type: ['line', 'bar', 'stack', 'tiled'],
                    title: {
                        line: 'Line Chart',
                        bar: 'Bar Chart',
                        stack: 'Stacked',
                        tiled: 'Tiled'
                    }
                },
                restore: {
                    show: true,
                    title: 'Reset'
                },
                saveAsImage: {
                    show: true,
                    title: 'Save Image'
                }
            },
            right: 20,
            top: 15
        },
        legend: {
            data: ['Male', 'Female', 'Total', 'Gender Ratio', 'Average'],
            bottom: 5,
            textStyle: {
                fontSize: 12
            },
            selected: {
                'Male': true,
                'Female': true,
                'Total': true,
                'Gender Ratio': true,
                'Average': false
            }
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '12%',
            top: '18%',
            containLabel: true
        },
        xAxis: [{
            type: 'category',
            data: grades,
            axisPointer: {
                type: 'shadow'
            },
            axisLabel: {
                interval: 0,
                hideOverlap: false,
                rotate: 0,
                fontSize: 12,
                fontWeight: 'bold',
                color: '#374151'
            },
            axisLine: {
                lineStyle: {
                    color: '#e5e7eb'
                }
            }
        }],
        yAxis: [{
                type: 'value',
                name: 'No of Students',
                min: 0,
                axisLabel: {
                    formatter: '{value}',
                    color: '#6b7280'
                },
                nameTextStyle: {
                    fontSize: 12,
                    fontWeight: 'bold',
                    color: '#374151'
                },
                splitLine: {
                    lineStyle: {
                        type: 'dashed',
                        color: '#f3f4f6'
                    }
                }
            },
            {
                type: 'value',
                name: 'Percentage %',
                min: 0,
                max: 100,
                axisLabel: {
                    formatter: '{value}%',
                    color: '#6b7280'
                },
                nameTextStyle: {
                    fontSize: 12,
                    fontWeight: 'bold',
                    color: '#374151'
                },
                splitLine: {
                    show: false
                }
            }
        ],
        series: [{
                name: 'Male',
                type: 'bar',
                barWidth: '25%',
                itemStyle: {
                    color: '#4549A2',
                    borderRadius: [3, 3, 0, 0],
                    shadowColor: 'rgba(69, 73, 162, 0.2)',
                    shadowBlur: 4,
                    shadowOffsetY: 2
                },
                emphasis: {
                    itemStyle: {
                        color: '#363a7d',
                        shadowBlur: 10,
                        shadowColor: 'rgba(69, 73, 162, 0.4)'
                    }
                },
                label: {
                    show: true,
                    position: 'inside',
                    formatter: '{c}',
                    color: 'white',
                    fontWeight: 'bold'
                },
                data: maleData,
                animationDelay: function(idx) {
                    return idx * 100;
                }
            },
            {
                name: 'Female',
                type: 'bar',
                barWidth: '25%',
                itemStyle: {
                    color: '#ec4899',
                    borderRadius: [3, 3, 0, 0],
                    shadowColor: 'rgba(236, 72, 153, 0.2)',
                    shadowBlur: 4,
                    shadowOffsetY: 2
                },
                emphasis: {
                    itemStyle: {
                        color: '#db2777',
                        shadowBlur: 10,
                        shadowColor: 'rgba(236, 72, 153, 0.4)'
                    }
                },
                label: {
                    show: true,
                    position: 'inside',
                    formatter: '{c}',
                    color: 'white',
                    fontWeight: 'bold'
                },
                data: femaleData,
                animationDelay: function(idx) {
                    return idx * 100 + 50;
                }
            },
            {
                name: 'Total',
                type: 'line',
                smooth: true,
                symbol: 'circle',
                symbolSize: 12,
                lineStyle: {
                    width: 4,
                    color: '#10b981',
                    shadowBlur: 8,
                    shadowColor: 'rgba(16, 185, 129, 0.4)',
                    cap: 'round'
                },
                itemStyle: {
                    color: '#10b981',
                    borderWidth: 3,
                    borderColor: '#fff',
                    shadowBlur: 8,
                    shadowColor: 'rgba(16, 185, 129, 0.4)'
                },
                emphasis: {
                    scale: 1.8,
                    itemStyle: {
                        color: '#059669'
                    }
                },
                label: {
                    show: true,
                    formatter: '{c}',
                    color: '#10b981',
                    fontWeight: 'bold',
                    backgroundColor: 'rgba(255,255,255,0.8)',
                    padding: [2, 4],
                    borderRadius: 3
                },
                areaStyle: {
                    color: {
                        type: 'linear',
                        x: 0,
                        y: 0,
                        x2: 0,
                        y2: 1,
                        colorStops: [{
                            offset: 0,
                            color: 'rgba(16, 185, 129, 0.3)'
                        }, {
                            offset: 1,
                            color: 'rgba(16, 185, 129, 0.05)'
                        }]
                    }
                },
                data: totalData,
                animationDelay: function(idx) {
                    return idx * 100 + 100;
                }
            },
            {
                name: 'Gender Ratio',
                type: 'line',
                yAxisIndex: 1,
                smooth: true,
                symbol: 'diamond',
                symbolSize: 10,
                lineStyle: {
                    width: 2,
                    type: 'dashed',
                    color: '#f59e0b'
                },
                itemStyle: {
                    color: '#f59e0b',
                    borderWidth: 2,
                    borderColor: '#fff'
                },
                emphasis: {
                    itemStyle: {
                        color: '#d97706',
                        scale: 1.5
                    }
                },
                data: malePercentage,
                animationDelay: function(idx) {
                    return idx * 100 + 150;
                }
            },
            {
                name: 'Average',
                type: 'line',
                smooth: false,
                lineStyle: {
                    width: 2,
                    type: 'dotted',
                    color: '#9ca3af'
                },
                itemStyle: {
                    opacity: 0
                },
                symbol: 'none',
                markLine: {
                    data: [{
                        type: 'average',
                        name: 'Average',
                        label: {
                            formatter: 'Avg: {c}',
                            position: 'middle',
                            color: '#6b7280'
                        },
                        lineStyle: {
                            color: '#9ca3af',
                            type: 'solid',
                            width: 2
                        }
                    }],
                    symbol: ['none', 'none']
                },
                data: totalData
            }
        ],
        animationEasing: 'elasticOut',
        animationDuration: 2000,
        animationDelayUpdate: function(idx) {
            return idx * 5;
        }
    };

    myChart.setOption(option);

    // Make chart responsive
    window.addEventListener('resize', function() {
        myChart.resize();
    });

    // Add interactivity - click to isolate series
    myChart.on('legendselectchanged', function(params) {
        // Custom legend behavior if needed
    });
</script>
