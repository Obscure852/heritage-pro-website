# Design Standards Document

This document outlines the consistent design patterns to be applied across all views in the application.

---

## Table of Contents
1. [Container Styles](#container-styles)
2. [List View (Index Pages)](#list-view-index-pages)
3. [Form View (Create/Edit Pages)](#form-view-createedit-pages)
4. [Table Design](#table-design)
5. [Filters and Search](#filters-and-search)
6. [Button Styles](#button-styles)
7. [Reports Dropdown](#reports-dropdown)
8. [Icons](#icons)
9. [Form Elements](#form-elements)
10. [Help Text](#help-text)
11. [Status Badges](#status-badges)
12. [Loading States](#loading-states)
13. [Responsive Design](#responsive-design)

---

## Container Styles

### List View Container
```css
.admissions-container {
    background: white;
    border-radius: 3px;
    padding: 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.admissions-header {
    background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
    color: white;
    padding: 28px;
    border-radius: 3px 3px 0 0;
}

.admissions-body {
    padding: 24px;
}
```

### Form Container
```css
.form-container {
    background: white;
    border-radius: 3px;
    padding: 32px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.page-title {
    font-size: 22px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}
```

---

## List View (Index Pages)

### Structure
```blade
@section('content')
    <div class="admissions-container">
        <div class="admissions-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Page Title</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Page description</p>
                </div>
                <div class="col-md-6">
                    <!-- Stats in header -->
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $count }}</h4>
                                <small class="opacity-75">Label</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="admissions-body">
            <!-- Help text -->
            <div class="help-text">...</div>

            <!-- Controls row with "New" button -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <!-- Filters/Search -->
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end">
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> New Item
                    </a>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped align-middle">...</table>
            </div>
        </div>
    </div>
@endsection
```

### Stats Item Styling
```css
.stat-item {
    padding: 10px 0;
}

.stat-item h4 {
    font-size: 1.5rem;
    font-weight: 700;
}

.stat-item small {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
```

---

## Form View (Create/Edit Pages)

### Structure
```blade
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('module.index') }}">Module Name</a>
        @endslot
        @slot('title')
            Page Title
        @endslot
    @endcomponent

    <!-- Alerts -->

    <div class="form-container">
        <div class="page-header">
            <h4 class="page-title">Form Title</h4>
        </div>

        <form>
            <div class="form-section">
                <div class="help-text">
                    <div class="help-title">Section Title</div>
                    <div class="help-content">Section description.</div>
                </div>
                <!-- Form fields -->
            </div>

            <div class="form-actions">
                <a href="#" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Save</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection
```

### Form Section Styling
```css
.form-section {
    margin-bottom: 28px;
}

.form-section:last-child {
    margin-bottom: 0;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e5e7eb;
}
```

---

## Table Design

### Table Structure
```html
<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>
                    <div class="student-cell">
                        <!-- Avatar placeholder -->
                        <div class="student-avatar-placeholder">AB</div>
                        <div>
                            <div><a href="#">Student Name</a></div>
                            <div class="text-muted" style="font-size: 12px;">ID: 123456</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div>email@example.com</div>
                    <div class="text-muted" style="font-size: 12px;">+123 456 789</div>
                </td>
                <td><span class="badge bg-info">Active</span></td>
                <td class="text-end">
                    <div class="action-buttons">
                        <a href="#" class="btn btn-sm btn-outline-info" title="View">
                            <i class="bx bx-show"></i>
                        </a>
                        <a href="#" class="btn btn-sm btn-outline-danger" title="Delete">
                            <i class="bx bx-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

### Table CSS
```css
.student-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.student-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    background: #e2e8f0;
}

.student-avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
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
    font-size: 16px;
}
```

### Pagination
```css
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 16px;
}
```

---

## Filters and Search

Client-side filtering for list views with search input, filter dropdowns, and pagination.

### Filter Controls HTML Structure
```html
<div class="row align-items-center mb-3">
    <div class="col-lg-8 col-md-12">
        <div class="controls">
            <div class="row g-2 align-items-center">
                <!-- Search Input -->
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search by name..." id="searchInput">
                    </div>
                </div>
                <!-- Status Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        @foreach ($statuses ?? [] as $status)
                            <option value="{{ strtolower($status) }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Additional Filters (Grade, Gender, etc.) -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <select class="form-select" id="gradeFilter">
                        <option value="">All Grades</option>
                        @foreach ($grades ?? [] as $grade)
                            <option value="{{ strtolower($grade) }}">{{ $grade }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-2 col-sm-6">
                    <select class="form-select" id="genderFilter">
                        <option value="">All Gender</option>
                        <option value="m">Male</option>
                        <option value="f">Female</option>
                    </select>
                </div>
                <!-- Reset Button -->
                <div class="col-lg-2 col-md-12">
                    <button type="button" class="btn btn-light w-100" id="resetFilters">Reset</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
        <!-- New Button and Reports Dropdown -->
    </div>
</div>
```

### Filter Controls CSS
```css
.controls .form-control,
.controls .form-select {
    font-size: 0.9rem;
}

.controls .input-group-text {
    background: #f8f9fa;
    border-color: #dee2e6;
}
```

### Table Row Data Attributes
Add data attributes to table rows for JavaScript filtering:
```html
<tr class="item-row"
    data-name="{{ strtolower($item->name) }}"
    data-status="{{ strtolower($item->status ?? '') }}"
    data-grade="{{ strtolower($item->grade ?? '') }}"
    data-gender="{{ strtolower($item->gender ?? '') }}">
    <!-- Row content -->
</tr>
```

### Pagination Container
```html
<div class="pagination-container mt-3" style="display: flex; justify-content: space-between; align-items: center;">
    <div class="text-muted" id="results-info">
        Showing <span id="showing-from">0</span> to <span id="showing-to">0</span> of <span id="total-count">0</span> Items
    </div>
    <nav id="pagination-nav">
        <!-- Pagination will be inserted here by JavaScript -->
    </nav>
</div>
```

### Client-Side Filtering JavaScript
```javascript
// Client-side filtering and pagination
let currentPage = 1;
const itemsPerPage = 20;

function filterAndPaginate(resetPage = true) {
    if (resetPage) currentPage = 1;

    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
    const gradeFilter = document.getElementById('gradeFilter').value.toLowerCase();
    const genderFilter = document.getElementById('genderFilter').value.toLowerCase();

    const allRows = document.querySelectorAll('.item-row');
    let filteredRows = [];
    let maleCount = 0;
    let femaleCount = 0;

    // First pass: filter rows and collect stats
    allRows.forEach(row => {
        const name = row.dataset.name || '';
        const status = row.dataset.status || '';
        const grade = row.dataset.grade || '';
        const gender = row.dataset.gender || '';

        // Check if row matches all filters
        const matchesSearch = !searchTerm || name.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        const matchesGrade = !gradeFilter || grade === gradeFilter;
        const matchesGender = !genderFilter || gender === genderFilter;

        if (matchesSearch && matchesStatus && matchesGrade && matchesGender) {
            filteredRows.push(row);
            if (gender === 'm') maleCount++;
            if (gender === 'f') femaleCount++;
        }
    });

    // Calculate pagination
    const totalFiltered = filteredRows.length;
    const totalPages = Math.ceil(totalFiltered / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;

    // Show/hide based on pagination
    allRows.forEach(row => row.style.display = 'none');
    filteredRows.forEach((row, index) => {
        if (index >= startIndex && index < endIndex) {
            row.style.display = '';
        }
    });

    // Update stats in header
    const statElements = document.querySelectorAll('.stat-item h4');
    if (statElements.length >= 3) {
        statElements[0].textContent = totalFiltered;
        statElements[1].textContent = maleCount;
        statElements[2].textContent = femaleCount;
    }

    // Update showing info
    const showingFrom = totalFiltered > 0 ? startIndex + 1 : 0;
    const showingTo = Math.min(endIndex, totalFiltered);
    document.getElementById('showing-from').textContent = showingFrom;
    document.getElementById('showing-to').textContent = showingTo;
    document.getElementById('total-count').textContent = totalFiltered;

    // Generate pagination controls
    generatePagination(totalPages, currentPage);
}

function generatePagination(totalPages, current) {
    const paginationNav = document.getElementById('pagination-nav');

    if (totalPages <= 1) {
        paginationNav.innerHTML = '';
        return;
    }

    let html = '<ul class="pagination mb-0">';

    // Previous button
    html += `<li class="page-item ${current === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="goToPage(${current - 1}); return false;">Previous</a>
    </li>`;

    // Page numbers (show max 5 visible)
    const maxVisible = 5;
    let startPage = Math.max(1, current - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);

    if (endPage - startPage < maxVisible - 1) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }

    if (startPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(1); return false;">1</a></li>`;
        if (startPage > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }

    for (let i = startPage; i <= endPage; i++) {
        html += `<li class="page-item ${i === current ? 'active' : ''}">
            <a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a>
        </li>`;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        html += `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(${totalPages}); return false;">${totalPages}</a></li>`;
    }

    // Next button
    html += `<li class="page-item ${current === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="goToPage(${current + 1}); return false;">Next</a>
    </li>`;

    html += '</ul>';
    paginationNav.innerHTML = html;
}

function goToPage(page) {
    currentPage = page;
    filterAndPaginate(false);
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('gradeFilter').value = '';
    document.getElementById('genderFilter').value = '';
    filterAndPaginate(true);
}

// Event listeners
document.getElementById('searchInput').addEventListener('input', () => filterAndPaginate(true));
document.getElementById('statusFilter').addEventListener('change', () => filterAndPaginate(true));
document.getElementById('gradeFilter').addEventListener('change', () => filterAndPaginate(true));
document.getElementById('genderFilter').addEventListener('change', () => filterAndPaginate(true));
document.getElementById('resetFilters').addEventListener('click', resetFilters);

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => filterAndPaginate(true));
```

### Controller Setup
Pass filter options from the controller:
```php
public function index()
{
    $items = Model::all();

    // Get unique values for filter dropdowns
    $statuses = $items->pluck('status')->unique()->filter()->sort()->values();
    $grades = $items->pluck('grade')->unique()->filter()->sort()->values();

    return view('module.index', compact('items', 'statuses', 'grades'));
}
```

---

## Button Styles

### Primary Button (Save/Submit/New)
```css
.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 3px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
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
```

### Secondary/Light Button (Cancel/Back)
```css
.btn-secondary {
    background: #f8f9fa;
    color: #6c757d;
    border: 1px solid #dee2e6;
    padding: 10px 16px;
    border-radius: 3px;
}

.btn-secondary:hover {
    background: #e9ecef;
    color: #495057;
    transform: translateY(-1px);
}
```

### "New" Button in List Views
```html
<a href="{{ route('module.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i> New Item
</a>
```

### Form Actions Container
```css
.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding-top: 24px;
    border-top: 1px solid #f3f4f6;
    margin-top: 32px;
}
```

---

## Reports Dropdown

A styled dropdown for report actions matching the primary button style.

### HTML Structure
```html
<div class="btn-group reports-dropdown">
    <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2" style="font-size: 10px;"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a class="dropdown-item" href="#">
                <i class="fas fa-list-ul text-primary"></i> Report Title
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="#">
                <i class="fas fa-tasks text-purple"></i> Another Report
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="#">
                <i class="fas fa-chart-pie text-success"></i> Chart Report
            </a>
        </li>
    </ul>
</div>
```

### CSS Styles
```css
/* Reports Dropdown Styling */
.reports-dropdown .dropdown-toggle {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border: none;
    color: white;
    font-weight: 500;
    padding: 10px 16px;
    border-radius: 3px;
    transition: all 0.2s ease;
}

.reports-dropdown .dropdown-toggle:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    color: white;
}

.reports-dropdown .dropdown-toggle:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
    color: white;
}

.reports-dropdown .dropdown-menu {
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-radius: 3px;
    padding: 8px 0;
    min-width: 220px;
    margin-top: 4px;
}

.reports-dropdown .dropdown-item {
    padding: 8px 16px;
    font-size: 14px;
    color: #374151;
}

.reports-dropdown .dropdown-item:hover {
    background: #f3f4f6;
    color: #1f2937;
}

.reports-dropdown .dropdown-item i {
    width: 20px;
    margin-right: 8px;
}
```

---

## Icons

### Icon Library Priority
1. **Font Awesome** (`fas`, `far`, `fab`) - Preferred for buttons
2. **Boxicons** (`bx`) - Used for action buttons in tables

### Common Icons
| Action | Icon | Class |
|--------|------|-------|
| Save/Create | save | `fas fa-save` |
| Add/New | plus | `fas fa-plus` |
| Back | arrow-left | `fas fa-arrow-left` |
| Edit | edit-alt | `bx bx-edit-alt` |
| View | show | `bx bx-show` |
| Delete | trash | `bx bx-trash` |
| Search | search | `fas fa-search` |
| Filter | filter | `fas fa-filter` |
| Reports | chart-bar | `fas fa-chart-bar` |
| Close/Cancel | times | `fas fa-times` |

### Button with Icon Examples
```html
<!-- Primary Save Button -->
<button type="submit" class="btn btn-primary">
    <i class="fas fa-save"></i> Save
</button>

<!-- New Item Button -->
<a href="#" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i> New Admission
</a>

<!-- Back Button -->
<a href="#" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back
</a>
```

---

## Form Elements

### Form Labels
```css
.form-label {
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
}

.required::after {
    content: '*';
    color: #dc2626;
    margin-left: 4px;
}
```

### Form Controls
```css
.form-control,
.form-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 3px;
    font-size: 14px;
    transition: all 0.2s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
}
```

---

## Help Text

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

---

## Status Badges

```css
.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    text-transform: capitalize;
}

.status-current { background: #d1fae5; color: #065f46; }
.status-pending { background: #fef3c7; color: #92400e; }
.status-enrolled { background: #dbeafe; color: #1e40af; }
.status-left { background: #fee2e2; color: #991b1b; }
.status-to-join { background: #e9d5ff; color: #6b21a8; }
.status-deleted { background: #f3f4f6; color: #4b5563; }
```

---

## Loading States

### Button Loading Animation
```html
<button type="submit" class="btn btn-primary btn-loading">
    <span class="btn-text"><i class="fas fa-save"></i> Save</span>
    <span class="btn-spinner d-none">
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Saving...
    </span>
</button>
```

### Loading Button CSS
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

### Loading Button JavaScript
```javascript
// Add to form submit handler
const forms = document.querySelectorAll('form');
forms.forEach(function(form) {
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"].btn-loading');
        if (submitBtn && form.checkValidity()) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        }
    });
});
```

---

## Responsive Design

### Mobile Breakpoints
```css
@media (max-width: 768px) {
    .stat-item h4 {
        font-size: 1.25rem;
    }

    .stat-item small {
        font-size: 0.75rem;
    }

    .admissions-header {
        padding: 20px;
    }

    .admissions-body {
        padding: 16px;
    }

    .form-container {
        padding: 20px;
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
```

---

## Checklist for Theming a Page

### List View (Index)
- [ ] Use `.admissions-container` wrapper
- [ ] Add gradient header with stats
- [ ] Include `.help-text` box
- [ ] Use `.student-cell` for name columns with avatar
- [ ] Style action buttons with `.action-buttons`
- [ ] "New" button uses `btn btn-primary` with `fas fa-plus` icon
- [ ] Add pagination container

### Form View (Create/Edit)
- [ ] Use breadcrumb component (NOT back button in header)
- [ ] Use `.form-container` wrapper
- [ ] Add `.page-header` with `.page-title`
- [ ] Use `.help-text` for each section
- [ ] Use `.form-section` grouping
- [ ] Save button uses `fas fa-save` icon
- [ ] Add loading animation to submit buttons
- [ ] Use `.form-actions` for button container

---

## File Reference
- **List View Reference**: `/docs/index.blade.php`
- **Create Form Reference**: `/docs/create.blade.php`
- **Edit Form Reference**: `/docs/edit.blade.php`
