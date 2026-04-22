@extends('layouts.crm')

@section('title', 'Share ' . $quote->quote_number)
@section('crm_heading', 'Share ' . $quote->quote_number)
@section('crm_subheading', 'Create an internal, email, or integration-backed discussion and attach the latest private PDF version of this quote.')

@section('crm_actions')
    <a href="{{ route('crm.products.quotes.show', $quote) }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to quote
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.products._tabs', ['activeSection' => 'quotes'])

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Quote delivery</p>
                    <h2>Share document</h2>
                    <p>Choose the dedicated channel flow for this quote. The latest private PDF is generated or refreshed automatically and reused by the selected composer.</p>
                </div>
            </div>

            @include('crm.discussions.partials.share-channel-picker', [
                'sourceType' => 'quote',
                'sourceId' => $quote->id,
                'sourceLabel' => 'Quote ' . $quote->quote_number,
                'backUrl' => route('crm.products.quotes.show', $quote),
            ])
        </section>
    </div>
@endsection
