@extends('layouts.master')
@section('title')
    SMS Templates
@endsection
@section('css')
    <style>
        .admissions-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .admissions-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .admissions-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 20px;
        }

        .admissions-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .admissions-body {
            padding: 24px;
        }

        .template-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
            transition: all 0.2s ease;
        }

        .template-card:hover {
            border-color: #4e73df;
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.15);
        }

        .template-card.inactive {
            opacity: 0.6;
            background: #f9fafb;
        }

        .template-name {
            font-weight: 600;
            font-size: 16px;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .template-category {
            display: inline-block;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 500;
            border-radius: 12px;
            background: #e0e7ff;
            color: #4338ca;
            margin-bottom: 8px;
        }

        .template-content {
            font-size: 14px;
            color: #4b5563;
            background: #f9fafb;
            padding: 12px;
            border-radius: 6px;
            margin: 8px 0;
            font-family: 'Courier New', monospace;
        }

        .template-meta {
            font-size: 12px;
            color: #9ca3af;
        }

        .placeholder-tag {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-family: monospace;
            margin: 2px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            padding: 8px 16px;
            font-weight: 500;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .character-counter {
            font-size: 12px;
            color: #6b7280;
        }

        .character-counter.warning {
            color: #f59e0b;
        }

        .character-counter.danger {
            color: #ef4444;
        }

        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

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
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="admissions-container">
            <div class="admissions-header d-flex justify-content-between align-items-center">
                <div>
                    <h4><i class="fas fa-file-alt me-2"></i>SMS Templates</h4>
                    <p>Create and manage reusable SMS message templates</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end">
                        <div class="d-flex gap-3">
                            <div>
                                <span class="d-block" style="font-size: 24px; font-weight: 600;">{{ $templates->total() }}</span>
                                <span style="font-size: 12px; opacity: 0.8;">Total Templates</span>
                            </div>
                            <div class="border-start ps-3" style="border-color: rgba(255,255,255,0.3) !important;">
                                <span class="d-block" style="font-size: 24px; font-weight: 600;">{{ $activeCount ?? $templates->where('is_active', true)->count() }}</span>
                                <span style="font-size: 12px; opacity: 0.8;">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admissions-body">
                <!-- Filters and New Template Button -->
                <div class="row align-items-center mb-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-5 col-md-5 col-sm-6">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" id="searchFilter" placeholder="Search templates..." value="{{ request('search') }}" onkeyup="debounceSearch()">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <select class="form-select" id="categoryFilter" onchange="filterTemplates()">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $key => $label)
                                            <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12">
                                    <button type="button" class="btn btn-light w-100" onclick="resetFilters()">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                                <i class="fas fa-plus me-1"></i> New Template
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Help Text -->
                <div class="help-text">
                    <div class="help-title">SMS Templates</div>
                    <div class="help-content">
                        Create reusable message templates for common SMS communications. Use placeholders to personalize messages dynamically.
                        <div class="mt-2">
                            <strong>Available Placeholders:</strong>
                            @foreach($placeholders as $placeholder => $description)
                                <span class="placeholder-tag" title="{{ $description }}">{{ $placeholder }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Templates List -->
                @if($templates->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No templates found. Create your first template to get started.</p>
                    </div>
                @else
                    <div class="row">
                        @foreach($templates as $template)
                            <div class="col-md-6">
                                <div class="template-card {{ !$template->is_active ? 'inactive' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="template-name">{{ $template->name }}</div>
                                            <span class="template-category">{{ $categories[$template->category] ?? $template->category }}</span>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="#" onclick="editTemplate({{ $template->id }})"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="toggleTemplate({{ $template->id }})"><i class="fas fa-toggle-{{ $template->is_active ? 'on' : 'off' }} me-2"></i>{{ $template->is_active ? 'Deactivate' : 'Activate' }}</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteTemplate({{ $template->id }})"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>

                                    @if($template->description)
                                        <p class="text-muted small mb-2">{{ $template->description }}</p>
                                    @endif

                                    <div class="template-content">{{ Str::limit($template->content, 150) }}</div>

                                    <div class="template-meta d-flex justify-content-between">
                                        <span><i class="fas fa-text-width me-1"></i>{{ strlen($template->content) }} chars ({{ ceil(strlen($template->content) / 160) }} SMS)</span>
                                        <span><i class="fas fa-chart-bar me-1"></i>Used {{ $template->usage_count }} times</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $templates->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Create Template Modal -->
    <div class="modal fade" id="createTemplateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="createTemplateForm" method="POST" action="{{ route('sms-templates.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Create SMS Template</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required maxlength="255" placeholder="e.g., Fee Reminder">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category" required>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="content" rows="4" required maxlength="480" placeholder="Type your message here. Use placeholders like {student_name} for dynamic content." oninput="updateCharacterCount(this, 'createCharCount')"></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Use placeholders from the list above</small>
                                <span id="createCharCount" class="character-counter">0/480 characters (0 SMS)</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <input type="text" class="form-control" name="description" maxlength="500" placeholder="Brief description of when to use this template">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Template Modal -->
    <div class="modal fade" id="editTemplateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editTemplateForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit SMS Template</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="editName" required maxlength="255">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category" id="editCategory" required>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="content" id="editContent" rows="4" required maxlength="480" oninput="updateCharacterCount(this, 'editCharCount')"></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Use placeholders from the list above</small>
                                <span id="editCharCount" class="character-counter">0/480 characters (0 SMS)</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <input type="text" class="form-control" name="description" id="editDescription" maxlength="500">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const templates = @json($templates->items());

        function updateCharacterCount(textarea, counterId) {
            const count = textarea.value.length;
            const smsUnits = Math.ceil(count / 160) || 0;
            const counter = document.getElementById(counterId);

            counter.textContent = `${count}/480 characters (${smsUnits} SMS)`;

            counter.classList.remove('warning', 'danger');
            if (count > 400) {
                counter.classList.add('danger');
            } else if (count > 320) {
                counter.classList.add('warning');
            }
        }

        function filterTemplates() {
            const category = document.getElementById('categoryFilter').value;
            const search = document.getElementById('searchFilter').value;
            let url = '{{ route("sms-templates.index") }}?';

            if (category) url += `category=${category}&`;
            if (search) url += `search=${encodeURIComponent(search)}`;

            window.location.href = url;
        }

        let searchTimeout;
        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(filterTemplates, 500);
        }

        function resetFilters() {
            document.getElementById('searchFilter').value = '';
            document.getElementById('categoryFilter').value = '';
            window.location.href = '{{ route("sms-templates.index") }}';
        }

        function editTemplate(id) {
            const template = templates.find(t => t.id === id);
            if (!template) return;

            document.getElementById('editName').value = template.name;
            document.getElementById('editCategory').value = template.category;
            document.getElementById('editContent').value = template.content;
            document.getElementById('editDescription').value = template.description || '';
            document.getElementById('editTemplateForm').action = `{{ url('notifications/sms-templates') }}/${id}`;

            updateCharacterCount(document.getElementById('editContent'), 'editCharCount');

            new bootstrap.Modal(document.getElementById('editTemplateModal')).show();
        }

        function toggleTemplate(id) {
            fetch(`{{ url('notifications/sms-templates') }}/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }

        function deleteTemplate(id) {
            if (!confirm('Are you sure you want to delete this template?')) return;

            fetch(`{{ url('notifications/sms-templates') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    </script>
@endsection
