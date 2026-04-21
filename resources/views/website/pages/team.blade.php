@extends('layouts.website-master')

@section('content')
    @include('website.partials.nav')
    @include('website.partials.hero', ['hero' => $pageConfig['hero']])
    @include('website.sections.team-page')
    @include('website.partials.contact')
    @include('website.partials.footer')
@endsection
