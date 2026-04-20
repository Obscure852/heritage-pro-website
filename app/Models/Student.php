<?php

namespace App\Models;

use App\Helpers\TermHelper;
use App\Notifications\StudentResetPasswordNotification;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable{
    use HasFactory, SoftDeletes, Notifiable;
    public $timestamps = true;

    protected $fillable = [
        'connect_id',
        'sponsor_id',
        'photo_path',
        'first_name',
        'last_name',
        'middle_name',
        'exam_number',
        'gender',
        'date_of_birth',
        'email',
        'nationality',
        'id_number',
        'status',
        'credit',
        'parent_is_staff',
        'is_boarding',
        'student_filter_id',
        'student_type_id',
        'year',
        'password',
        'remember_token',
        'last_updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'credit' => 'decimal:2',
        'parent_is_staff' => 'boolean',
        'is_boarding' => 'boolean',
    ];

    // Status constants
    const STATUS_CURRENT = 'Current';
    const STATUS_LEFT = 'Left';
    const STATUS_SUSPENDED = 'Suspended';
    const STATUS_GRADUATED = 'Graduated';

    // Gender constants
    const GENDER_MALE = 'M';
    const GENDER_FEMALE = 'F';

    // Boarding constants
    const BOARDING = true;
    const DAY = false;

    public function getFormattedDateOfBirthAttribute(): string
    {
        if (empty($this->date_of_birth)) {
            return '';
        }
        return $this->date_of_birth->format('d/m/Y');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new StudentResetPasswordNotification($token));
    }

    public function scopeInTerm($query, $termId)
    {
        return $query->whereHas('terms', function ($query) use ($termId) {
            $query->where('student_term.term_id', $termId);
        });
    }

    public function scopeInTermWithActiveGrade($query, $termId){
        return $query->whereHas('terms', function ($query) use ($termId) {
            $query->where('term_id', $termId)
                ->where('status', 'Current')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('grades')
                        ->join('student_term', 'grades.id', '=', 'student_term.grade_id')
                        ->whereRaw('grades.active = true')
                        ->whereRaw('student_term.student_id = students.id');
                });
        });
    }

    public function scopeNotAllocatedToClassInTerm($query, $termId)
    {
        return $query->whereHas('terms', function ($query) use ($termId) {
            $query->where('term_id', $termId)
                ->where('status', 'Current');
        })->whereDoesntHave('classes', function ($query) use ($termId) {
            $query->where('klasses.term_id', $termId);
        })->whereExists(function ($query) use ($termId) {
            $query->select(DB::raw(1))
                ->from('grades')
                ->join('student_term', 'grades.id', '=', 'student_term.grade_id')
                ->whereRaw('grades.active = true')
                ->whereRaw("student_term.term_id = {$termId}")
                ->whereRaw('student_term.student_id = students.id');
        });
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get student's house for the selected term.
     * Uses eager-loaded currentHouseRelation when available to avoid N+1 queries.
     */
    public function getHouseAttribute()
    {
        // Check if currentHouseRelation was eager loaded
        if ($this->relationLoaded('currentHouseRelation')) {
            return $this->currentHouseRelation->first();
        }

        // Fallback to query (should be avoided in listings)
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        return $this->houses()->wherePivot('term_id', $selectedTermId)->first();
    }

    /**
     * Get student's class for the selected term.
     * Uses eager-loaded currentClassRelation when available to avoid N+1 queries.
     */
    public function getClassAttribute()
    {
        // Check if currentClassRelation was eager loaded
        if ($this->relationLoaded('currentClassRelation')) {
            return $this->currentClassRelation->first();
        }

        // Fallback to query (should be avoided in listings)
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        return $this->classes()->wherePivot('term_id', $selectedTermId)->first();
    }

    /**
     * Eager-loadable relationship for current term's house.
     * Usage: Student::with(['currentHouseRelation' => fn($q) => $q->wherePivot('term_id', $termId)])
     */
    public function currentHouseRelation()
    {
        return $this->belongsToMany(House::class, 'student_house', 'student_id', 'house_id')
            ->withPivot('term_id');
    }

    function sponsor()
    {
        return $this->belongsTo(Sponsor::class, 'sponsor_id');
    }

    public function terms()
    {
        return $this->belongsToMany(Term::class)
            ->using(StudentTerm::class)
            ->withPivot('year', 'status')
            ->withTimestamps();
    }


    public function filter()
    {
        return $this->belongsTo(StudentFilter::class);
    }

    public function type()
    {
        return $this->belongsTo(StudentType::class, 'student_type_id');
    }

    public function departure()
    {
        return $this->hasOne(StudentDeparture::class);
    }

    public function studentTerms()
    {
        return $this->hasMany(StudentTerm::class);
    }

    public function manualAttendanceEntries()
    {
        return $this->hasMany(ManualAttendanceEntry::class);
    }

    public function currentGrade()
    {
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        return $this->hasOneThrough(
            Grade::class,
            StudentTerm::class,
            'student_id',
            'id',
            'id',
            'grade_id'
        )->where('student_term.term_id', $selectedTermId)
            ->where('student_term.status', 'Current');
    }

    public function studentTerm()
    {
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        return $this->studentTerms()->where('term_id', $selectedTermId)->sole();
    }

    public function absentDaysCount()
    {
        return $this->absentDays()->count();
    }

    public function absentDays()
    {
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $absentCodes = Attendance::getAbsentCodes();
        return $this->hasMany(Attendance::class)->where('term_id', $termId)->whereIn('status', $absentCodes);
    }

    public function classes()
    {
        return $this->belongsToMany(Klass::class, 'klass_student')->withPivot('term_id')->withTimestamps();
    }

    /**
     * Get student's current class for the selected term.
     * Uses eager-loaded currentClassRelation when available to avoid N+1 queries.
     */
    public function getCurrentClassAttribute()
    {
        // Check if currentClassRelation was eager loaded
        if ($this->relationLoaded('currentClassRelation')) {
            return $this->currentClassRelation->first();
        }

        // Fallback to query (should be avoided in listings)
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        return $this->classes()->wherePivot('term_id', $selectedTermId)->first();
    }


    public function tests()
    {
        return $this->belongsToMany(Test::class, 'student_tests')
            ->using(StudentTest::class)
            ->withPivot('avg_score', 'percentage', 'avg_grade', 'score', 'grade', 'points');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function overallComments()
    {
        return $this->hasMany(Comment::class, 'student_id');
    }

    public function subjectComments()
    {
        return $this->hasMany(SubjectComment::class, 'student_id');
    }

    public function studentMedicals()
    {
        return $this->hasOne(StudentMedicalInformation::class);
    }

    public function studentbehaviour()
    {
        return $this->hasMany(StudentBehaviour::class);
    }

    public function getCurrentTermAttribute()
    {
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;

        return DB::table('student_term')
            ->where('student_id', $this->id)
            ->where('term_id', $selectedTermId)
            ->where('status', 'Current')
            ->join('terms', 'student_term.term_id', '=', 'terms.id')
            ->select('terms.*', 'student_term.year', 'student_term.status')
            ->first();
    }

    public function getCurrentTermTestsAttribute()
    {
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;

        return DB::table('tests')->where('tests.term_id', $selectedTermId)
            ->where('tests.type', 'Exam')
            ->join('student_tests', 'tests.id', '=', 'student_tests.test_id')
            ->join('subjects', 'tests.grade_subject_id', '=', 'subjects.id')
            ->where('student_tests.student_id', $this->id)
            ->select('tests.*', 'student_tests.score', 'student_tests.grade', 'student_tests.percentage', 'subjects.name as subject_name')
            ->get();
    }

    public function currentTerm()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function getSubjectComment($termId, $gradeSubjectId)
    {
        return $this->hasMany(SubjectComment::class, 'student_id')->where('grade_subject_id', $gradeSubjectId)->where('term_id', $termId);
    }

    public function optionalSubjects()
    {
        return $this->belongsToMany(
            OptionalSubject::class,
            'student_optional_subjects',
            'student_id',
            'optional_subject_id'
        )->withPivot(['term_id','klass_id'])->withTimestamps();
    }

    public function currentClass()
    {
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        return $this->belongsToMany(Klass::class, 'klass_student')
            ->wherePivot('term_id', $selectedTermId)
            ->withTimestamps()->first();
    }

    public function bookAllocations()
    {
        return $this->hasMany(BookAllocation::class);
    }

    public function currentClassRelation()
    {
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        return $this->belongsToMany(Klass::class, 'klass_student')
            ->wherePivot('term_id', $selectedTermId)
            ->withTimestamps();
    }

    public function monitoredClasses()
    {
        return $this->hasMany(Klass::class, 'monitor_id');
    }

    public function monitressClasses()
    {
        return $this->hasMany(Klass::class, 'monitress_id');
    }

    public function houses()
    {
        return $this->belongsToMany(House::class, 'student_house', 'student_id', 'house_id')->withPivot('term_id');
    }


    public function psle()
    {
        return $this->hasOne(PSLE::class);
    }

    public function jce()
    {
        return $this->hasOne(JCE::class);
    }

    public function criteriaBasedStudentTests()
    {
        return $this->hasMany(CriteriaBasedStudentTest::class, 'student_id');
    }

    public function getFormattedPhoneAttribute(){
        if (empty($this->phone)) {
            return '';
        }

        $phone = preg_replace('/^00267/', '', $this->phone);
        $phone = preg_replace('/\s+/', '', $phone);

        if (strlen($phone) === 8) {
            return substr($phone, 0, 2) . ' ' .
                substr($phone, 2, 3) . ' ' .
                substr($phone, 5);
        }
        return $phone;
    }

    public function getFormattedTelephoneAttribute(){
        if (empty($this->telephone)) {
            return '';
        }

        $phone = preg_replace('/^00267/', '', $this->telephone);
        $phone = preg_replace('/\s+/', '', $phone);

        if (strlen($phone) === 7) {
            return substr($phone, 0, 3) . ' ' .
                substr($phone, 3);
        }
        return $phone;
    }

    public function getFormattedIdNumberAttribute(){
        if (empty($this->id_number)) {
            return '';
        }

        $idNumber = preg_replace('/\s+/', '', $this->id_number);
        $length = strlen($idNumber);

        if ($length <= 3) {
            return $idNumber;
        }

        $groups = [];
        $remainder = $length % 3;

        if ($remainder > 0) {
            $groups[] = substr($idNumber, 0, $remainder);
            $idNumber = substr($idNumber, $remainder);
        }

        $groups = array_merge($groups, str_split($idNumber, 3));
        return implode(' ', $groups);
    }

    // ==================== WELFARE RELATIONSHIPS ====================

    public function welfareCases()
    {
        return $this->hasMany(Welfare\WelfareCase::class);
    }

    public function counselingSessions()
    {
        return $this->hasMany(Welfare\CounselingSession::class);
    }

    public function disciplinaryRecords()
    {
        return $this->hasMany(Welfare\DisciplinaryRecord::class);
    }

    public function safeguardingConcerns()
    {
        return $this->hasMany(Welfare\SafeguardingConcern::class);
    }

    public function healthIncidents()
    {
        return $this->hasMany(Welfare\HealthIncident::class);
    }


    public function interventionPlans()
    {
        return $this->hasMany(Welfare\InterventionPlan::class);
    }

    public function parentCommunications()
    {
        return $this->hasMany(Welfare\ParentCommunication::class);
    }

    /**
     * Get all open welfare cases for this student.
     */
    public function openWelfareCases()
    {
        return $this->welfareCases()->open();
    }

    /**
     * Get high priority welfare cases for this student.
     */
    public function highPriorityWelfareCases()
    {
        return $this->welfareCases()->highPriority();
    }

    /**
     * Check if student has any open welfare cases.
     */
    public function hasOpenWelfareCases(): bool
    {
        return $this->welfareCases()->open()->exists();
    }

    /**
     * Check if student has active intervention plan.
     */
    public function hasActiveInterventionPlan(): bool
    {
        return $this->interventionPlans()->active()->exists();
    }

    /**
     * Get the count of welfare cases for this student.
     */
    public function getWelfareCasesCountAttribute(): int
    {
        return $this->welfareCases()->count();
    }

    // ==================== LMS Relationships ====================

    /**
     * Get all LMS course enrollments for this student.
     */
    public function lmsEnrollments()
    {
        return $this->hasMany(\App\Models\Lms\Enrollment::class);
    }

    /**
     * Get active LMS enrollments.
     */
    public function activeLmsEnrollments()
    {
        return $this->lmsEnrollments()->where('status', 'active');
    }

    /**
     * Get completed LMS enrollments.
     */
    public function completedLmsEnrollments()
    {
        return $this->lmsEnrollments()->where('status', 'completed');
    }

    /**
     * Get all LMS courses the student is enrolled in.
     */
    public function lmsCourses()
    {
        return $this->belongsToMany(\App\Models\Lms\Course::class, 'lms_enrollments', 'student_id', 'course_id')
            ->withPivot(['status', 'progress_percentage', 'grade', 'enrolled_at', 'completed_at'])
            ->withTimestamps();
    }

    /**
     * Get video progress records for this student.
     */
    public function lmsVideoProgress()
    {
        return $this->hasMany(\App\Models\Lms\VideoProgress::class);
    }

    /**
     * Get quiz attempts for this student.
     */
    public function lmsQuizAttempts()
    {
        return $this->hasMany(\App\Models\Lms\QuizAttempt::class);
    }

    /**
     * Check if student is enrolled in a specific course.
     */
    public function isEnrolledInCourse(int $courseId): bool
    {
        return $this->lmsEnrollments()->where('course_id', $courseId)->exists();
    }

    /**
     * Get enrollment for a specific course.
     */
    public function getEnrollmentForCourse(int $courseId): ?\App\Models\Lms\Enrollment
    {
        return $this->lmsEnrollments()->where('course_id', $courseId)->first();
    }

    /**
     * Get all learning path enrollments for this student.
     */
    public function learningPathEnrollments()
    {
        return $this->hasMany(\App\Models\Lms\LearningPathEnrollment::class);
    }

    /**
     * Get array of course IDs the student is enrolled in (active enrollments).
     */
    public function enrolledCourseIds(): array
    {
        return $this->lmsEnrollments()
            ->where('status', 'active')
            ->pluck('course_id')
            ->toArray();
    }

    // ==================== LMS Messaging Relationships ====================

    /**
     * Get all conversations for this student.
     */
    public function conversations()
    {
        return $this->hasMany(\App\Models\Lms\Conversation::class);
    }

    /**
     * Get active (non-archived) conversations for this student.
     */
    public function activeConversations()
    {
        return $this->conversations()->where('is_archived_by_student', false);
    }

    /**
     * Get unread conversation count for this student.
     */
    public function unreadConversationsCount(): int
    {
        return $this->activeConversations()
            ->get()
            ->filter(fn($conv) => $conv->hasUnreadForStudent())
            ->count();
    }

    // ==================== FEE ADMINISTRATION RELATIONSHIPS ====================

    /**
     * Get all invoices for this student.
     */
    public function invoices()
    {
        return $this->hasMany(\App\Models\Fee\StudentInvoice::class);
    }

    /**
     * Get all fee payments for this student.
     */
    public function feePayments()
    {
        return $this->hasMany(\App\Models\Fee\FeePayment::class);
    }

    /**
     * Get all discounts assigned to this student.
     */
    public function feeDiscounts()
    {
        return $this->hasMany(\App\Models\Fee\StudentDiscount::class);
    }

    /**
     * Get balance carryovers for this student.
     */
    public function balanceCarryovers()
    {
        return $this->hasMany(\App\Models\Fee\FeeBalanceCarryover::class);
    }

    /**
     * Get all refunds for this student.
     */
    public function feeRefunds()
    {
        return $this->hasMany(\App\Models\Fee\FeeRefund::class);
    }

    /**
     * Get all payment plans for this student.
     */
    public function paymentPlans()
    {
        return $this->hasMany(\App\Models\Fee\PaymentPlan::class);
    }

    /**
     * Get all late fee charges for this student (through invoices).
     */
    public function lateFeeCharges()
    {
        return $this->hasManyThrough(
            \App\Models\Fee\LateFeeCharge::class,
            \App\Models\Fee\StudentInvoice::class,
            'student_id',
            'student_invoice_id',
            'id',
            'id'
        );
    }

    /**
     * Get fee clearances for this student.
     */
    public function feeClearances()
    {
        return $this->hasMany(\App\Models\Fee\StudentClearance::class);
    }

    /**
     * Get the current outstanding fee balance for this student.
     */
    public function getCurrentFeeBalanceAttribute(): float
    {
        return $this->invoices()
            ->active()
            ->sum('balance');
    }

    /**
     * Check if student has any outstanding fees.
     */
    public function hasOutstandingFees(): bool
    {
        return $this->invoices()
            ->outstanding()
            ->exists();
    }

    /**
     * Get outstanding invoices for this student.
     */
    public function outstandingInvoices()
    {
        return $this->invoices()->outstanding();
    }

    /**
     * Get fee balance for a specific term.
     */
    public function getFeeBalanceForTerm(int $termId): float
    {
        return $this->invoices()
            ->forTerm($termId)
            ->active()
            ->sum('balance');
    }
}
