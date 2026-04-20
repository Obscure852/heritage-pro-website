<style>
/* ============================================
   SPONSOR PORTAL STYLES
   Matching admissions/index theming
   ============================================ */

/* Container & Card Styling */
.sponsor-container {
    background: white;
    border-radius: 3px;
    padding: 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 24px;
}

.sponsor-header {
    background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
    color: white;
    padding: 28px;
    border-radius: 3px 3px 0 0;
}

.sponsor-header h3,
.sponsor-header h4,
.sponsor-header h5 {
    margin: 0;
    font-weight: 600;
}

.sponsor-header p {
    margin: 6px 0 0 0;
    opacity: 0.9;
}

.sponsor-body {
    padding: 24px;
}

/* Term Selector - Above Header, No Radius */
.term-selector-bar {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    margin-bottom: 0;
    padding: 12px 16px;
    background: #f8f9fa;
    border: 1px solid #e5e7eb;
    border-bottom: none;
}

.term-selector-bar label {
    font-weight: 500;
    color: #374151;
    font-size: 14px;
    margin: 0;
}

.term-selector-bar .form-select {
    width: auto;
    min-width: 180px;
    font-size: 0.9rem;
    border-radius: 0;
    border: 1px solid #d1d5db;
    padding: 8px 32px 8px 12px;
}

.term-selector-bar .form-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Help Text - Matching admissions/index */
.help-text {
    background: #f8f9fa;
    padding: 12px;
    border-left: 4px solid #3b82f6;
    border-radius: 0 3px 3px 0;
    margin-bottom: 20px;
}

.help-text .help-title {
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
}

.help-text .help-content {
    color: #6b7280;
    font-size: 13px;
    line-height: 1.4;
}

/* Table Styling - Matching admissions/index */
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

/* Subject Table Styling */
.subject-table-container {
    background: white;
    border-radius: 3px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.subject-table {
    width: 100%;
    margin-bottom: 0;
}

.subject-table thead th {
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
    color: #374151;
    font-size: 13px;
    padding: 12px 16px;
}

.subject-table tbody tr {
    transition: background-color 0.15s ease;
}

.subject-table tbody tr:hover {
    background-color: #f9fafb;
}

.subject-table tbody td {
    padding: 12px 16px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.subject-table tbody tr:last-child td {
    border-bottom: none;
}

.subject-name {
    font-weight: 500;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 10px;
}

.subject-icon {
    width: 32px;
    height: 32px;
    border-radius: 3px;
    background: #e0e7ff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4e73df;
    font-size: 14px;
}

/* Score Colors */
.score-cell {
    font-weight: 600;
    font-size: 14px;
}

.score-excellent { color: #059669; }
.score-good { color: #4e73df; }
.score-average { color: #d97706; }
.score-poor { color: #dc2626; }

/* Grade Badges */
.grade-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 24px;
    padding: 0 8px;
    border-radius: 3px;
    font-weight: 600;
    font-size: 12px;
}

.grade-a { background: #d1fae5; color: #065f46; }
.grade-b { background: #dbeafe; color: #1e40af; }
.grade-c { background: #e0e7ff; color: #3730a3; }
.grade-d { background: #fef3c7; color: #92400e; }
.grade-e, .grade-f, .grade-u { background: #fee2e2; color: #991b1b; }
.grade-na { background: #f3f4f6; color: #6b7280; }

/* Primary Button - Matching admissions/index */
.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border: none;
    color: white;
    font-weight: 500;
    padding: 10px 16px;
    border-radius: 3px;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    color: white;
}

/* Main Navigation Tabs (Tests/Exams) */
.sponsor-nav-tabs {
    display: flex;
    gap: 4px;
    padding: 4px;
    background: #f3f4f6;
    border-radius: 3px;
    margin-bottom: 20px;
}

.sponsor-nav-tabs .nav-link {
    flex: 1;
    text-align: center;
    padding: 10px 16px;
    border-radius: 3px;
    border: none;
    background: transparent;
    color: #6b7280;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.sponsor-nav-tabs .nav-link:hover {
    color: #4e73df;
    background: rgba(78, 115, 223, 0.08);
}

.sponsor-nav-tabs .nav-link.active {
    background: white;
    color: #4e73df;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.sponsor-nav-tabs .nav-link i {
    font-size: 16px;
}

/* Vertical Month Tabs */
.month-tabs-container {
    background: #f8f9fa;
    border-radius: 3px;
    padding: 8px;
    height: fit-content;
}

.month-tabs {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.month-tabs .nav-link {
    text-align: left;
    padding: 10px 14px;
    border-radius: 3px;
    color: #4b5563;
    background: white;
    border: 1px solid #e5e7eb;
    font-weight: 500;
    font-size: 13px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}

.month-tabs .nav-link:hover {
    color: #4e73df;
    border-color: #4e73df;
    background: rgba(78, 115, 223, 0.04);
}

.month-tabs .nav-link.active {
    background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
    color: white;
    border-color: transparent;
}

.month-tabs .nav-link .month-icon {
    width: 28px;
    height: 28px;
    border-radius: 3px;
    background: rgba(78, 115, 223, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 600;
    color: #4e73df;
}

.month-tabs .nav-link.active .month-icon {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-state-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 28px;
    color: #9ca3af;
}

.empty-state h5 {
    color: #374151;
    font-weight: 600;
    font-size: 15px;
    margin-bottom: 6px;
}

.empty-state p {
    color: #6b7280;
    font-size: 13px;
    max-width: 280px;
    margin: 0 auto;
}

/* Child Profile Header */
.child-profile-header {
    display: flex;
    align-items: center;
    gap: 16px;
}

.child-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 600;
    border: 2px solid rgba(255, 255, 255, 0.3);
    overflow: hidden;
}

.child-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.child-info h5 {
    margin: 0;
    font-weight: 600;
}

.child-info .child-class {
    opacity: 0.85;
    font-size: 13px;
    margin-top: 2px;
}

/* Section Titles */
.section-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin: 20px 0 12px 0;
    padding-bottom: 8px;
    border-bottom: 2px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-title i {
    color: #4e73df;
}

/* Loading Spinner */
.loading-container {
    text-align: center;
    padding: 40px 20px;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e5e7eb;
    border-top-color: #4e73df;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.loading-container p {
    color: #6b7280;
    margin-top: 12px;
    font-size: 13px;
}

/* Card Footer */
.sponsor-footer {
    background: #f8f9fa;
    padding: 10px 24px;
    border-top: 1px solid #e5e7eb;
    font-size: 12px;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 6px;
}

.sponsor-footer i {
    color: #4e73df;
}

/* Comments Section */
.comments-section {
    background: #fffbeb;
    border-radius: 3px;
    padding: 14px;
    margin-top: 16px;
    border-left: 4px solid #f59e0b;
}

.comments-section .comment-title {
    font-weight: 600;
    color: #92400e;
    font-size: 13px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.comment-item {
    background: white;
    border-radius: 3px;
    padding: 10px 14px;
    margin-bottom: 8px;
}

.comment-item:last-child {
    margin-bottom: 0;
}

.comment-subject {
    font-weight: 600;
    color: #1f2937;
    font-size: 12px;
    margin-bottom: 4px;
}

.comment-text {
    color: #6b7280;
    font-size: 12px;
    line-height: 1.4;
}

/* Status Badges */
.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    text-transform: capitalize;
}

.status-paid { background: #d1fae5; color: #065f46; }
.status-partial { background: #fef3c7; color: #92400e; }
.status-outstanding { background: #fee2e2; color: #991b1b; }

/* ============================================
   REPORT CARD PREVIEW STYLES
   PDF-like styling for report card modal
   ============================================ */

.report-card-preview {
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 3px;
    padding: 30px;
    max-width: 800px;
    margin: 0 auto;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    font-family: 'Helvetica', 'Arial', sans-serif;
}

/* Report Header */
.report-header {
    border-bottom: 2px solid #374151;
    padding-bottom: 16px;
    margin-bottom: 20px;
}

.report-header-content {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 12px;
}

.report-coat-arms img,
.report-logo img {
    height: 70px;
    width: auto;
}

.report-school-info {
    flex: 1;
    text-align: center;
    padding: 0 20px;
}

.report-school-info h3 {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 6px 0;
    text-transform: uppercase;
}

.report-school-info p {
    font-size: 12px;
    color: #4b5563;
    margin: 2px 0;
    line-height: 1.4;
}

.report-title {
    text-align: center;
    margin-top: 12px;
}

.report-title h4 {
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    letter-spacing: 1px;
}

.report-title p {
    font-size: 13px;
    color: #6b7280;
    margin: 4px 0 0 0;
}

/* Student Info Section */
.report-student-info {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 3px;
    padding: 16px;
    margin-bottom: 20px;
}

.report-student-info .info-row {
    display: flex;
    gap: 20px;
    margin-bottom: 10px;
}

.report-student-info .info-row:last-child {
    margin-bottom: 0;
}

.report-student-info .info-item {
    flex: 1;
    display: flex;
    gap: 8px;
}

.report-student-info .info-label {
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
}

.report-student-info .info-value {
    font-size: 12px;
    color: #1f2937;
}

/* Report Section Title */
.report-section-title {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 12px 0;
    padding-bottom: 6px;
    border-bottom: 1px solid #e5e7eb;
}

/* Report Table */
.report-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    margin-bottom: 0;
}

.report-table th,
.report-table td {
    border: 1px solid #d1d5db;
    padding: 8px 10px;
    text-align: left;
}

.report-table th {
    background: #f3f4f6;
    font-weight: 600;
    color: #374151;
    font-size: 11px;
    text-transform: uppercase;
}

.report-table tbody tr:hover {
    background: #fafafa;
}

.report-table .summary-row {
    background: #f3f4f6;
}

.report-table .summary-row td {
    font-weight: 600;
}

/* Best 6 Subjects Highlighting (Senior) */
.report-table tr.best-subject {
    background: #ecfdf5;
}

.best-6-star {
    color: #f59e0b;
    font-weight: 700;
}

.report-note {
    font-size: 11px;
    color: #6b7280;
    margin-top: 8px;
    font-style: italic;
}

/* Remarks Section */
.report-remarks {
    margin-top: 20px;
}

.remarks-box {
    border: 1px solid #d1d5db;
    border-radius: 3px;
    overflow: hidden;
}

.remarks-header {
    background: #f3f4f6;
    padding: 8px 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #d1d5db;
}

.remarks-title {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.teacher-name {
    font-size: 11px;
    color: #6b7280;
}

.remarks-content {
    padding: 12px;
    font-size: 12px;
    color: #4b5563;
    min-height: 40px;
    line-height: 1.5;
}

/* Report Footer Note */
.report-footer-note {
    margin-top: 20px;
    padding-top: 12px;
    border-top: 1px dashed #d1d5db;
    text-align: center;
}

.report-footer-note p {
    margin: 0;
}

/* Modal Adjustments for Report Card */
#reportCardModal .modal-body {
    background: #f3f4f6;
    padding: 24px;
}

/* Print-friendly overrides */
@media print {
    .report-card-preview {
        box-shadow: none;
        border: none;
        padding: 0;
    }
}

/* Responsive Design */
@media (max-width: 991px) {
    .month-tabs-container {
        margin-bottom: 16px;
    }

    .month-tabs {
        flex-direction: row;
        overflow-x: auto;
        gap: 8px;
        padding-bottom: 4px;
    }

    .month-tabs .nav-link {
        white-space: nowrap;
        flex-shrink: 0;
    }
}

@media (max-width: 768px) {
    .sponsor-header {
        padding: 20px;
    }

    .sponsor-body {
        padding: 16px;
    }

    .child-profile-header {
        flex-direction: column;
        text-align: center;
    }

    .sponsor-nav-tabs {
        flex-direction: column;
    }

    .term-selector-bar {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }

    .term-selector-bar .form-select {
        width: 100%;
    }
}
</style>
