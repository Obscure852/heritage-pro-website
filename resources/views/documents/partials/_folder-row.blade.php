<tr class="subfolder-row" data-draggable-item data-type="folder" data-id="{{ $folder->id }}">
    <td style="width: 30px;"></td>
    <td>
        <input type="checkbox" class="form-check-input folder-checkbox" data-id="{{ $folder->id }}" data-type="folder" onchange="toggleSelect({{ $folder->id }}, this)">
    </td>
    <td>
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-folder" style="font-size: 20px; color: #f59e0b; width: 24px; text-align: center;"></i>
            <a href="{{ route('documents.index', ['folder' => $folder->id]) }}" style="color: #1f2937; font-weight: 500; text-decoration: none;">
                {{ $folder->name }}
            </a>
            @if(in_array($folder->visibility ?? null, [\App\Models\DocumentFolder::VISIBILITY_INTERNAL, \App\Models\DocumentFolder::VISIBILITY_PUBLIC], true))
                <span class="badge bg-info text-dark">Public</span>
            @elseif(($folder->repository_type ?? null) === \App\Models\DocumentFolder::REPOSITORY_PERSONAL)
                <span class="badge bg-secondary">Private</span>
            @endif
        </div>
    </td>
    <td></td>
    <td></td>
    <td style="color: #6b7280; font-size: 13px; white-space: nowrap;">{{ $folder->document_count }} item(s)</td>
    <td style="color: #6b7280; font-size: 13px; white-space: nowrap;">{{ $folder->created_at ? $folder->created_at->format('M d, Y') : '' }}</td>
    <td></td>
    <td class="text-end">
        <div class="dropdown">
            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown" style="padding: 4px 8px;">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('documents.index', ['folder' => $folder->id]) }}"><i class="fas fa-folder-open me-2 text-warning"></i> Open</a></li>
                @can('update', $folder)
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
                @endcan
            </ul>
        </div>
    </td>
</tr>
