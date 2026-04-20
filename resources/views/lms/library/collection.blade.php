@extends('layouts.master')

@section('title', $collection->name . ' - Content Library')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('li_2')
            <a class="text-muted font-size-14" href="{{ route('lms.library.index') }}">Content Library</a>
        @endslot
        @if($collection->parent)
            @slot('li_3')
                <a class="text-muted font-size-14" href="{{ route('lms.library.collection', $collection->parent) }}">{{ $collection->parent->name }}</a>
            @endslot
        @endif
        @slot('title')
            {{ $collection->name }}
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="rounded p-3 me-3" style="background-color: {{ $collection->color ?? '#6c757d' }}20;">
                    <i class="fas fa-folder fa-2x" style="color: {{ $collection->color ?? '#6c757d' }}"></i>
                </div>
                <div>
                    <h4 class="mb-0">{{ $collection->name }}</h4>
                    @if($collection->description)
                        <p class="text-muted mb-0">{{ $collection->description }}</p>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-{{ $collection->visibility === 'public' ? 'success' : ($collection->visibility === 'shared' ? 'warning' : 'secondary') }}">
                    {{ ucfirst($collection->visibility) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Sub-Collections -->
    @if($children->count())
        <div class="mb-4">
            <h6 class="text-muted mb-3"><i class="fas fa-folder me-2"></i>Sub-Collections</h6>
            <div class="row g-3">
                @foreach($children as $child)
                    <div class="col-md-4 col-lg-3">
                        <a href="{{ route('lms.library.collection', $child) }}" class="card shadow-sm h-100 text-decoration-none">
                            <div class="card-body text-center py-4">
                                <i class="fas fa-folder fa-3x mb-3" style="color: {{ $child->color ?? '#6c757d' }}"></i>
                                <h6 class="mb-1">{{ $child->name }}</h6>
                                <small class="text-muted">{{ $child->items_count }} items</small>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Items -->
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
                                </div>
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
                                @if($item->created_by === auth()->id())
                                    <a href="{{ route('lms.library.edit', $item) }}" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
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
                <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                <h5>Collection is Empty</h5>
                <p class="text-muted mb-0">No items in this collection yet.</p>
            </div>
        </div>
    @endif
</div>
@endsection
