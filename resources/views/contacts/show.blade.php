@extends('layouts.master')

@section('title')
    {{ $contact->name }}
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
            {{ $contact->name }}
        @endslot
    @endcomponent

    @include('contacts.partials.alerts')

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ $contact->name }}</h1>
                <p class="page-subtitle">Business contact profile used across asset assignment, maintenance tracking, and vendor replacement workflows.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-primary">Edit</a>
                <form action="{{ route('contacts.destroy', $contact) }}" method="POST" onsubmit="return confirm('Delete this contact?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">Delete</button>
                </form>
            </div>
        </div>

        <div class="help-text">
            <div class="help-title">Business Contact Details</div>
            <div class="help-content">
                Review the business profile, confirm the primary person and tags are correct, and check linked assets before deactivating or deleting this contact.
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <span class="status-badge {{ $contact->is_active ? 'status-active' : 'status-inactive' }}">{{ $contact->is_active ? 'Active' : 'Inactive' }}</span>
            @foreach ($contact->tags as $tag)
                <span class="tag-badge" style="background-color: {{ $tag->color ?: '#64748b' }};">{{ $tag->name }}</span>
            @endforeach
        </div>

        <div class="row g-4">
            <div class="col-xl-5">
                <div class="card detail-card">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Business Details</h4>
                        <dl class="row mb-0 key-value-list">
                            <dt class="col-sm-4">Email</dt>
                            <dd class="col-sm-8">{{ $contact->email ?: 'N/A' }}</dd>
                            <dt class="col-sm-4">Phone</dt>
                            <dd class="col-sm-8">{{ $contact->phone ?: 'N/A' }}</dd>
                            <dt class="col-sm-4">Address</dt>
                            <dd class="col-sm-8">{{ $contact->address ?: 'N/A' }}</dd>
                            <dt class="col-sm-4">Notes</dt>
                            <dd class="col-sm-8">{{ $contact->notes ?: 'N/A' }}</dd>
                        </dl>
                    </div>
                </div>

                <div class="card detail-card">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Usage</h4>
                        <div class="mb-3">
                            <div class="fw-semibold">{{ $contact->assets->count() }} linked assets</div>
                            <div class="small text-muted">Assets in the register using this business contact.</div>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ $contact->maintenances->count() }} maintenance records</div>
                            <div class="small text-muted">Maintenance jobs linked to this business contact.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="card detail-card">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Contact People</h4>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Title</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Primary</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($contact->people as $person)
                                        <tr>
                                            <td>{{ $person->name }}</td>
                                            <td>{{ $person->title ?: 'N/A' }}</td>
                                            <td>{{ $person->email ?: 'N/A' }}</td>
                                            <td>{{ $person->phone ?: 'N/A' }}</td>
                                            <td>
                                                @if ($person->is_primary)
                                                    <span class="status-badge status-active">Primary</span>
                                                @else
                                                    <span class="text-muted">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No contact people recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card detail-card">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Linked Assets</h4>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Asset</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($contact->assets->sortBy('name') as $asset)
                                        <tr>
                                            <td><a href="{{ route('assets.show', $asset) }}">{{ $asset->name }}</a></td>
                                            <td>{{ $asset->category?->name ?: 'N/A' }}</td>
                                            <td>{{ $asset->status }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">No linked assets.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
