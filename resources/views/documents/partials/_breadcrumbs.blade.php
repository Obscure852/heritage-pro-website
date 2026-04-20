<nav class="folder-breadcrumbs" aria-label="Folder navigation">
    <ol class="breadcrumb-trail">
        <li class="breadcrumb-item">
            <a href="{{ route('documents.index') }}" class="{{ !$currentFolder ? 'active' : '' }}">
                <i class="fas fa-home"></i> All Documents
            </a>
        </li>
        @if($currentFolder && $breadcrumbs->count() > 0)
            @foreach($breadcrumbs as $crumb)
                <li class="breadcrumb-item">
                    <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
                    @if($crumb->id === $currentFolder->id)
                        <span class="active">{{ $crumb->name }}</span>
                    @else
                        <a href="{{ route('documents.index', ['folder' => $crumb->id]) }}">{{ $crumb->name }}</a>
                    @endif
                </li>
            @endforeach
        @endif
    </ol>
</nav>
