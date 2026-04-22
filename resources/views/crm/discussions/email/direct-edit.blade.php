@extends('layouts.crm')

@section('title', 'Edit Email Draft')
@section('crm_heading', 'Email Discussions')
@section('crm_subheading', 'Edit the email draft on its own page until it is sent, then continue from the thread view.')

@section('crm_actions')
    <a href="{{ route('crm.discussions.email.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to email channel
    </a>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')
    @include('crm.discussions.partials.channel-nav', ['active' => 'email'])
    @include('crm.discussions.partials.external-direct-page', [
        'action' => route('crm.discussions.email.direct.update', $discussionThread),
        'method' => 'PATCH',
        'cancelUrl' => route('crm.discussions.email.index'),
        'discussionThread' => $discussionThread,
        'channelLabel' => $channelLabel,
        'sourceContext' => $sourceContext,
        'users' => $users,
        'leads' => $leads,
        'customers' => $customers,
        'contacts' => $contacts,
        'integrations' => $integrations,
    ])
@endsection
