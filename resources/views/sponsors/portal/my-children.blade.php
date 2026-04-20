@extends('layouts.master-sponsor-portal')
@section('title', 'My Children')
@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="mb-0">My Children</h4>
                <p class="text-muted">Below is a summary of your children’s profiles and performance.</p>
            </div>
        </div>

        <div class="row">
            @forelse($children as $child)
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                @if ($child->photo_path)
                                    <img id="imagePreview" src="{{ asset($child->photo_path) }}" class="img-fluid rounded"
                                        style="max-width: 150px; height: auto;" alt="{{ $child->full_name }}">
                                @else
                                    <div id="imagePreview"
                                        class="border rounded d-flex align-items-center justify-content-center"
                                        style="width: 140px; height: 160px; margin: 0 auto; background-color: #f8f9fa;">
                                        <i class="bx bx-user-circle" style="font-size: 5rem; color: #adb5bd;"></i>
                                    </div>
                                @endif
                            </div>
                            <h5 class="card-title">{{ $child->full_name }}</h5>
                            <p class="card-text">
                                <strong>Class:</strong>
                                {{ $child->currentClass ? $child->currentClass->name : 'Not Assigned' }}<br>
                                <strong>Attendance:</strong> {{ $child->absentDays->count() }} day(s) absent<br>
                                <strong>Test Average:</strong>
                                @if ($child->currentTermTests->isNotEmpty())
                                    {{ number_format($child->currentTermTests->avg('score'), 2) }}%
                                @else
                                    N/A
                                @endif
                            </p>
                            <a href="#" class="btn btn-primary btn-sm">View
                                Details</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">
                        No children records found.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
