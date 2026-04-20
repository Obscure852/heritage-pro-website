# PRD: Scheme of Work Module

> Status note (March 22, 2026): This draft is retained for historical context. The canonical, implementation-aligned plan is in [SchemeOfWork_PRD.md](./SchemeOfWork_PRD.md).

## Context

Heritage School Management System currently handles assessments and markbooks but has no structured curriculum planning tool. Teachers use paper-based schemes of work and have no digital way to plan, track, or connect their teaching sequence to assessments. The Ministry of Education provides syllabi per subject per grade level, which teachers must translate into term-by-term schemes of work and then daily lesson plans.

This module creates a **Syllabus -> Scheme of Work -> Lesson Plan** pipeline, stored centrally and integrated with the existing assessment/markbook system. Syllabi are stored in the existing Document Management System (DMS) with structured objectives in the database for browsing and copying. Tests can be linked to scheme objectives for coverage tracking.

**Supports:** All school types (Junior, Senior, Primary, Reception).

---

## 1. Planning Hierarchy

```
Syllabus (per Subject, per Grade level, stored in DMS + structured objectives)
  -> Scheme of Work (per Teacher, per KlassSubject OR OptionalSubject, per Term)
    -> Scheme of Work Entry (weekly row within a scheme)
      -> Lesson Plan (daily/period classroom plan)
```

**Teacher Assignment Sources:**
The system has **two** models for assigning teachers to subjects:
- **KlassSubject** (`klass_subject` table) - core/mandatory subjects: assigns a teacher to a subject in a specific class (e.g. Teacher X teaches Math in F1A)
- **OptionalSubject** (`optional_subjects` table) - optional/elective subjects: assigns a teacher to an optional subject group that may span multiple classes (e.g. Teacher Y teaches Art to a group of F1 students from F1A, F1B, F1C)

Both assignment types must be supported as the source for creating a Scheme of Work. A scheme links to **one or the other** (not both).

---

## 2. Module A: Syllabi Management (Central Storage)

### 2.1 DMS Integration

Leverage the existing Documents module -- no new file storage system needed:

- Seed a top-level `DocumentCategory` named **"Syllabi"** (slug: `syllabi`)
- Seed an Institutional `DocumentFolder` named **"Syllabi"** with sub-folders per level: `Primary Syllabi`, `Junior Syllabi`, `Senior Syllabi`
- Admin uploads syllabus PDFs via the existing `/documents/create` route, tagged to the Syllabi category
- All teachers can view/download via `visibility = 'internal'`

### 2.2 New Model: `Syllabus`

Bridges a DMS `Document` to a Subject + Grade, and parents the structured objectives.

**Table: `syllabi`**

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint unsigned | PK | |
| document_id | bigint unsigned | FK documents.id, nullable | Link to PDF in DMS |
| subject_id | bigint unsigned | FK subjects.id | |
| grade_name | string(20) | | e.g. 'F1', 'F2', 'STD 3' |
| level | string(20) | | 'Primary', 'Junior', 'Senior', 'Pre-primary' |
| year_issued | smallint | nullable | Ministry issue year |
| version | string(30) | nullable | e.g. 'Revised 2024' |
| description | text | nullable | |
| is_active | boolean | default true | One active per subject+grade |
| created_by | bigint unsigned | FK users.id | |
| timestamps + soft deletes | | | |

**Indexes:** `unique(subject_id, grade_name, is_active)` (partial, where is_active=true)

**Relationships:**
- `document()` -> Document
- `subject()` -> Subject
- `createdBy()` -> User
- `topics()` -> HasMany SyllabusTopic
- `objectives()` -> HasMany SyllabusObjective (through topics or direct)

### 2.3 New Model: `SyllabusTopic`

Major topics/units within a syllabus.

**Table: `syllabus_topics`**

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint unsigned | PK | |
| syllabus_id | bigint unsigned | FK syllabi.id, cascade | |
| sequence | smallint unsigned | | Display order |
| name | string(255) | | e.g. "Algebra", "Organic Chemistry" |
| description | text | nullable | |
| suggested_weeks | smallint unsigned | nullable | Ministry-suggested duration |
| suggested_term | tinyint unsigned | nullable | 1, 2, or 3 |
| timestamps | | | |

**Index:** `unique(syllabus_id, sequence)`

### 2.4 New Model: `SyllabusObjective`

Individual learning objectives parsed/entered from a syllabus.

**Table: `syllabus_objectives`**

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint unsigned | PK | |
| syllabus_topic_id | bigint unsigned | FK syllabus_topics.id, cascade | Parent topic |
| sequence | smallint unsigned | | Order within topic |
| code | string(30) | nullable | e.g. 'SCI-F1-03.02' |
| objective_text | text | | The learning objective |
| cognitive_level | string(30) | nullable | Bloom's taxonomy level |
| timestamps | | | |

### 2.5 Admin Workflow

1. Navigate to `/schemes/syllabi`
2. Create Syllabus record: select Subject + Grade level + optionally link a Document from DMS
3. Add Topics (manual or bulk textarea, one per line)
4. Within each Topic, add Objectives (manual or bulk import)
5. Teachers browse syllabi at `/schemes/syllabi` (read-only for non-admin)

---

## 3. Module B: Scheme of Work

### 3.1 New Model: `SchemeOfWork`

**Table: `schemes_of_work`**

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint unsigned | PK | |
| klass_subject_id | bigint unsigned | FK klass_subject.id, nullable | Core subject: teacher-class-subject link |
| optional_subject_id | bigint unsigned | FK optional_subjects.id, nullable | Optional subject: teacher-elective link |
| grade_subject_id | bigint unsigned | FK grade_subject.id | Subject in grade for term |
| syllabus_id | bigint unsigned | FK syllabi.id, nullable | Reference syllabus |
| teacher_id | bigint unsigned | FK users.id | Creator |
| term_id | bigint unsigned | FK terms.id | |
| grade_id | bigint unsigned | FK grades.id | |
| year | smallint unsigned | | |
| status | string(30) | default 'draft' | See status flow below |
| title | string(255) | nullable | Optional custom title |
| total_weeks | tinyint unsigned | default 13 | Teaching weeks in term |
| submitted_at | timestamp | nullable | |
| reviewed_at | timestamp | nullable | |
| reviewed_by | bigint unsigned | FK users.id, nullable | HOD reviewer |
| review_comments | text | nullable | HOD feedback |
| cloned_from_id | bigint unsigned | FK schemes_of_work.id, nullable | Source if cloned |
| timestamps + soft deletes | | | |

**Constraint:** Exactly one of `klass_subject_id` or `optional_subject_id` must be set (not both, not neither). Enforced at the application level in validation.

**Indexes:** `unique(klass_subject_id, term_id)` (where not null), `unique(optional_subject_id, term_id)` (where not null), `index(teacher_id, term_id)`, `index(status)`

**Status Flow:**
```
draft -> submitted -> under_review -> approved
                                   -> revision_required -> (teacher edits) -> submitted
```
Approved schemes are locked from editing (unless admin unlocks).

### 3.2 New Model: `SchemeOfWorkEntry`

One row per week within a scheme.

**Table: `scheme_of_work_entries`**

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint unsigned | PK | |
| scheme_of_work_id | bigint unsigned | FK schemes_of_work.id, cascade | |
| week_number | tinyint unsigned | | Week 1-13 |
| start_date | date | nullable | |
| end_date | date | nullable | |
| topic | string(255) | | Main topic |
| sub_topic | string(255) | nullable | |
| syllabus_topic_id | bigint unsigned | FK syllabus_topics.id, nullable | Linked syllabus topic |
| learning_objectives | text | nullable | Can be copied from syllabus |
| teaching_activities | text | nullable | Methods/strategies |
| learning_activities | text | nullable | What students do |
| resources | text | nullable | Materials needed |
| assessment_methods | text | nullable | How learning is assessed |
| homework | text | nullable | |
| references | text | nullable | Page numbers, URLs |
| remarks | text | nullable | Post-teaching reflection |
| status | string(20) | default 'planned' | planned/in_progress/completed/skipped |
| completed_at | date | nullable | |
| timestamps | | | |

**Index:** `unique(scheme_of_work_id, week_number)`

### 3.3 Pivot: `scheme_entry_objectives`

Links syllabus objectives to scheme entries (many-to-many).

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned | PK |
| scheme_entry_id | bigint unsigned | FK scheme_of_work_entries.id, cascade |
| syllabus_objective_id | bigint unsigned | FK syllabus_objectives.id, cascade |
| created_at | timestamp | |

**Index:** `unique(scheme_entry_id, syllabus_objective_id)`

### 3.4 Teacher's Subject List (Create Workflow)

When a teacher creates a scheme, the system fetches their assignable subjects from **both** sources:

```php
// Core subjects (from KlassSubject)
$coreSubjects = KlassSubject::where('user_id', $user->id)
    ->where('term_id', $currentTermId)->with('klass', 'gradeSubject.subject')->get();

// Optional subjects (from OptionalSubject)
$optionalSubjects = OptionalSubject::where('user_id', $user->id)
    ->where('term_id', $currentTermId)->with('gradeSubject.subject')->get();
```

The create form shows a combined dropdown grouped by type:
- **Core Subjects:** "F1A - Mathematics", "F1A - English" (from KlassSubject)
- **Optional Subjects:** "Art (F1 Group A)", "Music (F2 Group B)" (from OptionalSubject)

When a core subject is selected, `klass_subject_id` is set. When an optional is selected, `optional_subject_id` is set. The `grade_subject_id`, `term_id`, `grade_id`, and `year` are auto-populated from the selected assignment.

### 3.5 Copy Objectives Feature

When creating/editing a scheme entry, a side panel shows:
- Syllabus topics for the subject/grade
- Expanding a topic reveals its objectives
- Click "Copy" icon to append objective text to the `learning_objectives` field
- System also creates a `scheme_entry_objectives` pivot record for structured tracking

### 3.6 Cloning

Teachers can clone from:
1. **Own previous term** - e.g. clone F1A Math Term 1 2025 for Term 1 2026
2. **Another teacher's approved scheme** - same subject/grade (only `approved` schemes)
3. **HOD template schemes** - model schemes created as templates

Cloning copies all entries, resets status to `draft`, sets `cloned_from_id`.

### 3.7 HOD Approval

- Teacher submits -> HOD sees pending in department dashboard
- HOD reviews, approves or requests revision with comments
- HOD determined via `Department` model: `department_head` or `assistant` fields
- Scheme's department via `GradeSubject.department_id`

---

## 4. Module C: Lesson Plans

### 4.1 New Model: `LessonPlan`

**Table: `lesson_plans`**

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint unsigned | PK | |
| scheme_entry_id | bigint unsigned | FK scheme_of_work_entries.id, nullable, set null | Linked scheme week |
| teacher_id | bigint unsigned | FK users.id | |
| klass_subject_id | bigint unsigned | FK klass_subject.id, nullable | Core subject context |
| optional_subject_id | bigint unsigned | FK optional_subjects.id, nullable | Optional subject context |
| grade_subject_id | bigint unsigned | FK grade_subject.id | |
| term_id | bigint unsigned | FK terms.id | |
| date | date | | Lesson date |
| period | tinyint unsigned | nullable | Period number (timetable) |
| duration_minutes | smallint unsigned | default 40 | |
| topic | string(255) | | |
| sub_topic | string(255) | nullable | |
| learning_objectives | text | | |
| prerequisite_knowledge | text | nullable | |
| introduction | text | nullable | Starter activity (5-10 min) |
| development | text | nullable | Main lesson body |
| conclusion | text | nullable | Summary/plenary |
| assessment | text | nullable | How learning is checked |
| differentiation | text | nullable | Support for different abilities |
| resources | text | nullable | |
| homework | text | nullable | |
| teacher_reflection | text | nullable | Post-lesson reflection |
| status | string(20) | default 'planned' | planned/taught/cancelled |
| taught_at | timestamp | nullable | |
| timestamps + soft deletes | | | |

**Indexes:** `index(teacher_id, date)`, `index(scheme_entry_id)`, `index(term_id)`

### 4.2 Daily/Weekly Teacher Workflow

**Start of week:**
1. Teacher opens their Scheme of Work dashboard (`/schemes`)
2. The current week is auto-highlighted based on today's date (e.g. Week 4)
3. Teacher reviews the planned topic, objectives, resources, and assessment for the week

**Each day/period:**
1. Teacher clicks **"Create Lesson Plan"** on the current week's scheme entry
2. Form pre-fills: topic, sub-topic, learning objectives (from the scheme entry)
3. Teacher adds lesson-specific detail: introduction, development, conclusion, assessment, homework
4. Multiple lesson plans per scheme entry (3-5 per week, one per class period)

**After teaching:**
1. Teacher marks the lesson plan as **"Taught"**
2. Adds a **teacher reflection** (what worked, what didn't, students who need follow-up)

**End of week:**
1. Teacher marks the scheme entry as **"Completed"** (or "In Progress" if topic carries over)
2. The scheme progress bar updates

**What the teacher sees at a glance (scheme view):**

```
Week | Topic              | Status      | Lessons    | Test Linked
-----+--------------------+-------------+------------+------------
  1  | Number Systems     | completed   | 4/4 taught | CA Test 1
  2  | Fractions          | completed   | 3/4 taught | -
  3  | Decimals           | completed   | 4/4 taught | CA Test 2
  4  | Algebra Basics     | in_progress | 2/4 planned| -          <-- THIS WEEK
  5  | Algebra Expressions| planned     | 0 created  | -
 ...
```

**Standalone lesson plans** (without a linked scheme entry) are also supported for substitute coverage or ad-hoc lessons.

---

## 5. Module D: Assessment Integration

### 5.1 Test-to-Scheme Linking

**New Pivot: `test_scheme_entries`**

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned | PK |
| test_id | bigint unsigned | FK tests.id, cascade |
| scheme_entry_id | bigint unsigned | FK scheme_of_work_entries.id, cascade |
| created_at | timestamp | |

**New Pivot: `test_syllabus_objectives`**

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned | PK |
| test_id | bigint unsigned | FK tests.id, cascade |
| syllabus_objective_id | bigint unsigned | FK syllabus_objectives.id, cascade |
| created_at | timestamp | |

### 5.2 Test Model Changes

Add to existing `App\Models\Test`:

```php
public function schemeEntries(): BelongsToMany { ... } // via test_scheme_entries
public function syllabusObjectives(): BelongsToMany { ... } // via test_syllabus_objectives
```

### 5.3 Test Creation Enhancement

When creating/editing a test (`TestController::store/update`), add an optional "Link to Scheme" section:
- Select2 dropdown to search scheme entries for the same grade_subject + term
- Optional dropdown for specific syllabus objectives
- Saved to the pivot tables

### 5.4 Coverage Reports

- **Objective Coverage:** Total objectives vs tested objectives per scheme (gap analysis)
- **Topic Progress:** planned/in_progress/completed status + linked test indicator + lesson plan status
- **Per-Objective Analysis:** Average student score on linked tests

---

## 6. Module E: Dashboards & Reports

### 6.1 Teacher Dashboard (`/schemes`)

- My Schemes This Term (card grid with status badges, progress bars)
- Upcoming Lesson Plans (next 5 by date)
- Assessment Coverage (pie chart of topics with linked tests)

### 6.2 HOD Dashboard (`/schemes/dashboard/hod`)

- Department teacher list with scheme submission status
- Pending reviews count + quick-action buttons
- Department-level coverage summary

### 6.3 Admin Dashboard (`/schemes/dashboard/admin`)

- School-wide: expected vs submitted vs approved per department
- Teachers with no schemes (gap report)
- Submission timeline

### 6.4 Reports

| Report | Access |
|--------|--------|
| Scheme Completion Rate per term | Admin, HOD |
| Assessment-Objective Alignment per subject | Teacher, HOD |
| Topic Coverage by Class | Teacher, HOD |
| Lesson Plan Summary | Admin, HOD |
| Department Submission Report (exportable) | Admin |

---

## 7. Technical Architecture

### 7.1 New Files

**Models (6 new + 1 modified):**
- `app/Models/Syllabus.php`
- `app/Models/SyllabusTopic.php`
- `app/Models/SyllabusObjective.php`
- `app/Models/SchemeOfWork.php`
- `app/Models/SchemeOfWorkEntry.php`
- `app/Models/LessonPlan.php`
- `app/Models/Test.php` (add 2 relationships)

**Migrations (9):**
- `create_syllabi_table`
- `create_syllabus_topics_table`
- `create_syllabus_objectives_table`
- `create_schemes_of_work_table`
- `create_scheme_of_work_entries_table`
- `create_scheme_entry_objectives_table` (pivot)
- `create_lesson_plans_table`
- `create_test_scheme_entries_table` (pivot)
- `create_test_syllabus_objectives_table` (pivot)

**Controllers (5):**
- `app/Http/Controllers/Schemes/SyllabusController.php` - Syllabus CRUD + objectives
- `app/Http/Controllers/Schemes/SchemeController.php` - Scheme CRUD + workflow
- `app/Http/Controllers/Schemes/SchemeEntryController.php` - Weekly entries (AJAX)
- `app/Http/Controllers/Schemes/LessonPlanController.php` - Lesson plan CRUD
- `app/Http/Controllers/Schemes/SchemeDashboardController.php` - Dashboards + reports

**Services (3):**
- `app/Services/Schemes/SchemeService.php` - Cloning, status transitions
- `app/Services/Schemes/CoverageService.php` - Assessment coverage calculations
- `app/Services/Schemes/SyllabusImportService.php` - Bulk text import of objectives

**Policy:**
- `app/Policies/SchemeOfWorkPolicy.php` - CRUD, submit, review authorization

**Routes:**
- `routes/schemes/schemes.php` - All module routes
- Include in `routes/web.php`: `include __DIR__.'/schemes/schemes.php';`

**Views (new directory `resources/views/schemes/`):**
- `index.blade.php` - Teacher dashboard
- `create.blade.php`, `edit.blade.php`, `show.blade.php` - Scheme CRUD
- `syllabi/index.blade.php`, `syllabi/show.blade.php`, `syllabi/create.blade.php`
- `syllabi/partials/objective-browser.blade.php` - Reusable side panel
- `lesson-plans/index.blade.php`, `lesson-plans/create.blade.php`, `lesson-plans/show.blade.php`
- `dashboard/hod.blade.php`, `dashboard/admin.blade.php`
- `reports/coverage.blade.php`

### 7.2 Files to Modify

| File | Change |
|------|--------|
| `routes/web.php` | Add `include __DIR__.'/schemes/schemes.php';` |
| `app/Models/Test.php` | Add `schemeEntries()` and `syllabusObjectives()` relationships |
| `app/Models/User.php` | Add `schemesOfWork()` and `lessonPlans()` HasMany relationships |
| `app/Providers/AuthServiceProvider.php` | Register SchemeOfWork policy + add `access-schemes`, `manage-syllabi`, `review-schemes` gates |
| `app/Services/ModuleVisibilityService.php` | Add `schemes` module entry |
| `resources/views/layouts/sidebar.blade.php` | Add Scheme of Work sidebar menu |

### 7.3 Authorization

**Policy (`SchemeOfWorkPolicy`):**
- `viewAny` - any current staff
- `view` - owner, HOD of subject's department, or admin
- `create` - any current teacher
- `update` - owner (if draft/revision_required) or admin
- `submit` - owner (if draft/revision_required)
- `review` - HOD of department (via `Department.department_head` / `Department.assistant`) or admin
- `delete` - owner (if draft) or admin

**Gates (in AuthServiceProvider):**
```php
'access-schemes'  -> ['Administrator', 'Academic Admin', 'HOD', 'Teacher', 'Assessment Admin']
'manage-syllabi'  -> ['Administrator', 'Academic Admin', 'HOD']
'review-schemes'  -> ['Administrator', 'Academic Admin', 'HOD']
```

**ModuleVisibilityService entry:**
```php
'schemes' => [
    'key' => 'modules.schemes_visible',
    'name' => 'Scheme of Work',
    'icon' => 'bx bx-notepad',
    'roles' => ['Academic Admin'],
]
```

### 7.4 Key Relationships Map

```
Subject ---< GradeSubject >--- Grade
  |               |
  |               |---< Test >---< StudentTest >--- Student
  |               |      |
  |               |      +-- test_scheme_entries --> SchemeOfWorkEntry
  |               |      +-- test_syllabus_objectives --> SyllabusObjective
  |               |
  |               +---< KlassSubject >--- Klass     (core subjects)
  |               |        |
  |               |        +--- SchemeOfWork (via klass_subject_id)
  |               |
  |               +---< OptionalSubject              (elective subjects)
  |                        |
  |                        +--- SchemeOfWork (via optional_subject_id)
  |
  |           SchemeOfWork (linked via one of the above)
  |                |
  |                +---< SchemeOfWorkEntry
  |                         |
  |                         +---< LessonPlan
  |                         +-- scheme_entry_objectives --> SyllabusObjective
  |
  +--- Syllabus (--- Document in DMS)
          |
          +---< SyllabusTopic
                   |
                   +---< SyllabusObjective
```

### 7.5 Existing Code to Reuse

| What | File | How |
|------|------|-----|
| Core subject assignments | `app/Models/KlassSubject.php` | SchemeOfWork links via `klass_subject_id` for core/mandatory subjects |
| Optional subject assignments | `app/Models/OptionalSubject.php` | SchemeOfWork links via `optional_subject_id` for elective subjects |
| Department HOD lookup | `app/Models/Department.php` | `department_head`, `assistant` fields for review authorization |
| DMS file storage | `app/Services/Documents/DocumentStorageService.php` | Syllabus PDF storage via existing upload flow |
| DMS categories | `app/Models/DocumentCategory.php` | Seed "Syllabi" category |
| Gate pattern | `app/Providers/AuthServiceProvider.php` | Follow existing gate definition pattern |
| Module visibility | `app/Services/ModuleVisibilityService.php` | Register `schemes` module |
| Sidebar pattern | `resources/views/layouts/sidebar.blade.php` | Follow existing module sidebar pattern |
| UI theming | CLAUDE.md standards | Gradient headers, form-container, btn-loading pattern |

---

## 8. Implementation Phases

### Phase 1: Foundation (Migrations, Models, Routes, Auth)
- All 9 migrations + run them
- All 6 new models with relationships
- SchemeOfWorkPolicy + register in AuthServiceProvider
- Gate definitions (access-schemes, manage-syllabi, review-schemes)
- Route file + include in web.php
- ModuleVisibilityService entry + sidebar menu
- Seed "Syllabi" DocumentCategory + DocumentFolder

### Phase 2: Syllabus Management
- SyllabusController with CRUD for syllabi, topics, objectives
- Views: syllabi/index, syllabi/create, syllabi/show
- Bulk text import for objectives
- DMS file-picker integration (link Document to Syllabus)
- Objective browser partial (reusable side panel)

### Phase 3: Scheme of Work CRUD
- SchemeController with full CRUD
- SchemeEntryController with AJAX entry management
- Views: schemes/index, create, edit, show
- Copy-from-syllabus integration in entry form
- SchemeService with cloning logic + status transitions

### Phase 4: HOD Approval Workflow
- Submit and review actions in SchemeController
- HOD dashboard view
- Status transition enforcement (lockForUpdate for race conditions)

### Phase 5: Lesson Plans
- LessonPlanController with CRUD
- Views: lesson-plans/index, create, show
- Pre-fill from scheme entry
- Mark as taught + reflection

### Phase 6: Assessment Integration
- test_scheme_entries + test_syllabus_objectives pivot migrations
- Add relationships to Test model
- Modify test create/edit form with optional scheme/objective linking
- CoverageService for coverage metrics

### Phase 7: Dashboards & Reports
- SchemeDashboardController
- Teacher, HOD, Admin dashboards
- Coverage reports with ApexCharts
- Submission status reports

### Phase 8: Polish & Testing
- Feature tests for CRUD, cloning, approval workflow
- UI polish per CLAUDE.md theming standards
- Edge cases: terms with no schemes, subjects without syllabi

---

## 9. Verification

### Manual Testing
1. **Syllabus flow:** Admin creates syllabus -> adds topics -> adds objectives -> teacher can browse
2. **Scheme flow (core subject):** Teacher creates scheme for their KlassSubject (e.g. F1A Math) -> fills weekly entries -> copies objectives from syllabus -> submits -> HOD reviews and approves
3. **Scheme flow (optional subject):** Teacher creates scheme for their OptionalSubject (e.g. Art F1 Group A) -> same workflow as core subjects
4. **Clone flow:** Teacher clones an approved scheme -> gets draft copy with all entries
5. **Lesson plan flow:** Teacher creates lesson plan from scheme entry -> pre-fills -> teaches -> adds reflection
6. **Assessment link:** Teacher creates a test -> links to scheme entry/objective -> coverage report shows it
7. **Dashboards:** Teacher sees their schemes + progress. HOD sees department status. Admin sees school-wide.

### Database Verification
- `php artisan migrate` runs cleanly
- Foreign keys enforce referential integrity
- Unique constraints prevent duplicate schemes per class-subject-term

### Authorization Verification
- Teacher can only edit own draft/revision_required schemes
- HOD can only review schemes in their department
- Admin can do everything
- Non-teachers cannot access scheme module

### Route Verification
- `php artisan route:clear && php artisan route:list --path=schemes` shows all routes
