<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Heritage Pro Client Setup')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.crm-head-css')
    <style>
        body.crm-wizard-body {
            --crm-wizard-control-radius: 4px;
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(circle at top left, rgba(67, 77, 176, 0.14), transparent 32%),
                radial-gradient(circle at top right, rgba(62, 207, 142, 0.12), transparent 28%),
                #f4f7fc;
        }

        .crm-wizard-shell {
            width: min(100% - 32px, 1080px);
            margin: 0 auto;
            padding: 28px 0 56px;
        }

        .crm-wizard-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .crm-wizard-brand-mark {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 4px;
            box-shadow: 0 12px 26px rgba(36, 59, 122, .2);
        }

        .crm-wizard-brand-mark img {
            width: 100%;
            height: 100%;
            max-width: none;
            object-fit: contain;
        }

        .crm-wizard-brand-copy strong,
        .crm-wizard-brand-copy span {
            display: block;
        }

        .crm-wizard-brand-copy strong {
            color: #172033;
            font-size: 17px;
        }

        .crm-wizard-brand-copy span {
            margin-top: 2px;
            color: #64748b;
            font-size: 11px;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .crm-wizard-header {
            margin-bottom: 20px;
        }

        .crm-wizard-eyebrow {
            margin: 0 0 8px;
            color: #4f46b5;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .crm-wizard-header-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 250px;
            gap: 28px;
            align-items: end;
            margin-bottom: 24px;
        }

        .crm-wizard-header-row .crm-wizard-header {
            margin-bottom: 0;
        }

        .crm-wizard-header h1 {
            margin: 0;
            color: #172033;
            font-size: clamp(26px, 4vw, 38px);
            line-height: 1.1;
        }

        .crm-wizard-header p {
            max-width: 720px;
            margin: 10px 0 0;
            color: #64748b;
            line-height: 1.6;
        }

        .crm-wizard-progress {
            padding: 15px 16px;
            border: 1px solid rgba(148, 163, 184, .3);
            border-radius: 14px;
            background: rgba(255, 255, 255, .72);
        }

        .crm-wizard-progress-meta,
        .crm-wizard-stage-meta {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            color: #64748b;
            font-size: 12px;
        }

        .crm-wizard-progress-meta strong {
            color: #243b7a;
            font-size: 18px;
        }

        .crm-wizard-progress-track {
            height: 7px;
            margin: 11px 0 8px;
            overflow: hidden;
            border-radius: 999px;
            background: #e7ebf3;
        }

        .crm-wizard-progress-track span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #4f46b5, #3ecf8e);
            transition: width .25s ease;
        }

        .crm-wizard-progress small {
            color: #64748b;
            font-size: 11px;
        }

        .crm-wizard-mobile-stage {
            display: none;
            margin-bottom: 16px;
        }

        .crm-wizard-mobile-stage label {
            display: block;
            margin-bottom: 8px;
        }

        .crm-wizard-layout {
            display: grid;
            grid-template-columns: 268px minmax(0, 1fr);
            gap: 22px;
            align-items: start;
        }

        .crm-wizard-rail {
            position: sticky;
            top: 18px;
            padding: 18px;
            border: 1px solid rgba(148, 163, 184, .3);
            border-radius: 16px;
            background: rgba(255, 255, 255, .8);
        }

        .crm-wizard-rail-heading,
        .crm-wizard-stage-panel-header,
        .crm-wizard-saved-summary,
        .crm-wizard-form-actions,
        .crm-wizard-form-actions-right {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .crm-wizard-rail-heading {
            align-items: flex-start;
            padding-bottom: 14px;
            border-bottom: 1px solid #e7ebf3;
        }

        .crm-wizard-rail-heading h2,
        .crm-wizard-stage-panel-header h2,
        .crm-wizard-saved-summary h2,
        .crm-wizard-exit-panel h2 {
            margin: 0;
            color: #172033;
            font-size: 17px;
        }

        .crm-wizard-stage-panel-header h2[data-wizard-main-heading]:focus {
            outline: none;
            box-shadow: none;
        }

        .crm-wizard-rail-count {
            color: #4f46b5;
            font-size: 13px;
            font-weight: 800;
        }

        .crm-wizard-stage-list {
            display: grid;
            gap: 3px;
            margin: 14px 0 16px;
        }

        .crm-wizard-stage-item {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            padding: 9px 8px;
            border-radius: 10px;
            color: #475569;
            text-decoration: none;
            transition: background .18s ease, color .18s ease, transform .18s ease;
        }

        a.crm-wizard-stage-item:hover {
            background: #f1f4fb;
            color: #243b7a;
            transform: translateX(2px);
        }

        .crm-wizard-stage-item.state-current {
            background: #eef0ff;
            color: #243b7a;
        }

        .crm-wizard-stage-item.state-locked {
            cursor: not-allowed;
            opacity: .45;
        }

        .crm-wizard-stage-marker {
            display: inline-flex;
            flex: 0 0 25px;
            width: 25px;
            height: 25px;
            align-items: center;
            justify-content: center;
            border: 1px solid #cbd5e1;
            border-radius: 50%;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
        }

        .state-current .crm-wizard-stage-marker {
            border-color: #4f46b5;
            background: #4f46b5;
            color: #fff;
        }

        .state-complete .crm-wizard-stage-marker {
            border-color: #22a06b;
            background: #22a06b;
            color: #fff;
        }

        .crm-wizard-stage-copy {
            display: grid;
            min-width: 0;
            gap: 2px;
        }

        .crm-wizard-stage-copy strong,
        .crm-wizard-stage-copy small {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .crm-wizard-stage-copy strong {
            color: inherit;
            font-size: 12px;
        }

        .crm-wizard-stage-copy small {
            color: #94a3b8;
            font-size: 10px;
        }

        .crm-wizard-stage-current {
            margin-left: auto;
            color: #4f46b5;
        }

        .crm-wizard-rail-note {
            display: flex;
            gap: 8px;
            padding-top: 14px;
            border-top: 1px solid #e7ebf3;
            color: #64748b;
            font-size: 11px;
            line-height: 1.5;
        }

        .crm-wizard-rail-note i {
            flex: 0 0 auto;
            color: #4f46b5;
            font-size: 16px;
        }

        .crm-wizard-main {
            min-width: 0;
        }

        .crm-wizard-stage-meta {
            flex-wrap: wrap;
            margin: 0 2px 12px;
        }

        .crm-wizard-stage-meta strong {
            color: #334155;
        }

        .crm-wizard-stage-panel {
            padding: 24px;
            border: 1px solid rgba(148, 163, 184, .3);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(36, 59, 122, .07);
        }

        .crm-wizard-stage-panel-header {
            align-items: flex-start;
            padding-bottom: 18px;
            border-bottom: 1px solid #e7ebf3;
        }

        .crm-wizard-stage-panel-header p,
        .crm-wizard-saved-summary p {
            margin: 7px 0 0;
            color: #64748b;
            line-height: 1.55;
        }

        .crm-wizard-brief {
            display: flex;
            gap: 12px;
            margin: 18px 0;
            padding: 14px 16px;
            border-left: 3px solid #4f46b5;
            background: #f5f6ff;
            color: #334155;
        }

        .crm-wizard-brief-icon {
            color: #4f46b5;
            font-size: 20px;
        }

        .crm-wizard-brief ul {
            margin: 7px 0 0;
            padding-left: 18px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.55;
        }

        .crm-wizard-validation-summary {
            margin: 0 0 16px;
            padding: 13px 16px;
            border: 1px solid #fecaca;
            border-radius: 10px;
            background: #fff7f7;
            color: #991b1b;
        }

        .crm-wizard-validation-summary ul {
            margin: 7px 0 0;
            padding-left: 18px;
        }

        .crm-wizard-lock-notice,
        .crm-wizard-submit-panel {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 17px 18px;
            border-radius: 13px;
        }

        .crm-wizard-lock-notice {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #475569;
        }

        .crm-wizard-lock-notice > i {
            flex: 0 0 auto;
            color: #64748b;
            font-size: 22px;
        }

        .crm-wizard-lock-notice p,
        .crm-wizard-submit-panel p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 12px;
            line-height: 1.55;
        }

        .crm-wizard-submit-panel {
            align-items: center;
            margin-top: 16px;
            border: 1px solid rgba(62, 207, 142, .35);
            background: #f0fdf4;
        }

        .crm-wizard-submit-panel h2 {
            margin: 0;
            color: #166534;
            font-size: 17px;
        }

        .crm-wizard-readiness-panel {
            margin-top: 16px;
            padding: 18px 20px;
            border: 1px solid #fde68a;
            border-radius: 14px;
            background: #fffbeb;
        }

        .crm-wizard-readiness-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
        }

        .crm-wizard-readiness-heading h2 {
            margin: 0;
            color: #92400e;
            font-size: 17px;
        }

        .crm-wizard-readiness-panel ul {
            margin: 13px 0 0;
            padding-left: 19px;
            color: #78350f;
            font-size: 12px;
            line-height: 1.55;
        }

        .crm-wizard-form-actions {
            align-items: center;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid #e7ebf3;
        }

        .crm-wizard-form-actions-right {
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        /* Keep every wizard action on the same icon baseline, including its loading state. */
        .crm-wizard-body .btn-text,
        .crm-wizard-body .btn-spinner {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            line-height: 1;
        }

        .crm-wizard-body .btn > i,
        .crm-wizard-body .btn-text > i,
        .crm-wizard-body .btn-spinner > i {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .crm-wizard-body .btn-spinner .spinner-border {
            margin-right: 0 !important;
        }

        .crm-wizard-body .crm-wizard-remove-row {
            display: inline-flex;
            width: 36px;
            min-width: 36px;
            height: 36px;
            padding: 0;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .crm-wizard-body .crm-wizard-remove-row > i {
            display: block;
            margin: 0;
            font-size: 16px;
            line-height: 1;
        }

        .crm-wizard-saved-summary {
            align-items: flex-start;
            margin-top: 16px;
            padding: 17px 19px;
            border: 1px solid rgba(62, 207, 142, .28);
            border-radius: 14px;
            background: rgba(240, 253, 244, .7);
        }

        .crm-wizard-saved-summary > p {
            max-width: 410px;
            margin: 0;
            font-size: 12px;
        }

        .crm-wizard-review-summary {
            margin-top: 16px;
            padding: 18px 20px;
            border: 1px solid rgba(148, 163, 184, .3);
            border-radius: 14px;
            background: rgba(255, 255, 255, .78);
        }

        .crm-wizard-review-summary-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .crm-wizard-review-summary-heading h2 {
            margin: 0;
            color: #172033;
            font-size: 16px;
        }

        .crm-wizard-review-summary-heading > i {
            color: #4f46b5;
            font-size: 20px;
        }

        .crm-wizard-review-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 18px;
            margin: 16px 0 0;
        }

        .crm-wizard-review-list div {
            min-width: 0;
            padding-top: 9px;
            border-top: 1px solid #e7ebf3;
        }

        .crm-wizard-review-list dt {
            color: #64748b;
            font-size: 11px;
            text-transform: capitalize;
        }

        .crm-wizard-review-list dd {
            margin: 3px 0 0;
            overflow-wrap: anywhere;
            color: #172033;
            font-size: 12px;
            font-weight: 700;
        }

        .crm-wizard-repeatable-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr)) auto;
            gap: 12px;
            align-items: end;
            padding: 14px;
            border: 1px solid #e7ebf3;
            border-radius: 12px;
            background: #fff;
        }

        .crm-wizard-structured-fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .crm-wizard-structured-fields > .crm-wizard-repeatable,
        .crm-wizard-structured-fields > .crm-wizard-field-textarea,
        .crm-wizard-structured-fields > .crm-wizard-field-multiselect {
            grid-column: 1 / -1;
        }

        .crm-wizard-structured-fields > .crm-wizard-field:not(.crm-wizard-field-textarea),
        .crm-wizard-structured-fields > .crm-wizard-field-number,
        .crm-wizard-structured-fields > .crm-wizard-field-date,
        .crm-wizard-structured-fields > .crm-wizard-field-month,
        .crm-wizard-structured-fields > .crm-wizard-field-email,
        .crm-wizard-structured-fields > .crm-wizard-field-text,
        .crm-wizard-structured-fields > .crm-wizard-field-select,
        .crm-wizard-structured-fields > .crm-wizard-field-multiselect {
            min-width: 0;
        }

        .crm-wizard-repeatable {
            display: grid;
            gap: 12px;
            margin: 0;
            padding: 16px;
            border: 1px solid #dfe5ef;
            border-radius: 13px;
            background: #fbfcff;
        }

        .crm-wizard-repeatable legend {
            float: left;
            width: 100%;
            margin: 0 0 4px;
            color: #334155;
            font-size: 13px;
            font-weight: 800;
        }

        .crm-wizard-repeatable legend + .crm-wizard-field-help {
            display: block;
            clear: both;
        }

        .crm-wizard-repeatable-card {
            display: grid;
            gap: 12px;
            padding: 14px;
            border: 1px solid #e7ebf3;
            border-radius: 11px;
            background: #fff;
        }

        .crm-wizard-repeatable-card-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: #475569;
            font-size: 12px;
        }

        .crm-wizard-repeatable-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 13px;
        }

        .crm-wizard-repeatable-grid .crm-field {
            margin: 0;
        }

        .crm-wizard-repeatable-grid .crm-wizard-repeatable {
            grid-column: 1 / -1;
        }

        .crm-wizard-checkbox-row {
            display: flex;
            align-items: flex-start;
            gap: 9px;
        }

        .crm-wizard-structured-fields > .crm-wizard-field-boolean {
            grid-column: 1 / -1;
        }

        .crm-wizard-checkbox-row label {
            margin: 1px 0 0;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.45;
        }

        .crm-wizard-checkbox-row .form-check-input {
            width: 18px;
            min-width: 18px;
            height: 18px;
            flex: 0 0 18px;
            margin-top: 2px;
            padding: 0;
            accent-color: #4f46b5;
        }

        .crm-wizard-field .form-control::placeholder,
        .crm-wizard-field .form-select option:first-child {
            color: #64748b;
            opacity: 1;
        }

        /* Make single-select controls visibly read as dropdowns in the wizard theme. */
        .crm-wizard-body select.form-select:not([multiple]) {
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
            padding-right: 40px;
            background-color: #fff;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none'%3E%3Cpath d='M3 6l5 5 5-5' stroke='%2364758b' stroke-width='1.75' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px 16px;
        }

        .crm-wizard-body select.form-select:not([multiple]):focus {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none'%3E%3Cpath d='M3 6l5 5 5-5' stroke='%234f46b5' stroke-width='1.75' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        }

        .crm-wizard-requirement-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
        }

        .crm-wizard-info-tip {
            position: relative;
            display: inline-flex;
            align-items: center;
            margin-left: 5px;
            color: #4f46b5;
            cursor: help;
            vertical-align: middle;
        }

        .crm-wizard-info-tip > i {
            font-size: 15px;
        }

        .crm-wizard-info-popover {
            position: absolute;
            z-index: 30;
            top: calc(100% + 8px);
            left: 0;
            display: grid;
            gap: 4px;
            width: min(330px, calc(100vw - 48px));
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: var(--crm-wizard-control-radius);
            background: #fff;
            box-shadow: 0 4px 8px rgba(15, 23, 42, .14);
            color: #334155;
            font-size: 11px;
            font-weight: 500;
            line-height: 1.45;
            opacity: 0;
            pointer-events: none;
            visibility: hidden;
            transform: translateY(-3px);
            transition: opacity .16s ease, transform .16s ease, visibility .16s ease;
        }

        .crm-wizard-info-popover strong {
            margin-bottom: 3px;
            color: #172033;
            font-size: 12px;
        }

        .crm-wizard-info-tip:hover .crm-wizard-info-popover,
        .crm-wizard-info-tip:focus .crm-wizard-info-popover {
            opacity: 1;
            pointer-events: auto;
            visibility: visible;
            transform: translateY(0);
        }

        [data-wizard-form] .form-control,
        [data-wizard-form] .form-select,
        [data-wizard-form] .form-check-input,
        [data-wizard-form] .btn,
        [data-wizard-form] .crm-wizard-repeatable,
        [data-wizard-form] .crm-wizard-repeatable-row,
        [data-wizard-form] .crm-wizard-repeatable-card,
        [data-wizard-form] .crm-wizard-collapsible-field,
        [data-wizard-form] .crm-wizard-file-upload,
        [data-wizard-form] .crm-wizard-attachment-list li {
            border-radius: var(--crm-wizard-control-radius);
        }

        .crm-wizard-collapsible-field {
            grid-column: 1 / -1;
            border: 1px solid #dfe5ef;
            border-radius: 13px;
            background: #fbfcff;
        }

        .crm-wizard-collapsible-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            cursor: pointer;
            color: #334155;
            font-size: 13px;
            font-weight: 800;
            list-style: none;
        }

        .crm-wizard-collapsible-summary::-webkit-details-marker {
            display: none;
        }

        .crm-wizard-collapsible-summary::after {
            content: '+';
            color: #4f46b5;
            font-size: 20px;
            line-height: 1;
        }

        .crm-wizard-collapsible-field[open] .crm-wizard-collapsible-summary::after {
            content: '−';
        }

        .crm-wizard-collapsible-badge {
            margin-left: auto;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
        }

        .crm-wizard-collapsible-body {
            display: grid;
            gap: 18px;
            padding: 0 16px 16px;
        }

        .crm-wizard-collapsible-body .crm-field {
            display: grid;
            gap: 10px;
            margin: 0;
        }

        .crm-verification-card {
            width: min(100%, 620px);
            margin: 0 auto;
            padding: 28px;
            border: 1px solid rgba(148, 163, 184, .3);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(36, 59, 122, .08);
        }

        .crm-verification-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
        }

        .crm-verification-card-header h2 {
            margin: 0;
            color: #172033;
            font-size: 24px;
            line-height: 1.15;
        }

        .crm-verification-card-header p:last-child {
            max-width: 460px;
            margin: 9px 0 0;
            color: #64748b;
            line-height: 1.55;
        }

        .crm-verification-icon {
            display: inline-flex;
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: #eef0ff;
            color: #4f46b5;
            font-size: 22px;
        }

        .crm-verification-steps {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 25px 0 28px;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 700;
        }

        .crm-verification-steps span:not(.crm-verification-step-line) {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
        }

        .crm-verification-steps b {
            display: inline-flex;
            width: 24px;
            height: 24px;
            align-items: center;
            justify-content: center;
            border: 1px solid #cbd5e1;
            border-radius: 50%;
            font-size: 11px;
        }

        .crm-verification-steps .is-complete,
        .crm-verification-steps .is-current {
            color: #243b7a;
        }

        .crm-verification-steps .is-complete b,
        .crm-verification-steps .is-current b {
            border-color: #4f46b5;
            background: #4f46b5;
            color: #fff;
        }

        .crm-verification-step-line {
            width: 54px;
            height: 1px;
            background: #dbe2ee;
        }

        .crm-verification-form {
            gap: 22px;
        }

        .crm-verification-form .crm-field {
            gap: 11px;
            align-items: center;
        }

        .crm-verification-form .crm-field > label {
            text-align: center;
        }

        .crm-otp-inputs {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 10px;
            width: 100%;
            max-width: 430px;
            margin: 0 auto;
        }

        .crm-otp-input {
            width: 100% !important;
            height: 58px;
            padding: 0 !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 12px !important;
            background: #f8fafc !important;
            color: #172033 !important;
            font-size: 25px !important;
            font-weight: 800;
            text-align: center;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .crm-otp-input:focus {
            border-color: #4f46b5 !important;
            background: #fff !important;
            box-shadow: 0 0 0 4px rgba(79, 70, 181, .13) !important;
            outline: none;
        }

        .crm-otp-input[aria-invalid="true"] {
            border-color: #dc2626 !important;
            background: #fff7f7 !important;
        }

        .crm-verification-countdown {
            display: block;
            color: #4f46b5;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
        }

        .crm-verification-countdown.is-expired {
            color: #b45309;
        }

        .crm-verification-submit {
            display: flex;
            align-items: center;
            width: 100%;
            justify-content: center;
            text-align: center;
        }

        .crm-verification-submit .btn-text,
        .crm-verification-submit .btn-spinner {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .crm-verification-submit .btn-text {
            gap: 11px;
        }

        .crm-verification-submit .btn-text i {
            font-size: 18px;
            line-height: 1;
        }

        .crm-verification-resend {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid #e7ebf3;
            color: #64748b;
            font-size: 12px;
            line-height: 1.45;
        }

        .crm-verification-resend form {
            flex: 0 0 auto;
        }

        .crm-verification-resend button:disabled {
            cursor: not-allowed;
            opacity: .55;
        }

        .crm-verification-send-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 0 0 22px;
            padding: 13px 14px;
            border: 1px solid #dbe2ee;
            border-radius: 11px;
            background: #f8fafc;
            color: #64748b;
        }

        .crm-verification-send-note i {
            flex: 0 0 auto;
            margin-top: 1px;
            color: #4f46b5;
            font-size: 18px;
        }

        .crm-verification-send-note p {
            margin: 0;
            font-size: 12px;
            line-height: 1.5;
        }

        .crm-wizard-repeatable-row .crm-field {
            margin: 0;
        }

        .crm-wizard-remove-row {
            min-height: 38px;
        }

        .crm-wizard-file-upload {
            display: grid;
            gap: 9px;
            padding: 16px;
            border: 1px solid #dbe2ee;
            border-radius: 14px;
            background: linear-gradient(145deg, #fbfcff, #f7f9fd);
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .crm-wizard-stage-content.has-stage-attachments {
            display: flex;
            flex-direction: column;
        }

        .crm-wizard-stage-content.has-stage-attachments > form.crm-form {
            display: contents;
        }

        .crm-wizard-stage-content.has-stage-attachments > form.crm-form > .crm-wizard-structured-fields {
            order: 1;
        }

        .crm-wizard-stage-content.has-stage-attachments > .crm-wizard-attachment-panel {
            order: 2;
        }

        .crm-wizard-stage-content.has-stage-attachments > form.crm-form > .crm-field-grid {
            order: 3;
        }

        .crm-wizard-stage-content.has-stage-attachments > form.crm-form > .crm-wizard-form-actions {
            order: 4;
        }

        .crm-wizard-file-upload:focus-within,
        .crm-wizard-file-upload.is-dragover {
            border-color: #7167d8;
            background: #f8f7ff;
            box-shadow: 0 0 0 3px rgba(79, 70, 181, .1);
        }

        .crm-wizard-file-input {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0 0 0 0);
            clip-path: inset(50%);
            white-space: nowrap;
        }

        .crm-wizard-file-dropzone {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 66px;
            margin: 0;
            padding: 12px 14px;
            border: 1px dashed #aeb9cc;
            border-radius: 10px;
            background: rgba(255, 255, 255, .72);
            color: #334155;
            cursor: pointer;
            transition: border-color .2s ease, background .2s ease, transform .2s ease;
        }

        .crm-wizard-file-dropzone:hover {
            border-color: #7167d8;
            background: #fff;
        }

        .crm-wizard-file-dropzone:active {
            transform: translateY(1px);
        }

        .crm-wizard-file-dropzone-icon {
            display: inline-flex;
            flex: 0 0 auto;
            width: 38px;
            height: 38px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #ecebff;
            color: #4f46b5;
            font-size: 20px;
        }

        .crm-wizard-file-dropzone-copy {
            display: grid;
            gap: 2px;
            min-width: 0;
        }

        .crm-wizard-file-dropzone-copy strong {
            color: #27324a;
            font-size: 12px;
        }

        .crm-wizard-file-dropzone-copy small {
            color: #71809a;
            font-size: 11px;
            font-weight: 500;
        }

        .crm-wizard-file-browse {
            margin-left: auto;
            padding: 7px 10px;
            border: 1px solid #d6d9f6;
            border-radius: 7px;
            background: #fff;
            color: #4f46b5;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .crm-wizard-file-name {
            display: flex;
            align-items: center;
            gap: 7px;
            min-width: 0;
            color: #71809a;
            font-size: 11px;
        }

        .crm-wizard-file-name::before {
            content: "\f15b";
            color: #94a3b8;
            font-family: 'boxicons';
            font-size: 16px;
        }

        .crm-wizard-file-upload.has-file .crm-wizard-file-name {
            color: #334155;
            font-weight: 700;
        }

        .crm-wizard-file-upload.has-file .crm-wizard-file-name::before {
            color: #1f9d68;
        }

        .crm-wizard-file-upload.needs-upload {
            border-color: #f0ad4e;
            background: #fffaf0;
            box-shadow: 0 0 0 3px rgba(240, 173, 78, .12);
        }

        .crm-wizard-file-upload.needs-upload .crm-wizard-file-name {
            color: #9a5b08;
        }

        .crm-wizard-attachment-panel {
            display: grid;
            gap: 16px;
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid #e7ebf3;
        }

        .crm-migration-template-download {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            min-height: 68px;
            margin-bottom: 14px;
            padding: 12px 14px;
            border: 1px solid #dbe2ee;
            border-radius: 12px;
            background: #f8faff;
            color: #1f2937;
            text-decoration: none;
            transition: border-color .18s ease, background-color .18s ease, transform .18s ease;
        }

        .crm-migration-template-download:hover {
            border-color: #7167d8;
            background: #f5f4ff;
            color: #312e81;
            transform: translateY(-1px);
        }

        .crm-migration-template-download:focus-visible {
            outline: 3px solid rgba(79, 70, 181, .2);
            outline-offset: 2px;
            border-color: #4f46b5;
        }

        .crm-migration-template-download-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 38px;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #ebe9ff;
            color: #4f46b5;
            font-size: 19px;
        }

        .crm-migration-template-download-copy {
            display: grid;
            min-width: 0;
            gap: 3px;
            line-height: 1.25;
        }

        .crm-migration-template-download-copy strong {
            font-size: 13px;
            font-weight: 700;
        }

        .crm-migration-template-download-copy small {
            color: #64748b;
            font-size: 11px;
        }

        .crm-migration-template-download-arrow {
            margin-left: auto;
            color: #7167d8;
            font-size: 20px;
        }

        .crm-migration-template-download:hover .crm-migration-template-download-icon {
            background: #dedcff;
        }

        @media (prefers-reduced-motion: reduce) {
            .crm-migration-template-download {
                transition: none;
            }
        }

        .crm-wizard-attachment-list {
            display: grid;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .crm-wizard-attachment-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border: 1px solid #e7ebf3;
            border-radius: 9px;
            background: #fbfcff;
        }

        .crm-wizard-attachment-list li > i {
            color: #4f46b5;
            font-size: 20px;
        }

        .crm-wizard-attachment-list span {
            display: grid;
            gap: 2px;
            min-width: 0;
        }

        .crm-wizard-attachment-list strong,
        .crm-wizard-attachment-list small {
            overflow-wrap: anywhere;
        }

        .crm-wizard-attachment-list strong {
            color: #334155;
            font-size: 12px;
        }

        .crm-wizard-attachment-list small {
            color: #64748b;
            font-size: 11px;
        }

        .crm-wizard-file-upload label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-size: 13px;
            font-weight: 700;
        }

        .crm-wizard-file-upload > label.crm-wizard-file-dropzone {
            display: flex;
            margin-bottom: 0;
        }

        .crm-wizard-exit-panel {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: 18px;
            align-items: center;
            padding: 24px;
            border: 1px solid rgba(62, 207, 142, .28);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(36, 59, 122, .07);
        }

        .crm-wizard-exit-icon {
            display: inline-flex;
            width: 46px;
            height: 46px;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: #dcfce7;
            color: #15803d;
            font-size: 24px;
        }

        .crm-wizard-exit-panel p {
            margin: 7px 0 0;
            color: #64748b;
            line-height: 1.55;
        }

        .crm-wizard-alert {
            margin: 0 0 16px;
            padding: 13px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background: #fff;
            color: #334155;
        }

        .crm-wizard-alert.success {
            border-color: #86efac;
            background: #f0fdf4;
            color: #166534;
        }

        .crm-wizard-alert.error {
            border-color: #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        .crm-wizard-footer {
            margin-top: 18px;
            color: #64748b;
            font-size: 12px;
            text-align: center;
        }

        .crm-wizard-announcement {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        @media (max-width: 640px) {
            .crm-wizard-shell {
                width: min(100% - 20px, 1080px);
                padding-top: 18px;
            }

            .crm-wizard-header-row {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .crm-wizard-layout {
                display: block;
            }

            .crm-wizard-rail {
                display: none;
            }

            .crm-wizard-mobile-stage {
                display: block;
            }

            .crm-wizard-stage-panel,
            .crm-wizard-exit-panel {
                padding: 18px;
            }

            .crm-wizard-stage-panel-header,
            .crm-wizard-saved-summary,
            .crm-wizard-exit-panel,
            .crm-wizard-lock-notice,
            .crm-wizard-submit-panel {
                display: block;
            }

            .crm-wizard-review-list,
            .crm-wizard-repeatable-row,
            .crm-wizard-repeatable-grid,
            .crm-wizard-structured-fields {
                grid-template-columns: 1fr;
            }

            .crm-wizard-stage-panel-header .crm-pill {
                display: inline-block;
                margin-top: 12px;
            }

            .crm-wizard-form-actions {
                display: block;
            }

            .crm-wizard-form-actions-right {
                justify-content: stretch;
                margin-top: 14px;
            }

            .crm-wizard-form-actions-right .btn {
                flex: 1 1 100%;
            }

            .crm-wizard-exit-icon {
                margin-bottom: 14px;
            }

            .crm-wizard-exit-panel .btn {
                display: block;
                width: 100%;
                margin-top: 18px;
            }

            .crm-wizard-submit-panel form,
            .crm-wizard-submit-panel .btn {
                width: 100%;
            }

            .crm-wizard-submit-panel form {
                margin-top: 16px;
            }

            .crm-wizard-repeatable,
            .crm-wizard-file-upload {
                padding: 14px;
            }

            .crm-wizard-file-dropzone {
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .crm-wizard-file-browse {
                margin-left: 50px;
            }

            .crm-wizard-repeatable-card {
                padding: 12px;
            }

            .crm-wizard-stage-meta {
                display: grid;
                gap: 5px;
            }

            .crm-wizard-progress-meta,
            .crm-wizard-stage-meta {
                align-items: flex-start;
            }

            .crm-wizard-stage-panel,
            .crm-wizard-saved-summary,
            .crm-wizard-review-summary,
            .crm-wizard-readiness-panel {
                overflow-wrap: anywhere;
            }

            .crm-verification-card {
                padding: 20px;
            }

            .crm-verification-card-header h2 {
                font-size: 21px;
            }

            .crm-verification-steps {
                gap: 7px;
            }

            .crm-verification-step-line {
                width: 28px;
            }

            .crm-otp-inputs {
                gap: 7px;
            }

            .crm-otp-input {
                height: 50px;
                border-radius: 10px !important;
                font-size: 22px !important;
            }

            .crm-verification-resend {
                align-items: flex-start;
                flex-direction: column;
            }

            .crm-verification-resend form,
            .crm-verification-resend button {
                width: 100%;
            }
        }
    </style>
</head>
<body class="crm-wizard-body">
    <main class="crm-wizard-shell">
        <div class="crm-wizard-brand" aria-label="Heritage Pro client setup">
            <span class="crm-wizard-brand-mark" aria-hidden="true"><img src="{{ asset('assets/images/heritage-logo.png') }}" alt=""></span>
            <span class="crm-wizard-brand-copy">
                <strong>Heritage Pro</strong>
                <span>Client setup</span>
            </span>
        </div>

        @if (session('client_setup_success'))
            <div class="crm-wizard-alert success" role="status">{{ session('client_setup_success') }}</div>
        @endif

        @if (session('client_setup_error'))
            <div class="crm-wizard-alert error" role="alert">{{ session('client_setup_error') }}</div>
        @endif

        @if ($errors->any())
            <div class="crm-wizard-alert error" role="alert" tabindex="-1" data-wizard-error-alert>
                <strong>Please review the form.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="crm-wizard-announcement" aria-live="polite" aria-atomic="true" data-wizard-announcement></div>

        @hasSection('wizard_header')
            @yield('wizard_header')
        @endif

        @yield('content')

        <div class="crm-wizard-footer">Your information is saved securely for the client setup process.</div>
    </main>
    @stack('scripts')
    <script>
        window.crmWizardNavigationAllowed = false;

        document.querySelectorAll('form').forEach(function (form) {
            var submitting = false;
            var isWizardForm = form.matches('[data-wizard-form]');
            var actionInput = form.querySelector('[data-wizard-action-input]');

            form.querySelectorAll('[data-wizard-submit-action]').forEach(function (submitButton) {
                submitButton.addEventListener('click', function () {
                    if (actionInput) {
                        actionInput.value = submitButton.dataset.wizardSubmitAction || 'save';
                    }
                });
            });

            if (isWizardForm) {
                form.addEventListener('input', function () {
                    form.dataset.dirty = 'true';
                });

                form.addEventListener('change', function () {
                    form.dataset.dirty = 'true';
                });
            }

            form.addEventListener('submit', function (event) {
                var pendingUpload = isWizardForm
                    ? document.querySelector('[data-attachment-upload-form] [data-file-input]')
                    : null;

                if (pendingUpload?.files?.length) {
                    event.preventDefault();
                    pendingUpload.closest('[data-file-upload]')?.classList.add('needs-upload');
                    pendingUpload.closest('[data-attachment-upload-form]')?.querySelector('button[type="submit"]')?.focus();
                    announceWizard('Upload the selected attachment before continuing.');
                    return;
                }

                if (! isWizardForm) {
                    window.crmWizardNavigationAllowed = true;
                }

                submitting = true;
                var button = event.submitter || form.querySelector('.btn-loading');

                if (actionInput && event.submitter?.dataset.wizardSubmitAction) {
                    actionInput.value = event.submitter.dataset.wizardSubmitAction;
                }

                if (! button) {
                    return;
                }

                form.querySelectorAll('button[type="submit"]').forEach(function (submitButton) {
                    submitButton.disabled = true;
                });
                button.querySelector('.btn-text')?.classList.add('d-none');
                button.querySelector('.btn-spinner')?.classList.remove('d-none');
            });

            if (isWizardForm) {
                window.addEventListener('beforeunload', function (event) {
                    if (form.dataset.dirty === 'true' && ! submitting && ! window.crmWizardNavigationAllowed) {
                        event.preventDefault();
                        event.returnValue = '';
                    }
                });
            }
        });

        document.querySelectorAll('[data-file-upload]').forEach(function (upload) {
            var input = upload.querySelector('[data-file-input]');
            var dropzone = upload.querySelector('[data-file-dropzone]');
            var fileName = upload.querySelector('[data-file-name]');

            if (! input || ! dropzone || ! fileName) {
                return;
            }

            function updateFileName() {
                var hasFile = input.files && input.files.length > 0;
                upload.classList.toggle('has-file', hasFile);
                fileName.textContent = hasFile
                    ? input.files.length === 1
                        ? input.files[0].name
                        : input.files.length + ' files selected'
                    : 'No file selected';
            }

            ['dragenter', 'dragover'].forEach(function (eventName) {
                dropzone.addEventListener(eventName, function (event) {
                    event.preventDefault();
                    upload.classList.add('is-dragover');
                });
            });

            ['dragleave', 'drop'].forEach(function (eventName) {
                dropzone.addEventListener(eventName, function (event) {
                    event.preventDefault();
                    upload.classList.remove('is-dragover');
                });
            });

            dropzone.addEventListener('drop', function (event) {
                if (! event.dataTransfer?.files?.length) {
                    return;
                }

                input.files = event.dataTransfer.files;
                updateFileName();
            });

            input.addEventListener('change', updateFileName);
            updateFileName();
        });

        document.querySelector('[data-wizard-stage-selector]')?.addEventListener('change', function (event) {
            if (event.target.value) {
                window.location.assign(event.target.value);
            }
        });

        document.addEventListener('click', function (event) {
            var addButton = event.target.closest('[data-repeatable-add]');

            if (addButton) {
                var collection = addButton.closest('[data-repeatable-collection]');
                var rows = collection?.querySelector('[data-repeatable-rows]');
                var template = collection?.querySelector('[data-repeatable-template]');

                if (collection && rows && template) {
                    var placeholder = collection.dataset.repeatablePlaceholder;
                    var nextIndex = rows.querySelectorAll(':scope > [data-repeatable-row]').length;
                    var html = template.innerHTML.split(placeholder).join(String(nextIndex));
                    rows.insertAdjacentHTML('beforeend', html);
                    updateRepeatableCollection(collection);
                    syncConditionalFields();
                    var addedRow = rows.querySelector(':scope > [data-repeatable-row]:last-child');
                    var firstControl = addedRow?.querySelector('input:not([type="hidden"]), select, textarea');
                    firstControl?.focus();
                    announceWizard('Added ' + (collection.dataset.repeatableLabel || 'item') + ' ' + (nextIndex + 1) + '.');
                }

                return;
            }

            var removeButton = event.target.closest('[data-repeatable-remove]');

            if (removeButton) {
                var row = removeButton.closest('[data-repeatable-row]');
                var collection = removeButton.closest('[data-repeatable-collection]');
                var removedLabel = row?.querySelector('[data-repeatable-row-heading]')?.textContent?.trim();
                var nextControl = row?.nextElementSibling?.querySelector('input:not([type="hidden"]), select, textarea');

                row?.remove();

                if (collection) {
                    updateRepeatableCollection(collection);
                    syncConditionalFields();
                    (nextControl || collection.querySelector('[data-repeatable-add]'))?.focus();
                }

                announceWizard('Removed ' + (removedLabel || collection?.dataset.repeatableLabel || 'item') + '.');
            }
        });

        function announceWizard(message) {
            var announcement = document.querySelector('[data-wizard-announcement]');

            if (! announcement) {
                return;
            }

            announcement.textContent = '';
            window.setTimeout(function () {
                announcement.textContent = message;
            }, 20);
        }

        function updateRepeatableCollection(collection) {
            var rows = collection.querySelector('[data-repeatable-rows]');
            var label = collection.dataset.repeatableLabel || 'Item';
            var rowItems = rows ? Array.from(rows.querySelectorAll(':scope > [data-repeatable-row]')) : [];
            var required = collection.dataset.repeatableRequired === 'true';

            rowItems.forEach(function (row, index) {
                var rowLabel = label + ' ' + (index + 1);
                var heading = row.querySelector('[data-repeatable-row-heading]');
                var removeButton = row.querySelector('[data-repeatable-remove]');
                var removeLabel = row.querySelector('[data-repeatable-remove-label]');

                if (heading) {
                    heading.textContent = rowLabel;
                }

                if (removeButton) {
                    removeButton.setAttribute('aria-label', 'Remove ' + rowLabel.toLowerCase());
                    removeButton.disabled = required && rowItems.length === 1;
                }

                if (removeLabel) {
                    removeLabel.textContent = 'Remove ' + rowLabel.toLowerCase();
                }
            });
        }

        function readConditionalValue(source) {
            if (! source) {
                return null;
            }

            if (source.type === 'checkbox') {
                return source.checked;
            }

            if (source.multiple) {
                return Array.from(source.selectedOptions).map(function (option) {
                    return option.value;
                });
            }

            return source.value;
        }

        function syncConditionalField(field) {
            var source = document.getElementById(field.dataset.conditionSource);
            var actual = readConditionalValue(source);
            var expected = JSON.parse(atob(field.dataset.conditionValue));
            var matches;

            if (field.dataset.conditionOperator === 'not_equals') {
                matches = actual !== null && actual !== '' && actual !== expected;
            } else if (Array.isArray(actual)) {
                matches = actual.indexOf(String(expected)) !== -1;
            } else {
                matches = actual === expected || String(actual) === String(expected);
            }

            field.hidden = ! matches;
            field.setAttribute('aria-hidden', matches ? 'false' : 'true');
            field.querySelectorAll('[data-conditional-required]').forEach(function (control) {
                control.required = matches;
            });
        }

        function syncConditionalFields() {
            document.querySelectorAll('[data-conditional-field]').forEach(syncConditionalField);
        }

        document.addEventListener('change', syncConditionalFields);
        document.addEventListener('DOMContentLoaded', syncConditionalFields);
        syncConditionalFields();

        document.addEventListener('invalid', function (event) {
            event.target.setAttribute('aria-invalid', 'true');
        }, true);

        document.addEventListener('input', function (event) {
            if (event.target.matches('input, select, textarea')) {
                event.target.removeAttribute('aria-invalid');
            }
        });

        document.querySelectorAll('[data-repeatable-collection]').forEach(updateRepeatableCollection);

        function focusWizardContext() {
            var errorAlert = document.querySelector('[data-wizard-error-alert]');
            var validationSummary = document.querySelector('[data-wizard-validation-summary]');
            var heading = document.querySelector('[data-wizard-main-heading]');
            var target = errorAlert || validationSummary || heading;

            if (! target) {
                return;
            }

            if (target === heading) {
                target.focus({preventScroll: true});
                announceWizard('Stage ' + heading.textContent.trim() + ' loaded.');
                return;
            }

            target.focus();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', focusWizardContext);
        } else {
            focusWizardContext();
        }
    </script>
</body>
</html>
