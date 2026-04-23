@extends('layouts.crm')

@section('title', $page . ' — Attendance')

@section('content')
    <div class="crm-shell-content">
        <section class="crm-summary-hero">
            <div class="crm-summary-hero-copy">
                <h1 class="crm-summary-hero-title">{{ $page }}</h1>
                <p class="crm-summary-hero-subtitle">This page is under construction and will be available soon.</p>
            </div>
        </section>

        <section class="crm-card" style="margin-top: 20px;">
            <div class="crm-card-title">
                <p class="crm-kicker">Attendance Module</p>
                <h2>Coming Soon</h2>
            </div>
            <div class="crm-empty">
                <i class="bx bx-fingerprint" style="font-size: 32px; color: #94a3b8; margin-bottom: 8px;"></i>
                <p>The Attendance module is currently being built. Check back soon.</p>
            </div>
        </section>
    </div>
@endsection
