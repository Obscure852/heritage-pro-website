@extends('layouts.crm')

@section('title', 'New Email Campaign')
@section('crm_heading', 'Email Discussions')
@section('crm_subheading', 'Create an email bulk campaign on its own page with a dedicated audience snapshot.')

@section('crm_actions')
    <a href="{{ route('crm.discussions.email.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to email channel
    </a>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')
    @include('crm.discussions.partials.channel-nav', ['active' => 'email'])
    @include('crm.discussions.partials.external-bulk-page', [
        'action' => route('crm.discussions.email.bulk.store'),
        'cancelUrl' => route('crm.discussions.email.index'),
        'discussionCampaign' => $discussionCampaign ?? null,
        'channelLabel' => $channelLabel,
        'routeBase' => 'crm.discussions.email',
        'sourceContext' => $sourceContext,
        'users' => $users,
        'leads' => $leads,
        'customers' => $customers,
        'contacts' => $contacts,
        'integrations' => $integrations,
    ])
@endsection
