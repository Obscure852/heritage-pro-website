@extends('layouts.master')

@section('title')
    Contact Settings
@endsection

@section('css')
    @include('contacts.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('contacts.index') }}">Contacts</a>
        @endslot
        @slot('title')
            Settings
        @endslot
    @endcomponent

    @include('contacts.partials.alerts')

    <div class="contacts-container">
        <div class="contacts-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Contact Settings</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage the tags that categorise your business contacts across Assets and Maintenance.</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $contactTags->count() }}</h4>
                                <small class="opacity-75">Total Tags</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $contactTags->where('is_active', true)->count() }}</h4>
                                <small class="opacity-75">Active</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $contactTags->where('is_active', false)->count() }}</h4>
                                <small class="opacity-75">Inactive</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="contacts-body">
            <div class="help-text">
                <div class="help-title">Tag Management</div>
                <div class="help-content">
                    Tags describe the kind of business a contact performs. Use the table below to manage your tags, or create a new one.
                </div>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-md-12 text-end">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tagModal" onclick="openCreateModal()">
                        <i class="fas fa-plus me-1"></i> New Tag
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tag</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Assets</th>
                            <th>Maintenance</th>
                            <th>Status</th>
                            <th>Contacts</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contactTags as $tag)
                            <tr>
                                <td>
                                    <span class="tag-badge" style="background-color: {{ $tag->color ?: '#64748b' }};">{{ $tag->name }}</span>
                                </td>
                                <td class="text-muted small">{{ $tag->slug }}</td>
                                <td class="small" style="max-width: 250px;">{{ Str::limit($tag->description, 60) ?: '—' }}</td>
                                <td>
                                    @if ($tag->usable_in_assets)
                                        <span class="status-badge status-active">Yes</span>
                                    @else
                                        <span class="status-badge status-inactive">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($tag->usable_in_maintenance)
                                        <span class="status-badge status-active">Yes</span>
                                    @else
                                        <span class="status-badge status-inactive">No</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge {{ $tag->is_active ? 'status-active' : 'status-inactive' }}">
                                        {{ $tag->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="fw-semibold">{{ $tag->contacts_count }}</td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick='openEditModal(@json($tag))' title="Edit Tag">
                                            <i class="bx bx-edit-alt"></i>
                                        </button>
                                        <form method="POST" action="{{ route('contacts.tags.destroy', $tag) }}" onsubmit="return confirm('Delete this tag and remove it from all contacts?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Tag">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="fas fa-tags" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0" style="font-size: 15px;">No tags configured yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Create / Edit Modal --}}
    <div class="modal fade" id="tagModal" tabindex="-1" aria-labelledby="tagModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="tagForm" method="POST">
                    @csrf
                    <input type="hidden" id="tagMethod" name="_method" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tagModalLabel">New Tag</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="modal_name">Name</label>
                            <input type="text" class="form-control" id="modal_name" name="name" placeholder="e.g. Maintenance Provider" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="modal_slug">Slug</label>
                            <input type="text" class="form-control" id="modal_slug" name="slug" placeholder="auto-generated if left blank">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="modal_description">Description</label>
                            <textarea class="form-control" id="modal_description" name="description" rows="2" placeholder="Explain when this tag should be applied."></textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label" for="modal_color">Color</label>
                                <input type="color" class="form-control form-control-color w-100" id="modal_color" name="color" value="#1d4ed8">
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="modal_sort_order">Sort Order</label>
                                <input type="number" class="form-control" id="modal_sort_order" name="sort_order" value="0" min="0">
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modal_usable_in_assets" name="usable_in_assets" value="1">
                                <label class="form-check-label" for="modal_usable_in_assets">Available in Assets</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modal_usable_in_maintenance" name="usable_in_maintenance" value="1">
                                <label class="form-check-label" for="modal_usable_in_maintenance">Available in Maintenance</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modal_is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="modal_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-save"></i> <span id="tagSubmitLabel">Create Tag</span></span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('contacts.partials.form-script')
    <script>
        const storeUrl = "{{ route('contacts.tags.store') }}";
        const updateUrlBase = "{{ url('contacts/settings/tags') }}";

        function openCreateModal() {
            document.getElementById('tagModalLabel').textContent = 'New Tag';
            document.getElementById('tagSubmitLabel').textContent = 'Create Tag';
            document.getElementById('tagForm').action = storeUrl;
            document.getElementById('tagMethod').value = 'POST';

            document.getElementById('modal_name').value = '';
            document.getElementById('modal_slug').value = '';
            document.getElementById('modal_description').value = '';
            document.getElementById('modal_color').value = '#1d4ed8';
            document.getElementById('modal_sort_order').value = '0';
            document.getElementById('modal_usable_in_assets').checked = false;
            document.getElementById('modal_usable_in_maintenance').checked = false;
            document.getElementById('modal_is_active').checked = true;
        }

        function openEditModal(tag) {
            document.getElementById('tagModalLabel').textContent = 'Edit Tag';
            document.getElementById('tagSubmitLabel').textContent = 'Save Changes';
            document.getElementById('tagForm').action = updateUrlBase + '/' + tag.id;
            document.getElementById('tagMethod').value = 'PUT';

            document.getElementById('modal_name').value = tag.name || '';
            document.getElementById('modal_slug').value = tag.slug || '';
            document.getElementById('modal_description').value = tag.description || '';
            document.getElementById('modal_color').value = tag.color || '#64748b';
            document.getElementById('modal_sort_order').value = tag.sort_order || 0;
            document.getElementById('modal_usable_in_assets').checked = !!tag.usable_in_assets;
            document.getElementById('modal_usable_in_maintenance').checked = !!tag.usable_in_maintenance;
            document.getElementById('modal_is_active').checked = !!tag.is_active;

            var modal = new bootstrap.Modal(document.getElementById('tagModal'));
            modal.show();
        }
    </script>
@endsection
