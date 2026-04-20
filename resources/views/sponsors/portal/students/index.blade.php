@extends('layouts.master-sponsor-portal')
@section('title')
    My Students - Sponsor Portal
@endsection

@section('css')
@include('sponsors.portal.partials.sponsor-portal-styles')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Sponsor Portal
        @endslot
        @slot('title')
            My Students
        @endslot
    @endcomponent

    <div class="sponsor-container">
        <div class="sponsor-header">
            <h4 class="mb-0">My Students</h4>
            <p class="mb-0 mt-1" style="opacity: 0.9;">View and manage your children's profiles</p>
        </div>

        <div class="sponsor-body">
            <div class="help-text mb-4">
                <div class="help-title">Student Profiles</div>
                <div class="help-content">
                    Click on any student card below to view their complete profile including personal information,
                    academic records, health information, and more.
                </div>
            </div>

            @if($students->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bx bx-user-x"></i>
                    </div>
                    <h5>No Students Found</h5>
                    <p>No students are currently linked to your account. Please contact the school administration if you believe this is an error.</p>
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
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 student-card">
                                <div class="card-body text-center p-4">
                                    <div class="student-avatar-lg mx-auto mb-3">
                                        @if($child->photo_path)
                                            <img src="{{ asset($child->photo_path) }}" alt="{{ $child->full_name }}" class="rounded-circle">
                                        @else
                                            <div class="avatar-initials">{{ $initials }}</div>
                                        @endif
                                    </div>
                                    <h5 class="card-title mb-1">{{ $child->full_name }}</h5>
                                    <p class="text-muted mb-3">
                                        <i class="bx bx-buildings me-1"></i>
                                        {{ $currentClass ? $currentClass->name : 'Class not assigned' }}
                                    </p>
                                    @if($currentClass && $currentClass->grade)
                                        <span class="badge bg-primary-subtle text-primary mb-3">
                                            {{ $currentClass->grade->name }}
                                        </span>
                                    @endif
                                    <div class="d-grid">
                                        <a href="{{ route('sponsor.student.show', $child->id) }}" class="btn btn-primary">
                                            <i class="bx bx-user me-1"></i> View Profile
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

@section('css')
@parent
<style>
    .student-card {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        transition: all 0.2s ease;
    }

    .student-card:hover {
        border-color: #4e73df;
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.15);
        transform: translateY(-2px);
    }

    .student-avatar-lg {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #e5e7eb;
    }

    .student-avatar-lg img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .student-avatar-lg .avatar-initials {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 600;
    }

    .bg-primary-subtle {
        background-color: rgba(78, 115, 223, 0.1);
    }
</style>
@endsection
