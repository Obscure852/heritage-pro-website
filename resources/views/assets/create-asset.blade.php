@extends('layouts.master')
@section('title')
    Add New Asset
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

        .form-label .text-danger {
            color: #dc2626 !important;
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

        .form-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 20px;
        }

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
            Add New Asset
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
                    <h4><i class="fas fa-plus-circle me-2"></i>Add New Asset</h4>
                    <p>Register a new asset in the school inventory</p>
                </div>

                <div class="assets-body">
                    <div class="help-text">
                        <div class="help-title"><i class="fas fa-info-circle me-2"></i>Asset Registration</div>
                        <p class="help-content">Fill in the details below to add a new asset. Required fields are marked with an asterisk (*). You can also upload an image and documents for the asset.</p>
                    </div>

                    <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data" id="createAssetForm">
                        @csrf
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-lg-6">
                                <div class="section-title">
                                    <i class="fas fa-box"></i> Basic Information
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Asset Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required placeholder="Enter asset name">
                                </div>

                                <div class="mb-3">
                                    <label for="asset_code" class="form-label">Asset Code <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="asset_code" name="asset_code" value="{{ old('asset_code') }}" required placeholder="e.g. ASSET-123456">
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
                                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                                <option value="{{ $vendor->id }}" {{ old('contact_id') == $vendor->id ? 'selected' : '' }}>
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
                                                <option value="{{ $venue->id }}" {{ old('venue_id') == $venue->id ? 'selected' : '' }}>
                                                    {{ $venue->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="Available" {{ old('status') == 'Available' ? 'selected' : '' }}>Available</option>
                                            <option value="In Maintenance" {{ old('status') == 'In Maintenance' ? 'selected' : '' }}>In Maintenance</option>
                                            <option value="Disposed" {{ old('status') == 'Disposed' ? 'selected' : '' }}>Disposed</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="make" class="form-label">Manufacturer</label>
                                        <input type="text" class="form-control" id="make" placeholder="e.g. Apple" name="make" value="{{ old('make') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="model" class="form-label">Model</label>
                                        <input type="text" class="form-control" id="model" placeholder="e.g. MacBook Pro 2024" name="model" value="{{ old('model') }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="condition" class="form-label">Condition</label>
                                    <select class="form-select" id="condition" name="condition">
                                        <option value="New" {{ old('condition') == 'New' ? 'selected' : '' }}>New</option>
                                        <option value="Good" {{ old('condition', 'Good') == 'Good' ? 'selected' : '' }}>Good</option>
                                        <option value="Fair" {{ old('condition') == 'Fair' ? 'selected' : '' }}>Fair</option>
                                        <option value="Poor" {{ old('condition') == 'Poor' ? 'selected' : '' }}>Poor</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="specifications" class="form-label">Specifications</label>
                                    <textarea class="form-control" id="specifications" name="specifications" rows="3" placeholder="Include technical details, dimensions, etc.">{{ old('specifications') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes about the asset">{{ old('notes') }}</textarea>
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
                                        <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="{{ old('purchase_date') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="purchase_price" class="form-label">Purchase Price</label>
                                        <input type="number" step="0.01" class="form-control" id="purchase_price" name="purchase_price" value="{{ old('purchase_price') }}" placeholder="0.00">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="warranty_expiry" class="form-label">Warranty Expiry</label>
                                        <input type="date" class="form-control" id="warranty_expiry" name="warranty_expiry" value="{{ old('warranty_expiry') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="expected_lifespan" class="form-label">Expected Lifespan (months)</label>
                                        <input type="number" class="form-control" id="expected_lifespan" name="expected_lifespan" value="{{ old('expected_lifespan') }}" placeholder="12">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="current_value" class="form-label">Current Value</label>
                                        <input type="number" step="0.01" class="form-control" id="current_value" name="current_value" value="{{ old('current_value') }}" placeholder="0.00">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="invoice_number" class="form-label">Invoice Number</label>
                                        <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="{{ old('invoice_number') }}" placeholder="INV-123456">
                                    </div>
                                </div>

                                <div class="section-title mt-4">
                                    <i class="fas fa-image"></i> Asset Image
                                </div>

                                <div class="mb-3">
                                    <div class="custom-file-input">
                                        <input type="file" id="image" name="image" accept="image/*">
                                        <label for="image" class="file-input-label">
                                            <div class="file-input-icon">
                                                <i class="fas fa-camera"></i>
                                            </div>
                                            <div class="file-input-text">
                                                <span class="file-label">Choose Image</span>
                                                <span class="file-hint">JPG, PNG, GIF (Max 2MB)</span>
                                                <span class="file-name" id="imageFileName"></span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="image-preview-box" id="imagePreview">
                                        <img src="" alt="Preview" class="d-none">
                                        <i class="bx bx-image-alt"></i>
                                        <p>Image preview will appear here</p>
                                    </div>
                                </div>

                                <div class="section-title mt-4">
                                    <i class="fas fa-file-alt"></i> Asset Documents
                                </div>

                                <div class="mb-3">
                                    <div class="custom-file-input">
                                        <input type="file" id="document" name="document" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt">
                                        <label for="document" class="file-input-label">
                                            <div class="file-input-icon">
                                                <i class="fas fa-file-upload"></i>
                                            </div>
                                            <div class="file-input-text">
                                                <span class="file-label">Choose Document</span>
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
                                <span class="btn-text"><i class="fas fa-save"></i> Save Asset</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Saving...
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

            // Image Preview
            const imageInput = document.getElementById('image');
            const previewContainer = document.getElementById('imagePreview');
            const previewImage = previewContainer.querySelector('img');
            const previewIcon = previewContainer.querySelector('i');
            const previewText = previewContainer.querySelector('p');

            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                document.getElementById('imageFileName').textContent = file ? file.name : '';

                if (file) {
                    const reader = new FileReader();
                    reader.addEventListener('load', function() {
                        previewImage.setAttribute('src', this.result);
                        previewImage.classList.remove('d-none');
                        previewIcon.classList.add('d-none');
                        previewText.classList.add('d-none');
                    });
                    reader.readAsDataURL(file);
                } else {
                    previewImage.classList.add('d-none');
                    previewIcon.classList.remove('d-none');
                    previewText.classList.remove('d-none');
                }
            });

            // Document File Name
            const documentInput = document.getElementById('document');
            documentInput.addEventListener('change', function() {
                document.getElementById('documentFileName').textContent = this.files[0] ? this.files[0].name : '';
                updateDocumentTitle();
            });

            // Auto Document Title
            const documentTypeSelect = document.getElementById('document_type');
            const documentTitleInput = document.getElementById('document_title');

            function updateDocumentTitle() {
                if (documentTitleInput.value === '') {
                    const docType = documentTypeSelect.value;
                    if (docType && documentInput.files.length > 0) {
                        const fileName = documentInput.files[0].name.split('.')[0];
                        const formattedName = fileName
                            .replace(/([A-Z])/g, ' $1')
                            .replace(/_/g, ' ')
                            .replace(/^\w/, c => c.toUpperCase())
                            .trim();
                        documentTitleInput.value = docType + ' - ' + formattedName;
                    }
                }
            }

            documentTypeSelect.addEventListener('change', updateDocumentTitle);

            // Auto Warranty Date
            const purchaseDateInput = document.getElementById('purchase_date');
            purchaseDateInput.addEventListener('change', function() {
                const warrantyExpiryInput = document.getElementById('warranty_expiry');
                if (warrantyExpiryInput.value === '') {
                    const purchaseDate = new Date(this.value);
                    if (!isNaN(purchaseDate)) {
                        const warrantyExpiry = new Date(purchaseDate);
                        warrantyExpiry.setFullYear(warrantyExpiry.getFullYear() + 1);
                        warrantyExpiryInput.value = warrantyExpiry.toISOString().split('T')[0];
                    }
                }
            });

            // Form Submit with Loading
            const form = document.getElementById('createAssetForm');
            const submitBtn = document.getElementById('submitBtn');

            form.addEventListener('submit', function(e) {
                // Validate document type if document is uploaded
                if (documentInput.files.length > 0 && !documentTypeSelect.value) {
                    e.preventDefault();
                    alert('Please select a document type for the uploaded document');
                    documentTypeSelect.focus();
                    return;
                }

                // Show loading state
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });
        });
    </script>
@endsection
