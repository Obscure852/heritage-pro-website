@extends('layouts.crm')

@section('title', $discussionThread->subject . ' - Email Discussion')
@section('crm_heading', 'Email Discussions')
@section('crm_subheading', 'Review a sent or queued email thread and reply without reopening the original draft form.')
@section('crm_shell_attributes', 'data-crm-active-discussion-thread="' . $discussionThread->id . '"')

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.discussions.email.index') }}" class="btn btn-light crm-btn-light">
            <i class="bx bx-arrow-back"></i> Back to email channel
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
    @include('crm.discussions.partials.channel-nav', ['active' => 'email'])
    @include('crm.discussions.partials.external-direct-show', [
        'discussionThread' => $discussionThread,
        'channelLabel' => $channelLabel,
        'deliveryStatuses' => $deliveryStatuses,
        'routeBase' => 'crm.discussions.email',
    ])
@endsection
