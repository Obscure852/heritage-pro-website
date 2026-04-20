@extends('layouts.master')

@section('title')
    Contacts
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
            Business Contacts
        @endslot
    @endcomponent

    @include('contacts.partials.alerts')

    <div class="contacts-container">
        <div class="contacts-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="page-title">Business Contacts</h1>
                    <p class="page-subtitle mb-0">Reusable vendor and supplier records for assets, maintenance, and future shared business relationships.</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $contacts->total() }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $tags->count() }}</h4>
                                <small class="opacity-75">Tags</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $contacts->count() }}</h4>
                                <small class="opacity-75">Showing</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="contacts-body">
            <div class="help-text">
                <div class="help-title">Business Contacts Directory</div>
                <div class="help-content">
                    Use this directory to manage the business records that replace legacy asset vendors. Filter by tags and status to find the right contact before linking it to assets or maintenance.
                </div>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <form method="GET" action="{{ route('contacts.index') }}">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search business, person...">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <select name="tag_id" class="form-select">
                                        <option value="">All Tags</option>
                                        @foreach ($tags as $tag)
                                            <option value="{{ $tag->id }}" {{ (string) request('tag_id') === (string) $tag->id ? 'selected' : '' }}>{{ $tag->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <select name="status" class="form-select">
                                        <option value="">Any Status</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-6">
                                    <button type="button" class="btn btn-light w-100" onclick="window.location='{{ route('contacts.index') }}'">Reset</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <a href="{{ route('contacts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> New Contact
                    </a>
                </div>
            </div>

            <div class="contact-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Business</th>
                                    <th>Primary Contact</th>
                                    <th>Tags</th>
                                    <th>Usage</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($contacts as $contact)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $contact->name }}</div>
                                            <div class="small text-muted">{{ $contact->email ?: 'No email' }} | {{ $contact->phone ?: 'No phone' }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $contact->primary_person_label ?: 'No primary person' }}</div>
                                            <div class="small text-muted">{{ $contact->primaryPerson?->email ?: $contact->primaryPerson?->phone ?: 'No direct contact info' }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @forelse ($contact->tags as $tag)
                                                    <span class="tag-badge" style="background-color: {{ $tag->color ?: '#64748b' }};">{{ $tag->name }}</span>
                                                @empty
                                                    <span class="text-muted small">No tags</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="small">
                                            {{ $contact->assets_count }} assets<br>
                                            {{ $contact->maintenances_count }} maintenance records
                                        </td>
                                        <td>
                                            <span class="status-badge {{ $contact->is_active ? 'status-active' : 'status-inactive' }}">
                                                {{ $contact->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('contacts.show', $contact) }}" class="btn btn-sm btn-light">View</a>
                                                <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-sm btn-primary">Edit</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="text-center text-muted" style="padding: 40px 0;">
                                                <i class="fas fa-briefcase" style="font-size: 48px; opacity: 0.3;"></i>
                                                <p class="mt-3 mb-0" style="font-size: 15px;">No business contacts found.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="contacts-pagination mt-3">
                        {{ $contacts->onEachSide(1)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
