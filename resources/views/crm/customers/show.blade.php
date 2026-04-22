@extends('layouts.crm')

@section('title', $customer->company_name . ' - Customer')
@section('crm_heading', $customer->company_name)
@section('crm_subheading', 'Customer record with linked contacts, inherited lead history, and active sales or support requests.')

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.customers.edit', $customer) }}" class="btn btn-secondary">
            <i class="fas fa-edit"></i> Edit customer
        </a>
        @include('crm.partials.delete-button', [
            'action' => route('crm.customers.destroy', $customer),
            'message' => 'Are you sure you want to permanently delete this customer?',
            'label' => 'Delete customer',
        ])
    </div>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.customer-workspace-tabs')

        <div class="crm-grid cols-2">
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Customer summary</p>
                        <h2>Record details</h2>
                    </div>
                </div>

                <div class="crm-meta-list">
                    <div class="crm-meta-row">
                        <span>Institution</span>
                        <strong>{{ $customer->company_name }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Sector</span>
                        <strong>{{ $customer->industry ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Email</span>
                        <strong>{{ $customer->email ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Phone</span>
                        <strong>{{ $customer->phone ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Website</span>
                        <strong>{{ $customer->website ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Country</span>
                        <strong>{{ $customer->country ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Status</span>
                        <strong>{{ $customerStatuses[$customer->status] ?? ucfirst($customer->status) }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Owner</span>
                        <strong>{{ $customer->owner?->name ?: 'Unassigned' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Source lead</span>
                        <strong>
                            @if ($customer->lead)
                                <a href="{{ route('crm.leads.show', $customer->lead) }}">{{ $customer->lead->company_name }}</a>
                            @else
                                Source lead unavailable
                            @endif
                        </strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Purchase date</span>
                        <strong>{{ $customer->purchased_at?->format('d M Y') ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Notes</span>
                        <strong>{{ $customer->notes ?: 'None' }}</strong>
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

                    @if ($customer->contacts->isEmpty())
                        <div class="crm-empty">No contacts linked to this customer yet.</div>
                    @else
                        <div class="crm-list">
                            @foreach ($customer->contacts as $contact)
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

                    @if ($customer->requests->isEmpty())
                        <div class="crm-empty">No requests linked to this customer yet.</div>
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
                                    @foreach ($customer->requests as $request)
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
                    'quotes' => $customer->quotes,
                    'invoices' => $customer->invoices,
                    'quoteStatuses' => $quoteStatuses,
                    'invoiceStatuses' => $invoiceStatuses,
                    'title' => 'Related quotes and invoices',
                    'subtitle' => 'Commercial documents linked directly to this customer.',
                ])
            </div>
        </div>
    </div>
@endsection
