@extends('layouts.crm')

@section('title', 'CRM Contacts')
@section('crm_heading', 'Contacts')
@section('crm_subheading', 'Keep decision-makers, finance contacts, and institutional stakeholders linked to the right lead or customer account.')

@section('crm_actions')
    <a href="{{ route('crm.contacts.create') }}" class="btn btn-primary">
        <i class="bx bx-user-plus"></i> New contact
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find contacts</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.contacts.index') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="q">Search</label>
                        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Name, role, email, phone">
                    </div>
                    <div class="crm-field">
                        <label for="owner_id">Owner</label>
                        <select id="owner_id" name="owner_id">
                            <option value="">All owners</option>
                            @foreach ($owners as $owner)
                                <option value="{{ $owner->id }}" @selected($filters['owner_id'] !== '' && (int) $filters['owner_id'] === $owner->id)>{{ $owner->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="linked_to">Linked to</label>
                        <select id="linked_to" name="linked_to">
                            <option value="">All accounts</option>
                            <option value="lead" @selected($filters['linked_to'] === 'lead')>Lead</option>
                            <option value="customer" @selected($filters['linked_to'] === 'customer')>Customer</option>
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="primary">Primary</label>
                        <select id="primary" name="primary">
                            <option value="">All contacts</option>
                            <option value="1" @selected($filters['primary'] === '1')>Primary</option>
                            <option value="0" @selected($filters['primary'] === '0')>Secondary</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.contacts.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply filters</button>
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Contact book</p>
                    <h2>All linked contacts</h2>
                </div>
            </div>

            @if ($contacts->isEmpty())
                <div class="crm-empty">No contact records match the current filters.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Linked account</th>
                                <th>Owner</th>
                                <th>Primary</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contacts as $contact)
                                <tr>
                                    <td>
                                        <strong><a href="{{ route('crm.contacts.show', $contact) }}">{{ $contact->name }}</a></strong>
                                        <span class="crm-muted">{{ $contact->job_title ?: 'Role not set' }}</span>
                                    </td>
                                    <td>{{ $contact->customer?->company_name ?: $contact->lead?->company_name ?: 'Unassigned' }}</td>
                                    <td>{{ $contact->owner?->name ?: 'Unassigned' }}</td>
                                    <td>
                                        @if ($contact->is_primary)
                                            <span class="crm-pill primary">Primary</span>
                                        @else
                                            <span class="crm-pill muted">Secondary</span>
                                        @endif
                                    </td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            <a href="{{ route('crm.contacts.edit', $contact) }}" class="btn btn-secondary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            @include('crm.partials.delete-button', [
                                                'action' => route('crm.contacts.destroy', $contact),
                                                'message' => 'Are you sure you want to permanently delete this contact?',
                                                'label' => 'Delete',
                                            ])
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @include('crm.partials.pager', ['paginator' => $contacts])
            @endif
        </section>
    </div>
@endsection
