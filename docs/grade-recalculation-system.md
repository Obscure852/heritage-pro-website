# Grade Recalculation System with Progress Tracking

## Overview

The grade recalculation system allows teachers and administrators to recalculate all grades, percentages, and remarks for an entire grade level. The system processes subjects in the background using Laravel's queue system while providing real-time progress updates to the user through a polling mechanism.

## System Architecture

### Components

1. **Frontend (Blade Template)**: `markbook-junior-class-list.blade.php`
2. **Backend Controller**: `AssessmentController.php`
3. **Background Job**: `RecalculateGrades.php`
4. **Routes**: `assessment/assessment.php`

---

## How It Works

### 1. User Initiates Recalculation

**Location**: `markbook-junior-class-list.blade.php` (Lines 302-380)

The user clicks "Recalculate Marks" which opens a modal with two options:
- **Class Subjects**: Recalculate grades for mandatory class subjects
- **Optional Subjects**: Recalculate grades for optional subjects

```html
<div class="modal fade" id="recalculateModal">
    <div class="modal-dialog modal-dialog-centered">
        <!-- Two option cards for subject type selection -->
        <div class="option-card" data-value="klass_subjects">Class Subjects</div>
        <div class="option-card" data-value="optional_subjects">Optional Subjects</div>
    </div>
</div>
```

**Key Features**:
- Side-by-side card layout with hover effects
- Selection indicated by checkmark badge
- "Proceed with Recalculation" button enables when option selected

---

### 2. Selection Handling & Form Submission

**Location**: `markbook-junior-class-list.blade.php` (Lines 804-823, 828-874)

When user selects an option card:

```javascript
freshOptionCards.forEach((card) => {
    card.addEventListener('click', function(e) {
        // Remove previous selection
        freshOptionCards.forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');

        // Store value in hidden input
        const value = this.getAttribute('data-value');
        freshSelectedSubjectType.value = value;

        // Enable proceed button
        freshProceedBtn.disabled = false;
    });
});
```

**Important**: The code uses DOM cloning to remove duplicate event listeners that accumulate from AJAX partial reloads:

```javascript
// Clone and replace to remove all event listeners
optionCards.forEach((card) => {
    const newCard = card.cloneNode(true);
    card.parentNode.replaceChild(newCard, card);
});

// Re-query elements after replacement
const freshOptionCards = document.querySelectorAll('.option-card');
const freshProceedBtn = document.getElementById('proceedBtn');
```

When "Proceed" is clicked:

```javascript
freshProceedBtn.addEventListener('click', function(e) {
    // Capture subject type BEFORE hiding modal
    const capturedSubjectType = freshSelectedSubjectType.value;

    if (confirm(confirmMessage)) {
        $('#recalculateModal').modal('hide');

        // Submit form via AJAX
        $.ajax({
            url: recalculateForm.action,
            method: 'POST',
            data: $(recalculateForm).serialize(),
            success: function(response) {
                // Show progress modal and start polling
                $('#progressModal').modal('show');
                startProgressPolling(capturedSubjectType);
            }
        });
    }
});
```

**Critical Detail**: Subject type is captured BEFORE the modal closes to ensure the value is accessible in the AJAX callback.

---

### 3. Controller Dispatches Background Job

**Location**: `AssessmentController.php::recalculateGradesForGrade()` (Lines 779-828)

```php
public function recalculateGradesForGrade($id, Request $request) {
    $klass = Klass::find($id);
    $subjectType = $request->input('subject_type'); // 'klass_subjects' or 'optional_subjects'
    $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);

    // Initialize progress in cache BEFORE dispatching job
    // This prevents 404 errors when frontend starts polling immediately
    $progressKey = "recalc_progress_{$id}_{$subjectType}_{$selectedTermId}";
    Cache::put($progressKey, [
        'job_id' => uniqid('recalc_', true),
        'percentage' => 0,
        'status' => 'queued',
        'message' => 'Job queued, starting soon...',
        'updated_at' => now()->toIso8601String(),
    ], 7200); // Cache for 2 hours

    // Dispatch job to queue
    RecalculateGrades::dispatch($id, $subjectType, $selectedTermId, auth()->id());

    Log::info('Grade recalculation job dispatched successfully', [
        'class_id' => $id,
        'grade_id' => $klass->grade_id,
        'subject_type' => $subjectType,
        'term_id' => $selectedTermId,
        'initiated_by' => auth()->id()
    ]);

    return response()->json(['success' => true]);
}
```

**Key Point**: Progress is initialized in cache BEFORE dispatching the job to prevent race conditions where the frontend polls before the job has started.

---

### 4. Background Job Processes Grades

**Location**: `RecalculateGrades.php` (Lines 48-173)

#### 4.1 Job Initialization

```php
public function __construct(int $classId, string $subjectType, int $selectedTermId, ?int $userId = null) {
    $this->classId = $classId;
    $this->subjectType = $subjectType; // 'klass_subjects' or 'optional_subjects'
    $this->selectedTermId = $selectedTermId;
    $this->userId = $userId;
    $this->jobId = uniqid('recalc_', true);
}

public $timeout = 3600; // 1 hour
public $tries = 1;      // No retries
```

#### 4.2 Job Deduplication with Cache Locks

```php
public function handle(): void {
    // Increase memory limit for large datasets
    ini_set('memory_limit', '1024M');

    // Create unique lock key for this grade/term/subject combination
    $lockKey = "recalculate_grades_{$this->classId}_{$this->subjectType}_{$this->selectedTermId}";

    // Try to acquire lock (expires in 1 hour)
    $lock = Cache::lock($lockKey, 3600);

    if (!$lock->get()) {
        Log::warning("Recalculation already in progress for this combination");
        $this->updateProgress(0, 'failed', 'A recalculation is already in progress.');
        return;
    }

    try {
        // Process grades...
    } finally {
        $lock->release();
    }
}
```

**Purpose**: Prevents duplicate jobs from running simultaneously for the same grade/term/subject combination.

#### 4.3 Progress Tracking

The job calls `updateProgress()` at key milestones:

```php
$this->updateProgress(0, 'processing', 'Initializing recalculation...');      // 0%
$this->updateProgress(5, 'processing', 'Fetching subjects...');               // 5%
$this->updateProgress(10, 'processing', "Processing {$totalSubjects} subjects..."); // 10%

// Progress from 10% to 80% while processing subjects
foreach ($gradeSubjectIds as $index => $gradeSubjectId) {
    $progress = 10 + (($subjectsProcessed / $totalSubjects) * 70);
    $this->updateProgress(round($progress), 'processing', "Processing subject {$subjectsProcessed}/{$totalSubjects}...");

    $this->processSubjectGrades($gradeSubjectId, $termId, $year);

    // Clear memory periodically
    if ($subjectsProcessed % 10 === 0) {
        gc_collect_cycles();
    }
}

$this->updateProgress(85, 'processing', "Generating remarks for {$studentCount} students..."); // 85%
$this->updateProgress(100, 'completed', "Successfully recalculated..."); // 100%
```

**Progress Update Method**:

```php
private function updateProgress(int $percentage, string $status, string $message): void {
    $progressKey = "recalc_progress_{$this->classId}_{$this->subjectType}_{$this->selectedTermId}";

    $data = [
        'job_id' => $this->jobId,
        'percentage' => $percentage,
        'status' => $status, // 'queued', 'processing', 'completed', 'failed'
        'message' => $message,
        'updated_at' => now()->toIso8601String(),
    ];

    Cache::put($progressKey, $data, 7200); // Cache for 2 hours

    // Debug logging
    Log::debug("Progress updated in cache", [
        'key' => $progressKey,
        'percentage' => $percentage,
        'status' => $status,
        'verification' => Cache::has($progressKey) ? 'verified' : 'FAILED'
    ]);
}
```

#### 4.4 Grade Processing Logic

**Step 1: Fetch Subjects**

```php
private function getGradeSubjectIdsByType(int $gradeId, int $termId, string $subjectType): Collection {
    if ($subjectType === 'klass_subjects') {
        return KlassSubject::where('grade_id', $gradeId)
            ->where('term_id', $termId)
            ->where('active', true)
            ->pluck('grade_subject_id')
            ->unique();
    } else {
        return OptionalSubject::where('grade_id', $gradeId)
            ->where('term_id', $termId)
            ->where('active', true)
            ->pluck('grade_subject_id')
            ->unique();
    }
}
```

**Step 2: Process Each Subject's Tests**

```php
private function processSubjectGrades(int $gradeSubjectId, int $termId, int $year): array {
    $processedStudents = [];
    $processedTests = 0;

    // Get all tests for this subject
    $tests = Test::where('grade_subject_id', $gradeSubjectId)
        ->where('term_id', $termId)
        ->where('year', $year)
        ->get(['id', 'type', 'out_of']);

    $testIds = $tests->pluck('id')->all();

    // Process student tests in chunks for memory efficiency
    DB::transaction(function () use ($testIds, $gradeSubjectId, &$processedStudents, &$processedTests) {
        StudentTest::whereIn('test_id', $testIds)
            ->whereNotNull('score')
            ->with('test')
            ->chunkById(200, function ($chunk) use ($gradeSubjectId, &$processedStudents, &$processedTests) {
                foreach ($chunk as $st) {
                    if ($st->test->out_of == 0) continue;

                    $processedTests++;
                    $processedStudents[] = $st->student_id;

                    // Calculate percentage
                    $percentage = round($st->score / $st->test->out_of * 100);

                    // Get grade and points from grading scale
                    $gradeObj = $this->getGradePerSubject($gradeSubjectId, $percentage);

                    // Update student test record
                    $st->update([
                        'percentage' => $percentage,
                        'grade' => $gradeObj->grade,
                        'points' => $gradeObj->points,
                    ]);

                    // Generate subject comment for exams
                    if ($st->test->type === 'Exam') {
                        $comment = AssessmentHelper::getRandomCommentForScore($percentage);
                        SubjectComment::updateOrCreate([...], ['remarks' => $comment]);
                    }
                }
            });
    });

    // Process CA (Continuous Assessment) averages
    $this->processCAverages($caTestIds, $gradeSubjectId);

    return [
        'students' => array_unique($processedStudents),
        'tests' => $processedTests
    ];
}
```

**Step 3: Calculate CA Averages**

```php
private function processCAverages(array $caTestIds, int $gradeSubjectId): void {
    $cas = StudentTest::whereIn('test_id', $caTestIds)
        ->whereNotNull('percentage')
        ->select('student_id', DB::raw('ROUND(AVG(percentage)) as avg_perc'))
        ->groupBy('student_id')
        ->get();

    foreach ($cas as $row) {
        $gradeObj = $this->getGradePerSubject($gradeSubjectId, $row->avg_perc);

        StudentTest::where('student_id', $row->student_id)
            ->whereIn('test_id', $caTestIds)
            ->update([
                'avg_score' => $row->avg_perc,
                'avg_grade' => $gradeObj->grade,
            ]);
    }
}
```

**Step 4: Generate Student Remarks**

```php
private function generateRemarksInBatches(array $studentIds, int $chunkSize): void {
    $remarkService = app(RemarkGenerationService::class);

    foreach (array_chunk($studentIds, $chunkSize) as $chunk) {
        foreach ($chunk as $studentId) {
            try {
                $remarkService->generateRemarksForStudent($studentId);
            } catch (Exception $e) {
                Log::warning("Failed to generate remarks for student {$studentId}");
            }
        }
    }
}
```

#### 4.5 Memory Management

```php
// Increase memory limit
ini_set('memory_limit', '1024M');

// Clear memory periodically during processing
if ($subjectsProcessed % 10 === 0) {
    gc_collect_cycles();
}

// Use chunking for large datasets
->chunkById(200, function ($chunk) { /* ... */ });
```

---

### 5. Frontend Polls for Progress

**Location**: `markbook-junior-class-list.blade.php` (Lines 945-1045)

#### 5.1 Start Polling

```javascript
function startProgressPolling(subjectType) {
    const classId = '{{ $klass->klass->id ?? '' }}';
    const progressUrl = '{{ route("assessment.recalculate-progress", ["id" => ":id"]) }}'.replace(':id', classId);

    console.log('Starting progress polling:', { classId, subjectType, progressUrl });

    // Reset progress UI and poll counter
    resetProgressUI();
    pollAttempts = 0;
    progressStartTime = Date.now();

    // Initial poll
    pollProgress(progressUrl, subjectType);

    // Poll every 500ms
    progressInterval = setInterval(function() {
        pollProgress(progressUrl, subjectType);
    }, 500);
}
```

**Configuration**:
- Polling interval: **500ms** (optimized for fast-completing jobs)
- Max startup attempts: **30** (30 × 500ms = 15 seconds before timeout)
- Minimum display time: **2 seconds** (ensures progress is visible even for fast jobs)

#### 5.2 Poll Progress Endpoint

```javascript
function pollProgress(url, subjectType) {
    $.ajax({
        url: url,
        method: 'GET',
        data: {
            subject_type: subjectType // CRITICAL: Must match job's cache key
        },
        success: function(progress) {
            console.log('Progress received:', progress);
            pollAttempts++;
            updateProgressUI(progress);

            // Stop polling if completed or failed
            if (progress.status === 'completed' || progress.status === 'failed') {
                clearInterval(progressInterval);
                handleCompletion(progress);
            }
        },
        error: function(xhr) {
            pollAttempts++;

            if (xhr.status === 404) {
                // Job might still be starting up
                if (pollAttempts <= maxStartupAttempts) {
                    $('#progressMessage').text('Job starting, please wait...');
                } else {
                    // Timeout after 15 seconds
                    clearInterval(progressInterval);
                    handleCompletion({
                        status: 'failed',
                        message: 'Job failed to start after 15 seconds. Please try again.'
                    });
                }
            }
        }
    });
}
```

#### 5.3 Update Progress UI

```javascript
function updateProgressUI(progress) {
    const percentage = progress.percentage || 0;
    const message = progress.message || 'Processing...';

    // Update modal
    $('#progressPercentage').text(percentage + '%');
    $('#progressMessage').text(message);
    $('#progressBar').css('width', percentage + '%');
    $('#progressBarText').text(percentage + '%');

    // Update minimized indicator (if minimized)
    $('#minimizedPercentage').text(percentage + '%');
    $('#minimizedMessage').text(message);
    $('#minimizedProgressBar').css('width', percentage + '%');

    // Change color based on status
    if (progress.status === 'completed') {
        $('#progressBar').removeClass('bg-primary').addClass('bg-success');
        $('#progressSpinner').hide();
    } else if (progress.status === 'failed') {
        $('#progressBar').removeClass('bg-primary').addClass('bg-danger');
        $('#progressSpinner').hide();
    }
}
```

#### 5.4 Handle Completion

```javascript
function handleCompletion(progress) {
    if (progress.status === 'completed') {
        // Calculate elapsed time
        const elapsedTime = Date.now() - progressStartTime;
        const minDisplayTime = 2000; // Minimum 2 seconds
        const remainingTime = Math.max(0, minDisplayTime - elapsedTime);

        // Keep modal open for remaining time
        setTimeout(() => {
            $('#progressModal').modal('hide');

            // Show success message and reload
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }, remainingTime);

    } else if (progress.status === 'failed') {
        // Stop animation
        $('#progressBar').removeClass('progress-bar-animated');
        $('#progressSpinner').hide();

        // Show error message
        $('#progressMessage').text(progress.message || 'Recalculation failed.');

        // Auto-hide after 5 seconds
        setTimeout(() => {
            $('#progressModal').modal('hide');
        }, 5000);
    }
}
```

**Minimum Display Time Logic**:
- Tracks when progress modal opens (`progressStartTime`)
- Calculates elapsed time when job completes
- If job completes in < 2 seconds, keeps modal open for remaining time
- Ensures users can see the progress bar even for fast jobs

#### 5.5 Minimize/Maximize Feature

```javascript
$('#minimizeProgressBtn').on('click', function() {
    isMinimized = true;
    $('#progressModal').modal('hide');
    $('#minimizedProgressIndicator').fadeIn();
});

$('#minimizedProgressIndicator').on('click', function() {
    isMinimized = false;
    $(this).fadeOut();
    $('#progressModal').modal('show');
});
```

Allows users to minimize the progress modal and continue working while the job runs in the background.

---

### 6. Controller Returns Progress

**Location**: `AssessmentController.php::checkRecalculationProgress()` (Lines 762-785)

```php
public function checkRecalculationProgress($id, Request $request) {
    $subjectType = $request->input('subject_type');
    $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()?->id);

    // Build cache key (MUST match job's key format)
    $progressKey = "recalc_progress_{$id}_{$subjectType}_{$selectedTermId}";
    $progress = Cache::get($progressKey);

    // Debug logging
    Log::debug("Progress check", [
        'key' => $progressKey,
        'found' => $progress ? 'yes' : 'no',
        'cache_driver' => config('cache.default')
    ]);

    if (!$progress) {
        return response()->json([
            'status' => 'not_found',
            'message' => 'No active recalculation found.'
        ], 404);
    }

    return response()->json($progress);
}
```

**Response Format**:
```json
{
    "job_id": "recalc_690cbf481b8683.69989050",
    "percentage": 45,
    "status": "processing",
    "message": "Processing subject 3/6...",
    "updated_at": "2025-11-06T15:31:23+00:00"
}
```

---

## Cache Key Format

**Critical**: All three components (controller initialization, job updates, controller checks) must use the EXACT same cache key format:

```
recalc_progress_{classId}_{subjectType}_{termId}
```

**Example**: `recalc_progress_37_klass_subjects_3`

**Components**:
- `classId`: The class/grade being recalculated (e.g., 37)
- `subjectType`: Either `klass_subjects` or `optional_subjects`
- `termId`: The term being recalculated (e.g., 3)

**Mismatch Issues**:
If the frontend passes an empty `subject_type`, the key becomes `recalc_progress_37__3` (double underscore), which won't match the job's key `recalc_progress_37_klass_subjects_3`, causing 404 errors.

---

## Progress Modal UI

**Location**: `markbook-junior-class-list.blade.php` (Lines 382-433)

### Main Progress Modal

```html
<div class="modal fade" id="progressModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="bx bx-loader-circle bx-spin" id="progressSpinner"></i>
                    Grade Recalculation
                </h5>
                <button type="button" id="minimizeProgressBtn">
                    <i class="bx bx-minus"></i> Run in Background
                </button>
            </div>
            <div class="modal-body pt-3">
                <div class="d-flex justify-content-between mb-2">
                    <span id="progressMessage">Initializing...</span>
                    <span id="progressPercentage">0%</span>
                </div>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                         id="progressBar" style="width: 0%;">
                        <span id="progressBarText">0%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

**Features**:
- Animated striped progress bar
- Real-time percentage display (both inside and outside bar)
- Status message showing current operation
- Cannot be dismissed (static backdrop)
- Minimize button to run in background

### Minimized Progress Indicator

```html
<div id="minimizedProgressIndicator" style="position: fixed; bottom: 20px; right: 20px;">
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="bx bx-loader-circle bx-spin"></i>
            <span>Recalculating Grades</span>
        </div>
        <span class="badge" id="minimizedPercentage">0%</span>
    </div>
    <div class="progress" style="height: 8px;">
        <div class="progress-bar" id="minimizedProgressBar"></div>
    </div>
    <small id="minimizedMessage">Initializing...</small>
</div>
```

Compact indicator that appears in bottom-right corner when modal is minimized.

---

## Routes

**Location**: `routes/assessment/assessment.php`

```php
// Progress polling endpoint
Route::get('/recalculate/progress/{id}', [AssessmentController::class, 'checkRecalculationProgress'])
    ->name('assessment.recalculate-progress');

// Recalculation dispatch endpoint
Route::post('/recalculate/{id}', [AssessmentController::class, 'recalculateGradesForGrade'])
    ->name('assessment.recalculate');
```

---

## Error Handling

### Job Failures

```php
try {
    // Processing logic...
} catch (Exception $e) {
    Log::error('Fatal error during grade recalculation job', [
        'job_id' => $this->jobId,
        'class_id' => $this->classId,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);

    $this->updateProgress(0, 'failed', 'Recalculation failed: ' . $e->getMessage());
    $lock->release();
    $this->fail($e);
}
```

### Frontend Timeout

```javascript
if (pollAttempts > maxStartupAttempts) {
    clearInterval(progressInterval);
    handleCompletion({
        status: 'failed',
        message: 'Job failed to start after 15 seconds. Please try again.'
    });
}
```

### Cache Misses

If the progress cache entry doesn't exist, the controller returns 404, and the frontend shows "Job starting, please wait..." for up to 15 seconds.

---

## Performance Optimizations

### 1. Memory Management
```php
ini_set('memory_limit', '1024M');           // Increase limit
gc_collect_cycles();                         // Clear memory every 10 subjects
->chunkById(200, function ($chunk) { });     // Process in chunks
```

### 2. Database Optimization
```php
DB::transaction(function () { /* ... */ });  // Use transactions
->with('test')                               // Eager load relationships
->select(['id', 'type', 'out_of'])          // Only select needed columns
```

### 3. Polling Optimization
- Fast polling interval (500ms) to catch quick jobs
- Timeout after 15 seconds to avoid infinite loops
- Minimum display time to ensure visibility

### 4. Cache Strategy
- 2-hour cache expiry for progress entries
- Cache lock for job deduplication (1-hour expiry)
- Cache verification after writes (debug logging)

---

## Typical Execution Timeline (Production)

Based on production logs for Grade 10, 6 subjects, 1488 tests, 62 students:

| Time (seconds) | Event | Progress |
|----------------|-------|----------|
| 0.0 | Job dispatched by user | - |
| 0.0 | Controller initializes cache entry | 0% (queued) |
| 0.0 | Frontend starts polling every 500ms | - |
| 1.0 | Queue worker picks up job | - |
| 1.0 | Job starts processing | 0% (processing) |
| 1.0 | Fetching subjects | 5% |
| 1.0 | Start processing subjects | 10% |
| 1.0-6.0 | Processing 6 subjects | 22%, 33%, 45%, 57%, 68%, 80% |
| 6.0 | Generating remarks for 62 students | 85% |
| 7.0 | Recalculation completed | 100% (completed) |
| 7.0-9.0 | Modal stays open (minimum display) | - |
| 9.0 | Page reloads with updated data | - |

**Total Duration**: ~7 seconds processing + 2 seconds display = 9 seconds

---

## Debugging Tips

### Enable Debug Logging

Both the job and controller include debug logging:

```php
// Job logs cache writes
Log::debug("Progress updated in cache", [
    'key' => $progressKey,
    'percentage' => $percentage,
    'verification' => Cache::has($progressKey) ? 'verified' : 'FAILED'
]);

// Controller logs cache reads
Log::debug("Progress check", [
    'key' => $progressKey,
    'found' => $progress ? 'yes' : 'no'
]);
```

### Common Issues

1. **Progress Bar Stuck at 0%**
   - Check cache key mismatch (subject_type parameter)
   - Verify queue worker is running: `php artisan queue:work`
   - Check cache driver configuration

2. **"Job failed to start after 15 seconds"**
   - Queue worker not running
   - Job failed immediately during initialization
   - Database connection issues

3. **Option Cards Not Selectable**
   - Duplicate event listeners from AJAX reloads
   - DOM cloning/replacement not working correctly
   - Check browser console for JavaScript errors

4. **Modal Not Showing Progress**
   - Wrong blade file (markbook-class-list vs markbook-junior-class-list)
   - JavaScript not capturing subject type correctly
   - Check network tab for polling requests

---

## Production Deployment Checklist

1. **Clear caches**:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

2. **Restart queue workers**:
   ```bash
   php artisan queue:restart
   ```

3. **Verify queue worker is running**:
   ```bash
   php artisan queue:work --daemon
   ```

4. **Test with small dataset first**

5. **Monitor logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

6. **Check memory usage** during large recalculations

---

## Future Enhancements (Phase 2-4)

### Phase 2: Enhanced Error Handling
- Detailed error messages per subject
- Partial completion tracking
- Resume capability for failed jobs

### Phase 3: Batch Processing
- Process multiple grades simultaneously
- Priority queue for urgent recalculations
- Scheduled recalculations (cron jobs)

### Phase 4: Advanced Features
- Real-time WebSocket updates (remove polling)
- Email notifications on completion
- CSV export of affected records
- Rollback capability
- Audit trail of recalculations

---

## Technical Specifications

### System Requirements
- **PHP**: 7.4+ (8.0+ recommended)
- **Laravel**: 8.x or higher
- **Cache Driver**: File, Redis, or Memcached
- **Queue Driver**: Database (can use Redis for better performance)
- **Memory**: Minimum 1024MB for large grades

### Browser Compatibility
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Performance Benchmarks
- **Small Grade** (1-2 subjects, 20 students): ~2-3 seconds
- **Medium Grade** (5-7 subjects, 50 students): ~5-10 seconds
- **Large Grade** (10+ subjects, 100+ students): ~15-30 seconds

---

## Credits

**Developed By**: Claude Code Assistant
**System**: Junior School Management System
**Module**: Assessment & Grade Management
**Version**: 1.0
**Date**: November 2025

---

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Review this documentation
3. Contact system administrator
