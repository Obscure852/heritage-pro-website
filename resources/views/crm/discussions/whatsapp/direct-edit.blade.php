@extends('layouts.crm')

@section('title', 'Edit WhatsApp Draft')
@section('crm_heading', 'WhatsApp Discussions')
@section('crm_subheading', 'Edit the WhatsApp draft on a dedicated page until it is sent or queued.')

@section('crm_actions')
    <a href="{{ route('crm.discussions.whatsapp.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to WhatsApp channel
    </a>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')
    @include('crm.discussions.partials.channel-nav', ['active' => 'whatsapp'])
    @include('crm.discussions.partials.external-direct-page', [
        'action' => route('crm.discussions.whatsapp.direct.update', $discussionThread),
        'method' => 'PATCH',
        'cancelUrl' => route('crm.discussions.whatsapp.index'),
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
