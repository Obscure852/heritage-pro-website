@php
    $isEdit = isset($plan);
@endphp

<h3 class="section-title">Plan Details</h3>
<div class="form-grid">
    <div class="form-group">
        <label class="form-label">Employee <span class="text-danger">*</span></label>
        <select name="user_id" class="form-select" {{ $isEdit ? 'disabled' : '' }} required>
            @foreach ($availableUsers as $availableUser)
                <option value="{{ $availableUser->id }}"
                    @selected((int) old('user_id', $plan->user_id ?? auth()->id()) === (int) $availableUser->id)>
                    {{ $availableUser->full_name }}{{ $availableUser->position ? ' - ' . $availableUser->position : '' }}
                </option>
            @endforeach
        </select>
        @if ($isEdit)
            <input type="hidden" name="user_id" value="{{ $plan->user_id }}">
        @endif
    </div>

    <div class="form-group">
        <label class="form-label">Template <span class="text-danger">*</span></label>
        @if ($isEdit)
            <input type="text" class="form-control" value="{{ $plan->template->name }} (v{{ $plan->template->version }})" disabled>
        @else
            <select name="template_id" class="form-select" required>
                @foreach ($templates as $template)
                    <option value="{{ $template->id }}" @selected((int) old('template_id', $templates->first()?->id) === (int) $template->id)>
                        {{ $template->name }}
                    </option>
                @endforeach
            </select>
        @endif
    </div>

    <div class="form-group">
        <label class="form-label">Plan Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select" required>
            @foreach (['draft' => 'Draft', 'active' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $statusValue => $statusLabel)
                <option value="{{ $statusValue }}" @selected(old('status', $plan->status ?? 'draft') === $statusValue)>
                    {{ $statusLabel }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-grid mt-3">
    <div class="form-group">
        <label class="form-label">Start Date <span class="text-danger">*</span></label>
        <input type="date" name="plan_period_start" class="form-control"
            value="{{ old('plan_period_start', isset($plan) ? $plan->plan_period_start->format('Y-m-d') : ($suggestedDates['start'] ?? now()->startOfYear())->format('Y-m-d')) }}"
            required>
    </div>

    <div class="form-group">
        <label class="form-label">End Date <span class="text-danger">*</span></label>
        <input type="date" name="plan_period_end" class="form-control"
            value="{{ old('plan_period_end', isset($plan) ? $plan->plan_period_end->format('Y-m-d') : ($suggestedDates['end'] ?? now()->endOfYear())->format('Y-m-d')) }}"
            required>
    </div>

    <div class="form-group">
        <label class="form-label">Current Period Key</label>
        @if ($isEdit)
            <select name="current_period_key" class="form-select">
                <option value="">None</option>
                @foreach ($plan->template->periods as $period)
                    <option value="{{ $period->key }}" @selected(old('current_period_key', $plan->current_period_key) === $period->key)>
                        {{ $period->label }}
                    </option>
                @endforeach
            </select>
        @else
            <input type="text" name="current_period_key" class="form-control"
                value="{{ old('current_period_key') }}"
                placeholder="Leave blank to use the first template period">
        @endif
    </div>
</div>

@if ($isEdit)
    <h3 class="section-title">Workflow Assignment</h3>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Supervisor</label>
            <select name="supervisor_id" class="form-select">
                <option value="">Not assigned</option>
                @foreach ($availableUsers as $availableUser)
                    <option value="{{ $availableUser->id }}"
                        @selected((int) old('supervisor_id', $plan->supervisor_id) === (int) $availableUser->id)>
                        {{ $availableUser->full_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
@endif

<div class="form-actions">
    <a class="btn btn-secondary" href="{{ route('staff.pdp.plans.index') }}">
        <i class="bx bx-x"></i> Cancel
    </a>
    @include('pdp.partials.submit-button', [
        'label' => $isEdit ? 'Save Plan' : 'Create Plan',
        'loadingText' => $isEdit ? 'Saving plan...' : 'Creating plan...',
        'icon' => 'fas fa-save',
    ])
</div>
