@extends('layouts.master')

@section('title')
    House Student Allocations
@endsection

@section('css')
    @include('houses.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('house.index') }}">Houses</a>
        @endslot
        @slot('title')
            Student Allocations
        @endslot
    @endcomponent

    <div class="print-toolbar">
        <a href="{{ route('house.students-house-export') }}" class="btn btn-light">
            <i class="bx bx-export me-1"></i> Export
        </a>
        <button type="button" class="btn btn-light" onclick="window.print()">
            <i class="bx bx-printer me-1"></i> Print
        </button>
    </div>

    <div class="houses-report-container printable">
        <div class="houses-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-1 text-white">House Student Allocations</h3>
                    <p class="mb-0 opacity-75">Detailed student membership by house, including class and grade breakdowns.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <span class="summary-chip" style="background: rgba(255,255,255,0.18); color: #fff;">
                        <i class="fas fa-school"></i> {{ $school_data->school_name ?? 'School' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="houses-report-body">
            @include('houses.partials.module-nav', ['current' => 'allocations-report'])

            <div class="help-text">
                <div class="help-title">Report Scope</div>
                <div class="help-content">
                    Each block lists current students in the house and surfaces the saved color code used across house reporting.
                </div>
            </div>

            <div class="section-stack">
                @forelse ($houses as $house)
                    <div class="card-shell house-report-block">
                        <div class="card-body p-4">
                            <div class="house-section-header">
                                <div>
                                    <div class="house-title-row">
                                        <span class="house-color-swatch house-card-swatch" style="background: {{ $house->color_code }};"></span>
                                        <div>
                                            <h5 class="house-section-title mb-0">{{ $house->name }}</h5>
                                            <p class="house-section-subtitle">
                                                Head: {{ $house->houseHead->fullName ?? 'Not assigned' }}.
                                                Assistant: {{ $house->houseAssistant->fullName ?? 'Not assigned' }}.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="summary-chip-group">
                                    <span class="summary-chip house-chip"
                                        style="--house-color: {{ $house->color_code }}; --house-color-soft: {{ $house->colorWithAlpha(0.14) }};">
                                        {{ strtoupper($house->color_code) }}
                                    </span>
                                    <span class="summary-chip pill-muted"><i class="fas fa-user-graduate"></i> {{ $house->students_count }} students</span>
                                    <span class="summary-chip pill-muted"><i class="fas fa-user-tie"></i> {{ $house->users_count }} users</span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Gender</th>
                                            <th>Status</th>
                                            <th>Class</th>
                                            <th>Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($house->students as $index => $student)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $student->fullName }}</td>
                                                <td>{{ $student->gender ?? '-' }}</td>
                                                <td>{{ $student->status ?? '-' }}</td>
                                                <td>{{ $student->class?->name ?? '-' }}</td>
                                                <td>{{ $student->class?->grade?->name ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6">
                                                    <div class="empty-state">
                                                        <p class="mb-0">No students are currently allocated to {{ $house->name }}.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card-shell">
                        <div class="card-body">
                            <div class="empty-state">
                                <div><i class="fas fa-user-friends empty-state-icon"></i></div>
                                <p class="mb-0">No houses are available for allocation analysis.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
