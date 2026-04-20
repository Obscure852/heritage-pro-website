@extends('layouts.master')
@section('title')
    Library Settings
@endsection
@section('css')
    <style>
        .library-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .library-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .library-body {
            padding: 24px;
        }

        /* Card Border */
        .card {
            border: none;
            box-shadow: none;
        }

        /* Tab Styling */
        .nav-tabs-custom {
            border-bottom: 1px solid #e5e7eb;
            gap: 8px;
            position: sticky;
            top: 0;
            z-index: 10;
            background: white;
            padding-top: 8px;
            margin: -8px -16px 0 -16px;
            padding-left: 16px;
            padding-right: 16px;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            background: transparent;
            color: #6b7280;
            font-weight: 500;
            padding: 12px 20px;
            transition: all 0.2s ease;
            border-radius: 0;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #4e73df;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            border-bottom-color: #4e73df;
            background: transparent;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
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
            line-height: 1.5;
            margin: 0;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select,
        textarea.form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus,
        textarea.form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
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

        /* Settings Form */
        .settings-section {
            margin-bottom: 32px;
        }

        .settings-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        /* Button Loading State */
        .btn-loading .btn-spinner {
            display: none;
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

        /* Chip Tags for simple lists */
        .chip-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e0e7ff;
            color: #3730a3;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .chip-remove {
            background: none;
            border: none;
            color: #6366f1;
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
            padding: 0;
            margin-left: 2px;
        }

        .chip-remove:hover {
            color: #dc2626;
        }

        .chip-add-row {
            display: flex;
            gap: 8px;
            max-width: 400px;
        }

        /* Item Type Cards */
        .item-type-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 12px 16px;
            margin-bottom: 12px;
            background: #fafbfc;
        }

        .item-type-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .item-type-name-group {
            flex: 1;
            max-width: 300px;
        }

        .item-type-rules .form-label-sm {
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 4px;
        }

        /* Inline Edit */
        .inline-edit-input {
            padding: 4px 8px;
            font-size: 13px;
        }

        .edit-actions {
            display: flex;
            gap: 4px;
        }

        .count-badge {
            display: inline-block;
            background: #e5e7eb;
            color: #374151;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 500;
        }

        #authorsTable .display-actions,
        #publishersTable .display-actions {
            display: flex;
            gap: 4px;
        }

        @media (max-width: 768px) {
            .library-header {
                padding: 20px;
            }

            .library-body {
                padding: 16px;
            }

            .nav-tabs-custom .nav-link {
                padding: 10px 12px;
                font-size: 13px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="javascript:void(0);">Library</a>
        @endslot
        @slot('title')
            Library Settings
        @endslot
    @endcomponent

    <div id="messageContainer"></div>

    <div class="library-container">
        <div class="library-header">
            <h4 class="mb-1 text-white"><i class="bx bx-book me-2"></i>Library Settings</h4>
            <p class="mb-0 opacity-75">Configure borrowing rules, fine rates, and penalty thresholds for students and staff</p>
        </div>
        <div class="library-body">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start flex-wrap" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#borrowingRules" role="tab">
                                <i class="fas fa-book-reader me-2 text-muted"></i>Borrowing Rules
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#finesPenalties" role="tab">
                                <i class="fas fa-coins me-2 text-muted"></i>Fines & Penalties
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#apikeys" role="tab">
                                <i class="fas fa-key me-2 text-muted"></i>API Keys
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#catalogOptions" role="tab">
                                <i class="fas fa-tags me-2 text-muted"></i>Catalog Options
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#authorsTab" role="tab">
                                <i class="fas fa-user-edit me-2 text-muted"></i>Authors
                                <span class="badge bg-secondary ms-1" style="font-size: 11px;">{{ $authors->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#publishersTab" role="tab">
                                <i class="fas fa-building me-2 text-muted"></i>Publishers
                                <span class="badge bg-secondary ms-1" style="font-size: 11px;">{{ $publishers->count() }}</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        {{-- Borrowing Rules Tab --}}
                        <div class="tab-pane active" id="borrowingRules" role="tabpanel">
                            @include('library.settings._borrowing-rules-tab')
                        </div>

                        {{-- Fines & Penalties Tab --}}
                        <div class="tab-pane" id="finesPenalties" role="tabpanel">
                            @include('library.settings._fines-tab')
                        </div>

                        {{-- API Keys Tab --}}
                        <div class="tab-pane" id="apikeys" role="tabpanel">
                            @include('library.settings._api-keys-tab')
                        </div>

                        {{-- Catalog Options Tab --}}
                        <div class="tab-pane" id="catalogOptions" role="tabpanel">
                            @include('library.settings._catalog-options-tab')
                        </div>

                        {{-- Authors Tab --}}
                        <div class="tab-pane" id="authorsTab" role="tabpanel">
                            @include('library.settings._authors-tab')
                        </div>

                        {{-- Publishers Tab --}}
                        <div class="tab-pane" id="publishersTab" role="tabpanel">
                            @include('library.settings._publishers-tab')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab persistence
            const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
            tabLinks.forEach(tabLink => {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    const activeTabHref = event.target.getAttribute('href');
                    localStorage.setItem('librarySettingsActiveTab', activeTabHref);
                });
            });

            // Check for hash in URL first
            const hash = window.location.hash;
            if (hash) {
                const tabTriggerEl = document.querySelector(`.nav-link[href="${hash}"]`);
                if (tabTriggerEl) {
                    const tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                    history.replaceState(null, null, window.location.pathname);
                }
            } else {
                // Fall back to localStorage
                const activeTab = localStorage.getItem('librarySettingsActiveTab');
                if (activeTab) {
                    const tabTriggerEl = document.querySelector(`.nav-link[href="${activeTab}"]`);
                    if (tabTriggerEl) {
                        const tab = new bootstrap.Tab(tabTriggerEl);
                        tab.show();
                    }
                }
            }

            // Initialize tab functionalities
            initializeBorrowingRulesTab();
            initializeFinesTab();
            initializeApiKeysTab();
            initializeCatalogOptionsTab();
            initializeAuthorsTab();
            initializePublishersTab();
        });

        // Message display function
        function displayMessage(message, type = 'success') {
            const messageContainer = document.getElementById('messageContainer');
            const iconClass = type === 'success' ? 'mdi-check-all' : (type === 'error' ? 'mdi-block-helper' : 'mdi-information');
            messageContainer.innerHTML = `
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-${type === 'error' ? 'danger' : type} alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi ${iconClass} label-icon"></i>
                        <strong>${message}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>`;

            // Scroll to top to show message
            window.scrollTo({ top: 0, behavior: 'smooth' });

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                const alert = messageContainer.querySelector('.alert');
                if (alert) {
                    const dismissBtn = alert.querySelector('.btn-close');
                    if (dismissBtn) dismissBtn.click();
                }
            }, 5000);
        }

        // ========================================
        // Borrowing Rules Tab Functions
        // ========================================
        function initializeBorrowingRulesTab() {
            const form = document.getElementById('borrowingRulesForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitSettingsForm(this);
                });
            }
        }

        // ========================================
        // Fines Tab Functions
        // ========================================
        function initializeFinesTab() {
            const form = document.getElementById('finesForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitSettingsForm(this);
                });

                // Live-update currency labels across all tabs
                const currencyInput = document.getElementById('library_currency');
                if (currencyInput) {
                    currencyInput.addEventListener('input', function() {
                        const code = this.value.trim().toUpperCase() || 'BWP';
                        document.querySelectorAll('.currency-label').forEach(function(el) {
                            el.textContent = code;
                        });
                    });
                }
            }
        }

        // ========================================
        // API Keys Tab Functions
        // ========================================
        function initializeApiKeysTab() {
            const form = document.getElementById('apiKeysForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitSettingsForm(this);
                });
            }
        }

        // ========================================
        // Catalog Options Tab Functions
        // ========================================
        function initializeCatalogOptionsTab() {
            const form = document.getElementById('catalogOptionsForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitSettingsForm(this);
                });

                // Allow Enter key in chip add inputs
                ['newLocationInput', 'newCategoryInput', 'newReadingLevelInput'].forEach(function(inputId) {
                    const input = document.getElementById(inputId);
                    if (input) {
                        input.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                this.nextElementSibling.click();
                            }
                        });
                    }
                });
            }
        }

        // Add a chip to a container
        function addChip(containerId, inputId, inputName) {
            const container = document.getElementById(containerId);
            const input = document.getElementById(inputId);
            const value = input.value.trim();

            if (!value) return;

            // Check for duplicates
            const existing = container.querySelectorAll('input[type="hidden"]');
            for (let i = 0; i < existing.length; i++) {
                if (existing[i].value.toLowerCase() === value.toLowerCase()) {
                    input.value = '';
                    return;
                }
            }

            const chip = document.createElement('span');
            chip.className = 'chip';
            chip.innerHTML = `${value}<input type="hidden" name="${inputName}" value="${value}"><button type="button" class="chip-remove" onclick="this.parentElement.remove()">&times;</button>`;
            container.appendChild(chip);
            input.value = '';
            input.focus();
        }

        // Item type management
        let itemTypeIndex = {{ count($settings['catalog_item_types'] ?? []) }};

        function addItemType() {
            const container = document.getElementById('itemTypesContainer');
            const idx = itemTypeIndex++;
            const card = document.createElement('div');
            card.className = 'item-type-card';
            card.dataset.index = idx;
            card.innerHTML = `
                <div class="item-type-header">
                    <div class="item-type-name-group">
                        <input type="text" class="form-control form-control-sm" name="item_types[${idx}][name]" placeholder="Item type name" required maxlength="100">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItemType(this)">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <div class="item-type-rules">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">Loan Period (days)</label>
                            <div class="row g-1">
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" name="item_types[${idx}][loan_period_student]" placeholder="Student" min="1" max="365">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" name="item_types[${idx}][loan_period_staff]" placeholder="Staff" min="1" max="365">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">Fine Rate (<span class="currency-label">{{ $settings['library_currency']['code'] ?? 'BWP' }}</span>/day)</label>
                            <div class="row g-1">
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" name="item_types[${idx}][fine_rate_student]" placeholder="Student" min="0" max="100" step="0.01">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" name="item_types[${idx}][fine_rate_staff]" placeholder="Staff" min="0" max="100" step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">Max Renewals</label>
                            <div class="row g-1">
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" name="item_types[${idx}][max_renewals_student]" placeholder="Student" min="0" max="10">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" name="item_types[${idx}][max_renewals_staff]" placeholder="Staff" min="0" max="10">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(card);
            card.querySelector('input[name$="[name]"]').focus();
        }

        function removeItemType(btn) {
            btn.closest('.item-type-card').remove();
        }

        // ========================================
        // Authors Tab Functions
        // ========================================
        function initializeAuthorsTab() {
            const csrfToken = '{{ csrf_token() }}';

            // Add author
            document.getElementById('addAuthorBtn').addEventListener('click', function() {
                const firstName = document.getElementById('authorFirstName').value.trim();
                const lastName = document.getElementById('authorLastName').value.trim();
                if (!firstName || !lastName) {
                    displayMessage('Please enter both first and last name.', 'error');
                    return;
                }

                this.disabled = true;
                fetch('{{ route("library.settings.store-author") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ first_name: firstName, last_name: lastName })
                })
                .then(r => r.json())
                .then(data => {
                    this.disabled = false;
                    if (data.success) {
                        displayMessage(data.message);
                        addAuthorRow(data.author);
                        document.getElementById('authorFirstName').value = '';
                        document.getElementById('authorLastName').value = '';
                        document.getElementById('authorFirstName').focus();
                        updateAuthorBadge(1);
                    } else {
                        displayMessage(data.message || 'Error adding author.', 'error');
                    }
                })
                .catch(() => {
                    this.disabled = false;
                    displayMessage('An error occurred while adding the author.', 'error');
                });
            });

            // Enter key in add inputs
            ['authorFirstName', 'authorLastName'].forEach(id => {
                document.getElementById(id).addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        document.getElementById('addAuthorBtn').click();
                    }
                });
            });

            // Search filter
            document.getElementById('authorSearch').addEventListener('input', function() {
                const term = this.value.toLowerCase();
                const rows = document.querySelectorAll('#authorsTable tbody tr:not(.empty-row)');
                let idx = 0;
                rows.forEach(row => {
                    const fn = row.querySelector('.author-first-name .display-value')?.textContent.toLowerCase() || '';
                    const ln = row.querySelector('.author-last-name .display-value')?.textContent.toLowerCase() || '';
                    const match = fn.includes(term) || ln.includes(term);
                    row.style.display = match ? '' : 'none';
                    if (match) row.querySelector('.row-number').textContent = ++idx;
                });
            });

            // Delegate edit/save/cancel/delete
            document.getElementById('authorsTable').addEventListener('click', function(e) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const row = btn.closest('tr');
                const id = row?.dataset.id;

                if (btn.classList.contains('edit-author-btn')) {
                    enterAuthorEditMode(row);
                } else if (btn.classList.contains('cancel-author-btn')) {
                    exitAuthorEditMode(row);
                } else if (btn.classList.contains('save-author-btn')) {
                    saveAuthor(row, id, csrfToken);
                } else if (btn.classList.contains('delete-author-btn')) {
                    deleteAuthor(row, id, csrfToken);
                }
            });
        }

        function enterAuthorEditMode(row) {
            row.querySelectorAll('.display-value').forEach(el => el.classList.add('d-none'));
            row.querySelectorAll('.inline-edit-input').forEach(el => { el.classList.remove('d-none'); el.focus(); });
            row.querySelector('.display-actions').classList.add('d-none');
            row.querySelector('.edit-actions').classList.remove('d-none');
        }

        function exitAuthorEditMode(row) {
            row.querySelectorAll('.display-value').forEach(el => el.classList.remove('d-none'));
            row.querySelectorAll('.inline-edit-input').forEach((el, i) => {
                el.classList.add('d-none');
                el.value = row.querySelectorAll('.display-value')[i].textContent;
            });
            row.querySelector('.display-actions').classList.remove('d-none');
            row.querySelector('.edit-actions').classList.add('d-none');
        }

        function saveAuthor(row, id, csrfToken) {
            const inputs = row.querySelectorAll('.inline-edit-input');
            const firstName = inputs[0].value.trim();
            const lastName = inputs[1].value.trim();
            if (!firstName || !lastName) {
                displayMessage('First and last name are required.', 'error');
                return;
            }

            fetch(`{{ url('library/settings/authors') }}/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ first_name: firstName, last_name: lastName })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    displayMessage(data.message);
                    const displays = row.querySelectorAll('.display-value');
                    displays[0].textContent = data.author.first_name;
                    displays[1].textContent = data.author.last_name;
                    exitAuthorEditMode(row);
                } else {
                    displayMessage(data.message || 'Error updating author.', 'error');
                }
            })
            .catch(() => displayMessage('An error occurred while updating the author.', 'error'));
        }

        function deleteAuthor(row, id, csrfToken) {
            const name = row.querySelector('.author-first-name .display-value').textContent + ' ' + row.querySelector('.author-last-name .display-value').textContent;
            if (!confirm(`Are you sure you want to delete "${name}"?`)) return;

            fetch(`{{ url('library/settings/authors') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                if (data.success) {
                    displayMessage(data.message);
                    row.remove();
                    renumberTable('authorsTable');
                    updateAuthorBadge(-1);
                } else {
                    displayMessage(data.message || 'Error deleting author.', 'error');
                }
            })
            .catch(() => displayMessage('An error occurred while deleting the author.', 'error'));
        }

        function addAuthorRow(author) {
            const tbody = document.querySelector('#authorsTable tbody');
            const emptyRow = tbody.querySelector('.empty-row');
            if (emptyRow) emptyRow.remove();

            const count = tbody.querySelectorAll('tr:not(.empty-row)').length + 1;
            const tr = document.createElement('tr');
            tr.dataset.id = author.id;
            tr.innerHTML = `
                <td class="row-number">${count}</td>
                <td class="author-first-name">
                    <span class="display-value">${escapeHtml(author.first_name)}</span>
                    <input type="text" class="form-control form-control-sm inline-edit-input d-none" value="${escapeHtml(author.first_name)}" maxlength="100">
                </td>
                <td class="author-last-name">
                    <span class="display-value">${escapeHtml(author.last_name)}</span>
                    <input type="text" class="form-control form-control-sm inline-edit-input d-none" value="${escapeHtml(author.last_name)}" maxlength="100">
                </td>
                <td><span class="count-badge">0</span></td>
                <td>
                    <div class="display-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary edit-author-btn" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-author-btn" title="Delete"><i class="fas fa-trash-alt"></i></button>
                    </div>
                    <div class="edit-actions d-none">
                        <button type="button" class="btn btn-sm btn-success save-author-btn" title="Save"><i class="fas fa-check"></i></button>
                        <button type="button" class="btn btn-sm btn-secondary cancel-author-btn" title="Cancel"><i class="fas fa-times"></i></button>
                    </div>
                </td>`;
            tbody.appendChild(tr);
        }

        function updateAuthorBadge(delta) {
            const badge = document.querySelector('a[href="#authorsTab"] .badge');
            if (badge) badge.textContent = parseInt(badge.textContent) + delta;
        }

        // ========================================
        // Publishers Tab Functions
        // ========================================
        function initializePublishersTab() {
            const csrfToken = '{{ csrf_token() }}';

            // Add publisher
            document.getElementById('addPublisherBtn').addEventListener('click', function() {
                const name = document.getElementById('publisherName').value.trim();
                if (!name) {
                    displayMessage('Please enter a publisher name.', 'error');
                    return;
                }

                this.disabled = true;
                fetch('{{ route("library.settings.store-publisher") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ name: name })
                })
                .then(r => r.json().then(data => ({ ok: r.ok, data })))
                .then(({ ok, data }) => {
                    this.disabled = false;
                    if (data.success) {
                        displayMessage(data.message);
                        addPublisherRow(data.publisher);
                        document.getElementById('publisherName').value = '';
                        document.getElementById('publisherName').focus();
                        updatePublisherBadge(1);
                    } else {
                        displayMessage(data.message || data.errors?.name?.[0] || 'Error adding publisher.', 'error');
                    }
                })
                .catch(() => {
                    this.disabled = false;
                    displayMessage('An error occurred while adding the publisher.', 'error');
                });
            });

            // Enter key in add input
            document.getElementById('publisherName').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('addPublisherBtn').click();
                }
            });

            // Search filter
            document.getElementById('publisherSearch').addEventListener('input', function() {
                const term = this.value.toLowerCase();
                const rows = document.querySelectorAll('#publishersTable tbody tr:not(.empty-row)');
                let idx = 0;
                rows.forEach(row => {
                    const name = row.querySelector('.publisher-name .display-value')?.textContent.toLowerCase() || '';
                    const match = name.includes(term);
                    row.style.display = match ? '' : 'none';
                    if (match) row.querySelector('.row-number').textContent = ++idx;
                });
            });

            // Delegate edit/save/cancel/delete
            document.getElementById('publishersTable').addEventListener('click', function(e) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const row = btn.closest('tr');
                const id = row?.dataset.id;

                if (btn.classList.contains('edit-publisher-btn')) {
                    enterPublisherEditMode(row);
                } else if (btn.classList.contains('cancel-publisher-btn')) {
                    exitPublisherEditMode(row);
                } else if (btn.classList.contains('save-publisher-btn')) {
                    savePublisher(row, id, csrfToken);
                } else if (btn.classList.contains('delete-publisher-btn')) {
                    deletePublisher(row, id, csrfToken);
                }
            });
        }

        function enterPublisherEditMode(row) {
            row.querySelector('.display-value').classList.add('d-none');
            const input = row.querySelector('.inline-edit-input');
            input.classList.remove('d-none');
            input.focus();
            row.querySelector('.display-actions').classList.add('d-none');
            row.querySelector('.edit-actions').classList.remove('d-none');
        }

        function exitPublisherEditMode(row) {
            const display = row.querySelector('.display-value');
            const input = row.querySelector('.inline-edit-input');
            display.classList.remove('d-none');
            input.classList.add('d-none');
            input.value = display.textContent;
            row.querySelector('.display-actions').classList.remove('d-none');
            row.querySelector('.edit-actions').classList.add('d-none');
        }

        function savePublisher(row, id, csrfToken) {
            const name = row.querySelector('.inline-edit-input').value.trim();
            if (!name) {
                displayMessage('Publisher name is required.', 'error');
                return;
            }

            fetch(`{{ url('library/settings/publishers') }}/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ name: name })
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                if (data.success) {
                    displayMessage(data.message);
                    row.querySelector('.display-value').textContent = data.publisher.name;
                    exitPublisherEditMode(row);
                } else {
                    displayMessage(data.message || data.errors?.name?.[0] || 'Error updating publisher.', 'error');
                }
            })
            .catch(() => displayMessage('An error occurred while updating the publisher.', 'error'));
        }

        function deletePublisher(row, id, csrfToken) {
            const name = row.querySelector('.publisher-name .display-value').textContent;
            if (!confirm(`Are you sure you want to delete "${name}"?`)) return;

            fetch(`{{ url('library/settings/publishers') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                if (data.success) {
                    displayMessage(data.message);
                    row.remove();
                    renumberTable('publishersTable');
                    updatePublisherBadge(-1);
                } else {
                    displayMessage(data.message || 'Error deleting publisher.', 'error');
                }
            })
            .catch(() => displayMessage('An error occurred while deleting the publisher.', 'error'));
        }

        function addPublisherRow(publisher) {
            const tbody = document.querySelector('#publishersTable tbody');
            const emptyRow = tbody.querySelector('.empty-row');
            if (emptyRow) emptyRow.remove();

            const count = tbody.querySelectorAll('tr:not(.empty-row)').length + 1;
            const tr = document.createElement('tr');
            tr.dataset.id = publisher.id;
            tr.innerHTML = `
                <td class="row-number">${count}</td>
                <td class="publisher-name">
                    <span class="display-value">${escapeHtml(publisher.name)}</span>
                    <input type="text" class="form-control form-control-sm inline-edit-input d-none" value="${escapeHtml(publisher.name)}" maxlength="150">
                </td>
                <td><span class="count-badge">0</span></td>
                <td>
                    <div class="display-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary edit-publisher-btn" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-publisher-btn" title="Delete"><i class="fas fa-trash-alt"></i></button>
                    </div>
                    <div class="edit-actions d-none">
                        <button type="button" class="btn btn-sm btn-success save-publisher-btn" title="Save"><i class="fas fa-check"></i></button>
                        <button type="button" class="btn btn-sm btn-secondary cancel-publisher-btn" title="Cancel"><i class="fas fa-times"></i></button>
                    </div>
                </td>`;
            tbody.appendChild(tr);
        }

        function updatePublisherBadge(delta) {
            const badge = document.querySelector('a[href="#publishersTab"] .badge');
            if (badge) badge.textContent = parseInt(badge.textContent) + delta;
        }

        // ========================================
        // Shared Helpers
        // ========================================
        function renumberTable(tableId) {
            const rows = document.querySelectorAll(`#${tableId} tbody tr:not(.empty-row)`);
            rows.forEach((row, i) => {
                const cell = row.querySelector('.row-number');
                if (cell) cell.textContent = i + 1;
            });
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // ========================================
        // Common Form Submission
        // ========================================
        function submitSettingsForm(form) {
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');

            submitBtn.classList.add('loading');
            submitBtn.disabled = true;

            fetch('{{ route("library.settings.update") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;

                if (data.success) {
                    displayMessage(data.message || 'Settings saved successfully.');
                } else {
                    displayMessage(data.message || 'Error saving settings', 'error');
                }
            })
            .catch(error => {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                console.error('Error:', error);
                displayMessage('An error occurred while saving settings', 'error');
            });
        }
    </script>
@endsection
