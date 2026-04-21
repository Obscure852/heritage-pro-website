@extends('layouts.crm')

@section('title', $developmentRequest->title . ' - Edit Dev Request')
@section('crm_heading', $developmentRequest->title)
@section('crm_subheading', 'Update the development request on a dedicated edit page while keeping the detail page focused on context and status.')

@section('crm_actions')
    <a href="{{ route('crm.dev.show', $developmentRequest) }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to dev item
    </a>
@endsection

@section('content')
    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">Dev request</p>
                <h2>Edit backlog item</h2>
            </div>
        </div>

        @include('crm.dev._form', [
            'action' => route('crm.dev.update', $developmentRequest),
            'method' => 'PATCH',
            'submitLabel' => 'Save changes',
            'submitIcon' => 'fas fa-save',
            'cancelUrl' => route('crm.dev.show', $developmentRequest),
            'deleteUrl' => route('crm.dev.destroy', $developmentRequest),
            'developmentRequest' => $developmentRequest,
        ])
    </section>
@endsection
