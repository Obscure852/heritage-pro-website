@extends('layouts.master')

@section('title', 'Edit ' . $item->title)

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('li_2')
            <a class="text-muted font-size-14" href="{{ route('lms.library.index') }}">Content Library</a>
        @endslot
        @slot('li_3')
            <a class="text-muted font-size-14" href="{{ route('lms.library.item', $item) }}">{{ $item->title }}</a>
        @endslot
        @slot('title')
            Edit Item
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Item</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lms.library.update', $item) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $item->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                rows="3">{{ old('description', $item->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Collection</label>
                                <select name="collection_id" class="form-select @error('collection_id') is-invalid @enderror">
                                    <option value="">None</option>
                                    @foreach($collections as $collection)
                                        <option value="{{ $collection->id }}" {{ old('collection_id', $item->collection_id) == $collection->id ? 'selected' : '' }}>
                                            {{ $collection->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('collection_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Visibility</label>
                                <select name="visibility" class="form-select @error('visibility') is-invalid @enderror">
                                    <option value="private" {{ old('visibility', $item->visibility) === 'private' ? 'selected' : '' }}>Private</option>
                                    <option value="shared" {{ old('visibility', $item->visibility) === 'shared' ? 'selected' : '' }}>Shared</option>
                                    <option value="public" {{ old('visibility', $item->visibility) === 'public' ? 'selected' : '' }}>Public</option>
                                </select>
                                @error('visibility')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Tags</label>
                            <input type="text" name="tags" class="form-control @error('tags') is-invalid @enderror"
                                value="{{ old('tags', $item->tags->pluck('name')->implode(', ')) }}"
                                placeholder="tag1, tag2, tag3">
                            <small class="text-muted">Comma-separated tags</small>
                            @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <a href="{{ route('lms.library.item', $item) }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            @if($item->usages()->count() === 0)
                <div class="card shadow-sm mt-4 border-danger">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Permanently delete this item and all its versions. This action cannot be undone.</p>
                        <form action="{{ route('lms.library.destroy', $item) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this item? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i>Delete Item
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle me-2"></i>
                    This item cannot be deleted because it is used in {{ $item->usages()->count() }} place(s).
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Current File Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Current File</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light rounded p-3 me-3">
                            @include('lms.library.partials.type-icon', ['type' => $item->type])
                        </div>
                        <div>
                            <strong class="d-block text-truncate" style="max-width: 200px;">{{ $item->file_name }}</strong>
                            <small class="text-muted">{{ $item->human_file_size }}</small>
                        </div>
                    </div>

                    <dl class="mb-0 small">
                        <dt>Type</dt>
                        <dd>{{ ucfirst($item->type) }}</dd>

                        <dt>MIME Type</dt>
                        <dd><code>{{ $item->mime_type }}</code></dd>

                        <dt>Uploaded</dt>
                        <dd>{{ $item->created_at->format('M j, Y') }}</dd>

                        @if($item->versions->count())
                            <dt>Versions</dt>
                            <dd>{{ $item->versions->count() + 1 }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- All Tags -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">Available Tags</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Click to add to your tags:</p>
                    @foreach($allTags as $tag)
                        <span class="badge bg-light text-dark me-1 mb-1 cursor-pointer tag-suggestion"
                            data-tag="{{ $tag->name }}">
                            {{ $tag->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tagsInput = document.querySelector('input[name="tags"]');
    const tagSuggestions = document.querySelectorAll('.tag-suggestion');

    tagSuggestions.forEach(function(badge) {
        badge.style.cursor = 'pointer';
        badge.addEventListener('click', function() {
            const tag = this.dataset.tag;
            const currentTags = tagsInput.value.split(',').map(t => t.trim()).filter(t => t);
            if (!currentTags.includes(tag)) {
                currentTags.push(tag);
                tagsInput.value = currentTags.join(', ');
            }
        });
    });
});
</script>
@endpush
@endsection
