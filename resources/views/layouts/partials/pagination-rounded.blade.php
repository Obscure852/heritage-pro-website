<style>
    .dataTables_wrapper .dataTables_paginate {
        float: right !important;
        padding-top: 0.5rem;
    }

    .pagination-rounded {
        justify-content: flex-end !important;
        margin-bottom: 0;
        flex-wrap: wrap;
        gap: 0;
    }

    .pagination-rounded .page-link,
    .pagination-rounded .page-item .page-link {
        border-radius: 30px !important;
        margin: 0 3px !important;
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
        text-align: center;
    }

    .pagination-rounded .page-link:hover {
        background: #e5e7eb;
        color: #1d4ed8;
    }

    .pagination-rounded .page-link:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    .pagination-rounded .page-item.active .page-link {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: #fff;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.25);
    }

    .pagination-rounded .page-item.disabled .page-link {
        background: #f9fafb;
        color: #cbd5e1;
    }
</style>
