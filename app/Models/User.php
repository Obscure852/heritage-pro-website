<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    protected static $roleTablesAvailable;

    public $timestamps = true;

    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'email',
        'avatar',
        'date_of_birth',
        'gender',
        'position',
        'reporting_to',
        'department',
        'area_of_work',
        'personal_payroll_number',
        'dpsm_personal_file_number',
        'date_of_appointment',
        'earning_band',
        'nationality',
        'signature_path',
        'sms_signature',
        'email_signature',
        'phone',
        'id_number',
        'city',
        'address',
        'active',
        'status',
        'user_filter_id',
        'username',
        'password',
        'last_updated_by',
        'year',
    ];


    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_users', 'user_id')->withTimestamps();;
    }

    public function hasRoles($roleName)
    {
        if (!$this->roleTablesExist()) {
            return false;
        }

        return $this->roles()->where('name', $roleName)->exists();
    }

    public function filter()
    {
        return $this->belongsTo(UserFilter::class);
    }

    public function hasAnyRoles($roles)
    {
        if (!$this->roleTablesExist()) {
            return false;
        }

        if (is_array($roles)) {
            return $this->roles()->whereIn('name', $roles)->exists();
        }
        return $this->roles()->where('name', $roles)->exists();
    }

    protected function roleTablesExist(): bool
    {
        if (self::$roleTablesAvailable !== null) {
            return self::$roleTablesAvailable;
        }

        try {
            self::$roleTablesAvailable = Schema::hasTable('roles') && Schema::hasTable('role_users');
        } catch (\Throwable $exception) {
            self::$roleTablesAvailable = false;
        }

        return self::$roleTablesAvailable;
    }

    public function scopeTeachingAndCurrent($query)
    {
        return $query->where('area_of_work', 'Teaching')->where('status', 'Current');
    }

    public static function getFillableAttributes()
    {
        return (new static)->fillable;
    }

    public static function isSchoolHeadPositionAvailable()
    {
        return !static::where('position', 'School Head')->exists();
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_appointment' => 'date',
    ];


    public function getFormattedDateOfBirthAttribute(): string
    {
        if (empty($this->date_of_birth)) {
            return '';
        }
        return \Carbon\Carbon::parse($this->date_of_birth)->format('d/m/Y');
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->firstname} {$this->lastname}");
    }

    public function subjects()
    {
        return $this->belongsTo(GradeSubject::class);
    }


    public function klass()
    {
        return $this->hasMany(Klass::class, 'user_id');
    }


    public function logs()
    {
        return $this->hasMany(Logging::class, 'user_id', 'id');
    }

    public function klasses()
    {
        return $this->hasMany(KlassSubject::class, 'user_id');
    }

    public function klassSubjects()
    {
        return $this->hasMany(KlassSubject::class, 'user_id');
    }

    public function qualifications()
    {
        return $this->belongsToMany(Qualification::class)
            ->using(QualificationUser::class)->withPivot('level', 'college', 'start_date', 'completion_date')
            ->whereNull('qualification_user.deleted_at')
            ->withTimestamps();
    }

    public function workHistory()
    {
        return $this->hasMany(WorkHistory::class);
    }

    public function taughtOptionalSubjects(){
        return $this->hasMany(OptionalSubject::class, 'user_id');
    }

    public function taughtOptionalSubjectsForTerm($termId){
        return $this->hasMany(OptionalSubject::class, 'user_id')
                    ->where('term_id', $termId);
    }

    /**
     * Get the teacher's threshold preference for markbook highlighting.
     */
    public function thresholdPreference()
    {
        return $this->hasOne(TeacherThresholdPreference::class, 'user_id');
    }

    public function headedDepartments()
    {
        return $this->hasMany(Department::class, 'department_head');
    }

    public function assistedDepartments(){
        return $this->hasMany(Department::class, 'department_assistant');
    }

    public function housesAsHead(){
        return $this->hasMany(House::class, 'head');
    }

    public function housesAsAssistant(){
        return $this->hasMany(House::class, 'assistant');
    }

    public function allocatedHouses()
    {
        return $this->belongsToMany(House::class, 'user_house', 'user_id', 'house_id')->withPivot('term_id')->withTimestamps();
    }

    public function term(){
        return $this->hasMany(House::class, 'term_id');
    }

    public function loggings(){
        return $this->hasMany(Logging::class);
    }

    public function notifications(){
        return $this->belongsToMany(Notification::class, 'notification_user')->withTimestamps();
    }

    public function messages(){
        return $this->hasMany(Message::class);
    }

    public function channelConsents()
    {
        return $this->morphMany(RecipientChannelConsent::class, 'recipient');
    }

    public function authoredMessages(){
        return $this->hasMany(Message::class, 'author');
    }

    public function staffDirectConversationsAsUserOne()
    {
        return $this->hasMany(StaffDirectConversation::class, 'user_one_id');
    }

    public function staffDirectConversationsAsUserTwo()
    {
        return $this->hasMany(StaffDirectConversation::class, 'user_two_id');
    }

    public function staffDirectMessagesSent()
    {
        return $this->hasMany(StaffDirectMessage::class, 'sender_id');
    }

    public function receivedMessages(){
        return $this->hasMany(Message::class, 'user_id');
    }

    public function sentEmails(){
        return $this->hasMany(Email::class, 'sender_id');
    }

    public function receivedEmails(){
        return $this->hasMany(Email::class, 'user_id');
    }

    public function documentQuota(): \Illuminate\Database\Eloquent\Relations\HasOne {
        return $this->hasOne(UserDocumentQuota::class);
    }

    public function profileMetadata()
    {
        return $this->hasMany(UserProfileMetadata::class, 'user_id');
    }

    public function pdpPlans()
    {
        return $this->hasMany(\App\Models\Pdp\PdpPlan::class, 'user_id');
    }

    public function launchedPdpRollouts()
    {
        return $this->hasMany(\App\Models\Pdp\PdpRollout::class, 'launched_by');
    }

    public function fallbackPdpRollouts()
    {
        return $this->hasMany(\App\Models\Pdp\PdpRollout::class, 'fallback_supervisor_user_id');
    }

    public function supervisedPdpPlans()
    {
        return $this->hasMany(\App\Models\Pdp\PdpPlan::class, 'supervisor_id');
    }

    public function pdpSignatures()
    {
        return $this->hasMany(\App\Models\Pdp\PdpPlanSignature::class, 'signer_user_id');
    }

    public function getProfileMetadataValue(string $key, $default = null)
    {
        $metadata = $this->profileMetadata()
            ->where('key', $key)
            ->first();

        return $metadata?->value ?? $default;
    }

    public function hasAllocatedClass(){
        return $this->klass()->where('active', 1)->exists() || $this->hasAnyRoles(['Administrator', 'HOD', 'Assessment Admin', 'Academic Admin', 'Communications Admin']);
    }

    public function hasValidPhoneNumber(){
        $localPhoneRegex = '/^002677\d{7}$/';
        $shortPhoneRegex = '/^7\d{7}$/';
        return isset($this->phone) && (preg_match($localPhoneRegex, $this->phone) || preg_match($shortPhoneRegex, $this->phone));
    }

    public function hasValidEmail(){
        return filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function reportsTo(){
        return $this->belongsTo(User::class, 'reporting_to');
    }

    public function subordinates(){
        return $this->hasMany(User::class, 'reporting_to');
    }

    public function isSchoolHead(){
        return $this->position === 'School Head' && is_null($this->reporting_to);
    }

    public function getAllSubordinates(){
        $allSubordinates = collect();
        foreach ($this->subordinates as $subordinate) {
            $allSubordinates->push($subordinate);
            $allSubordinates = $allSubordinates->merge($subordinate->getAllSubordinates());
        }
        return $allSubordinates;
    }


    function parseUrl($url){
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'];
        $segments = explode('/', trim($path, '/'));

        $entity = isset($segments[0]) ? $segments[0] : null;
        $action = isset($segments[1]) ? $segments[1] : null;
        $id = isset($segments[2]) ? $segments[2] : null;

        return [
            'entity' => $entity,
            'action' => $action,
            'id' => $id,
        ];
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

    public function assetAssignments(){
        return $this->morphMany(AssetAssignment::class, 'assignable');
    }

    // ==================== WELFARE RELATIONSHIPS ====================

    public function openedWelfareCases()
    {
        return $this->hasMany(Welfare\WelfareCase::class, 'opened_by');
    }

    public function assignedWelfareCases()
    {
        return $this->hasMany(Welfare\WelfareCase::class, 'assigned_to');
    }

    public function approvedWelfareCases()
    {
        return $this->hasMany(Welfare\WelfareCase::class, 'approved_by');
    }

    public function conductedCounselingSessions()
    {
        return $this->hasMany(Welfare\CounselingSession::class, 'counsellor_id');
    }

    public function reportedDisciplinaryRecords()
    {
        return $this->hasMany(Welfare\DisciplinaryRecord::class, 'reported_by');
    }

    public function resolvedDisciplinaryRecords()
    {
        return $this->hasMany(Welfare\DisciplinaryRecord::class, 'resolved_by');
    }

    public function reportedSafeguardingConcerns()
    {
        return $this->hasMany(Welfare\SafeguardingConcern::class, 'reported_by');
    }

    public function reportedHealthIncidents()
    {
        return $this->hasMany(Welfare\HealthIncident::class, 'reported_by');
    }

    public function treatedHealthIncidents()
    {
        return $this->hasMany(Welfare\HealthIncident::class, 'treated_by');
    }


    public function createdInterventionPlans()
    {
        return $this->hasMany(Welfare\InterventionPlan::class, 'created_by');
    }

    public function conductedInterventionPlanReviews()
    {
        return $this->hasMany(Welfare\InterventionPlanReview::class, 'reviewed_by');
    }

    public function parentCommunications()
    {
        return $this->hasMany(Welfare\ParentCommunication::class, 'communicated_by');
    }

    public function welfareNotes()
    {
        return $this->hasMany(Welfare\WelfareCaseNote::class, 'created_by');
    }

    public function welfareAttachments()
    {
        return $this->hasMany(Welfare\WelfareCaseAttachment::class, 'uploaded_by');
    }

    public function welfareAuditLogs()
    {
        return $this->hasMany(Welfare\WelfareAuditLog::class, 'user_id');
    }

    /**
     * Check if user has welfare-related roles.
     */
    public function hasWelfareAccess(): bool
    {
        return $this->hasAnyRoles([
            'Administrator',
            'School Counsellor',
            'Welfare Admin',
            'Welfare View',
            'Nurse',
        ]);
    }

    /**
     * Check if user can manage welfare cases.
     */
    public function canManageWelfare(): bool
    {
        return $this->hasAnyRoles([
            'Administrator',
            'School Counsellor',
            'Welfare Admin',
        ]);
    }

    /**
     * Get count of assigned open welfare cases.
     */
    public function getAssignedOpenCasesCountAttribute(): int
    {
        return $this->assignedWelfareCases()->open()->count();
    }

    // ==================== LEAVE RELATIONSHIPS ====================

    public function leaveRequests()
    {
        return $this->hasMany(Leave\LeaveRequest::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(Leave\LeaveBalance::class);
    }

    public function approvableLeaveRequests()
    {
        return $this->hasManyThrough(
            Leave\LeaveRequest::class,
            User::class,
            'reporting_to',  // Foreign key on users table
            'user_id',       // Foreign key on leave_requests table
            'id',            // Local key on this user
            'id'             // Local key on subordinate users
        )->where('leave_requests.status', Leave\LeaveRequest::STATUS_PENDING);
    }

    public function leaveAttachments()
    {
        return $this->hasMany(Leave\LeaveAttachment::class, 'uploaded_by');
    }

    public function leaveBalanceAdjustments()
    {
        return $this->hasMany(Leave\LeaveBalanceAdjustment::class, 'adjusted_by');
    }

    public function approvedLeaveRequests()
    {
        return $this->hasMany(Leave\LeaveRequest::class, 'approved_by');
    }

    public function cancelledLeaveRequests()
    {
        return $this->hasMany(Leave\LeaveRequest::class, 'cancelled_by');
    }

    /**
     * Check if user has leave management access.
     */
    public function hasLeaveAccess(): bool
    {
        return $this->hasAnyRoles([
            'Administrator',
            'HR Admin',
            'Leave Admin',
        ]);
    }

    /**
     * Check if user can approve leave requests.
     */
    public function canApproveLeave(): bool
    {
        // Users can approve if they have subordinates or have admin roles
        return $this->subordinates()->exists() || $this->hasLeaveAccess();
    }

    /**
     * Get count of pending leave requests for approval.
     */
    public function getPendingLeaveApprovalsCountAttribute(): int
    {
        return Leave\LeaveRequest::forApprover($this->id)->pending()->count();
    }
}
