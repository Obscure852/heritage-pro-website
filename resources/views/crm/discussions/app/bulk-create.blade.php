@extends('layouts.crm')

@section('title', 'New App Group Chat')
@section('crm_heading', 'App Messaging')
@section('crm_subheading', 'Create a reusable internal group chat from selected users and departments.')

@section('crm_actions')
    <a href="{{ route('crm.discussions.app.workspace') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to workspace
    </a>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')
    @include('crm.discussions.partials.channel-nav', ['active' => 'app'])

    <div class="crm-stack">
        @include('crm.discussions.app.partials.mode-switch', ['active' => 'bulk'])

        @include('crm.partials.helper-text', [
            'title' => 'Group Chats',
            'content' => 'Bulk app messaging now creates a dedicated chat thread. Combine custom users with departments to build the member list, then send the opening message into that new group.',
        ])

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">New group chat</p>
                    <h2>Create an app messaging group</h2>
                </div>
            </div>

            @include('crm.discussions.app.partials.bulk-form', [
                'action' => route('crm.discussions.app.bulk.store'),
                'cancelUrl' => route('crm.discussions.app.workspace'),
                'crmUsers' => $crmUsers,
                'departments' => $departments,
                'sourceContext' => $sourceContext ?? null,
            ])
        </section>
    </div>
@endsection
