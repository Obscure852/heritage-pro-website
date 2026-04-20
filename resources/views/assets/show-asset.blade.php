@extends('layouts.master')
@section('title')
    {{ $asset->name ?? 'Asset' }}
@endsection

@section('css')
    <style>
        .assets-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .assets-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .assets-header h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .assets-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .assets-body {
            padding: 24px;
        }

        .asset-header-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .asset-image-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.2);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .asset-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .asset-image-wrapper i {
            font-size: 36px;
            color: rgba(255, 255, 255, 0.8);
        }

        .asset-info {
            flex-grow: 1;
        }

        .asset-info h4 {
            margin-bottom: 4px;
        }

        .asset-code {
            font-size: 14px;
            opacity: 0.9;
            font-family: 'Courier New', monospace;
            background: rgba(255, 255, 255, 0.15);
            padding: 2px 8px;
            border-radius: 3px;
            display: inline-block;
        }

        .asset-badges {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-available {
            background: rgba(209, 250, 229, 0.9);
            color: #065f46;
        }

        .status-assigned {
            background: rgba(191, 219, 254, 0.9);
            color: #1e40af;
        }

        .status-maintenance {
            background: rgba(254, 243, 199, 0.9);
            color: #92400e;
        }

        .status-disposed {
            background: rgba(254, 202, 202, 0.9);
            color: #991b1b;
        }

        .condition-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .category-badge {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .asset-actions {
            display: flex;
            gap: 8px;
        }

        .asset-actions .btn {
            padding: 8px 16px;
            font-size: 13px;
            border-radius: 3px;
        }

        .asset-actions .btn-light {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            color: #374151;
        }

        .asset-actions .btn-light:hover {
            background: white;
        }

        .nav-tabs-custom {
            border-bottom: 2px solid #e5e7eb;
            gap: 0;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: #6b7280;
            padding: 12px 20px;
            font-weight: 500;
            position: relative;
            background: transparent;
            border-radius: 0;
            font-size: 14px;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #4e73df;
            background: #f9fafb;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        }

        .nav-tabs-custom .nav-link .badge {
            font-size: 10px;
            padding: 3px 6px;
        }

        .tab-content {
            padding: 24px 0 0 0;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
        }

        .detail-table th,
        .detail-table td {
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            font-size: 14px;
        }

        .detail-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            width: 35%;
        }

        .detail-table td {
            color: #4b5563;
        }

        .assets-table {
            width: 100%;
            border-collapse: collapse;
        }

        .assets-table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 12px 10px;
            text-align: left;
        }

        .assets-table tbody td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
            color: #4b5563;
        }

        .assets-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .assets-table .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .current-assignment-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .current-assignment-card h5 {
            color: #1e40af;
            margin-bottom: 16px;
            font-size: 16px;
        }

        .assignment-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .assignment-info-item {
            font-size: 14px;
        }

        .assignment-info-item strong {
            color: #374151;
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
        }

        .empty-state-icon {
            width: 64px;
            height: 64px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .empty-state-icon i {
            font-size: 28px;
            color: #9ca3af;
        }

        .empty-state h5 {
            color: #374151;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 16px;
        }

        .attachment-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .attachment-card-header {
            background: #f9fafb;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .attachment-card-header h5 {
            margin: 0;
            font-size: 15px;
            color: #374151;
        }

        .attachment-card-body {
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 200px;
        }

        .attachment-image {
            max-height: 200px;
            border-radius: 3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .attachment-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }

        .document-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }

        .document-icon i {
            font-size: 32px;
            color: white;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }

        /* Modal Theming */
        .modal-content {
            border: none;
            border-radius: 3px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .modal-title {
            font-weight: 600;
            color: #374151;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-body .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .modal-body .form-control,
        .modal-body .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .modal-footer .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            padding: 8px 16px;
            font-weight: 500;
        }

        .modal-footer .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .disposal-info-card {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            border-radius: 3px;
            padding: 20px;
        }

        .disposal-info-card h5 {
            color: #991b1b;
            margin-bottom: 16px;
        }

        @media (max-width: 768px) {
            .assets-header {
                padding: 20px;
            }

            .asset-header-content {
                flex-direction: column;
                text-align: center;
            }

            .asset-actions {
                justify-content: center;
                flex-wrap: wrap;
            }

            .assignment-info-grid {
                grid-template-columns: 1fr;
            }

            .nav-tabs-custom .nav-link {
                padding: 10px 12px;
                font-size: 13px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.index') }}">Back</a>
        @endslot
        @slot('title')
            Asset Details
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

    @if (session('error'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach
    @endif

    <div class="assets-container">
        <div class="assets-header">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div class="asset-header-content">
                    <div class="asset-image-wrapper">
                        @if ($asset->image_path)
                            <img src="{{ URL::asset('storage/' . $asset->image_path) }}" alt="{{ $asset->name }}">
                        @else
                            <i class="bx bx-box"></i>
                        @endif
                    </div>
                    <div class="asset-info">
                        <h4>{{ $asset->name }}</h4>
                        <span class="asset-code">{{ $asset->asset_code }}</span>
                        <div class="asset-badges">
                            @if ($asset->status == 'Available')
                                <span class="status-badge status-available">Available</span>
                            @elseif($asset->status == 'Assigned')
                                <span class="status-badge status-assigned">Assigned</span>
                            @elseif($asset->status == 'In Maintenance')
                                <span class="status-badge status-maintenance">In Maintenance</span>
                            @elseif($asset->status == 'Disposed')
                                <span class="status-badge status-disposed">Disposed</span>
                            @else
                                <span class="status-badge" style="background: rgba(255,255,255,0.2); color: white;">{{ $asset->status }}</span>
                            @endif
                            <span class="condition-badge">{{ $asset->condition }}</span>
                            @if ($asset->category)
                                <span class="category-badge">{{ $asset->category->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="asset-actions">
                    <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-light btn-sm">
                        <i class="bx bx-edit me-1"></i> Edit
                    </a>
                    <div class="dropdown">
                        <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if ($asset->isAvailable())
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#assignAssetModal">
                                        <i class="bx bx-user-check me-2"></i> Assign
                                    </a>
                                </li>
                            @endif
                            @if ($asset->isAssigned())
                                <li>
                                    <a class="dropdown-item" href="{{ route('assets.return-asset', $asset->id) }}">
                                        <i class="bx bx-undo me-2"></i> Return
                                    </a>
                                </li>
                            @endif
                            @if ($asset->isAvailable())
                                <li>
                                    <a class="dropdown-item" href="{{ route('assets.create-maintenance', $asset->id) }}">
                                        <i class="bx bx-wrench me-2"></i> Maintenance
                                    </a>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" id="delete-form-{{ $asset->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="dropdown-item text-danger"
                                        onclick="if(confirm('Are you sure you want to delete this asset? This action cannot be undone.')) { document.getElementById('delete-form-{{ $asset->id }}').submit(); }">
                                        <i class="bx bx-trash me-2"></i> Delete
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="assets-body">
            <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#details" role="tab">
                        <i class="bx bx-info-circle me-1"></i> Details
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#assignment" role="tab">
                        <i class="bx bx-user-check me-1"></i> Assignment
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#maintenance" role="tab">
                        <i class="bx bx-wrench me-1"></i> Maintenance
                        @if($asset->maintenances->count() > 0)
                            <span class="badge bg-primary ms-1 rounded-pill">{{ $asset->maintenances->count() }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#history" role="tab">
                        <i class="bx bx-history me-1"></i> History
                        @if($logs->count() > 0)
                            <span class="badge bg-primary ms-1 rounded-pill">{{ $logs->count() }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#attachments" role="tab">
                        <i class="bx bx-paperclip me-1"></i> Attachments
                        @if($asset->images->count() + $asset->documents->count() > 0)
                            <span class="badge bg-primary ms-1 rounded-pill">{{ $asset->images->count() + $asset->documents->count() }}</span>
                        @endif
                    </a>
                </li>
                @if($asset->isDisposed() && $asset->disposal)
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#disposal" role="tab">
                            <i class="bx bx-trash-alt me-1"></i> Disposal
                        </a>
                    </li>
                @endif
            </ul>

            <div class="tab-content">
                <!-- Details Tab -->
                <div class="tab-pane active" id="details" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h6 class="section-title"><i class="bx bx-info-circle me-2"></i>Basic Information</h6>
                            <table class="detail-table">
                                <tbody>
                                    <tr>
                                        <th>Asset Name</th>
                                        <td>{{ $asset->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Asset Code</th>
                                        <td><code>{{ $asset->asset_code }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Category</th>
                                        <td>{{ $asset->category->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>{{ $asset->status }}</td>
                                    </tr>
                                    <tr>
                                        <th>Condition</th>
                                        <td>{{ $asset->condition }}</td>
                                    </tr>
                                    <tr>
                                        <th>Location</th>
                                        <td>{{ $asset->venue->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Make / Manufacturer</th>
                                        <td>{{ $asset->make ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Model</th>
                                        <td>{{ $asset->model ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Specifications</th>
                                        <td>{{ $asset->specifications ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Notes</th>
                                        <td>{{ $asset->notes ?? 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6 class="section-title"><i class="bx bx-dollar-circle me-2"></i>Financial Information</h6>
                            <table class="detail-table">
                                <tbody>
                                    <tr>
                                        <th>Business Contact</th>
                                        <td>
                                            @if($asset->vendor)
                                                <div>{{ $asset->vendor->name }}</div>
                                                <small class="text-muted">{{ $asset->vendor->primary_person_label ?? 'No primary person' }}</small>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Purchase Date</th>
                                        <td>{{ $asset->purchase_date ? $asset->purchase_date->format('M d, Y') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Purchase Price</th>
                                        <td>
                                            @if ($asset->purchase_price)
                                                {{ number_format($asset->purchase_price, 2) }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Invoice Number</th>
                                        <td>{{ $asset->invoice_number ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Warranty Expiry</th>
                                        <td>
                                            @if ($asset->warranty_expiry)
                                                {{ $asset->warranty_expiry->format('M d, Y') }}
                                                @if ($asset->warranty_expiry->isPast())
                                                    <span class="badge bg-danger ms-1">Expired</span>
                                                @else
                                                    <span class="badge bg-success ms-1">Active</span>
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Expected Lifespan</th>
                                        <td>{{ $asset->expected_lifespan ? $asset->expected_lifespan . ' months' : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Current Value</th>
                                        <td>
                                            @if ($asset->current_value)
                                                {{ number_format($asset->current_value, 2) }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created At</th>
                                        <td>{{ $asset->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Last Updated</th>
                                        <td>{{ $asset->updated_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Assignment Tab -->
                <div class="tab-pane" id="assignment" role="tabpanel">
                    @if ($asset->currentAssignment)
                        <div class="current-assignment-card">
                            <h5><i class="bx bx-user-check me-2"></i>Currently Assigned</h5>
                            <div class="assignment-info-grid">
                                <div class="assignment-info-item">
                                    <strong>Assigned To:</strong>
                                    @if ($asset->currentAssignment->assignable_type == 'App\\Models\\User')
                                        {{ $asset->currentAssignment->assignable->full_name ?? 'User' }}
                                    @endif
                                </div>
                                <div class="assignment-info-item">
                                    <strong>Assigned Date:</strong>
                                    {{ $asset->currentAssignment->assigned_date->format('M d, Y') }}
                                </div>
                                <div class="assignment-info-item">
                                    <strong>Condition:</strong>
                                    {{ $asset->currentAssignment->condition_on_assignment }}
                                </div>
                                <div class="assignment-info-item">
                                    <strong>Expected Return:</strong>
                                    @if ($asset->currentAssignment->expected_return_date)
                                        {{ $asset->currentAssignment->expected_return_date->format('M d, Y') }}
                                        @if ($asset->currentAssignment->expected_return_date->isPast())
                                            <span class="badge bg-danger">Overdue</span>
                                        @endif
                                    @else
                                        Not specified
                                    @endif
                                </div>
                                <div class="assignment-info-item">
                                    <strong>Assigned By:</strong>
                                    {{ $asset->currentAssignment->assignedByUser->full_name ?? 'System' }}
                                </div>
                                @if ($asset->currentAssignment->assignment_notes)
                                    <div class="assignment-info-item" style="grid-column: span 2;">
                                        <strong>Notes:</strong>
                                        {{ $asset->currentAssignment->assignment_notes }}
                                    </div>
                                @endif
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('assets.return-asset', $asset->id) }}" class="btn btn-primary btn-sm">
                                    <i class="bx bx-undo me-1"></i> Process Return
                                </a>
                            </div>
                        </div>
                    @elseif($asset->status == 'Available')
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bx bx-user-check"></i>
                            </div>
                            <h5>Asset is Available for Assignment</h5>
                            <p>This asset is currently not assigned to anyone.</p>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignAssetModal">
                                <i class="bx bx-user-plus me-1"></i> Assign Asset
                            </button>
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon" style="background: #fef3c7;">
                                <i class="bx bx-error-circle" style="color: #d97706;"></i>
                            </div>
                            <h5>Asset Not Available for Assignment</h5>
                            <p>This asset is currently {{ strtolower($asset->status) }} and cannot be assigned.</p>
                        </div>
                    @endif

                    <div class="mt-4">
                        <h6 class="section-title"><i class="bx bx-history me-2"></i>Assignment History</h6>
                        <div class="table-responsive">
                            <table class="assets-table">
                                <thead>
                                    <tr>
                                        <th>Assigned To</th>
                                        <th>Assigned Date</th>
                                        <th>Return Date</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($asset->assignments ?? [] as $assignment)
                                        <tr>
                                            <td>
                                                @if ($assignment->assignable_type == 'App\\Models\\User')
                                                    {{ $assignment->assignable->full_name ?? 'User' }}
                                                @else
                                                    {{ $assignment->assignable->name ?? $assignment->assignable_type }}
                                                @endif
                                            </td>
                                            <td>{{ $assignment->assigned_date->format('M d, Y') }}</td>
                                            <td>{{ $assignment->actual_return_date ? $assignment->actual_return_date->format('M d, Y') : '-' }}</td>
                                            <td>
                                                @if ($assignment->actual_return_date)
                                                    {{ $assignment->assigned_date->diffInDays($assignment->actual_return_date) }} days
                                                @elseif($assignment->status == 'Assigned')
                                                    {{ $assignment->assigned_date->diffInDays(now()) }} days (ongoing)
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($assignment->status == 'Assigned')
                                                    <span class="badge bg-info">Assigned</span>
                                                @elseif($assignment->status == 'Returned')
                                                    <span class="badge bg-success">Returned</span>
                                                @elseif($assignment->status == 'Overdue')
                                                    <span class="badge bg-danger">Overdue</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $assignment->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No assignment history found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Tab -->
                <div class="tab-pane" id="maintenance" role="tabpanel">
                    @if (!$asset->isDisposed())
                        <div class="d-flex justify-content-end mb-3">
                            <a href="{{ route('assets.create-maintenance', $asset->id) }}" class="btn btn-primary btn-sm">
                                <i class="bx bx-plus me-1"></i> Add Maintenance Record
                            </a>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="assets-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Business Contact</th>
                                    <th>Cost</th>
                                    <th>Status</th>
                                    <th>Description</th>
                                    <th>Next Maintenance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($asset->maintenances as $maintenance)
                                    <tr>
                                        <td>{{ $maintenance->maintenance_date->format('M d, Y') }}</td>
                                        <td>{{ $maintenance->maintenance_type }}</td>
                                        <td>
                                            @if($maintenance->vendor)
                                                <div>{{ $maintenance->vendor->name }}</div>
                                                <small class="text-muted">{{ $maintenance->vendor->primary_person_label ?? 'No primary person' }}</small>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if ($maintenance->cost)
                                                {{ number_format($maintenance->cost, 2) }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if ($maintenance->status == 'Scheduled')
                                                <span class="badge bg-info">Scheduled</span>
                                            @elseif($maintenance->status == 'In Progress')
                                                <span class="badge bg-warning">In Progress</span>
                                            @elseif($maintenance->status == 'Completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($maintenance->status == 'Cancelled')
                                                <span class="badge bg-danger">Cancelled</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $maintenance->status }}</span>
                                            @endif
                                        </td>
                                        <td>{{ \Illuminate\Support\Str::limit($maintenance->description, 50) }}</td>
                                        <td>
                                            @if ($maintenance->next_maintenance_date)
                                                {{ $maintenance->next_maintenance_date->format('M d, Y') }}
                                                @if ($maintenance->next_maintenance_date->isPast())
                                                    <span class="badge bg-danger ms-1">Overdue</span>
                                                @elseif($maintenance->next_maintenance_date->diffInDays(now()) <= 30)
                                                    <span class="badge bg-warning ms-1">Soon</span>
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No maintenance records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- History/Logs Tab -->
                <div class="tab-pane" id="history" role="tabpanel">
                    <div class="table-responsive">
                        <table class="assets-table">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>Performed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            @if ($log->action == 'create')
                                                <span class="badge bg-success">Created</span>
                                            @elseif($log->action == 'update')
                                                <span class="badge bg-info">Updated</span>
                                            @elseif($log->action == 'delete')
                                                <span class="badge bg-danger">Deleted</span>
                                            @elseif($log->action == 'assign')
                                                <span class="badge bg-primary">Assigned</span>
                                            @elseif($log->action == 'return')
                                                <span class="badge bg-warning">Returned</span>
                                            @elseif($log->action == 'maintenance')
                                                <span class="badge bg-secondary">Maintenance</span>
                                            @else
                                                <span class="badge bg-dark">{{ ucfirst($log->action) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $log->description }}</td>
                                        <td>{{ $log->performedByUser->full_name ?? 'System' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No history records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Attachments Tab -->
                <div class="tab-pane" id="attachments" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="attachment-card">
                                <div class="attachment-card-header">
                                    <h5><i class="bx bx-image text-primary me-2"></i>Asset Image</h5>
                                </div>
                                <div class="attachment-card-body">
                                    @if ($asset->image_path)
                                        <img src="{{ asset('storage/' . $asset->image_path) }}" alt="{{ $asset->name }}" class="attachment-image">
                                        <div class="attachment-actions">
                                            <a href="{{ asset('storage/' . $asset->image_path) }}" class="btn btn-sm btn-primary" download>
                                                <i class="bx bx-download"></i>
                                            </a>
                                            <form action="{{ route('assets.destroy-image', $asset->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this image?');">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="empty-state-icon">
                                            <i class="bx bx-image-alt"></i>
                                        </div>
                                        <h6 class="mt-3">No Image Attached</h6>
                                        <p class="text-muted small mb-0">Upload an image from the asset edit page</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="attachment-card">
                                <div class="attachment-card-header">
                                    <h5><i class="bx bx-file text-info me-2"></i>Asset Document</h5>
                                </div>
                                <div class="attachment-card-body">
                                    @if ($asset->documents->isNotEmpty())
                                        @php $document = $asset->documents->first(); @endphp
                                        <div class="document-icon">
                                            @if (Str::endsWith(strtolower($document->document_path), '.pdf'))
                                                <i class="bx bxs-file-pdf"></i>
                                            @elseif(Str::endsWith(strtolower($document->document_path), ['.doc', '.docx']))
                                                <i class="bx bxs-file-doc"></i>
                                            @elseif(Str::endsWith(strtolower($document->document_path), ['.xls', '.xlsx']))
                                                <i class="bx bxs-file-xlsx"></i>
                                            @elseif(Str::endsWith(strtolower($document->document_path), '.txt'))
                                                <i class="bx bxs-file-txt"></i>
                                            @else
                                                <i class="bx bxs-file"></i>
                                            @endif
                                        </div>
                                        <h6>{{ $document->title }}</h6>
                                        <p class="text-muted small mb-1">{{ $document->document_type }}</p>
                                        @if ($document->description)
                                            <p class="text-muted small mb-3">{{ $document->description }}</p>
                                        @endif
                                        <div class="attachment-actions">
                                            <a href="{{ asset('storage/' . $document->document_path) }}" class="btn btn-sm btn-primary" target="_blank">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <a href="{{ asset('storage/' . $document->document_path) }}" class="btn btn-sm btn-info" download>
                                                <i class="bx bx-download"></i>
                                            </a>
                                            <form action="{{ route('assets.destroy-document', $document->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this document?');">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="empty-state-icon">
                                            <i class="bx bx-file-blank"></i>
                                        </div>
                                        <h6 class="mt-3">No Document Attached</h6>
                                        <p class="text-muted small mb-0">Upload a document from the asset edit page</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($asset->isDisposed() && $asset->disposal)
                    <!-- Disposal Tab -->
                    <div class="tab-pane" id="disposal" role="tabpanel">
                        <div class="disposal-info-card">
                            <h5><i class="bx bx-trash-alt me-2"></i>Disposal Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="detail-table">
                                        <tr>
                                            <th>Disposal Date</th>
                                            <td>{{ $asset->disposal->disposal_date->format('M d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Disposal Method</th>
                                            <td>{{ $asset->disposal->disposal_method }}</td>
                                        </tr>
                                        <tr>
                                            <th>Reason</th>
                                            <td>{{ $asset->disposal->reason ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="detail-table">
                                        <tr>
                                            <th>Disposed Value</th>
                                            <td>{{ $asset->disposal->disposed_value ? number_format($asset->disposal->disposed_value, 2) : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Disposed By</th>
                                            <td>{{ $asset->disposal->disposedByUser->full_name ?? 'System' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Notes</th>
                                            <td>{{ $asset->disposal->notes ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Asset Assignment Modal -->
    <div class="modal fade" id="assignAssetModal" tabindex="-1" aria-labelledby="assignAssetModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignAssetModalLabel"><i class="bx bx-user-plus me-2"></i>Assign Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignAssetForm" action="{{ route('assets.assign-asset') }}" method="POST">
                    @csrf
                    <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                    <input type="hidden" name="assignee_type" value="user">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Assign To Staff Member <span class="text-danger">*</span></label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">Select Staff Member</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->lastname . ' ' . $user->firstname }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="assigned_date" class="form-label">Assignment Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="assigned_date" name="assigned_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="expected_return_date" class="form-label">Expected Return Date</label>
                                <input type="date" class="form-control" id="expected_return_date" name="expected_return_date">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="condition_on_assignment" class="form-label">Condition <span class="text-danger">*</span></label>
                            <select class="form-select" id="condition_on_assignment" name="condition_on_assignment" required>
                                <option value="New" {{ $asset->condition == 'New' ? 'selected' : '' }}>New</option>
                                <option value="Good" {{ $asset->condition == 'Good' ? 'selected' : '' }}>Good</option>
                                <option value="Fair" {{ $asset->condition == 'Fair' ? 'selected' : '' }}>Fair</option>
                                <option value="Poor" {{ $asset->condition == 'Poor' ? 'selected' : '' }}>Poor</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="assignment_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="assignment_notes" name="assignment_notes" rows="3" placeholder="Optional notes about this assignment..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-check me-1"></i>Assign Asset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-asset');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const assetId = this.getAttribute('data-asset-id');

                    if (confirm('Are you sure you want to delete this asset? This action cannot be undone.')) {
                        document.getElementById('delete-form-' + assetId).submit();
                    }
                });
            });

            function saveActiveTab(tabId) {
                localStorage.setItem('assetShowActiveTab', tabId);
            }

            function loadActiveTab() {
                const activeTabId = localStorage.getItem('assetShowActiveTab');

                if (activeTabId) {
                    const tabElement = document.querySelector(`a[href="#${activeTabId}"]`);

                    if (tabElement) {
                        const tab = new bootstrap.Tab(tabElement);
                        tab.show();
                    }
                }
            }

            loadActiveTab();

            const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
            tabLinks.forEach(tabLink => {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    const targetId = event.target.getAttribute('href').substring(1);
                    saveActiveTab(targetId);
                });
            });

            const viewChangesButtons = document.querySelectorAll('.view-changes');
            viewChangesButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const changes = JSON.parse(this.getAttribute('data-changes'));
                    const tableBody = document.getElementById('changesTableBody');
                    tableBody.innerHTML = '';

                    for (const [field, values] of Object.entries(changes)) {
                        const row = document.createElement('tr');

                        const fieldCell = document.createElement('td');
                        fieldCell.textContent = field.charAt(0).toUpperCase() + field.slice(1).replace('_', ' ');

                        const oldValueCell = document.createElement('td');
                        oldValueCell.textContent = values.old !== null ? values.old : 'N/A';

                        const newValueCell = document.createElement('td');
                        newValueCell.textContent = values.new !== null ? values.new : 'N/A';

                        row.appendChild(fieldCell);
                        row.appendChild(oldValueCell);
                        row.appendChild(newValueCell);

                        tableBody.appendChild(row);
                    }
                });
            });
        });
    </script>
@endsection
