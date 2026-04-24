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

            .crm-external-channel {
                --crm-external-accent: #0891b2;
                --crm-external-accent-soft: #ecfeff;
                --crm-external-accent-border: #bae6fd;
                --crm-external-shadow: rgba(8, 145, 178, 0.1);
                --crm-external-head-glow: rgba(8, 145, 178, 0.1);
                --crm-external-gradient-a: #0891b2;
                --crm-external-gradient-b: #67e8f9;
                display: grid;
                gap: 16px;
            }

            .crm-external-channel.is-whatsapp {
                --crm-external-accent: #7c3aed;
                --crm-external-accent-soft: #f5f3ff;
                --crm-external-accent-border: #ddd6fe;
                --crm-external-shadow: rgba(124, 58, 237, 0.1);
                --crm-external-head-glow: rgba(124, 58, 237, 0.1);
                --crm-external-gradient-a: #7c3aed;
                --crm-external-gradient-b: #a78bfa;
            }

            .crm-external-head {
                display: flex;
                justify-content: space-between;
                gap: 18px;
                align-items: flex-start;
                padding: 20px 22px;
                border: 1px solid #e2e8f0;
                border-radius: 3px;
                background:
                    radial-gradient(circle at top right, var(--crm-external-head-glow), transparent 34%),
                    linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
            }

            .crm-external-head.is-compact {
                padding: 18px 20px;
            }

            .crm-external-head-copy {
                display: grid;
                gap: 10px;
                min-width: 0;
            }

            .crm-external-head-copy h2 {
                margin: 0;
                color: #0f172a;
                font-size: 18px;
            }

            .crm-external-head-copy p:not(.crm-kicker) {
                margin: 0;
                color: #64748b;
                line-height: 1.6;
            }

            .crm-external-stat-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(86px, 1fr));
                gap: 10px;
                min-width: 0;
            }

            .crm-external-stat-grid.is-compact {
                grid-template-columns: repeat(3, minmax(86px, 1fr));
            }

            .crm-external-stat {
                display: grid;
                gap: 6px;
                min-width: 86px;
                padding: 10px 12px;
                border: 1px solid #eef2f7;
                border-radius: 3px;
                background: #fcfdff;
                text-align: right;
            }

            .crm-external-stat span {
                color: #64748b;
                font-size: 10px;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .crm-external-stat strong {
                color: #0f172a;
                font-size: 18px;
                line-height: 1.1;
                font-weight: 700;
            }

            .crm-external-index-shell {
                display: grid;
                grid-template-columns: minmax(280px, 0.88fr) minmax(0, 1.12fr);
                gap: 16px;
            }

            .crm-external-activity-column,
            .crm-external-side-column {
                display: grid;
                gap: 16px;
                align-content: start;
            }

            .crm-external-panel,
            .crm-external-channel .crm-card {
                border: 1px solid #e2e8f0;
                border-radius: 3px;
                background: #fff;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
                min-width: 0;
            }

            .crm-external-panel-head,
            .crm-external-channel .crm-card-title {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: flex-start;
                padding: 18px 20px 16px;
                margin: 0;
                border-bottom: 1px solid #eff2f7;
            }

            .crm-external-panel-head h3,
            .crm-external-channel .crm-card-title h2 {
                margin: 0;
                color: #0f172a;
                font-size: 18px;
            }

            .crm-external-panel-body,
            .crm-external-main-card > .crm-form,
            .crm-external-main-card > .crm-stack,
            .crm-external-side-card > .crm-meta-list,
            .crm-external-side-card > .crm-stack,
            .crm-external-side-card > .crm-empty,
            .crm-external-side-card > .crm-form,
            .crm-external-side-card > form {
                padding: 18px 20px;
            }

            .crm-external-main-card > .crm-form .form-actions,
            .crm-external-side-card > form .form-actions {
                margin-top: 28px;
            }

            .crm-external-launchpad,
            .crm-external-activity-list {
                display: grid;
                gap: 12px;
            }

            .crm-external-launch-card,
            .crm-external-activity-row {
                display: grid;
                grid-template-columns: 44px minmax(0, 1fr);
                gap: 14px;
                padding: 16px;
                border: 1px solid #e6edf5;
                border-radius: 3px;
                background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
                transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
            }

            .crm-external-launch-card {
                grid-template-columns: 44px minmax(0, 1fr) auto;
                align-items: center;
            }

            .crm-external-launch-card:hover,
            .crm-external-activity-row:hover {
                border-color: var(--crm-external-accent-border);
                box-shadow: 0 12px 24px var(--crm-external-shadow);
                transform: translateY(-1px);
            }

            .crm-external-launch-icon,
            .crm-external-activity-icon {
                width: 44px;
                height: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 12px;
                background: linear-gradient(135deg, var(--crm-external-gradient-a), var(--crm-external-gradient-b));
                color: #fff;
                font-size: 19px;
                flex: 0 0 auto;
            }

            .crm-external-launch-copy,
            .crm-external-activity-main {
                display: grid;
                gap: 10px;
                min-width: 0;
            }

            .crm-external-launch-copy h3,
            .crm-external-activity-top h3 {
                margin: 0;
                color: #0f172a;
                font-size: 16px;
            }

            .crm-external-launch-copy p,
            .crm-external-activity-main p {
                margin: 0;
                color: #64748b;
                line-height: 1.55;
            }

            .crm-external-launch-actions {
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-end;
                gap: 8px;
            }

            .crm-external-activity-top,
            .crm-external-activity-foot {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                align-items: flex-start;
            }

            .crm-external-activity-foot {
                align-items: center;
                flex-wrap: wrap;
            }

            .crm-external-compose-page .crm-discussion-split,
            .crm-external-thread-page .crm-discussion-split {
                gap: 16px;
            }

            .crm-external-thread-page .crm-discussion-split {
                grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
            }

            .crm-external-channel .crm-meta-list {
                gap: 12px;
            }

            .crm-external-channel .crm-meta-row strong {
                text-align: right;
            }

            .crm-audience-builder {
                display: grid;
                gap: 14px;
                padding: 18px;
                border: 1px solid #dbe5f1;
                border-radius: 3px;
                background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
                box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
            }

            .crm-audience-builder.is-invalid {
                border-color: #fca5a5;
                box-shadow: 0 10px 24px rgba(239, 68, 68, 0.1);
            }

            .crm-audience-builder-summary {
                display: grid;
                gap: 12px;
            }

            .crm-audience-builder-summary-title {
                font-size: 13px;
                font-weight: 700;
                color: #0f172a;
            }

            .crm-audience-builder-summary-copy {
                margin-top: 4px;
                color: #64748b;
                font-size: 12px;
                line-height: 1.55;
            }

            .crm-audience-builder-trigger {
                margin-top: 12px;
                width: fit-content;
            }

            .crm-audience-builder-selected {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .crm-audience-selected-tag {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 12px;
                border: 1px solid #bfdbfe;
                border-radius: 999px;
                background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
                color: #1d4ed8;
                font-size: 12px;
                font-weight: 600;
                transition: all 0.18s ease;
            }

            .crm-audience-selected-tag:hover {
                transform: translateY(-1px);
                box-shadow: 0 10px 18px rgba(37, 99, 235, 0.14);
            }

            .crm-audience-selected-tag i {
                font-size: 16px;
            }

            .crm-email-editor-field.is-invalid .ck.ck-editor__main > .ck-editor__editable {
                border-color: #dc2626 !important;
                box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.08);
            }

            .crm-audience-modal .modal-content {
                border-radius: 3px;
                border: 1px solid #dbe5f1;
                box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
            }

            .crm-audience-modal .modal-header,
            .crm-audience-modal .modal-footer {
                border-color: #e2e8f0;
            }

            .crm-audience-modal .modal-body {
                display: grid;
                gap: 16px;
                padding: 20px;
                background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
            }

            .crm-audience-builder-toolbar {
                display: grid;
                gap: 14px;
            }

            .crm-audience-builder-search {
                position: relative;
            }

            .crm-audience-builder-search i {
                position: absolute;
                top: 50%;
                left: 14px;
                transform: translateY(-50%);
                color: #64748b;
                font-size: 16px;
            }

            .crm-audience-builder-search .form-control {
                padding-left: 40px;
            }

            .crm-audience-builder-filters,
            .crm-audience-builder-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .crm-audience-filter {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 10px 14px;
                border: 1px solid #cbd5e1;
                border-radius: 999px;
                background: #fff;
                color: #334155;
                font-size: 12px;
                font-weight: 600;
                transition: all 0.18s ease;
            }

            .crm-audience-filter:hover,
            .crm-audience-filter.is-active {
                border-color: #93c5fd;
                background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
                color: #1d4ed8;
                box-shadow: 0 10px 18px rgba(37, 99, 235, 0.12);
            }

            .crm-audience-builder-list {
                display: grid;
                gap: 14px;
            }

            .crm-audience-section {
                display: grid;
                gap: 12px;
                padding: 16px;
                border: 1px solid #dbe5f1;
                border-radius: 3px;
                background: rgba(255, 255, 255, 0.92);
            }

            .crm-audience-section-head {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                align-items: flex-start;
            }

            .crm-audience-section-head strong,
            .crm-audience-section-head span {
                display: block;
            }

            .crm-audience-section-head span {
                margin-top: 4px;
                color: #64748b;
                font-size: 12px;
            }

            .crm-audience-options {
                display: grid;
                gap: 10px;
            }

            .crm-audience-option {
                display: block;
                margin: 0;
                cursor: pointer;
            }

            .crm-audience-option-input {
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }

            .crm-audience-option-box {
                position: relative;
                display: flex;
                justify-content: space-between;
                gap: 12px;
                align-items: center;
                padding: 14px 16px 14px 52px;
                border: 1px solid #dbe5f1;
                border-radius: 3px;
                background: #fff;
                transition: all 0.18s ease;
            }

            .crm-audience-option-box::before,
            .crm-audience-option-box::after {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
            }

            .crm-audience-option-box::before {
                content: '';
                left: 16px;
                width: 20px;
                height: 20px;
                border: 1px solid #cbd5e1;
                border-radius: 6px;
                background: #fff;
                transition: all 0.18s ease;
            }

            .crm-audience-option-box::after {
                content: '\ea41';
                left: 20px;
                color: #fff;
                font-family: boxicons !important;
                font-size: 12px;
                opacity: 0;
                transition: opacity 0.18s ease;
            }

            .crm-audience-option-copy strong,
            .crm-audience-option-copy span {
                display: block;
            }

            .crm-audience-option-copy strong {
                color: #0f172a;
                font-size: 13px;
            }

            .crm-audience-option-copy span {
                margin-top: 4px;
                color: #64748b;
                font-size: 12px;
                line-height: 1.45;
            }

            .crm-audience-option:hover .crm-audience-option-box {
                border-color: #93c5fd;
                transform: translateY(-1px);
                box-shadow: 0 12px 22px rgba(37, 99, 235, 0.08);
            }

            .crm-audience-option-input:checked + .crm-audience-option-box {
                border-color: #93c5fd;
                background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
                box-shadow: 0 12px 24px rgba(37, 99, 235, 0.12);
            }

            .crm-audience-option-input:checked + .crm-audience-option-box::before {
                background: #2563eb;
                border-color: #2563eb;
            }

            .crm-audience-option-input:checked + .crm-audience-option-box::after {
                opacity: 1;
            }

            .crm-audience-option-input:focus-visible + .crm-audience-option-box {
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
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
                padding: 10px 12px;
                border-radius: 10px;
                background: #f8fbff;
                border: 1px solid #dbeafe;
                color: #37557a;
                font-size: 13px;
                line-height: 1.55;
            }

            .crm-discussions-hub {
                display: grid;
                gap: 20px;
            }

            .crm-discussions-actions {
                gap: 8px;
            }

            .crm-discussions-metric-strip {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 12px;
            }

            .crm-discussions-metric {
                display: grid;
                gap: 8px;
                padding: 16px 18px;
                border: 1px solid #e2e8f0;
                border-radius: 3px;
                background: #fff;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
            }

            .crm-discussions-metric span {
                color: #64748b;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .crm-discussions-metric strong {
                color: #0f172a;
                font-size: 26px;
                line-height: 1;
                font-weight: 700;
            }

            .crm-discussions-hub-grid {
                display: grid;
                grid-template-columns: minmax(0, 1.12fr) minmax(340px, 0.88fr);
                gap: 20px;
                align-items: start;
            }

            .crm-discussions-surface {
                display: grid;
                gap: 18px;
                padding: 20px 22px;
                border: 1px solid #e2e8f0;
                border-radius: 3px;
                background: #fff;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
            }

            .crm-discussions-surface-head {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: flex-start;
                padding-bottom: 14px;
                border-bottom: 1px solid #eef2f7;
            }

            .crm-discussions-surface-head h2 {
                margin: 0;
                color: #0f172a;
                font-size: 18px;
            }

            .crm-discussions-surface-note {
                color: #64748b;
                font-size: 12px;
                line-height: 1.5;
            }

            .crm-discussions-channel-list,
            .crm-discussions-activity-column,
            .crm-discussions-activity-list,
            .crm-app-compose-grid,
            .crm-app-rail-nav,
            .crm-app-file-list,
            .crm-app-inbox-body,
            .crm-app-inbox-section,
            .crm-app-inbox-list {
                display: grid;
                gap: 0;
            }

            .crm-discussions-channel-row {
                display: grid;
                grid-template-columns: 52px minmax(0, 1fr) auto;
                gap: 16px;
                align-items: start;
                padding: 18px 0;
                border-bottom: 1px solid #eef2f7;
            }

            .crm-discussions-channel-row:last-child,
            .crm-discussions-activity-row:last-child,
            .crm-app-file-row:last-child {
                border-bottom: 0;
            }

            .crm-discussions-channel-row.is-primary {
                margin: 2px 0 8px;
                padding: 18px 16px;
                border: 1px solid #dbeafe;
                border-radius: 3px;
                background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            }

            .crm-discussions-channel-icon,
            .crm-discussions-activity-icon,
            .crm-app-inbox-row-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 12px;
                color: #fff;
                flex: 0 0 auto;
            }

            .crm-discussions-channel-icon {
                width: 52px;
                height: 52px;
                font-size: 22px;
            }

            .crm-discussions-channel-icon.is-app,
            .crm-discussions-activity-icon.is-app,
            .crm-app-inbox-row-icon.is-direct {
                background: linear-gradient(135deg, #2563eb 0%, #38bdf8 100%);
            }

            .crm-discussions-channel-icon.is-email,
            .crm-discussions-activity-icon.is-email {
                background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
            }

            .crm-discussions-channel-icon.is-whatsapp,
            .crm-discussions-activity-icon.is-whatsapp,
            .crm-app-inbox-row-icon.is-group {
                background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
            }

            .crm-app-inbox-row-icon.is-company {
                background: linear-gradient(135deg, #0891b2 0%, #14b8a6 100%);
            }

            .crm-discussions-channel-body,
            .crm-discussions-channel-copy,
            .crm-discussions-activity-copy,
            .crm-app-file-row-copy,
            .crm-app-inbox-row-main,
            .crm-app-thread-header-main {
                min-width: 0;
            }

            .crm-discussions-channel-head,
            .crm-discussions-activity-meta,
            .crm-app-thread-header-top,
            .crm-app-inbox-row-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                align-items: center;
            }

            .crm-discussions-channel-head {
                margin-bottom: 8px;
            }

            .crm-discussions-channel-copy strong,
            .crm-discussions-activity-head strong {
                color: #0f172a;
                font-size: 15px;
                line-height: 1.45;
            }

            .crm-discussions-channel-copy p,
            .crm-discussions-activity-preview,
            .crm-app-workspace-copy p,
            .crm-app-thread-summary {
                margin: 0;
                color: #64748b;
                font-size: 13px;
                line-height: 1.6;
            }

            .crm-discussions-inline-actions {
                display: flex;
                flex-direction: column;
                gap: 8px;
                align-items: stretch;
            }

            .crm-discussions-inline-actions .crm-app-btn {
                justify-content: flex-start;
            }

            .crm-discussions-activity-column {
                gap: 20px;
            }

            .crm-discussions-activity-row {
                display: grid;
                grid-template-columns: 36px minmax(0, 1fr) auto;
                gap: 14px;
                align-items: start;
                padding: 14px 0;
                border-bottom: 1px solid #eef2f7;
            }

            .crm-discussions-activity-icon {
                width: 36px;
                height: 36px;
                font-size: 16px;
                border-radius: 10px;
            }

            .crm-discussions-activity-head,
            .crm-app-inbox-row-top {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                align-items: flex-start;
            }

            .crm-discussions-activity-time,
            .crm-app-inbox-row-time {
                color: #94a3b8;
                font-size: 12px;
                white-space: nowrap;
            }

            .crm-discussions-activity-preview {
                margin-top: 10px;
            }

            .crm-app-workspace-head {
                display: flex;
                justify-content: space-between;
                gap: 20px;
                align-items: flex-start;
                margin-bottom: 20px;
                padding: 20px 22px;
                border: 1px solid #e2e8f0;
                border-radius: 3px;
                background: #fff;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
            }

            .crm-app-workspace-copy h2 {
                margin: 0;
                color: #0f172a;
                font-size: 22px;
            }

            .crm-app-workspace-copy p {
                margin-top: 10px;
                max-width: 760px;
            }

            .crm-app-workspace-stats {
                display: grid;
                grid-auto-flow: column;
                grid-auto-columns: minmax(86px, 1fr);
                gap: 10px;
            }

            .crm-app-workspace-stat {
                display: grid;
                gap: 6px;
                min-width: 92px;
                padding: 10px 12px;
                border: 1px solid #eef2f7;
                border-radius: 3px;
                background: #fcfdff;
                text-align: right;
            }

            .crm-app-workspace-stat span {
                color: #64748b;
                font-size: 10px;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .crm-app-workspace-stat strong {
                color: #0f172a;
                font-size: 20px;
                line-height: 1;
                font-weight: 700;
            }

            .crm-app-shell {
                display: grid;
                grid-template-columns: 240px 330px minmax(0, 1fr);
                gap: 16px;
                align-items: start;
                isolation: isolate;
            }

            .crm-app-shell.is-inbox-hidden {
                grid-template-columns: 240px minmax(0, 1fr);
            }

            .crm-app-rail,
            .crm-app-pane {
                min-width: 0;
                border: 1px solid #e2e8f0;
                border-radius: 3px;
                background: #fff;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
            }

            .crm-app-rail {
                display: grid;
                gap: 18px;
                padding: 18px;
                position: sticky;
                top: 24px;
            }

            .crm-app-rail-section {
                display: grid;
                gap: 12px;
            }

            .crm-app-rail-section + .crm-app-rail-section {
                padding-top: 18px;
                border-top: 1px solid #eff2f7;
            }

            .crm-app-rail-section h3,
            .crm-app-pane-head h3,
            .crm-app-thread-header h3 {
                margin: 0;
                color: #0f172a;
                font-size: 18px;
            }

            .crm-app-compose-grid {
                gap: 8px;
            }

            .crm-app-rail-nav {
                gap: 6px;
            }

            .crm-app-rail-nav-link {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 10px 12px;
                border: 1px solid transparent;
                border-radius: 3px;
                background: #fff;
                color: #334155;
                transition: border-color 0.18s ease, background-color 0.18s ease;
            }

            .crm-app-rail-nav-link:hover,
            .crm-app-rail-nav-link.active {
                border-color: #dbeafe;
                background: #f8fbff;
                color: #1d4ed8;
            }

            .crm-app-rail-nav-link i {
                font-size: 16px;
                color: inherit;
            }

            .crm-app-rail-nav-link span {
                flex: 1 1 auto;
                min-width: 0;
                font-size: 13px;
                font-weight: 600;
            }

            .crm-app-rail-nav-link em {
                color: #64748b;
                font-size: 11px;
                font-style: normal;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .crm-app-file-list {
                gap: 0;
            }

            .crm-app-file-row {
                display: grid;
                grid-template-columns: 34px minmax(0, 1fr) auto;
                gap: 10px;
                align-items: center;
                padding: 10px 0;
                border-bottom: 1px solid #eef2f7;
            }

            .crm-app-file-badge {
                width: 34px;
                height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 10px;
                font-size: 16px;
                line-height: 1;
                border: 1px solid #dbe5f1;
                background: #f8fafc;
                color: #475569;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
            }

            .crm-app-file-badge.is-pdf {
                border-color: #fecaca;
                background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
                color: #dc2626;
            }

            .crm-app-file-badge.is-image {
                border-color: #bfdbfe;
                background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
                color: #2563eb;
            }

            .crm-app-file-badge.is-docx {
                border-color: #c7d2fe;
                background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
                color: #4f46e5;
            }

            .crm-app-file-badge.is-file {
                border-color: #dbe5f1;
                background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
                color: #475569;
            }

            .crm-app-file-row-copy strong,
            .crm-app-file-row-copy span {
                display: block;
            }

            .crm-app-file-row-copy strong {
                color: #0f172a;
                font-size: 13px;
                line-height: 1.45;
            }

            .crm-app-file-row-copy span {
                margin-top: 4px;
                color: #64748b;
                font-size: 12px;
            }

            .crm-app-inbox {
                display: grid;
                grid-template-rows: auto minmax(360px, 1fr);
                min-height: 760px;
                overflow: hidden;
            }

            .crm-app-pane-head {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                align-items: flex-start;
                padding: 18px 18px 14px;
                border-bottom: 1px solid #eff2f7;
                background: #fff;
            }

            .crm-app-inbox-body {
                gap: 0;
                padding: 8px 18px 14px;
                overflow-y: auto;
                align-content: start;
            }

            .crm-app-inbox-section {
                gap: 8px;
                padding: 10px 0;
                align-content: start;
            }

            .crm-app-inbox-section + .crm-app-inbox-section {
                border-top: 1px solid #eff2f7;
            }

            .crm-app-inbox-section-head {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                color: #64748b;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .crm-app-inbox-list {
                gap: 4px;
                align-content: start;
            }

            .crm-app-inbox-row {
                display: grid;
                grid-template-columns: 40px minmax(0, 1fr);
                gap: 12px;
                align-items: start;
                padding: 10px 12px;
                border: 1px solid transparent;
                border-radius: 3px;
                background: #fff;
                transition: border-color 0.18s ease, background-color 0.18s ease;
            }

            .crm-app-inbox-row:hover,
            .crm-app-inbox-row.active {
                border-color: #dbeafe;
                background: #f8fbff;
            }

            .crm-app-inbox-row.active {
                box-shadow: inset 2px 0 0 #6366f1;
            }

            .crm-app-inbox-row.unread {
                background: #fbfdff;
            }

            .crm-app-inbox-avatar {
                width: 38px;
                height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                overflow: hidden;
                flex: 0 0 auto;
                background: linear-gradient(135deg, #2563eb 0%, #38bdf8 100%);
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.22);
            }

            .crm-app-inbox-avatar-photo {
                width: 100%;
                height: 100%;
                display: block;
                object-fit: cover;
                background: #fff;
            }

            .crm-app-inbox-avatar-initials {
                color: #fff;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 0.04em;
                line-height: 1;
            }

            .crm-app-inbox-row-icon {
                width: 38px;
                height: 38px;
                font-size: 17px;
            }

            .crm-app-inbox-row-headline {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                align-items: flex-start;
            }

            .crm-app-inbox-row-identity {
                min-width: 0;
            }

            .crm-app-inbox-row-headline strong {
                color: #0f172a;
                font-size: 14px;
                line-height: 1.45;
            }

            .crm-app-inbox-row-secondary {
                display: block;
                margin-top: 2px;
                color: #64748b;
                font-size: 12px;
                line-height: 1.4;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .crm-app-inbox-row-summary {
                margin-top: 6px;
                color: #475569;
                font-size: 12px;
                line-height: 1.45;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 2;
                overflow: hidden;
            }

            .crm-app-inbox-row-meta {
                margin-top: 6px;
                color: #64748b;
                font-size: 11px;
                line-height: 1.4;
            }

            .crm-app-inbox-row-dot {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                color: #2563eb;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .crm-app-inbox-row-dot::before {
                content: '';
                width: 7px;
                height: 7px;
                border-radius: 999px;
                background: currentColor;
            }

            .crm-app-thread-panel {
                display: grid;
                grid-template-rows: auto minmax(360px, 1fr) auto;
                min-height: 760px;
                overflow: hidden;
            }

            .crm-app-thread-header {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: flex-start;
                padding: 18px 22px 16px;
                border-bottom: 1px solid #eff2f7;
                background: #fff;
            }

            .crm-app-thread-summary {
                margin-top: 8px;
            }

            .crm-app-thread-actions {
                flex-shrink: 0;
            }

            .crm-app-message-panel {
                padding: 18px 22px;
                overflow-y: auto;
                background: #fcfdff;
            }

            .crm-app-message-stream {
                gap: 14px;
            }

            .crm-app-message-bubble {
                width: min(100%, 760px);
                gap: 10px;
                padding: 14px 16px;
                border-radius: 14px;
                border: 1px solid #e6edf5;
                background: #fff;
                box-shadow: none;
            }

            .crm-app-message-row.mine .crm-app-message-bubble {
                border-color: #dbeafe;
                background: #f8fbff;
                box-shadow: none;
            }

            .crm-app-message-meta {
                align-items: flex-start;
                color: #94a3b8;
                font-size: 10px;
            }

            .crm-app-message-meta strong {
                color: #334155;
                font-size: 11px;
            }

            .crm-app-message-bubble p {
                color: #334155;
                font-size: 12px;
                line-height: 1.55;
            }

            .crm-app-attachment-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 10px;
            }

            .crm-app-attachment {
                padding: 12px;
                border-radius: 12px;
                background: #fbfdff;
                border: 1px solid #e6edf5;
                box-shadow: none;
            }

            .crm-message-receipt {
                padding: 4px 9px;
                font-size: 10px;
                letter-spacing: 0.02em;
            }

            .crm-app-composer {
                padding: 16px 22px 22px;
                border-top: 1px solid #eff2f7;
                background: #fff;
            }

            .crm-app-composer textarea {
                min-height: 104px;
                border-color: #d7dee7;
                background: #fff;
            }

            .crm-live-composer-hint {
                margin-top: 8px;
                color: #64748b;
                font-size: 12px;
                line-height: 1.5;
            }

            .crm-app-mention-menu {
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                background: #fff;
                box-shadow: 0 14px 28px rgba(15, 23, 42, 0.1);
            }

            .crm-app-mention-option {
                border-radius: 10px;
                background: #fff;
            }

            .crm-app-mention-option:hover,
            .crm-app-mention-option.active {
                border-color: #dbeafe;
                background: #f8fbff;
            }

            .crm-discussion-dropzone {
                gap: 12px;
                padding: 14px 16px;
                border: 1px solid #e2e8f0;
                border-style: solid;
                background: #fcfdff;
                box-shadow: none;
            }

            .crm-discussion-dropzone:hover,
            .crm-discussion-dropzone.is-dragover {
                border-color: #cbd5e1;
                background: #fff;
                box-shadow: none;
            }

            .crm-discussion-dropzone-head {
                display: grid;
                grid-template-columns: 38px minmax(0, 1fr) auto;
                gap: 12px;
                align-items: center;
            }

            .crm-discussion-dropzone-icon {
                width: 38px;
                height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 12px;
                background: #eff6ff;
                color: #2563eb;
                font-size: 18px;
            }

            .crm-discussion-dropzone-copy strong,
            .crm-discussion-dropzone-copy p {
                display: block;
                margin: 0;
            }

            .crm-discussion-dropzone-copy strong {
                color: #0f172a;
                font-size: 13px;
                line-height: 1.35;
            }

            .crm-discussion-dropzone-copy p {
                margin-top: 3px;
                color: #64748b;
                font-size: 12px;
                line-height: 1.45;
            }

            .crm-discussion-dropzone-trigger {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 7px 11px;
                border: 1px solid #d7dee7;
                border-radius: 999px;
                background: #fff;
                color: #334155;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .crm-discussion-dropzone-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                padding-left: 50px;
            }

            .crm-discussion-dropzone-meta span {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 5px 9px;
                border-radius: 999px;
                background: #fff;
                border: 1px solid #eef2f7;
                color: #64748b;
                font-size: 11px;
                line-height: 1;
            }

            .crm-discussion-dropzone .crm-dropzone-list {
                gap: 6px;
                padding-top: 10px;
                border-top: 1px solid #eef2f7;
            }

            .crm-discussion-dropzone .crm-dropzone-empty {
                padding: 4px 0 0 50px;
                color: #94a3b8;
                font-size: 11px;
                text-align: left;
            }

            .crm-discussion-dropzone .crm-dropzone-file {
                position: relative;
                display: grid;
                grid-template-columns: 28px minmax(0, 1fr) auto;
                gap: 10px;
                align-items: center;
                padding: 8px 10px;
                border-radius: 10px;
                background: #fff;
                border: 1px solid #eef2f7;
            }

            .crm-discussion-dropzone .crm-dropzone-file::before {
                content: 'FILE';
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 28px;
                height: 28px;
                border-radius: 9px;
                background: #eff6ff;
                color: #2563eb;
                font-size: 9px;
                font-weight: 700;
                letter-spacing: 0.04em;
            }

            .crm-discussion-dropzone .crm-dropzone-file strong {
                font-size: 12px;
                color: #0f172a;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .crm-discussion-dropzone .crm-dropzone-file span {
                font-size: 11px;
                color: #64748b;
            }

            .crm-discussion-attachment-list {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                gap: 8px;
            }

            .crm-discussion-attachment-row {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 12px;
                align-items: center;
                padding: 10px 12px;
                border: 1px solid #e6edf5;
                border-radius: 12px;
                background: #fff;
                box-shadow: none;
            }

            .crm-discussion-attachment-file {
                display: grid;
                grid-template-columns: 34px minmax(0, 1fr);
                gap: 10px;
                align-items: center;
                min-width: 0;
            }

            .crm-discussion-attachment-badge {
                width: 34px;
                height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 10px;
                background: #eff6ff;
                color: #2563eb;
                font-size: 17px;
            }

            .crm-discussion-attachment-copy,
            .crm-discussion-attachment-copy strong,
            .crm-discussion-attachment-copy span {
                display: block;
                min-width: 0;
            }

            .crm-discussion-attachment-copy strong {
                color: #0f172a;
                font-size: 12px;
                line-height: 1.4;
                white-space: normal;
                overflow-wrap: anywhere;
                word-break: break-word;
            }

            .crm-discussion-attachment-copy span {
                margin-top: 2px;
                color: #64748b;
                font-size: 11px;
                line-height: 1.4;
            }

            .crm-discussion-attachment-actions {
                gap: 6px;
                justify-content: flex-end;
                flex-wrap: nowrap;
            }

            .crm-discussion-attachment-actions .crm-btn-light {
                padding: 7px 10px;
                border-radius: 10px;
                font-size: 12px;
            }

            .crm-app-btn {
                padding: 8px 12px;
                border-color: #d7dee7;
                box-shadow: none;
                font-size: 12px;
            }

            .crm-app-btn:hover {
                background: #f8fbff;
                box-shadow: none;
            }

            .crm-app-btn i {
                font-size: 17px;
            }

            .crm-icon-btn {
                width: 34px;
                min-width: 34px;
                height: 34px;
                border-color: #d7dee7;
                box-shadow: none;
            }

            .crm-icon-btn:hover {
                background: #f8fbff;
                box-shadow: none;
            }

            .crm-icon-btn i {
                font-size: 17px;
            }

            @media (max-width: 1399.98px) {
                .crm-app-shell {
                    grid-template-columns: 220px 300px minmax(0, 1fr);
                }

                .crm-app-shell.is-inbox-hidden {
                    grid-template-columns: 220px minmax(0, 1fr);
                }
            }

            @media (max-width: 1199.98px) {
                .crm-discussions-hub-grid {
                    grid-template-columns: 1fr;
                }

                .crm-discussions-metric-strip {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .crm-app-workspace-head,
                .crm-app-shell,
                .crm-discussion-split,
                .crm-external-index-shell {
                    grid-template-columns: 1fr;
                }

                .crm-app-workspace-head,
                .crm-external-head {
                    display: grid;
                }

                .crm-app-rail {
                    position: static;
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }

                .crm-app-rail-section + .crm-app-rail-section {
                    padding-top: 0;
                    border-top: 0;
                }

                .crm-app-rail-section:last-child {
                    grid-column: 1 / -1;
                    padding-top: 18px;
                    border-top: 1px solid #eff2f7;
                }

                .crm-external-stat-grid,
                .crm-external-stat-grid.is-compact {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 767.98px) {
                .crm-discussions-actions {
                    width: 100%;
                }

                .crm-discussions-actions .crm-app-btn,
                .crm-discussions-inline-actions .crm-app-btn,
                .crm-app-compose-grid .crm-app-btn {
                    width: 100%;
                    justify-content: flex-start;
                }

                .crm-discussions-metric-strip,
                .crm-app-workspace-stats {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    grid-auto-flow: row;
                }

                .crm-discussions-channel-row,
                .crm-discussions-activity-row,
                .crm-app-rail {
                    grid-template-columns: 1fr;
                }

                .crm-discussions-surface-head,
                .crm-app-pane-head,
                .crm-app-thread-header,
                .crm-external-panel-head,
                .crm-external-activity-top,
                .crm-external-activity-foot,
                .crm-external-launch-card {
                    display: grid;
                }

                .crm-app-inbox,
                .crm-app-thread-panel {
                    min-height: 0;
                }

                .crm-app-rail-section:last-child {
                    grid-column: auto;
                }

                .crm-app-thread-actions {
                    width: 100%;
                }

                .crm-discussion-dropzone-head,
                .crm-discussion-attachment-row {
                    grid-template-columns: 1fr;
                }

                .crm-external-stat-grid,
                .crm-external-stat-grid.is-compact {
                    grid-template-columns: 1fr;
                }

                .crm-external-launch-card,
                .crm-external-activity-row,
                .crm-external-thread-page .crm-discussion-split {
                    grid-template-columns: 1fr;
                }

                .crm-external-launch-actions {
                    justify-content: flex-start;
                }

                .crm-discussion-dropzone-meta,
                .crm-discussion-dropzone .crm-dropzone-empty {
                    padding-left: 0;
                }

                .crm-discussion-attachment-actions {
                    justify-content: flex-start;
                    flex-wrap: wrap;
                }

                .crm-app-message-panel,
                .crm-app-composer,
                .crm-app-pane-head,
                .crm-app-thread-header,
                .crm-app-inbox-body,
                .crm-discussions-surface {
                    padding-left: 16px;
                    padding-right: 16px;
                }
            }
        </style>
    @endpush
@endonce
