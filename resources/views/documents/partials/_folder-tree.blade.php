@php
    $showOwnerContext = $showOwnerContext ?? false;
    $currentUserId = $currentUserId ?? (auth()->id() ? (int) auth()->id() : null);
@endphp

<ul class="folder-tree-list">
    @foreach ($folders as $folder)
        @php
            $isActive = ($currentFolderId == $folder->id);
            $hasChildren = $folder->children && count($folder->children) > 0;
            $showOwnerLabel = $showOwnerContext
                && ($folder->repository_type ?? null) === \App\Models\DocumentFolder::REPOSITORY_PERSONAL
                && isset($folder->owner_id)
                && (int) $folder->owner_id !== (int) $currentUserId
                && !empty($folder->owner_name ?? null);
        @endphp
        <li class="folder-tree-item {{ $isActive ? 'active' : '' }}"
            data-folder-id="{{ $folder->id }}"
            data-repository="{{ $folder->repository_type }}">
            <div class="folder-tree-label">
                @if ($hasChildren)
                    <span class="folder-toggle" data-folder="{{ $folder->id }}">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                @else
                    <span class="folder-toggle-spacer"></span>
                @endif
                <a href="{{ route('documents.index', ['folder' => $folder->id]) }}" class="folder-name {{ $isActive ? 'fw-bold' : '' }}">
                    <i class="fas fa-folder{{ $isActive ? '-open' : '' }}" style="color: #f59e0b; margin-right: 4px;"></i>
                    {{ \Illuminate\Support\Str::limit($folder->name, 30) }}
                    @if ($showOwnerLabel)
                        <span style="margin-left: 6px; color: #6b7280; font-size: 11px;">{{ $folder->owner_name }}</span>
                    @endif
                </a>
                <span class="folder-count">({{ $folder->document_count }})</span>
            </div>
            @if ($hasChildren)
                <div class="folder-children" data-parent="{{ $folder->id }}">
                    @include('documents.partials._folder-tree', [
                        'folders' => $folder->children,
                        'currentFolderId' => $currentFolderId,
                        'showOwnerContext' => $showOwnerContext,
                        'currentUserId' => $currentUserId,
                    ])
                </div>
            @endif
        </li>
    @endforeach
</ul>
