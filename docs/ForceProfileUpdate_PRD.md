# Force Profile Update -- PRD & Implementation Tracker

**Module Status:** PHASE 1 NOT STARTED
**Last Updated:** March 13, 2026

---

## 1. Purpose

Administrators need a way to force all staff to complete their profile data before accessing system functions. Currently, users can log in and use the system without ever filling in critical fields like payroll numbers, DPSM file numbers, or dates of appointment.

This feature adds:
- An admin toggle in Staff Settings > General Settings
- Configurable field-level requirements (admins pick which fields are mandatory)
- A dedicated "Complete Your Profile" page that blocks system access until the profile is complete
- Clear, in-theme messaging so users understand what's happening and what's needed

**UX:** After login, users with incomplete profiles see a dedicated full page with the sidebar greyed out and navigation disabled. Only the missing fields are shown in the form, with already-filled fields displayed as a completed checklist. Once saved, the user proceeds to the dashboard.

---

## 2. How To Use This Tracker

1. Move a phase to `IN PROGRESS` when implementation starts.
2. Check off tasks as they are completed.
3. Run the verification items for that phase.
4. Only then mark the phase `COMPLETE`.
5. Record the completion date and a short note in the completion log.

Status values:
- `NOT STARTED`
- `IN PROGRESS`
- `BLOCKED`
- `COMPLETE`

---

## 3. Phase Overview

| Phase | Focus | Status | Depends On |
|-------|-------|--------|------------|
| Phase 1 | Database & Model | NOT STARTED | None |
| Phase 2 | Middleware | NOT STARTED | Phase 1 |
| Phase 3 | Admin Settings UI (toggle + field config) | NOT STARTED | Phase 1 |
| Phase 4 | Dedicated "Complete Your Profile" page | NOT STARTED | Phase 2, Phase 3 |
| Phase 5 | Tests & polish | NOT STARTED | Phase 4 |

---

## 4. Detailed Phases

### Phase 1: Database & Model

**Status:** NOT STARTED
**Goal:** Create the settings table and model that stores the force-update toggle and required fields configuration.

**Implementation targets:**
- [ ] Migration: `create_staff_profile_settings_table`
  - Schema: `id`, `key` (string, unique), `value` (json, nullable), `description` (text, nullable), `updated_by` (FK → users.id, nullable), `updated_at` (timestamp, nullable)
  - Seed rows: `force_profile_update_enabled` → `false`, `force_profile_update_fields` → `["firstname","lastname","date_of_birth","id_number","email","nationality"]`
- [ ] Model: `app/Models/StaffProfileSetting.php`
  - Follow `app/Models/StaffAttendance/StaffAttendanceSetting.php` pattern exactly
  - `$timestamps = false`, manual `updated_at` via boot
  - `$casts = ['value' => 'array']`
  - Static `get(string $key, $default)` and `set(string $key, $value, ?int $userId)` helpers
  - `updatedBy()` BelongsTo relationship
  - Constants: `KEY_ENABLED`, `KEY_FIELDS`
  - Convenience: `isForceUpdateEnabled(): bool`, `getRequiredFields(): array`
- [ ] Run migration: `php artisan migrate`

**Files:**
- `database/migrations/YYYY_MM_DD_000001_create_staff_profile_settings_table.php` (create)
- `app/Models/StaffProfileSetting.php` (create)

**Verification:**
- [ ] Migration runs without errors
- [ ] `php artisan tinker`: `StaffProfileSetting::get('force_profile_update_enabled')` returns `false`
- [ ] `StaffProfileSetting::set('force_profile_update_enabled', true, 1)` updates correctly
- [ ] `StaffProfileSetting::isForceUpdateEnabled()` returns expected boolean

**Completion:** _Date:_ ________ | _Notes:_ ________

---

### Phase 2: Middleware

**Status:** NOT STARTED
**Goal:** Create middleware that intercepts requests and redirects incomplete users to the profile completion page.

**Implementation targets:**
- [ ] Create `app/Http/Middleware/EnsureProfileComplete.php`
  - Pass through if: not authenticated, setting disabled (cached 60s), user has `Administrator` role, or request matches whitelisted routes
  - Whitelisted routes: `profile.complete`, `profile.complete.save`, `users.update-profile-details`, `profile.update-avatar`, `logout`, `login`, `password.*`
  - Check user's profile fields against required fields list (also cached 60s)
  - If incomplete → redirect to `route('profile.complete')`
  - If complete → pass through
- [ ] Register in `app/Http/Kernel.php` → add to `$middlewareGroups['web']` after `ResourceOptimizer`

**Files:**
- `app/Http/Middleware/EnsureProfileComplete.php` (create)
- `app/Http/Kernel.php` (modify -- add 1 line to web middleware group)

**Verification:**
- [ ] Middleware class exists and is registered
- [ ] With setting disabled: all routes work normally (no redirects)
- [ ] With setting enabled + incomplete user: accessing `/dashboard` redirects to `/staff/profile/complete`
- [ ] With setting enabled + complete user: accessing `/dashboard` works normally
- [ ] Administrator with incomplete fields can access `/dashboard` normally

**Completion:** _Date:_ ________ | _Notes:_ ________

---

### Phase 3: Admin Settings UI

**Status:** NOT STARTED
**Goal:** Build the admin toggle and field configuration form in Staff Settings > General Settings tab.

**Implementation targets:**
- [ ] Modify `UserController::staffSettings()` (line 1306) to pass new data to view:
  - `$forceUpdateEnabled`, `$forceUpdateFields`, `$configurableFields` (field name → label map)
- [ ] New method `UserController::updateForceProfileSetting(Request $request)`:
  - Authorize via `$this->authorize('manage-hr')`
  - Validate: `force_update_enabled` (required|boolean), `required_fields` (array when enabled), `required_fields.*` (string|in:valid field list)
  - `DB::transaction()` → `StaffProfileSetting::set()` for both keys
  - Clear cache keys, redirect with success message
- [ ] Add route in `routes/staff/users.php`:
  - `POST /staff/settings/force-profile-update` → `updateForceProfileSetting` (inside access-hr group)
- [ ] Replace empty General Settings tab in `resources/views/staff/staff-settings.blade.php` (lines 674-686):
  - Help text explaining the feature
  - Bootstrap form-switch toggle for enable/disable
  - Two-column checkbox grid for configurable fields (shown/hidden via JS based on toggle)
  - Save button with `.btn-loading` pattern

**Files:**
- `app/Http/Controllers/UserController.php` (modify -- `staffSettings()` + new method)
- `routes/staff/users.php` (modify -- add 1 route)
- `resources/views/staff/staff-settings.blade.php` (modify -- replace empty General Settings tab)

**Verification:**
- [ ] Staff Settings > General Settings tab shows the toggle and field checkboxes
- [ ] Toggle starts OFF, field checkboxes hidden
- [ ] Turning toggle ON reveals field checkboxes
- [ ] Saving with toggle ON + selected fields persists to DB
- [ ] Saving with toggle OFF clears the enabled state
- [ ] Non-HR users cannot access the settings endpoint (403)

**Completion:** _Date:_ ________ | _Notes:_ ________

---

### Phase 4: Dedicated "Complete Your Profile" Page

**Status:** NOT STARTED
**Goal:** Build the full-page profile completion experience that users see when their profile is incomplete.

**Implementation targets:**
- [ ] New method `UserController::showCompleteProfile()`:
  - Load current user, required fields, determine incomplete vs complete fields
  - Load form dependencies: nationalities, earning bands
  - Build a `$configurableFields` label map for display
  - Return `profile.complete-profile` view
- [ ] New method `UserController::saveCompleteProfile(Request $request)`:
  - Validate only the submitted (incomplete) fields using same rules as `updateProfileDetails()`
  - Phone normalization if phone is submitted
  - `DB::transaction()` → update user
  - Redirect to `route('dashboard')` with success flash "Profile completed successfully!"
- [ ] Add routes in `routes/staff/users.php` (OUTSIDE `can:access-hr` group, inside `auth` group):
  - `GET /staff/profile/complete` → `showCompleteProfile` → `profile.complete`
  - `POST /staff/profile/complete` → `saveCompleteProfile` → `profile.complete.save`
- [ ] Create `resources/views/profile/complete-profile.blade.php`:
  - Extends `layouts.master`
  - CSS: grey out sidebar (`opacity: 0.4; pointer-events: none`), disable topbar nav links
  - Gradient header (matching theme `#4e73df → #36b9cc`) with warning icon:
    - Title: "Profile Update Required"
    - Subtitle: "Your administrator has requested that you complete your profile information before continuing."
  - White card body with:
    - Help text box explaining what's needed and why
    - Form showing ONLY missing fields (appropriate input types per field: text, date, select for nationality/earning_band)
    - "Already Complete" section showing filled fields with green checkmarks and current values
    - "Save & Continue" primary button with `.btn-loading` spinner
  - Form POSTs to `route('profile.complete.save')`

**Files:**
- `app/Http/Controllers/UserController.php` (modify -- add 2 new methods)
- `routes/staff/users.php` (modify -- add 2 routes)
- `resources/views/profile/complete-profile.blade.php` (create)

**Verification:**
- [ ] Visiting `/staff/profile/complete` as an incomplete user shows the dedicated page
- [ ] Sidebar is greyed out and unclickable
- [ ] Only missing fields are shown in the form
- [ ] Already-complete fields show with green checkmarks
- [ ] Filling in fields and clicking "Save & Continue" saves data and redirects to dashboard
- [ ] After completion, navigating the system works normally (no more blocking)
- [ ] Form validation errors display correctly (e.g., invalid date)

**Completion:** _Date:_ ________ | _Notes:_ ________

---

### Phase 5: Tests & Polish

**Status:** NOT STARTED
**Goal:** Add automated tests and final polish.

**Implementation targets:**
- [ ] Update `tests/Concerns/EnsuresStaffProfileSchema.php`:
  - Add `ensureStaffProfileSettingsTable()` method to create table and seed defaults
- [ ] Add tests to `tests/Feature/Staff/StaffProfileFieldsTest.php`:
  - [ ] `test_force_profile_update_setting_can_be_toggled`
  - [ ] `test_middleware_redirects_incomplete_user_when_enabled`
  - [ ] `test_middleware_allows_complete_user_through`
  - [ ] `test_middleware_allows_admin_through_even_if_incomplete`
  - [ ] `test_complete_profile_page_shows_only_missing_fields`
  - [ ] `test_complete_profile_save_redirects_to_dashboard`
- [ ] Run full test suite: `php artisan test --filter=StaffProfileFieldsTest`
- [ ] Clear route cache: `php artisan route:clear`
- [ ] End-to-end manual walkthrough

**Files:**
- `tests/Concerns/EnsuresStaffProfileSchema.php` (modify)
- `tests/Feature/Staff/StaffProfileFieldsTest.php` (modify)

**Verification:**
- [ ] All 6 new tests pass
- [ ] Existing tests still pass: `php artisan test --filter=StaffProfileFieldsTest`
- [ ] Full E2E: enable toggle → log in as non-admin with empty fields → see completion page → fill in → reach dashboard → disable toggle → no blocking

**Completion:** _Date:_ ________ | _Notes:_ ________

---

## 5. Key Design Decisions

| Decision | Rationale |
|----------|-----------|
| Per-module settings table (`staff_profile_settings`) | Follows existing pattern (StaffAttendanceSetting, LeaveSetting, etc.) |
| 60-second cache TTL for settings | Avoids DB hit on every request; admin toggle changes apply within 1 minute |
| Administrators always bypass | Prevents admins from locking themselves out |
| Dedicated page (not modal/banner) | Unmissable UX -- user can't ignore or navigate away |
| Show only missing fields | Focused experience -- user sees exactly what's needed, nothing more |
| Field-level configuration | Admins can require just the fields they care about |
| Route whitelist in middleware | More secure than blacklist -- new routes blocked by default |

---

## 6. File Summary

| File | Action | Phase |
|------|--------|-------|
| `database/migrations/YYYY_create_staff_profile_settings_table.php` | Create | 1 |
| `app/Models/StaffProfileSetting.php` | Create | 1 |
| `app/Http/Middleware/EnsureProfileComplete.php` | Create | 2 |
| `app/Http/Kernel.php` | Modify (1 line) | 2 |
| `app/Http/Controllers/UserController.php` | Modify (4 methods) | 3, 4 |
| `routes/staff/users.php` | Modify (3 routes) | 3, 4 |
| `resources/views/staff/staff-settings.blade.php` | Modify (General Settings tab) | 3 |
| `resources/views/profile/complete-profile.blade.php` | Create | 4 |
| `tests/Concerns/EnsuresStaffProfileSchema.php` | Modify | 5 |
| `tests/Feature/Staff/StaffProfileFieldsTest.php` | Modify | 5 |
