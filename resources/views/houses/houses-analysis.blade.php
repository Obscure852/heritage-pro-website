@extends('layouts.master')

@section('title')
    House List Analysis
@endsection

@section('css')
    @include('houses.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('house.index') }}">Houses</a>
        @endslot
        @slot('title')
            House List Analysis
        @endslot
    @endcomponent

    <div class="print-toolbar">
        <button type="button" class="btn btn-light" onclick="window.print()">
            <i class="bx bx-printer me-1"></i> Print
        </button>
    </div>

    <div class="houses-report-container printable">
        <div class="houses-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-1 text-white">House List Analysis</h3>
                    <p class="mb-0 opacity-75">Review saved house colors, leadership, and current student or user distribution.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="summary-chip-group justify-content-md-end">
                        <span class="summary-chip" style="background: rgba(255,255,255,0.18); color: #fff;">
                            <i class="fas fa-home"></i> {{ $houses->count() }} houses
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="houses-report-body">
            @include('houses.partials.module-nav', ['current' => 'list-report'])

            <div class="help-text">
                <div class="help-title">Report Scope</div>
                <div class="help-content">
                    The chart uses each house’s saved color code. Use this view to verify color assignments alongside student and user totals.
                </div>
            </div>

            <div class="house-report-grid">
                <div class="card-shell">
                    <div class="card-body p-4">
                        <div class="house-section-header">
                            <div>
                                <h5 class="house-section-title">House Register</h5>
                                <p class="house-section-subtitle">{{ $school_data->school_name ?? 'School' }}</p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>House</th>
                                        <th>Leadership</th>
                                        <th>Counts</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($houses as $index => $house)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="house-table-name">
                                                    <span class="house-color-swatch house-card-swatch" style="background: {{ $house->color_code }};"></span>
                                                    <div class="house-table-name-copy">
                                                        <div class="fw-semibold">{{ $house->name }}</div>
                                                        <div class="activity-meta-pills">
                                                            <span class="summary-chip house-chip"
                                                                style="--house-color: {{ $house->color_code }}; --house-color-soft: {{ $house->colorWithAlpha(0.14) }};">
                                                                {{ strtoupper($house->color_code) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="small">
                                                <div class="fw-semibold">{{ $house->houseHead->fullName ?? 'Not assigned' }}</div>
                                                <div class="house-table-meta">Assistant: {{ $house->houseAssistant->fullName ?? 'Not assigned' }}</div>
                                            </td>
                                            <td>
                                                <div class="house-count-stack">
                                                    <div class="house-count-item"><strong>{{ $house->students_count }}</strong> students</div>
                                                    <div class="house-count-item"><strong>{{ $house->users_count }}</strong> users</div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4">
                                                <div class="empty-state">
                                                    <div><i class="fas fa-chart-pie empty-state-icon"></i></div>
                                                    <p class="mb-0">No houses are available for analysis.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card-shell house-chart-shell">
                    <div class="card-body p-4">
                        <div class="house-section-header">
                            <div>
                                <h5 class="house-section-title">Student Distribution</h5>
                                <p class="house-section-subtitle">Pie chart keyed to each house color.</p>
                            </div>
                        </div>
                        <div id="housesChart" class="house-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartElement = document.getElementById('housesChart');
            if (!chartElement || typeof echarts === 'undefined') {
                return;
            }

            const chart = echarts.init(chartElement);
            chart.setOption({
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} students ({d}%)'
                },
                legend: {
                    bottom: 0,
                    type: 'scroll'
                },
                series: [{
                    name: 'Students',
                    type: 'pie',
                    radius: ['36%', '68%'],
                    center: ['50%', '42%'],
                    data: @json($chartData),
                    label: {
                        formatter: '{b}\n{c}'
                    },
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 12,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(15, 23, 42, 0.2)'
                        }
                    }
                }]
            });

            window.addEventListener('resize', function() {
                chart.resize();
            });
        });
    </script>
@endsection
