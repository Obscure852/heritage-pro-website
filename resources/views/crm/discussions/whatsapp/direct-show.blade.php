@extends('layouts.crm')

@section('title', $discussionThread->subject . ' - WhatsApp Discussion')
@section('crm_heading', 'WhatsApp Discussions')
@section('crm_subheading', 'Review queued or sent WhatsApp threads and continue the conversation from the thread view.')
@section('crm_shell_attributes', 'data-crm-active-discussion-thread="' . $discussionThread->id . '"')

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.discussions.whatsapp.index') }}" class="btn btn-light crm-btn-light">
            <i class="bx bx-arrow-back"></i> Back to WhatsApp channel
        </a>
        @include('crm.partials.delete-button', [
            'action' => route('crm.discussions.destroy', $discussionThread),
            'message' => 'Are you sure you want to permanently delete this discussion?',
            'label' => 'Delete discussion',
        ])
    </div>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')
    @include('crm.discussions.partials.channel-nav', ['active' => 'whatsapp'])
    @include('crm.discussions.partials.external-direct-show', [
        'discussionThread' => $discussionThread,
        'channelLabel' => $channelLabel,
        'deliveryStatuses' => $deliveryStatuses,
        'routeBase' => 'crm.discussions.whatsapp',
    ])
@endsection
