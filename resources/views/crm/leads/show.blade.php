@extends('layouts.crm')

@section('title', $lead->company_name . ' - Lead')
@section('crm_heading', $lead->company_name)
@section('crm_subheading', 'Lead record with owner assignment, notes, linked contacts, and request history preserved for eventual conversion.')

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.leads.edit', $lead) }}" class="btn btn-secondary">
            <i class="fas fa-edit"></i> Edit lead
        </a>
        @include('crm.partials.delete-button', [
            'action' => route('crm.leads.destroy', $lead),
            'message' => 'Are you sure you want to permanently delete this lead?',
            'label' => 'Delete lead',
        ])
    </div>
    @if ($customer)
        <a href="{{ route('crm.customers.show', $customer) }}" class="btn btn-primary"><i class="fas fa-external-link-alt"></i> Open converted customer</a>
    @else
        <form method="POST" action="{{ route('crm.leads.convert', $lead) }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-loading">
                <span class="btn-text"><i class="bx bx-transfer-alt"></i> {{ $lead->converted_at ? 'Recreate customer' : 'Convert to customer' }}</span>
                <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>{{ $lead->converted_at ? 'Recreating...' : 'Converting...' }}</span>
            </button>
        </form>
    @endif
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.customer-workspace-tabs')

        <div class="crm-grid cols-2">
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Lead summary</p>
                        <h2>Record details</h2>
                    </div>
                </div>

                <div class="crm-meta-list">
                    <div class="crm-meta-row">
                        <span>Institution</span>
                        <strong>{{ $lead->company_name }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Sector</span>
                        <strong>{{ $lead->industry ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Email</span>
                        <strong>{{ $lead->email ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Phone</span>
                        <strong>{{ $lead->phone ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Fax</span>
                        <strong>{{ $lead->fax ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Website</span>
                        <strong>{{ $lead->website ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Country</span>
                        <strong>{{ $lead->country ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Region</span>
                        <strong>{{ $lead->region ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Location</span>
                        <strong>{{ $lead->location ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>P.O. Box address</span>
                        <strong>{{ $lead->postal_address ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Owner</span>
                        <strong>{{ $lead->owner?->name ?: 'Unassigned' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Status</span>
                        <strong>{{ $leadStatuses[$lead->status] ?? ucfirst($lead->status) }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Converted</span>
                        <strong>{{ $lead->converted_at?->format('d M Y H:i') ?: 'Not yet' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Notes</span>
                        <strong>{{ $lead->notes ?: 'None' }}</strong>
                    </div>
                </div>
            </section>

            <div class="crm-stack">
                <section class="crm-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Contacts</p>
                            <h2>Linked people</h2>
                        </div>
                    </div>

                    @if ($lead->contacts->isEmpty())
                        <div class="crm-empty">No contacts linked to this lead yet.</div>
                    @else
                        <div class="crm-list">
                            @foreach ($lead->contacts as $contact)
                                <div class="crm-list-item">
                                    <h4><a href="{{ route('crm.contacts.show', $contact) }}">{{ $contact->name }}</a></h4>
                                    <p>{{ $contact->job_title ?: 'Role not set' }}</p>
                                    <div class="crm-inline">
                                        @if ($contact->is_primary)
                                            <span class="crm-pill primary">Primary</span>
                                        @endif
                                        <span class="crm-muted-copy">{{ $contact->email ?: 'No email' }}</span>
                                        <span class="crm-muted-copy">{{ $contact->phone ?: 'No phone' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="crm-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Requests</p>
                            <h2>Linked sales and support work</h2>
                        </div>
                    </div>

                    @if ($lead->requests->isEmpty())
                        <div class="crm-empty">No requests linked to this lead yet.</div>
                    @else
                        <div class="crm-table-wrap">
                            <table class="crm-table">
                                <thead>
                                    <tr>
                                        <th>Request</th>
                                        <th>Owner</th>
                                        <th>State</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lead->requests as $request)
                                        <tr>
                                            <td>
                                                <strong><a href="{{ route('crm.requests.show', $request) }}">{{ $request->title }}</a></strong>
                                                <span class="crm-muted">{{ ucfirst($request->type) }}</span>
                                            </td>
                                            <td>{{ $request->owner?->name ?: 'Unassigned' }}</td>
                                            <td>{{ $request->type === 'sales' ? ($request->salesStage?->name ?: 'No stage') : ucfirst(str_replace('_', ' ', $request->support_status ?: 'open')) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

                @include('crm.products._related_documents', [
                    'quotes' => $lead->quotes,
                    'invoices' => $lead->invoices,
                    'quoteStatuses' => $quoteStatuses,
                    'invoiceStatuses' => $invoiceStatuses,
                    'title' => 'Related quotes and invoices',
                    'subtitle' => 'Commercial documents linked directly to this lead.',
                ])
            </div>
        </div>
    </div>
@endsection
