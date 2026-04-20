@extends('layouts.master')
@section('title')
    Version History - {{ $timetable->name }}
@endsection
@section('css')
    <style>
        .timetable-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .timetable-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .timetable-body {
            padding: 24px;
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

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-draft { background: #fef3c7; color: #92400e; }
        .status-published { background: #d1fae5; color: #065f46; }
        .status-archived { background: #f3f4f6; color: #4b5563; }

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

        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .version-note {
            font-size: 12px;
            color: #6b7280;
            font-style: italic;
        }

        .back-link {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 14px;
        }

        .back-link:hover {
            color: white;
        }
    </style>
@endsection
@section('content')
    <div class="timetable-container">
        <div class="timetable-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <a href="{{ route('timetable.index') }}" class="back-link">
                        <i class="bx bx-arrow-back"></i> Back to Timetables
                    </a>
                    <h3 style="margin:8px 0 0 0;">Version History</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        {{ $timetable->name }}
                        <span class="status-badge status-{{ $timetable->status }}" style="margin-left: 8px;">{{ $timetable->status }}</span>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="text-white opacity-75">{{ $versions->count() }} version(s)</span>
                </div>
            </div>
        </div>
        <div class="timetable-body">
            <div class="help-text">
                <div class="help-title">About Versions</div>
                <div class="help-content">
                    Each time a timetable is published, a snapshot of all slots is saved as a version.
                    You can restore any previous version, which will replace all current slots and set the timetable to draft status for review before re-publishing.
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Slots</th>
                            <th>Published By</th>
                            <th>Published At</th>
                            <th>Notes</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($versions as $version)
                            <tr>
                                <td><strong>v{{ $version->version_number }}</strong></td>
                                <td>{{ $version->slot_count }} slots</td>
                                <td>{{ $version->publisher->firstname ?? '' }} {{ $version->publisher->lastname ?? '' }}</td>
                                <td>{{ $version->published_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if ($version->notes)
                                        <span class="version-note">{{ $version->notes }}</span>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-warning rollback-btn"
                                            data-id="{{ $version->id }}"
                                            data-version="{{ $version->version_number }}"
                                            data-slots="{{ $version->slot_count }}"
                                            title="Restore this version">
                                            <i class="bx bx-revision me-1"></i> Restore
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="bx bx-history" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0" style="font-size: 15px;">No versions yet. Versions are created each time the timetable is published.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.querySelectorAll('.rollback-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const versionId = this.dataset.id;
                const versionNum = this.dataset.version;
                const slotCount = this.dataset.slots;
                const timetableId = {{ $timetable->id }};

                Swal.fire({
                    title: 'Restore Version?',
                    html: `Restoring to <strong>v${versionNum}</strong> (${slotCount} slots) will:<br><br>` +
                        `<ul style="text-align:left;">` +
                        `<li>Save a snapshot of the current state as a safety backup</li>` +
                        `<li>Replace all current slots with the selected version</li>` +
                        `<li>Set the timetable to <strong>draft</strong> status for review</li>` +
                        `</ul>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    confirmButtonText: 'Yes, restore it',
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/timetable/publishing/${timetableId}/rollback/${versionId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        })
                        .then(r => {
                            if (!r.ok) return r.json().then(d => Promise.reject(d));
                            return r.json();
                        })
                        .then(data => {
                            Swal.fire('Restored!', data.message, 'success')
                                .then(() => window.location.reload());
                        })
                        .catch(err => {
                            Swal.fire('Error', err.message || 'Failed to restore version.', 'error');
                        });
                    }
                });
            });
        });
    </script>
@endsection
