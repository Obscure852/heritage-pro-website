@extends('layouts.master')
@section('title', 'Disposal By Date Report')
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('disposals.index') }}">Back</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('disposals.index') }}">Disposal Reports</a>
        @endslot
        @slot('title')
            By Date Report
        @endslot
    @endcomponent

    @section('css')
        <style>
            @media print {
                body * {
                    visibility: hidden;
                }
                .card, .card * {
                    visibility: visible;
                }
                .btn-group, .dropdown-menu, .collapse, .btn {
                    display: none;
                }
            }
        </style>
    @endsection

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

    <!-- Header Section -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="d-flex justify-content-end align-items-center">
                <div class="d-flex gap-2">
                    <a href="#" onclick="printReport()" class="text-muted">
                        <i class="bx bx-printer me-1 font-size-18"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card border shadow-none mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Date Range Filter</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('disposals.index') }}">
                <input type="hidden" name="report" value="by-date">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="date_from">Date From</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" 
                               value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="date_to">Date To</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" 
                               value="{{ request('date_to', $dateTo->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm me-2">
                            <i class="bx bx-search"></i> Filter
                        </button>
                        <a href="{{ route('disposals.index', ['report' => 'by-date']) }}" class="btn btn-secondary btn-sm">
                            <i class="bx bx-reset"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Total Disposals</p>
                            <h4 class="mb-2">{{ $summary['total_disposals'] }}</h4>
                            <small class="text-muted">{{ $dateFrom->format('M d') }} - {{ $dateTo->format('M d, Y') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Total Amount</p>
                            <h4 class="mb-2 text-success">P {{ number_format($summary['total_amount'], 2) }}</h4>
                            <small class="text-muted">All disposal methods</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Peak Month</p>
                            <h5 class="mb-2 text-info">{{ $summary['peak_month'] ?: 'N/A' }}</h5>
                            <small class="text-muted">{{ $summary['peak_count'] }} disposals</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Monthly Average</p>
                            <h4 class="mb-2 text-warning">{{ number_format($summary['average_per_month'], 1) }}</h4>
                            <small class="text-muted">Disposals per month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Method Totals Summary -->
    <div class="row  mb-4">
        <div class="col-12">
            <div class="card border shadow-none">
                <div class="card-header">
                    <h5 class="card-title mb-0">Disposal Methods Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($summary['method_totals'] as $method => $data)
                            <div class="col-md-3 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <span class="avatar-title {{ 
                                            $method == 'Sold' ? 'bg-soft-success text-success' : 
                                            ($method == 'Donated' ? 'bg-soft-primary text-primary' : 
                                            ($method == 'Scrapped' ? 'bg-soft-danger text-danger' : 'bg-soft-warning text-warning')) 
                                        }} rounded">
                                            <i class="bx {{ 
                                                $method == 'Sold' ? 'bx-money' : 
                                                ($method == 'Donated' ? 'bx-heart' : 
                                                ($method == 'Scrapped' ? 'bx-trash' : 'bx-recycle')) 
                                            }}"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ $method }}</h6>
                                        <p class="mb-0">{{ $data['count'] }} items</p>
                                        <small class="text-muted">P {{ number_format($data['amount'], 2) }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Breakdown -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Monthly Breakdown</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @forelse($disposalsByMonth as $monthKey => $monthData)
                    @if($monthData['total_count'] > 0)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title mb-1">{{ $monthData['label'] }}</h5>
                                            <p class="text-muted mb-0">{{ $monthData['total_count'] }} disposals</p>
                                        </div>
                                        <span class="badge bg-primary">P {{ number_format($monthData['total_amount'], 0) }}</span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        @foreach($monthData['by_method'] as $method => $methodData)
                                            @if($methodData['count'] > 0)
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <small class="text-muted">
                                                        <span class="badge badge-sm {{ 
                                                            $method == 'Sold' ? 'bg-success' : 
                                                            ($method == 'Donated' ? 'bg-primary' : 
                                                            ($method == 'Scrapped' ? 'bg-danger' : 'bg-warning')) 
                                                        }}">{{ $method }}</span>
                                                    </small>
                                                    <small class="text-muted">{{ $methodData['count'] }} items</small>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="col-12">
                        <div class="text-center py-4">
                            <i class="bx bx-calendar-x fa-2x text-muted mb-3"></i>
                            <h5>No Disposals Found</h5>
                            <p class="text-muted">No disposals found in the selected date range.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Monthly Details (Collapsible) -->
    @foreach($disposalsByMonth as $monthKey => $monthData)
        @if($monthData['total_count'] > 0)
            <div class="collapse" id="month-{{ $monthKey }}">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">{{ $monthData['label'] }} - Detailed Records</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Asset</th>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th>Amount</th>
                                        <th>Recipient</th>
                                        <th>Authorized By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monthData['disposals'] as $disposal)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($disposal->asset && $disposal->asset->image_path)
                                                        <img src="{{ asset('storage/' . $disposal->asset->image_path) }}" 
                                                             alt="" class="rounded me-2" height="24" width="24">
                                                    @else
                                                        <div class="avatar-xs me-2">
                                                            <span class="avatar-title bg-soft-primary text-primary rounded">
                                                                <i class="bx bx-package" style="font-size: 10px;"></i>
                                                            </span>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="font-weight-bold" style="font-size: 12px;">
                                                            @if($disposal->asset)
                                                                <a href="{{ route('assets.show', $disposal->asset_id) }}" class="text-decoration-none">
                                                                    {{ Str::limit($disposal->asset->name, 20) }}
                                                                </a>
                                                            @else
                                                                Asset Deleted
                                                            @endif
                                                        </div>
                                                        <small class="text-muted">{{ $disposal->asset->asset_code ?? 'N/A' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $disposal->disposal_date->format('M d') }}</td>
                                            <td>
                                                <span class="badge badge-sm {{ 
                                                    $disposal->disposal_method == 'Sold' ? 'bg-success' : 
                                                    ($disposal->disposal_method == 'Donated' ? 'bg-primary' : 
                                                    ($disposal->disposal_method == 'Scrapped' ? 'bg-danger' : 'bg-warning')) 
                                                }}">{{ $disposal->disposal_method }}</span>
                                            </td>
                                            <td>
                                                @if($disposal->disposal_amount)
                                                    P {{ number_format($disposal->disposal_amount, 2) }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ Str::limit($disposal->recipient ?? 'N/A', 15) }}</td>
                                            <td>{{ $disposal->authorizedByUser->name ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection

@section('script')
    <script>
        function printReport() {
            window.print();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                input.addEventListener('change', function() {
                    setTimeout(() => {
                        this.closest('form').submit();
                    }, 500);
                });
            });
        });
    </script>
@endsection