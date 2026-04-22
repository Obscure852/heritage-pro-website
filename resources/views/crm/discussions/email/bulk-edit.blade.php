@extends('layouts.crm')

@section('title', 'Edit Email Campaign')
@section('crm_heading', 'Email Discussions')
@section('crm_subheading', 'Keep email campaign drafts and sent bulk runs separate from direct message pages.')

@section('crm_actions')
    <a href="{{ route('crm.discussions.email.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to email channel
    </a>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')
    @include('crm.discussions.partials.channel-nav', ['active' => 'email'])
    @include('crm.discussions.partials.external-bulk-page', [
        'action' => route('crm.discussions.email.bulk.update', $discussionCampaign),
        'method' => 'PATCH',
        'cancelUrl' => route('crm.discussions.email.index'),
        'discussionCampaign' => $discussionCampaign,
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
