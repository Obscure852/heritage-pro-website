@once
    @push('head')
        <style>
            .crm-app-shell,
            .crm-app-sidebar,
            .crm-app-main {
                --crm-app-blue: #2563eb;
                --crm-app-cyan: #0891b2;
                --crm-app-violet: #7c3aed;
                --crm-app-amber: #d97706;
                --crm-app-slate: #475569;
            }

            .crm-discussions-nav {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                margin-bottom: 20px;
            }

            .crm-discussions-nav-link {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 11px 16px;
                border: 1px solid #dbe5f1;
                border-radius: 3px;
                background: #fff;
                color: #475569;
                font-weight: 600;
                transition: all 0.18s ease;
            }

            .crm-discussions-nav-link:hover,
            .crm-discussions-nav-link.active {
                color: #1d4ed8;
                border-color: #93c5fd;
                background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);
                box-shadow: 0 12px 24px rgba(59, 130, 246, 0.12);
            }

            .crm-discussion-channel-card,
            .crm-discussion-thread-card,
            .crm-discussion-campaign-card,
            .crm-attachment-card,
            .crm-app-file-card {
                border: 1px solid #dbe5f1;
                border-radius: 3px;
                background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
            }

            .crm-discussion-channel-card {
                display: grid;
                gap: 18px;
                padding: 22px;
            }

            .crm-discussion-channel-card.preferred {
                border-color: #60a5fa;
                box-shadow: 0 16px 32px rgba(59, 130, 246, 0.16);
            }

            .crm-discussion-channel-card h3,
            .crm-discussion-thread-card h3,
            .crm-discussion-campaign-card h3 {
                margin: 0;
                font-size: 18px;
                color: #0f172a;
            }

            .crm-discussion-channel-card p,
            .crm-discussion-thread-card p,
            .crm-discussion-campaign-card p {
                margin: 0;
                color: #64748b;
                line-height: 1.55;
            }

            .crm-discussion-channel-pills,
            .crm-discussion-meta-row,
            .crm-discussion-recipient-pills,
            .crm-app-sidebar-list,
            .crm-app-sidebar-meta,
            .crm-app-participant-list {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .crm-discussion-thread-card,
            .crm-discussion-campaign-card {
                display: grid;
                gap: 14px;
                padding: 18px 20px;
            }

            .crm-discussion-thread-head,
            .crm-discussion-campaign-head {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: flex-start;
            }

            .crm-discussion-message-preview {
                color: #475569;
                font-size: 13px;
                line-height: 1.6;
                white-space: pre-line;
            }

            .crm-discussion-recipient-pill {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 12px;
                border-radius: 999px;
                border: 1px solid #dbe5f1;
                background: #fff;
                color: #475569;
                font-size: 12px;
                font-weight: 600;
            }

            .crm-discussion-source-card {
                display: flex;
                justify-content: space-between;
                gap: 18px;
                padding: 16px 18px;
                border-radius: 3px;
                border: 1px solid #bfdbfe;
                background: linear-gradient(135deg, rgba(239, 246, 255, 0.92), rgba(224, 242, 254, 0.92));
            }

            .crm-discussion-source-card strong,
            .crm-discussion-source-card span {
                display: block;
            }

            .crm-discussion-source-card span {
                margin-top: 4px;
                color: #475569;
                font-size: 13px;
            }

            .crm-discussion-split {
                display: grid;
                grid-template-columns: minmax(0, 1.4fr) minmax(280px, 0.8fr);
                gap: 20px;
            }

            .crm-discussion-timeline {
                display: grid;
                gap: 14px;
            }

            .crm-discussion-timeline-item {
                display: grid;
                gap: 12px;
                padding: 18px;
                border: 1px solid #dbe5f1;
                border-radius: 3px;
                background: #fff;
            }

            .crm-discussion-timeline-head {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: flex-start;
            }

            .crm-discussion-timeline-copy {
                color: #334155;
                line-height: 1.65;
                white-space: normal;
                word-break: break-word;
            }

            .crm-attachments-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 14px;
            }

            .crm-attachment-card,
            .crm-app-file-card {
                padding: 16px;
            }

            .crm-attachment-head {
                display: flex;
                gap: 12px;
                align-items: flex-start;
                margin-bottom: 12px;
            }

            .crm-attachment-icon {
                width: 42px;
                height: 42px;
                border-radius: 3px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
                color: #2563eb;
                font-size: 18px;
                flex: 0 0 auto;
            }

            .crm-attachment-copy strong,
            .crm-app-file-card strong {
                display: block;
                color: #0f172a;
            }

            .crm-attachment-copy span,
            .crm-app-file-card span {
                display: block;
                margin-top: 4px;
                color: #64748b;
                font-size: 12px;
            }

            .crm-app-shell {
                display: grid;
                grid-template-columns: minmax(300px, 320px) minmax(0, 1fr);
                gap: 20px;
                align-items: start;
                isolation: isolate;
            }

            .crm-app-sidebar,
            .crm-app-main {
                border: 1px solid #cfe0ff;
                border-radius: 3px;
                box-shadow: 0 14px 24px rgba(37, 99, 235, 0.08);
                min-width: 0;
                position: relative;
            }

            .crm-app-sidebar {
                background:
                    radial-gradient(circle at top left, rgba(59, 130, 246, 0.12), transparent 30%),
                    radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.12), transparent 28%),
                    linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%);
                padding: 18px;
                display: grid;
                gap: 18px;
                position: sticky;
                top: 24px;
                z-index: 0;
            }

            .crm-app-sidebar-section {
                display: grid;
                gap: 12px;
            }

            .crm-app-sidebar-section h3,
            .crm-app-main-header h2 {
                margin: 0;
                color: #0f172a;
                font-size: 18px;
            }

            .crm-app-thread-list {
                display: grid;
                gap: 10px;
            }

            .crm-app-thread-link {
                display: grid;
                grid-template-columns: 40px minmax(0, 1fr);
                gap: 12px;
                align-items: start;
                padding: 12px;
                border-radius: 3px;
                border: 1px solid #d7e5ff;
                background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
                transition: all 0.18s ease;
            }

            .crm-app-thread-link:hover,
            .crm-app-thread-link.active {
                border-color: #60a5fa;
                background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(34, 211, 238, 0.08));
                box-shadow: 0 14px 28px rgba(59, 130, 246, 0.16);
            }

            .crm-app-thread-link.unread {
                border-color: #38bdf8;
                box-shadow: inset 3px 0 0 #0ea5e9, 0 10px 22px rgba(14, 165, 233, 0.12);
            }

            .crm-app-thread-icon {
                width: 44px;
                height: 44px;
                border-radius: 3px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-size: 20px;
                box-shadow: 0 10px 20px rgba(37, 99, 235, 0.18);
            }

            .crm-app-thread-icon-company {
                background: linear-gradient(135deg, #0891b2 0%, #14b8a6 100%);
            }

            .crm-app-thread-icon-direct {
                background: linear-gradient(135deg, #2563eb 0%, #60a5fa 100%);
            }

            .crm-app-thread-icon-group {
                background: linear-gradient(135deg, #7c3aed 0%, #3b82f6 100%);
            }

            .crm-app-thread-copy {
                min-width: 0;
            }

            .crm-app-thread-copy strong,
            .crm-app-thread-copy span {
                display: block;
            }

            .crm-app-thread-copy strong {
                color: #0f172a;
                line-height: 1.45;
            }

            .crm-app-thread-copy span {
                margin-top: 4px;
                color: #64748b;
                font-size: 12px;
                line-height: 1.5;
            }

            .crm-app-thread-unread {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                margin-top: 8px;
                color: #1d4ed8;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }

            .crm-app-main {
                display: grid;
                grid-template-rows: auto minmax(360px, 1fr) auto;
                min-height: 760px;
                z-index: 1;
                overflow: hidden;
                background:
                    radial-gradient(circle at top right, rgba(99, 102, 241, 0.08), transparent 22%),
                    radial-gradient(circle at bottom left, rgba(6, 182, 212, 0.08), transparent 26%),
                    linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
            }

            .crm-app-main-header {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: flex-start;
                padding: 22px 24px 18px;
                border-bottom: 1px solid #d7e5ff;
                background: linear-gradient(135deg, rgba(37, 99, 235, 0.07), rgba(14, 165, 233, 0.05));
            }

            .crm-app-main-header p {
                margin: 8px 0 0;
                color: #64748b;
                line-height: 1.55;
            }

            .crm-app-message-panel {
                padding: 22px 24px;
                overflow-y: auto;
                background:
                    radial-gradient(circle at top right, rgba(96, 165, 250, 0.08), transparent 28%),
                    linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
            }

            .crm-app-message-stream {
                display: grid;
                gap: 18px;
            }

            .crm-app-message-row {
                display: flex;
                justify-content: flex-start;
            }

            .crm-app-message-row.mine {
                justify-content: flex-end;
            }

            .crm-app-message-bubble {
                width: min(100%, 760px);
                display: grid;
                gap: 12px;
                padding: 16px 18px;
                border-radius: 3px;
                border: 1px solid #d7e5ff;
                background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
                box-shadow: 0 14px 28px rgba(37, 99, 235, 0.08);
            }

            .crm-app-message-row.mine .crm-app-message-bubble {
                border-color: #60a5fa;
                background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 70%, #ffffff 100%);
                box-shadow: 0 16px 32px rgba(37, 99, 235, 0.12);
            }

            .crm-app-message-meta {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                align-items: center;
                color: #64748b;
                font-size: 12px;
            }

            .crm-app-message-bubble p {
                margin: 0;
                color: #334155;
                line-height: 1.7;
                white-space: pre-line;
            }

            .crm-discussion-mention {
                display: inline-flex;
                align-items: center;
                padding: 1px 7px;
                border-radius: 999px;
                background: rgba(59, 130, 246, 0.14);
                color: #1d4ed8;
                font-weight: 700;
            }

            .crm-discussion-mention.is-personal {
                background: rgba(124, 58, 237, 0.16);
                color: #6d28d9;
                box-shadow: inset 0 0 0 1px rgba(124, 58, 237, 0.12);
            }

            .crm-app-attachment-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 14px;
            }

            .crm-app-attachment {
                display: grid;
                gap: 10px;
                padding: 12px;
                border-radius: 3px;
                background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
                border: 1px solid #d4e4ff;
                box-shadow: 0 12px 24px rgba(37, 99, 235, 0.08);
            }

            .crm-docx-preview {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                border: 0;
                border-radius: 3px;
                background: #fff;
                padding: 12px;
                overflow: auto;
                color: #64748b;
                font-size: 12px;
                text-align: center;
            }

            .crm-docx-preview-shell {
                width: 100%;
                min-height: 100%;
            }

            .crm-app-attachment-actions {
                gap: 8px;
            }

            .crm-message-receipt {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                width: fit-content;
                padding: 5px 10px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .crm-message-receipt.is-seen {
                background: rgba(14, 165, 233, 0.12);
                color: #0f766e;
            }

            .crm-message-receipt.is-pending {
                background: rgba(148, 163, 184, 0.12);
                color: #475569;
            }

            .crm-app-user-picker {
                position: relative;
            }

            .crm-app-user-picker-control {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 8px;
                min-height: 52px;
                padding: 8px 12px;
                border: 1px solid #d1d5db;
                border-radius: 3px;
                background: #fff;
                transition: border-color 0.18s ease, box-shadow 0.18s ease;
            }

            .crm-app-user-picker-control:focus-within {
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }

            .crm-app-user-picker.is-invalid .crm-app-user-picker-control {
                border-color: #dc2626;
                box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.08);
            }

            .crm-app-user-picker-tags {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .crm-app-user-picker-input {
                flex: 1 1 180px;
                min-width: 180px;
                border: 0 !important;
                box-shadow: none !important;
                padding: 0 !important;
                min-height: 32px;
                background: transparent !important;
            }

            .crm-app-user-picker-input:focus {
                outline: 0;
            }

            .crm-app-user-tag {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 7px 10px;
                border: 1px solid #bfdbfe;
                border-radius: 999px;
                background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
                color: #1d4ed8;
                font-size: 12px;
                font-weight: 600;
                line-height: 1;
                transition: border-color 0.18s ease, transform 0.18s ease;
            }

            .crm-app-user-tag:hover {
                border-color: #60a5fa;
                transform: translateY(-1px);
            }

            .crm-app-user-tag-meta {
                color: #475569;
                font-weight: 500;
            }

            .crm-app-user-tag i {
                font-size: 16px;
                color: #2563eb;
            }

            .crm-app-user-picker-dropdown {
                position: absolute;
                top: calc(100% + 8px);
                left: 0;
                right: 0;
                z-index: 30;
                max-height: 260px;
                overflow-y: auto;
                padding: 6px;
                border: 1px solid #dbe5f1;
                border-radius: 3px;
                background: #fff;
                box-shadow: 0 18px 34px rgba(15, 23, 42, 0.12);
            }

            .crm-app-user-picker-option {
                width: 100%;
                display: grid;
                gap: 4px;
                padding: 10px 12px;
                border: 0;
                border-radius: 3px;
                background: transparent;
                text-align: left;
                transition: background-color 0.18s ease;
            }

            .crm-app-user-picker-option:hover {
                background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(34, 211, 238, 0.08));
            }

            .crm-app-user-picker-option-name {
                color: #0f172a;
                font-size: 13px;
                font-weight: 600;
            }

            .crm-app-user-picker-option-meta,
            .crm-app-user-picker-empty {
                color: #64748b;
                font-size: 12px;
                line-height: 1.45;
            }

            .crm-app-user-picker-empty {
                padding: 12px;
            }

            .crm-app-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                color: #0f172a !important;
                background: #fff;
                border: 1px solid #d1d5db;
                border-radius: 3px;
                padding: 8px 14px;
                font-size: 13px;
                font-weight: 600;
                box-shadow: 0 8px 16px rgba(15, 23, 42, 0.05);
                transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background-color 0.18s ease;
            }

            .crm-app-btn:hover {
                color: #0f172a !important;
                transform: translateY(-1px);
                background: #fff;
                border-color: #cbd5e1;
                box-shadow: 0 12px 22px rgba(15, 23, 42, 0.08);
            }

            .crm-app-btn i {
                font-size: 16px;
                line-height: 1;
            }

            .crm-app-btn-company i {
                color: #0f766e;
            }

            .crm-app-btn-direct i {
                color: #2563eb;
            }

            .crm-app-btn-group i {
                color: #7c3aed;
            }

            .crm-app-btn-open i {
                color: #0891b2;
            }

            .crm-icon-btn {
                width: 40px;
                min-width: 40px;
                height: 40px;
                padding: 0;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0;
                color: #0f172a !important;
                background: #fff;
                border: 1px solid #d1d5db;
                border-radius: 3px;
                box-shadow: 0 8px 16px rgba(15, 23, 42, 0.05);
                transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background-color 0.18s ease;
            }

            .crm-icon-btn:hover {
                color: #0f172a !important;
                transform: translateY(-1px);
                background: #fff;
                border-color: #cbd5e1;
                box-shadow: 0 12px 20px rgba(15, 23, 42, 0.08);
            }

            .crm-icon-btn i {
                font-size: 19px;
                line-height: 1;
            }

            .crm-icon-btn-preview i {
                color: #2563eb;
            }

            .crm-icon-btn-open i {
                color: #0891b2;
            }

            .crm-icon-btn-download i {
                color: #d97706;
            }

            .crm-icon-btn-close i {
                color: #475569;
            }

            .crm-app-attachment-preview-loading {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 80px;
                padding: 12px;
                border-radius: 3px;
                background: #fff;
                border: 1px dashed #cbd5e1;
                color: #64748b;
                font-size: 12px;
                text-align: center;
            }

            .crm-app-preview-modal .modal-dialog {
                max-width: min(920px, calc(100vw - 40px));
            }

            .crm-app-preview-modal .modal-content {
                border: 1px solid #d7e5ff;
                border-radius: 3px;
                overflow: hidden;
                box-shadow: 0 24px 60px rgba(15, 23, 42, 0.2);
            }

            .crm-app-preview-modal .modal-header {
                align-items: flex-start;
                padding: 18px 20px;
                border-bottom: 1px solid #dbe5f1;
                background: linear-gradient(135deg, rgba(37, 99, 235, 0.08), rgba(14, 165, 233, 0.05));
            }

            .crm-app-preview-modal .modal-title {
                color: #0f172a;
                font-size: 18px;
                line-height: 1.4;
                word-break: break-word;
            }

            .crm-app-preview-modal .modal-body {
                padding: 20px;
                background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
            }

            .crm-app-preview-modal-status {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 420px;
                padding: 24px;
                border-radius: 3px;
                border: 1px dashed #cbd5e1;
                background: #fff;
                color: #64748b;
                font-size: 13px;
                text-align: center;
            }

            .crm-app-preview-modal-body {
                min-height: 420px;
                border: 1px solid #dbe5f1;
                border-radius: 3px;
                background: #fff;
                overflow: hidden;
            }

            .crm-app-preview-modal-image,
            .crm-app-preview-modal-frame {
                display: block;
                width: 100%;
                min-height: 70vh;
                max-height: 76vh;
                border: 0;
                background: #fff;
            }

            .crm-app-preview-modal-image {
                height: auto;
                max-width: 100%;
                object-fit: contain;
            }

            .crm-app-preview-modal-docx {
                min-height: 70vh;
                max-height: 76vh;
                padding: 20px;
                overflow: auto;
                align-items: flex-start;
                justify-content: flex-start;
                text-align: left;
            }

            .crm-app-attachment-copy strong,
            .crm-app-attachment-copy span {
                display: block;
            }

            .crm-app-attachment-copy strong {
                color: #0f172a;
                font-size: 13px;
            }

            .crm-app-attachment-copy span {
                margin-top: 4px;
                color: #64748b;
                font-size: 12px;
            }

            .crm-app-attachment-icon {
                height: 96px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 3px;
                background: linear-gradient(135deg, #c7d2fe 0%, #bfdbfe 45%, #a5f3fc 100%);
                color: #1d4ed8;
                font-size: 36px;
            }

            .crm-app-composer {
                display: grid;
                gap: 18px;
                padding: 22px 24px 24px;
                border-top: 1px solid #dbe5f1;
                background: #fff;
            }

            .crm-app-composer-field {
                position: relative;
            }

            .crm-app-composer textarea {
                min-height: 120px;
            }

            .crm-live-composer-hint {
                margin-top: 8px;
                color: #64748b;
                font-size: 12px;
                line-height: 1.5;
            }

            .crm-app-mention-menu {
                position: absolute;
                left: 0;
                right: 0;
                top: calc(100% + 10px);
                display: grid;
                gap: 6px;
                padding: 10px;
                border: 1px solid #d7e5ff;
                border-radius: 3px;
                background: #fff;
                box-shadow: 0 18px 34px rgba(37, 99, 235, 0.12);
                z-index: 12;
            }

            .crm-app-mention-option {
                display: grid;
                gap: 2px;
                padding: 10px 12px;
                border: 1px solid transparent;
                border-radius: 3px;
                background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
                text-align: left;
                color: #0f172a;
            }

            .crm-app-mention-option strong,
            .crm-app-mention-option span {
                display: block;
            }

            .crm-app-mention-option span {
                color: #64748b;
                font-size: 12px;
            }

            .crm-app-mention-option:hover,
            .crm-app-mention-option.active {
                border-color: #93c5fd;
                background: linear-gradient(135deg, rgba(219, 234, 254, 0.9), rgba(224, 242, 254, 0.82));
            }

            .crm-app-file-grid {
                display: grid;
                gap: 12px;
            }

            .crm-app-file-card {
                display: grid;
                gap: 10px;
                background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
                border: 1px solid #d4e4ff;
                box-shadow: 0 14px 28px rgba(37, 99, 235, 0.08);
            }

            .crm-discussion-form-note {
                padding: 12px 14px;
                border-radius: 3px;
                background: linear-gradient(135deg, rgba(224, 242, 254, 0.9), rgba(239, 246, 255, 0.95));
                border: 1px dashed #93c5fd;
                color: #0f4c81;
                font-size: 13px;
                line-height: 1.55;
            }

            @media (max-width: 1199.98px) {
                .crm-app-shell,
                .crm-discussion-split {
                    grid-template-columns: 1fr;
                }

                .crm-app-sidebar {
                    position: static;
                }
            }

            @media (max-width: 767.98px) {
                .crm-discussions-nav {
                    flex-direction: column;
                }

                .crm-app-main {
                    min-height: 0;
                }

                .crm-app-main-header,
                .crm-discussion-thread-head,
                .crm-discussion-campaign-head,
                .crm-discussion-timeline-head,
                .crm-discussion-source-card {
                    flex-direction: column;
                }

                .crm-app-message-panel,
                .crm-app-composer,
                .crm-app-main-header {
                    padding-left: 16px;
                    padding-right: 16px;
                }
            }
        </style>
    @endpush
@endonce
