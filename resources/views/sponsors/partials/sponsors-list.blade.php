<style>
    /* Table Header Styling from Admissions Index */
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

    .sponsor-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sponsor-avatar-placeholder {
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
</style>

<div class="table-responsive">
    <table id="parents-table" class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Gender</th>
                <th>Email</th>
                <th>Nationality</th>
                <th>Phone</th>
                <th>Status</th>
                @if (!session('is_past_term'))
                    <th class="text-end">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($sponsors as $sponsor)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <div class="sponsor-cell">
                            <div class="sponsor-avatar-placeholder">
                                {{ strtoupper(substr($sponsor->first_name ?? 'N', 0, 1)) }}{{ strtoupper(substr($sponsor->last_name ?? 'A', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $sponsor->fullName ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if ($sponsor->gender == 'M')
                            <span class="gender-male"><i class="bx bx-male-sign"></i> {{ $sponsor->gender }}</span>
                        @else
                            <span class="gender-female"><i class="bx bx-female-sign"></i>
                                {{ $sponsor->gender }}</span>
                        @endif
                    </td>
                    <td>{{ $sponsor->email ?? '' }}</td>
                    <td>{{ $sponsor->nationality ?? '' }}</td>
                    <td>{{ $sponsor->formatted_phone ?? '' }}</td>
                    <td>{{ $sponsor->status ?? '' }}</td>
@php
    $smsEnabled = $communicationChannels['sms_enabled'] ?? false;
@endphp
                    @if (!session('is_past_term'))
                        <td class="text-end">
                            <div class="action-buttons">
                                <a href="{{ route('sponsors.sponsor-edit', $sponsor->id) }}"
                                    class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="View & Edit">
                                    <i class="bx bx-edit-alt"></i>
                                </a>
                                @can('sms-admin')
                                    @if ($smsEnabled && $sponsor->hasValidPhoneNumber())
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#sendSMSModalSponsor"
                                            data-recipient-id="{{ $sponsor->id }}" data-recipient-type="sponsor"
                                            title="Send SMS">
                                            <i class="bx bx-message-rounded-dots"></i>
                                        </button>
                                    @endif
                                    @if ($sponsor->hasValidEmail())
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                            data-bs-toggle="modal" data-bs-target="#sendEmailModal"
                                            data-recipient-email="{{ $sponsor->email }}"
                                            data-recipient-id="{{ $sponsor->id }}" data-recipient-type="sponsor"
                                            title="Send Email">
                                            <i class="bx bx-mail-send"></i>
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr id="no-sponsors-row">
                    <td colspan="8">
                        <div class="text-center text-muted" style="padding: 40px 0;">
                            <i class="fas fa-users" style="font-size: 48px; opacity: 0.3;"></i>
                            <p class="mt-3 mb-0" style="font-size: 15px;">No parents/sponsors found</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        initializeSponsorsTooltips();
    });

    function initializeSponsorsTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('#parents-table [data-bs-toggle="tooltip"]'));

        tooltipTriggerList.forEach(function(el) {
            var tooltip = bootstrap.Tooltip.getInstance(el);
            if (tooltip) {
                tooltip.dispose();
            }
        });

        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
</script>
