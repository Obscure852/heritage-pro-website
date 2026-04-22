@extends('layouts.crm')

@section('title', 'Edit WhatsApp Campaign')
@section('crm_heading', 'WhatsApp Discussions')
@section('crm_subheading', 'Keep WhatsApp campaign drafts and sent bulk runs separate from direct threads.')

@section('crm_actions')
    <a href="{{ route('crm.discussions.whatsapp.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to WhatsApp channel
    </a>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')
    @include('crm.discussions.partials.channel-nav', ['active' => 'whatsapp'])
    @include('crm.discussions.partials.external-bulk-page', [
        'action' => route('crm.discussions.whatsapp.bulk.update', $discussionCampaign),
        'method' => 'PATCH',
        'cancelUrl' => route('crm.discussions.whatsapp.index'),
        'discussionCampaign' => $discussionCampaign,
        'channelLabel' => $channelLabel,
        'routeBase' => 'crm.discussions.whatsapp',
        'sourceContext' => $sourceContext,
        'users' => $users,
        'leads' => $leads,
        'customers' => $customers,
        'contacts' => $contacts,
        'integrations' => $integrations,
    ])
@endsection
