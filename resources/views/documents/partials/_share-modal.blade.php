{{-- Share Document Modal --}}
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true"
     data-document-id="{{ $document->id }}" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content share-modal-content">

            {{-- Header --}}
            <div class="modal-header share-modal-header">
                <div class="share-header-inner">
                    <div class="share-header-icon">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="shareModalLabel">Share Document</h5>
                        <p class="share-header-subtitle">Control who can access this document</p>
                    </div>
                </div>
                <button type="button" class="share-close-btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body share-modal-body">

                {{-- Add Share Section --}}
                <div class="share-add-section">

                    {{-- Permission Selector --}}
                    <div class="share-permission-bar">
                        <span class="share-permission-label">Permission level</span>
                        <div class="share-permission-pills">
                            <select class="form-select" id="sharePermission" style="display: none;">
                                <option value="view" selected>View</option>
                                <option value="comment">Comment</option>
                                <option value="edit">Edit</option>
                                <option value="manage">Manage</option>
                            </select>
                            <button type="button" class="share-pill active" data-value="view" onclick="setPermission(this)">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button type="button" class="share-pill" data-value="comment" onclick="setPermission(this)">
                                <i class="fas fa-comment"></i> Comment
                            </button>
                            <button type="button" class="share-pill" data-value="edit" onclick="setPermission(this)">
                                <i class="fas fa-pen"></i> Edit
                            </button>
                            <button type="button" class="share-pill" data-value="manage" onclick="setPermission(this)">
                                <i class="fas fa-cog"></i> Manage
                            </button>
                        </div>
                    </div>

                    {{-- Tabs --}}
                    <ul class="nav share-nav-tabs" id="shareTargetTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="share-tab-btn active" id="tab-users" data-bs-toggle="tab" data-bs-target="#pane-users" type="button" role="tab">
                                <i class="fas fa-user"></i>
                                <span>Users</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="share-tab-btn" id="tab-roles" data-bs-toggle="tab" data-bs-target="#pane-roles" type="button" role="tab">
                                <i class="fas fa-users"></i>
                                <span>Roles</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="share-tab-btn" id="tab-departments" data-bs-toggle="tab" data-bs-target="#pane-departments" type="button" role="tab">
                                <i class="fas fa-building"></i>
                                <span>Departments</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="share-tab-btn{{ $document->status !== 'published' ? ' disabled' : '' }}" id="public-link-tab" data-bs-toggle="tab" data-bs-target="#public-link-pane" type="button" role="tab"
                                @if($document->status !== 'published') title="Document must be published first" onclick="return false;" @endif>
                                <i class="fas fa-link"></i>
                                <span>Public Link</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content share-tab-content">

                        {{-- Users Tab --}}
                        <div class="tab-pane fade show active" id="pane-users" role="tabpanel">
                            <div class="share-search-wrapper">
                                <i class="fas fa-search share-search-icon"></i>
                                <input type="text" class="form-control share-search-input" id="shareUserSearch"
                                       placeholder="Search by name or email..." autocomplete="off">
                                <div class="share-search-results" id="shareSearchResults" style="display:none;"></div>
                            </div>
                            <div id="selectedUserChip" style="display:none;" class="mt-2">
                                <span class="share-user-chip">
                                    <i class="fas fa-user-check"></i>
                                    <span id="selectedUserName"></span>
                                    <button type="button" class="share-chip-remove" onclick="deselectUser()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </span>
                            </div>
                            <div id="userShareLimitMsg" class="share-limit-msg" style="display:none;">
                                <i class="fas fa-exclamation-triangle"></i> Maximum 50 individual shares reached. Remove existing shares to add new ones.
                            </div>
                            <button type="button" class="btn share-btn-action mt-3" id="btnShareUser" onclick="submitUserShare()" disabled>
                                <i class="fas fa-paper-plane"></i> Share with User
                            </button>
                        </div>

                        {{-- Roles Tab --}}
                        <div class="tab-pane fade" id="pane-roles" role="tabpanel">
                            <div id="rolesListContainer" class="share-list-container">
                                <div class="share-loading"><i class="fas fa-circle-notch fa-spin"></i> Loading roles...</div>
                            </div>
                            <button type="button" class="btn share-btn-action mt-3" id="btnShareRole" onclick="submitRoleShare()" disabled>
                                <i class="fas fa-paper-plane"></i> Share with Role
                            </button>
                        </div>

                        {{-- Departments Tab --}}
                        <div class="tab-pane fade" id="pane-departments" role="tabpanel">
                            <div id="departmentsListContainer" class="share-list-container">
                                <div class="share-loading"><i class="fas fa-circle-notch fa-spin"></i> Loading departments...</div>
                            </div>
                            <button type="button" class="btn share-btn-action mt-3" id="btnShareDept" onclick="submitDeptShare()" disabled>
                                <i class="fas fa-paper-plane"></i> Share with Department
                            </button>
                        </div>

                        {{-- Public Link Tab --}}
                        <div class="tab-pane fade" id="public-link-pane" role="tabpanel">
                            <div id="link-count-info" class="share-link-count">
                                <i class="fas fa-info-circle"></i> <span id="link-count-text">Loading...</span>
                            </div>

                            <div class="share-link-form">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="link-expires-at" class="share-form-label">Expiry Date <span class="text-danger">*</span></label>
                                        <input type="date" id="link-expires-at" class="form-control share-form-input">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="link-max-views" class="share-form-label">View Limit <small class="text-muted">(optional)</small></label>
                                        <input type="number" id="link-max-views" class="form-control share-form-input" placeholder="Unlimited" min="1">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="share-toggle-row">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="link-allow-download" checked>
                                                <label class="form-check-label" for="link-allow-download">Allow download</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="share-toggle-row">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="link-password-toggle">
                                                <label class="form-check-label" for="link-password-toggle">Require password</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12" id="link-password-wrapper" style="display: none;">
                                        <input type="password" id="link-password" class="form-control share-form-input" placeholder="Min 8 characters" minlength="8">
                                    </div>
                                </div>
                                <button type="button" id="btn-generate-link" class="btn share-btn-action mt-3">
                                    <i class="fas fa-link"></i> Generate Link
                                </button>
                            </div>

                            {{-- Success Banner --}}
                            <div id="link-success-banner" class="share-success-banner d-none mt-3">
                                <div class="share-success-header">
                                    <i class="fas fa-check-circle"></i> Link generated and copied to clipboard!
                                </div>
                                <div class="share-success-url">
                                    <input type="text" id="link-generated-url" class="form-control" readonly>
                                    <button class="btn share-copy-btn" type="button" id="btn-copy-link">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Active Links Table --}}
                            <div class="share-links-section mt-4">
                                <h6 class="share-section-title"><i class="fas fa-link me-1"></i> Active Links</h6>
                                <div id="public-links-table-container">
                                    <div class="share-loading">
                                        <i class="fas fa-circle-notch fa-spin"></i> Loading links...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Optional message --}}
                    <div class="share-message-section">
                        <textarea class="form-control share-message-input" id="shareMessage" rows="2"
                                  placeholder="Add an optional message for recipients..."></textarea>
                    </div>
                </div>

                {{-- Current Shares Section --}}
                <div class="share-current-section">
                    <div class="share-current-header">
                        <h6 class="share-section-title">
                            <i class="fas fa-users me-1"></i> Currently shared with
                        </h6>
                        <span class="share-count-badge" id="shareCountBadge">0</span>
                    </div>
                    <div id="currentSharesList">
                        <div class="share-loading">
                            <i class="fas fa-circle-notch fa-spin"></i> Loading shares...
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* ====================== Modal Shell ====================== */
    .share-modal-content {
        border: none;
        border-radius: 3px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.18), 0 0 0 1px rgba(0, 0, 0, 0.04);
    }

    .share-modal-header {
        background: white;
        padding: 20px 24px;
        border: none;
        border-bottom: 1.5px solid #e9ecef;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .share-header-inner {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .share-header-icon {
        width: 42px;
        height: 42px;
        border-radius: 3px;
        background: #f1f3f5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        color: #2d6a9f;
    }

    .share-modal-header .modal-title {
        color: #1f2937;
        font-size: 17px;
        font-weight: 700;
        letter-spacing: -0.2px;
        margin: 0;
    }

    .share-header-subtitle {
        color: #8899a6;
        font-size: 12px;
        margin: 2px 0 0 0;
        font-weight: 400;
    }

    .share-close-btn {
        background: #f1f3f5;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 3px;
        color: #6b7280;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        transition: all 0.2s;
    }

    .share-close-btn:hover {
        background: #e5e7eb;
        color: #374151;
    }

    .share-modal-body {
        padding: 0;
        background: #f8f9fb;
    }

    .share-add-section {
        padding: 20px 24px 16px;
        background: white;
    }

    /* ====================== Permission Pills ====================== */
    .share-permission-bar {
        margin-bottom: 18px;
    }

    .share-permission-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #8899a6;
        display: block;
        margin-bottom: 8px;
    }

    .share-permission-pills {
        display: flex;
        gap: 0;
        background: #f1f3f5;
        border-radius: 3px;
        padding: 3px;
    }

    .share-pill {
        flex: 1;
        padding: 7px 12px;
        border: none;
        background: transparent;
        color: #6b7280;
        font-size: 12px;
        font-weight: 500;
        border-radius: 3px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
    }

    .share-pill i {
        font-size: 10px;
    }

    .share-pill:hover {
        color: #374151;
        background: rgba(255, 255, 255, 0.5);
    }

    .share-pill.active {
        background: white;
        color: #1e3a5f;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        font-weight: 600;
    }

    /* ====================== Tab Navigation ====================== */
    .share-nav-tabs {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        border-bottom: 2px solid #e9ecef;
        gap: 0;
    }

    .share-nav-tabs .nav-item {
        flex: 1;
    }

    .share-tab-btn {
        width: 100%;
        padding: 11px 8px;
        border: none;
        background: transparent;
        color: #8899a6;
        font-size: 12.5px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        position: relative;
    }

    .share-tab-btn::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 12px;
        right: 12px;
        height: 2px;
        background: transparent;
        border-radius: 2px 2px 0 0;
        transition: all 0.25s ease;
    }

    .share-tab-btn:hover {
        color: #4b5563;
    }

    .share-tab-btn.active {
        color: #1e3a5f;
        font-weight: 600;
    }

    .share-tab-btn.active::after {
        background: #2d6a9f;
    }

    .share-tab-btn.disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .share-tab-btn i {
        font-size: 12px;
    }

    .share-tab-content {
        padding: 18px 0 0;
    }

    /* ====================== User Search ====================== */
    .share-search-wrapper {
        position: relative;
    }

    .share-search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 13px;
        z-index: 2;
        pointer-events: none;
    }

    .share-search-input {
        padding-left: 36px !important;
        border: 1.5px solid #e2e5e9;
        border-radius: 3px;
        font-size: 13px;
        height: 42px;
        transition: all 0.2s;
        background: #fafbfc;
    }

    .share-search-input:focus {
        border-color: #2d6a9f;
        box-shadow: 0 0 0 3px rgba(45, 106, 159, 0.1);
        background: white;
    }

    .share-search-input::placeholder {
        color: #b0b8c1;
    }

    .share-search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1060;
        background: white;
        border: 1.5px solid #e2e5e9;
        border-top: none;
        border-radius: 0 0 3px 3px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        max-height: 220px;
        overflow-y: auto;
    }

    .share-result-item {
        padding: 10px 14px;
        cursor: pointer;
        font-size: 13px;
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.15s;
        display: flex;
        flex-direction: column;
        gap: 1px;
    }

    .share-result-item:last-child {
        border-bottom: none;
    }

    .share-result-item:hover {
        background: #f0f5ff;
    }

    .share-result-item .result-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 13px;
    }

    .share-result-item .result-email {
        color: #8899a6;
        font-size: 12px;
    }

    .share-result-item .result-dept {
        color: #b0b8c1;
        font-size: 11px;
    }

    /* ====================== User Chip ====================== */
    .share-user-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #e8f0fe;
        color: #1e3a5f;
        padding: 6px 12px;
        border-radius: 3px;
        font-size: 13px;
        font-weight: 500;
        border: 1px solid rgba(45, 106, 159, 0.15);
    }

    .share-user-chip i {
        font-size: 11px;
        opacity: 0.7;
    }

    .share-chip-remove {
        background: none;
        border: none;
        color: #1e3a5f;
        cursor: pointer;
        padding: 0;
        margin-left: 2px;
        font-size: 11px;
        opacity: 0.5;
        transition: opacity 0.15s;
        line-height: 1;
    }

    .share-chip-remove:hover {
        opacity: 1;
    }

    /* ====================== Limit Warning ====================== */
    .share-limit-msg {
        background: #fef3cd;
        border: 1px solid #ffc107;
        border-radius: 3px;
        padding: 8px 12px;
        font-size: 12px;
        color: #856404;
        margin-top: 8px;
    }

    .share-limit-msg i {
        margin-right: 4px;
    }

    /* ====================== Action Button ====================== */
    .share-btn-action {
        background: #2d6a9f;
        color: white;
        border: none;
        padding: 9px 20px;
        border-radius: 3px;
        font-size: 13px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: all 0.2s;
        cursor: pointer;
    }

    .share-btn-action:hover:not(:disabled) {
        background: #245a8a;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(45, 106, 159, 0.25);
        color: white;
    }

    .share-btn-action:disabled {
        opacity: 0.45;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* ====================== Loading State ====================== */
    .share-loading {
        text-align: center;
        padding: 20px;
        color: #8899a6;
        font-size: 13px;
    }

    .share-loading i {
        margin-right: 6px;
    }

    /* ====================== List Containers (Roles/Depts) ====================== */
    .share-list-container {
        max-height: 200px;
        overflow-y: auto;
        border: 1.5px solid #e9ecef;
        border-radius: 3px;
        background: #fafbfc;
    }

    .role-check-item, .dept-check-item {
        padding: 9px 12px;
        cursor: pointer;
        font-size: 13px;
        transition: background 0.15s;
        border-bottom: 1px solid #f0f2f4;
        display: flex;
        align-items: center;
    }

    .role-check-item:last-child, .dept-check-item:last-child {
        border-bottom: none;
    }

    .role-check-item:hover, .dept-check-item:hover {
        background: #f0f5ff;
    }

    .role-check-item input, .dept-check-item input {
        margin-right: 10px;
        accent-color: #2d6a9f;
    }

    /* ====================== Public Link Form ====================== */
    .share-link-count {
        color: #8899a6;
        font-size: 12px;
        margin-bottom: 14px;
        padding: 8px 12px;
        background: #f0f5ff;
        border-radius: 3px;
        border: 1px solid #d4e4fa;
    }

    .share-link-count i {
        color: #2d6a9f;
        margin-right: 4px;
    }

    .share-link-form {
        background: #fafbfc;
        border: 1.5px solid #e9ecef;
        border-radius: 3px;
        padding: 16px;
    }

    .share-form-label {
        font-size: 12px;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 4px;
        display: block;
    }

    .share-form-input {
        border: 1.5px solid #e2e5e9;
        border-radius: 3px;
        font-size: 13px;
        height: 38px;
        transition: all 0.2s;
    }

    .share-form-input:focus {
        border-color: #2d6a9f;
        box-shadow: 0 0 0 3px rgba(45, 106, 159, 0.1);
    }

    .share-toggle-row {
        padding: 4px 0;
    }

    .share-toggle-row .form-check-label {
        font-size: 13px;
        color: #4b5563;
    }

    .share-toggle-row .form-check-input:checked {
        background-color: #2d6a9f;
        border-color: #2d6a9f;
    }

    /* ====================== Success Banner ====================== */
    .share-success-banner {
        background: #ecfdf5;
        border: 1px solid #6ee7b7;
        border-radius: 3px;
        padding: 14px;
    }

    .share-success-header {
        color: #065f46;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .share-success-header i {
        margin-right: 6px;
    }

    .share-success-url {
        display: flex;
        gap: 0;
    }

    .share-success-url .form-control {
        border-radius: 3px 0 0 3px;
        font-size: 12px;
        border: 1px solid #a7f3d0;
        background: white;
        height: 36px;
    }

    .share-copy-btn {
        border-radius: 0 3px 3px 0;
        background: #059669;
        color: white;
        border: 1px solid #059669;
        padding: 0 14px;
        font-size: 13px;
        transition: all 0.15s;
    }

    .share-copy-btn:hover {
        background: #047857;
        color: white;
    }

    /* ====================== Active Links Section ====================== */
    .share-links-section {
        border-top: 1.5px solid #e9ecef;
        padding-top: 14px;
    }

    .share-section-title {
        font-size: 13px;
        font-weight: 700;
        color: #374151;
        margin: 0 0 10px 0;
        letter-spacing: -0.1px;
    }

    .public-links-table {
        width: 100%;
        font-size: 12px;
        border-collapse: separate;
        border-spacing: 0;
        border: 1.5px solid #e9ecef;
        border-radius: 3px;
        overflow: hidden;
    }

    .public-links-table th {
        background: #f3f5f7;
        padding: 9px 10px;
        font-weight: 600;
        color: #4b5563;
        border-bottom: 1.5px solid #e9ecef;
        text-align: left;
        white-space: nowrap;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .public-links-table td {
        padding: 9px 10px;
        border-bottom: 1px solid #f0f2f4;
        vertical-align: middle;
        color: #4b5563;
    }

    .public-links-table tr:last-child td {
        border-bottom: none;
    }

    .public-links-table .link-actions {
        display: flex;
        gap: 4px;
        align-items: center;
    }

    .public-links-table .link-actions .btn {
        padding: 4px 7px;
        font-size: 11px;
        border-radius: 3px;
    }

    .link-disabled-row {
        opacity: 0.4;
    }

    #public-link-tab.disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    /* ====================== Message Section ====================== */
    .share-message-section {
        margin-top: 16px;
    }

    .share-message-input {
        border: 1.5px solid #e2e5e9;
        border-radius: 3px;
        font-size: 13px;
        padding: 10px 14px;
        resize: none;
        transition: all 0.2s;
        background: #fafbfc;
    }

    .share-message-input:focus {
        border-color: #2d6a9f;
        box-shadow: 0 0 0 3px rgba(45, 106, 159, 0.1);
        background: white;
    }

    .share-message-input::placeholder {
        color: #b0b8c1;
    }

    /* ====================== Current Shares ====================== */
    .share-current-section {
        padding: 18px 24px 20px;
        background: #f8f9fb;
    }

    .share-current-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .share-current-header .share-section-title {
        margin: 0;
    }

    .share-count-badge {
        background: #2d6a9f;
        color: white;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 9px;
        border-radius: 20px;
        min-width: 24px;
        text-align: center;
    }

    /* ====================== Permission Badges ====================== */
    .permission-badge {
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    .permission-badge.perm-view { background: #f1f3f5; color: #495057; }
    .permission-badge.perm-comment { background: #e8f0fe; color: #1e3a5f; }
    .permission-badge.perm-edit { background: #fef3cd; color: #856404; }
    .permission-badge.perm-manage { background: #d1fae5; color: #065f46; }

    /* ====================== Share Rows ====================== */
    .share-type-header {
        font-size: 11px;
        font-weight: 700;
        color: #8899a6;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        margin: 14px 0 6px 0;
        padding-bottom: 4px;
    }

    .share-type-header:first-child {
        margin-top: 0;
    }

    .share-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 3px;
        font-size: 13px;
        transition: background 0.15s;
        margin-bottom: 2px;
        background: white;
        border: 1px solid #f0f2f4;
    }

    .share-row:hover {
        background: #f8fafc;
        border-color: #e2e5e9;
    }

    .share-row .share-icon {
        width: 34px;
        height: 34px;
        border-radius: 3px;
        background: #e8f0fe;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 13px;
        color: #2d6a9f;
    }

    .share-row .share-info {
        flex: 1;
        min-width: 0;
    }

    .share-row .share-name {
        font-weight: 600;
        color: #1f2937;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 13px;
    }

    .share-row .share-email {
        font-size: 12px;
        color: #8899a6;
    }

    .share-row .share-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }

    .share-row .share-actions select {
        font-size: 12px;
        padding: 3px 8px;
        border-radius: 3px;
        border: 1.5px solid #e2e5e9;
        background: #fafbfc;
        color: #4b5563;
        transition: all 0.15s;
    }

    .share-row .share-actions select:focus {
        border-color: #2d6a9f;
        outline: none;
        box-shadow: 0 0 0 2px rgba(45, 106, 159, 0.1);
    }

    .share-row .btn-remove-share {
        background: none;
        border: none;
        color: #ef4444;
        cursor: pointer;
        padding: 5px;
        font-size: 13px;
        opacity: 0;
        transition: all 0.2s;
        border-radius: 3px;
    }

    .share-row:hover .btn-remove-share {
        opacity: 0.5;
    }

    .share-row .btn-remove-share:hover {
        opacity: 1;
        background: #fef2f2;
    }

    /* ====================== Scrollbar ====================== */
    .share-search-results::-webkit-scrollbar,
    .share-list-container::-webkit-scrollbar {
        width: 5px;
    }

    .share-search-results::-webkit-scrollbar-track,
    .share-list-container::-webkit-scrollbar-track {
        background: transparent;
    }

    .share-search-results::-webkit-scrollbar-thumb,
    .share-list-container::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 10px;
    }

    /* ====================== Empty State ====================== */
    .share-empty-state {
        text-align: center;
        padding: 24px 16px;
        color: #8899a6;
    }

    .share-empty-state i {
        font-size: 24px;
        display: block;
        margin-bottom: 8px;
        opacity: 0.4;
    }

    .share-empty-state span {
        font-size: 13px;
    }
</style>

<script>
(function() {
    const documentId = '{{ $document->id }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let selectedUserId = null;
    let searchTimeout = null;
    let currentXHR = null;
    let rolesLoaded = false;
    let departmentsLoaded = false;
    let cachedRoles = [];
    let cachedDepartments = [];

    // ==================== Permission Pill Sync ====================

    window.setPermission = function(el) {
        document.querySelectorAll('.share-pill').forEach(function(p) { p.classList.remove('active'); });
        el.classList.add('active');
        document.getElementById('sharePermission').value = el.dataset.value;
    };

    // ==================== Modal Events ====================

    const shareModal = document.getElementById('shareModal');
    shareModal.addEventListener('shown.bs.modal', function() {
        loadShares(documentId);
    });

    // ==================== User Search ====================

    const searchInput = document.getElementById('shareUserSearch');
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(searchTimeout);

        if (query.length < 2) {
            document.getElementById('shareSearchResults').style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(function() {
            searchUsers(query);
        }, 300);
    });

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#shareUserSearch') && !e.target.closest('#shareSearchResults')) {
            document.getElementById('shareSearchResults').style.display = 'none';
        }
    });

    function searchUsers(query) {
        if (currentXHR) currentXHR.abort();

        currentXHR = $.ajax({
            url: '{{ route("documents.shares.users.search") }}',
            data: { q: query },
            success: function(users) {
                const container = document.getElementById('shareSearchResults');
                if (users.length === 0) {
                    container.innerHTML = '<div class="share-result-item text-muted">No users found</div>';
                } else {
                    container.innerHTML = users.map(function(u) {
                        return '<div class="share-result-item" onclick="selectUser(' + u.id + ', \'' + escapeHtml(u.name) + '\')">' +
                            '<div class="result-name">' + escapeHtml(u.name) + '</div>' +
                            '<div class="result-email">' + escapeHtml(u.email) + (u.department ? ' &middot; <span class="result-dept">' + escapeHtml(u.department) + '</span>' : '') + '</div>' +
                            '</div>';
                    }).join('');
                }
                container.style.display = 'block';
            },
            error: function(xhr) {
                if (xhr.statusText !== 'abort') {
                    console.error('User search failed', xhr);
                }
            }
        });
    }

    window.selectUser = function(userId, userName) {
        selectedUserId = userId;
        document.getElementById('shareUserSearch').value = '';
        document.getElementById('shareSearchResults').style.display = 'none';
        document.getElementById('selectedUserName').textContent = userName;
        document.getElementById('selectedUserChip').style.display = 'block';
        document.getElementById('btnShareUser').disabled = false;
    };

    window.deselectUser = function() {
        selectedUserId = null;
        document.getElementById('selectedUserChip').style.display = 'none';
        document.getElementById('btnShareUser').disabled = true;
    };

    // ==================== Tab Loading ====================

    document.getElementById('tab-roles').addEventListener('shown.bs.tab', function() {
        if (!rolesLoaded) loadRoles();
    });

    document.getElementById('tab-departments').addEventListener('shown.bs.tab', function() {
        if (!departmentsLoaded) loadDepartments();
    });

    function loadRoles() {
        $.get('{{ route("documents.shares.roles") }}', function(roles) {
            cachedRoles = roles;
            rolesLoaded = true;
            const container = document.getElementById('rolesListContainer');
            if (roles.length === 0) {
                container.innerHTML = '<div class="share-loading">No roles available</div>';
                return;
            }
            container.innerHTML = roles.map(function(r) {
                var roleId = r.id !== undefined ? r.id : r;
                var roleName = r.name !== undefined ? r.name : String(r);
                return '<label class="role-check-item d-flex align-items-center">' +
                    '<input type="radio" name="shareRole" value="' + escapeHtml(String(roleId)) + '"> ' +
                    '<span>' + escapeHtml(roleName) + '</span></label>';
            }).join('');

            container.querySelectorAll('input[name="shareRole"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    document.getElementById('btnShareRole').disabled = false;
                });
            });
        });
    }

    function loadDepartments() {
        $.get('{{ route("documents.shares.departments") }}', function(departments) {
            cachedDepartments = departments;
            departmentsLoaded = true;
            const container = document.getElementById('departmentsListContainer');
            if (departments.length === 0) {
                container.innerHTML = '<div class="share-loading">No departments available</div>';
                return;
            }
            container.innerHTML = departments.map(function(d) {
                return '<label class="dept-check-item d-flex align-items-center">' +
                    '<input type="radio" name="shareDept" value="' + escapeHtml(d) + '"> ' +
                    '<span>' + escapeHtml(d) + '</span></label>';
            }).join('');

            container.querySelectorAll('input[name="shareDept"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    document.getElementById('btnShareDept').disabled = false;
                });
            });
        });
    }

    // ==================== Share Submission ====================

    window.submitUserShare = function() {
        if (!selectedUserId) return;
        addShare(documentId, 'user', selectedUserId, getPermission(), getMessage());
    };

    window.submitRoleShare = function() {
        const checked = document.querySelector('input[name="shareRole"]:checked');
        if (!checked) return;
        addShare(documentId, 'role', checked.value, getPermission(), getMessage());
    };

    window.submitDeptShare = function() {
        const checked = document.querySelector('input[name="shareDept"]:checked');
        if (!checked) return;
        addShare(documentId, 'department', checked.value, getPermission(), getMessage());
    };

    function getPermission() {
        return document.getElementById('sharePermission').value;
    }

    function getMessage() {
        return document.getElementById('shareMessage').value.trim() || null;
    }

    function addShare(docId, type, id, permission, message) {
        $.ajax({
            url: '/documents/' + docId + '/shares',
            method: 'POST',
            data: {
                _token: csrfToken,
                shareable_type: type,
                shareable_id: id,
                permission: permission,
                message: message
            },
            success: function(response) {
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success',
                    title: response.message || 'Document shared successfully.',
                    showConfirmButton: false, timer: 2000
                });
                // Reset inputs
                deselectUser();
                document.getElementById('shareMessage').value = '';
                // Reload shares
                loadShares(docId);
            },
            error: function(xhr) {
                var msg = 'Failed to share document.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errs = xhr.responseJSON.errors;
                    var first = Object.values(errs)[0];
                    if (Array.isArray(first)) msg = first[0];
                }
                Swal.fire('Error', msg, 'error');
            }
        });
    }

    // ==================== Load / Render Current Shares ====================

    function loadShares(docId) {
        $.get('/documents/' + docId + '/shares', function(data) {
            renderShares(data);
        }).fail(function() {
            document.getElementById('currentSharesList').innerHTML =
                '<div class="share-loading">Failed to load shares</div>';
        });
    }

    function renderShares(data) {
        const container = document.getElementById('currentSharesList');
        const totalCount = (data.users || []).length + (data.roles || []).length + (data.departments || []).length;
        document.getElementById('shareCountBadge').textContent = totalCount;

        // Check 50-user limit
        if ((data.users || []).length >= 50) {
            document.getElementById('userShareLimitMsg').style.display = 'block';
            document.getElementById('shareUserSearch').disabled = true;
        } else {
            document.getElementById('userShareLimitMsg').style.display = 'none';
            document.getElementById('shareUserSearch').disabled = false;
        }

        if (totalCount === 0) {
            container.innerHTML = '<div class="share-empty-state">' +
                '<i class="fas fa-lock"></i><span>This document has not been shared yet</span></div>';
            return;
        }

        let html = '';

        if (data.users && data.users.length > 0) {
            html += '<div class="share-type-header"><i class="fas fa-user me-1"></i> Users</div>';
            data.users.forEach(function(s) {
                html += buildShareRow(s, 'fa-user', s.user_name, s.user_email);
            });
        }

        if (data.roles && data.roles.length > 0) {
            html += '<div class="share-type-header"><i class="fas fa-users me-1"></i> Roles</div>';
            data.roles.forEach(function(s) {
                html += buildShareRow(s, 'fa-users', s.role_name || s.shareable_id, null);
            });
        }

        if (data.departments && data.departments.length > 0) {
            html += '<div class="share-type-header"><i class="fas fa-building me-1"></i> Departments</div>';
            data.departments.forEach(function(s) {
                html += buildShareRow(s, 'fa-building', s.shareable_id, null);
            });
        }

        container.innerHTML = html;
    }

    function buildShareRow(share, iconClass, name, email) {
        var permClass = 'perm-' + share.permission;
        var permLabel = share.permission.charAt(0).toUpperCase() + share.permission.slice(1);

        return '<div class="share-row">' +
            '<div class="share-icon"><i class="fas ' + iconClass + '"></i></div>' +
            '<div class="share-info">' +
                '<div class="share-name">' + escapeHtml(name) + '</div>' +
                (email ? '<div class="share-email">' + escapeHtml(email) + '</div>' : '') +
            '</div>' +
            '<div class="share-actions">' +
                '<span class="permission-badge ' + permClass + '">' + permLabel + '</span>' +
                '<select class="form-select form-select-sm" onchange="changeSharePermission(' + share.id + ', this.value, \'' + escapeHtml(name) + '\')" style="width: auto; min-width: 90px;">' +
                    '<option value="view"' + (share.permission === 'view' ? ' selected' : '') + '>View</option>' +
                    '<option value="comment"' + (share.permission === 'comment' ? ' selected' : '') + '>Comment</option>' +
                    '<option value="edit"' + (share.permission === 'edit' ? ' selected' : '') + '>Edit</option>' +
                    '<option value="manage"' + (share.permission === 'manage' ? ' selected' : '') + '>Manage</option>' +
                '</select>' +
                '<button type="button" class="btn-remove-share" onclick="removeShare(' + share.id + ', \'' + escapeHtml(name) + '\')" title="Remove access">' +
                    '<i class="fas fa-times"></i>' +
                '</button>' +
            '</div>' +
        '</div>';
    }

    // ==================== Change Permission (delete + re-create) ====================

    window.changeSharePermission = function(shareId, newPermission, name) {
        $.ajax({
            url: '/documents/' + documentId + '/shares/' + shareId,
            method: 'DELETE',
            data: { _token: csrfToken },
            success: function() {
                loadShares(documentId);
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'info',
                    title: 'To change permission, remove the share and re-add with the new level.',
                    showConfirmButton: false, timer: 3000
                });
            },
            error: function() {
                Swal.fire('Error', 'Failed to update permission.', 'error');
                loadShares(documentId);
            }
        });
    };

    // ==================== Remove Share ====================

    window.removeShare = function(shareId, name) {
        Swal.fire({
            title: 'Remove access?',
            text: 'Remove access for ' + name + '? They will no longer be able to view this document.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, remove access'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/documents/' + documentId + '/shares/' + shareId,
                    method: 'DELETE',
                    data: { _token: csrfToken },
                    success: function() {
                        Swal.fire({
                            toast: true, position: 'top-end', icon: 'success',
                            title: 'Access removed for ' + name,
                            showConfirmButton: false, timer: 2000
                        });
                        loadShares(documentId);
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to remove share.', 'error');
                    }
                });
            }
        });
    };

    // ==================== Public Link Tab ====================

    (function initLinkDateDefaults() {
        var expiryInput = document.getElementById('link-expires-at');
        if (!expiryInput) return;
        var now = new Date();
        var tomorrow = new Date(now);
        tomorrow.setDate(tomorrow.getDate() + 1);
        var defaultDate = new Date(now);
        defaultDate.setDate(defaultDate.getDate() + 30);
        var maxDate = new Date(now);
        maxDate.setDate(maxDate.getDate() + 365);
        expiryInput.min = tomorrow.toISOString().split('T')[0];
        expiryInput.max = maxDate.toISOString().split('T')[0];
        expiryInput.value = defaultDate.toISOString().split('T')[0];
    })();

    document.getElementById('link-password-toggle').addEventListener('change', function() {
        var wrapper = document.getElementById('link-password-wrapper');
        wrapper.style.display = this.checked ? 'block' : 'none';
        if (!this.checked) {
            document.getElementById('link-password').value = '';
        }
    });

    var publicLinkTabEl = document.getElementById('public-link-tab');
    if (publicLinkTabEl) {
        publicLinkTabEl.addEventListener('shown.bs.tab', function() {
            loadPublicLinks();
        });
    }

    function loadPublicLinks() {
        $.ajax({
            url: '/documents/' + documentId + '/public-links',
            method: 'GET',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(links) {
                renderPublicLinks(links);
            },
            error: function() {
                document.getElementById('public-links-table-container').innerHTML =
                    '<div class="share-loading">Failed to load public links</div>';
            }
        });
    }

    function renderPublicLinks(links) {
        var countText = document.getElementById('link-count-text');
        countText.textContent = links.length + ' of 5 links used';

        var container = document.getElementById('public-links-table-container');

        if (links.length === 0) {
            container.innerHTML = '<div class="share-empty-state">' +
                '<i class="fas fa-link"></i><span>No active public links</span></div>';
            return;
        }

        var html = '<table class="public-links-table"><thead><tr>' +
            '<th>Created</th><th>Expires</th><th>Views</th><th>Password</th><th>Download</th><th>Actions</th>' +
            '</tr></thead><tbody>';

        links.forEach(function(link) {
            var rowClass = link.is_active ? '' : ' class="link-disabled-row"';
            var createdDate = new Date(link.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
            var expiresDate = new Date(link.expires_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
            var viewsText = link.view_count + '/' + (link.max_views ? link.max_views : '\u221E');
            var passwordBadge = link.has_password
                ? '<span class="badge bg-success" style="font-size: 10px;">Yes</span>'
                : '<span class="badge bg-secondary" style="font-size: 10px;">No</span>';
            var downloadBadge = link.allow_download
                ? '<span class="badge bg-success" style="font-size: 10px;">Yes</span>'
                : '<span class="badge bg-secondary" style="font-size: 10px;">No</span>';

            html += '<tr' + rowClass + '>' +
                '<td>' + createdDate + '</td>' +
                '<td>' + expiresDate + '</td>' +
                '<td>' + viewsText + '</td>' +
                '<td>' + passwordBadge + '</td>' +
                '<td>' + downloadBadge + '</td>' +
                '<td><div class="link-actions">' +
                    '<button class="btn btn-outline-secondary btn-sm" onclick="copyPublicLink(\'' + escapeHtml(link.url) + '\')" title="Copy link"><i class="fas fa-copy"></i></button>' +
                    '<button class="btn btn-outline-' + (link.is_active ? 'warning' : 'success') + ' btn-sm" onclick="togglePublicLink(' + link.id + ')" title="' + (link.is_active ? 'Disable' : 'Enable') + '"><i class="fas fa-' + (link.is_active ? 'ban' : 'check') + '"></i></button>' +
                    '<button class="btn btn-outline-danger btn-sm" onclick="deletePublicLink(' + link.id + ')" title="Delete"><i class="fas fa-trash"></i></button>' +
                '</div></td>' +
                '</tr>';
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    document.getElementById('btn-generate-link').addEventListener('click', function() {
        var btn = this;
        var expiresAt = document.getElementById('link-expires-at').value;
        var allowDownload = document.getElementById('link-allow-download').checked;
        var maxViews = document.getElementById('link-max-views').value;
        var passwordToggle = document.getElementById('link-password-toggle').checked;
        var password = document.getElementById('link-password').value;

        if (!expiresAt) {
            Swal.fire('Error', 'Please select an expiry date.', 'error');
            return;
        }

        var data = {
            expires_at: expiresAt,
            allow_download: allowDownload ? 1 : 0
        };

        if (maxViews) data.max_views = parseInt(maxViews);
        if (passwordToggle && password) data.password = password;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Generating...';

        $.ajax({
            url: '/documents/' + documentId + '/public-links',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: data,
            success: function(response) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(response.url).catch(function() {});
                }

                document.getElementById('link-generated-url').value = response.url;
                document.getElementById('link-success-banner').classList.remove('d-none');

                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success',
                    title: 'Link generated!',
                    showConfirmButton: false, timer: 2000
                });

                loadPublicLinks();

                document.getElementById('link-password').value = '';
                document.getElementById('link-password-toggle').checked = false;
                document.getElementById('link-password-wrapper').style.display = 'none';
                document.getElementById('link-max-views').value = '';
            },
            error: function(xhr) {
                var msg = 'Failed to generate link.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errs = xhr.responseJSON.errors;
                    var first = Object.values(errs)[0];
                    if (Array.isArray(first)) msg = first[0];
                }
                Swal.fire('Error', msg, 'error');
            },
            complete: function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-link"></i> Generate Link';
            }
        });
    });

    document.getElementById('btn-copy-link').addEventListener('click', function() {
        var url = document.getElementById('link-generated-url').value;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function() {
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success',
                    title: 'Copied!',
                    showConfirmButton: false, timer: 1500
                });
            });
        }
    });

    window.copyPublicLink = function(url) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function() {
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success',
                    title: 'Copied!',
                    showConfirmButton: false, timer: 1500
                });
            });
        }
    };

    window.togglePublicLink = function(shareId) {
        $.ajax({
            url: '/documents/' + documentId + '/public-links/' + shareId + '/disable',
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function() {
                loadPublicLinks();
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success',
                    title: 'Link status updated.',
                    showConfirmButton: false, timer: 2000
                });
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to update link.', 'error');
            }
        });
    };

    window.deletePublicLink = function(shareId) {
        Swal.fire({
            title: 'Delete public link?',
            text: 'This link will stop working immediately. This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/documents/' + documentId + '/public-links/' + shareId,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: function() {
                        loadPublicLinks();
                        Swal.fire({
                            toast: true, position: 'top-end', icon: 'success',
                            title: 'Link deleted.',
                            showConfirmButton: false, timer: 2000
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete link.', 'error');
                    }
                });
            }
        });
    };

    // ==================== Helpers ====================

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
})();
</script>
