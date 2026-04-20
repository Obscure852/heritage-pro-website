<style>
    /* ============================================================
       Schemes of Work — Shared Styles
       Included via @@include('schemes.partials.schemes-styles')
       ============================================================ */

    /* --- Animations --- */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    @keyframes checkPop {
        0% { transform: scale(0); opacity: 0; }
        60% { transform: scale(1.2); }
        100% { transform: scale(1); opacity: 1; }
    }

    .animate-in {
        animation: fadeInUp 0.4s cubic-bezier(0.4, 0, 0.2, 1) both;
        animation-delay: calc(var(--i, 0) * 60ms);
    }

    /* --- Layout Containers --- */
    .schemes-container,
    .syllabi-container {
        background: white;
        border-radius: 6px;
        padding: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06), 0 4px 16px rgba(0, 0, 0, 0.04);
        border: 1px solid #e5e7eb;
    }

    .header,
    .schemes-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 28px;
        border-radius: 5px 5px 0 0;
    }

    .form-container {
        background: white;
        border-radius: 3px;
        padding: 32px;
    }

    .schemes-body {
        padding: 24px;
    }

    /* --- Buttons --- */
    .btn-outline-white {
        border: 1px solid rgba(255, 255, 255, 0.8);
        color: white;
        background: transparent;
        padding: 8px 16px;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-outline-white:hover {
        background: rgba(255, 255, 255, 0.15);
        color: white;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        padding: 10px 20px;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }

    .btn-primary:active {
        transform: translateY(0);
        box-shadow: 0 1px 3px rgba(59, 130, 246, 0.2);
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

    .btn-action {
        padding: 5px 12px;
        font-size: 13px;
        border-radius: 3px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
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
        font-size: 14px;
    }

    /* --- Help Text --- */
    .help-text {
        background: #f8fafc;
        padding: 14px 18px;
        border-left: 3px solid #3b82f6;
        border-radius: 0 6px 6px 0;
        margin-bottom: 20px;
    }

    .help-text .help-title {
        font-weight: 700;
        color: #374151;
        margin-bottom: 4px;
        font-size: 13.5px;
    }

    .help-text .help-content {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
    }

    /* --- Section Title with Left Accent Bar --- */
    .section-title {
        font-size: 15px;
        font-weight: 700;
        margin: 24px 0 16px 0;
        color: #1f2937;
        padding-bottom: 10px;
        padding-left: 14px;
        border-bottom: 1px solid #e5e7eb;
        border-left: 3px solid transparent;
        border-image: linear-gradient(135deg, #4e73df, #36b9cc) 1;
        border-image-slice: 0 0 0 1;
        letter-spacing: -0.01em;
    }

    /* --- Forms --- */
    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
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
    .form-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-control.is-invalid {
        border-color: #dc3545;
    }

    .invalid-feedback {
        color: #dc3545;
        font-size: 12px;
        margin-top: 3px;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: space-between;
        padding-top: 24px;
        border-top: 1px solid #f3f4f6;
        margin-top: 32px;
    }

    /* --- Stat Cards (Elevated) --- */
    .stat-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #e5e7eb;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        border-color: #d1d5db;
    }

    .stat-card .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        font-size: 20px;
    }

    .stat-card .stat-number {
        font-size: 32px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 6px;
    }

    .stat-card .stat-label {
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
    }

    /* Stat card semantic top borders */
    .stat-card.stat-primary::before { background: linear-gradient(135deg, #4e73df, #3b82f6); }
    .stat-card.stat-success::before { background: #22c55e; }
    .stat-card.stat-warning::before { background: #f59e0b; }
    .stat-card.stat-danger::before { background: #ef4444; }
    .stat-card.stat-info::before { background: #06b6d4; }
    .stat-card.stat-secondary::before { background: #6b7280; }
    .stat-card.stat-dark::before { background: #1f2937; }

    /* Stat card icon backgrounds */
    .stat-card .stat-icon.icon-primary { background: rgba(78, 115, 223, 0.1); }
    .stat-card .stat-icon.icon-success { background: rgba(34, 197, 94, 0.1); }
    .stat-card .stat-icon.icon-warning { background: rgba(245, 158, 11, 0.1); }
    .stat-card .stat-icon.icon-danger { background: rgba(239, 68, 68, 0.1); }
    .stat-card .stat-icon.icon-info { background: rgba(6, 182, 212, 0.1); }
    .stat-card .stat-icon.icon-secondary { background: rgba(107, 114, 128, 0.1); }
    .stat-card .stat-icon.icon-dark { background: rgba(31, 41, 55, 0.1); }

    /* --- Tables --- */
    .scheme-table th,
    .missing-table th {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: #6b7280;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e5e7eb;
        padding: 10px 12px;
    }

    .scheme-table td,
    .missing-table td {
        padding: 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
        color: #374151;
        transition: background 0.15s ease;
    }

    .scheme-table tr:nth-child(even) td,
    .missing-table tr:nth-child(even) td {
        background: #fafbfc;
    }

    .scheme-table tr:last-child td,
    .missing-table tr:last-child td {
        border-bottom: none;
    }

    .scheme-table tr:hover td,
    .missing-table tr:hover td {
        background: #f0f4ff;
    }

    /* Table row status left borders */
    .scheme-table tr.row-draft td:first-child { border-left: 3px solid #6b7280; }
    .scheme-table tr.row-submitted td:first-child { border-left: 3px solid #3b82f6; }
    .scheme-table tr.row-under_review td:first-child { border-left: 3px solid #f59e0b; }
    .scheme-table tr.row-approved td:first-child { border-left: 3px solid #22c55e; }
    .scheme-table tr.row-revision_required td:first-child { border-left: 3px solid #ef4444; }
    .scheme-table tr.row-supervisor_reviewed td:first-child { border-left: 3px solid #6366f1; }
    .missing-table tr td:first-child { border-left: 3px solid #ef4444; }

    .table thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
    }

    .table tbody tr:hover {
        background-color: #f0f4ff;
    }

    /* --- Status Badges (Translucent Pills) --- */
    .status-badge,
    .status-pill {
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
        text-transform: capitalize;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        letter-spacing: 0.02em;
    }

    .status-draft { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
    .status-submitted { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
    .status-under_review { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .status-approved { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .status-revision_required { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .status-supervisor_reviewed { background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }
    .status-planned { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
    .status-taught { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }

    /* Status dot indicator */
    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }

    .status-dot.dot-draft { background: #6b7280; }
    .status-dot.dot-submitted { background: #3b82f6; animation: pulse-dot 2s ease-in-out infinite; }
    .status-dot.dot-under_review { background: #f59e0b; animation: pulse-dot 2s ease-in-out infinite; }
    .status-dot.dot-approved { background: #22c55e; }
    .status-dot.dot-revision_required { background: #ef4444; }
    .status-dot.dot-supervisor_reviewed { background: #6366f1; }

    /* Syllabi badges */
    .badge-active {
        background: #d1fae5;
        color: #065f46;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-inactive {
        background: #f3f4f6;
        color: #4b5563;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    /* --- Empty States (Enhanced) --- */
    .empty-state,
    .placeholder-message {
        text-align: center;
        padding: 48px 20px;
        color: #6b7280;
    }

    .empty-state i,
    .placeholder-message i {
        width: 72px;
        height: 72px;
        background: #f0f4ff;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        opacity: 0.7;
        margin-bottom: 16px;
        color: #4e73df;
        animation: float 3s ease-in-out infinite;
    }

    .empty-state p,
    .placeholder-message p {
        font-size: 15px;
        margin: 0 0 16px 0;
        font-weight: 500;
        color: #4b5563;
    }

    .empty-state .btn,
    .placeholder-message .btn {
        margin-top: 4px;
    }

    /* --- Field Labels / Values --- */
    .field-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .field-value {
        font-size: 14px;
        color: #1f2937;
        line-height: 1.5;
    }

    .field-value.empty {
        color: #9ca3af;
        font-style: italic;
    }

    .field-value ul, .field-value ol {
        margin: 0;
        padding-left: 20px;
    }

    .field-block {
        margin-bottom: 20px;
    }

    /* --- Term Selector --- */
    .term-select {
        max-width: 220px;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        padding: 8px 12px;
        font-size: 14px;
        color: #374151;
        background-color: white;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .term-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* --- Header Pills --- */
    .header-pill {
        background: rgba(255, 255, 255, 0.14);
        color: rgba(255, 255, 255, 0.92);
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.12);
        letter-spacing: 0.01em;
    }

    /* --- CKEditor --- */
    .ck.ck-editor__editable_inline {
        min-height: 120px !important;
    }

    .learning-objectives-editor .ck.ck-editor__editable_inline,
    textarea[name="learning_objectives"] {
        min-height: 260px !important;
    }

    /* --- Reflection Box --- */
    .reflection-box {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 3px;
        padding: 16px;
        margin-top: 24px;
    }

    .reflection-box .reflection-title {
        font-weight: 600;
        color: #166534;
        margin-bottom: 8px;
        font-size: 15px;
    }

    .reflection-box .reflection-content {
        color: #15803d;
        font-size: 14px;
        line-height: 1.6;
    }

    .reflection-content ul, .reflection-content ol {
        margin: 0;
        padding-left: 20px;
    }

    /* --- Document Picker --- */
    .doc-picker-wrapper {
        position: relative;
    }

    #doc-search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        z-index: 100;
        max-height: 220px;
        overflow-y: auto;
    }

    .doc-result-item {
        padding: 8px 12px;
        cursor: pointer;
        font-size: 14px;
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.15s;
    }

    .doc-result-item:last-child {
        border-bottom: none;
    }

    .doc-result-item:hover {
        background: #f0f9ff;
    }

    #doc-selected .doc-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #dbeafe;
        color: #1e40af;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 13px;
        margin-top: 6px;
    }

    #doc-selected .doc-chip button {
        background: none;
        border: none;
        color: #1e40af;
        cursor: pointer;
        padding: 0;
        line-height: 1;
        font-size: 14px;
    }

    /* --- Teacher Group (HOD Dashboard) --- */
    .teacher-group {
        margin-bottom: 28px;
    }

    .teacher-heading {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .teacher-heading .teacher-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .teacher-heading i {
        color: #6b7280;
    }

    /* --- Scheme Card (Teacher Dashboard) --- */
    .scheme-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .scheme-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        border-color: #d1d5db;
    }

    .scheme-card-header {
        background: #f9fafb;
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }

    .scheme-card-header .scheme-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .scheme-card-body {
        padding: 0;
    }

    .scheme-card-footer {
        padding: 10px 16px;
        border-top: 1px solid #f3f4f6;
        text-align: right;
    }

    /* Scheme card status top borders */
    .scheme-card.card-draft { border-top: 3px solid #6b7280; }
    .scheme-card.card-submitted { border-top: 3px solid #3b82f6; }
    .scheme-card.card-under_review { border-top: 3px solid #f59e0b; }
    .scheme-card.card-approved { border-top: 3px solid #22c55e; }
    .scheme-card.card-revision_required { border-top: 3px solid #ef4444; }
    .scheme-card.card-supervisor_reviewed { border-top: 3px solid #6366f1; }

    /* --- Current week highlight (Teacher Dashboard) --- */
    .current-week-row td {
        background: #eff6ff !important;
        position: relative;
    }

    .current-week-row td:first-child {
        border-left: 3px solid;
        border-image: linear-gradient(135deg, #4e73df, #36b9cc) 1;
    }

    /* --- Entry Status Dots (Document view) --- */
    .entry-status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .dot-planned { background: #d1d5db; }
    .dot-in_progress { background: #3b82f6; }
    .dot-taught { background: #0ea5e9; }
    .dot-completed { background: #22c55e; }
    .dot-skipped { background: #f59e0b; }

    /* --- Manage Syllabi Card (Index Page) --- */
    .manage-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        border-top: 3px solid transparent;
        border-image: linear-gradient(135deg, #4e73df, #36b9cc) 1;
        border-image-slice: 1 0 0 0;
        padding: 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }

    .manage-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        border-color: #3b82f6;
        transform: translateY(-2px);
    }

    .manage-card .card-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .manage-card .card-icon i {
        font-size: 20px;
        color: white;
    }

    /* --- Progress Bar (Coverage) --- */
    .coverage-bar-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .coverage-bar {
        width: 80px;
        height: 6px;
        background: #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
    }

    .coverage-bar-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .coverage-bar-fill.fill-success { background: #22c55e; }
    .coverage-bar-fill.fill-warning { background: #f59e0b; }
    .coverage-bar-fill.fill-danger { background: #ef4444; }
    .coverage-bar-fill.fill-secondary { background: #9ca3af; }
</style>
