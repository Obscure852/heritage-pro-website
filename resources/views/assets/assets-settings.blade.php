@extends('layouts.master')

@section('title')
    Asset Settings
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Assets
        @endslot
        @slot('title')
            Asset Settings
        @endslot
    @endcomponent

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">New Category</h4>
                    <form method="POST" action="{{ route('asset-categories.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="code">Code</label>
                            <input type="text" class="form-control" id="code" name="code" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Category</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">Business Contacts</h4>
                    <p class="text-muted">Vendor management has moved out of Assets. Use the standalone Contacts module for suppliers, contractors, service providers, and maintenance contacts.</p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('contacts.index') }}" class="btn btn-primary">Open Contacts</a>
                        <a href="{{ route('contacts.settings') }}" class="btn btn-light">Tag Settings</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="card-title mb-1">Asset Categories</h4>
                            <p class="text-muted mb-0">Edit category names, codes, descriptions, and active status.</p>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @forelse ($categories as $category)
                            <form method="POST" action="{{ route('asset-categories.update', $category) }}" class="border rounded p-3">
                                @csrf
                                @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Name</label>
                                        <input type="text" class="form-control" name="name" value="{{ $category->name }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Code</label>
                                        <input type="text" class="form-control" name="code" value="{{ $category->code }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Description</label>
                                        <input type="text" class="form-control" name="description" value="{{ $category->description }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label d-block">Status</label>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="category_{{ $category->id }}" {{ $category->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label" for="category_{{ $category->id }}">Active</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="text-muted small">{{ $category->assets_count }} linked assets</span>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                    </div>
                                </div>
                            </form>

                            <form method="POST" action="{{ route('asset-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?');" class="text-end mt-n2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete Category</button>
                            </form>
                        @empty
                            <div class="text-center text-muted py-5">No categories found.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="card-title mb-1">Asset Import</h4>
                            <p class="text-muted mb-0">Bulk import assets and optionally auto-create missing categories and business contacts.</p>
                        </div>
                        <a href="{{ route('assets.import-download-template') }}" class="btn btn-light">Download Template</a>
                    </div>

                    <form method="POST" action="{{ route('assets.import-process') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="file">Import File</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="create_missing_categories" name="create_missing_categories" value="1" checked>
                            <label class="form-check-label" for="create_missing_categories">Create missing categories automatically</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="create_missing_contacts" name="create_missing_contacts" value="1" checked>
                            <label class="form-check-label" for="create_missing_contacts">Create missing business contacts automatically</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="clear_data_first" name="clear_data_first" value="1">
                            <label class="form-check-label" for="clear_data_first">Clear all existing assets before import</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Import Assets</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
