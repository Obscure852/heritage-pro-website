@extends('layouts.crm')

@section('title', 'Edit CRM User')
@section('crm_heading', $user->name)
@section('crm_subheading', 'Manage profile details, qualifications, module access, login history, and signature files for this staff account.')

@section('crm_actions')
    @if ($canAdminUsers)
        <a href="{{ route('crm.users.settings.index') }}" class="btn btn-light crm-btn-light">
            <i class="bx bx-cog"></i> Users settings
        </a>
    @endif
    <a href="{{ route('crm.users.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to users
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.users._tabs', ['user' => $user, 'activeTab' => $activeTab])

        @if ($activeTab === 'profile')
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Profile</p>
                        <h2>Core staff details</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('crm.users.update', $user) }}" class="crm-form">
                    @csrf
                    @method('PATCH')

                    @include('crm.users._directory-form-fields', [
                        'formMode' => 'edit',
                    ])

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-loading" @disabled(! $canEditUser)>
                            <span class="btn-text"><i class="fas fa-save"></i> Save profile</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
                        </button>
                    </div>
                </form>
            </section>
        @elseif ($activeTab === 'qualifications')
            <div class="crm-grid cols-2">
                @if ($canEditUser)
                    <section class="crm-card">
                        <div class="crm-card-title">
                            <div>
                                <p class="crm-kicker">Qualifications</p>
                                <h2>{{ $editingQualification ? 'Edit qualification' : 'Add qualification' }}</h2>
                            </div>
                        </div>

                        <form method="POST" action="{{ $editingQualification ? route('crm.users.qualifications.update', [$user, $editingQualification]) : route('crm.users.qualifications.store', $user) }}" enctype="multipart/form-data" class="crm-form">
                            @csrf
                            @if ($editingQualification)
                                @method('PATCH')
                            @endif

                            <div class="crm-field-grid">
                                <div class="crm-field">
                                    <label for="title">Title</label>
                                    <input id="title" name="title" value="{{ old('title', $editingQualification->title ?? '') }}" required>
                                </div>
                                <div class="crm-field">
                                    <label for="level">Level</label>
                                    <input id="level" name="level" value="{{ old('level', $editingQualification->level ?? '') }}">
                                </div>
                                <div class="crm-field">
                                    <label for="institution">Institution</label>
                                    <input id="institution" name="institution" value="{{ old('institution', $editingQualification->institution ?? '') }}">
                                </div>
                                <div class="crm-field">
                                    <label for="start_date">Start date</label>
                                    <input id="start_date" name="start_date" type="date" value="{{ old('start_date', optional($editingQualification->start_date ?? null)?->format('Y-m-d')) }}">
                                </div>
                                <div class="crm-field">
                                    <label for="completion_date">Completion date</label>
                                    <input id="completion_date" name="completion_date" type="date" value="{{ old('completion_date', optional($editingQualification->completion_date ?? null)?->format('Y-m-d')) }}">
                                </div>
                                <div class="crm-field full">
                                    <label for="notes">Notes</label>
                                    <textarea id="notes" name="notes">{{ old('notes', $editingQualification->notes ?? '') }}</textarea>
                                </div>
                                <div class="crm-field full">
                                    <label for="attachments">Attachments</label>
                                    <div class="crm-dropzone" data-dropzone>
                                        <input id="attachments" name="attachments[]" type="file" class="crm-dropzone-input" multiple accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" data-dropzone-input>
                                        <div class="crm-dropzone-copy">
                                            <span class="crm-dropzone-icon"><i class="fas fa-file-upload"></i></span>
                                            <strong>Drop qualification files here</strong>
                                            <p>Attach supporting certificates, transcripts, or scans.</p>
                                        </div>
                                        <div class="crm-dropzone-list" data-dropzone-list>
                                            <div class="crm-dropzone-empty">No new files selected yet.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                @if ($editingQualification)
                                    <a href="{{ route('crm.users.edit', ['user' => $user, 'tab' => 'qualifications']) }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Clear</a>
                                @endif
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-save"></i> {{ $editingQualification ? 'Save qualification' : 'Add qualification' }}</span>
                                    <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
                                </button>
                            </div>
                        </form>
                    </section>
                @endif

                <section class="crm-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Existing records</p>
                            <h2>Qualifications and files</h2>
                        </div>
                    </div>

                    @if ($user->qualifications->isEmpty())
                        <div class="crm-empty">No qualification records have been added yet.</div>
                    @else
                        <div class="crm-stack">
                            @foreach ($user->qualifications as $qualification)
                                <article class="crm-list-item">
                                    <div class="crm-list-item-header">
                                        <div>
                                            <strong>{{ $qualification->title }}</strong>
                                            <div class="crm-inline">
                                                @if ($qualification->level)
                                                    <span class="crm-pill primary">{{ $qualification->level }}</span>
                                                @endif
                                                @if ($qualification->institution)
                                                    <span class="crm-pill muted">{{ $qualification->institution }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if ($canEditUser)
                                            <div class="crm-action-row">
                                                <a href="{{ route('crm.users.edit', ['user' => $user, 'tab' => 'qualifications', 'qualification' => $qualification->id]) }}" class="btn crm-icon-action" title="Edit qualification" aria-label="Edit qualification">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @include('crm.partials.delete-button', [
                                                    'action' => route('crm.users.qualifications.destroy', [$user, $qualification]),
                                                    'message' => 'Are you sure you want to permanently delete this qualification?',
                                                    'label' => 'Delete qualification',
                                                    'iconOnly' => true,
                                                ])
                                            </div>
                                        @endif
                                    </div>
                                    <div class="crm-muted-copy">
                                        {{ $qualification->start_date?->format('d M Y') ?: 'Unknown start' }} to {{ $qualification->completion_date?->format('d M Y') ?: 'Present' }}
                                    </div>
                                    @if ($qualification->notes)
                                        <p>{{ $qualification->notes }}</p>
                                    @endif
                                    @if ($qualification->attachments->isNotEmpty())
                                        <div class="crm-stack-sm">
                                            @foreach ($qualification->attachments as $attachment)
                                                <div class="crm-file-row">
                                                    <div>
                                                        <strong>{{ $attachment->original_name }}</strong>
                                                        <span class="crm-muted">{{ $attachment->extensionLabel() }} · {{ $attachment->formattedSize() }}</span>
                                                    </div>
                                                    <div class="crm-action-row">
                                                        <a href="{{ route('crm.users.qualifications.attachments.open', [$user, $qualification, $attachment]) }}" class="btn btn-light crm-btn-light btn-sm" target="_blank" rel="noopener">
                                                            <i class="bx bx-show"></i> Open
                                                        </a>
                                                        <a href="{{ route('crm.users.qualifications.attachments.download', [$user, $qualification, $attachment]) }}" class="btn btn-light crm-btn-light btn-sm">
                                                            <i class="bx bx-download"></i> Download
                                                        </a>
                                                        @if ($canEditUser)
                                                            @include('crm.partials.delete-button', [
                                                                'action' => route('crm.users.qualifications.attachments.destroy', [$user, $qualification, $attachment]),
                                                                'message' => 'Are you sure you want to permanently delete this file?',
                                                                'label' => 'Delete file',
                                                                'iconOnly' => true,
                                                            ])
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        @elseif ($activeTab === 'roles')
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Roles allocation</p>
                        <h2>Role defaults and module permissions</h2>
                    </div>
                </div>

                @if ($canAdminUsers)
                    <form method="POST" action="{{ route('crm.users.roles.update', $user) }}" class="crm-form">
                        @csrf
                        @method('PATCH')

                        <div class="crm-field-grid">
                            <div class="crm-field full">
                                <label for="role">Top-level role</label>
                                <select id="role" name="role">
                                    @foreach ($roles as $value => $label)
                                        <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="crm-table-wrap">
                            <table class="crm-table">
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th>No access</th>
                                        @foreach ($permissionChoices as $value => $label)
                                            <th>{{ $label }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($modules as $module)
                                        @php($selectedLevel = old('module_permissions.' . $module['key'], $modulePermissionLevels[$module['key']] ?? ''))
                                        <tr>
                                            <td>
                                                <strong>{{ $module['label'] }}</strong>
                                                <span class="crm-muted">{{ $module['caption'] ?? 'CRM module' }}</span>
                                            </td>
                                            <td>
                                                <input type="radio" name="module_permissions[{{ $module['key'] }}]" value="" @checked($selectedLevel === null || $selectedLevel === '')>
                                            </td>
                                            @foreach ($permissionChoices as $value => $label)
                                                <td>
                                                    <input type="radio" name="module_permissions[{{ $module['key'] }}]" value="{{ $value }}" @checked($selectedLevel === $value)>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="form-actions">
                            @if (! auth()->user()->is($user))
                                @include('crm.partials.delete-button', [
                                    'action' => route('crm.users.destroy', $user),
                                    'message' => 'Are you sure you want to permanently delete this CRM user?',
                                    'label' => 'Delete user',
                                ])
                            @endif
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save"></i> Save access matrix</span>
                                <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
                            </button>
                        </div>
                    </form>
                @else
                    <div class="crm-help">
                        <div class="crm-help-title">Read-only access</div>
                        <div class="crm-help-content">Only users with admin permission for the Users module can change role allocation. The current effective permissions are shown below.</div>
                    </div>

                    <div class="crm-table-wrap">
                        <table class="crm-table">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Permission</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($modules as $module)
                                    <tr>
                                        <td>{{ $module['label'] }}</td>
                                        <td>{{ $permissionChoices[$modulePermissionLevels[$module['key']] ?? ''] ?? 'No access' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @elseif ($activeTab === 'history')
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Login history</p>
                        <h2>Recent authentication events</h2>
                    </div>
                </div>

                @if ($loginEvents->isEmpty())
                    <div class="crm-empty">No login history has been recorded for this user yet.</div>
                @else
                    <div class="crm-table-wrap">
                        <table class="crm-table">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Occurred at</th>
                                    <th>IP address</th>
                                    <th>User agent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($loginEvents as $event)
                                    <tr>
                                        <td>{{ $loginEventTypes[$event->event_type] ?? ucfirst(str_replace('_', ' ', $event->event_type)) }}</td>
                                        <td>{{ $event->occurred_at?->format('d M Y H:i') ?: 'Unknown' }}</td>
                                        <td>{{ $event->ip_address ?: 'Unknown' }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($event->user_agent ?: 'Unknown', 90) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @include('crm.partials.pager', ['paginator' => $loginEvents])
                @endif
            </section>
        @else
            <div class="crm-grid cols-2">
                @if ($canEditUser)
                    <section class="crm-card">
                        <div class="crm-card-title">
                            <div>
                                <p class="crm-kicker">Settings</p>
                                <h2>Upload staff signatures</h2>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('crm.users.signatures.store', $user) }}" enctype="multipart/form-data" class="crm-form">
                            @csrf

                            <div class="crm-field-grid">
                                <div class="crm-field">
                                    <label for="label">Label</label>
                                    <input id="label" name="label" value="{{ old('label') }}" placeholder="e.g. Default signature" required>
                                </div>
                                <div class="crm-field">
                                    <label for="file">Signature file</label>
                                    <input id="file" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-save"></i> Upload signature</span>
                                    <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
                                </button>
                            </div>
                        </form>
                    </section>
                @endif

                <section class="crm-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Stored files</p>
                            <h2>Signature library</h2>
                        </div>
                    </div>

                    @if ($user->signatures->isEmpty())
                        <div class="crm-empty">No signature files have been uploaded yet.</div>
                    @else
                        <div class="crm-stack">
                            @foreach ($user->signatures as $signature)
                                <div class="crm-file-row">
                                    <div>
                                        <strong>{{ $signature->label }}</strong>
                                        <div class="crm-inline">
                                            @if ($signature->is_default)
                                                <span class="crm-pill success">Default</span>
                                            @endif
                                            <span class="crm-pill muted">{{ $signature->formattedSize() }}</span>
                                        </div>
                                        <span class="crm-muted">{{ $signature->original_name }}</span>
                                    </div>
                                    <div class="crm-action-row">
                                        <a href="{{ route('crm.users.signatures.open', [$user, $signature]) }}" class="btn btn-light crm-btn-light btn-sm" target="_blank" rel="noopener">
                                            <i class="bx bx-show"></i> Open
                                        </a>
                                        <a href="{{ route('crm.users.signatures.download', [$user, $signature]) }}" class="btn btn-light crm-btn-light btn-sm">
                                            <i class="bx bx-download"></i> Download
                                        </a>
                                        @if ($canEditUser && ! $signature->is_default)
                                            <form method="POST" action="{{ route('crm.users.signatures.default', [$user, $signature]) }}" class="crm-inline-form">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-light crm-btn-light btn-sm">
                                                    <i class="bx bx-check"></i> Make default
                                                </button>
                                            </form>
                                        @endif
                                        @if ($canEditUser)
                                            @include('crm.partials.delete-button', [
                                                'action' => route('crm.users.signatures.destroy', [$user, $signature]),
                                                'message' => 'Are you sure you want to permanently delete this signature file?',
                                                'label' => 'Delete signature',
                                                'iconOnly' => true,
                                            ])
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        @endif
    </div>
@endsection
