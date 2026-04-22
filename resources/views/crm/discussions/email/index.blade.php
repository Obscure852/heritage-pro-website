@extends('layouts.crm')

@section('title', 'Email Discussions')
@section('crm_heading', 'Email Discussions')
@section('crm_subheading', 'Channel-specific direct and bulk email workflows with dedicated draft and show pages.')

@section('crm_header_stats')
    @include('crm.partials.header-stat', ['value' => $threads->total(), 'label' => 'THREADS'])
    @include('crm.partials.header-stat', ['value' => $campaigns->count(), 'label' => 'RECENT CAMPAIGNS'])
    @include('crm.partials.header-stat', ['value' => $threads->where('status', 'draft')->count(), 'label' => 'OPEN DRAFTS'])
@endsection

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.discussions.email.direct.create') }}" class="btn btn-primary">
            <i class="bx bx-envelope"></i> New direct email
        </a>
        <a href="{{ route('crm.discussions.email.bulk.create') }}" class="btn btn-light crm-btn-light">
            <i class="bx bx-layer-plus"></i> New bulk email
        </a>
    </div>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')
    @include('crm.discussions.partials.channel-nav', ['active' => 'email'])
    @include('crm.discussions.partials.external-index', [
        'channelLabel' => $channelLabel,
        'threads' => $threads,
        'campaigns' => $campaigns,
        'deliveryStatuses' => $deliveryStatuses,
        'routeBase' => 'crm.discussions.email',
    ])
@endsection
