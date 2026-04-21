@extends('layouts.crm')

@section('title', $contact->name . ' - Contact')
@section('crm_heading', $contact->name)
@section('crm_subheading', 'Contact record with ownership, linked account information, and notes for the sales or support team.')

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.contacts.edit', $contact) }}" class="btn btn-secondary">
            <i class="fas fa-edit"></i> Edit contact
        </a>
        @include('crm.partials.delete-button', [
            'action' => route('crm.contacts.destroy', $contact),
            'message' => 'Are you sure you want to permanently delete this contact?',
            'label' => 'Delete contact',
        ])
    </div>
@endsection

@section('content')
    @php
        $initials = collect(preg_split('/\s+/', trim($contact->name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
            ->implode('');
    @endphp

    <div class="crm-stack">
        <section class="crm-card">
            <div class="crm-detail-hero">
                <span class="crm-initial-avatar crm-initial-avatar-lg">{{ $initials ?: 'C' }}</span>
                <div class="crm-detail-hero-copy">
                    <p class="crm-kicker">Contact profile</p>
                    <h2>{{ $contact->name }}</h2>
                    <p>{{ $contact->job_title ?: 'Role not set' }}{{ $contact->owner?->name ? ' · Owned by ' . $contact->owner->name : '' }}</p>
                    <div class="crm-inline">
                        @if ($contact->is_primary)
                            <span class="crm-pill primary">Primary contact</span>
                        @endif
                        <span class="crm-pill muted">{{ $contact->email ?: 'No email set' }}</span>
                        <span class="crm-pill muted">{{ $contact->phone ?: 'No phone set' }}</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="crm-grid cols-2 crm-detail-grid">
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Contact summary</p>
                        <h2>Record details</h2>
                    </div>
                </div>

                <div class="crm-meta-list">
                    <div class="crm-meta-row">
                        <span>Full name</span>
                        <strong>{{ $contact->name }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Role</span>
                        <strong>{{ $contact->job_title ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Email</span>
                        <strong>{{ $contact->email ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Phone</span>
                        <strong>{{ $contact->phone ?: 'Not set' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Owner</span>
                        <strong>{{ $contact->owner?->name ?: 'Unassigned' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Primary contact</span>
                        <strong>{{ $contact->is_primary ? 'Yes' : 'No' }}</strong>
                    </div>
                </div>
            </section>

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Relationships</p>
                        <h2>Linked account context</h2>
                    </div>
                </div>

                <div class="crm-meta-list">
                    <div class="crm-meta-row">
                        <span>Lead link</span>
                        <strong>{{ $contact->lead?->company_name ?: 'None' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Customer link</span>
                        <strong>{{ $contact->customer?->company_name ?: 'None' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Created</span>
                        <strong>{{ $contact->created_at?->format('d M Y H:i') ?: 'Unknown' }}</strong>
                    </div>
                    <div class="crm-meta-row">
                        <span>Updated</span>
                        <strong>{{ $contact->updated_at?->format('d M Y H:i') ?: 'Unknown' }}</strong>
                    </div>
                </div>
            </section>
        </div>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Notes</p>
                    <h2>Internal context</h2>
                </div>
            </div>

            <div class="crm-note-panel">{{ $contact->notes ?: 'No notes have been added for this contact yet.' }}</div>
        </section>
    </div>
@endsection
