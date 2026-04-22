@extends('layouts.crm')

@section('title', $stage->name . ' - Edit Sales Stage')
@section('crm_heading', 'Settings')
@section('crm_subheading', 'Update the sales stage on a dedicated edit page while keeping the stage list focused on filtering and review.')

@section('crm_actions')
    <a href="{{ route('crm.settings.sales-stages') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to sales stages
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.settings._tabs', ['activeSection' => 'sales-stages'])

        @include('crm.partials.helper-text', [
            'title' => 'Edit Sales Stage',
            'content' => 'Update the stage definition here, then return to the stage list to confirm ordering, terminal flags, and usage.',
        ])

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Sales stage</p>
                    <h2>Edit {{ $stage->name }}</h2>
                </div>
            </div>

            @include('crm.settings.sales-stages._form', [
                'action' => route('crm.settings.sales-stages.update', $stage),
                'method' => 'PATCH',
                'submitLabel' => 'Save changes',
                'submitIcon' => 'fas fa-save',
                'cancelUrl' => route('crm.settings.sales-stages'),
                'deleteUrl' => route('crm.settings.sales-stages.destroy', $stage),
                'stage' => $stage,
            ])
        </section>
    </div>
@endsection
