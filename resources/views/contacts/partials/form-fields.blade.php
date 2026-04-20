@php
    $selectedTagIds = old('tag_ids', isset($contact) && $contact->relationLoaded('tags') ? $contact->tags->pluck('id')->all() : []);
@endphp

<div class="d-flex flex-column gap-4">
    <div>
        <div class="card contact-card">
            <div class="card-body">
                <h4 class="card-title mb-4">Business Details</h4>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Business Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $contact->name) }}" placeholder="Acme Maintenance Services" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Business Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $contact->phone) }}" placeholder="+267 391 0000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Business Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $contact->email) }}" placeholder="hello@acmeservices.co.bw">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="is_active">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ (string) old('is_active', $contact->is_active ?? true) === '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active and selectable</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="address">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3" placeholder="Plot 1234, Gaborone West Industrial">{{ old('address', $contact->address) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Preferred supplier for HVAC and generator maintenance.">{{ old('notes', $contact->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card contact-card">
            <div class="card-body">
                <div class="person-toolbar mb-3">
                    <div>
                        <h4 class="card-title mb-1">Contact People</h4>
                        <p class="text-muted mb-0">Capture at least one person. The form starts with three rows and keeps one primary contact.</p>
                    </div>
                    <button type="button" class="btn btn-outline-primary" id="add-person-row">
                        <i class="bx bx-plus"></i> Add Person
                    </button>
                </div>

                @error('people')
                    <div class="alert alert-danger py-2">{{ $message }}</div>
                @enderror

                <div id="people-rows" class="d-flex flex-column gap-3">
                    @foreach ($peopleRows as $index => $person)
                        <div class="border rounded p-3 person-row" data-row-index="{{ $index }}">
                            <div class="person-row-grid">
                                <div class="person-field">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="people[{{ $index }}][name]" value="{{ $person['name'] ?? '' }}" placeholder="Kagiso Molefe">
                                </div>
                                <div class="person-field">
                                    <label class="form-label">Title / Role</label>
                                    <input type="text" class="form-control" name="people[{{ $index }}][title]" value="{{ $person['title'] ?? '' }}" placeholder="Service Manager">
                                </div>
                                <div class="person-field">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="people[{{ $index }}][email]" value="{{ $person['email'] ?? '' }}" placeholder="kagiso@acmeservices.co.bw">
                                </div>
                                <div class="person-field">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control" name="people[{{ $index }}][phone]" value="{{ $person['phone'] ?? '' }}" placeholder="+267 72 000 000">
                                </div>
                                <div class="person-actions">
                                    <div class="person-primary-group">
                                        <label class="form-label">Primary Contact</label>
                                        <div class="form-check person-primary-check">
                                            <input class="form-check-input primary-person" type="radio" name="primary_person_index" value="{{ $index }}" {{ !empty($person['is_primary']) ? 'checked' : '' }}>
                                            <label class="form-check-label">Primary</label>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-person-row">Remove</button>
                                </div>
                            </div>
                            <input type="hidden" name="people[{{ $index }}][is_primary]" value="{{ !empty($person['is_primary']) ? 1 : 0 }}" class="is-primary-input">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card contact-card">
            <div class="card-body">
                <h4 class="card-title mb-3">Tags</h4>

                <div class="tag-grid">
                    @forelse ($tags as $tag)
                        <label class="tag-option p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ $tag->name }}</div>
                                    <div class="small text-muted">{{ $tag->description ?: 'No description provided.' }}</div>
                                </div>
                                @if ($tag->color)
                                    <span class="badge" style="background-color: {{ $tag->color }};">&nbsp;</span>
                                @endif
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" id="tag_{{ $tag->id }}" {{ in_array($tag->id, $selectedTagIds, true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="tag_{{ $tag->id }}">
                                    Use this tag
                                </label>
                            </div>
                            <div class="small text-muted mt-1">
                                {{ $tag->usable_in_assets ? 'Assets' : 'No asset use' }} | {{ $tag->usable_in_maintenance ? 'Maintenance' : 'No maintenance use' }}
                            </div>
                        </label>
                    @empty
                        <div class="alert alert-warning mb-0">
                            No active contact tags are available. Create them in <a href="{{ route('contacts.settings') }}">Contact Settings</a>.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<template id="person-row-template">
    <div class="border rounded p-3 person-row" data-row-index="__INDEX__">
        <div class="person-row-grid">
            <div class="person-field">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" name="people[__INDEX__][name]" placeholder="Kagiso Molefe">
            </div>
            <div class="person-field">
                <label class="form-label">Title / Role</label>
                <input type="text" class="form-control" name="people[__INDEX__][title]" placeholder="Service Manager">
            </div>
            <div class="person-field">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="people[__INDEX__][email]" placeholder="kagiso@acmeservices.co.bw">
            </div>
            <div class="person-field">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control" name="people[__INDEX__][phone]" placeholder="+267 72 000 000">
            </div>
            <div class="person-actions">
                <div class="person-primary-group">
                    <label class="form-label">Primary Contact</label>
                    <div class="form-check person-primary-check">
                        <input class="form-check-input primary-person" type="radio" name="primary_person_index" value="__INDEX__">
                        <label class="form-check-label">Primary</label>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-person-row">Remove</button>
            </div>
        </div>
        <input type="hidden" name="people[__INDEX__][is_primary]" value="0" class="is-primary-input">
    </div>
</template>
