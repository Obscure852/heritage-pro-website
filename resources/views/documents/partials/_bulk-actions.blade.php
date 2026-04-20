<div id="bulk-toolbar" style="display: none; background: #f0f9ff; padding: 12px 16px; border-radius: 3px; border-left: 4px solid #3b82f6; margin-bottom: 16px; align-items: center; gap: 12px;">
    <span style="font-size: 14px; color: #374151; font-weight: 500;">
        <span id="selected-count">0</span> item(s) selected
    </span>
    <div style="display: flex; gap: 8px; margin-left: auto;">
        <button onclick="bulkDownload()" class="btn btn-sm btn-primary" style="padding: 4px 12px; font-size: 13px;">
            <i class="fas fa-download"></i> Download Selected
        </button>
        <button onclick="showMoveModal()" class="btn btn-sm btn-warning" style="padding: 4px 12px; font-size: 13px;">
            <i class="fas fa-folder-open"></i> Move Selected
        </button>
        <button onclick="bulkDelete()" class="btn btn-sm btn-danger" style="padding: 4px 12px; font-size: 13px;">
            <i class="fas fa-trash"></i> Delete Selected
        </button>
        <button onclick="deselectAll()" class="btn btn-sm btn-outline-secondary" style="padding: 4px 12px; font-size: 13px;">
            Deselect All
        </button>
    </div>
</div>
