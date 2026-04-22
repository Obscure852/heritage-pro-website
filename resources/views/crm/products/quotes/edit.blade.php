@extends('layouts.crm')

@section('title', $quote->quote_number . ' - Edit Quote')
@section('crm_heading', $quote->quote_number)
@section('crm_subheading', 'Update this quote while it remains in an editable workflow state.')

@section('crm_actions')
    <a href="{{ route('crm.products.quotes.show', $quote) }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to quote
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.products._tabs', ['activeSection' => 'quotes'])

        @include('crm.products.quotes._form', [
            'action' => route('crm.products.quotes.update', $quote),
            'method' => 'PATCH',
            'submitLabel' => 'Save changes',
            'submitIcon' => 'fas fa-save',
            'cancelUrl' => route('crm.products.quotes.show', $quote),
        ])
    </div>
@endsection
