@extends('layouts.master')
@section('title')
    Edit Asset: {{ $asset->name }}
@endsection

@section('css')
    <style>
        .assets-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .assets-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .assets-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 20px;
        }

        .assets-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .assets-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #4e73df;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
        }

        .section-title {
            font-weight: 600;
            color: #374151;
            font-size: 16px;
            padding-bottom: 12px;
            margin-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #4e73df;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .form-control:disabled,
        .form-select:disabled {
            background: #f3f4f6;
            cursor: not-allowed;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .input-group .form-control {
            border-right: none;
        }

        .input-group .btn-outline-secondary {
            border: 1px solid #d1d5db;
            background: #f9fafb;
            color: #6b7280;
            font-size: 13px;
        }

        .input-group .btn-outline-secondary:hover {
            background: #e5e7eb;
            color: #374151;
            border-color: #d1d5db;
        }

        .form-hint {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 4px;
        }

        /* Custom File Input */
        .custom-file-input {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .custom-file-input input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #fff;
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-input-label:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
        }

        .file-input-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .file-input-text {
            flex: 1;
        }

        .file-input-text .file-label {
            display: block;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .file-input-text .file-hint {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        .file-input-text .file-name {
            display: block;
            font-size: 13px;
            color: #059669;
            margin-top: 4px;
            font-weight: 500;
        }

        .current-file-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .current-file-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .current-file-info {
            flex: 1;
        }

        .current-file-info h6 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #374151;
        }

        .current-file-info p {
            margin: 0;
            font-size: 12px;
            color: #6b7280;
        }

        .image-preview-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            text-align: center;
            min-height: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .image-preview-box i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 8px;
        }

        .image-preview-box p {
            color: #9ca3af;
            font-size: 13px;
            margin: 0;
        }

        .image-preview-box img {
            max-height: 150px;
            border-radius: 3px;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-secondary {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-available { background: #d1fae5; color: #065f46; }
        .status-assigned { background: #dbeafe; color: #1e40af; }
        .status-maintenance { background: #fef3c7; color: #92400e; }
        .status-disposed { background: #fee2e2; color: #991b1b; }

        @media (max-width: 768px) {
            .assets-header {
                padding: 20px;
            }

            .assets-body {
                padding: 16px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.index') }}">Assets</a>
        @endslot
        @slot('title')
            Edit Asset
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ $error }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="assets-container">
                <div class="assets-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4><i class="fas fa-edit me-2"></i>Edit Asset</h4>
                            <p>Update information for: {{ $asset->name }}</p>
                        </div>
                        <div>
                            @if ($asset->status == 'Available')
                                <span class="status-badge status-available">Available</span>
                            @elseif($asset->status == 'Assigned')
                                <span class="status-badge status-assigned">Assigned</span>
                            @elseif($asset->status == 'In Maintenance')
                                <span class="status-badge status-maintenance">In Maintenance</span>
                            @else
                                <span class="status-badge status-disposed">Disposed</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="assets-body">
                    <div class="help-text">
                        <div class="help-title"><i class="fas fa-info-circle me-2"></i>Update Asset Information</div>
                        <p class="help-content">Modify the asset details below. Status cannot be changed directly - use the appropriate action (assign, return, maintenance, dispose) to change status.</p>
                    </div>

                    <form action="{{ route('assets.update', $asset->id) }}" method="POST" enctype="multipart/form-data" id="updateAssetForm">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-lg-6">
                                <div class="section-title">
                                    <i class="fas fa-box"></i> Basic Information
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Asset Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $asset->name) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="asset_code" class="form-label">Asset Code <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="asset_code" name="asset_code" value="{{ old('asset_code', $asset->asset_code) }}" required>
                                        <button class="btn btn-outline-secondary" type="button" id="generateAssetCode">
                                            <i class="fas fa-sync-alt me-1"></i> Generate
                                        </button>
                                    </div>
                                    <div class="form-hint">Unique identifier such as barcode or serial number</div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id', $asset->category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="contact_id" class="form-label">Business Contact</label>
                                        <select class="form-select" id="contact_id" name="contact_id">
                                            <option value="">Select Business Contact</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}" {{ old('contact_id', $asset->contact_id) == $vendor->id ? 'selected' : '' }}>
                                                    {{ $vendor->name }}{{ $vendor->primary_person_label ? ' - ' . $vendor->primary_person_label : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="venue_id" class="form-label">Location</label>
                                        <select class="form-select" id="venue_id" name="venue_id">
                                            <option value="">Select Location</option>
                                            @foreach ($venues as $venue)
                                                <option value="{{ $venue->id }}" {{ old('venue_id', $asset->venue_id) == $venue->id ? 'selected' : '' }}>
                                                    {{ $venue->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="condition" class="form-label">Condition</label>
                                        <select class="form-select" id="condition" name="condition">
                                            <option value="New" {{ old('condition', $asset->condition) == 'New' ? 'selected' : '' }}>New</option>
                                            <option value="Good" {{ old('condition', $asset->condition) == 'Good' ? 'selected' : '' }}>Good</option>
                                            <option value="Fair" {{ old('condition', $asset->condition) == 'Fair' ? 'selected' : '' }}>Fair</option>
                                            <option value="Poor" {{ old('condition', $asset->condition) == 'Poor' ? 'selected' : '' }}>Poor</option>
                                        </select>
                                    </div>
                                </div>

                                <input type="hidden" name="status" value="{{ old('status', $asset->status) }}">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="make" class="form-label">Manufacturer</label>
                                        <input type="text" class="form-control" id="make" name="make" value="{{ old('make', $asset->make) }}" placeholder="e.g. Apple">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="model" class="form-label">Model</label>
                                        <input type="text" class="form-control" id="model" name="model" value="{{ old('model', $asset->model) }}" placeholder="e.g. MacBook Pro 2024">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="specifications" class="form-label">Specifications</label>
                                    <textarea class="form-control" id="specifications" name="specifications" rows="3" placeholder="Include technical details, dimensions, etc.">{{ old('specifications', $asset->specifications) }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes about the asset">{{ old('notes', $asset->notes) }}</textarea>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-lg-6">
                                <div class="section-title">
                                    <i class="fas fa-dollar-sign"></i> Financial & Warranty
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="purchase_date" class="form-label">Purchase Date</label>
                                        <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="purchase_price" class="form-label">Purchase Price</label>
                                        <input type="number" step="0.01" class="form-control" id="purchase_price" name="purchase_price" value="{{ old('purchase_price', $asset->purchase_price) }}" placeholder="0.00">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="warranty_expiry" class="form-label">Warranty Expiry</label>
                                        <input type="date" class="form-control" id="warranty_expiry" name="warranty_expiry" value="{{ old('warranty_expiry', $asset->warranty_expiry ? $asset->warranty_expiry->format('Y-m-d') : '') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="expected_lifespan" class="form-label">Expected Lifespan (months)</label>
                                        <input type="number" class="form-control" id="expected_lifespan" name="expected_lifespan" value="{{ old('expected_lifespan', $asset->expected_lifespan) }}" placeholder="12">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="current_value" class="form-label">Current Value</label>
                                        <input type="number" step="0.01" class="form-control" id="current_value" name="current_value" value="{{ old('current_value', $asset->current_value) }}" placeholder="0.00">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="invoice_number" class="form-label">Invoice Number</label>
                                        <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $asset->invoice_number) }}" placeholder="INV-123456">
                                    </div>
                                </div>

                                <div class="section-title mt-4">
                                    <i class="fas fa-image"></i> Asset Image
                                </div>

                                @if ($asset->image_path)
                                    <div class="mb-3">
                                        <div class="current-file-box">
                                            <img src="{{ asset('storage/' . $asset->image_path) }}" alt="Current Image" style="max-height: 80px; border-radius: 3px;">
                                            <div class="current-file-info">
                                                <h6>Current Image</h6>
                                                <p>Upload new image to replace</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <div class="custom-file-input">
                                        <input type="file" id="image" name="image" accept="image/*">
                                        <label for="image" class="file-input-label">
                                            <div class="file-input-icon">
                                                <i class="fas fa-camera"></i>
                                            </div>
                                            <div class="file-input-text">
                                                <span class="file-label">{{ $asset->image_path ? 'Replace Image' : 'Choose Image' }}</span>
                                                <span class="file-hint">JPG, PNG, GIF (Max 2MB)</span>
                                                <span class="file-name" id="imageFileName"></span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="section-title mt-4">
                                    <i class="fas fa-file-alt"></i> Asset Documents
                                </div>

                                @if ($asset->documents->isNotEmpty())
                                    @php $document = $asset->documents->first(); @endphp
                                    <div class="mb-3">
                                        <div class="current-file-box">
                                            <div class="current-file-icon">
                                                @if (Str::endsWith(strtolower($document->document_path), '.pdf'))
                                                    <i class="fas fa-file-pdf"></i>
                                                @elseif(Str::endsWith(strtolower($document->document_path), ['.doc', '.docx']))
                                                    <i class="fas fa-file-word"></i>
                                                @elseif(Str::endsWith(strtolower($document->document_path), ['.xls', '.xlsx']))
                                                    <i class="fas fa-file-excel"></i>
                                                @else
                                                    <i class="fas fa-file"></i>
                                                @endif
                                            </div>
                                            <div class="current-file-info">
                                                <h6>{{ $document->title }}</h6>
                                                <p>{{ $document->document_type }} - <a href="{{ asset('storage/' . $document->document_path) }}" target="_blank">View</a></p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <div class="custom-file-input">
                                        <input type="file" id="document" name="document" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt">
                                        <label for="document" class="file-input-label">
                                            <div class="file-input-icon">
                                                <i class="fas fa-file-upload"></i>
                                            </div>
                                            <div class="file-input-text">
                                                <span class="file-label">{{ $asset->documents->isNotEmpty() ? 'Replace Document' : 'Choose Document' }}</span>
                                                <span class="file-hint">PDF, DOC, DOCX, XLS, XLSX, TXT (Max 5MB)</span>
                                                <span class="file-name" id="documentFileName"></span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="document_type" class="form-label">Document Type</label>
                                        <select class="form-select" id="document_type" name="document_type">
                                            <option value="">Select Type</option>
                                            <option value="Manual">Manual</option>
                                            <option value="Invoice">Invoice</option>
                                            <option value="Warranty">Warranty</option>
                                            <option value="Certificate">Certificate</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="document_title" class="form-label">Document Title</label>
                                        <input type="text" class="form-control" id="document_title" name="document_title" placeholder="e.g. User Manual">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('assets.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary btn-loading" id="submitBtn">
                                <span class="btn-text"><i class="fas fa-save"></i> Update Asset</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Updating...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Generate Asset Code
            document.getElementById('generateAssetCode').addEventListener('click', function() {
                const prefix = 'ASSET';
                const timestamp = Date.now().toString().slice(-6);
                const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
                const assetCode = `${prefix}-${timestamp}-${random}`;
                document.getElementById('asset_code').value = assetCode;
            });

            // Image File Name
            const imageInput = document.getElementById('image');
            imageInput.addEventListener('change', function() {
                document.getElementById('imageFileName').textContent = this.files[0] ? this.files[0].name : '';
            });

            // Document File Name
            const documentInput = document.getElementById('document');
            const documentTypeSelect = document.getElementById('document_type');
            const documentTitleInput = document.getElementById('document_title');

            documentInput.addEventListener('change', function() {
                const file = this.files[0];
                document.getElementById('documentFileName').textContent = file ? file.name : '';

                if (file) {
                    const filename = file.name.split('.').slice(0, -1).join('.');
                    const ext = file.name.split('.').pop().toLowerCase();

                    // Auto-detect document type
                    if (documentTypeSelect.value === '') {
                        if (filename.toLowerCase().includes('manual') || filename.toLowerCase().includes('guide')) {
                            documentTypeSelect.value = 'Manual';
                        } else if (filename.toLowerCase().includes('invoice') || filename.toLowerCase().includes('receipt')) {
                            documentTypeSelect.value = 'Invoice';
                        } else if (filename.toLowerCase().includes('warranty')) {
                            documentTypeSelect.value = 'Warranty';
                        } else if (filename.toLowerCase().includes('cert')) {
                            documentTypeSelect.value = 'Certificate';
                        }
                    }

                    // Auto-generate title
                    if (documentTitleInput.value === '') {
                        const formattedName = filename
                            .replace(/([A-Z])/g, ' $1')
                            .replace(/_/g, ' ')
                            .replace(/-/g, ' ')
                            .replace(/\w\S*/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());

                        documentTitleInput.value = documentTypeSelect.value
                            ? documentTypeSelect.value + ' - ' + formattedName
                            : formattedName;
                    }
                }
            });

            documentTypeSelect.addEventListener('change', function() {
                if (documentTitleInput.value && documentTitleInput.value.includes(' - ')) {
                    const parts = documentTitleInput.value.split(' - ');
                    parts.shift();
                    documentTitleInput.value = this.value ? this.value + ' - ' + parts.join(' - ') : parts.join(' - ');
                } else if (documentTitleInput.value && this.value) {
                    documentTitleInput.value = this.value + ' - ' + documentTitleInput.value;
                }
            });

            // Form Submit with Loading
            const form = document.getElementById('updateAssetForm');
            const submitBtn = document.getElementById('submitBtn');

            form.addEventListener('submit', function(e) {
                // Validate document type if document is uploaded
                if (documentInput.files.length > 0) {
                    if (!documentTypeSelect.value) {
                        e.preventDefault();
                        alert('Please select a document type for the uploaded document');
                        documentTypeSelect.focus();
                        return;
                    }
                    if (!documentTitleInput.value) {
                        e.preventDefault();
                        alert('Please provide a title for the uploaded document');
                        documentTitleInput.focus();
                        return;
                    }
                }

                // Show loading state
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });
        });
    </script>
@endsection
