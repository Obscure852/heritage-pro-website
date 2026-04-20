{{-- Move Items Modal with Folder Tree Picker --}}
<div class="modal fade" id="move-modal" tabindex="-1" aria-labelledby="moveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%); color: white;">
                <h5 class="modal-title" id="moveModalLabel">
                    <i class="fas fa-folder-open"></i> Move <span id="move-modal-count"></span> Item(s)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                <div id="move-tree-loading" class="text-center py-3">
                    <span class="spinner-border spinner-border-sm"></span> Loading folders...
                </div>
                <div id="move-tree-container" style="display: none;">
                    {{-- Root option --}}
                    <div class="move-tree-item" data-target-folder="" data-repository="" style="padding: 8px 12px; cursor: pointer; border-radius: 3px; margin-bottom: 2px;">
                        <i class="fas fa-home" style="width: 20px; color: #6b7280;"></i>
                        <span>Root (No Folder)</span>
                    </div>
                    <hr style="margin: 4px 0;">
                    {{-- Dynamic tree rendered by JS --}}
                    <div id="move-tree-content"></div>
                </div>
                <div id="move-tree-error" style="display: none;" class="text-center py-3 text-danger">
                    <i class="fas fa-exclamation-triangle"></i> Failed to load folder tree.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-loading" id="move-confirm-btn" onclick="confirmMove()" disabled>
                    <span class="btn-text"><i class="fas fa-folder-open"></i> Move Here</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Moving...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .move-tree-item {
        padding: 6px 12px;
        cursor: pointer;
        border-radius: 3px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        transition: background 0.15s;
    }

    .move-tree-item:hover:not(.disabled) {
        background: #f3f4f6;
    }

    .move-tree-item.selected {
        background: #dbeafe;
        font-weight: 600;
    }

    .move-tree-item.disabled {
        opacity: 0.4;
        cursor: not-allowed;
        pointer-events: none;
    }

    .move-tree-children {
        padding-left: 20px;
    }

    .move-tree-toggle {
        width: 16px;
        text-align: center;
        cursor: pointer;
        color: #9ca3af;
        font-size: 10px;
        transition: transform 0.2s;
        flex-shrink: 0;
    }

    .move-tree-toggle.expanded {
        transform: rotate(90deg);
    }

    .move-tree-toggle-spacer {
        width: 16px;
        flex-shrink: 0;
    }
</style>
