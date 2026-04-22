@extends('layouts.crm')

@section('title', 'Create Quote')
@section('crm_heading', 'Create Quote')
@section('crm_subheading', 'Build a commercial quote for a lead or customer using catalog defaults and custom line items.')

@section('crm_actions')
    <a href="{{ route('crm.products.quotes.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to quotes
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.products._tabs', ['activeSection' => 'quotes'])

        @include('crm.products.quotes._form', [
            'action' => route('crm.products.quotes.store'),
            'method' => null,
            'submitLabel' => 'Save quote',
            'submitIcon' => 'fas fa-save',
            'cancelUrl' => route('crm.products.quotes.index'),
        ])
    </div>
@endsection
