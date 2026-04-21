@extends('layouts.crm')

@section('title', 'Create Contact')
@section('crm_heading', 'Create Contact')
@section('crm_subheading', 'Add a new decision-maker or stakeholder on its own form page instead of inside the contacts listing.')

@section('crm_actions')
    <a href="{{ route('crm.contacts.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to contacts
    </a>
@endsection

@section('content')
    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">New contact</p>
                <h2>Add a contact</h2>
            </div>
        </div>

        @include('crm.contacts._form', [
            'action' => route('crm.contacts.store'),
            'method' => null,
            'submitLabel' => 'Save contact',
            'submitIcon' => 'fas fa-save',
            'cancelUrl' => route('crm.contacts.index'),
        ])
    </section>
@endsection
