@extends('layouts.crm')

@section('title', 'Create CRM User')
@section('crm_heading', 'Create User')
@section('crm_subheading', 'Add a new internal CRM account with the correct role and activation state.')

@section('crm_actions')
    <a href="{{ route('crm.users.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to users
    </a>
@endsection

@section('content')
    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">New user</p>
                <h2>Create an internal account</h2>
            </div>
        </div>

        @include('crm.users._form', [
            'action' => route('crm.users.store'),
            'method' => null,
            'submitLabel' => 'Create user',
            'submitIcon' => 'fas fa-save',
            'cancelUrl' => route('crm.users.index'),
        ])
    </section>
@endsection
