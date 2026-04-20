# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Path

**CRITICAL:** This project is located at:

```
/Users/thatoobuseng/Sites/Junior
```

Always verify you are working in this directory before making any changes. Do not modify files outside of this project path.

## Project Overview

This is a Laravel 9.x School Management System for Heritage Junior Secondary School. It handles academics, admissions, assessments, attendance, fees, and administrative functions across multiple school types (Senior, Junior/CJSS, Primary, Reception/Pre-school).

## Common Commands

### Development Server
```bash
php artisan serve              # Start Laravel development server
npm run dev                    # Start Vite development server with HMR
```

### Building Assets
```bash
npm run build                  # Production build
npm run watch                  # Watch mode for development
```

### Database
```bash
php artisan migrate            # Run migrations
php artisan migrate:fresh --seed  # Reset and seed database
php artisan tinker             # Interactive PHP shell
```

### Testing
```bash
php artisan test               # Run all tests
php artisan test --filter=TestName  # Run specific test
```

### Cache & Maintenance
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

## Architecture

### Route Organization
Routes are modularized in `/routes/` with includes for each feature domain:
- `assessment/` - Separate files for senior, junior, primary, and reception assessments
- `finals/` - Final grades, classes, houses, core subjects, external exams
- `fees/`, `students/`, `staff/`, `academic/`, `attendance/`, etc.

### Controllers by School Type
Assessment controllers are split by school type in `/app/Http/Controllers/Assessment/`:
- `SeniorAssessmentController.php`
- `JuniorAssessmentController.php`
- `PrimaryAssessmentController.php`
- `ReceptionAssessmentController.php`

### Views Structure
Blade templates in `/resources/views/` mirror the controller organization:
- `assessment/senior/` - Senior school markbooks, report cards, class lists
- `assessment/junior/` - CJSS markbooks and assessments
- `assessment/primary/` - Primary school assessments
- `assessment/shared/` - Shared components like optional subject markbooks

### Key Models
127 Eloquent models in `/app/Models/`. Key relationships:
- `Student` - Core student data, linked to classes, tests, sponsors
- `Klass` - Classes with students and teachers
- `GradeSubject` - Subjects per grade with associated tests
- `StudentTest` - Individual student test scores
- `Term` - Academic terms for assessment periods

### Services Layer
Business logic in `/app/Services/` (24 files) handles complex operations like:
- Report card generation
- Grade calculations
- Data exports

### Exports/Imports
Excel operations via Maatwebsite/Excel in `/app/Exports/` (57 files) and `/app/Imports/`.

## Key Patterns

### Assessment Markbooks
Markbook views (`markbook-*-class-list.blade.php`) use:
- Sequential `$rowIndex` counter for Tab navigation (not collection keys)
- `$loop->index` for column indices in nested foreach loops
- `score-input` class with `data-row` and `data-col` attributes for keyboard navigation

### PDF Generation
Uses `barryvdh/laravel-dompdf` for report cards. PDF templates are in `resources/views/` with `-pdf` suffix.

### Frontend Stack
- Bootstrap 5 for UI
- Vite for asset bundling (configured in `vite.config.js`)
- jQuery for DOM manipulation
- ApexCharts, DataTables, Sweetalert2 for interactive components

## Coding Standards & Best Practices

### Code Style & Formatting
- **Brace Style:** Use "Same Line" (K&R / 1TBS) braces for all methods, classes, and control structures.
  - *Correct:* `public function index() {`
  - *Incorrect:* `public function index()` followed by `{` on a new line.
- **Type Hinting:** Strictly use return types and typed properties where possible (e.g., `public function grade(Student $student): float {`).
- **Naming:** Follow Laravel naming conventions (camelCase for variables/methods, Snake_case for database columns).

### Performance & Optimization
- **Database Queries:**
  - **No N+1 Queries:** Always use Eager Loading (`with()`) for relationships in loops.
  - **Select Specific Columns:** Avoid `select *`. Fetch only needed columns (e.g., `User::select('id', 'name')->get()`) to reduce memory usage.
  - **Indexing:** Ensure foreign keys and frequently searched columns are indexed in migrations.
- **Caching:** Cache expensive dashboard queries or static reference data using `Cache::remember`.

### Robustness & Security
- **Validation:** Never use `$request->all()`. Use FormRequests or `valdiated()` data for all state-changing operations.
- **Authorization:** Always check policies (e.g., `$this->authorize('view', $record)`) before returning data.
- **Race Conditions:**
  - **Transactions:** Wrap all multi-step database updates in `DB::transaction(function() { ... })`.
  - **Locking:** Use `lockForUpdate()` when reading data that will be immediately modified (e.g., allocating a student to a full class) to prevent race conditions.
- **Error Handling:** Use `try/catch` blocks for external service calls or file operations. Log errors using `Log::error()` with context.

### Frontend Considerations
- **Asset Loading:** Defer non-critical JavaScript.
- **Feedback:** Always provide user feedback (Toast/SweetAlert) on success or failure of async operations.

## UI Theming Standards

**CRITICAL:** All pages and buttons MUST follow the established theming patterns. Reference these files for the correct styling:
- `resources/views/admissions/admission-new.blade.php` - Form pages with save button
- `resources/views/admissions/index.blade.php` - Index/listing pages
- `docs/create.blade.php` - Create form pages
- `docs/edit.blade.php` - Edit form pages

### Container & Card Styling
```css
.form-container {
    background: white;
    border-radius: 3px;
    padding: 32px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
```

### Index Page Header (Gradient)
```css
.header {
    background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
    color: white;
    padding: 28px;
    border-radius: 3px 3px 0 0;
}
```

### Primary Button Styling
```css
.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
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

.btn-primary:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}
```

### Save Button with Icon and Loading Animation
**REQUIRED:** All save/submit buttons must use this pattern:
```html
<button type="submit" class="btn btn-primary btn-loading">
    <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
    <span class="btn-spinner d-none">
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Saving...
    </span>
</button>
```

**Required CSS for loading state:**
```css
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
```

**Required JavaScript to trigger loading state on form submit:**
```javascript
const submitBtn = form.querySelector('button[type="submit"].btn-loading');
if (submitBtn) {
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
}
```

### Help Text Box
```css
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
```

### Form Input Focus States
```css
.form-control:focus,
.form-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
```

### Section Title
```css
.section-title {
    font-size: 16px;
    font-weight: 600;
    margin: 24px 0 16px 0;
    color: #1f2937;
    padding-bottom: 8px;
    border-bottom: 1px solid #e5e7eb;
}
```

### Form Actions Layout
```css
.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end; /* or space-between if back button exists */
    padding-top: 24px;
    border-top: 1px solid #f3f4f6;
    margin-top: 32px;
}
```
