<div class="subfolder-card" data-draggable-item data-type="folder" data-id="{{ $folder->id }}">
    @can('update', $folder)
        <div class="dropdown" style="position: absolute; top: 8px; right: 8px;">
            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown" style="padding: 2px 6px;">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @if(($folder->repository_type ?? null) === \App\Models\DocumentFolder::REPOSITORY_PERSONAL)
                    @php
                        $isPublicFolder = in_array($folder->visibility ?? null, [\App\Models\DocumentFolder::VISIBILITY_INTERNAL, \App\Models\DocumentFolder::VISIBILITY_PUBLIC], true);
                    @endphp
                    <li>
                        <a
                            class="dropdown-item toggle-folder-access-btn"
                            href="#"
                            data-folder-id="{{ $folder->id }}"
                            data-folder-name="{{ $folder->name }}"
                            data-target-access="{{ $isPublicFolder ? 'private' : 'public' }}"
                        >
                            <i class="fas fa-{{ $isPublicFolder ? 'lock' : 'globe' }} me-2 text-info"></i>
                            {{ $isPublicFolder ? 'Make Private' : 'Make Public' }}
                        </a>
                    </li>
                @endif
                <li><a class="dropdown-item rename-folder-btn" href="#" data-folder-id="{{ $folder->id }}" data-folder-name="{{ $folder->name }}"><i class="fas fa-edit me-2 text-primary"></i> Rename</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger delete-folder-btn" href="#" data-folder-id="{{ $folder->id }}" data-folder-name="{{ $folder->name }}"><i class="fas fa-trash me-2"></i> Delete</a></li>
            </ul>
        </div>
    @endcan

    <a href="{{ route('documents.index', ['folder' => $folder->id]) }}" class="text-decoration-none d-block text-center mb-2">
        <i class="fas fa-folder" style="font-size: 40px; color: #f59e0b;"></i>
    </a>
    <a href="{{ route('documents.index', ['folder' => $folder->id]) }}" class="text-decoration-none">
        <h6 style="color: #1f2937; font-size: 14px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $folder->name }}">
            {{ \Illuminate\Support\Str::limit($folder->name, 30) }}
        </h6>
    </a>
    @if(in_array($folder->visibility ?? null, [\App\Models\DocumentFolder::VISIBILITY_INTERNAL, \App\Models\DocumentFolder::VISIBILITY_PUBLIC], true))
        <div>
            <span class="badge bg-info text-dark">Public</span>
        </div>
    @elseif(($folder->repository_type ?? null) === \App\Models\DocumentFolder::REPOSITORY_PERSONAL)
        <div>
            <span class="badge bg-secondary">Private</span>
        </div>
    @endif
    <div style="font-size: 12px; color: #6b7280;">
        {{ $folder->document_count }} item(s)
    </div>
</div>
