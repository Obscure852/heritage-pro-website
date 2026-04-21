body.crm-body {
    font-family: 'Inter', sans-serif;
    background: #f8fafc;
    color: #1f2937;
}

#layout-wrapper {
    min-height: 100vh;
}

.main-content {
    min-height: 100vh;
    overflow: visible;
}

.page-content {
    padding: calc(70px + 1.25rem) 0 96px;
}

.footer {
    position: fixed;
    right: 0;
    bottom: 0;
    left: 250px;
    z-index: 1000;
}

body.crm-body[data-sidebar-size="sm"] .footer {
    left: 70px;
}

body.crm-body .vertical-menu {
    background: #fbfaff;
    border-right: 1px solid #e9e9ef;
}

body.crm-body #sidebar-menu ul li a,
body.crm-body #sidebar-menu ul li a i {
    color: #545a6d;
}

body.crm-body #sidebar-menu ul li ul.sub-menu li a,
body.crm-body .menu-title,
body.crm-body .crm-shell-footer,
body.crm-body .crm-shell-footer p {
    color: #74788d;
}

body.crm-body #sidebar-menu ul li a:hover,
body.crm-body #sidebar-menu ul li a:hover i {
    color: #5156be;
}

body.crm-body .mm-active > a,
body.crm-body .mm-active > a i,
body.crm-body .mm-active .active,
body.crm-body .mm-active .active i {
    color: #5156be !important;
}

.crm-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 20px;
}

.crm-page-title {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #1f2937;
}

.crm-page-subtitle {
    margin: 8px 0 0;
    color: #6b7280;
    font-size: 14px;
    line-height: 1.5;
    max-width: 860px;
}

.crm-page-tools {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.crm-page-tools form,
.crm-action-row form,
.crm-inline-form {
    margin: 0;
}

.crm-stack {
    display: grid;
    gap: 20px;
}

.crm-grid {
    display: grid;
    gap: 20px;
}

.crm-grid.cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.crm-grid.cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.crm-grid.cols-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.crm-split {
    display: grid;
    grid-template-columns: minmax(320px, 420px) minmax(0, 1fr);
    gap: 20px;
    align-items: start;
}

.crm-card,
.crm-metric,
.crm-tabs {
    background: #fff;
    border-radius: 3px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.crm-card {
    padding: 24px;
}

.crm-filter-card {
    padding-bottom: 18px;
}

.crm-filter-form {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: end;
    gap: 16px;
}

.crm-filter-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    align-items: end;
}

.crm-filter-form > .form-actions {
    margin-top: 0;
    padding-top: 0;
    border-top: 0;
    align-self: end;
    flex-shrink: 0;
    white-space: nowrap;
}

.crm-card-title {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.crm-card-title h2,
.crm-card-title h3,
.crm-card-title h4 {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
    color: #1f2937;
}

.crm-card-title p {
    margin: 6px 0 0;
    color: #6b7280;
    font-size: 13px;
    line-height: 1.5;
}

.crm-kicker {
    margin: 0 0 6px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #4e73df;
}

.crm-help {
    background: #f8f9fa;
    padding: 12px;
    border-left: 4px solid #3b82f6;
    border-radius: 0 3px 3px 0;
    margin-bottom: 20px;
    color: #6b7280;
    font-size: 13px;
    line-height: 1.5;
}

.crm-form {
    display: grid;
    gap: 20px;
}

.crm-field-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.crm-field {
    margin-bottom: 0;
}

.crm-field.full {
    grid-column: 1 / -1;
}

.crm-field label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.crm-field input,
.crm-field select,
.crm-field textarea,
.staff-presence-search .form-control,
.app-search .form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 3px;
    font-size: 14px;
    transition: all 0.2s;
    background: #fff;
    color: #1f2937;
}

.crm-field textarea {
    min-height: 108px;
    resize: vertical;
}

.crm-field input:focus,
.crm-field select:focus,
.crm-field textarea:focus,
.staff-presence-search .form-control:focus,
.app-search .form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.crm-check {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 42px;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 3px;
    background: #fff;
}

.crm-check input {
    width: auto;
    margin: 0;
}

.crm-actions,
.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding-top: 24px;
    border-top: 1px solid #f3f4f6;
    margin-top: 8px;
}

.btn {
    padding: 10px 20px;
    border-radius: 3px;
    font-size: 14px;
    font-weight: 500;
    border: none;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
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

.btn-secondary,
.btn-light.crm-btn-light {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover,
.btn-light.crm-btn-light:hover {
    background: #5a6268;
    transform: translateY(-1px);
    color: white;
}

.btn-danger,
.crm-btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.btn-danger:hover,
.crm-btn-danger:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.24);
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

.crm-metric {
    padding: 0;
    overflow: hidden;
}

.crm-metric span {
    display: block;
    background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
    color: white;
    padding: 14px 18px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.crm-metric strong {
    display: block;
    padding: 18px;
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
}

.crm-tabs {
    display: inline-flex;
    gap: 0;
    padding: 6px;
}

.crm-tab {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 16px;
    border-radius: 3px;
    font-size: 14px;
    font-weight: 500;
    color: #6b7280;
}

.crm-tab.is-active {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #fff;
}

.crm-table-wrap {
    overflow-x: auto;
}

.crm-table {
    width: 100%;
    border-collapse: collapse;
}

.crm-table thead th {
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
    color: #374151;
    font-size: 13px;
    padding: 12px 14px;
    text-align: left;
}

.crm-table tbody td {
    padding: 14px;
    border-top: 1px solid #f3f4f6;
    vertical-align: top;
}

.crm-table tbody tr:hover {
    background-color: #f9fafb;
}

.crm-list {
    display: grid;
    gap: 12px;
}

.crm-list-item {
    border: 1px solid #e5e7eb;
    border-radius: 3px;
    padding: 16px;
    background: #fff;
}

.crm-list-item h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
}

.crm-list-item p {
    margin: 8px 0 0;
    color: #6b7280;
    font-size: 13px;
    line-height: 1.5;
}

.crm-table-actions {
    width: 1%;
    white-space: nowrap;
    text-align: right;
}

.crm-action-row {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    flex-wrap: wrap;
}

.crm-table-actions .btn {
    padding: 8px 14px;
    font-size: 13px;
}

.crm-table-actions .crm-action-row {
    justify-content: flex-end;
}

.crm-empty {
    min-height: 240px;
    padding: 32px 24px;
    border-radius: 3px;
    border: 1px dashed #d1d5db;
    background: #f8fafc;
    color: #6b7280;
    font-size: 13px;
    line-height: 1.6;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 12px;
    position: relative;
    overflow: hidden;
}

.crm-empty::before {
    content: '';
    width: 78px;
    height: 78px;
    border-radius: 999px;
    border: 1px solid rgba(99, 102, 241, 0.16);
    background: radial-gradient(circle at 30% 30%, #ffffff 0%, #eef2ff 55%, #e0e7ff 100%);
    box-shadow: 0 12px 28px rgba(99, 102, 241, 0.16);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64' fill='none'%3E%3Crect x='14' y='20' width='30' height='22' rx='6' fill='white' stroke='%235156be' stroke-width='2.2'/%3E%3Cpath d='M20 20.5l3.8-5.2A4 4 0 0 1 27 13.7h8.3a4 4 0 0 1 3.1 1.5l3 3.8' stroke='%235156be' stroke-width='2.2' stroke-linecap='round'/%3E%3Cpath d='M20 29h18' stroke='%23a5b4fc' stroke-width='2.2' stroke-linecap='round'/%3E%3Ccircle cx='43.5' cy='42.5' r='8.5' fill='white' stroke='%232563eb' stroke-width='2.4'/%3E%3Cpath d='M49.7 48.7l4.8 4.8' stroke='%232563eb' stroke-width='2.4' stroke-linecap='round'/%3E%3Cpath d='M40.4 42.5h6.2' stroke='%232563eb' stroke-width='2.2' stroke-linecap='round'/%3E%3Cpath d='M47.2 38.8l-7.4 7.4' stroke='%23f97316' stroke-width='2.2' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: center;
    background-size: 56px 56px;
    z-index: 1;
}

.crm-empty::after {
    content: '';
    position: absolute;
    inset: auto auto 18px 50%;
    width: 110px;
    height: 18px;
    transform: translateX(-50%);
    border-radius: 999px;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.18) 0%, rgba(99, 102, 241, 0.04) 58%, rgba(99, 102, 241, 0) 100%);
    filter: blur(6px);
}

.crm-muted,
.crm-muted-copy {
    display: block;
    color: #6b7280;
    font-size: 12px;
    line-height: 1.5;
}

.crm-inline {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.crm-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 500;
}

.crm-pill.primary {
    background: #dbeafe;
    color: #1d4ed8;
}

.crm-pill.success {
    background: #d1fae5;
    color: #065f46;
}

.crm-pill.warning {
    background: #fef3c7;
    color: #92400e;
}

.crm-pill.danger {
    background: #fee2e2;
    color: #991b1b;
}

.crm-pill.muted {
    background: #f3f4f6;
    color: #4b5563;
}

.crm-meta-list {
    display: grid;
    gap: 12px;
}

.crm-meta-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #f3f4f6;
}

.crm-meta-row:last-child {
    border-bottom: none;
}

.crm-meta-row span {
    color: #6b7280;
    font-size: 13px;
}

.crm-meta-row strong {
    text-align: right;
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
}

.crm-timeline {
    display: grid;
    gap: 14px;
}

.crm-timeline-item {
    display: grid;
    grid-template-columns: 72px minmax(0, 1fr);
    gap: 14px;
}

.crm-timeline-time {
    font-size: 12px;
    line-height: 1.5;
    color: #6b7280;
    text-align: right;
    padding-top: 4px;
}

.crm-timeline-card {
    border: 1px solid #e5e7eb;
    border-radius: 3px;
    padding: 14px;
    background: #fff;
}

.crm-timeline-card h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
}

.crm-timeline-card p {
    margin: 0;
    color: #374151;
    font-size: 13px;
    line-height: 1.6;
}

.crm-stage-card {
    border: 1px solid #e5e7eb;
    border-radius: 3px;
    padding: 16px;
    background: #fff;
}

.crm-stage-card strong {
    display: block;
    margin-top: 12px;
    font-size: 26px;
    font-weight: 700;
    color: #1f2937;
}

.crm-stage-card .crm-muted-copy {
    margin-top: 8px;
}

.crm-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-top: 18px;
    font-size: 13px;
    color: #6b7280;
}

.crm-pagination-links {
    display: flex;
    gap: 10px;
    align-items: center;
}

.crm-pagination-links a,
.crm-pagination-links span {
    padding: 8px 12px;
    border-radius: 3px;
    background: #f3f4f6;
    color: #374151;
}

.crm-alerts {
    display: grid;
    gap: 10px;
    margin-bottom: 20px;
}

.crm-alert {
    padding: 14px 16px;
    border-radius: 3px;
    border-left: 4px solid transparent;
    font-size: 14px;
}

.crm-alert.success {
    background: #ecfdf5;
    border-left-color: #10b981;
    color: #065f46;
}

.crm-alert.error {
    background: #fef2f2;
    border-left-color: #ef4444;
    color: #991b1b;
}

.crm-alert ul {
    margin-top: 10px;
}

.crm-card a,
.crm-table a,
.crm-list a {
    color: #2563eb;
}

.crm-card a:hover,
.crm-table a:hover,
.crm-list a:hover {
    color: #1d4ed8;
}

.crm-user-avatar-circle,
.crm-initial-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
}

.crm-initial-avatar.crm-initial-avatar-lg {
    width: 72px;
    height: 72px;
    font-size: 22px;
}

.crm-detail-hero {
    display: flex;
    align-items: center;
    gap: 18px;
}

.crm-detail-hero-copy {
    display: grid;
    gap: 8px;
}

.crm-detail-hero-copy h2 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
}

.crm-detail-hero-copy p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
    line-height: 1.6;
    max-width: 780px;
}

.crm-detail-grid {
    align-items: start;
}

.crm-note-panel {
    padding: 18px 20px;
    border: 1px solid #e5e7eb;
    border-radius: 3px;
    background: #fff;
    color: #374151;
    font-size: 14px;
    line-height: 1.7;
    min-height: 140px;
}

.app-search .position-relative {
    width: 340px;
}

.app-search .form-control {
    height: 38px;
    padding-right: 88px;
    font-size: 13px;
    font-weight: 600;
}

.app-search .search-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #74788d;
    font-size: 14px;
    pointer-events: none;
}

.app-search .shortcut-hint {
    position: absolute;
    right: 36px;
    top: 50%;
    transform: translateY(-50%);
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 6px;
    background: #f3f3f9;
    color: #000;
    border: 1px solid #e9ebef;
    border-radius: 4px;
    font-size: 11px;
    line-height: 1;
    pointer-events: none;
    user-select: none;
}

.app-search .shortcut-hint kbd {
    font-family: inherit;
    background: #fff;
    border: 1px solid #e9ebef;
    border-bottom-color: #dfe3e8;
    border-radius: 3px;
    padding: 1px 4px;
    font-size: 11px;
    color: #000;
    font-weight: 600;
}

.app-search .search-results,
.crm-floating-panel {
    position: absolute;
    top: calc(100% + 8px);
    background: #fff;
    border-radius: 3px;
    box-shadow: 0 20px 45px rgba(15, 23, 42, 0.14);
    z-index: 1100;
    border: 1px solid #e9ebef;
}

.app-search .search-results {
    left: 0;
    right: 0;
    overflow: hidden;
}

.crm-floating-panel {
    right: 0;
}

.app-search .search-section {
    border-bottom: 1px solid #e9ebef;
}

.app-search .search-section:last-child {
    border-bottom: none;
}

.app-search .section-header {
    padding: 10px 16px;
    background-color: #f8f9fa;
    font-weight: 600;
    font-size: 13px;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 8px;
}

.app-search .result-item {
    display: block;
    padding: 10px 16px;
    transition: all 0.2s;
}

.app-search .result-item:hover {
    background-color: #f3f3f9;
}

.app-search .result-name {
    font-weight: 500;
    color: #495057;
    font-size: 13px;
    margin-bottom: 3px;
}

.app-search .result-details,
.app-search .no-results,
.app-search .loading-results {
    font-size: 12px;
    color: #74788d;
    line-height: 1.4;
}

.app-search .no-results,
.app-search .loading-results {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 16px;
}

.loading-spinner {
    width: 18px;
    height: 18px;
    border: 2px solid #e9ebef;
    border-top-color: #556ee6;
    border-radius: 50%;
    animation: crm-spin 0.8s linear infinite;
}

@keyframes crm-spin {
    to {
        transform: rotate(360deg);
    }
}

.crm-panel-trigger,
.module-launcher-toggle,
.staff-presence-trigger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    height: 38px;
    padding: 0 12px;
    border: 1px solid #e9ebef;
    border-radius: 3px;
    background: #fff;
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background-color 0.18s ease;
}

.crm-panel-trigger:hover,
.crm-panel-trigger.is-open,
.module-launcher-toggle:hover,
.module-launcher-toggle.is-open,
.staff-presence-trigger:hover,
.staff-presence-trigger.is-open {
    border-color: #cbd5e1;
    background: #f8fafc;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
}

.module-launcher,
.staff-presence-launcher {
    position: relative;
    display: inline-flex;
    align-items: center;
}

.module-launcher-menu {
    width: 420px;
    padding: 14px;
}

.module-launcher-title {
    margin-bottom: 12px;
    padding: 0 4px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #7b8190;
}

.module-launcher-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}

.module-launcher-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    gap: 12px;
    min-height: 104px;
    border-radius: 3px;
    padding: 14px 10px 12px;
    font-weight: 500;
    color: #495057;
    white-space: normal;
    text-align: center;
    border: 1px solid #edf1f7;
    background: linear-gradient(180deg, #ffffff 0%, #f8faff 100%);
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background 0.18s ease;
}

.module-launcher-item:hover {
    background: linear-gradient(180deg, #ffffff 0%, #f2f6ff 100%);
    color: #212529;
    border-color: rgba(85, 110, 230, 0.2);
    box-shadow: 0 12px 24px rgba(85, 110, 230, 0.12);
    transform: translateY(-2px);
}

.module-launcher-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 46px;
    height: 46px;
    border-radius: 3px;
    background: linear-gradient(135deg, rgba(85, 110, 230, 0.16) 0%, rgba(85, 110, 230, 0.08) 100%);
}

.module-launcher-icon i {
    font-size: 1.55rem;
    color: #556ee6;
}

.module-launcher-label {
    display: block;
    font-size: 0.84rem;
    font-weight: 600;
    line-height: 1.25;
}

.module-launcher-caption {
    display: block;
    font-size: 11px;
    color: #6b7280;
    line-height: 1.4;
}

.staff-presence-launcher {
    width: 220px;
}

.staff-presence-trigger {
    justify-content: space-between;
    gap: 10px;
    width: 100%;
}

.staff-presence-trigger-copy {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
    flex: 1;
}

.staff-presence-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.14);
    flex-shrink: 0;
}

.staff-presence-trigger-label {
    color: #334155;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.staff-presence-trigger-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    padding: 0 8px;
    border-radius: 999px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 12px;
    font-weight: 700;
}

.staff-presence-panel {
    width: 360px;
    padding: 0;
    overflow: hidden;
}

.staff-presence-panel-header {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 18px 12px;
    border-bottom: 1px solid #f1f5f9;
}

.staff-presence-panel-header strong {
    display: block;
    color: #0f172a;
    font-size: 14px;
}

.staff-presence-panel-header span {
    color: #64748b;
    font-size: 12px;
}

.staff-presence-search {
    padding: 14px 18px 0;
}

.staff-presence-panel-note {
    margin: 12px 18px 0;
    padding: 10px 12px;
    border-radius: 8px;
    background: #f8fafc;
    color: #64748b;
    font-size: 12px;
}

.staff-presence-list {
    max-height: 360px;
    overflow-y: auto;
    padding: 14px 18px;
}

.staff-presence-empty,
.staff-presence-loading {
    padding: 20px 12px;
    text-align: center;
    color: #64748b;
    font-size: 13px;
}

.staff-presence-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f8fafc;
}

.staff-presence-item:last-child {
    border-bottom: none;
}

.staff-presence-meta {
    min-width: 0;
    flex: 1;
}

.staff-presence-name {
    color: #0f172a;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 2px;
}

.staff-presence-role,
.staff-presence-last-seen {
    color: #64748b;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.staff-presence-item .btn {
    padding: 6px 12px;
    font-size: 12px;
    white-space: nowrap;
}

.crm-sidebar-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    margin-left: 8px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
    color: #dbeafe;
    font-size: 11px;
    font-weight: 700;
}

.crm-shell-footer {
    padding: 18px 20px 0;
    color: #74788d;
    font-size: 12px;
}

.crm-shell-footer p {
    margin: 0;
    line-height: 1.5;
}

.crm-user-menu .dropdown-menu {
    min-width: 220px;
}

.crm-user-menu-panel {
    width: 220px;
    padding: 8px 0;
}

.crm-user-menu-panel .dropdown-item,
.crm-user-menu-panel .dropdown-header {
    font-size: 13px;
    padding: 9px 16px;
}

.crm-user-menu-panel .dropdown-header {
    color: #6b7280;
    font-size: 12px;
}

.crm-user-menu-panel .dropdown-divider {
    margin: 6px 0;
}

@media (max-width: 1199.98px) {
    .crm-grid.cols-4 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 991.98px) {
    .crm-filter-form {
        grid-template-columns: 1fr;
    }

    .crm-filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .footer {
        left: 0;
    }

    .crm-grid.cols-2,
    .crm-grid.cols-3,
    .crm-grid.cols-4,
    .crm-split {
        grid-template-columns: 1fr;
    }

    .crm-page-header {
        flex-direction: column;
    }

    .app-search {
        display: none !important;
    }

    .staff-presence-launcher {
        width: auto;
    }

    .staff-presence-trigger-label {
        display: none;
    }

    .module-launcher-menu,
    .staff-presence-panel {
        width: min(420px, calc(100vw - 24px));
    }
}

@media (max-width: 767.98px) {
    .crm-card {
        padding: 18px;
    }

    .crm-filter-grid,
    .crm-field-grid {
        grid-template-columns: 1fr;
    }

    .crm-actions,
    .form-actions {
        flex-direction: column;
    }

    .crm-actions .btn,
    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }

    .crm-timeline-item {
        grid-template-columns: 1fr;
    }

    .crm-timeline-time {
        text-align: left;
    }

    .crm-detail-hero {
        align-items: flex-start;
        flex-direction: column;
    }
}
