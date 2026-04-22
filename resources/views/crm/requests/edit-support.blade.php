@extends('layouts.crm')

@section('title', $crmRequest->title . ' - Edit Support Request')
@section('crm_heading', $crmRequest->title)
@section('crm_subheading', 'Update this customer support request on its own dedicated support form.')

@section('crm_actions')
    <a href="{{ route('crm.requests.show', $crmRequest) }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to request
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Customer support workflow</p>
                    <h2>Edit support request</h2>
                </div>
            </div>

            @include('crm.requests._support_form', [
                'action' => route('crm.requests.update', $crmRequest),
                'method' => 'PATCH',
                'submitLabel' => 'Save changes',
                'submitIcon' => 'fas fa-save',
                'cancelUrl' => route('crm.requests.show', $crmRequest),
                'deleteUrl' => route('crm.requests.destroy', $crmRequest),
                'crmRequest' => $crmRequest,
            ])
        </section>

        @include('crm.requests._attachments', [
            'crmRequest' => $crmRequest,
            'attachments' => $crmRequest->attachments,
            'allowDelete' => true,
            'title' => 'Current request files',
            'subtitle' => 'Open or remove PDF and DOCX files already attached to this support request.',
        ])
    </div>
@endsection
