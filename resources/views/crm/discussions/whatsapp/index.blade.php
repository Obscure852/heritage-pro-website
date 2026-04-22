@extends('layouts.crm')

@section('title', 'WhatsApp Discussions')
@section('crm_heading', 'WhatsApp Discussions')
@section('crm_subheading', 'Channel-specific direct and bulk WhatsApp workflows with separate draft and thread pages.')

@section('crm_header_stats')
    @include('crm.partials.header-stat', ['value' => $threads->total(), 'label' => 'THREADS'])
    @include('crm.partials.header-stat', ['value' => $campaigns->count(), 'label' => 'RECENT CAMPAIGNS'])
    @include('crm.partials.header-stat', ['value' => $threads->where('status', 'draft')->count(), 'label' => 'OPEN DRAFTS'])
@endsection

@section('crm_actions')
    <div class="crm-action-row">
        <a href="{{ route('crm.discussions.whatsapp.direct.create') }}" class="btn btn-primary">
            <i class="bx bxl-whatsapp"></i> New direct WhatsApp
        </a>
        <a href="{{ route('crm.discussions.whatsapp.bulk.create') }}" class="btn btn-light crm-btn-light">
            <i class="bx bx-layer-plus"></i> New bulk WhatsApp
        </a>
    </div>
@endsection

@section('content')
    @include('crm.discussions.partials.channel-styles')
    @include('crm.discussions.partials.channel-nav', ['active' => 'whatsapp'])
    @include('crm.discussions.partials.external-index', [
        'channelLabel' => $channelLabel,
        'threads' => $threads,
        'campaigns' => $campaigns,
        'deliveryStatuses' => $deliveryStatuses,
        'routeBase' => 'crm.discussions.whatsapp',
    ])
@endsection
