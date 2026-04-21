@extends('layouts.crm')

@section('title', $crmRequest->title . ' - Edit Request')
@section('crm_heading', $crmRequest->title)
@section('crm_subheading', 'Update the request details on a dedicated edit page while keeping the detail screen focused on timeline and summary.')

@section('crm_actions')
    <a href="{{ route('crm.requests.show', $crmRequest) }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to request
    </a>
@endsection

@section('content')
    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">Request details</p>
                <h2>Edit request</h2>
            </div>
        </div>

        @include('crm.requests._form', [
            'action' => route('crm.requests.update', $crmRequest),
            'method' => 'PATCH',
            'submitLabel' => 'Save changes',
            'submitIcon' => 'fas fa-save',
            'cancelUrl' => route('crm.requests.show', $crmRequest),
            'deleteUrl' => route('crm.requests.destroy', $crmRequest),
            'crmRequest' => $crmRequest,
        ])
    </section>
@endsection
