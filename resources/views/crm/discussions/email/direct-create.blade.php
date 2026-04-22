@extends('layouts.crm')

@section('title', 'New Email Discussion')
@section('crm_heading', 'Email Discussions')
@section('crm_subheading', 'Create a direct outbound email on its own page without sharing the form with other channels.')

@section('crm_actions')
    <a href="{{ route('crm.discussions.email.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to email channel
    </a>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')
    @include('crm.discussions.partials.channel-nav', ['active' => 'email'])
    @include('crm.discussions.partials.external-direct-page', [
        'action' => route('crm.discussions.email.direct.store'),
        'cancelUrl' => route('crm.discussions.email.index'),
        'discussionThread' => $discussionThread ?? null,
        'channelLabel' => $channelLabel,
        'sourceContext' => $sourceContext,
        'users' => $users,
        'leads' => $leads,
        'customers' => $customers,
        'contacts' => $contacts,
        'integrations' => $integrations,
    ])
@endsection
