@include('layouts.website-base-styles')

:root {
    color-scheme: light;
}

* {
    box-sizing: border-box;
}

body.crm-body {
    margin: 0;
    min-height: 100vh;
    background:
        radial-gradient(circle at top left, rgba(67, 77, 176, 0.12), transparent 30%),
        radial-gradient(circle at top right, rgba(62, 207, 142, 0.12), transparent 26%),
        #f4f7fc;
    color: var(--fg-1);
    font-family: var(--font-body);
}

a {
    color: inherit;
    text-decoration: none;
}

.crm-app {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 280px minmax(0, 1fr);
}

.crm-sidebar {
    position: sticky;
    top: 0;
    min-height: 100vh;
    padding: 28px 20px 24px;
    background: linear-gradient(180deg, #101630 0%, #161d42 100%);
    color: rgba(255, 255, 255, 0.88);
    border-right: 1px solid rgba(255, 255, 255, 0.08);
}

.crm-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
}

.crm-brand img {
    width: 38px;
    height: 38px;
}

.crm-brand-copy strong {
    display: block;
    font-size: 17px;
    font-weight: 700;
}

.crm-brand-copy span {
    display: block;
    margin-top: 2px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.62);
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.crm-sidebar-group {
    margin-top: 22px;
}

.crm-sidebar-label {
    margin-bottom: 10px;
    color: rgba(255, 255, 255, 0.48);
    font-size: 11px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.crm-sidebar-nav {
    display: grid;
    gap: 8px;
}

.crm-sidebar-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 14px;
    color: rgba(255, 255, 255, 0.76);
    transition: background 0.2s ease, transform 0.2s ease, color 0.2s ease;
}

.crm-sidebar-link:hover,
.crm-sidebar-link.is-active {
    color: #fff;
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(2px);
}

.crm-sidebar-link small {
    color: rgba(255, 255, 255, 0.45);
    font-size: 11px;
}

.crm-sidebar-footer {
    margin-top: 24px;
    padding: 18px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.crm-sidebar-footer h3 {
    margin: 0 0 8px;
    font-size: 14px;
}

.crm-sidebar-footer p {
    margin: 0;
    color: rgba(255, 255, 255, 0.62);
    font-size: 13px;
    line-height: 1.6;
}

.crm-main {
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.crm-topbar {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    align-items: center;
    padding: 28px 34px 18px;
}

.crm-heading-wrap h1 {
    margin: 0;
    font-family: var(--font-display);
    font-size: clamp(28px, 4vw, 38px);
    line-height: 1;
    letter-spacing: -0.04em;
}

.crm-heading-wrap p {
    margin: 10px 0 0;
    max-width: 760px;
    color: var(--fg-3);
    font-size: 15px;
    line-height: 1.7;
}

.crm-topbar-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.crm-user-chip {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.88);
    border: 1px solid rgba(15, 23, 42, 0.06);
    box-shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
}

.crm-user-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
}

.crm-user-chip strong {
    display: block;
    font-size: 14px;
}

.crm-user-chip span {
    display: block;
    color: var(--fg-3);
    font-size: 12px;
}

.crm-utility-links {
    display: flex;
    align-items: center;
    gap: 10px;
}

.crm-content {
    padding: 0 34px 34px;
}

.crm-stack {
    display: grid;
    gap: 22px;
}

.crm-grid {
    display: grid;
    gap: 22px;
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
    grid-template-columns: minmax(320px, 380px) minmax(0, 1fr);
    gap: 22px;
    align-items: start;
}

.crm-card,
.crm-metric {
    background: rgba(255, 255, 255, 0.94);
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 22px;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
}

.crm-card {
    padding: 24px;
}

.crm-card-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}

.crm-card-title h2,
.crm-card-title h3 {
    margin: 0;
    font-size: 20px;
    font-family: var(--font-display);
    letter-spacing: -0.03em;
}

.crm-card-title p {
    margin: 8px 0 0;
    color: var(--fg-3);
    font-size: 14px;
}

.crm-metric {
    padding: 20px 22px;
}

.crm-metric span {
    display: block;
    color: var(--fg-3);
    font-size: 12px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.crm-metric strong {
    display: block;
    margin-top: 12px;
    font-size: 36px;
    line-height: 1;
    font-family: var(--font-display);
    letter-spacing: -0.05em;
}

.crm-tabs {
    display: inline-flex;
    gap: 8px;
    padding: 6px;
    border-radius: 16px;
    background: rgba(67, 77, 176, 0.08);
}

.crm-tab {
    padding: 10px 16px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    color: var(--fg-3);
}

.crm-tab.is-active {
    background: #fff;
    color: var(--brand-indigo-500);
    box-shadow: 0 10px 24px rgba(67, 77, 176, 0.14);
}

.crm-form {
    display: grid;
    gap: 18px;
}

.crm-field-grid {
    display: grid;
    gap: 16px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.crm-field {
    display: grid;
    gap: 8px;
}

.crm-field.full {
    grid-column: 1 / -1;
}

.crm-field label {
    font-size: 13px;
    font-weight: 600;
    color: var(--fg-2);
}

.crm-field input,
.crm-field select,
.crm-field textarea {
    width: 100%;
    border: 1px solid rgba(148, 163, 184, 0.36);
    border-radius: 14px;
    padding: 12px 14px;
    background: #fff;
    color: var(--fg-1);
    font: inherit;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.crm-field textarea {
    min-height: 120px;
    resize: vertical;
}

.crm-field input:focus,
.crm-field select:focus,
.crm-field textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
}

.crm-check {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: var(--fg-2);
}

.crm-check input {
    width: 18px;
    height: 18px;
}

.crm-help {
    padding: 14px 16px;
    border-radius: 16px;
    background: rgba(78, 115, 223, 0.08);
    border-left: 4px solid #4e73df;
    color: var(--fg-2);
    font-size: 14px;
    line-height: 1.6;
}

.crm-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 16px;
    border-top: 1px solid rgba(148, 163, 184, 0.18);
}

.crm-table-wrap {
    overflow-x: auto;
}

.crm-table {
    width: 100%;
    border-collapse: collapse;
}

.crm-table th,
.crm-table td {
    padding: 14px 12px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.16);
    text-align: left;
    vertical-align: top;
    font-size: 14px;
}

.crm-table th {
    color: var(--fg-3);
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.crm-table strong {
    display: block;
    margin-bottom: 4px;
}

.crm-table td small,
.crm-table td span.crm-muted {
    display: block;
    color: var(--fg-3);
}

.crm-pill {
    display: inline-flex;
    align-items: center;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.crm-pill.primary {
    background: rgba(67, 77, 176, 0.1);
    color: var(--brand-indigo-500);
}

.crm-pill.success {
    background: rgba(22, 163, 74, 0.12);
    color: #166534;
}

.crm-pill.warning {
    background: rgba(245, 158, 11, 0.14);
    color: #92400e;
}

.crm-pill.danger {
    background: rgba(239, 68, 68, 0.12);
    color: #b91c1c;
}

.crm-pill.muted {
    background: rgba(148, 163, 184, 0.14);
    color: #475569;
}

.crm-kicker {
    margin: 0 0 8px;
    color: var(--brand-indigo-500);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.crm-meta-list {
    display: grid;
    gap: 12px;
}

.crm-meta-row {
    display: flex;
    justify-content: space-between;
    gap: 14px;
    align-items: flex-start;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.16);
}

.crm-meta-row:last-child {
    padding-bottom: 0;
    border-bottom: 0;
}

.crm-meta-row span {
    color: var(--fg-3);
    font-size: 13px;
}

.crm-meta-row strong {
    font-size: 14px;
    text-align: right;
}

.crm-list {
    display: grid;
    gap: 14px;
}

.crm-list-item {
    padding: 16px 18px;
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    background: rgba(247, 249, 252, 0.9);
}

.crm-list-item h4 {
    margin: 0 0 6px;
    font-size: 16px;
}

.crm-list-item p {
    margin: 0;
    color: var(--fg-3);
    font-size: 14px;
    line-height: 1.6;
}

.crm-list-item .crm-inline {
    margin-top: 10px;
}

.crm-inline {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

.crm-empty {
    padding: 24px;
    text-align: center;
    border-radius: 18px;
    border: 1px dashed rgba(148, 163, 184, 0.34);
    color: var(--fg-3);
    background: rgba(255, 255, 255, 0.68);
}

.crm-alerts {
    display: grid;
    gap: 12px;
    margin-bottom: 22px;
}

.crm-alert {
    padding: 14px 18px;
    border-radius: 16px;
    font-size: 14px;
    line-height: 1.6;
}

.crm-alert.success {
    background: rgba(22, 163, 74, 0.12);
    color: #166534;
    border: 1px solid rgba(22, 163, 74, 0.16);
}

.crm-alert.error {
    background: rgba(239, 68, 68, 0.1);
    color: #991b1b;
    border: 1px solid rgba(239, 68, 68, 0.15);
}

.crm-timeline {
    display: grid;
    gap: 16px;
}

.crm-timeline-item {
    display: grid;
    grid-template-columns: 84px 1fr;
    gap: 16px;
}

.crm-timeline-time {
    color: var(--fg-3);
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.crm-timeline-card {
    padding: 16px 18px;
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    background: rgba(247, 249, 252, 0.9);
}

.crm-timeline-card h4 {
    margin: 0 0 6px;
    font-size: 15px;
}

.crm-timeline-card p {
    margin: 0;
    color: var(--fg-3);
    font-size: 14px;
    line-height: 1.6;
}

.crm-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding-top: 18px;
}

.crm-pagination-links {
    display: flex;
    gap: 10px;
}

.crm-pagination a,
.crm-pagination span {
    font-size: 14px;
    color: var(--fg-2);
}

.crm-stat-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.crm-stage-card {
    padding: 18px;
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.92) 0%, rgba(243, 247, 255, 0.88) 100%);
}

.crm-stage-card strong {
    display: block;
    font-size: 28px;
    line-height: 1;
    margin-top: 10px;
    font-family: var(--font-display);
}

.crm-muted-copy {
    color: var(--fg-3);
    font-size: 14px;
}

.btn {
    appearance: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 18px;
    border-radius: 14px;
    border: 1px solid transparent;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #fff;
    box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}

.btn-secondary {
    background: rgba(15, 23, 42, 0.04);
    border-color: rgba(148, 163, 184, 0.24);
    color: var(--fg-1);
}

.btn-danger {
    background: rgba(239, 68, 68, 0.1);
    color: #b91c1c;
    border-color: rgba(239, 68, 68, 0.12);
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

.d-none {
    display: none !important;
}

@media (max-width: 1180px) {
    .crm-app {
        grid-template-columns: 1fr;
    }

    .crm-sidebar {
        position: static;
        min-height: auto;
    }

    .crm-topbar {
        padding-top: 22px;
    }
}

@media (max-width: 900px) {
    .crm-topbar,
    .crm-content {
        padding-left: 18px;
        padding-right: 18px;
    }

    .crm-topbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .crm-grid.cols-2,
    .crm-grid.cols-3,
    .crm-grid.cols-4,
    .crm-split,
    .crm-field-grid,
    .crm-stat-grid,
    .crm-timeline-item {
        grid-template-columns: 1fr;
    }

    .crm-timeline-time {
        margin-bottom: -6px;
    }

    .crm-topbar-actions,
    .crm-utility-links {
        width: 100%;
        flex-wrap: wrap;
    }
}
