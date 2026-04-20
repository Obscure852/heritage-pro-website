@extends('layouts.master')
@section('title', 'Audit Comparison Report')
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('audits.index') }}">Back</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('audits.index') }}">Audits</a>
        @endslot
        @slot('title')
            Comparison Report
        @endslot
    @endcomponent

    @if(session('message'))
        <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <!-- Comparison Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('audits.comparison-report') }}" id="comparisonForm">
                        <div class="row">
                            <div class="col-md-8">
                                <label class="form-label">Select Audits to Compare (2-5 audits)</label>
                                <select class="form-select" name="audit_ids[]" multiple size="5" required>
                                    @foreach($allAudits as $audit)
                                        <option value="{{ $audit->id }}" 
                                            {{ in_array($audit->id, $audits->pluck('id')->toArray()) ? 'selected' : '' }}>
                                            {{ $audit->audit_code }} - {{ $audit->audit_date->format('M d, Y') }} 
                                            ({{ $audit->auditItems->count() }} items)
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple audits</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Comparison Focus</label>
                                <select class="form-select" name="focus">
                                    <option value="overall" {{ $comparisonFocus === 'overall' ? 'selected' : '' }}>
                                        Overall Comparison
                                    </option>
                                    <option value="categories" {{ $comparisonFocus === 'categories' ? 'selected' : '' }}>
                                        By Categories
                                    </option>
                                    <option value="locations" {{ $comparisonFocus === 'locations' ? 'selected' : '' }}>
                                        By Locations
                                    </option>
                                    <option value="conditions" {{ $comparisonFocus === 'conditions' ? 'selected' : '' }}>
                                        Asset Conditions
                                    </option>
                                    <option value="trends" {{ $comparisonFocus === 'trends' ? 'selected' : '' }}>
                                        Trend Analysis
                                    </option>
                                </select>
                                <button type="submit" class="btn btn-primary w-100 mt-3">
                                    <i class="bx bx-refresh me-1"></i>Update Comparison
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        Comparison Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($audits as $audit)
                            <div class="col-md-{{ $audits->count() <= 2 ? '6' : ($audits->count() <= 3 ? '4' : '3') }}">
                                <div class="card shadow-none border">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar-sm flex-shrink-0 me-3">
                                                <span class="avatar-title bg-primary text-white rounded-circle">
                                                    {{ $loop->iteration }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">{{ $audit->audit_code }}</h6>
                                                <small class="text-muted">{{ $audit->audit_date->format('M d, Y') }}</small>
                                            </div>
                                        </div>
                                        
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="border-end">
                                                    <h5 class="mb-1 text-success">{{ $audit->auditItems->where('is_present', true)->count() }}</h5>
                                                    <small class="text-muted">Present</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <h5 class="mb-1 text-danger">{{ $audit->auditItems->where('is_present', false)->count() }}</h5>
                                                <small class="text-muted">Missing</small>
                                            </div>
                                        </div>

                                        <div class="row text-center mt-3 pt-3 border-top">
                                            <div class="col-6">
                                                <div class="border-end">
                                                    <h6 class="mb-1 text-warning">{{ $audit->auditItems->where('needs_maintenance', true)->count() }}</h6>
                                                    <small class="text-muted">Need Maintenance</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <h6 class="mb-1 text-info">{{ number_format(($audit->auditItems->where('is_present', true)->count() / $audit->auditItems->count()) * 100, 1) }}%</h6>
                                                <small class="text-muted">Accuracy</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Comparison Based on Focus -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        Detailed Comparison - {{ ucfirst(str_replace('_', ' ', $comparisonFocus)) }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($comparisonFocus === 'overall')
                        <!-- Overall Comparison Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Metric</th>
                                        @foreach($audits as $audit)
                                            <th class="text-center">{{ $audit->audit_code }}</th>
                                        @endforeach
                                        <th class="text-center">Trend</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Total Assets Audited</strong></td>
                                        @foreach($audits as $audit)
                                            <td class="text-center">{{ $audit->auditItems->count() }}</td>
                                        @endforeach
                                        <td class="text-center">
                                            @php
                                                $first = $audits->last()->auditItems->count();
                                                $last = $audits->first()->auditItems->count();
                                                $trend = $last - $first;
                                            @endphp
                                            @if($trend > 0)
                                                <span class="badge bg-success">+{{ $trend }}</span>
                                            @elseif($trend < 0)
                                                <span class="badge bg-danger">{{ $trend }}</span>
                                            @else
                                                <span class="badge bg-secondary">No Change</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Assets Present</strong></td>
                                        @foreach($audits as $audit)
                                            <td class="text-center text-success">
                                                {{ $audit->auditItems->where('is_present', true)->count() }}
                                                ({{ number_format(($audit->auditItems->where('is_present', true)->count() / $audit->auditItems->count()) * 100, 1) }}%)
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            @php
                                                $firstPresent = $audits->last()->auditItems->where('is_present', true)->count();
                                                $lastPresent = $audits->first()->auditItems->where('is_present', true)->count();
                                                $presentTrend = $lastPresent - $firstPresent;
                                            @endphp
                                            @if($presentTrend > 0)
                                                <span class="badge bg-success">+{{ $presentTrend }}</span>
                                            @elseif($presentTrend < 0)
                                                <span class="badge bg-danger">{{ $presentTrend }}</span>
                                            @else
                                                <span class="badge bg-secondary">No Change</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Missing Assets</strong></td>
                                        @foreach($audits as $audit)
                                            <td class="text-center text-danger">
                                                {{ $audit->auditItems->where('is_present', false)->count() }}
                                                ({{ number_format(($audit->auditItems->where('is_present', false)->count() / $audit->auditItems->count()) * 100, 1) }}%)
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            @php
                                                $firstMissing = $audits->last()->auditItems->where('is_present', false)->count();
                                                $lastMissing = $audits->first()->auditItems->where('is_present', false)->count();
                                                $missingTrend = $lastMissing - $firstMissing;
                                            @endphp
                                            @if($missingTrend > 0)
                                                <span class="badge bg-danger">+{{ $missingTrend }}</span>
                                            @elseif($missingTrend < 0)
                                                <span class="badge bg-success">{{ $missingTrend }}</span>
                                            @else
                                                <span class="badge bg-secondary">No Change</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Need Maintenance</strong></td>
                                        @foreach($audits as $audit)
                                            <td class="text-center text-warning">
                                                {{ $audit->auditItems->where('needs_maintenance', true)->count() }}
                                                ({{ number_format(($audit->auditItems->where('needs_maintenance', true)->count() / $audit->auditItems->count()) * 100, 1) }}%)
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            @php
                                                $firstMaintenance = $audits->last()->auditItems->where('needs_maintenance', true)->count();
                                                $lastMaintenance = $audits->first()->auditItems->where('needs_maintenance', true)->count();
                                                $maintenanceTrend = $lastMaintenance - $firstMaintenance;
                                            @endphp
                                            @if($maintenanceTrend > 0)
                                                <span class="badge bg-warning">+{{ $maintenanceTrend }}</span>
                                            @elseif($maintenanceTrend < 0)
                                                <span class="badge bg-success">{{ $maintenanceTrend }}</span>
                                            @else
                                                <span class="badge bg-secondary">No Change</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    @elseif($comparisonFocus === 'categories')
                        @php
                            $allCategories = collect();
                            foreach($audits as $audit) {
                                $categories = $audit->auditItems->pluck('asset.category.name')->filter()->unique();
                                $allCategories = $allCategories->merge($categories);
                            }
                            $allCategories = $allCategories->unique()->sort();
                        @endphp
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Category</th>
                                        @foreach($audits as $audit)
                                            <th class="text-center" colspan="3">
                                                {{ $audit->audit_code }}
                                                <br><small class="text-muted">Total | Present | Missing</small>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allCategories as $categoryName)
                                        <tr>
                                            <td><strong>{{ $categoryName ?: 'Uncategorized' }}</strong></td>
                                            @foreach($audits as $audit)
                                                @php
                                                    $categoryItems = $audit->auditItems->filter(function($item) use ($categoryName) {
                                                        $itemCategoryName = $item->asset->category->name ?? null;
                                                        return $itemCategoryName === $categoryName;
                                                    });
                                                    $total = $categoryItems->count();
                                                    $present = $categoryItems->where('is_present', true)->count();
                                                    $missing = $categoryItems->where('is_present', false)->count();
                                                @endphp
                                                <td class="text-center">{{ $total }}</td>
                                                <td class="text-center text-success">{{ $present }}</td>
                                                <td class="text-center text-danger">{{ $missing }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    @elseif($comparisonFocus === 'locations')
                        @php
                            $allLocations = collect();
                            foreach($audits as $audit) {
                                $locations = $audit->auditItems->pluck('asset.venue.name')->filter()->unique();
                                $allLocations = $allLocations->merge($locations);
                            }
                            $allLocations = $allLocations->unique()->sort();
                        @endphp
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Location</th>
                                        @foreach($audits as $audit)
                                            <th class="text-center" colspan="3">
                                                {{ $audit->audit_code }}
                                                <br><small class="text-muted">Total | Present | Missing</small>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allLocations as $locationName)
                                        <tr>
                                            <td><strong>{{ $locationName ?: 'Unassigned' }}</strong></td>
                                            @foreach($audits as $audit)
                                                @php
                                                    $locationItems = $audit->auditItems->filter(function($item) use ($locationName) {
                                                        $itemLocationName = $item->asset->venue->name ?? null;
                                                        return $itemLocationName === $locationName;
                                                    });
                                                    $total = $locationItems->count();
                                                    $present = $locationItems->where('is_present', true)->count();
                                                    $missing = $locationItems->where('is_present', false)->count();
                                                @endphp
                                                <td class="text-center">{{ $total }}</td>
                                                <td class="text-center text-success">{{ $present }}</td>
                                                <td class="text-center text-danger">{{ $missing }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    @elseif($comparisonFocus === 'conditions')
                        @php
                            $conditions = ['New', 'Good', 'Fair', 'Poor'];
                        @endphp
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Condition</th>
                                        @foreach($audits as $audit)
                                            <th class="text-center">{{ $audit->audit_code }}</th>
                                        @endforeach
                                        <th class="text-center">Trend</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($conditions as $condition)
                                        <tr>
                                            <td><strong>{{ $condition }}</strong></td>
                                            @foreach($audits as $audit)
                                                @php
                                                    $conditionCount = $audit->auditItems->filter(function($item) use ($condition) {
                                                        return $item->condition === $condition;
                                                    })->count();
                                                @endphp
                                                <td class="text-center">
                                                    {{ $conditionCount }}
                                                    @if($audit->auditItems->count() > 0)
                                                        ({{ number_format(($conditionCount / $audit->auditItems->count()) * 100, 1) }}%)
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="text-center">
                                                @php
                                                    $firstCount = $audits->last()->auditItems->filter(function($item) use ($condition) {
                                                        return $item->condition === $condition;
                                                    })->count();
                                                    $lastCount = $audits->first()->auditItems->filter(function($item) use ($condition) {
                                                        return $item->condition === $condition;
                                                    })->count();
                                                    $conditionTrend = $lastCount - $firstCount;
                                                @endphp
                                                @if($conditionTrend > 0)
                                                    <span class="badge bg-{{ $condition === 'Poor' ? 'danger' : 'success' }}">+{{ $conditionTrend }}</span>
                                                @elseif($conditionTrend < 0)
                                                    <span class="badge bg-{{ $condition === 'Poor' ? 'success' : 'warning' }}">{{ $conditionTrend }}</span>
                                                @else
                                                    <span class="badge bg-secondary">No Change</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    @elseif($comparisonFocus === 'trends')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card shadow-none border">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0">Asset Presence Trend</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Audit Date</th>
                                                        <th>Present</th>
                                                        <th>Missing</th>
                                                        <th>Accuracy</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($audits->reverse() as $audit)
                                                        <tr>
                                                            <td>{{ $audit->audit_date->format('M d, Y') }}</td>
                                                            <td class="text-success">{{ $audit->auditItems->where('is_present', true)->count() }}</td>
                                                            <td class="text-danger">{{ $audit->auditItems->where('is_present', false)->count() }}</td>
                                                            <td>
                                                                @php
                                                                    $accuracy = ($audit->auditItems->where('is_present', true)->count() / $audit->auditItems->count()) * 100;
                                                                @endphp
                                                                <span class="badge bg-{{ $accuracy >= 95 ? 'success' : ($accuracy >= 85 ? 'warning' : 'danger') }}">
                                                                    {{ number_format($accuracy, 1) }}%
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card shadow-none border">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0">Maintenance Needs Trend</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Audit Date</th>
                                                        <th>Need Maintenance</th>
                                                        <th>Percentage</th>
                                                        <th>Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($audits->reverse() as $audit)
                                                        <tr>
                                                            <td>{{ $audit->audit_date->format('M d, Y') }}</td>
                                                            <td class="text-warning">{{ $audit->auditItems->where('needs_maintenance', true)->count() }}</td>
                                                            <td>{{ number_format(($audit->auditItems->where('needs_maintenance', true)->count() / $audit->auditItems->count()) * 100, 1) }}%</td>
                                                            <td>
                                                                @if(!$loop->first)
                                                                    @php
                                                                        $current = $audit->auditItems->where('needs_maintenance', true)->count();
                                                                        $previous = $audits->reverse()->skip($loop->index - 1)->first()->auditItems->where('needs_maintenance', true)->count();
                                                                        $change = $current - $previous;
                                                                    @endphp
                                                                    @if($change > 0)
                                                                        <span class="badge bg-danger">+{{ $change }}</span>
                                                                    @elseif($change < 0)
                                                                        <span class="badge bg-success">{{ $change }}</span>
                                                                    @else
                                                                        <span class="badge bg-secondary">0</span>
                                                                    @endif
                                                                @else
                                                                    <span class="text-muted">-</span>
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
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Key Insights -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        Key Insights & Recommendations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="avatar-sm flex-shrink-0 me-3">
                                    <span class="avatar-title bg-success text-white rounded-circle">
                                        <i class="bx bx-trending-up"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6>Improvements</h6>
                                    @php
                                        $latestAccuracy = ($audits->first()->auditItems->where('is_present', true)->count() / $audits->first()->auditItems->count()) * 100;
                                        $oldestAccuracy = ($audits->last()->auditItems->where('is_present', true)->count() / $audits->last()->auditItems->count()) * 100;
                                        $accuracyImprovement = $latestAccuracy - $oldestAccuracy;
                                    @endphp
                                    <p class="text-muted mb-0">
                                        Asset tracking accuracy has 
                                        @if($accuracyImprovement > 0)
                                            <span class="text-success">improved by {{ number_format($accuracyImprovement, 1) }}%</span>
                                        @elseif($accuracyImprovement < 0)
                                            <span class="text-danger">decreased by {{ number_format(abs($accuracyImprovement), 1) }}%</span>
                                        @else
                                            <span class="text-muted">remained stable</span>
                                        @endif
                                        since the oldest audit in this comparison.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="avatar-sm flex-shrink-0 me-3">
                                    <span class="avatar-title bg-warning text-white rounded-circle">
                                        <i class="bx bx-error-alt"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6>Areas of Concern</h6>
                                    @php
                                        $latestMissing = $audits->first()->auditItems->where('is_present', false)->count();
                                        $latestMaintenance = $audits->first()->auditItems->where('needs_maintenance', true)->count();
                                    @endphp
                                    <p class="text-muted mb-0">
                                        Currently {{ $latestMissing }} assets are missing and 
                                        {{ $latestMaintenance }} assets need maintenance attention.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="avatar-sm flex-shrink-0 me-3">
                                    <span class="avatar-title bg-info text-white rounded-circle">
                                        <i class="bx bx-check-shield"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6>Recommendations</h6>
                                    <p class="text-muted mb-0">
                                        @if($latestAccuracy >= 95)
                                            Maintain current audit procedures. Consider extending audit intervals.
                                        @elseif($latestAccuracy >= 85)
                                            Focus on improving asset tracking in problem areas identified above.
                                        @else
                                            Immediate action needed to improve asset management processes.
                                        @endif
                                    </p>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add form validation
        const comparisonForm = document.getElementById('comparisonForm');
        const auditSelect = document.querySelector('select[name="audit_ids[]"]');
        
        comparisonForm.addEventListener('submit', function(e) {
            const selectedOptions = Array.from(auditSelect.selectedOptions);
            
            if (selectedOptions.length < 2) {
                e.preventDefault();
                alert('Please select at least 2 audits to compare.');
                return false;
            }
            
            if (selectedOptions.length > 5) {
                e.preventDefault();
                alert('Please select no more than 5 audits to compare.');
                return false;
            }
        });
        
        document.querySelector('select[name="focus"]').addEventListener('change', function() {
            const selectedAudits = Array.from(auditSelect.selectedOptions);
            if (selectedAudits.length >= 2 && selectedAudits.length <= 5) {
                comparisonForm.submit();
            }
        });
        
        window.addEventListener('beforeprint', function() {
            document.title = 'Audit Comparison Report - {{ $school_data["school_name"] ?? "School" }}';
        });
    });
</script>

<style>
    @media print {
        .btn, .dropdown, .breadcrumb, .alert {
            display: none !important;
        }
        
        .card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
        }
        
        .table {
            font-size: 0.8rem;
        }
        
        .badge {
            border: 1px solid #000;
        }
    }
</style>
@endsection