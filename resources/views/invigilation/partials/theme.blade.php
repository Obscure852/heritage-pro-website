@include('academic.partials.module-theme')

<style>
    .invigilation-filter-row {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .invigilation-daily-series-select {
        min-width: 340px;
        max-width: 420px;
    }

    .invigilation-daily-actions {
        gap: 10px;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
    }

    .invigilation-daily-action-cluster {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        padding: 10px 12px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(10px);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }

    .invigilation-layout-toggle {
        border-radius: 999px;
        overflow: hidden;
        padding: 4px;
        background: rgba(15, 23, 42, 0.16);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
    }

    .invigilation-layout-button {
        border: 0;
        border-radius: 999px !important;
        background: transparent;
        color: rgba(255, 255, 255, 0.78);
        font-size: 13px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        transition: background-color 0.18s ease, color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
    }

    .invigilation-layout-button:hover,
    .invigilation-layout-button:focus {
        color: #fff;
        background: rgba(255, 255, 255, 0.1);
        box-shadow: none;
        transform: translateY(-1px);
    }

    .invigilation-layout-button.active {
        background: #fff;
        color: #0f172a;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.18);
    }

    .invigilation-header-button {
        min-height: 42px;
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        transition: background-color 0.18s ease, border-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
    }

    .invigilation-header-button:hover,
    .invigilation-header-button:focus {
        color: #fff;
        border-color: rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.18);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.14);
        transform: translateY(-1px);
    }

    .invigilation-header-button.disabled,
    .invigilation-header-button:disabled {
        opacity: 0.55;
        color: rgba(255, 255, 255, 0.8);
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.12);
        box-shadow: none;
        transform: none;
        cursor: not-allowed;
        pointer-events: none;
    }

    .invigilation-header-action-icon {
        width: 22px;
        height: 22px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        flex-shrink: 0;
        background: rgba(255, 255, 255, 0.16);
    }

    .invigilation-layout-button.active .invigilation-header-action-icon {
        background: #e0f2fe;
        color: #0284c7;
    }

    .invigilation-layout-button:not(.active):hover .invigilation-header-action-icon,
    .invigilation-layout-button:not(.active):focus .invigilation-header-action-icon,
    .invigilation-header-button:hover .invigilation-header-action-icon,
    .invigilation-header-button:focus .invigilation-header-action-icon {
        background: rgba(255, 255, 255, 0.22);
    }

    .invigilation-header-button .invigilation-header-action-icon {
        background: rgba(15, 23, 42, 0.18);
    }

    .invigilation-table-card .card-body {
        padding: 0;
    }

    .invigilation-table-card {
        overflow: hidden;
    }

    .invigilation-table-card .table-responsive {
        padding: 0;
        margin: 0;
    }

    .invigilation-table-card .table {
        margin-bottom: 0;
    }

    .invigilation-workspace-table {
        margin: 0;
        width: calc(100% + 48px);
        margin-left: -24px;
        margin-right: -24px;
        margin-bottom: 0;
        border-top: 1px solid #e5e7eb;
        background: #fff;
        box-shadow: none;
    }

    .invigilation-workspace-table .table-responsive {
        padding: 0;
    }

    .invigilation-workspace-table .table {
        margin-bottom: 0;
    }

    .invigilation-modal .modal-content {
        border: none;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 18px 48px rgba(15, 23, 42, 0.18);
    }

    .invigilation-modal .modal-header {
        background: #fff;
        color: #111827;
        border-bottom: 1px solid #e5e7eb;
        padding: 20px 24px;
    }

    .invigilation-modal .btn-close {
        filter: none;
        opacity: 0.7;
    }

    .invigilation-modal .modal-body,
    .invigilation-modal .modal-footer {
        padding: 24px;
    }

    .invigilation-modal .select2-container {
        width: 100% !important;
    }

    .invigilation-modal .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 3px;
        display: flex;
        align-items: center;
        padding: 0 36px 0 0;
    }

    .invigilation-modal .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #111827;
        line-height: 36px;
        padding-left: 12px;
        padding-right: 12px;
        width: 100%;
    }

    .invigilation-modal .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6b7280;
    }

    .invigilation-modal .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 8px;
    }

    .invigilation-modal .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .invigilation-modal .modal-footer {
        border-top: 1px solid #e5e7eb;
    }

    .invigilation-grid {
        display: grid;
        grid-template-columns: minmax(320px, 0.9fr) minmax(0, 1.1fr);
        gap: 24px;
        align-items: start;
    }

    .invigilation-grid + .invigilation-grid {
        margin-top: 24px;
    }

    .invigilation-section + .invigilation-section {
        margin-top: 24px;
    }

    .invigilation-section-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .invigilation-section-title {
        margin: 0;
        color: #111827;
        font-size: 16px;
        font-weight: 600;
    }

    .invigilation-section-subtitle {
        color: #6b7280;
        font-size: 13px;
        margin: 6px 0 0 0;
    }

    .required-label::after {
        content: ' *';
        color: #dc2626;
        font-weight: 700;
    }

    .invigilation-meta-pills,
    .invigilation-issue-pills {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .invigilation-issue-pills {
        margin-top: 12px;
    }

    .summary-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 12px;
        font-weight: 600;
    }

    .summary-chip.pill-muted {
        background: #f3f4f6;
        color: #4b5563;
    }

    .summary-chip.status-draft {
        background: #eff6ff;
        color: #2563eb;
    }

    .summary-chip.status-published {
        background: #ecfdf5;
        color: #047857;
    }

    .summary-chip.status-archived {
        background: #f3f4f6;
        color: #4b5563;
    }

    .summary-chip.issue-shortages {
        background: #fef3c7;
        color: #b45309;
    }

    .summary-chip.issue-conflicts {
        background: #fee2e2;
        color: #b91c1c;
    }

    .series-table-name {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .series-table-name strong {
        color: #111827;
    }

    .session-row-title {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .session-row-lock-indicator {
        width: 20px;
        height: 20px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
        font-size: 11px;
        box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.12);
    }

    .series-table-meta {
        color: #6b7280;
        font-size: 12px;
    }

    .series-actions,
    .room-actions,
    .assignment-actions {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .series-actions form,
    .room-actions form,
    .assignment-actions form {
        margin: 0;
    }

    .workspace-toolbar {
        align-items: center;
    }

    .invigilation-body-workspace {
        padding: 24px 24px 0 24px;
    }

    .invigilation-workspace-top {
        padding-bottom: 24px;
    }

    .workspace-actions {
        gap: 10px;
        align-items: center;
    }

    .workspace-actions form {
        margin: 0;
    }

    .invigilation-icon-button {
        width: 40px;
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }

    .invigilation-issue-trigger {
        position: relative;
    }

    .invigilation-action-badge {
        position: absolute;
        top: -7px;
        right: -7px;
        min-width: 20px;
        height: 20px;
        border-radius: 999px;
        background: #dc2626;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 6px;
        box-shadow: 0 0 0 3px #fff;
    }

    .issue-list {
        display: grid;
        gap: 12px;
    }

    .issue-list-modal {
        margin-top: 20px;
    }

    .issue-card {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 14px 16px;
        background: #fff;
    }

    .issue-card.issue-danger {
        border-left: 4px solid #dc2626;
        background: #fff7f7;
    }

    .issue-card.issue-warning {
        border-left: 4px solid #f59e0b;
        background: #fffbeb;
    }

    .issue-card-title {
        font-weight: 600;
        color: #111827;
        margin-bottom: 6px;
    }

    .issue-card-copy {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
    }

    .assignment-stack {
        display: grid;
        gap: 10px;
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px solid #e5e7eb;
    }

    .assignment-stack-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .assignment-stack-title {
        margin: 0;
        color: #111827;
        font-size: 14px;
        font-weight: 600;
    }

    .assignment-stack-copy {
        margin: 6px 0 0 0;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
    }

    .assignment-inline-form {
        display: none;
    }

    .assignment-field {
        min-width: 0;
    }

    .assignment-row {
        padding: 14px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #fff;
    }

    .assignment-edit-grid {
        display: grid;
        grid-template-columns: minmax(260px, 1.35fr) minmax(240px, 1fr);
        gap: 14px;
        align-items: end;
    }

    .assignment-row-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px dashed #e5e7eb;
    }

    .assignment-row-controls {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        margin-left: auto;
    }

    .assignment-row.assignment-auto {
        background: #f8fbff;
    }

    .assignment-row.assignment-locked {
        border-color: #bfdbfe;
        box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.1);
    }

    .assignment-row.assignment-readonly {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) minmax(180px, 1fr) auto;
        align-items: start;
    }

    .assignment-meta {
        color: #6b7280;
        font-size: 12px;
    }

    .assignment-note-copy {
        font-size: 13px;
        line-height: 1.5;
    }

    .assignment-lock-toggle {
        display: flex;
        align-items: center;
    }

    .assignment-lock-toggle .form-check {
        margin: 0;
    }

    .assignment-lock-state {
        align-self: center;
        justify-self: end;
    }

    .inline-field-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .room-source-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .series-card + .series-card {
        margin-top: 16px;
    }

    .series-card-header {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .session-shell + .session-shell {
        margin-top: 18px;
    }

    .session-detail-row > td {
        padding: 0 !important;
        background: #f8fafc;
        border-top: none;
    }

    .session-detail-panel {
        padding: 24px;
        border-top: 1px solid #e5e7eb;
    }

    .session-lock-copy {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #6b7280;
        font-size: 13px;
        font-weight: 600;
    }

    .session-detail-toolbar {
        margin-bottom: 18px;
    }

    .session-detail-summary {
        color: #6b7280;
        font-size: 13px;
        margin-top: 14px;
    }

    .session-readonly-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .session-readonly-value {
        font-size: 18px;
        line-height: 1.3;
    }

    .session-modal-subsection {
        padding: 14px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #f8fafc;
    }

    .session-modal-subsection-title {
        color: #111827;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .session-modal-subsection-copy {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
    }

    .initial-room-source-field {
        display: none;
    }

    .invigilation-select2-option {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        width: 100%;
    }

    .invigilation-select2-subject {
        color: #111827;
        font-weight: 500;
    }

    .invigilation-select2-grade {
        color: #6b7280;
        font-size: 12px;
        text-align: right;
        white-space: nowrap;
    }

    .room-readonly-grid {
        margin-bottom: 16px;
    }

    .session-inline-card {
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    .session-room-table .table td {
        vertical-align: top;
    }

    .report-empty {
        color: #6b7280;
        font-size: 14px;
        margin: 0;
    }

    .invigilation-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 10px;
        padding: 36px 20px;
        min-height: 220px;
    }

    .invigilation-empty-state-compact {
        min-height: 180px;
        padding: 28px 20px;
    }

    .invigilation-empty-icon {
        width: 56px;
        height: 56px;
        border-radius: 999px;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .invigilation-empty-title {
        color: #111827;
        font-size: 15px;
        font-weight: 600;
        line-height: 1.5;
    }

    .invigilation-empty-copy {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.6;
        max-width: 460px;
    }

    .invigilation-empty-cell {
        padding: 0 !important;
    }

    .invigilation-timetable-shell {
        overflow: hidden;
    }

    .invigilation-timetable-header {
        padding: 24px 24px 0 24px;
        margin-bottom: 0;
    }

    .invigilation-timetable-scroll {
        padding: 24px;
        padding-top: 18px;
    }

    .invigilation-timetable {
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 0;
        min-width: max-content;
    }

    .invigilation-timetable > :not(caption) > * > * {
        border-color: #e5e7eb;
        vertical-align: top;
    }

    .invigilation-timetable thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        background: #f8fafc;
        color: #111827;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 14px 16px;
    }

    .invigilation-timetable thead th small {
        display: block;
        margin-top: 4px;
        color: #6b7280;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0;
        text-transform: none;
    }

    .invigilation-timetable-time-col {
        position: sticky;
        left: 0;
        z-index: 4 !important;
        background: #f8fafc !important;
        min-width: 148px;
        width: 148px;
    }

    .invigilation-timetable tbody th.invigilation-timetable-time-col {
        background: #f9fafb !important;
        color: #111827;
        font-size: 13px;
        font-weight: 700;
        padding: 18px 16px;
    }

    .invigilation-timetable-row-col {
        position: sticky;
        left: 0;
        z-index: 4 !important;
        background: #f8fafc !important;
        min-width: 220px;
        width: 220px;
    }

    .invigilation-timetable tbody th.invigilation-timetable-row-col {
        background: #f9fafb !important;
        color: #111827;
        padding: 18px 16px;
        text-align: left;
    }

    .invigilation-timetable-row-label {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.45;
    }

    .invigilation-timetable-row-meta {
        margin-top: 4px;
        color: #6b7280;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.45;
    }

    .invigilation-timetable-date-col {
        min-width: 240px;
        width: 240px;
    }

    .invigilation-timetable-cell {
        min-width: 240px;
        width: 240px;
        padding: 14px;
        background: #fff;
    }

    .invigilation-timetable-time {
        display: flex;
        align-items: center;
        min-height: 100%;
        line-height: 1.5;
    }

    .invigilation-slot-stack {
        display: grid;
        gap: 10px;
    }

    .invigilation-slot-empty {
        min-height: 112px;
        border: 1px dashed #d1d5db;
        border-radius: 10px;
        background: #f9fafb;
        color: #9ca3af;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 16px;
    }

    .invigilation-slot-tile {
        border: 1px solid #e5e7eb;
        border-left-width: 4px;
        border-radius: 10px;
        background: #fff;
        padding: 12px 13px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
    }

    .invigilation-slot-tile.tile-covered {
        border-left-color: #16a34a;
        background: linear-gradient(180deg, #ffffff 0%, #f6fef9 100%);
    }

    .invigilation-slot-tile.tile-pending {
        border-left-color: #f59e0b;
        background: linear-gradient(180deg, #ffffff 0%, #fffaf0 100%);
    }

    .invigilation-slot-title-row {
        display: flex;
        gap: 10px;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .invigilation-slot-subject {
        color: #111827;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.45;
    }

    .invigilation-slot-coverage {
        border-radius: 999px;
        padding: 4px 8px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .invigilation-slot-coverage.covered {
        background: #dcfce7;
        color: #166534;
    }

    .invigilation-slot-coverage.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .invigilation-slot-meta {
        color: #6b7280;
        font-size: 12px;
        line-height: 1.5;
    }

    .invigilation-slot-staff {
        margin-top: 9px;
        color: #1f2937;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.55;
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

    @media (max-width: 1199px) {
        .invigilation-grid,
        .room-source-grid {
            grid-template-columns: 1fr;
        }

        .workspace-toolbar {
            align-items: flex-start;
        }

        .invigilation-timetable-date-col,
        .invigilation-timetable-cell {
            min-width: 220px;
            width: 220px;
        }
    }

    @media (max-width: 991px) {
        .session-readonly-grid,
        .assignment-edit-grid,
        .assignment-row.assignment-readonly,
        .inline-field-grid {
            grid-template-columns: 1fr;
        }

        .assignment-stack-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .assignment-row-footer,
        .assignment-row-controls,
        .assignment-lock-toggle,
        .assignment-actions {
            justify-content: flex-start !important;
        }

        .invigilation-daily-actions {
            justify-content: flex-start;
        }

        .invigilation-daily-action-cluster {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width: 767px) {
        .invigilation-daily-action-cluster {
            display: grid;
            gap: 10px;
        }

        .invigilation-layout-toggle {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .invigilation-layout-button {
            justify-content: center;
        }

        .invigilation-header-button {
            width: 100%;
            justify-content: center;
        }
    }

    @media print {
        .invigilation-filter-row,
        .invigilation-daily-actions {
            display: none !important;
        }
    }
</style>
