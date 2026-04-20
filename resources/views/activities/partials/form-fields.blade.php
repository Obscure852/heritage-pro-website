@php
    $selectedTermLabel = $selectedTerm
        ? 'Term ' . $selectedTerm->term . ' - ' . $selectedTerm->year
        : 'No active term selected';
@endphp

<h3 class="section-title">Activity Basics</h3>
<div class="form-grid">
    <div class="form-group">
        <label class="form-label" for="name">Activity Name <span class="text-danger">*</span></label>
        <div class="input-icon-group">
            <i class="fas fa-layer-group input-icon"></i>
            <input type="text"
                class="form-control @error('name') is-invalid @enderror"
                id="name"
                name="name"
                value="{{ old('name', $activity->name) }}"
                placeholder="Debate Club"
                required>
        </div>
        @error('name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="code">Activity Code <span class="text-danger">*</span></label>
        <div class="input-icon-group">
            <i class="fas fa-hashtag input-icon"></i>
            <input type="text"
                class="form-control @error('code') is-invalid @enderror"
                id="code"
                name="code"
                value="{{ old('code', $activity->code) }}"
                placeholder="DEBATE-01"
                required>
        </div>
        @error('code')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="term_context">Selected Term</label>
        <div class="input-icon-group">
            <i class="fas fa-calendar input-icon"></i>
            <input type="text"
                class="form-control"
                id="term_context"
                value="{{ $selectedTermLabel }}"
                placeholder="No active term selected"
                readonly>
        </div>
    </div>
</div>

<div class="form-grid">
    <div class="form-group">
        <label class="form-label" for="category">Category <span class="text-danger">*</span></label>
        <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" data-trigger required>
            <option value="">Select category</option>
            @foreach ($categories as $key => $label)
                <option value="{{ $key }}" {{ old('category', $activity->category) === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('category')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="delivery_mode">Delivery Mode <span class="text-danger">*</span></label>
        <select class="form-select @error('delivery_mode') is-invalid @enderror" id="delivery_mode" name="delivery_mode" data-trigger required>
            <option value="">Select delivery mode</option>
            @foreach ($deliveryModes as $key => $label)
                <option value="{{ $key }}" {{ old('delivery_mode', $activity->delivery_mode) === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('delivery_mode')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="participation_mode">Participation Mode <span class="text-danger">*</span></label>
        <select class="form-select @error('participation_mode') is-invalid @enderror" id="participation_mode" name="participation_mode" data-trigger required>
            <option value="">Select participation mode</option>
            @foreach ($participationModes as $key => $label)
                <option value="{{ $key }}" {{ old('participation_mode', $activity->participation_mode) === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('participation_mode')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="result_mode">Result Mode <span class="text-danger">*</span></label>
        <select class="form-select @error('result_mode') is-invalid @enderror" id="result_mode" name="result_mode" data-trigger required>
            <option value="">Select result mode</option>
            @foreach ($resultModes as $key => $label)
                <option value="{{ $key }}" {{ old('result_mode', $activity->result_mode) === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('result_mode')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

<h3 class="section-title">Operations and Billing</h3>
<div class="form-grid">
    <div class="form-group">
        <label class="form-label" for="default_location">Default Location</label>
        <div class="input-icon-group">
            <i class="fas fa-map-marker-alt input-icon"></i>
            <input type="text"
                class="form-control @error('default_location') is-invalid @enderror"
                id="default_location"
                name="default_location"
                value="{{ old('default_location', $activity->default_location) }}"
                placeholder="Assembly Hall">
        </div>
        @error('default_location')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="capacity">Capacity</label>
        <div class="input-icon-group">
            <i class="fas fa-users input-icon"></i>
            <input type="number"
                class="form-control @error('capacity') is-invalid @enderror"
                id="capacity"
                name="capacity"
                min="1"
                value="{{ old('capacity', $activity->capacity) }}"
                placeholder="36">
        </div>
        @error('capacity')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="gender_policy">Gender Policy</label>
        <select class="form-select @error('gender_policy') is-invalid @enderror" id="gender_policy" name="gender_policy" data-trigger>
            <option value="">Select gender policy</option>
            @foreach ($genderPolicies as $key => $label)
                <option value="{{ $key }}" {{ old('gender_policy', $activity->gender_policy) === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('gender_policy')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-grid">
    <div class="form-group">
        <label class="form-label" for="fee_type_id">Optional Fee Type</label>
        <select class="form-select @error('fee_type_id') is-invalid @enderror" id="fee_type_id" name="fee_type_id" data-trigger>
            <option value="">Select optional fee type</option>
            @foreach ($feeTypes as $feeType)
                <option value="{{ $feeType->id }}" {{ (string) old('fee_type_id', $activity->fee_type_id) === (string) $feeType->id ? 'selected' : '' }}>
                    {{ $feeType->name }}
                </option>
            @endforeach
        </select>
        @error('fee_type_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="default_fee_amount">Default Fee Amount</label>
        <div class="input-icon-group">
            <i class="fas fa-money-bill-wave input-icon"></i>
            <input type="number"
                step="0.01"
                min="0"
                class="form-control @error('default_fee_amount') is-invalid @enderror"
                id="default_fee_amount"
                name="default_fee_amount"
                value="{{ old('default_fee_amount', $activity->default_fee_amount) }}"
                placeholder="150.00">
        </div>
        @error('default_fee_amount')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group" style="margin-top: 16px;">
    <label class="form-label" for="description">Description</label>
    <div class="input-icon-group textarea-icon-group">
        <i class="fas fa-align-left input-icon"></i>
        <textarea
            class="form-control @error('description') is-invalid @enderror"
            id="description"
            name="description"
            rows="4"
            placeholder="Describe the purpose, structure, and expected outcomes of this activity.">{{ old('description', $activity->description) }}</textarea>
    </div>
    @error('description')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<div class="option-grid">
    <div class="option-card">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="attendance_required" name="attendance_required"
                value="1" {{ old('attendance_required', $activity->attendance_required) ? 'checked' : '' }}>
            <label class="form-check-label" for="attendance_required">
                Attendance required
            </label>
            <span class="option-help">Require session-by-session attendance capture for this activity.</span>
        </div>
    </div>

    <div class="option-card">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="allow_house_linkage" name="allow_house_linkage"
                value="1" {{ old('allow_house_linkage', $activity->allow_house_linkage) ? 'checked' : '' }}>
            <label class="form-check-label" for="allow_house_linkage">
                Allow house-linked reporting
            </label>
            <span class="option-help">Enable outputs and results that can reference houses without changing house membership.</span>
        </div>
    </div>
</div>
