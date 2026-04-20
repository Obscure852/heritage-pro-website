@extends('layouts.master')
@section('title', 'Disposal Summary Report')
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('disposals.index') }}">Back</a>
        @endslot
        @slot('li_2')
            Reports
        @endslot
        @slot('title')
            Disposal Summary Report
        @endslot
    @endcomponent

    @section('css')
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #disposalTable, #disposalTable * {
                visibility: visible;
            }
            #disposalTable {
                position: absolute;
                left: 0;
                top: 0;
            }
            .btn-group, .dropdown-menu {
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

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Total Disposals</p>
                            <h4 class="mb-2">{{ $summary['total_disposals'] }}</h4>
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
                            <p class="text-truncate font-size-14 mb-2">Recent (30 days)</p>
                            <h4 class="mb-2 text-info">{{ $summary['recent_disposals'] }}</h4>
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
                            <p class="text-truncate font-size-14 mb-2">Average Amount</p>
                            <h4 class="mb-2 text-warning">P {{ number_format($summary['average_amount'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Disposal Method Breakdown -->
    <div class="row mb-4">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Disposal Methods (Count)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-soft-success text-success rounded">
                                        <i class="bx bx-money"></i>
                                    </span>
                                </div>
                                <div>
                                    <p class="mb-1">Sold</p>
                                    <h5 class="mb-0">{{ $summary['disposals_by_method']['Sold'] }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-soft-primary text-primary rounded">
                                        <i class="bx bx-heart"></i>
                                    </span>
                                </div>
                                <div>
                                    <p class="mb-1">Donated</p>
                                    <h5 class="mb-0">{{ $summary['disposals_by_method']['Donated'] }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-soft-danger text-danger rounded">
                                        <i class="bx bx-trash"></i>
                                    </span>
                                </div>
                                <div>
                                    <p class="mb-1">Scrapped</p>
                                    <h5 class="mb-0">{{ $summary['disposals_by_method']['Scrapped'] }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-soft-warning text-warning rounded">
                                        <i class="bx bx-recycle"></i>
                                    </span>
                                </div>
                                <div>
                                    <p class="mb-1">Recycled</p>
                                    <h5 class="mb-0">{{ $summary['disposals_by_method']['Recycled'] }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Financial Impact by Method</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-soft-success text-success rounded">
                                        <i class="bx bx-money"></i>
                                    </span>
                                </div>
                                <div>
                                    <p class="mb-1">Sold</p>
                                    <h6 class="mb-0">P {{ number_format($summary['amounts_by_method']['Sold'], 2) }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-soft-primary text-primary rounded">
                                        <i class="bx bx-heart"></i>
                                    </span>
                                </div>
                                <div>
                                    <p class="mb-1">Donated</p>
                                    <h6 class="mb-0">P {{ number_format($summary['amounts_by_method']['Donated'], 2) }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-soft-danger text-danger rounded">
                                        <i class="bx bx-trash"></i>
                                    </span>
                                </div>
                                <div>
                                    <p class="mb-1">Scrapped</p>
                                    <h6 class="mb-0">P {{ number_format($summary['amounts_by_method']['Scrapped'], 2) }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-soft-warning text-warning rounded">
                                        <i class="bx bx-recycle"></i>
                                    </span>
                                </div>
                                <div>
                                    <p class="mb-1">Recycled</p>
                                    <h6 class="mb-0">P {{ number_format($summary['amounts_by_method']['Recycled'], 2) }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Disposal Records Table -->
    <div class="card shadow mb-4" id="disposalTable">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Disposal Records</h6>
            <span class="badge bg-primary">{{ $disposals->count() }} Records</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover align-middle" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Disposal Date</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Recipient</th>
                            <th>Reason</th>
                            <th>Authorized By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($disposals as $disposal)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($disposal->asset && $disposal->asset->image_path)
                                            <img src="{{ asset('storage/' . $disposal->asset->image_path) }}" 
                                                 alt="" class="rounded me-2" height="32" width="32">
                                        @else
                                            <div class="avatar-xs me-2">
                                                <span class="avatar-title bg-soft-primary text-primary rounded">
                                                    <i class="bx bx-package"></i>
                                                </span>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-weight-bold">
                                                @if($disposal->asset)
                                                    <a href="{{ route('assets.show', $disposal->asset_id) }}" class="text-decoration-none">
                                                        {{ $disposal->asset->name }}
                                                    </a>
                                                @else
                                                    Asset Deleted
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $disposal->asset->asset_code ?? 'N/A' }}</small><br>
                                            @if($disposal->asset && $disposal->asset->category)
                                                <small class="badge bg-info">{{ $disposal->asset->category->name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $disposal->disposal_date ? $disposal->disposal_date->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    @if($disposal->disposal_method == 'Sold')
                                        <span class="badge bg-success">Sold</span>
                                    @elseif($disposal->disposal_method == 'Donated')
                                        <span class="badge bg-primary">Donated</span>
                                    @elseif($disposal->disposal_method == 'Scrapped')
                                        <span class="badge bg-danger">Scrapped</span>
                                    @elseif($disposal->disposal_method == 'Recycled')
                                        <span class="badge bg-warning">Recycled</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $disposal->disposal_method }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($disposal->disposal_amount)
                                        P {{ number_format($disposal->disposal_amount, 2) }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $disposal->recipient ?? 'N/A' }}</td>
                                <td>{{ Str::limit($disposal->reason ?? 'No reason provided', 40) }}</td>
                                <td>{{ $disposal->authorizedByUser->full_name ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-trash fa-2x text-muted mb-3"></i>
                                        <h5>No Disposal Records Found</h5>
                                        <p class="text-muted">No asset disposals have been recorded yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        function printReport() {
            window.print();
        }
    </script>
@endsection