@include('activities.partials.theme')

<style>
    .academic-module-container,
    .houses-container,
    .houses-report-container,
    .invigilation-container,
    .invigilation-report-container {
        background: #fff;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .module-filter-select,
    .term-select {
        max-width: 200px;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 8px 12px;
        font-size: 14px;
        color: #374151;
        background-color: #fff;
        cursor: pointer;
    }

    .module-filter-select:focus,
    .term-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    @keyframes academicModuleSkeletonShimmer {
        0% {
            background-position: -320px 0;
        }
        100% {
            background-position: 320px 0;
        }
    }

    .skeleton {
        display: inline-block;
        background: #eef0f3 linear-gradient(90deg, rgba(238, 240, 243, 0) 0%, rgba(255, 255, 255, 0.65) 50%, rgba(238, 240, 243, 0) 100%);
        background-size: 320px 100%;
        background-repeat: no-repeat;
        border-radius: 6px;
        animation: academicModuleSkeletonShimmer 1.8s ease-in-out infinite;
    }

    .skeleton-text {
        display: block;
        height: 12px;
        border-radius: 4px;
        max-width: 100%;
    }

    .skeleton-text.skeleton-sm {
        height: 10px;
    }

    .skeleton-swatch {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        flex-shrink: 0;
    }

    .skeleton-chip {
        height: 20px;
        border-radius: 999px;
    }

    .skeleton-btn {
        width: 32px;
        height: 32px;
        border-radius: 6px;
    }

    .skeleton-row td {
        vertical-align: middle;
    }

    .academic-module-header,
    .houses-header,
    .invigilation-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: #fff;
        padding: 28px;
        border-radius: 3px 3px 0 0;
    }

    .houses-header .stat-item,
    .invigilation-header .stat-item,
    .academic-module-header .stat-item {
        background: transparent;
        border-radius: 0;
        padding: 10px 0;
    }

    .academic-module-body,
    .houses-body,
    .houses-report-body,
    .invigilation-body,
    .invigilation-report-body {
        padding: 24px;
    }

    .academic-module-body .btn-primary,
    .houses-body .btn-primary,
    .houses-report-body .btn-primary,
    .invigilation-body .btn-primary,
    .invigilation-report-body .btn-primary,
    .form-container .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: #fff;
        font-weight: 500;
    }

    .academic-module-body .btn-primary:hover,
    .houses-body .btn-primary:hover,
    .houses-report-body .btn-primary:hover,
    .invigilation-body .btn-primary:hover,
    .invigilation-report-body .btn-primary:hover,
    .form-container .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .academic-module-body .btn-light,
    .houses-body .btn-light,
    .houses-report-body .btn-light,
    .invigilation-body .btn-light,
    .invigilation-report-body .btn-light,
    .form-container .btn-light {
        border: 1px solid #d1d5db;
        background: #f3f4f6;
        color: #111827;
    }

    .academic-module-body .btn-light:hover,
    .houses-body .btn-light:hover,
    .houses-report-body .btn-light:hover,
    .invigilation-body .btn-light:hover,
    .invigilation-report-body .btn-light:hover,
    .form-container .btn-light:hover {
        background: #e5e7eb;
        color: #111827;
    }

    .academic-module-body .btn-danger,
    .houses-body .btn-danger,
    .invigilation-body .btn-danger,
    .form-container .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        border: none;
        color: #fff;
    }

    .academic-module-body .btn-danger:hover,
    .houses-body .btn-danger:hover,
    .invigilation-body .btn-danger:hover,
    .form-container .btn-danger:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
    }

    .module-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .module-summary-card {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 16px 18px;
        background: #fff;
    }

    .module-summary-label {
        color: #6b7280;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
    }

    .module-summary-value {
        color: #111827;
        font-size: 1.45rem;
        font-weight: 700;
    }

    .module-summary-meta {
        color: #6b7280;
        font-size: 13px;
        margin-top: 6px;
    }

    .module-header-actions {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .section-stack {
        display: grid;
        gap: 24px;
    }

    .section-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .section-toolbar h5,
    .section-toolbar h6 {
        margin: 0;
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

    .print-toolbar {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-bottom: 16px;
    }

    .print-toolbar .btn {
        min-height: auto;
    }

    @media (max-width: 991px) {
        .module-summary-grid {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        body * {
            visibility: hidden;
        }

        .printable,
        .printable * {
            visibility: visible;
        }

        .printable {
            position: absolute;
            inset: 0;
            width: 100%;
            background: #fff;
        }

        .print-toolbar,
        .subnav-links,
        .breadcrumb-card,
        .page-title-box {
            display: none !important;
        }

        .academic-module-container,
        .houses-container,
        .houses-report-container,
        .invigilation-container,
        .invigilation-report-container,
        .form-container,
        .card-shell {
            box-shadow: none !important;
            border: 1px solid #d1d5db !important;
        }
    }
</style>
