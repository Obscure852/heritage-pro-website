@extends('layouts.crm')

@section('title', 'Create Discussion')
@section('crm_heading', 'Create Discussion')
@section('crm_subheading', 'Start a new internal, email, or WhatsApp-ready conversation on a dedicated page instead of inside the thread list.')

@section('crm_actions')
    <a href="{{ route('crm.discussions.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to discussions
    </a>
@endsection

@section('content')
    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">New discussion</p>
                <h2>Start a conversation</h2>
            </div>
        </div>

        @include('crm.discussions._form', [
            'action' => route('crm.discussions.store'),
            'submitLabel' => 'Start discussion',
            'submitIcon' => 'bx bx-send',
            'cancelUrl' => route('crm.discussions.index'),
        ])
    </section>
@endsection
