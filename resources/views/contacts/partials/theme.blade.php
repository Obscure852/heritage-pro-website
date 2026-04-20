<style>
    .contacts-container {
        background: white;
        border-radius: 3px;
        padding: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .contacts-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 28px;
        border-radius: 3px 3px 0 0;
    }

    .contacts-body {
        padding: 24px;
    }

    .contacts-pagination nav {
        display: flex;
        justify-content: flex-end;
    }

    .contacts-pagination .pagination {
        justify-content: flex-end;
        margin-bottom: 0;
        flex-wrap: wrap;
    }

    .contacts-pagination .pagination .page-link {
        border-radius: 30px !important;
        margin: 0 3px;
        border: none;
        min-width: 32px;
        height: 32px;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        color: #3b82f6;
        background: #f3f4f6;
        font-size: 13px;
    }

    .contacts-pagination .pagination .page-link:hover {
        background: #e5e7eb;
        color: #1d4ed8;
    }

    .contacts-pagination .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: #fff;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.25);
    }

    .contacts-pagination .pagination .page-item.disabled .page-link {
        background: #f9fafb;
        color: #cbd5e1;
    }

    .stat-item {
        padding: 10px 0;
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

    .controls .form-control,
    .controls .form-select {
        font-size: 0.9rem;
    }

    .controls .btn {
        padding: 7px 16px;
        font-size: 0.9rem;
    }

    .form-container {
        background: white;
        border-radius: 3px;
        padding: 32px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e5e7eb;
    }

    .page-title {
        font-size: 22px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .page-subtitle {
        margin: 6px 0 0 0;
        color: rgba(17, 24, 39, 0.7);
        font-size: 14px;
    }

    .contacts-header .page-title,
    .contacts-header .page-subtitle {
        color: white;
    }

    .contacts-header .page-subtitle {
        opacity: 0.9;
    }

    .help-text {
        background: #f8f9fa;
        padding: 12px;
        border-left: 4px solid #3b82f6;
        border-radius: 0 3px 3px 0;
        margin-bottom: 24px;
    }

    .info-note {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-left: 4px solid #2563eb;
        border-radius: 0 3px 3px 0;
        padding: 12px;
        margin-bottom: 24px;
    }

    .info-note .help-title {
        font-weight: 600;
        color: #1e3a8a;
        margin-bottom: 4px;
    }

    .info-note .help-content {
        color: #475569;
        font-size: 13px;
        line-height: 1.4;
    }

    .help-text .help-title {
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }

    .help-text .help-content {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.4;
    }

    .summary-stat {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 16px;
        height: 100%;
    }

    .summary-stat-label {
        color: #6b7280;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .summary-stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.2;
        margin-top: 8px;
    }

    .summary-stat-text {
        font-size: 13px;
        color: #6b7280;
        margin-top: 6px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 600;
        margin: 24px 0 16px 0;
        color: #1f2937;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
        font-size: 14px;
    }

    .form-control,
    .form-select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        font-size: 14px;
        transition: all 0.2s;
    }

    .form-control:focus,
    .form-select:focus,
    .form-check-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-control-color {
        min-height: 42px;
        padding: 6px;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 24px;
        border-top: 1px solid #f3f4f6;
        margin-top: 32px;
    }

    .form-actions.form-actions-between {
        justify-content: space-between;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }

    .btn-light:hover,
    .btn-secondary:hover,
    .btn-outline-primary:hover,
    .btn-outline-danger:hover {
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        color: white;
    }

    .btn-light {
        border: 1px solid #d1d5db;
    }

    .btn-outline-primary,
    .btn-outline-danger {
        border-width: 1px;
        border-style: solid;
        background: transparent;
    }

    .btn-sm {
        padding: 0.4rem 0.75rem;
        font-size: 0.875rem;
        gap: 6px;
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

    .status-badge,
    .tag-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
    }

    .status-badge.status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.status-inactive {
        background: #e5e7eb;
        color: #4b5563;
    }

    .tag-badge {
        color: white;
    }

    .detail-card,
    .settings-card,
    .contact-card {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        box-shadow: none;
    }

    .detail-card .card-body,
    .settings-card .card-body,
    .contact-card .card-body {
        padding: 24px;
    }

    .person-row {
        background: #f8fafc;
        border: 1px solid #e5e7eb !important;
    }

    .person-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
    }

    .person-toolbar .btn {
        white-space: nowrap;
    }

    .person-row-grid {
        display: grid;
        grid-template-columns: 1.7fr 1.5fr 1.9fr 1.2fr auto;
        gap: 16px;
        align-items: start;
    }

    .person-field {
        min-width: 0;
    }

    .person-actions {
        min-width: 140px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .person-primary-group {
        width: 100%;
    }

    .person-primary-check {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 42px;
        margin: 0;
        padding: 0;
    }

    .person-primary-check .form-check-input {
        float: none;
        margin: 0;
    }

    .person-primary-check .form-check-label {
        margin: 0;
    }

    .remove-person-row {
        white-space: nowrap;
    }

    .tag-option {
        background: #f8fafc;
        border: 1px solid #e5e7eb !important;
        border-radius: 3px;
        display: block;
    }

    .tag-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .empty-state {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 3px;
        padding: 24px;
    }

    .key-value-list dt {
        color: #6b7280;
        font-weight: 600;
    }

    .key-value-list dd {
        color: #111827;
    }

    @media (max-width: 768px) {
        .contacts-header,
        .contacts-body,
        .form-container,
        .detail-card .card-body,
        .settings-card .card-body,
        .contact-card .card-body {
            padding: 20px;
        }

        .page-header,
        .form-actions,
        .form-actions.form-actions-between {
            flex-direction: column;
            align-items: stretch;
        }

        .person-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .person-toolbar .btn {
            width: 100%;
            white-space: normal;
        }

        .person-row-grid {
            grid-template-columns: 1fr;
        }

        .person-actions {
            min-width: 0;
            flex-direction: column;
            align-items: flex-start;
        }

        .tag-grid {
            grid-template-columns: 1fr;
        }

        .page-header .d-flex,
        .form-actions .btn,
        .form-actions.form-actions-between .btn {
            width: 100%;
        }
    }

    @media (max-width: 1200px) and (min-width: 769px) {
        .person-row-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .person-actions {
            grid-column: 1 / -1;
            min-width: 0;
            flex-direction: row;
            justify-content: space-between;
            align-items: flex-end;
        }
    }
</style>
