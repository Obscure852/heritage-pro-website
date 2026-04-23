body.crm-body {
    font-family: 'Inter', sans-serif;
    background: #f8fafc;
    color: #1f2937;
    --crm-shell-inline-start: 24px;
    --crm-shell-inline-end: 20px;
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

.page-content > .container-fluid,
.footer > .container-fluid {
    padding-left: 0;
    padding-right: 0;
}

.crm-shell-content {
    padding-left: var(--crm-shell-inline-start);
    padding-right: var(--crm-shell-inline-end);
}

.crm-shell-content-footer {
    padding-top: 18px;
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

.crm-sidebar-child-link {
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.crm-sidebar-child-link > span:first-child {
    min-width: 0;
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
    font-size: 21px;
    font-weight: 600;
    color: #1f2937;
}

.crm-page-subtitle {
    margin: 8px 0 0;
    color: #6b7280;
    font-size: 12px;
    line-height: 1.5;
    max-width: 860px;
}

.crm-page-tools {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.crm-page-header-tools-only {
    justify-content: flex-end;
    align-items: center;
    margin-bottom: 14px;
}

.crm-summary-hero {
    display: grid;
    grid-template-columns: minmax(260px, 1.3fr) auto;
    align-items: center;
    gap: 28px;
    padding: 28px;
    border-radius: 3px;
    background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
    box-shadow: 0 12px 28px rgba(37, 99, 235, 0.18);
    margin-bottom: 20px;
}

.crm-summary-hero-copy {
    min-width: 0;
}

.crm-summary-hero-title {
    margin: 0;
    font-size: 21px;
    font-weight: 700;
    color: #334155;
}

.crm-summary-hero-subtitle {
    margin: 10px 0 0;
    max-width: 760px;
    color: rgba(255, 255, 255, 0.95);
    font-size: 12px;
    line-height: 1.5;
}

.crm-summary-hero-stats {
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: minmax(110px, 1fr);
    gap: 28px;
    align-items: center;
}

.crm-summary-hero-stat {
    text-align: center;
}

.crm-summary-hero-stat strong {
    display: block;
    font-size: 35px;
    line-height: 1;
    font-weight: 700;
    color: #fff;
}

.crm-summary-hero-stat span {
    display: block;
    margin-top: 8px;
    color: rgba(255, 255, 255, 0.95);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
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

.crm-choice-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.crm-choice-card {
    display: grid;
    gap: 18px;
}

.crm-choice-list {
    display: grid;
    gap: 10px;
}

.crm-choice-list span {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #475569;
    font-size: 13px;
}

.crm-choice-list i {
    color: #2563eb;
    font-size: 15px;
}

.crm-split {
    display: grid;
    grid-template-columns: minmax(320px, 420px) minmax(0, 1fr);
    gap: 20px;
    align-items: start;
}

.crm-card,
.crm-metric {
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

.crm-filter-form-sales {
    gap: 12px;
}

.crm-filter-grid-sales {
    grid-template-columns: minmax(180px, 1.4fr) repeat(4, minmax(120px, 0.85fr));
    gap: 12px;
}

.crm-filter-form > .form-actions {
    margin-top: 0;
    padding-top: 0;
    border-top: 0;
    align-self: end;
    flex-shrink: 0;
    flex-wrap: wrap;
    white-space: normal;
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
    font-size: 19px;
    font-weight: 600;
    color: #1f2937;
}

.crm-card-title p {
    margin: 6px 0 0;
    color: #6b7280;
    font-size: 12px;
    line-height: 1.5;
}

.crm-kicker {
    margin: 0 0 6px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #4e73df;
}

.crm-help {
    background: #f8f9fa;
    padding: 16px 18px;
    border-left: 4px solid #3b82f6;
    border-radius: 0 3px 3px 0;
    margin-bottom: 20px;
    color: #6b7280;
    font-size: 12px;
    line-height: 1.5;
}

.crm-help-title {
    margin: 0 0 6px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.crm-help-content {
    margin: 0;
    color: #6b7280;
    font-size: 12px;
    line-height: 1.5;
}

.crm-page-help {
    margin-bottom: 0;
}

.crm-import-guide {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.crm-import-guide-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    border: 1px solid #e5e7eb;
    border-radius: 3px;
    background: #fff;
}

.crm-import-guide-item code {
    margin: 0;
    font-size: 11px;
    font-weight: 600;
    color: #1d4ed8;
    background: #eff6ff;
    border-radius: 3px;
    padding: 6px 8px;
}

.crm-import-guide-item span {
    color: #6b7280;
    font-size: 11px;
    font-weight: 500;
    white-space: nowrap;
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
    font-size: 13px;
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
    font-size: 13px;
    transition: all 0.2s;
    background: #fff;
    color: #1f2937;
}

.crm-field textarea {
    min-height: 108px;
    resize: vertical;
}

.crm-field input[type="file"] {
    padding: 12px;
    background: #f8fafc;
}

.crm-field input[type="file"]::file-selector-button {
    margin-right: 12px;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 3px;
    background: #fff;
    color: #374151;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.crm-field input[type="file"]::file-selector-button:hover {
    border-color: #93c5fd;
    color: #1d4ed8;
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
    font-size: 13px;
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

.btn-primary,
.btn-primary i,
.crm-card a.btn-primary,
.crm-card a.btn-primary i {
    color: #fff;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    color: white;
}

.crm-card a.btn-primary:hover,
.crm-card a.btn-primary:hover i {
    color: #fff;
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
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.crm-metric strong {
    display: block;
    padding: 18px;
    font-size: 25px;
    font-weight: 700;
    color: #1f2937;
}

.crm-tabs {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 28px;
    padding: 0 2px;
    border-bottom: 1px solid #dbe3ef;
    background: transparent;
}

.crm-tab {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0 0 12px;
    font-size: 13px;
    font-weight: 500;
    line-height: 1;
    color: #6b7280;
    transition: color 0.2s ease;
}

.crm-tab i {
    font-size: 15px;
    color: inherit;
}

.crm-tab::after {
    content: '';
    position: absolute;
    right: 0;
    bottom: -1px;
    left: 0;
    height: 2px;
    border-radius: 999px;
    background: #4e73df;
    opacity: 0;
    transform: scaleX(0.55);
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.crm-tab:hover,
.crm-tab.is-active {
    color: #4e73df;
}

.crm-tab:hover::after,
.crm-tab.is-active::after {
    opacity: 1;
    transform: scaleX(1);
}

.crm-tabs-top {
    padding-left: 0;
    padding-right: 0;
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
    font-size: 12px;
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
    font-size: 14px;
    font-weight: 600;
}

.crm-list-item p {
    margin: 8px 0 0;
    color: #6b7280;
    font-size: 12px;
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
    flex-wrap: nowrap;
}

.crm-icon-action {
    width: 36px;
    height: 36px;
    padding: 0;
    justify-content: center;
    border: 1px solid #dbe1ea;
    background: #fff;
    color: #475569;
    box-shadow: none;
}

.crm-icon-action:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
}

.crm-icon-action.crm-icon-danger {
    color: #dc2626;
}

.crm-icon-action.crm-icon-danger:hover {
    background: #fff5f5;
    border-color: #fecaca;
    color: #b91c1c;
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.1);
}

.crm-table-actions .btn {
    padding: 8px 14px;
    font-size: 12px;
}

.crm-table-actions .btn.crm-icon-action {
    width: 36px;
    padding: 0;
}

.crm-table-actions .crm-action-row {
    justify-content: flex-end;
}

.crm-attachment-empty {
    padding: 18px 20px;
    border: 1px dashed #d1d5db;
    border-radius: 3px;
    background: #f8fafc;
    color: #6b7280;
    font-size: 12px;
}

.crm-attachments-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.crm-attachment-card {
    display: grid;
    gap: 14px;
    padding: 16px;
    border: 1px solid #e5e7eb;
    border-radius: 3px;
    background: #fff;
}

.crm-attachment-head {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.crm-attachment-icon {
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 3px;
    background: #eef2ff;
    color: #2563eb;
    font-size: 17px;
}

.crm-attachment-copy {
    min-width: 0;
    display: grid;
    gap: 4px;
}

.crm-attachment-copy strong {
    font-size: 13px;
    color: #1f2937;
    overflow-wrap: anywhere;
}

.crm-attachment-copy span {
    color: #6b7280;
    font-size: 11px;
    line-height: 1.5;
}

.crm-attachment-actions {
    justify-content: flex-start;
    flex-wrap: wrap;
}

.crm-dropzone {
    position: relative;
    display: grid;
    gap: 16px;
    padding: 22px;
    border: 1px dashed #93c5fd;
    border-radius: 3px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    transition: border-color 0.18s ease, background 0.18s ease, box-shadow 0.18s ease;
    cursor: pointer;
}

.crm-dropzone:hover,
.crm-dropzone.is-dragover {
    border-color: #3b82f6;
    background: linear-gradient(180deg, #ffffff 0%, #eff6ff 100%);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.08);
}

.crm-dropzone-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

.crm-dropzone-copy {
    text-align: center;
    display: grid;
    gap: 8px;
    justify-items: center;
    color: #475569;
}

.crm-dropzone-copy strong {
    font-size: 14px;
    color: #1f2937;
}

.crm-dropzone-copy p {
    margin: 0;
    font-size: 12px;
    color: #6b7280;
}

.crm-dropzone-icon {
    width: 52px;
    height: 52px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: #dbeafe;
    color: #2563eb;
    font-size: 21px;
}

.crm-dropzone-list {
    display: grid;
    gap: 8px;
}

.crm-import-dropzone {
    padding: 26px;
}

.crm-import-dropzone .crm-dropzone-copy {
    gap: 10px;
}

.crm-import-dropzone .crm-dropzone-copy strong {
    font-size: 15px;
}

.crm-import-dropzone .crm-dropzone-copy p {
    max-width: 360px;
}

.crm-import-dropzone-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 12px;
}

.crm-import-dropzone-meta span {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border-radius: 999px;
    border: 1px solid #dbeafe;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 11px;
    font-weight: 500;
}

.crm-dropzone-empty {
    font-size: 11px;
    color: #6b7280;
    text-align: center;
}

.crm-dropzone-file {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 3px;
    background: #fff;
    border: 1px solid #dbe1ea;
}

.crm-dropzone-file strong {
    font-size: 12px;
    color: #1f2937;
    overflow-wrap: anywhere;
}

.crm-dropzone-file span {
    font-size: 11px;
    color: #6b7280;
    white-space: nowrap;
}

.crm-empty {
    min-height: 240px;
    padding: 32px 24px;
    border-radius: 3px;
    border: 1px dashed #d1d5db;
    background: #f8fafc;
    color: #6b7280;
    font-size: 12px;
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
    font-size: 11px;
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
    font-size: 11px;
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
    font-size: 12px;
}

.crm-meta-row strong {
    text-align: right;
    font-size: 13px;
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
    font-size: 11px;
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
    font-size: 14px;
    font-weight: 600;
}

.crm-timeline-card p {
    margin: 0;
    color: #374151;
    font-size: 12px;
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
    font-size: 25px;
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
    font-size: 12px;
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

.crm-toast-stack {
    position: fixed;
    top: 86px;
    right: 22px;
    z-index: 1600;
    width: min(392px, calc(100vw - 28px));
    display: grid;
    gap: 12px;
    pointer-events: none;
}

.crm-toast {
    position: relative;
    overflow: hidden;
    display: grid;
    grid-template-columns: 44px minmax(0, 1fr) 32px;
    gap: 14px;
    align-items: start;
    padding: 16px 16px 16px 14px;
    border-radius: 3px;
    border: 1px solid #dbe3ee;
    background: rgba(255, 255, 255, 0.98);
    box-shadow: 0 22px 44px rgba(15, 23, 42, 0.16);
    backdrop-filter: blur(10px);
    pointer-events: auto;
    opacity: 0;
    transform: translate3d(120%, 0, 0);
    animation: crmToastSlideIn 0.34s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

.crm-toast.is-closing {
    animation: crmToastSlideOut 0.24s ease forwards;
}

.crm-toast-success {
    border-color: rgba(16, 185, 129, 0.18);
}

.crm-toast-error {
    border-color: rgba(239, 68, 68, 0.16);
}

.crm-toast-icon {
    width: 44px;
    height: 44px;
    border-radius: 3px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

.crm-toast-success .crm-toast-icon {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    color: #059669;
}

.crm-toast-error .crm-toast-icon {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    color: #dc2626;
}

.crm-toast-copy {
    min-width: 0;
}

.crm-toast-title {
    margin: 0;
    color: #0f172a;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.3;
}

.crm-toast-message {
    margin: 4px 0 0;
    color: #475569;
    font-size: 13px;
    line-height: 1.55;
}

.crm-toast-list {
    margin: 8px 0 0 18px;
    padding: 0;
    color: #475569;
    font-size: 12px;
    line-height: 1.55;
}

.crm-toast-list li + li {
    margin-top: 4px;
}

.crm-toast-close {
    width: 32px;
    height: 32px;
    border: 0;
    border-radius: 3px;
    background: transparent;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    transition: background 0.18s ease, color 0.18s ease;
}

.crm-toast-close:hover {
    background: #f8fafc;
    color: #0f172a;
}

.crm-toast-close:focus-visible {
    outline: 0;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
}

.crm-toast-progress {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 3px;
    transform-origin: left center;
}

.crm-toast-success .crm-toast-progress {
    background: linear-gradient(90deg, #34d399 0%, #10b981 100%);
}

.crm-toast-error .crm-toast-progress {
    background: linear-gradient(90deg, #f87171 0%, #ef4444 100%);
}

.crm-toast.is-paused .crm-toast-progress {
    animation-play-state: paused !important;
}

@keyframes crmToastSlideIn {
    from {
        opacity: 0;
        transform: translate3d(120%, 0, 0);
    }

    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

@keyframes crmToastSlideOut {
    from {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }

    to {
        opacity: 0;
        transform: translate3d(120%, 0, 0);
    }
}

@keyframes crmToastProgress {
    from {
        transform: scaleX(1);
    }

    to {
        transform: scaleX(0);
    }
}

@media (max-width: 767.98px) {
    .crm-toast-stack {
        top: 74px;
        right: 14px;
        left: 14px;
        width: auto;
    }

    .crm-toast {
        grid-template-columns: 40px minmax(0, 1fr) 30px;
        gap: 12px;
        padding: 14px 14px 14px 12px;
    }

    .crm-toast-icon {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }
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
    font-size: 12px;
    font-weight: 700;
}

.crm-user-avatar-photo {
    display: block;
    object-fit: cover;
    background: #fff;
}

.crm-user-avatar-placeholder {
    background: #e2e8f0;
    border: 1px solid #cbd5e1;
    color: #475569;
}

.crm-initial-avatar.crm-initial-avatar-lg {
    width: 72px;
    height: 72px;
    font-size: 21px;
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
    font-size: 27px;
    font-weight: 700;
    color: #1f2937;
}

.crm-detail-hero-copy p {
    margin: 0;
    color: #6b7280;
    font-size: 13px;
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
    font-size: 13px;
    line-height: 1.7;
    min-height: 140px;
}

.app-search .position-relative {
    width: 340px;
}

.app-search .form-control {
    height: 38px;
    padding-right: 88px;
    font-size: 12px;
    font-weight: 600;
}

.app-search .search-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #74788d;
    font-size: 13px;
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
    font-size: 10px;
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
    font-size: 10px;
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
    font-size: 12px;
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
    font-size: 12px;
    margin-bottom: 3px;
}

.app-search .result-details,
.app-search .no-results,
.app-search .loading-results {
    font-size: 11px;
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
    font-size: 10px;
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
    font-size: 12px;
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
    font-size: 11px;
    font-weight: 700;
}

.staff-presence-trigger-unread {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    padding: 0 6px;
    border-radius: 999px;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    box-shadow: 0 8px 18px rgba(239, 68, 68, 0.24);
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
    font-size: 13px;
}

.staff-presence-panel-header span {
    color: #64748b;
    font-size: 11px;
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
    font-size: 11px;
}

.staff-presence-sound-control {
    margin: 12px 18px 0;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #dbeafe;
    background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
}

.staff-presence-sound-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.staff-presence-sound-toggle {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 3px;
}

.staff-presence-sound-preview {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 3px;
}

.staff-presence-sound-toggle i {
    font-size: 16px;
}

.staff-presence-sound-preview i {
    font-size: 16px;
    color: #0891b2;
}

.staff-presence-sound-toggle.is-enabled {
    border-color: #bfdbfe;
    color: #1d4ed8;
}

.staff-presence-sound-toggle.is-muted {
    border-color: #cbd5e1;
    color: #475569;
}

.staff-presence-sound-status {
    display: block;
    margin-top: 8px;
    color: #64748b;
    font-size: 11px;
    line-height: 1.45;
}

.staff-presence-unread-panel {
    margin: 12px 18px 0;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #fee2e2;
    background: linear-gradient(180deg, #fff7f7 0%, #ffffff 100%);
}

.staff-presence-unread-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 10px;
}

.staff-presence-unread-header strong {
    color: #991b1b;
    font-size: 12px;
}

.staff-presence-unread-list {
    display: grid;
    gap: 8px;
}

.staff-presence-unread-link {
    display: grid;
    grid-template-columns: 28px minmax(0, 1fr);
    gap: 10px;
    align-items: start;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #fecaca;
    background: #fff;
    transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
}

.staff-presence-unread-link:hover {
    border-color: #f87171;
    box-shadow: 0 10px 22px rgba(239, 68, 68, 0.12);
    transform: translateY(-1px);
}

.staff-presence-unread-icon {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: #fee2e2;
    color: #dc2626;
    font-size: 15px;
}

.staff-presence-unread-copy {
    min-width: 0;
}

.staff-presence-unread-copy strong,
.staff-presence-unread-copy span {
    display: block;
}

.staff-presence-unread-copy strong {
    color: #0f172a;
    font-size: 12px;
    line-height: 1.4;
}

.staff-presence-unread-copy span {
    margin-top: 3px;
    color: #64748b;
    font-size: 11px;
}

.staff-presence-unread-meta {
    color: #991b1b !important;
    font-weight: 600;
}

.staff-presence-unread-preview {
    color: #475569 !important;
    line-height: 1.45;
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
    font-size: 12px;
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
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 2px;
}

.staff-presence-role,
.staff-presence-last-seen {
    color: #64748b;
    font-size: 11px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.staff-presence-item .btn {
    padding: 6px 12px;
    font-size: 11px;
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
    font-size: 10px;
    font-weight: 700;
}

.crm-sidebar-badge.is-alert {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #fff;
    box-shadow: 0 8px 18px rgba(239, 68, 68, 0.18);
}

.crm-shell-footer {
    padding: 18px 20px 0;
    color: #74788d;
    font-size: 11px;
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
    font-size: 12px;
    padding: 9px 16px;
}

.crm-user-menu-panel .dropdown-header {
    color: #6b7280;
    font-size: 11px;
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
    body.crm-body {
        --crm-shell-inline-start: 10px;
        --crm-shell-inline-end: 10px;
    }

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
    .crm-choice-grid,
    .crm-split {
        grid-template-columns: 1fr;
    }

    .crm-page-header {
        flex-direction: column;
    }

    .crm-page-header-tools-only {
        flex-direction: row;
        justify-content: flex-end;
    }

    .crm-summary-hero {
        grid-template-columns: 1fr;
    }

    .crm-summary-hero-stats {
        grid-auto-flow: unset;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px 24px;
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

    .crm-attachments-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767.98px) {
    .crm-card {
        padding: 18px;
    }

    .crm-summary-hero {
        padding: 24px 18px;
    }

    .crm-summary-hero-title {
        font-size: 19px;
    }

    .crm-summary-hero-stats {
        gap: 16px;
    }

    .crm-summary-hero-stat strong {
        font-size: 27px;
    }

    .crm-tabs {
        gap: 18px;
    }

    .crm-tab {
        padding-bottom: 10px;
    }

    .crm-filter-grid,
    .crm-import-guide,
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

    .crm-action-row {
        flex-wrap: wrap;
    }

    .crm-detail-hero {
        align-items: flex-start;
        flex-direction: column;
    }
}
