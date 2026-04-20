@extends('layouts.master')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Gradient Header --}}
    <div class="header" style="background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%); color: white; padding: 28px; border-radius: 3px 3px 0 0;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h4 style="margin: 0; font-weight: 600;">Document Dashboard</h4>
                <p style="margin: 4px 0 0; opacity: 0.85; font-size: 14px;">Welcome back, {{ auth()->user()->name }}</p>
            </div>
            <a href="{{ route('documents.create') }}" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4); padding: 10px 20px; border-radius: 3px; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-cloud-upload-alt"></i> Quick Upload
            </a>
        </div>
    </div>

    {{-- Dashboard Grid --}}
    <div class="form-container" style="border-radius: 0 0 3px 3px; padding: 24px;">
        <div class="dashboard-grid">
            <div class="dashboard-card">
                @include('documents.partials._dashboard-quota')
            </div>
            <div class="dashboard-card">
                @include('documents.partials._dashboard-approvals')
            </div>
            <div class="dashboard-card">
                @include('documents.partials._dashboard-recent')
            </div>
            <div class="dashboard-card">
                @include('documents.partials._dashboard-shared')
            </div>
            @if($isAdmin && $stats)
            <div class="dashboard-card dashboard-card-full">
                @include('documents.partials._dashboard-stats')
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    .dashboard-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
    }
    .dashboard-card-full {
        grid-column: 1 / -1;
    }
    .widget-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .widget-header h6 {
        margin: 0;
        font-weight: 600;
        color: #1f2937;
        font-size: 14px;
    }
    .widget-body {
        padding: 16px 20px;
    }
    .widget-empty {
        text-align: center;
        padding: 24px;
        color: #9ca3af;
        font-size: 13px;
    }
    .widget-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f9fafb;
    }
    .widget-item:last-child {
        border-bottom: none;
    }
    .widget-item-title {
        font-size: 13px;
        font-weight: 500;
        color: #374151;
    }
    .widget-item-title a {
        color: #374151;
        text-decoration: none;
    }
    .widget-item-title a:hover {
        color: #3b82f6;
    }
    .widget-item-meta {
        font-size: 12px;
        color: #9ca3af;
    }
    .stat-cards {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
    }
    .stat-card {
        flex: 1;
        background: #f9fafb;
        border-radius: 3px;
        padding: 16px;
        text-align: center;
    }
    .stat-card .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
    }
    .stat-card .stat-label {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }
    @media (max-width: 768px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
        .stat-cards {
            flex-direction: column;
        }
    }
</style>
@endsection

@section('script')
@if(isset($isAdmin) && $isAdmin && isset($stats) && $stats && count($stats['status_counts']) > 0)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var statusLabels = @json(array_keys($stats['status_counts']));
        var statusValues = @json(array_values($stats['status_counts']));

        // Capitalize labels for display
        statusLabels = statusLabels.map(function(label) {
            return label.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
        });

        var statusOptions = {
            chart: { type: 'donut', height: 280 },
            series: statusValues,
            labels: statusLabels,
            colors: ['#6c757d', '#ffc107', '#17a2b8', '#28a745', '#007bff', '#dc3545'],
            legend: { position: 'bottom' },
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total'
                            }
                        }
                    }
                }
            }
        };

        var statusChart = new ApexCharts(document.querySelector("#statusChart"), statusOptions);
        statusChart.render();
    });
</script>
@endif
@endsection
