@extends('layouts.crm')

@section('title', $contact->name . ' - Edit Contact')
@section('crm_heading', $contact->name)
@section('crm_subheading', 'Update the contact record on a dedicated edit page while keeping the detail screen focused on summary and account context.')

@section('crm_actions')
    <a href="{{ route('crm.contacts.show', $contact) }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to contact
    </a>
@endsection

@section('content')
    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">Contact details</p>
                <h2>Edit contact</h2>
            </div>
        </div>

        @include('crm.contacts._form', [
            'action' => route('crm.contacts.update', $contact),
            'method' => 'PATCH',
            'submitLabel' => 'Save changes',
            'submitIcon' => 'fas fa-save',
            'cancelUrl' => route('crm.contacts.show', $contact),
            'deleteUrl' => route('crm.contacts.destroy', $contact),
            'contact' => $contact,
        ])
    </section>
@endsection
