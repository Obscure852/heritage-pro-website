@extends('layouts.master-sponsor-portal')
@section('title')
    Assessment - Sponsor Portal
@endsection

@section('css')
@include('sponsors.portal.partials.sponsor-portal-styles')
<style>
    .student-assessment-card {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        transition: all 0.2s ease;
        height: 100%;
        text-decoration: none;
        display: block;
        color: inherit;
    }

    .student-assessment-card:hover {
        border-color: #4e73df;
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.15);
        transform: translateY(-2px);
        text-decoration: none;
        color: inherit;
    }

    .student-avatar-lg {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 24px;
        overflow: hidden;
        margin: 0 auto 16px;
    }

    .student-avatar-lg img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .student-card-name {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .student-card-class {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 12px;
    }

    .student-card-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #4e73df;
        font-size: 13px;
        font-weight: 500;
    }

    .student-assessment-card:hover .student-card-action {
        text-decoration: underline;
    }
</style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Sponsor Portal
        @endslot
        @slot('title')
            Assessment
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <!-- Page Container -->
    <div class="sponsor-container">
        <!-- Page Header -->
        <div class="sponsor-header">
            <h3>Assessment</h3>
            <p>View continuous assessments and exam results</p>
        </div>

        <div class="sponsor-body">
            <!-- Help Text -->
            <div class="help-text">
                <div class="help-title">Select a Student</div>
                <div class="help-content">
                    Choose a student below to view their assessment records, including continuous assessments (monthly tests) and end-of-term examinations.
                </div>
            </div>

            @if($students->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bx bx-user-x"></i>
                    </div>
                    <h5>No Students Found</h5>
                    <p>No students are linked to your account. Please contact the school administration.</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach($students as $child)
                        @php
                            $nameParts = explode(' ', $child->full_name);
                            $initials = '';
                            foreach (array_slice($nameParts, 0, 2) as $part) {
                                $initials .= strtoupper(substr($part, 0, 1));
                            }
                            $currentClass = $child->currentClassRelation ? $child->currentClassRelation->first() : null;
                        @endphp
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ route('sponsor.assessment.student', $child->id) }}" class="student-assessment-card">
                                <div class="p-4 text-center">
                                    <div class="student-avatar-lg">
                                        @if($child->photo_path)
                                            <img src="{{ asset($child->photo_path) }}" alt="{{ $child->full_name }}">
                                        @else
                                            {{ $initials }}
                                        @endif
                                    </div>
                                    <div class="student-card-name">{{ $child->full_name }}</div>
                                    <div class="student-card-class">
                                        <i class="bx bx-buildings me-1"></i>
                                        {{ $currentClass ? $currentClass->name : 'Class not assigned' }}
                                        @if($currentClass && $currentClass->grade)
                                            <span class="mx-1">|</span>
                                            {{ $currentClass->grade->name }}
                                        @endif
                                    </div>
                                    <div class="student-card-action">
                                        <i class="bx bx-bar-chart-alt-2"></i>
                                        View Assessment
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="sponsor-footer">
            <i class="bx bx-calendar"></i>
            <span>Term {{ $currentTerm->term }}, {{ $currentTerm->year }}</span>
        </div>
    </div>
@endsection
