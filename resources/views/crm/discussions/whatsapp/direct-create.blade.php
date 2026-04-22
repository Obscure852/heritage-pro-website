@extends('layouts.crm')

@section('title', 'New WhatsApp Discussion')
@section('crm_heading', 'WhatsApp Discussions')
@section('crm_subheading', 'Create a direct WhatsApp draft on its own page without mixing it with email or app forms.')

@section('crm_actions')
    <a href="{{ route('crm.discussions.whatsapp.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to WhatsApp channel
    </a>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')
    @include('crm.discussions.partials.channel-nav', ['active' => 'whatsapp'])
    @include('crm.discussions.partials.external-direct-page', [
        'action' => route('crm.discussions.whatsapp.direct.store'),
        'cancelUrl' => route('crm.discussions.whatsapp.index'),
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
