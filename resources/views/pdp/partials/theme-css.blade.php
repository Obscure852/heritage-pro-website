<style>
    .pdp-theme .page-shell {
        background: white;
        border-radius: 3px;
        padding: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .pdp-theme .page-shell-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 28px;
    }

    .pdp-theme .page-shell-body {
        padding: 24px;
    }

    .pdp-theme .form-container {
        background: white;
        border-radius: 3px;
        padding: 32px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .pdp-theme .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e5e7eb;
    }

    .pdp-theme .page-title {
        font-size: 22px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .pdp-theme .page-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 14px;
        line-height: 1.5;
        max-width: 760px;
        margin-top: 6px;
    }

    .pdp-theme .page-shell-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .pdp-theme .header-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 16px;
    }

    .pdp-theme .header-meta-item {
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 3px;
        padding: 10px 14px;
        min-width: 150px;
    }

    .pdp-theme .header-meta-label {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.85;
        margin-bottom: 4px;
    }

    .pdp-theme .header-meta-value {
        font-weight: 600;
        font-size: 14px;
    }

    .pdp-theme .help-text {
        background: #f8f9fa;
        padding: 12px;
        border-left: 4px solid #3b82f6;
        border-radius: 0 3px 3px 0;
        margin-bottom: 24px;
    }

    .pdp-theme .help-text .help-title {
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }

    .pdp-theme .help-text .help-content {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.4;
    }

    .pdp-theme.my-pdp-page .my-pdp-header-title {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
    }

    .pdp-theme.my-pdp-page .my-pdp-header-subtitle {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, 0.9);
        font-size: 14px;
        line-height: 1.5;
    }

    .pdp-theme.my-pdp-page .stat-item {
        padding: 10px 0;
    }

    .pdp-theme.my-pdp-page .stat-item h4 {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .pdp-theme.my-pdp-page .stat-item small {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .pdp-theme.reports-page .stat-item {
        padding: 10px 0;
    }

    .pdp-theme.reports-page .stat-item h4 {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .pdp-theme.reports-page .stat-item small {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .pdp-theme.my-pdp-page .page-empty-state {
        min-height: 420px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pdp-theme.my-pdp-page .empty-state-illustrated {
        max-width: 420px;
        width: 100%;
        padding: 12px 28px;
        text-align: center;
    }

    .pdp-theme.my-pdp-page .empty-state-icon {
        margin: 0 auto 18px;
        color: #2563eb;
        font-size: 34px;
    }

    .pdp-theme.my-pdp-page .empty-state-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 8px;
    }

    .pdp-theme.my-pdp-page .empty-state-copy {
        max-width: 300px;
        margin: 0 auto;
        line-height: 1.55;
    }

    .pdp-theme .comment-bank-picker {
        margin-bottom: 10px;
        padding: 12px 14px;
        background: linear-gradient(180deg, #f8fbff 0%, #f3f6fb 100%);
        border: 1px solid #d8e4f3;
        border-radius: 6px;
    }

    .pdp-theme .comment-bank-picker-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }

    .pdp-theme .comment-bank-picker-label {
        font-size: 12px;
        font-weight: 600;
        color: #244163;
        letter-spacing: 0.2px;
    }

    .pdp-theme .comment-bank-picker-meta {
        color: #5b728c;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .pdp-theme .comment-bank-picker-controls {
        display: block;
    }

    .pdp-theme .comment-bank-picker-controls .form-select {
        width: 100%;
        min-height: 40px;
        background-color: #fff;
        border-color: #cdd9ea;
    }

    .pdp-theme .comment-bank-picker-controls .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .pdp-theme .comment-bank-picker-hint {
        margin-top: 8px;
        color: #5f6f82;
        font-size: 12px;
        line-height: 1.45;
    }

    .pdp-theme.plan-page .page-shell-body {
        padding-top: 20px;
    }

    .pdp-theme.plan-page .plan-header-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px 18px;
        text-align: center;
    }

    .pdp-theme.plan-page .plan-header-stat {
        min-width: 0;
    }

    .pdp-theme.plan-page .plan-header-stat-value {
        font-size: 1.1rem;
        font-weight: 700;
        line-height: 1.3;
        color: white;
        overflow-wrap: anywhere;
    }

    .pdp-theme.plan-page .plan-header-stat-label {
        margin-top: 4px;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        opacity: 0.75;
    }

    .pdp-theme.plan-page .plan-overview-panel {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }

    .pdp-theme.plan-page .plan-overview-intro,
    .pdp-theme.plan-page .plan-action-row,
    .pdp-theme.plan-page .plan-facts-grid,
    .pdp-theme.plan-page .plan-review-header,
    .pdp-theme.plan-page .plan-review-list {
        padding-left: 24px;
        padding-right: 24px;
    }

    .pdp-theme.plan-page .plan-overview-intro {
        padding-top: 20px;
        padding-bottom: 10px;
    }

    .pdp-theme.plan-page .plan-overview-intro .help-title {
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .pdp-theme.plan-page .plan-overview-intro .help-content {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
        max-width: 840px;
    }

    .pdp-theme.plan-page .plan-action-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding-top: 0;
        padding-bottom: 18px;
        border-bottom: 1px solid #eef2f7;
    }

    .pdp-theme.plan-page .plan-action-row .btn {
        padding: 8px 14px;
        font-size: 13px;
    }

    .pdp-theme.plan-page .plan-facts-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0;
        padding-top: 18px;
        padding-bottom: 18px;
        border-bottom: 1px solid #eef2f7;
    }

    .pdp-theme.plan-page .plan-fact {
        padding: 0 16px;
        min-width: 0;
    }

    .pdp-theme.plan-page .plan-fact:first-child {
        padding-left: 0;
    }

    .pdp-theme.plan-page .plan-fact:last-child {
        padding-right: 0;
    }

    .pdp-theme.plan-page .plan-fact + .plan-fact {
        border-left: 1px solid #eef2f7;
    }

    .pdp-theme.plan-page .plan-review-header {
        padding-top: 18px;
        padding-bottom: 8px;
    }

    .pdp-theme.plan-page .plan-review-list {
        padding-top: 0;
        padding-bottom: 8px;
    }

    .pdp-theme.plan-page .plan-review-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 16px 0;
        border-top: 1px solid #eef2f7;
    }

    .pdp-theme.plan-page .plan-review-item:first-child {
        border-top: none;
    }

    .pdp-theme.plan-page .plan-review-copy {
        min-width: 0;
        flex: 1 1 auto;
    }

    .pdp-theme.plan-page .plan-review-title-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        margin-bottom: 4px;
    }

    .pdp-theme.plan-page .plan-review-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        color: #6b7280;
        font-size: 13px;
    }

    .pdp-theme.plan-page .plan-review-action {
        flex: 0 0 auto;
    }

    .pdp-theme.plan-page .plan-review-action form {
        margin: 0;
    }

    .pdp-theme.plan-page .plan-review-action .btn {
        padding: 8px 14px;
        font-size: 13px;
    }

    .pdp-theme .section-title {
        font-size: 16px;
        font-weight: 600;
        margin: 24px 0 16px 0;
        color: #1f2937;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
    }

    .pdp-theme .form-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .pdp-theme .form-group {
        margin-bottom: 0;
    }

    .pdp-theme .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
        font-size: 14px;
    }

    .pdp-theme .form-control,
    .pdp-theme .form-select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        font-size: 14px;
        transition: all 0.2s;
        min-height: 42px;
        background-color: #fff;
    }

    .pdp-theme textarea.form-control {
        min-height: auto;
    }

    .pdp-theme .form-control:focus,
    .pdp-theme .form-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .pdp-theme .form-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 24px;
        border-top: 1px solid #f3f4f6;
        margin-top: 32px;
    }

    .pdp-theme .btn {
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
        justify-content: center;
        gap: 8px;
    }

    .pdp-theme .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .pdp-theme .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }

    .pdp-theme .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .pdp-theme .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-1px);
        color: white;
    }

    .pdp-theme .btn-light {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }

    .pdp-theme .btn-light:hover {
        background: #e5e7eb;
        color: #1f2937;
        transform: translateY(-1px);
    }

    .pdp-theme .btn-outline-primary,
    .pdp-theme .btn-outline-success,
    .pdp-theme .btn-outline-danger,
    .pdp-theme .btn-outline-dark {
        background: white;
        border: 1px solid currentColor;
    }

    .pdp-theme .btn-outline-primary {
        color: #2563eb;
    }

    .pdp-theme .btn-outline-primary:hover {
        background: #eff6ff;
        color: #1d4ed8;
        transform: translateY(-1px);
    }

    .pdp-theme .btn-outline-success {
        color: #15803d;
    }

    .pdp-theme .btn-outline-success:hover {
        background: #f0fdf4;
        color: #166534;
        transform: translateY(-1px);
    }

    .pdp-theme .btn-outline-danger {
        color: #dc2626;
    }

    .pdp-theme .btn-outline-danger:hover {
        background: #fef2f2;
        color: #b91c1c;
        transform: translateY(-1px);
    }

    .pdp-theme .btn-outline-dark {
        color: #1f2937;
    }

    .pdp-theme .btn-outline-dark:hover {
        background: #f3f4f6;
        color: #111827;
        transform: translateY(-1px);
    }

    .pdp-theme .btn-loading.loading .btn-text {
        display: none;
    }

    .pdp-theme .btn-loading.loading .btn-spinner {
        display: inline-flex !important;
        align-items: center;
    }

    .pdp-theme .btn-loading:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .pdp-theme .table thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
    }

    .pdp-theme .table tbody tr:hover {
        background-color: #f9fafb;
    }

    .pdp-theme .metric-card {
        background: white;
        border-radius: 3px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        height: 100%;
    }

    .pdp-theme .metric-card-label {
        display: block;
        color: #6b7280;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .pdp-theme .metric-card-value {
        font-size: 1.9rem;
        font-weight: 700;
        color: #1f2937;
    }

    .pdp-theme .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .pdp-theme .summary-card,
    .pdp-theme .section-panel,
    .pdp-theme .entry-card,
    .pdp-theme .timeline-card {
        background: white;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .pdp-theme .section-panel-header,
    .pdp-theme .summary-card-header {
        padding: 20px 24px 0;
    }

    .pdp-theme .section-panel-body,
    .pdp-theme .summary-card-body {
        padding: 20px 24px 24px;
    }

    .pdp-theme .section-panel-title {
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .pdp-theme .section-panel-subtitle {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
    }

    .pdp-theme .entry-card {
        padding: 20px;
        margin-bottom: 16px;
    }

    .pdp-theme .entry-card:last-child {
        margin-bottom: 0;
    }

    .pdp-theme .display-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 16px;
        height: 100%;
    }

    .pdp-theme .display-label {
        color: #6b7280;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .pdp-theme .display-value {
        color: #1f2937;
        font-weight: 600;
        line-height: 1.5;
    }

    .pdp-theme .action-buttons {
        display: flex;
        gap: 4px;
        justify-content: flex-end;
    }

    .pdp-theme .action-buttons .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
        transition: all 0.2s ease;
    }

    .pdp-theme .action-buttons .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .pdp-theme .action-buttons .btn i {
        font-size: 16px;
    }

    .pdp-theme .badge-soft {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .pdp-theme .badge-soft-primary {
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
    }

    .pdp-theme .badge-soft-success {
        background: rgba(22, 163, 74, 0.12);
        color: #15803d;
    }

    .pdp-theme .badge-soft-warning {
        background: rgba(245, 158, 11, 0.14);
        color: #b45309;
    }

    .pdp-theme .badge-soft-dark {
        background: rgba(31, 41, 55, 0.08);
        color: #1f2937;
    }

    .pdp-theme .empty-state {
        background: #f9fafb;
        border: 1px dashed #d1d5db;
        border-radius: 3px;
        padding: 20px;
        text-align: center;
        color: #6b7280;
    }

    .pdp-theme pre {
        margin: 0;
        white-space: pre-wrap;
    }

    @media (max-width: 992px) {
        .pdp-theme .form-grid,
        .pdp-theme .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .pdp-theme.plan-page .plan-facts-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .pdp-theme.plan-page .plan-fact {
            padding: 0;
        }

        .pdp-theme.plan-page .plan-fact + .plan-fact {
            border-left: none;
            border-top: 1px solid #eef2f7;
            padding-top: 14px;
        }
    }

    @media (max-width: 768px) {
        .pdp-theme .form-container,
        .pdp-theme .page-shell-body,
        .pdp-theme .page-shell-header,
        .pdp-theme .section-panel-body,
        .pdp-theme .section-panel-header,
        .pdp-theme .summary-card-body,
        .pdp-theme .summary-card-header {
            padding: 20px;
        }

        .pdp-theme .form-actions {
            flex-direction: column;
        }

        .pdp-theme .form-actions .btn {
            width: 100%;
        }

        .pdp-theme .comment-bank-picker-header {
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .pdp-theme.plan-page .plan-overview-intro,
        .pdp-theme.plan-page .plan-action-row,
        .pdp-theme.plan-page .plan-facts-grid,
        .pdp-theme.plan-page .plan-review-header,
        .pdp-theme.plan-page .plan-review-list {
            padding-left: 20px;
            padding-right: 20px;
        }

        .pdp-theme.plan-page .plan-header-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            text-align: left;
        }

        .pdp-theme.plan-page .plan-review-item {
            flex-direction: column;
            align-items: stretch;
        }

        .pdp-theme.plan-page .plan-review-action .btn {
            width: 100%;
        }

        .pdp-theme.my-pdp-page .stat-item h4 {
            font-size: 1.25rem;
        }

        .pdp-theme.my-pdp-page .page-empty-state {
            min-height: 320px;
        }

        .pdp-theme.reports-page .stat-item h4 {
            font-size: 1.25rem;
        }
    }

    @media (max-width: 576px) {
        .pdp-theme .form-grid,
        .pdp-theme .summary-grid {
            grid-template-columns: 1fr;
        }

        .pdp-theme.plan-page .plan-header-stats {
            grid-template-columns: 1fr;
        }

        .pdp-theme.my-pdp-page .stat-item small {
            font-size: 0.75rem;
        }

        .pdp-theme.reports-page .stat-item small {
            font-size: 0.75rem;
        }
    }
</style>
