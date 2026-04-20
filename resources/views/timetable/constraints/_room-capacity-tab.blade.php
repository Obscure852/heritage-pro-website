{{-- Room Capacity Tab Content (CONST-04) --}}
<style>
    .venue-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 12px;
        margin-bottom: 8px;
    }

    .venue-type-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 16px;
        position: relative;
        overflow: hidden;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .venue-type-card:hover {
        border-color: #c7d2fe;
        box-shadow: 0 2px 8px rgba(78, 115, 223, 0.08);
    }

    .venue-type-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        opacity: 0.6;
    }

    .venue-type-card .venue-type-name {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .venue-type-card .venue-type-name i {
        color: #4e73df;
        font-size: 12px;
    }

    .venue-type-card .venue-metrics {
        display: flex;
        align-items: baseline;
        gap: 16px;
    }

    .venue-type-card .venue-metric {
        display: flex;
        flex-direction: column;
    }

    .venue-type-card .venue-metric .metric-value {
        font-size: 22px;
        font-weight: 700;
        color: #1f2937;
        line-height: 1;
    }

    .venue-type-card .venue-metric .metric-label {
        font-size: 11px;
        color: #9ca3af;
        font-weight: 500;
        margin-top: 2px;
    }

    .venue-type-card .venue-metric .metric-divider {
        width: 1px;
        height: 28px;
        background: #e5e7eb;
        align-self: center;
    }

    .venue-totals-bar {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 12px 16px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
    }

    .venue-totals-bar .total-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #374151;
    }

    .venue-totals-bar .total-item strong {
        font-weight: 700;
        color: #1f2937;
    }

    .venue-totals-bar .total-divider {
        width: 1px;
        height: 20px;
        background: #d1d5db;
    }

    .enforcement-card {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
    }

    .enforcement-option {
        padding: 16px 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        cursor: pointer;
        transition: background 0.15s;
        border-bottom: 1px solid #f3f4f6;
    }

    .enforcement-option:last-child {
        border-bottom: none;
    }

    .enforcement-option:hover {
        background: #f9fafb;
    }

    .enforcement-option.selected {
        background: #eff6ff;
        border-left: 3px solid #4e73df;
        padding-left: 17px;
    }

    .enforcement-option .enforcement-icon {
        width: 36px;
        height: 36px;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .enforcement-option .enforcement-icon.strict-icon {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .enforcement-option .enforcement-icon.warn-icon {
        background: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
    }

    .enforcement-option .enforcement-body {
        flex: 1;
    }

    .enforcement-option .enforcement-body .enforcement-title {
        font-weight: 600;
        color: #1f2937;
        font-size: 14px;
    }

    .enforcement-option .enforcement-body .enforcement-desc {
        font-size: 12px;
        color: #6b7280;
        margin-top: 2px;
        line-height: 1.4;
    }
</style>

<div class="help-text">
    <div class="help-title">Room Capacity</div>
    <div class="help-content">
        When enabled, the system checks that class sizes do not exceed venue capacity. Strict mode blocks scheduling in over-capacity venues. Warn mode allows it but flags a warning.
    </div>
</div>

<div class="settings-section">
    @php
        $capacityConstraints = $constraints->get('room_capacity', collect());
        $capacitySetting = $capacityConstraints->first();
        $isEnabled = $capacitySetting ? ($capacitySetting->constraint_config['enabled'] ?? true) : true;
        $enforcement = $capacitySetting ? ($capacitySetting->constraint_config['enforcement'] ?? 'strict') : 'strict';
        $venuesByType = $venues->groupBy('type');
        $typeIcons = [
            'Classroom' => 'fas fa-chalkboard',
            'Laboratory' => 'fas fa-flask',
            'Lab' => 'fas fa-flask',
            'Hall' => 'fas fa-archway',
            'Library' => 'fas fa-book',
            'Workshop' => 'fas fa-tools',
            'Computer Lab' => 'fas fa-desktop',
            'Sports' => 'fas fa-running',
            'Field' => 'fas fa-running',
        ];
    @endphp

    {{-- Venue Capacity Summary --}}
    <h6 class="section-title"><i class="fas fa-chart-bar me-2"></i>Venue Capacity Summary</h6>

    <div class="venue-summary-grid">
        @foreach($venuesByType as $type => $typeVenues)
            @php
                $typeName = $type ?: 'Unspecified';
                $icon = $typeIcons[$typeName] ?? 'fas fa-door-open';
                $avgCap = $typeVenues->avg('capacity') ? round($typeVenues->avg('capacity')) : '--';
                $minCap = $typeVenues->min('capacity') ?: '--';
                $maxCap = $typeVenues->max('capacity') ?: '--';
            @endphp
            <div class="venue-type-card">
                <div class="venue-type-name">
                    <i class="{{ $icon }}"></i>
                    {{ $typeName }}
                </div>
                <div class="venue-metrics">
                    <div class="venue-metric">
                        <span class="metric-value">{{ $typeVenues->count() }}</span>
                        <span class="metric-label">Venues</span>
                    </div>
                    <div class="metric-divider"></div>
                    <div class="venue-metric">
                        <span class="metric-value">{{ $avgCap }}</span>
                        <span class="metric-label">Avg Capacity</span>
                    </div>
                    @if($minCap !== '--' && $maxCap !== '--' && $minCap !== $maxCap)
                        <div class="metric-divider"></div>
                        <div class="venue-metric">
                            <span class="metric-value" style="font-size: 16px;">{{ $minCap }}–{{ $maxCap }}</span>
                            <span class="metric-label">Range</span>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="venue-totals-bar">
        <div class="total-item">
            <i class="fas fa-building" style="color: #4e73df;"></i>
            <span>Total Venues: <strong>{{ $venues->count() }}</strong></span>
        </div>
        <div class="total-divider"></div>
        <div class="total-item">
            <i class="fas fa-users" style="color: #36b9cc;"></i>
            <span>Avg Capacity: <strong>{{ $venues->avg('capacity') ? round($venues->avg('capacity')) : '--' }}</strong></span>
        </div>
        @if($venues->where('capacity', '>', 0)->count() > 0)
            <div class="total-divider"></div>
            <div class="total-item">
                <i class="fas fa-arrow-up" style="color: #10b981;"></i>
                <span>Largest: <strong>{{ $venues->max('capacity') }}</strong></span>
            </div>
            <div class="total-divider"></div>
            <div class="total-item">
                <i class="fas fa-arrow-down" style="color: #f59e0b;"></i>
                <span>Smallest: <strong>{{ $venues->where('capacity', '>', 0)->min('capacity') }}</strong></span>
            </div>
        @endif
    </div>

    {{-- Capacity Settings (Full Width) --}}
    <h6 class="section-title mt-4"><i class="fas fa-cog me-2"></i>Capacity Settings</h6>

    <div class="mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="roomCapacityEnabled" @if($isEnabled) checked @endif>
            <label class="form-check-label fw-medium" for="roomCapacityEnabled">Enable room capacity enforcement</label>
        </div>
        <div class="form-hint">When enabled, the system checks class sizes against venue capacity.</div>
    </div>

    {{-- Enforcement Mode --}}
    <div class="mb-3" id="enforcementModeSection">
        <label class="form-label">Enforcement Mode</label>
        <div class="enforcement-card">
            <label class="enforcement-option {{ $enforcement === 'strict' ? 'selected' : '' }}" id="enforcementStrictOption">
                <div class="enforcement-icon strict-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="enforcement-body">
                    <div class="enforcement-title">Strict (Block)</div>
                    <div class="enforcement-desc">Prevents scheduling classes in venues that cannot hold them. Over-capacity assignments are blocked entirely.</div>
                </div>
                <input class="form-check-input" type="radio" name="enforcement" id="enforcementStrict" value="strict" @if($enforcement === 'strict') checked @endif style="margin-top: 4px;">
            </label>
            <label class="enforcement-option {{ $enforcement === 'warn_only' ? 'selected' : '' }}" id="enforcementWarnOption">
                <div class="enforcement-icon warn-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="enforcement-body">
                    <div class="enforcement-title">Warn Only</div>
                    <div class="enforcement-desc">Allows over-capacity scheduling but flags it as a warning. Useful when flexibility is needed but visibility is still important.</div>
                </div>
                <input class="form-check-input" type="radio" name="enforcement" id="enforcementWarn" value="warn_only" @if($enforcement === 'warn_only') checked @endif style="margin-top: 4px;">
            </label>
        </div>
    </div>

    <div class="form-actions" style="border-top: none; margin-top: 16px; padding-top: 0;">
        <button type="button" class="btn btn-primary btn-loading" id="saveRoomCapacityBtn">
            <span class="btn-text"><i class="fas fa-save"></i> Save Capacity Setting</span>
            <span class="btn-spinner d-none">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Saving...
            </span>
        </button>
    </div>
</div>

<script>
    document.querySelectorAll('.enforcement-option').forEach(function(option) {
        option.addEventListener('click', function() {
            document.querySelectorAll('.enforcement-option').forEach(function(o) { o.classList.remove('selected'); });
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });
</script>
