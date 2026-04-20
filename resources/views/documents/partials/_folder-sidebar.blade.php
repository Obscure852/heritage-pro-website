<aside class="folder-sidebar" id="folder-sidebar">
    @php
        $documentsUser = auth()->user();
        $showOwnerContext = isset($showOwnerContext)
            ? (bool) $showOwnerContext
            : ($documentsUser && \App\Policies\DocumentFolderPolicy::isAdmin($documentsUser));
        $currentUserId = $currentUserId ?? ($documentsUser?->id);
        $personalSectionLabel = $showOwnerContext ? 'Personal Repositories' : 'My Documents';
    @endphp
    <div class="sidebar-header">
        <span class="sidebar-title">Folders</span>
        <button class="sidebar-toggle" id="sidebar-toggle" title="Collapse sidebar">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>
    <div class="sidebar-content">
        {{-- New Folder button --}}
        <div class="sidebar-actions">
            <button class="btn btn-sm btn-primary w-100" id="new-folder-btn" onclick="showNewFolderModal()">
                <i class="fas fa-folder-plus"></i> New Folder
            </button>
        </div>

        {{-- Quota Widget --}}
        @include('documents.partials._quota-widget')
        <div style="border-top: 1px solid #e5e7eb; margin: 4px 12px 8px;"></div>

        {{-- Personal section --}}
        @if(!empty($folderTree['personal']))
        <div class="tree-section">
            <div class="tree-section-header"><i class="fas fa-user"></i> {{ $personalSectionLabel }}</div>
            @include('documents.partials._folder-tree', [
                'folders' => $folderTree['personal'],
                'currentFolderId' => $currentFolder->id ?? null,
                'showOwnerContext' => $showOwnerContext,
                'currentUserId' => $currentUserId,
            ])
        </div>
        @endif

        {{-- Public section --}}
        @if(!empty($folderTree['public']))
        <div class="tree-section">
            <div class="tree-section-header"><i class="fas fa-globe"></i> Public</div>
            @include('documents.partials._folder-tree', [
                'folders' => $folderTree['public'],
                'currentFolderId' => $currentFolder->id ?? null,
                'showOwnerContext' => $showOwnerContext,
                'currentUserId' => $currentUserId,
            ])
        </div>
        @endif

        {{-- Institutional section --}}
        @if(!empty($folderTree['institutional']))
        <div class="tree-section">
            <div class="tree-section-header"><i class="fas fa-building"></i> Institutional</div>
            @include('documents.partials._folder-tree', [
                'folders' => $folderTree['institutional'],
                'currentFolderId' => $currentFolder->id ?? null,
                'showOwnerContext' => $showOwnerContext,
                'currentUserId' => $currentUserId,
            ])
        </div>
        @endif

        {{-- Shared section --}}
        @if(!empty($folderTree['shared']))
        <div class="tree-section">
            <div class="tree-section-header"><i class="fas fa-share-alt"></i> Shared</div>
            @include('documents.partials._folder-tree', [
                'folders' => $folderTree['shared'],
                'currentFolderId' => $currentFolder->id ?? null,
                'showOwnerContext' => $showOwnerContext,
                'currentUserId' => $currentUserId,
            ])
        </div>
        @endif

        {{-- Department section --}}
        @if(!empty($folderTree['department']))
        <div class="tree-section">
            <div class="tree-section-header"><i class="fas fa-users"></i> Department</div>
            @include('documents.partials._folder-tree', [
                'folders' => $folderTree['department'],
                'currentFolderId' => $currentFolder->id ?? null,
                'showOwnerContext' => $showOwnerContext,
                'currentUserId' => $currentUserId,
            ])
        </div>
        @endif

        {{-- Separator --}}
        <div style="border-top: 1px solid #e5e7eb; margin: 8px 12px;"></div>

        @php
            $favIconMap = [
                'pdf' => 'fa-file-pdf text-danger',
                'doc' => 'fa-file-word text-primary',
                'docx' => 'fa-file-word text-primary',
                'xls' => 'fa-file-excel text-success',
                'xlsx' => 'fa-file-excel text-success',
                'ppt' => 'fa-file-powerpoint text-warning',
                'pptx' => 'fa-file-powerpoint text-warning',
                'jpg' => 'fa-file-image text-info',
                'jpeg' => 'fa-file-image text-info',
                'png' => 'fa-file-image text-info',
                'txt' => 'fa-file-alt text-secondary',
            ];
        @endphp

        {{-- Favorites section --}}
        <div class="tree-section">
            <div class="tree-section-header"><i class="fas fa-star" style="color: #f59e0b;"></i> Favorites</div>
            @if(isset($favorites) && $favorites->isNotEmpty())
                <ul class="folder-tree-list">
                    @foreach($favorites as $favDoc)
                        <li class="folder-tree-item">
                            <a href="{{ route('documents.show', $favDoc->id) }}" class="folder-tree-label" style="text-decoration: none;">
                                @php
                                    $favIcon = $favIconMap[strtolower($favDoc->extension ?? '')] ?? 'fa-file text-muted';
                                @endphp
                                <span class="folder-toggle-spacer"></span>
                                <i class="fas {{ $favIcon }}" style="width: 16px; text-align: center; margin-right: 6px; font-size: 12px;"></i>
                                <span class="folder-name" style="font-size: 13px;">{{ Str::limit($favDoc->title, 30) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div style="padding: 4px 16px; font-size: 12px; color: #9ca3af;">No favorites yet</div>
            @endif
        </div>

        {{-- Recent section --}}
        <div class="tree-section">
            <div class="tree-section-header"><i class="fas fa-clock"></i> Recent</div>
            @if(isset($recentDocuments) && $recentDocuments->isNotEmpty())
                <ul class="folder-tree-list">
                    @foreach($recentDocuments as $recentDoc)
                        <li class="folder-tree-item">
                            <a href="{{ route('documents.show', $recentDoc->id) }}" class="folder-tree-label" style="text-decoration: none;">
                                @php
                                    $recIcon = $favIconMap[strtolower($recentDoc->extension ?? '')] ?? 'fa-file text-muted';
                                @endphp
                                <span class="folder-toggle-spacer"></span>
                                <i class="fas {{ $recIcon }}" style="width: 16px; text-align: center; margin-right: 6px; font-size: 12px;"></i>
                                <span class="folder-name" style="font-size: 13px;">{{ Str::limit($recentDoc->title, 30) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div style="padding: 4px 16px; font-size: 12px; color: #9ca3af;">No recent documents</div>
            @endif
        </div>

        {{-- Shared with me section --}}
        <div class="tree-section">
            <div class="tree-section-header"><i class="fas fa-share-alt" style="color: #3b82f6;"></i> Shared with me</div>
            @if(isset($sharedWithMe) && $sharedWithMe->isNotEmpty())
                <ul class="folder-tree-list">
                    @foreach($sharedWithMe as $sharedItem)
                        @if($sharedItem->document)
                            <li class="folder-tree-item">
                                <a href="{{ route('documents.show', $sharedItem->document->id) }}" class="folder-tree-label" style="text-decoration: none;">
                                    @php
                                        $sharedIcon = $favIconMap[strtolower($sharedItem->document->extension ?? '')] ?? 'fa-file text-muted';
                                        $sharedPermClass = [
                                            'view' => 'bg-secondary',
                                            'comment' => 'bg-info',
                                            'edit' => 'bg-warning text-dark',
                                            'manage' => 'bg-success',
                                        ][$sharedItem->permission_level] ?? 'bg-secondary';
                                    @endphp
                                    <span class="folder-toggle-spacer"></span>
                                    <i class="fas {{ $sharedIcon }}" style="width: 16px; text-align: center; margin-right: 6px; font-size: 12px;"></i>
                                    <span class="folder-name" style="font-size: 13px;">{{ Str::limit($sharedItem->document->title, 25) }}</span>
                                    <span class="badge {{ $sharedPermClass }}" style="font-size: 9px; padding: 1px 5px; margin-left: 4px;">{{ ucfirst($sharedItem->permission_level) }}</span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
                <div style="padding: 4px 16px;">
                    <a href="{{ route('documents.shared') }}" style="font-size: 12px; color: #3b82f6; text-decoration: none;">
                        View all <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
                    </a>
                </div>
            @else
                <div style="padding: 4px 16px; font-size: 12px; color: #9ca3af;">No shared documents</div>
            @endif
        </div>
    </div>
</aside>

{{-- Icon rail when sidebar is collapsed --}}
<aside class="folder-sidebar-rail" id="folder-sidebar-rail">
    <button class="rail-btn" id="rail-expand" title="Expand folders">
        <i class="fas fa-folder"></i>
    </button>
</aside>
