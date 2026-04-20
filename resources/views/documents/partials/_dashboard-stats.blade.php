{{-- DSH-05: Admin-Only System Statistics Widget --}}
<div class="widget-header">
    <h6><i class="fas fa-chart-bar" style="color: #6b7280; margin-right: 8px;"></i>System Statistics</h6>
</div>
<div class="widget-body">
    <div class="stat-cards">
        <div class="stat-card">
            <div><i class="fas fa-file" style="color: #3b82f6; font-size: 18px; margin-bottom: 8px;"></i></div>
            <div class="stat-value">{{ number_format($stats['total_documents']) }}</div>
            <div class="stat-label">Total Documents</div>
        </div>
        <div class="stat-card">
            <div><i class="fas fa-database" style="color: #8b5cf6; font-size: 18px; margin-bottom: 8px;"></i></div>
            <div class="stat-value">{{ number_format($stats['total_storage'] / (1024*1024*1024), 2) }} GB</div>
            <div class="stat-label">Total Storage</div>
        </div>
        <div class="stat-card">
            <div><i class="fas fa-users" style="color: #10b981; font-size: 18px; margin-bottom: 8px;"></i></div>
            <div class="stat-value">{{ number_format($stats['active_users']) }}</div>
            <div class="stat-label">Active Users</div>
        </div>
    </div>

    @if(count($stats['status_counts']) > 0)
        <div style="border-top: 1px solid #f3f4f6; padding-top: 16px;">
            <h6 style="font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 12px;">Documents by Status</h6>
            <div id="statusChart"></div>
        </div>
    @endif
</div>

