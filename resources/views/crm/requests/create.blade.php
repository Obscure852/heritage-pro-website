@extends('layouts.crm')

@section('title', 'Create Request')
@section('crm_heading', 'Choose Request Type')
@section('crm_subheading', 'Start the right workflow from the beginning: use a lead sales form for pre-sale work and a customer support form for post-sale service.')

@section('crm_actions')
    <a href="{{ route('crm.requests.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to requests
    </a>
@endsection

@section('content')
    <div class="crm-choice-grid">
        <section class="crm-card crm-choice-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Lead workflow</p>
                    <h2>Sales request</h2>
                    <p>Use this when a lead is still moving through calls, demos, proposals, procurement, and purchase decisions.</p>
                </div>
            </div>

            <div class="crm-choice-list">
                <span><i class="bx bx-check-circle"></i> Cold calls and outreach</span>
                <span><i class="bx bx-check-circle"></i> Demo and follow-up scheduling</span>
                <span><i class="bx bx-check-circle"></i> Proposal and procurement tracking</span>
            </div>

            <div class="form-actions">
                <a href="{{ route('crm.requests.sales.create') }}" class="btn btn-primary">
                    <i class="bx bx-line-chart"></i> Create Sales Request
                </a>
            </div>
        </section>

        <section class="crm-card crm-choice-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Customer workflow</p>
                    <h2>Support request</h2>
                    <p>Use this when a customer needs post-sale help, account follow-up, issue resolution, or an enhancement request.</p>
                </div>
            </div>

            <div class="crm-choice-list">
                <span><i class="bx bx-check-circle"></i> Support incidents and service follow-up</span>
                <span><i class="bx bx-check-circle"></i> Operational account requests</span>
                <span><i class="bx bx-check-circle"></i> Improvement and enhancement asks</span>
            </div>

            <div class="form-actions">
                <a href="{{ route('crm.requests.support.create') }}" class="btn btn-primary">
                    <i class="bx bx-support"></i> Create Support Request
                </a>
            </div>
        </section>
    </div>
@endsection
