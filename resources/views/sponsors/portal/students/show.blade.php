@extends('layouts.master-sponsor-portal')
@section('title')
    {{ $student->full_name }} - Sponsor Portal
@endsection

@section('css')
@include('sponsors.portal.partials.sponsor-portal-styles')
<style>
    .profile-header-card {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        border-radius: 3px 3px 0 0;
        padding: 24px;
        color: white;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 3px solid rgba(255,255,255,0.3);
        overflow: hidden;
        background: rgba(255,255,255,0.2);
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-avatar .initials {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 600;
        color: white;
    }

    .status-badge-lg {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-current { background: rgba(255,255,255,0.2); color: white; }
    .status-left { background: #fee2e2; color: #991b1b; }
    .status-graduated { background: #dbeafe; color: #1e40af; }
    .status-suspended { background: #fecaca; color: #dc2626; }

    .profile-tabs {
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
        padding: 0 16px;
    }

    .profile-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        background: none;
        color: #6b7280;
        font-weight: 500;
        padding: 14px 20px;
        border-radius: 0;
        transition: all 0.2s;
    }

    .profile-tabs .nav-link:hover {
        color: #4e73df;
        background: rgba(78, 115, 223, 0.05);
    }

    .profile-tabs .nav-link.active {
        color: #4e73df;
        border-bottom-color: #4e73df;
        background: white;
    }

    .profile-tabs .nav-link i {
        margin-right: 6px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }

    .info-item {
        padding: 12px 16px;
        background: #f9fafb;
        border-radius: 3px;
        border-left: 3px solid #4e73df;
    }

    .info-item .label {
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .info-item .value {
        font-size: 15px;
        color: #1f2937;
        font-weight: 500;
    }

    .section-divider {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin: 24px 0 16px 0;
        padding-bottom: 8px;
        border-bottom: 2px solid #e5e7eb;
    }
</style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Sponsor Portal
        @endslot
        @slot('li_2')
            Students
        @endslot
        @slot('title')
            {{ $student->full_name }}
        @endslot
    @endcomponent

    @php
        $nameParts = explode(' ', $student->full_name);
        $initials = '';
        foreach (array_slice($nameParts, 0, 2) as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        $currentClass = $student->currentClassRelation ? $student->currentClassRelation->first() : null;
        $statusClass = strtolower($student->status ?? 'current');
    @endphp

    <div class="card border-0 shadow-sm">
        {{-- Profile Header --}}
        <div class="profile-header-card">
            <div class="d-flex align-items-center gap-4">
                <div class="profile-avatar">
                    @if($student->photo_path)
                        <img src="{{ asset($student->photo_path) }}" alt="{{ $student->full_name }}">
                    @else
                        <div class="initials">{{ $initials }}</div>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <h4 class="mb-0">{{ $student->full_name }}</h4>
                        <span class="status-badge-lg status-{{ $statusClass }}">
                            {{ $student->status ?? 'Current' }}
                        </span>
                    </div>
                    <p class="mb-0" style="opacity: 0.9;">
                        <i class="bx bx-buildings me-1"></i>
                        {{ $currentClass ? $currentClass->name : 'Class not assigned' }}
                        @if($currentClass && $currentClass->grade)
                            <span class="mx-2">|</span>
                            <i class="bx bx-graduation me-1"></i>
                            {{ $currentClass->grade->name }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Profile Tabs --}}
@php
    $studentDriver = $schoolModeResolver->assessmentDriverForLevel(
        $schoolModeResolver->levelForKlass($currentClass)
    );
@endphp

        <ul class="nav profile-tabs" id="profileTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#personal" type="button">
                    <i class="bx bx-user"></i> Personal Info
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#books" type="button">
                    <i class="bx bx-book"></i> Books
                </button>
            </li>
            @if($studentDriver === 'junior')
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#psle" type="button">
                        <i class="bx bx-medal"></i> PSLE
                    </button>
                </li>
            @elseif($studentDriver === 'senior')
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#jce" type="button">
                        <i class="bx bx-medal"></i> JCE
                    </button>
                </li>
            @endif
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#health" type="button">
                    <i class="bx bx-plus-medical"></i> Health
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#behavior" type="button">
                    <i class="bx bx-user-voice"></i> Behavior
                </button>
            </li>
        </ul>

        {{-- Tab Content --}}
        <div class="tab-content p-4" id="profileTabContent">
            {{-- Personal Info Tab --}}
            <div class="tab-pane fade show active" id="personal" role="tabpanel">
                @include('sponsors.portal.students.partials.personal-info', ['student' => $student, 'currentClass' => $currentClass])
            </div>

            {{-- Books Tab --}}
            <div class="tab-pane fade" id="books" role="tabpanel">
                @include('sponsors.portal.students.partials.books', ['student' => $student])
            </div>

            {{-- PSLE Tab (Junior) --}}
            @if($studentDriver === 'junior')
                <div class="tab-pane fade" id="psle" role="tabpanel">
                    @include('sponsors.portal.students.partials.external-exams', ['student' => $student, 'examType' => 'psle'])
                </div>
            @endif

            {{-- JCE Tab (Senior) --}}
            @if($studentDriver === 'senior')
                <div class="tab-pane fade" id="jce" role="tabpanel">
                    @include('sponsors.portal.students.partials.external-exams', ['student' => $student, 'examType' => 'jce'])
                </div>
            @endif

            {{-- Health Tab --}}
            <div class="tab-pane fade" id="health" role="tabpanel">
                @include('sponsors.portal.students.partials.health', ['student' => $student])
            </div>

            {{-- Behavior Tab --}}
            <div class="tab-pane fade" id="behavior" role="tabpanel">
                @include('sponsors.portal.students.partials.behavior', ['student' => $student])
            </div>
        </div>
    </div>
@endsection
