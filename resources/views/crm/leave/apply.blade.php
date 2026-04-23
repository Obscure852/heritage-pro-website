@extends('layouts.crm')

@section('title', 'Apply for Leave')
@section('crm_heading', 'Apply for Leave')
@section('crm_subheading', 'Submit a new leave request for approval by your supervisor.')

@section('content')
    <div class="crm-stack">
        @include('crm.partials.helper-text', [
            'title' => 'Leave Application',
            'content' => 'Select your leave type and dates. Working days are calculated automatically based on your shift schedule, excluding weekends and public holidays.',
        ])

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">New Request</p>
                    <h2>Leave application form</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('crm.leave.store') }}" enctype="multipart/form-data" id="leave-apply-form">
                @csrf

                <div class="crm-form-grid cols-2">
                    <div class="crm-field">
                        <label for="leave_type_id">Leave Type <span class="text-danger">*</span></label>
                        <select id="leave_type_id" name="leave_type_id" required>
                            <option value="">Select leave type</option>
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->id }}"
                                    data-allow-half="{{ $type->allow_half_day ? '1' : '0' }}"
                                    data-requires-attachment="{{ $type->requires_attachment ? '1' : '0' }}"
                                    @selected(old('leave_type_id') == $type->id)>
                                    {{ $type->name }}
                                    @if ($type->default_days_per_year !== null)
                                        ({{ number_format((float) $type->default_days_per_year, 1) }}d/yr)
                                    @else
                                        (Unlimited)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('leave_type_id')
                            <div class="crm-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="crm-field">
                        <label>Balance</label>
                        <div id="balance-preview" class="crm-muted-copy" style="padding: 8px 0;">
                            Select a leave type to see your balance.
                        </div>
                    </div>

                    <div class="crm-field">
                        <label for="start_date">Start Date <span class="text-danger">*</span></label>
                        <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                        @error('start_date')
                            <div class="crm-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="crm-field">
                        <label for="end_date">End Date <span class="text-danger">*</span></label>
                        <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                        @error('end_date')
                            <div class="crm-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="crm-field" id="start-half-field" style="display: none;">
                        <label for="start_half">Start Date Half</label>
                        <select id="start_half" name="start_half">
                            <option value="full">Full Day</option>
                            <option value="first_half">First Half (Morning)</option>
                            <option value="second_half">Second Half (Afternoon)</option>
                        </select>
                    </div>

                    <div class="crm-field" id="end-half-field" style="display: none;">
                        <label for="end_half">End Date Half</label>
                        <select id="end_half" name="end_half">
                            <option value="full">Full Day</option>
                            <option value="first_half">First Half (Morning)</option>
                            <option value="second_half">Second Half (Afternoon)</option>
                        </select>
                    </div>
                </div>

                <div id="days-preview" style="display: none; margin: 16px 0; padding: 12px 16px; background: #f0f9ff; border-radius: 3px; border-left: 3px solid #3b82f6;">
                    <strong id="total-days-display">0</strong> working day(s) will be deducted.
                    <span id="balance-warning" style="display: none; color: #f06548; font-weight: 600;"> — Insufficient balance!</span>
                </div>

                <div class="crm-form-grid cols-1" style="margin-top: 16px;">
                    <div class="crm-field">
                        <label for="reason">Reason <span class="text-danger">*</span></label>
                        <textarea id="reason" name="reason" rows="3" required placeholder="Briefly explain the reason for your leave request...">{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="crm-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="crm-field" id="attachments-field">
                        <label for="attachments">Attachments <span id="attachment-required-label" class="text-danger" style="display: none;">*</span></label>
                        <input type="file" id="attachments" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="crm-muted-copy" style="margin-top: 4px;">PDF, images, or Word documents. Max 5MB each, up to 5 files.</div>
                        @error('attachments.*')
                            <div class="crm-field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.leave.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Back</a>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="bx bx-send"></i> Submit Request</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Submitting...
                        </span>
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('leave-apply-form');
    const typeSelect = document.getElementById('leave_type_id');
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    const startHalf = document.getElementById('start_half');
    const endHalf = document.getElementById('end_half');
    const startHalfField = document.getElementById('start-half-field');
    const endHalfField = document.getElementById('end-half-field');
    const daysPreview = document.getElementById('days-preview');
    const totalDaysDisplay = document.getElementById('total-days-display');
    const balanceWarning = document.getElementById('balance-warning');
    const balancePreview = document.getElementById('balance-preview');

    @php
        $balanceMap = [];
        foreach ($balances as $b) {
            $balanceMap[$b->leave_type_id] = [
                'available' => $b->effective_available_days,
                'entitled' => (float) $b->entitled_days + (float) $b->carried_over_days + (float) $b->adjustment_days,
                'used' => (float) $b->used_days,
                'pending' => (float) $b->pending_days,
            ];
        }
    @endphp
    const balances = @json($balanceMap);

    typeSelect.addEventListener('change', function() {
        const opt = typeSelect.options[typeSelect.selectedIndex];
        const allowHalf = opt.dataset.allowHalf === '1';

        startHalfField.style.display = allowHalf ? '' : 'none';
        endHalfField.style.display = allowHalf ? '' : 'none';

        if (!allowHalf) {
            startHalf.value = 'full';
            endHalf.value = 'full';
        }

        const bal = balances[typeSelect.value];
        if (bal) {
            balancePreview.innerHTML = '<strong>' + bal.available.toFixed(1) + '</strong> days available (' + bal.entitled.toFixed(1) + ' entitled, ' + bal.used.toFixed(1) + ' used, ' + bal.pending.toFixed(1) + ' pending)';
        } else {
            balancePreview.textContent = 'Select a leave type to see your balance.';
        }

        calculateDays();
    });

    [startDate, endDate, startHalf, endHalf].forEach(el => el.addEventListener('change', calculateDays));

    function calculateDays() {
        if (!typeSelect.value || !startDate.value || !endDate.value) {
            daysPreview.style.display = 'none';
            return;
        }

        fetch('{{ route("crm.leave.calculate-days") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                leave_type_id: typeSelect.value,
                start_date: startDate.value,
                end_date: endDate.value,
                start_half: startHalf.value,
                end_half: endHalf.value,
            }),
        })
        .then(r => r.json())
        .then(data => {
            daysPreview.style.display = '';
            totalDaysDisplay.textContent = data.total_days.toFixed(1);
            balanceWarning.style.display = data.has_enough ? 'none' : '';
        })
        .catch(() => {
            daysPreview.style.display = 'none';
        });
    }

    form.addEventListener('submit', function() {
        const submitBtn = form.querySelector('button[type="submit"].btn-loading');
        if (submitBtn) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        }
    });
});
</script>
@endpush
