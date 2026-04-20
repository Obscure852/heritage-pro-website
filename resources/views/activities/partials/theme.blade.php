<style>
    .activities-container,
    .activity-show-container {
        background: #fff;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .form-container {
        background: white;
        border-radius: 3px;
        padding: 32px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .activities-header,
    .activity-show-container .page-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: #fff;
        padding: 28px;
        border-radius: 3px 3px 0 0;
    }

    .activities-body,
    .activity-show-body {
        padding: 24px;
    }

    .activities-container .page-title,
    .activity-show-container .page-title {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
    }

    .activities-container .page-subtitle,
    .activity-show-container .page-subtitle {
        margin-top: 8px;
        color: rgba(255, 255, 255, 0.9);
    }

    .form-container .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e5e7eb;
    }

    .form-container .page-title {
        font-size: 22px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .form-container .page-subtitle {
        margin-top: 8px;
        color: #6b7280;
    }

    .form-body {
        padding: 0;
    }

    .stat-item {
        background: rgba(255, 255, 255, 0.14);
        border-radius: 3px;
        padding: 14px;
    }

    .stat-item h4 {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .stat-item small {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card-shell {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        background: #fff;
    }

    .help-text,
    .info-note {
        border-left: 4px solid #3b82f6;
        background: #f8fbff;
        padding: 16px 18px;
        margin-bottom: 24px;
    }

    .info-note {
        border-left-color: #10b981;
        background: #f3fffb;
    }

    .help-title {
        font-weight: 600;
        margin-bottom: 6px;
        color: #1f2937;
    }

    .form-container .help-text,
    .form-container .info-note {
        background: #f8f9fa;
        padding: 12px;
        border-left: 4px solid #3b82f6;
        border-radius: 0 3px 3px 0;
        margin-bottom: 24px;
    }

    .form-container .info-note {
        border-left-color: #10b981;
        background: #f3fffb;
    }

    .form-container .help-content {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.4;
    }

    .section-title {
        font-size: 16px;
        font-weight: 600;
        margin: 24px 0 16px 0;
        color: #1f2937;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    .form-grid + .form-grid {
        margin-top: 16px;
    }

    .form-group {
        margin-bottom: 0;
    }

    .grid-span-full {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
        font-size: 14px;
    }

    .form-container .form-control,
    .form-container .form-select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        font-size: 14px;
        transition: all 0.2s;
    }

    .form-container .form-control:focus,
    .form-container .form-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .input-icon-group {
        position: relative;
    }

    .input-icon-group .input-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-size: 13px;
        pointer-events: none;
        z-index: 2;
    }

    .input-icon-group .form-control,
    .input-icon-group .form-select {
        padding-left: 40px;
    }

    .textarea-icon-group .input-icon {
        top: 16px;
        transform: none;
    }

    .textarea-icon-group textarea.form-control {
        padding-left: 40px;
        min-height: 120px;
    }

    .required,
    .text-danger {
        color: #dc2626;
    }

    .option-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-top: 16px;
    }

    .option-card {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 14px 16px;
        background: #f9fafb;
    }

    .option-card .form-check {
        margin: 0;
    }

    .option-card .form-check-label {
        color: #374151;
        font-weight: 500;
    }

    .option-card .option-help {
        display: block;
        margin-top: 6px;
        color: #6b7280;
        font-size: 12px;
        line-height: 1.4;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-draft {
        background: #eef2ff;
        color: #4338ca;
    }

    .status-active {
        background: #ecfdf5;
        color: #047857;
    }

    .status-paused {
        background: #fff7ed;
        color: #c2410c;
    }

    .status-closed {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .status-archived {
        background: #f3f4f6;
        color: #4b5563;
    }

    .activity-meta-pills {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 8px;
    }

    .meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
    }

    .meta-pill-code {
        background: #eef2ff;
        color: #4338ca;
    }

    .meta-pill-location {
        background: #ecfeff;
        color: #0f766e;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
    }

    .detail-card {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 18px;
        background: #fff;
    }

    .detail-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .detail-value {
        font-weight: 600;
        color: #111827;
    }

    .summary-card-title {
        font-size: 15px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 12px;
    }

    .summary-chip-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .summary-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #f3f4f6;
        color: #374151;
        font-size: 12px;
        font-weight: 500;
    }

    .summary-empty {
        color: #6b7280;
        font-size: 13px;
    }

    .management-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.9fr);
        gap: 24px;
    }

    .management-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 20px;
    }

    .management-subtitle {
        color: #6b7280;
        margin: 6px 0 0 0;
        font-size: 14px;
    }

    .management-list {
        display: grid;
        gap: 12px;
    }

    .management-item {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 16px;
        background: #fff;
    }

    .management-item-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .management-item-title {
        font-weight: 600;
        color: #111827;
    }

    .management-item-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }

    .management-item-notes {
        margin-top: 10px;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
    }

    .roster-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .roster-summary-value {
        font-size: 1.4rem;
        font-weight: 700;
        color: #111827;
    }

    .enrollment-status-chip {
        font-weight: 600;
    }

    .enrollment-status-active {
        background: #ecfdf5;
        color: #047857;
    }

    .enrollment-status-withdrawn {
        background: #fff7ed;
        color: #c2410c;
    }

    .enrollment-status-completed {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .enrollment-status-suspended {
        background: #fef3c7;
        color: #b45309;
    }

    .roster-status-form {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #f3f4f6;
    }

    .roster-action-grid {
        display: grid;
        grid-template-columns: minmax(170px, 0.9fr) minmax(150px, 0.8fr) minmax(220px, 1.5fr) auto;
        gap: 12px;
        align-items: end;
    }

    .roster-reason-field {
        min-width: 0;
    }

    .roster-action-submit {
        display: flex;
        align-items: flex-end;
    }

    .candidate-preview-list {
        display: grid;
        gap: 10px;
    }

    .candidate-preview-item {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 12px 14px;
        background: #fff;
    }

    .candidate-select-list {
        max-height: 360px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .candidate-checkbox-item {
        display: grid;
        grid-template-columns: 22px minmax(0, 1fr);
        gap: 12px;
        align-items: start;
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .candidate-checkbox-item:hover {
        border-color: #bfdbfe;
        background: #f8fbff;
    }

    .candidate-checkbox-shell {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 100%;
        padding-top: 2px;
    }

    .candidate-checkbox-shell input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #2563eb;
        cursor: pointer;
    }

    .candidate-preview-content {
        min-width: 0;
    }

    .bulk-select-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
        padding: 12px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #f9fafb;
    }

    .bulk-select-toggle {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #111827;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        margin: 0;
    }

    .bulk-select-toggle input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #2563eb;
    }

    .bulk-select-count {
        color: #4b5563;
        font-size: 13px;
        font-weight: 600;
    }

    .bulk-search-shell {
        position: relative;
        margin-bottom: 14px;
    }

    .bulk-search-shell .search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-size: 13px;
        pointer-events: none;
    }

    .bulk-search-input {
        width: 100%;
        padding: 10px 12px 10px 40px;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        font-size: 14px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .bulk-search-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .candidate-preview-title {
        font-weight: 600;
        color: #111827;
    }

    .candidate-preview-meta {
        margin-top: 4px;
        color: #6b7280;
        font-size: 13px;
    }

    .candidate-preview-pill-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .candidate-preview-submeta {
        display: block;
        margin-top: 6px;
        color: #6b7280;
        font-size: 13px;
    }

    .bulk-search-empty {
        display: none;
        margin-top: -2px;
        margin-bottom: 12px;
        color: #6b7280;
        font-size: 13px;
    }

    .pill-primary {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .pill-muted {
        background: #f3f4f6;
        color: #4b5563;
    }

    .event-status-scheduled {
        background: #eef2ff;
        color: #4338ca;
    }

    .event-status-completed {
        background: #ecfdf5;
        color: #047857;
    }

    .event-status-postponed {
        background: #fff7ed;
        color: #c2410c;
    }

    .event-status-cancelled {
        background: #fee2e2;
        color: #b91c1c;
    }

    .compact-stack {
        gap: 12px;
    }

    .result-register-list {
        display: grid;
        gap: 12px;
    }

    .result-register-item {
        padding: 14px;
    }

    .participant-result-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
    }

    .participant-result-grid .result-notes-input {
        grid-column: 1 / -1;
    }

    .inline-delete-form {
        margin: 0;
    }

    .inline-delete-form .btn {
        padding: 8px 12px;
        min-height: auto;
    }

    .multi-select-shell {
        min-height: 160px;
    }

    .field-help {
        margin-top: 6px;
        color: #6b7280;
        font-size: 12px;
        line-height: 1.4;
    }

    .section-stack {
        display: grid;
        gap: 18px;
    }

    .subnav-links {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 24px;
    }

    .subnav-links .btn {
        padding: 9px 14px;
        border-radius: 999px;
    }

    .module-nav-links {
        margin-top: -6px;
    }

    .form-actions,
    .activities-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .form-actions {
        justify-content: flex-end;
        padding-top: 24px;
        border-top: 1px solid #f3f4f6;
        margin-top: 32px;
    }

    .form-actions form {
        margin: 0;
    }

    .activities-actions {
        justify-content: flex-end;
        margin-top: 24px;
    }

    .action-buttons {
        display: flex;
        gap: 4px;
        justify-content: flex-end;
    }

    .action-buttons .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
        transition: all 0.2s ease;
    }

    .action-buttons .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .action-buttons .btn i {
        font-size: 16px;
    }

    .controls .form-control,
    .controls .form-select {
        font-size: 0.9rem;
    }

    .filter-actions {
        display: flex;
        gap: 8px;
    }

    .filter-actions .btn {
        height: 38px;
        min-height: 38px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        line-height: 1;
    }

    .filter-actions .btn-light {
        border: 1px solid #d1d5db;
        background: #f3f4f6;
        color: #111827;
    }

    .filter-actions .btn-light:hover {
        background: #e5e7eb;
        color: #111827;
    }

    .activities-body .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: white;
        font-weight: 500;
        padding: 10px 16px;
        border-radius: 3px;
        transition: all 0.2s ease;
    }

    .activities-body .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }

    .table thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
    }

    .table tbody tr:hover {
        background-color: #f9fafb;
    }

    .empty-state {
        padding: 40px 0;
        color: #6b7280;
        text-align: center;
    }

    .empty-state .btn {
        margin-bottom: 16px;
    }

    .report-filter-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 16px;
    }

    .audit-filter-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .report-secondary-grid {
        align-items: start;
    }

    .report-table td,
    .audit-table td {
        vertical-align: top;
    }

    .report-metric {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
    }

    .report-metric + .report-metric-note,
    .report-metric-note + .report-metric-note {
        margin-top: 4px;
    }

    .report-metric-note,
    .billing-ledger-note {
        font-size: 12px;
        color: #6b7280;
        line-height: 1.45;
    }

    .billing-status-chip.status-posted {
        background: rgba(5, 150, 105, 0.12);
        color: #047857;
    }

    .billing-status-chip.status-pending {
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
    }

    .billing-status-chip.status-blocked {
        background: rgba(245, 158, 11, 0.14);
        color: #b45309;
    }

    .billing-status-chip.status-cancelled {
        background: rgba(107, 114, 128, 0.14);
        color: #4b5563;
    }

    .audit-notes-cell {
        min-width: 220px;
    }

    .audit-payload-stack {
        display: grid;
        gap: 8px;
        min-width: 280px;
    }

    .audit-payload-card {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #f8fafc;
        overflow: hidden;
    }

    .audit-payload-card summary {
        cursor: pointer;
        list-style: none;
        padding: 10px 12px;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        background: #f3f4f6;
    }

    .audit-payload-card summary::-webkit-details-marker {
        display: none;
    }

    .audit-payload {
        margin: 0;
        padding: 12px;
        font-size: 12px;
        line-height: 1.5;
        color: #1f2937;
        background: #fff;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .empty-state-icon {
        font-size: 48px;
        opacity: 0.3;
        margin-bottom: 12px;
    }

    .form-container .btn {
        padding: 10px 20px;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .form-container .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .form-container .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }

    .form-container .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .form-container .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-1px);
        color: white;
    }

    .btn-loading.loading .btn-text {
        display: none;
    }

    .btn-loading.loading .btn-spinner {
        display: inline-flex !important;
        align-items: center;
    }

    .btn-loading:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    @media (max-width: 992px) {
        .form-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .activities-header,
        .activity-show-container .page-header {
            padding: 20px;
        }

        .activities-body,
        .activity-show-body {
            padding: 16px;
        }

        .form-container {
            padding: 20px;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .filter-actions {
            width: 100%;
        }

        .management-grid {
            grid-template-columns: 1fr;
        }

        .roster-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .report-filter-grid,
        .audit-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .roster-action-grid {
            grid-template-columns: 1fr;
        }

        .participant-result-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .bulk-select-toolbar {
            flex-direction: column;
            align-items: flex-start;
        }

        .roster-action-submit .btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        .form-grid,
        .option-grid {
            grid-template-columns: 1fr;
        }

        .report-filter-grid,
        .audit-filter-grid {
            grid-template-columns: 1fr;
        }

        .participant-result-grid {
            grid-template-columns: 1fr;
        }

        .roster-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
