@extends('layouts.master')

@section('title')
    Markbook
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Assessment Premium
        @endslot
        @slot('title')
            Markbook
        @endslot
    @endcomponent

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <h3 class="mb-2">Select Markbook Area</h3>
                        <p class="text-muted mb-0">
                            Choose the mark entry workspace you want to open for this combined-school setup.
                        </p>
                    </div>

                    <div class="row g-3">
                        @foreach ($contexts as $context)
                            <div class="col-md-6">
                                <a href="{{ $context['url'] }}" class="card h-100 text-decoration-none border shadow-sm">
                                    <div class="card-body">
                                        <h5 class="mb-2 text-dark">{{ $context['label'] }}</h5>
                                        <p class="mb-0 text-muted">{{ $context['description'] }}</p>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
