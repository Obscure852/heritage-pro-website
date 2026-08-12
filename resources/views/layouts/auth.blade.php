<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Secure Access') | {{ $crmBrandingSettings?->company_name ?: config('app.name', 'Heritage Pro') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --auth-primary: #232160;
            --auth-primary-dark: #1A1950;
            --auth-accent: #C08A3C;
            --auth-text: #16162B;
            --auth-muted: #55556B;
            --auth-border: #E3E3EC;
            --auth-surface: #FFFFFF;
            --auth-surface-soft: #F7F7FB;
            --auth-blue: #2563EB;
            --auth-blue-dark: #1D4ED8;
            /* Primary action colour, kept separate from --auth-primary so the
               button can be retuned without moving focus rings or link colour. */
            --auth-action: #4254C6;
            --auth-action-dark: #3746AB;
            --auth-gold: #C08A3C;
            --auth-gold-ink: #96631E;
            --auth-hairline: #EBEBF3;
            --auth-serif: 'Source Serif 4', Georgia, 'Times New Roman', serif;
            --auth-sans: Archivo, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --auth-stage-padding: clamp(16px, 2.4vw, 32px);
            --auth-shell-width: 980px;
            /* Sits just above the tallest panel content (reset ≈ 475px) so the
               sign-in and reset cards resolve to an identical height. */
            --auth-shell-min-height: 480px;
        }

        * {
            box-sizing: border-box;
        }

        .auth-visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            margin: -1px;
            padding: 0;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: var(--auth-sans);
            color: var(--auth-text);
            -webkit-font-smoothing: antialiased;
            background: #FFFFFF;
        }

        .auth-stage {
            min-height: 100svh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--auth-stage-padding);
        }

        .auth-shell {
            display: flex;
            align-items: stretch;
            flex-direction: row-reverse;
            width: min(100%, var(--auth-shell-width));
            min-height: min(var(--auth-shell-min-height), calc(100svh - (var(--auth-stage-padding) + var(--auth-stage-padding))));
            background: var(--auth-surface);
            border: 1px solid var(--auth-border);
            border-radius: 10px;
            overflow: hidden;
            /* On a white page the card is defined by its hairline and a soft
               lift rather than by contrast with the background. */
            box-shadow: 0 28px 60px -38px rgba(22, 22, 43, 0.34);
        }

        .auth-shell.auth-shell-no-media {
            flex-direction: row;
            width: min(100%, 1120px);
            min-height: auto;
        }

        .auth-panel {
            flex: 0 0 44%;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 0;
            /* Vertical padding stays tight so the card fits a laptop viewport;
               horizontal padding keeps the composed, generous feel. */
            padding: clamp(22px, 2.2vw, 32px) clamp(26px, 3.2vw, 46px);
            background: var(--auth-surface);
            animation: authPanelIn 0.45s ease-out;
        }

        .auth-shell.auth-shell-no-media .auth-panel {
            flex: 1 1 auto;
            align-items: flex-start;
            padding: clamp(20px, 3vw, 28px);
        }

        .auth-panel-inner {
            width: min(100%, 384px);
            margin-inline: auto;
        }

        .auth-shell.auth-shell-no-media .auth-panel-inner {
            width: min(100%, 980px);
        }

        .auth-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 22px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--auth-hairline);
        }

        .auth-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: inherit;
            text-decoration: none;
        }

        .auth-brand:hover {
            color: inherit;
        }

        .auth-brand img {
            width: 38px;
            height: 38px;
            border-radius: 4px;
            object-fit: cover;
        }

        .auth-brand-copy {
            display: grid;
            gap: 2px;
        }

        .auth-brand-copy strong {
            font-family: var(--auth-serif);
            font-size: 18px;
            font-weight: 600;
            letter-spacing: -0.015em;
            line-height: 1.1;
        }

        .auth-brand-copy span {
            color: var(--auth-muted);
            font-size: 10px;
            font-weight: 500;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .auth-toolbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .auth-ghost-button {
            padding: 8px 12px;
            border-radius: 3px;
            border: 1px solid rgba(35, 33, 96, 0.35);
            background: rgba(255, 255, 255, 0.8);
            color: var(--auth-text);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.18s ease;
        }

        .auth-ghost-button:hover {
            color: var(--auth-text);
            border-color: rgba(35, 33, 96, 0.26);
            box-shadow: 0 10px 24px rgba(22, 22, 43, 0.08);
            transform: translateY(-1px);
        }

        /* Gold tracked label with a short rule — the same signature the marketing
           site uses above each section heading. */
        .auth-kicker {
            display: flex;
            align-items: center;
            gap: 12px;
            width: fit-content;
            margin: 0 0 12px;
            padding: 0;
            border: 0;
            background: none;
            color: var(--auth-gold-ink);
            font-size: 10.5px;
            font-weight: 500;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .auth-kicker::before {
            content: '';
            width: 22px;
            height: 1px;
            background: var(--auth-gold);
        }

        .auth-heading {
            margin: 0;
            font-family: var(--auth-serif);
            font-size: clamp(25px, 2.6vw, 33px);
            font-weight: 400;
            line-height: 1.12;
            letter-spacing: -0.022em;
            text-wrap: pretty;
        }

        .auth-copy {
            margin: 10px 0 0;
            color: var(--auth-muted);
            font-size: 13.5px;
            line-height: 1.6;
        }

        .auth-shell.auth-shell-no-media .auth-copy {
            max-width: 560px;
        }

        .auth-progress {
            display: grid;
            gap: 10px;
            margin-bottom: 18px;
        }

        .auth-shell.auth-shell-no-media .auth-progress {
            justify-items: center;
        }

        .auth-progress-track {
            width: min(100%, 360px);
            height: 8px;
            border-radius: 999px;
            background: rgba(35, 33, 96, 0.18);
            overflow: hidden;
        }

        .auth-progress-bar {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(135deg, #2E2B7A 0%, #232160 100%);
        }

        .auth-progress-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .auth-shell.auth-shell-no-media .auth-progress-pills {
            justify-content: center;
        }

        .auth-progress-step {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            border-radius: 999px;
            background: rgba(226, 232, 240, 0.6);
            color: var(--auth-muted);
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .auth-progress-step.is-current {
            background: linear-gradient(135deg, rgba(35, 33, 96, 0.16), rgba(192, 138, 60, 0.2));
            color: var(--auth-primary-dark);
        }

        .auth-progress-step.is-complete {
            background: rgba(35, 33, 96, 0.12);
            color: var(--auth-primary-dark);
        }

        .auth-helper {
            margin: 26px 0 22px;
            padding: 14px 16px;
            border-left: 4px solid var(--auth-primary);
            border-radius: 0 3px 3px 0;
            background: #F1F1F8;
        }

        .auth-helper strong {
            display: block;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .auth-helper span {
            display: block;
            color: var(--auth-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .auth-alert {
            margin-top: 24px;
            margin-bottom: 0;
            padding: 13px 16px;
            border-radius: 4px;
            border: 1px solid transparent;
            font-size: 13px;
            line-height: 1.6;
        }

        .auth-alert.success {
            color: #1F5B3E;
            background: #EEF7F2;
            border-color: rgba(31, 91, 62, 0.2);
        }

        .auth-alert.error {
            color: #8C1D33;
            background: #FBF0F2;
            border-color: rgba(164, 36, 59, 0.22);
        }

        .auth-form {
            display: grid;
            gap: 14px;
            margin-top: 22px;
        }

        .auth-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .auth-field.full {
            grid-column: 1 / -1;
        }

        .auth-field label {
            display: inline-block;
            margin-bottom: 6px;
            font-size: 10.5px;
            font-weight: 500;
            letter-spacing: 0.13em;
            text-transform: uppercase;
            color: var(--auth-muted);
        }

        .auth-required {
            color: var(--auth-gold);
        }

        /* Label on the left, an inline helper link (e.g. reset password) on the
           right, sharing the same baseline above the input. */
        .auth-field-header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
        }

        .auth-field-header label {
            margin-bottom: 6px;
        }

        .auth-field-link {
            margin-bottom: 6px;
            color: var(--auth-blue);
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            border-bottom: 1px solid transparent;
            transition: color 0.18s ease, border-color 0.18s ease;
        }

        .auth-field-link:hover {
            color: var(--auth-blue-dark);
            text-decoration: none;
            border-bottom-color: var(--auth-blue-dark);
        }

        .auth-field .form-control,
        .auth-field .form-select {
            min-height: 44px;
            border-radius: 4px;
            border: 1px solid var(--auth-border);
            padding: 10px 14px;
            font-size: 14px;
            color: var(--auth-text);
            background: #FFFFFF;
            transition: border-color 0.16s ease, box-shadow 0.16s ease;
        }

        .auth-field .form-control::placeholder,
        .auth-field .form-select::placeholder {
            color: #9C9CB2;
        }

        .auth-input-shell {
            position: relative;
        }

        .auth-input-shell .form-control {
            padding-right: 44px;
        }

        .auth-input-action {
            position: absolute;
            top: 50%;
            right: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            padding: 0;
            border: 0;
            background: transparent;
            color: #55556B;
            transform: translateY(-50%);
            cursor: pointer;
            transition: color 0.18s ease;
        }

        .auth-input-action:hover {
            color: var(--auth-primary-dark);
        }

        .auth-input-action:focus-visible {
            outline: none;
            color: var(--auth-primary-dark);
        }

        .auth-input-action svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
        }

        .auth-input-action .icon-eye-off {
            display: none;
        }

        .auth-input-action.is-visible .icon-eye {
            display: none;
        }

        .auth-input-action.is-visible .icon-eye-off {
            display: block;
        }

        .auth-field .form-control:focus,
        .auth-field .form-select:focus {
            border-color: var(--auth-primary);
            box-shadow: 0 0 0 3px rgba(35, 33, 96, 0.12);
        }

        .auth-field .form-control.is-invalid,
        .auth-field .form-select.is-invalid {
            border-color: rgba(164, 36, 59, 0.45);
            background-image: none;
            padding-right: 14px;
        }

        .auth-field .form-control.is-invalid:focus,
        .auth-field .form-select.is-invalid:focus {
            border-color: #A4243B;
            box-shadow: 0 0 0 3px rgba(164, 36, 59, 0.1);
        }

        .auth-field .invalid-feedback {
            display: block;
            margin-top: 7px;
            color: #A4243B;
            font-size: 12.5px;
            line-height: 1.5;
        }

        .auth-submit {
            width: 100%;
            justify-content: center;
            min-height: 46px;
            padding: 11px 24px;
            border: 0;
            border-radius: 999px;
            background: var(--auth-action);
            color: #FFFFFF;
            font-size: 14.5px;
            font-weight: 600;
            letter-spacing: 0.005em;
            transition: background-color 0.18s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .auth-submit:hover {
            background: var(--auth-action-dark);
            color: #FFFFFF;
        }

        .auth-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .auth-link-stack {
            display: grid;
            gap: 12px;
        }

        .auth-link-stack-centered {
            justify-items: center;
            text-align: center;
        }

        .auth-link {
            color: var(--auth-primary);
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            border-bottom: 1px solid #C4C4D8;
        }

        .auth-link:hover {
            color: var(--auth-primary);
            text-decoration: none;
            border-bottom-color: var(--auth-primary);
        }

        .auth-meta {
            color: var(--auth-muted);
            font-size: 12.5px;
            line-height: 1.6;
        }

        .auth-link-stack-centered .auth-meta {
            max-width: 280px;
        }

        .auth-onboarding-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 260px;
            gap: 24px;
            align-items: start;
        }

        .auth-onboarding-main,
        .auth-onboarding-sidebar {
            min-width: 0;
        }

        .auth-onboarding-sidebar .auth-avatar-panel {
            align-content: start;
            height: 100%;
        }

        .auth-onboarding-sidebar .auth-avatar-shell {
            width: min(100%, 190px);
        }

        .auth-form-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding-top: 4px;
        }

        .auth-form-footer-copy {
            min-width: 0;
        }

        .auth-form-footer-copy .auth-meta,
        .auth-form-footer-copy .auth-link {
            margin: 0;
        }

        .auth-form-footer-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
        }

        .auth-form-footer-actions form {
            margin: 0;
        }

        .auth-form-footer-actions .auth-submit {
            width: auto;
            min-width: 190px;
        }

        .auth-form-footer-actions .auth-ghost-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 110px;
            min-height: 40px;
        }

        .auth-submit-icon {
            width: 14px;
            height: 14px;
            stroke: currentColor;
        }

        .auth-avatar-panel {
            display: grid;
            gap: 16px;
            padding: 18px;
            border: 1px solid var(--auth-border);
            border-radius: 3px;
            background: var(--auth-surface-soft);
        }

        .auth-avatar-copy {
            display: grid;
            gap: 4px;
        }

        .auth-avatar-copy strong {
            font-size: 15px;
        }

        .auth-avatar-copy span {
            color: var(--auth-muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .auth-avatar-shell {
            position: relative;
            width: min(100%, 180px);
            aspect-ratio: 1 / 1;
            margin: 0 auto;
            border-radius: 3px;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(35, 33, 96, 0.14), rgba(192, 138, 60, 0.2));
            box-shadow: 0 20px 38px rgba(22, 22, 43, 0.08);
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .auth-avatar-shell:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 40px rgba(35, 33, 96, 0.14);
        }

        .auth-avatar-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .auth-avatar-placeholder {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 18px;
            text-align: center;
        }

        .auth-avatar-placeholder strong {
            font-size: 28px;
            line-height: 1;
        }

        .auth-avatar-placeholder span {
            color: var(--auth-muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .auth-avatar-upload-icon {
            width: 28px;
            height: 28px;
            stroke: var(--auth-primary-dark);
        }

        .auth-avatar-hint {
            text-align: center;
            color: var(--auth-muted);
            font-size: 12px;
        }

        .auth-file-input {
            display: none;
        }

        .auth-media {
            position: relative;
            flex: 1 1 56%;
            min-width: 0;
            overflow: hidden;
            background-color: #1A1950;
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
        }

        /* Navy scrim rather than a neutral grey one, so the photograph sits in the
           brand rather than beside it. */
        .auth-media-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: flex-end;
            padding: clamp(28px, 3.4vw, 46px);
            background:
                linear-gradient(180deg, rgba(35, 33, 96, 0.12) 0%, rgba(16, 16, 36, 0.55) 46%, rgba(12, 12, 28, 0.92) 100%);
        }

        .auth-media-card {
            max-width: 400px;
            color: #FAFAFD;
            animation: authPanelIn 0.55s ease-out;
        }

        .auth-media-card p {
            margin: 0;
        }

        .auth-media-kicker {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
            font-size: 10.5px;
            font-weight: 500;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #EBC891;
        }

        .auth-media-kicker::before {
            content: '';
            width: 22px;
            height: 1px;
            background: var(--auth-gold);
        }

        .auth-media-heading {
            margin: 0 0 14px;
            font-family: var(--auth-serif);
            font-size: clamp(27px, 3vw, 38px);
            font-weight: 400;
            line-height: 1.12;
            letter-spacing: -0.022em;
            text-wrap: pretty;
        }

        .auth-media-copy {
            max-width: 40ch;
            color: rgba(250, 250, 253, 0.82);
            font-size: 14px;
            line-height: 1.62;
        }

        .auth-cropper-backdrop[hidden] {
            display: none;
        }

        .auth-cropper-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1055;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(22, 22, 43, 0.72);
        }

        .auth-cropper-modal {
            width: min(100%, 860px);
            border-radius: 3px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 28px 58px rgba(22, 22, 43, 0.24);
        }

        .auth-cropper-header,
        .auth-cropper-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 24px;
            border-bottom: 1px solid #EBEBF3;
        }

        .auth-cropper-footer {
            border-bottom: 0;
            border-top: 1px solid #EBEBF3;
        }

        .auth-cropper-footer .auth-submit {
            width: auto;
        }

        .auth-cropper-header h3 {
            margin: 0 0 6px;
            font-size: 20px;
        }

        .auth-cropper-header p,
        .auth-cropper-note {
            margin: 0;
            color: var(--auth-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .auth-cropper-body {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 240px;
            gap: 24px;
            padding: 24px;
        }

        .auth-cropper-canvas-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 360px;
            border-radius: 3px;
            background: linear-gradient(135deg, rgba(35, 33, 96, 0.08), rgba(192, 138, 60, 0.14));
        }

        .auth-cropper-canvas {
            width: 100%;
            max-width: 360px;
            height: auto;
            border-radius: 3px;
            cursor: grab;
        }

        .auth-cropper-canvas.is-dragging {
            cursor: grabbing;
        }

        .auth-cropper-controls {
            display: grid;
            gap: 16px;
        }

        .auth-cropper-controls label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #16162B;
        }

        @keyframes authPanelIn {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Short laptop viewports: tighten the vertical rhythm further so the card
           still fits on screen rather than forcing the page to scroll. */
        @media (min-width: 992px) and (max-height: 760px) {
            :root {
                --auth-stage-padding: 16px;
            }

            .auth-panel {
                padding-top: 20px;
                padding-bottom: 20px;
            }

            .auth-toolbar {
                margin-bottom: 16px;
                padding-bottom: 12px;
            }

            .auth-copy {
                margin-top: 8px;
            }

            .auth-form {
                margin-top: 16px;
                gap: 12px;
            }
        }

        @media (max-width: 991.98px) {
            .auth-stage {
                display: block;
                padding: 16px;
            }

            .auth-shell {
                display: flex;
                flex-direction: column-reverse;
                width: min(100%, 720px);
                margin: 0 auto;
            }

            .auth-shell.auth-shell-no-media {
                display: block;
                width: min(100%, 720px);
            }

            .auth-panel {
                min-height: auto;
                display: block;
                width: 100%;
                padding: 24px 20px 28px;
            }

            .auth-panel-inner {
                width: min(100%, 430px);
            }

            .auth-shell.auth-shell-no-media .auth-panel-inner {
                width: 100%;
            }

            .auth-media {
                min-height: 280px;
                width: 100%;
            }

            .auth-media-overlay {
                align-items: flex-start;
                padding-top: 96px;
            }

            .auth-onboarding-layout {
                grid-template-columns: 1fr;
            }

            .auth-form-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .auth-form-footer-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .auth-form-footer-actions form {
                width: 100%;
            }

            .auth-form-footer-actions .auth-submit,
            .auth-form-footer-actions .auth-ghost-button {
                width: 100%;
            }
        }

        @media (max-width: 767.98px) {
            .auth-toolbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .auth-form-grid,
            .auth-cropper-body {
                grid-template-columns: 1fr;
            }

            .auth-cropper-header,
            .auth-cropper-footer {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
    @stack('head')
</head>
    <body>
    @php
        $companyName = $crmBrandingSettings?->company_name ?: config('app.name', 'Heritage Pro');
        $companyLogoUrl = $crmBrandingSettings?->company_logo_url ?: asset('assets/images/heritage-logo.png');
        $loginImageUrl = $crmBrandingSettings?->login_image_url ?: asset('assets/images/login-page-image.jpg');
        $hideAuthMedia = trim((string) $__env->yieldContent('auth_hide_media')) === '1';
        $hideAuthHeading = trim((string) $__env->yieldContent('auth_hide_heading')) === '1';
    @endphp
    <div class="auth-stage">
        <div class="auth-shell{{ $hideAuthMedia ? ' auth-shell-no-media' : '' }}">
            <section class="auth-panel">
                <div class="auth-panel-inner">
                    <div class="auth-toolbar">
                        <a href="{{ route('website.home') }}" class="auth-brand">
                            <img src="{{ $companyLogoUrl }}" alt="{{ $companyName }}">
                            <span class="auth-brand-copy">
                                <strong>{{ $companyName }}</strong>
                                <span>CRM access</span>
                            </span>
                        </a>

                        @auth
                            <div class="auth-toolbar-actions">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="auth-ghost-button">Log out</button>
                                </form>
                            </div>
                        @endauth
                    </div>

                    @hasSection('auth_progress')
                        <div class="auth-progress">
                            @yield('auth_progress')
                        </div>
                    @endif

                    <p class="auth-kicker">@yield('auth_kicker', 'Secure Access')</p>

                    {{-- Screens that only need the fields hide the heading visually
                         while keeping it in the document outline. --}}
                    <h1 @class(['auth-heading', 'auth-visually-hidden' => $hideAuthHeading])>@yield('auth_heading')</h1>

                    @hasSection('auth_copy')
                        <p class="auth-copy">@yield('auth_copy')</p>
                    @endif

                    @hasSection('auth_helper')
                        @yield('auth_helper')
                    @endif

                    @if (session('status'))
                        <div class="auth-alert success" role="alert">{{ session('status') }}</div>
                    @endif

                    @if (session('crm_success'))
                        <div class="auth-alert success" role="alert">{{ session('crm_success') }}</div>
                    @endif

                    @if (session('crm_error'))
                        <div class="auth-alert error" role="alert">{{ session('crm_error') }}</div>
                    @endif

                    @if ($errors->has('email') && ! $__env->hasSection('auth_inline_email_errors'))
                        <div class="auth-alert error" role="alert">{{ $errors->first('email') }}</div>
                    @endif

                    @yield('auth_content')
                </div>
            </section>

            @unless ($hideAuthMedia)
                <aside class="auth-media" style="background-image: url('{{ $loginImageUrl }}');">
                    <div class="auth-media-overlay">
                        <div class="auth-media-card">
                            <p class="auth-media-kicker">@yield('auth_media_kicker', 'Internal Workspace')</p>
                            <h2 class="auth-media-heading">@yield('auth_media_heading', $companyName)</h2>
                            <p class="auth-media-copy">@yield('auth_media_copy', 'Secure access for staff accounts across the CRM workspace, user directory, product modules, and customer operations.')</p>
                        </div>
                    </div>
                </aside>
            @endunless
        </div>
    </div>

    <div id="auth-image-cropper-modal" class="auth-cropper-backdrop" hidden>
        <div class="auth-cropper-modal" role="dialog" aria-modal="true" aria-labelledby="auth-image-cropper-title">
            <div class="auth-cropper-header">
                <div>
                    <h3 id="auth-image-cropper-title">Crop image</h3>
                    <p>Use the square cropper to frame the image before saving it.</p>
                </div>
                <button type="button" class="auth-ghost-button" id="auth-image-cropper-cancel-top">Close</button>
            </div>

            <div class="auth-cropper-body">
                <div class="auth-cropper-canvas-wrap">
                    <canvas id="auth-image-cropper-canvas" class="auth-cropper-canvas" width="360" height="360"></canvas>
                </div>

                <div class="auth-cropper-controls">
                    <div>
                        <label for="auth-image-cropper-zoom">Zoom</label>
                        <input id="auth-image-cropper-zoom" type="range" min="1" max="3" step="0.01" value="1" class="form-range">
                    </div>
                    <div class="auth-helper">
                        <strong>Drag to reposition</strong>
                        <span>Move the image inside the square and adjust the zoom until the framing looks right.</span>
                    </div>
                    <p class="auth-cropper-note" id="auth-image-cropper-note">The image is saved as a square image for consistent display across CRM.</p>
                </div>
            </div>

            <div class="auth-cropper-footer">
                <button type="button" class="auth-ghost-button" id="auth-image-cropper-cancel-bottom">Cancel</button>
                <button type="button" class="auth-submit" id="auth-image-cropper-apply">
                    <span class="btn-text">Apply crop</span>
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    var submitBtn = event.submitter;

                    if (!submitBtn || !submitBtn.classList.contains('btn-loading')) {
                        submitBtn = form.querySelector('button[type="submit"].btn-loading');

                        if (!submitBtn && form.id) {
                            submitBtn = document.querySelector('button[type="submit"].btn-loading[form="' + form.id + '"]');
                        }
                    }

                    if (submitBtn) {
                        if (submitBtn.classList.contains('loading')) {
                            event.preventDefault();
                            return;
                        }

                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                        submitBtn.setAttribute('aria-busy', 'true');
                    }
                });
            });

            document.querySelectorAll('[data-password-toggle]').forEach(function (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    var targetId = toggleBtn.getAttribute('data-password-target');
                    var targetInput = targetId ? document.getElementById(targetId) : null;

                    if (!targetInput) {
                        return;
                    }

                    var isVisible = targetInput.type === 'text';
                    targetInput.type = isVisible ? 'password' : 'text';
                    toggleBtn.classList.toggle('is-visible', !isVisible);
                    toggleBtn.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
                    toggleBtn.setAttribute('title', isVisible ? 'Show password' : 'Hide password');
                });
            });

            var cropperModal = document.getElementById('auth-image-cropper-modal');
            var cropperCanvas = document.getElementById('auth-image-cropper-canvas');
            var cropperContext = cropperCanvas ? cropperCanvas.getContext('2d') : null;
            var cropperZoom = document.getElementById('auth-image-cropper-zoom');
            var cropperApply = document.getElementById('auth-image-cropper-apply');
            var cropperCancelTop = document.getElementById('auth-image-cropper-cancel-top');
            var cropperCancelBottom = document.getElementById('auth-image-cropper-cancel-bottom');
            var cropperTitle = document.getElementById('auth-image-cropper-title');
            var cropperNote = document.getElementById('auth-image-cropper-note');
            var cropperState = {
                image: null,
                scale: 1,
                minScale: 1,
                x: 0,
                y: 0,
                dragging: false,
                startX: 0,
                startY: 0,
                originX: 0,
                originY: 0,
                targetInput: null,
                previewImage: null,
                fallbackTarget: null,
                fileInput: null,
                objectUrl: null
            };

            function closeCropper() {
                if (!cropperModal) {
                    return;
                }

                cropperModal.hidden = true;

                if (cropperState.fileInput) {
                    cropperState.fileInput.value = '';
                }

                if (cropperState.objectUrl) {
                    URL.revokeObjectURL(cropperState.objectUrl);
                }

                cropperState.image = null;
                cropperState.targetInput = null;
                cropperState.previewImage = null;
                cropperState.fallbackTarget = null;
                cropperState.fileInput = null;
                cropperState.objectUrl = null;
                cropperState.dragging = false;
            }

            function renderCropperCanvas() {
                if (!cropperContext || !cropperState.image) {
                    return;
                }

                var minX = cropperCanvas.width - (cropperState.image.width * cropperState.scale);
                var minY = cropperCanvas.height - (cropperState.image.height * cropperState.scale);

                cropperState.x = Math.min(0, Math.max(minX, cropperState.x));
                cropperState.y = Math.min(0, Math.max(minY, cropperState.y));

                cropperContext.clearRect(0, 0, cropperCanvas.width, cropperCanvas.height);
                cropperContext.fillStyle = '#dbeafe';
                cropperContext.fillRect(0, 0, cropperCanvas.width, cropperCanvas.height);
                cropperContext.drawImage(
                    cropperState.image,
                    cropperState.x,
                    cropperState.y,
                    cropperState.image.width * cropperState.scale,
                    cropperState.image.height * cropperState.scale
                );
            }

            function startCropper(fileInput, file) {
                if (!cropperModal || !cropperCanvas || !cropperZoom || !file) {
                    return;
                }

                var hiddenTargetId = fileInput.getAttribute('data-cropper-hidden-target');
                var previewTargetId = fileInput.getAttribute('data-cropper-preview-target');
                var fallbackTargetId = fileInput.getAttribute('data-cropper-fallback-target');
                var cropperTitleText = fileInput.getAttribute('data-cropper-title') || 'Crop image';
                var cropperNoteText = fileInput.getAttribute('data-cropper-note') || 'The image is saved as a square image for consistent display across CRM.';
                var targetInput = hiddenTargetId ? document.getElementById(hiddenTargetId) : null;
                var previewImage = previewTargetId ? document.getElementById(previewTargetId) : null;
                var fallbackTarget = fallbackTargetId ? document.getElementById(fallbackTargetId) : null;

                if (!targetInput || !previewImage) {
                    fileInput.value = '';
                    return;
                }

                var objectUrl = URL.createObjectURL(file);
                var image = new Image();

                image.onload = function () {
                    cropperState.image = image;
                    cropperState.fileInput = fileInput;
                    cropperState.targetInput = targetInput;
                    cropperState.previewImage = previewImage;
                    cropperState.fallbackTarget = fallbackTarget;
                    cropperState.objectUrl = objectUrl;
                    cropperState.minScale = Math.max(
                        cropperCanvas.width / image.width,
                        cropperCanvas.height / image.height
                    );
                    cropperState.scale = cropperState.minScale;
                    cropperState.x = (cropperCanvas.width - (image.width * cropperState.scale)) / 2;
                    cropperState.y = (cropperCanvas.height - (image.height * cropperState.scale)) / 2;

                    cropperZoom.min = cropperState.minScale.toFixed(2);
                    cropperZoom.max = Math.max(cropperState.minScale * 3, cropperState.minScale + 0.5).toFixed(2);
                    cropperZoom.step = '0.01';
                    cropperZoom.value = cropperState.scale.toFixed(2);

                    if (cropperTitle) {
                        cropperTitle.textContent = cropperTitleText;
                    }

                    if (cropperNote) {
                        cropperNote.textContent = cropperNoteText;
                    }

                    cropperModal.hidden = false;
                    renderCropperCanvas();
                };

                image.src = objectUrl;
            }

            document.querySelectorAll('[data-cropper-input]').forEach(function (input) {
                input.addEventListener('change', function () {
                    if (!input.files || !input.files[0]) {
                        return;
                    }

                    startCropper(input, input.files[0]);
                });
            });

            if (cropperZoom) {
                cropperZoom.addEventListener('input', function () {
                    if (!cropperState.image) {
                        return;
                    }

                    var newScale = parseFloat(cropperZoom.value || cropperState.minScale);
                    var centerX = cropperCanvas.width / 2;
                    var centerY = cropperCanvas.height / 2;
                    var ratio = newScale / cropperState.scale;

                    cropperState.x = centerX - ((centerX - cropperState.x) * ratio);
                    cropperState.y = centerY - ((centerY - cropperState.y) * ratio);
                    cropperState.scale = newScale;
                    renderCropperCanvas();
                });
            }

            function dragPosition(event) {
                if (!cropperState.dragging) {
                    return;
                }

                var point = event.touches ? event.touches[0] : event;
                cropperState.x = cropperState.originX + (point.clientX - cropperState.startX);
                cropperState.y = cropperState.originY + (point.clientY - cropperState.startY);
                renderCropperCanvas();
            }

            if (cropperCanvas) {
                ['mousedown', 'touchstart'].forEach(function (eventName) {
                    cropperCanvas.addEventListener(eventName, function (event) {
                        if (!cropperState.image) {
                            return;
                        }

                        var point = event.touches ? event.touches[0] : event;
                        cropperState.dragging = true;
                        cropperState.startX = point.clientX;
                        cropperState.startY = point.clientY;
                        cropperState.originX = cropperState.x;
                        cropperState.originY = cropperState.y;
                        cropperCanvas.classList.add('is-dragging');
                    });
                });
            }

            ['mousemove', 'touchmove'].forEach(function (eventName) {
                document.addEventListener(eventName, dragPosition, { passive: false });
            });

            ['mouseup', 'mouseleave', 'touchend', 'touchcancel'].forEach(function (eventName) {
                document.addEventListener(eventName, function () {
                    cropperState.dragging = false;

                    if (cropperCanvas) {
                        cropperCanvas.classList.remove('is-dragging');
                    }
                });
            });

            if (cropperApply) {
                cropperApply.addEventListener('click', function () {
                    if (!cropperState.targetInput || !cropperState.previewImage) {
                        closeCropper();
                        return;
                    }

                    var dataUrl = cropperCanvas.toDataURL('image/png');
                    cropperState.targetInput.value = dataUrl;
                    cropperState.previewImage.src = dataUrl;
                    cropperState.previewImage.classList.remove('d-none');

                    if (cropperState.fallbackTarget) {
                        cropperState.fallbackTarget.classList.add('d-none');
                    }

                    closeCropper();
                });
            }

            [cropperCancelTop, cropperCancelBottom].forEach(function (button) {
                if (!button) {
                    return;
                }

                button.addEventListener('click', function () {
                    closeCropper();
                });
            });
        });
    </script>
</body>
</html>
