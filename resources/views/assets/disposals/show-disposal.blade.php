@extends('layouts.master')
@section('title')
    Disposal Details
@endsection

@section('css')
    <style>
        .disposal-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .disposal-header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .disposal-header h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .disposal-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .disposal-body {
            padding: 24px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .info-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 24px;
        }

        .info-card-header {
            background: #f3f4f6;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 15px;
        }

        .info-card-body {
            padding: 20px;
        }

        .asset-preview {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .asset-preview img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 3px;
        }

        .asset-preview .asset-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .asset-preview .asset-icon i {
            font-size: 28px;
            color: #3b82f6;
        }

        .asset-preview .asset-info h5 {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: 600;
            color: #374151;
        }

        .asset-preview .asset-info p {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
        }

        .info-table {
            width: 100%;
        }

        .info-table tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .info-table tr:last-child {
            border-bottom: none;
        }

        .info-table th {
            width: 40%;
            padding: 10px 0;
            font-weight: 500;
            color: #6b7280;
            font-size: 14px;
            vertical-align: top;
        }

        .info-table td {
            padding: 10px 0;
            font-size: 14px;
            color: #374151;
        }

        .method-badge {
            padding: 6px 14px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 600;
        }

        .method-badge.sold { background: #d1fae5; color: #065f46; }
        .method-badge.scrapped { background: #fee2e2; color: #991b1b; }
        .method-badge.donated { background: #dbeafe; color: #1e40af; }
        .method-badge.recycled { background: #fef3c7; color: #b45309; }

        .status-badge {
            background: #fee2e2;
            color: #991b1b;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
        }

        .category-badge {
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        @media (max-width: 768px) {
            .disposal-header {
                padding: 20px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.index') }}">Asset Management</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('assets.disposals.index') }}">Disposals</a>
        @endslot
        @slot('title')
            Disposal Details
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="disposal-container">
        <div class="disposal-header">
            <h4><i class="bx bx-detail me-2"></i>Disposal Details</h4>
            <p>Recorded on {{ $disposal->created_at->format('M d, Y H:i') }}</p>
        </div>

        <div class="disposal-body">
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('assets.disposals.edit', $disposal->id) }}" class="btn btn-info">
                    <i class="bx bx-edit me-1"></i> Edit Disposal
                </a>
                <a href="{{ route('assets.show', $disposal->asset_id) }}" class="btn btn-primary">
                    <i class="bx bx-package me-1"></i> View Asset
                </a>
                <button type="button" class="btn btn-danger"
                    onclick="if(confirm('Are you sure you want to cancel this disposal? This will revert the asset to Available status.')) document.getElementById('delete-form').submit();">
                    <i class="bx bx-trash me-1"></i> Cancel Disposal
                </button>
                <form id="delete-form" action="{{ route('assets.disposals.destroy', $disposal->id) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

            <div class="row">
                <!-- Asset Information -->
                <div class="col-md-6">
                    <div class="info-card">
                        <div class="info-card-header">
                            <i class="bx bx-package me-2"></i>Asset Information
                        </div>
                        <div class="info-card-body">
                            <div class="asset-preview">
                                @if($disposal->asset && $disposal->asset->image_path)
                                    <img src="{{ asset('storage/' . $disposal->asset->image_path) }}"
                                        alt="{{ $disposal->asset->name }}">
                                @else
                                    <div class="asset-icon">
                                        <i class="bx bx-package"></i>
                                    </div>
                                @endif
                                <div class="asset-info">
                                    <h5>{{ $disposal->asset->name ?? 'Unknown Asset' }}</h5>
                                    <p>{{ $disposal->asset->asset_code ?? 'N/A' }}</p>
                                    <span class="category-badge">
                                        {{ $disposal->asset->category->name ?? 'Uncategorized' }}
                                    </span>
                                </div>
                            </div>

                            <table class="info-table">
                                <tr>
                                    <th>Original Purchase Date</th>
                                    <td>{{ $disposal->asset->purchase_date ? $disposal->asset->purchase_date->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Original Purchase Price</th>
                                    <td>{{ $disposal->asset->purchase_price ? '$'.number_format($disposal->asset->purchase_price, 2) : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Business Contact</th>
                                    <td>
                                        @if($disposal->asset->vendor)
                                            <div>{{ $disposal->asset->vendor->name }}</div>
                                            <small class="text-muted">{{ $disposal->asset->vendor->primary_person_label ?? 'No primary person' }}</small>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Age at Disposal</th>
                                    <td>
                                        @if($disposal->asset->purchase_date)
                                            {{ $disposal->asset->purchase_date->diffInMonths($disposal->disposal_date) }} months
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Current Status</th>
                                    <td><span class="status-badge">Disposed</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Disposal Information -->
                <div class="col-md-6">
                    <div class="info-card">
                        <div class="info-card-header">
                            <i class="bx bx-trash me-2"></i>Disposal Information
                        </div>
                        <div class="info-card-body">
                            <div class="d-flex align-items-center gap-3 mb-4 pb-3" style="border-bottom: 1px solid #e5e7eb;">
                                <span class="method-badge
                                    @if($disposal->disposal_method == 'Sold') sold
                                    @elseif($disposal->disposal_method == 'Scrapped') scrapped
                                    @elseif($disposal->disposal_method == 'Donated') donated
                                    @elseif($disposal->disposal_method == 'Recycled') recycled
                                    @endif">
                                    {{ $disposal->disposal_method }}
                                </span>
                                <span class="text-muted">{{ $disposal->disposal_date->format('M d, Y') }}</span>
                            </div>

                            <table class="info-table">
                                @if($disposal->disposal_method == 'Sold')
                                <tr>
                                    <th>Sale Amount</th>
                                    <td><strong>${{ number_format($disposal->disposal_amount, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Depreciation</th>
                                    <td>
                                        @if($disposal->asset && $disposal->asset->purchase_price)
                                            ${{ number_format($disposal->asset->purchase_price - $disposal->disposal_amount, 2) }}
                                            ({{ $disposal->asset->purchase_price > 0 ?
                                                round((1 - $disposal->disposal_amount / $disposal->asset->purchase_price) * 100) : 0 }}%)
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                @endif

                                @if($disposal->disposal_method == 'Donated')
                                <tr>
                                    <th>Recipient</th>
                                    <td>{{ $disposal->recipient ?? 'N/A' }}</td>
                                </tr>
                                @endif

                                <tr>
                                    <th>Reason for Disposal</th>
                                    <td>{{ $disposal->reason }}</td>
                                </tr>

                                @if($disposal->notes)
                                <tr>
                                    <th>Additional Notes</th>
                                    <td>{{ $disposal->notes }}</td>
                                </tr>
                                @endif

                                <tr>
                                    <th>Authorized By</th>
                                    <td>{{ $disposal->authorizedByUser->name ?? 'Unknown' }}</td>
                                </tr>

                                <tr>
                                    <th>Disposal Record Created</th>
                                    <td>{{ $disposal->created_at->format('M d, Y H:i') }}</td>
                                </tr>

                                @if($disposal->created_at != $disposal->updated_at)
                                <tr>
                                    <th>Last Updated</th>
                                    <td>{{ $disposal->updated_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
