@extends('layouts.crm')

@section('title', 'Create Request')
@section('crm_heading', 'Create Request')
@section('crm_subheading', 'Create a sales or support request on its own page instead of inside the requests listing.')

@section('crm_actions')
    <a href="{{ route('crm.requests.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to requests
    </a>
@endsection

@section('content')
    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">New request</p>
                <h2>Create sales or support work</h2>
            </div>
        </div>

        @include('crm.requests._form', [
            'action' => route('crm.requests.store'),
            'method' => null,
            'submitLabel' => 'Save request',
            'submitIcon' => 'fas fa-save',
            'cancelUrl' => route('crm.requests.index'),
        ])
    </section>
@endsection
