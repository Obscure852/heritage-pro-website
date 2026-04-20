<style>
    .doc-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 12px 24px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        margin-bottom: 20px;
    }

    .doc-toolbar-actions .btn {
        min-width: 110px;
        height: 31px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .doc-container {
        background: white;
        border-radius: 3px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
        padding: 0;
    }

    .doc-header {
        background: #ffffff;
        color: #1f2937;
        padding: 28px 32px;
        border-radius: 3px 3px 0 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .doc-letterhead {
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: center;
        padding-bottom: 18px;
        margin-bottom: 18px;
        border-bottom: 2px solid #dbe4f0;
    }

    .doc-letterhead-crest {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .doc-letterhead-crest img {
        width: 94px;
        height: auto;
        border-radius: 4px;
        padding: 4px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.08);
    }

    .doc-letterhead-body {
        text-align: center;
        width: 100%;
    }

    .doc-letterhead-school {
        font-size: 0.96rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #0f172a;
        margin-bottom: 3px;
    }

    .doc-letterhead-contact {
        font-size: 0.82rem;
        color: #475569;
        line-height: 1.35;
    }

    .doc-letterhead-subtitle {
        margin-top: 8px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #1d4ed8;
    }

    .doc-header-title {
        margin: 0 0 8px 0;
        font-weight: 800;
        font-size: 1.34rem;
        text-align: center;
        letter-spacing: -0.03em;
        color: #0f172a;
    }

    .doc-header-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        font-size: 12px;
        color: #4b5563;
        justify-content: center;
        text-align: center;
    }

    .doc-header-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        justify-content: center;
    }

    .doc-body {
        padding: 32px;
    }

    .doc-summary {
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
        padding: 16px 20px;
        background: #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        margin-bottom: 28px;
        font-size: 14px;
    }

    .doc-summary-item {
        display: flex;
        flex-direction: column;
        min-width: 120px;
    }

    .doc-summary-item .label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        margin-bottom: 2px;
    }

    .doc-summary-item .value {
        font-weight: 600;
        color: #1f2937;
    }

    .week-block {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        margin-bottom: 24px;
        page-break-inside: avoid;
    }

    .week-header {
        background: #f3f4f6;
        padding: 14px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .week-header h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
    }

    .week-body {
        padding: 20px;
    }

    .field-row {
        margin-bottom: 14px;
    }

    .lined-sheet {
        --rule-height: 1.8rem;
        line-height: var(--rule-height);
        min-height: calc(var(--rule-height) + 0.45rem);
        padding: 0.35rem 0.95rem 0.45rem 1.5rem;
        border: 1px solid #dbe4f0;
        border-radius: 2px;
        background-image:
            linear-gradient(to right, transparent 0, transparent 1.2rem, rgba(239, 68, 68, 0.25) 1.2rem, rgba(239, 68, 68, 0.25) calc(1.2rem + 1px), transparent calc(1.2rem + 1px)),
            repeating-linear-gradient(
                to bottom,
                rgba(255, 255, 255, 0.95) 0,
                rgba(255, 255, 255, 0.95) calc(var(--rule-height) - 1px),
                rgba(148, 163, 184, 0.42) calc(var(--rule-height) - 1px),
                rgba(148, 163, 184, 0.42) var(--rule-height)
            );
        background-color: #fefefe;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75);
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }

    .lined-sheet--compact {
        --rule-height: 1.65rem;
        min-height: calc(var(--rule-height) + 0.25rem);
    }

    .lined-sheet > :first-child {
        margin-top: 0;
    }

    .lined-sheet > :last-child {
        margin-bottom: 0;
    }

    .lined-sheet p,
    .lined-sheet ul,
    .lined-sheet ol {
        margin-bottom: 0.4rem;
    }

    .lined-sheet.empty {
        color: #94a3b8;
        font-style: italic;
    }

    .objective-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .objective-pill {
        display: inline-flex;
        align-items: flex-start;
        gap: 6px;
        max-width: 100%;
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #f8fafc;
        color: #334155;
        font-size: 12px;
        line-height: 1.4;
    }

    .lesson-plan-card {
        border: 1px solid #dbeafe;
        border-left: 4px solid #3b82f6;
        border-radius: 0 3px 3px 0;
        padding: 16px 20px;
        margin-top: 16px;
        background: #fafbff;
        page-break-inside: avoid;
    }

    .lesson-plan-card h5 {
        margin: 0 0 12px 0;
        font-size: 14px;
        font-weight: 700;
        color: #1e40af;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .lesson-plan-card .lp-meta {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 12px;
    }

    .lp-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .lp-grid .full-width {
        grid-column: 1 / -1;
    }

    .reflection-box {
        margin-top: 14px;
    }

    .reflection-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #475569;
        margin-bottom: 8px;
    }

    .reflection-content {
        color: #1f2937;
    }

    .week-block.block-completed { border-left: 3px solid #22c55e; }
    .week-block.block-in_progress { border-left: 3px solid #3b82f6; }
    .week-block.block-taught { border-left: 3px solid #0ea5e9; }
    .week-block.block-skipped { border-left: 3px solid #f59e0b; }
    .week-block.block-planned { border-left: 3px solid #d1d5db; }

    @media (max-width: 768px) {
        .doc-header,
        .doc-body {
            padding: 22px;
        }

        .lp-grid {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        @page {
            margin: 12mm;
        }

        .doc-toolbar,
        .topbar,
        .topnav,
        .sidebar,
        .navbar,
        .left-side-menu,
        .breadcrumb,
        #layout-wrapper > .vertical-menu,
        .footer,
        .right-bar,
        .right-bar-toggle,
        .page-title-box,
        #sidebar-menu,
        .vertical-menu {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .page-content {
            padding: 0 !important;
        }

        .container-fluid {
            padding: 0 !important;
            max-width: 100% !important;
        }

        .doc-container {
            box-shadow: none;
            border: none;
        }

        .week-block,
        .lesson-plan-card {
            page-break-inside: avoid;
        }

        body {
            font-size: 12px;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
    }
</style>
