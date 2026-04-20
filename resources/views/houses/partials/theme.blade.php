@include('academic.partials.module-theme')

<style>
    .house-page-accent {
        border-top: 4px solid var(--house-color, #2563eb);
    }

    .house-title-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .house-color-dot,
    .house-color-swatch {
        display: inline-block;
        flex-shrink: 0;
        border-radius: 999px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.25);
    }

    .house-color-dot {
        width: 14px;
        height: 14px;
    }

    .house-color-swatch {
        width: 44px;
        height: 44px;
        border-radius: 12px;
    }

    .house-card-swatch {
        width: 36px;
        height: 36px;
        border-radius: 10px;
    }

    .house-chip,
    .summary-chip.house-chip {
        background: var(--house-color-soft, rgba(37, 99, 235, 0.14));
        color: var(--house-color, #2563eb);
        border: 1px solid rgba(37, 99, 235, 0.12);
    }

    .house-classes {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 0;
        max-width: 320px;
    }

    .house-classes .summary-chip {
        background: var(--house-color-soft, rgba(37, 99, 235, 0.12));
        color: var(--house-color, #2563eb);
    }

    .house-chip-more {
        background: #f1f5f9;
        color: #475569;
        border: 1px dashed #cbd5e1;
        cursor: help;
    }

    #houses td:nth-child(5) {
        max-width: 320px;
    }

    .house-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .house-summary-card {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 16px 18px;
        background: #fff;
    }

    .house-summary-label {
        color: #6b7280;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
    }

    .house-summary-value {
        color: #111827;
        font-size: 1.45rem;
        font-weight: 700;
    }

    .house-summary-meta {
        color: #6b7280;
        font-size: 13px;
        margin-top: 6px;
    }

    .house-table-name {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .house-table-name-copy {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .house-table-name-copy .activity-meta-pills {
        margin-top: 0;
        gap: 6px;
    }

    .house-table-meta {
        color: #6b7280;
        font-size: 12px;
        margin-top: 6px;
    }

    .house-count-stack {
        display: grid;
        gap: 6px;
    }

    .house-count-item {
        color: #374151;
        font-size: 13px;
    }

    .house-count-item strong {
        color: #111827;
    }

    .house-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
        gap: 12px;
        align-items: center;
        margin-bottom: 18px;
    }

    .house-filter-grid > * {
        min-width: 0;
    }

    .house-filter-grid .input-group {
        flex-wrap: nowrap;
    }

    .house-filter-grid .input-group .form-control {
        min-width: 0;
    }

    .house-filters-inline {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 18px;
    }

    .house-section-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 16px;
    }

    .house-section-title {
        margin: 0;
        color: #111827;
        font-size: 16px;
        font-weight: 600;
    }

    .house-section-subtitle {
        color: #6b7280;
        font-size: 13px;
        margin: 6px 0 0 0;
    }

    .house-toolbar {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .house-table-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .house-table-actions form {
        margin: 0;
    }

    .house-inline-form {
        margin: 0;
    }

    .color-input-shell {
        display: grid;
        grid-template-columns: 72px minmax(0, 1fr);
        gap: 12px;
        align-items: start;
    }

    .color-input-shell input[type="color"] {
        width: 72px;
        height: 44px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        padding: 4px;
    }

    .house-report-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
        gap: 24px;
    }

    .house-chart-shell {
        min-height: 420px;
    }

    .house-chart {
        min-height: 360px;
    }

    .house-report-block + .house-report-block {
        margin-top: 18px;
    }

    .house-member-highlight {
        border-left: 4px solid var(--house-color, #2563eb);
    }

    .house-role-tag {
        background: #f3f4f6;
        color: #4b5563;
    }

    .pagination-info {
        color: #6b7280;
        font-size: 13px;
    }

    .pagination .page-link {
        border-radius: 3px;
        margin: 0 2px;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border-color: #3b82f6;
    }

    @media (max-width: 991px) {
        .house-summary-grid,
        .house-report-grid,
        .house-filter-grid {
            grid-template-columns: 1fr;
        }

        .house-section-header,
        .house-toolbar {
            align-items: stretch;
        }

        .house-table-actions {
            justify-content: flex-start;
        }
    }

</style>
