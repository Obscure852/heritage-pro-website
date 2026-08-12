@extends('layouts.crm')

@section('title', 'New Client Setup Invitation')
@section('crm_heading', 'New Client Setup Invitation')
@section('crm_subheading', 'Create a secure public link and connect the onboarding record to the right CRM context before it is sent.')

@section('content')
    <div class="crm-stack">
        <section class="crm-card">
            <div class="crm-card-title"><div><p class="crm-kicker">Invitation details</p><h2>Who should complete setup?</h2><p>The recipient receives a public link and verifies access by email before entering the wizard.</p></div></div>
            <form method="POST" action="{{ route('crm.client-setup.store') }}" class="crm-form">
                @csrf
                <div class="crm-field-grid cols-2">
                    <div class="crm-field"><label for="email">Client email <span class="text-danger">*</span></label><input id="email" name="email" type="email" value="{{ old('email') }}" required></div>
                    <div class="crm-field"><label for="contact_name">Contact name</label><input id="contact_name" name="contact_name" value="{{ old('contact_name') }}"></div>
                    <div class="crm-field"><label for="lead_id">Link to lead</label><select id="lead_id" name="lead_id"><option value="">No lead link</option>@foreach ($leads as $lead)<option value="{{ $lead->id }}" @selected(old('lead_id') == $lead->id)>{{ $lead->company_name }}</option>@endforeach</select></div>
                    <div class="crm-field"><label for="customer_id">Link to customer</label><select id="customer_id" name="customer_id"><option value="">No customer link</option>@foreach ($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->company_name }}</option>@endforeach</select></div>
                    <div class="crm-field"><label for="primary_contact_id">Primary contact</label><select id="primary_contact_id" name="primary_contact_id"><option value="">No contact link</option>@foreach ($contacts as $contact)<option value="{{ $contact->id }}" @selected(old('primary_contact_id') == $contact->id)>{{ $contact->name }}{{ $contact->email ? ' · '.$contact->email : '' }}</option>@endforeach</select></div>
                    <div class="crm-field"><label for="assigned_to_id">Implementation owner</label><select id="assigned_to_id" name="assigned_to_id"><option value="">Leave unassigned</option>@foreach ($owners as $owner)<option value="{{ $owner->id }}" @selected(old('assigned_to_id') == $owner->id)>{{ $owner->name }}</option>@endforeach</select></div>
                </div>
                <div class="form-actions"><a href="{{ route('crm.client-setup.index') }}" class="btn btn-light crm-btn-light">Cancel</a><button type="submit" class="btn btn-primary btn-loading"><span class="btn-text"><i class="bx bx-send"></i> Create and email link</span><span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...</span></button></div>
            </form>
        </section>
    </div>
@endsection
