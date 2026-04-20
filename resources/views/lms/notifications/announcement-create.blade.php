@extends('layouts.master')

@section('title', 'Create Announcement')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.announcements') }}">Announcements</a>
        @endslot
        @slot('title')
            Create Announcement
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Create New Announcement</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lms.announcements.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}" placeholder="Enter announcement title" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror"
                                      rows="6" placeholder="Write your announcement content here..." required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="course_id" class="form-label">Target Audience</label>
                                <select name="course_id" id="course_id" class="form-select @error('course_id') is-invalid @enderror">
                                    <option value="">Global (All Students)</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Leave empty to show to all Learning Space students</small>
                            </div>
                            <div class="col-md-6">
                                <label for="priority" class="form-label">Priority</label>
                                <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror">
                                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="published_at" class="form-label">Publish Date</label>
                                <input type="datetime-local" name="published_at" id="published_at"
                                       class="form-control @error('published_at') is-invalid @enderror"
                                       value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}">
                                @error('published_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Schedule for later or leave as now to publish immediately</small>
                            </div>
                            <div class="col-md-6">
                                <label for="expires_at" class="form-label">Expiry Date (Optional)</label>
                                <input type="datetime-local" name="expires_at" id="expires_at"
                                       class="form-control @error('expires_at') is-invalid @enderror"
                                       value="{{ old('expires_at') }}">
                                @error('expires_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Announcement will hide after this date</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-check-inline">
                                <input type="checkbox" name="is_pinned" id="is_pinned" class="form-check-input"
                                       value="1" {{ old('is_pinned') ? 'checked' : '' }}>
                                <label for="is_pinned" class="form-check-label">
                                    <i class="fas fa-thumbtack text-warning"></i> Pin to top
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" name="send_notification" id="send_notification" class="form-check-input"
                                       value="1" {{ old('send_notification', '1') ? 'checked' : '' }}>
                                <label for="send_notification" class="form-check-label">
                                    <i class="fas fa-bell text-primary"></i> Send notification to students
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" name="send_email" id="send_email" class="form-check-input"
                                       value="1" {{ old('send_email') ? 'checked' : '' }}>
                                <label for="send_email" class="form-check-label">
                                    <i class="fas fa-envelope text-info"></i> Also send email
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('lms.announcements') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <div>
                                <button type="submit" name="action" value="draft" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-save me-1"></i>Save as Draft
                                </button>
                                <button type="submit" name="action" value="publish" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i>Publish
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
