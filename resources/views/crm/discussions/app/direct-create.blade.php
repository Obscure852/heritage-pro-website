@extends('layouts.crm')

@section('title', 'New App Direct Message')
@section('crm_heading', 'App Messaging')
@section('crm_subheading', 'Start a one-to-one internal conversation on its own page instead of routing through a multi-purpose form.')

@section('crm_actions')
    <a href="{{ route('crm.discussions.app.workspace') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to workspace
    </a>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')
    @include('crm.discussions.partials.channel-nav', ['active' => 'app'])

    <div class="crm-stack">
        @include('crm.discussions.app.partials.mode-switch', ['active' => 'direct'])

        @include('crm.partials.helper-text', [
            'title' => 'Direct Messaging',
            'content' => 'Direct messages are one-to-one. Use the switch above whenever you need a multi-person app chat instead.',
        ])

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">New DM</p>
                    <h2>Start a direct conversation</h2>
                </div>
            </div>

            @include('crm.discussions.app.partials.direct-form', [
                'action' => route('crm.discussions.app.direct.store'),
                'cancelUrl' => route('crm.discussions.app.workspace'),
                'crmUsers' => $crmUsers,
                'sourceContext' => $sourceContext,
            ])
        </section>
    </div>
@endsection
