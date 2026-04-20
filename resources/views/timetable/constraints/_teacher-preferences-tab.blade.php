{{-- Teacher Preferences Tab Content (CONST-02) --}}
<div class="help-text">
    <div class="help-title">Teacher Preferences</div>
    <div class="help-content">
        Set morning or afternoon teaching preferences for teachers. This is a soft constraint -- the system will try to honor preferences but may schedule teachers outside preferred times if necessary.
    </div>
</div>

<div class="settings-section">
    <div class="table-responsive">
        <table class="table table-striped align-middle teacher-preferences-table mb-0">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Teacher Name</th>
                    <th>Department</th>
                    <th style="width: 200px;">Preference</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $preferenceConstraints = $constraints->get('teacher_preference', collect());
                    $prefByTeacher = $preferenceConstraints->keyBy(function($c) {
                        return $c->constraint_config['teacher_id'] ?? null;
                    });
                @endphp
                @foreach($teachers as $idx => $teacher)
                    @php
                        $existing = $prefByTeacher->get($teacher->id);
                        $currentPref = $existing ? ($existing->constraint_config['preference'] ?? 'none') : 'none';
                    @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $teacher->firstname }} {{ $teacher->lastname }}</td>
                        <td>{{ $teacher->department ?: 'N/A' }}</td>
                        <td>
                            <select class="form-select form-select-sm" id="preference_{{ $teacher->id }}">
                                <option value="none" @if($currentPref === 'none') selected @endif>No Preference</option>
                                <option value="morning" @if($currentPref === 'morning') selected @endif>Morning</option>
                                <option value="afternoon" @if($currentPref === 'afternoon') selected @endif>Afternoon</option>
                            </select>
                        </td>
                        <td class="text-end">
                            <div class="action-buttons">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-loading save-preference-btn" data-teacher-id="{{ $teacher->id }}" title="Save Preference">
                                    <span class="btn-text"><i class="fas fa-save"></i></span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    </span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
