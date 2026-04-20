@extends('layouts.master')

@section('title', 'My Favorites - Content Library')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('li_2')
            <a class="text-muted font-size-14" href="{{ route('lms.library.index') }}">Content Library</a>
        @endslot
        @slot('title')
            My Favorites
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-0"><i class="fas fa-star text-warning me-2"></i>My Favorites</h4>
            <p class="text-muted mb-0">Content items you've marked as favorites for quick access</p>
        </div>
    </div>

    @if($items->count())
        <div class="row g-3">
            @foreach($items as $item)
                <div class="col-md-6 col-xl-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="bg-light rounded p-3 me-3">
                                    @include('lms.library.partials.type-icon', ['type' => $item->type])
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <h6 class="mb-1 text-truncate">
                                        <a href="{{ route('lms.library.item', $item) }}" class="text-decoration-none">
                                            {{ $item->title }}
                                        </a>
                                    </h6>
                                    <small class="text-muted d-block">
                                        {{ ucfirst($item->type) }} &bull; {{ $item->human_file_size }}
                                    </small>
                                    @if($item->collection)
                                        <small class="text-muted">
                                            <i class="fas fa-folder me-1"></i>{{ $item->collection->name }}
                                        </small>
                                    @endif
                                </div>
                                <form action="{{ route('lms.library.toggle-favorite', $item) }}" method="POST" class="ms-2">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link text-warning p-0" title="Remove from favorites">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </form>
                            </div>

                            @if($item->tags->count())
                                <div class="mt-2">
                                    @foreach($item->tags->take(3) as $tag)
                                        <span class="badge bg-light text-dark me-1">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                            <small class="text-muted">{{ $item->creator?->full_name }}</small>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('lms.library.item', $item) }}" class="btn btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ Storage::url($item->file_path) }}" class="btn btn-outline-success" title="Download" download>
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-star fa-4x text-muted mb-3"></i>
                <h5>No Favorites Yet</h5>
                <p class="text-muted mb-3">Star items from the library to add them to your favorites for quick access.</p>
                <a href="{{ route('lms.library.index') }}" class="btn btn-primary">
                    <i class="fas fa-photo-video me-2"></i>Browse Library
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
