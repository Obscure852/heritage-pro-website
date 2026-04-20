<?php

namespace App\Providers;

use App\Models\Fee\FeePayment;
use App\Models\Fee\FeeRefund;
use App\Models\Fee\StudentInvoice;
use App\Helpers\TermHelper;
use App\Models\Klass;
use App\Models\Leave\LeaveBalance;
use App\Models\Leave\LeaveRequest;
use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\SchoolSetup;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use App\Models\Welfare\CounselingSession;
use App\Models\Welfare\DisciplinaryRecord;
use App\Models\Welfare\SafeguardingConcern;
use App\Models\Welfare\WelfareCase;
use App\Policies\AttendancePolicy;
use App\Policies\DocumentPolicy;
use App\Policies\Fee\FeePaymentPolicy;
use App\Policies\Fee\FeeRefundPolicy;
use App\Policies\Fee\StudentInvoicePolicy;
use App\Policies\KlassSubjectPolicy;
use App\Policies\Leave\LeaveBalancePolicy;
use App\Policies\Leave\LeaveRequestPolicy;
use App\Policies\StudentPolicy;
use App\Policies\TermPolicy;
use App\Policies\UserPolicy;
use App\Policies\Welfare\CounselingPolicy;
use App\Policies\Welfare\DisciplinaryPolicy;
use App\Policies\Welfare\SafeguardingPolicy;
use App\Policies\Welfare\WelfareCasePolicy;
use App\Services\SchoolModeResolver;
use Illuminate\Support\Str;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        User::class => UserPolicy::class,
        Klass::class => AttendancePolicy::class,
        KlassSubject::class => KlassSubjectPolicy::class,
        Term::class => TermPolicy::class,
        Student::class => StudentPolicy::class,
        \App\Models\Activities\Activity::class => \App\Policies\Activities\ActivityPolicy::class,
        // Welfare policies
        WelfareCase::class => WelfareCasePolicy::class,
        CounselingSession::class => CounselingPolicy::class,
        SafeguardingConcern::class => SafeguardingPolicy::class,
        DisciplinaryRecord::class => DisciplinaryPolicy::class,
        // Fee policies
        StudentInvoice::class => StudentInvoicePolicy::class,
        FeePayment::class => FeePaymentPolicy::class,
        FeeRefund::class => FeeRefundPolicy::class,
        // Leave policies
        LeaveRequest::class => LeaveRequestPolicy::class,
        LeaveBalance::class => LeaveBalancePolicy::class,
        // Document policies (model created in plan 01-02)
        'App\Models\Document' => DocumentPolicy::class,
        // Scheme of Work policies
        \App\Models\Schemes\SchemeOfWork::class => \App\Policies\SchemeOfWorkPolicy::class,
        // Standard Scheme policies
        \App\Models\Schemes\StandardScheme::class => \App\Policies\StandardSchemePolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        foreach ($this->policies as $model => $policy) {
            $definedPolicies = get_class_methods($policy);

            foreach ($definedPolicies as $method) {
                Gate::define($method, [$policy, $method]);
            }
        }

        Gate::define('access-admissions', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Admissions Admin', 'Admissions Edit', 'Admissions View']);
        });

        Gate::define('manage-admissions', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Admissions Admin', 'Admissions Edit']);
        });

        Gate::define('admissions-health', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Admissions Health']);
        });

        Gate::define('access-students', function ($user) {
            if ($user->hasAnyRoles(['Administrator', 'HOD', 'Academic Admin', 'Academic Edit', 'Students Admin', 'Class Teacher', 'Students Edit', 'Student View'])) {
                return true;
            }
            if (Klass::where('user_id', $user->id)->exists()) {
                return true;
            }
            return KlassSubject::where(function ($q) use ($user) { $q->where('user_id', $user->id)->orWhere('assistant_user_id', $user->id); })->exists()
                || OptionalSubject::where(function ($q) use ($user) { $q->where('user_id', $user->id)->orWhere('assistant_user_id', $user->id); })->exists();
        });

        Gate::define('students-view', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'HOD', 'Academic Admin', 'Academic Edit', 'Students Admin', 'Class Teacher', 'Students Edit', 'Students View', 'Students Health']);
        });

        Gate::define('manage-students', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'HOD', 'Academic Admin', 'Academic Edit', 'Students Admin', 'Students Edit']);
        });

        Gate::define('students-health', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Students Health']);
        });

        Gate::define('access-sponsors', function ($user) {
            if ($user->hasAnyRoles(['Administrator', 'HOD', 'Academic Admin', 'Sponsors Admin', 'Sponsors Edit', 'Sponsors View', 'Class Teacher'])) {
                return true;
            }
            if (Klass::where('user_id', $user->id)->exists()) {
                return true;
            }
            return KlassSubject::where(function ($q) use ($user) { $q->where('user_id', $user->id)->orWhere('assistant_user_id', $user->id); })->exists()
                || OptionalSubject::where(function ($q) use ($user) { $q->where('user_id', $user->id)->orWhere('assistant_user_id', $user->id); })->exists();
        });

        Gate::define('manage-sponsors', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'HOD', 'Academic Admin', 'Sponsors Admin', 'Sponsors Edit']);
        });

        Gate::define('access-hr', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'HR Admin', 'HR Edit', 'HR View']);
        });

        Gate::define('manage-hr', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'HR Admin', 'HR Edit']);
        });

        Gate::define('access-attendance', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Attendance Admin', 'Attendance View', 'HOD', 'Assessment Admin', 'Class Teacher']);
        });

        Gate::define('manage-attendance', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Academic Admin', 'Assessment Admin', 'Attendance Admin', 'HOD']);
        });

        $academicOverviewRoles = ['Administrator', 'HOD', 'Academic Admin', 'Academic Edit', 'Academic View', 'Assessment Admin'];
        $classAllocationAdminRoles = ['Administrator', 'HOD', 'Academic Admin', 'Academic Edit', 'Assessment Admin'];
        $optionalAdminRoles = ['Administrator', 'HOD', 'Academic Admin', 'Assessment Admin', 'Academic Edit'];

        $selectedAcademicTermId = function () {
            return session('selected_term_id', TermHelper::getCurrentTerm()->id);
        };

        $hasOptionalAssignmentOrSupervision = function (User $user, ?int $termId = null) {
            return OptionalSubject::query()
                ->when($termId, fn ($query) => $query->where('term_id', $termId))
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhere('assistant_user_id', $user->id)
                        ->orWhereHas('teacher', function ($teacherQuery) use ($user) {
                            $teacherQuery->where('reporting_to', $user->id);
                        })
                        ->orWhereHas('assistantTeacher', function ($assistantQuery) use ($user) {
                            $assistantQuery->where('reporting_to', $user->id);
                        });
                })
                ->exists();
        };

        Gate::define('access-class-allocations', function (User $user) use ($classAllocationAdminRoles, $selectedAcademicTermId) {
            if ($user->hasAnyRoles($classAllocationAdminRoles)) {
                return true;
            }

            if (!$user->hasRoles('Class Teacher')) {
                return false;
            }

            $selectedTermId = $selectedAcademicTermId();

            return Klass::where('user_id', $user->id)
                ->where('term_id', $selectedTermId)
                ->exists();
        });

        Gate::define('access-academic', function (User $user) use ($academicOverviewRoles) {
            if ($user->hasAnyRoles($academicOverviewRoles)) {
                return true;
            }

            return Gate::forUser($user)->allows('access-class-allocations')
                || Gate::forUser($user)->allows('access-optional');
        });

        Gate::define('manage-academic', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'HOD', 'Academic Admin', 'Assessment Admin', 'Academic Edit']);
        });

        Gate::define('access-optional', function (User $user) use ($optionalAdminRoles, $selectedAcademicTermId, $hasOptionalAssignmentOrSupervision) {
            if ($user->hasAnyRoles($optionalAdminRoles)) {
                return true;
            }

            return $hasOptionalAssignmentOrSupervision($user, $selectedAcademicTermId());
        });

        Gate::define('optional-teacher', function (User $user, OptionalSubject $optionalSubject) use ($optionalAdminRoles) {
            $teacherIds = array_values(array_filter([
                $optionalSubject->user_id,
                $optionalSubject->assistant_user_id,
            ]));

            $isTeacher = $user->id === (int) $optionalSubject->user_id;
            $isAssistant = $user->id === (int) $optionalSubject->assistant_user_id;
            $isSupervisor = !empty($teacherIds)
                && User::whereIn('id', $teacherIds)
                    ->where('reporting_to', $user->id)
                    ->exists();
            $hasAdminRole = $user->hasAnyRoles($optionalAdminRoles);

            return $isTeacher || $isAssistant || $isSupervisor || $hasAdminRole;
        });

        Gate::define('access-assessment', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Academic Admin', 'Assessment Admin', 'Academic Edit', 'Academic View', 'Teacher']);
        });

        Gate::define('access-markbook', function (User $user) {
            return app(SchoolModeResolver::class)->hasMarkbookAccess($user);
        });

        Gate::define('manage-assessment', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Assessment Admin', 'Assessment Edit', 'HOD', 'Academic Admin']);
        });

        Gate::define('access-learning-management', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Academic Admin', 'Academic Edit', 'Academic View', 'Teacher']);
        });

        Gate::define('manage-learning-management', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Assessment Admin', 'Assessment Edit', 'HOD', 'Academic Admin']);
        });

        // ==================== LMS GATES ====================

        // Basic access to LMS module (view courses, content)
        Gate::define('access-lms', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'Academic Admin',
                'Academic Edit',
                'Academic View',
                'Teacher',
                'HOD',
            ]);
        });

        // Manage LMS courses (create, edit, delete courses)
        Gate::define('manage-lms-courses', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'Academic Admin',
                'HOD',
            ]);
        });

        // Create and manage LMS content (modules, content items)
        Gate::define('manage-lms-content', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'Academic Admin',
                'HOD',
                'Teacher',
            ]);
        });

        // Manage enrollments (enroll/unenroll students)
        Gate::define('manage-lms-enrollments', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'Academic Admin',
                'HOD',
                'Teacher',
            ]);
        });

        // Grade quizzes and assignments
        Gate::define('grade-lms-content', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'Academic Admin',
                'HOD',
                'Teacher',
            ]);
        });

        // View LMS analytics and reports
        Gate::define('view-lms-analytics', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'Academic Admin',
                'HOD',
            ]);
        });

        Gate::define('class-teacher', function (User $user, Klass $klass) {
            $isClassTeacher = $user->id === $klass->user_id;
            $hasAllowedRole = $user->hasAnyRoles(['Administrator', 'Assessment Admin', 'Assessment Edit', 'HOD', 'Academic Admin']);
            return $isClassTeacher || $hasAllowedRole;
        });

        Gate::define('class-allocation-teacher', function (User $user, Klass $klass) use ($classAllocationAdminRoles) {
            $hasAdminRole = $user->hasAnyRoles($classAllocationAdminRoles);
            $isAssignedClassTeacher = $user->hasRoles('Class Teacher') && $user->id === (int) $klass->user_id;

            return $hasAdminRole || $isAssignedClassTeacher;
        });

        Gate::define('access-setup', function ($user) {
            return $user->hasAnyRoles(['System Setup']);
        });

        Gate::define('access-communications', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Communications Admin', 'Communications View', 'Academic Admin', 'HOD', 'Class Teacher']);
        });

        Gate::define('manage-communications', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Communications Admin', 'Communications Edit']);
        });

        Gate::define('email-communications', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Academic Admin', 'HOD', 'Communications Admin', 'Communications Edit', 'Class Teacher']);
        });

        Gate::define('sms-communications', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Communications Admin', 'Communications Edit']);
        });

        Gate::define('sms-admin', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Communications Admin', 'SMS Admin', 'Class Teacher']);
        });

        Gate::define('teacher', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Assessment Admin', 'Academic Admin', 'Teacher', 'House View', 'Student Admin']);
        });

        Gate::define('houses-access', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Houses Admin', 'Houses Edit', 'Houses View', 'Class Teacher', 'Teacher']);
        });

        Gate::define('manage-houses', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Houses Admin', 'Houses Edit']);
        });

        Gate::define('access-invigilation', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Academic Admin', 'HOD']);
        });

        Gate::define('access-invigilation-published-roster', function ($user) {
            return $user->hasAnyRoles(['Teacher', 'Class Teacher', 'Administrator', 'Academic Admin', 'HOD']);
        });

        Gate::define('manage-invigilation', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Academic Admin', 'HOD']);
        });

        Gate::define('access-activities', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'Activities Admin',
                'Activities Edit',
                'Activities View',
                'Activities Staff',
            ]);
        });

        Gate::define('manage-activities', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'Activities Admin',
                'Activities Edit',
            ]);
        });

        Gate::define('manage-activity-rosters', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'Activities Admin',
                'Activities Edit',
            ]);
        });

        Gate::define('manage-activity-settings', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'Activities Admin',
            ]);
        });

        Gate::define('access-asset-management', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Asset Management Admin', 'Asset Management Edit', 'Asset Management View']);
        });

        Gate::define('manage-assets', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Asset Management Admin', 'Asset Management Edit']);
        });

        Gate::define('fee-administration-access', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Fee Admin', 'Bursar']);
        });

        // Fee module gates
        Gate::define('manage-fee-setup', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Fee Admin', 'Bursar']);
        });

        Gate::define('collect-fees', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Fee Admin', 'Fee Collection', 'Bursar']);
        });

        Gate::define('void-payments', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Bursar']);
        });

        Gate::define('view-fee-reports', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Fee Admin', 'Fee Collection', 'Fee Reports', 'Bursar']);
        });

        Gate::define('export-fee-reports', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Bursar', 'Fee Reports']);
        });

        // Fee refund gates
        Gate::define('view-refunds', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Fee Admin', 'Bursar']);
        });

        Gate::define('request-refunds', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Fee Admin', 'Fee Collection', 'Bursar']);
        });

        Gate::define('approve-refunds', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Bursar']);
        });

        Gate::define('process-refunds', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Bursar']);
        });

        // Override historical year lock (for emergencies/corrections)
        Gate::define('override-historical-year-lock', function ($user) {
            return $user->hasAnyRoles(['Administrator']);
        });

        // ==================== LEAVE GATES ====================

        // Base access to leave module admin pages
        Gate::define('leave-administration-access', function ($user) {
            return $user->hasAnyRoles(['Administrator','HR Admin','HOD','Leave Admin']);
        });

        // Create/edit/delete leave types and policies
        Gate::define('manage-leave-types', function ($user) {
            return $user->hasAnyRoles(['Administrator','HR Admin','Leave Admin']);
        });

        // Manage public holidays
        Gate::define('manage-leave-holidays', function ($user) {
            return $user->hasAnyRoles(['Administrator','HR Admin','Leave Admin']);
        });

        // View all balances, make adjustments
        Gate::define('manage-leave-balances', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Leave Admin', 'HR Admin']);
        });

        // Configure leave module settings
        Gate::define('manage-leave-settings', function ($user) {
            return $user->hasAnyRoles(['Administrator','HR Admin','Leave Admin']);
        });

        // Approve/reject leave requests (future use in Phase 5)
        Gate::define('approve-leave-requests', function ($user) {
            if ($user->hasAnyRoles(['Administrator', 'Leave Admin', 'HR Admin'])) {
                return true;
            }
            return \App\Models\User::where('reporting_to', $user->id)->exists();
        });

        // View leave reports and analytics (future use in Phase 8)
        Gate::define('view-leave-reports', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Leave Admin', 'HR Admin', 'Leave View']);
        });

        // ==================== STAFF ATTENDANCE GATES ====================

        // Access staff attendance module (all authenticated staff)
        Gate::define('access-staff-attendance', function ($user) {
            return $user->status === 'Current';
        });

        // Manage staff attendance codes (add, edit, delete)
        Gate::define('manage-staff-attendance-codes', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'HR Admin', 'Leave Admin']);
        });

        // Manage staff attendance register (manual entry)
        Gate::define('manage-staff-attendance-register', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'HR Admin', 'Leave Admin']);
        });

        // View staff attendance reports
        Gate::define('view-attendance-reports', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'HR Admin', 'Leave Admin']);
        });

        // Manage staff attendance settings (configuration)
        Gate::define('manage-staff-attendance-settings', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'HR Admin', 'Leave Admin']);
        });

        // Staff attendance administration access
        Gate::define('staff-attendance-administration-access', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'HR Admin', 'Leave Admin']);
        });

        // ==================== LIBRARY GATES ====================

        // Base access to library module (sidebar, dashboard)
        Gate::define('access-library', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'Librarian',
            ]);
        });

        // Full library management (checkout, return, catalog management)
        Gate::define('manage-library', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'Librarian',
            ]);
        });

        // Manage library settings (loan periods, fine rates, etc.)
        Gate::define('manage-library-settings', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'Librarian',
            ]);
        });

        // Waive fines (admin override - AUTH-04)
        Gate::define('waive-library-fines', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
            ]);
        });

        // Delete library records (admin only - AUTH-04)
        Gate::define('delete-library-records', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
            ]);
        });

        // View library audit logs
        Gate::define('view-library-audit', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'Librarian',
            ]);
        });

        // ==================== TIMETABLE GATES ====================

        // Base access to timetable module (sidebar, viewing)
        Gate::define('access-timetable', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'Academic Admin',
                'HOD',
                'Teacher',
            ]);
        });

        // Full timetable management (create, edit, generate, publish)
        Gate::define('manage-timetable', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'Academic Admin',
            ]);
        });

        Gate::define('view-system-admin', function ($user) {
            return strtolower($user->email) === 'obscure852@gmail.com' || strtolower($user->email) === 'support@heritagepro.co' || strtolower($user->email) === 'support@imagelife.co';
        });

        Gate::define('access-finals', function ($user) {
            return $user->email === 'techteam@imagelife.co';
        });

        Gate::define('access-leadership', function ($user) {
            return in_array($user->position, ['School Head', 'Deputy School Head', 'HOD']);
        });

        Gate::define('junior-school-access', function () {
            $school = SchoolSetup::first();
            return $school && in_array(SchoolSetup::normalizeType($school->type), ['Junior', 'PRE_F3', 'JUNIOR_SENIOR', 'K12'], true);
        });

        // ==================== WELFARE GATES ====================

        // Basic access to view welfare dashboard and records
        Gate::define('access-welfare', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'School Counsellor',
                'Welfare Admin',
                'Welfare View',
                'Nurse',
            ]);
        });

        // Full management access for welfare cases
        Gate::define('manage-welfare', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'School Counsellor',
                'Welfare Admin',
            ]);
        });

        // Counseling-specific access (Level 4 confidential)
        Gate::define('access-counseling', function ($user) {
            return $user->hasAnyRoles([
                'School Counsellor',
            ]);
        });

        // Safeguarding access (Level 4 confidential, highly sensitive)
        Gate::define('access-safeguarding', function ($user) {
            return $user->hasAnyRoles([
                'School Counsellor',
                'Welfare Admin',
            ]);
        });

        // Health incidents access
        Gate::define('access-health-incidents', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'School Counsellor',
                'Welfare Admin',
                'Welfare View',
                'Nurse',
            ]);
        });

        // Manage health incidents (create, update)
        Gate::define('manage-health-incidents', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'School Counsellor',
                'Welfare Admin',
                'Nurse',
            ]);
        });

        // Disciplinary records access
        Gate::define('access-disciplinary', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'School Counsellor',
                'Welfare Admin',
                'Welfare View',
                'HOD',
                'Class Teacher',
            ]);
        });

        // Manage disciplinary records
        Gate::define('manage-disciplinary', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'School Counsellor',
                'Welfare Admin',
            ]);
        });

        // Intervention plans access
        Gate::define('access-intervention-plans', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'School Counsellor',
                'Welfare Admin',
                'Welfare View',
                'HOD',
                'Class Teacher',
            ]);
        });

        // Manage intervention plans
        Gate::define('manage-intervention-plans', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'School Counsellor',
                'Welfare Admin',
            ]);
        });

        // Approve welfare cases (requires senior access)
        Gate::define('approve-welfare', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'School Counsellor',
            ]) || in_array($user->position, ['School Head', 'Deputy School Head']);
        });

        // View welfare audit logs
        Gate::define('view-welfare-audit', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'School Counsellor',
                'Welfare Admin',
            ]);
        });

        // Export welfare data
        Gate::define('export-welfare', function ($user) {
            return $user->hasAnyRoles([
                'Administrator',
                'School Counsellor',
            ]);
        });

        // Delete welfare records (restricted)
        Gate::define('delete-welfare', function ($user) {
            return $user->hasAnyRoles(['Administrator']);
        });

        // ==================== DOCUMENT MANAGEMENT GATES ====================

        // Base access to document management module (sidebar, dashboard)
        Gate::define('access-documents', function ($user) {
            return $user->status === 'Current';
        });

        // Upload new documents (PRM-02)
        Gate::define('create-documents', function ($user) {
            return $user->status === 'Current';
        });

        // Manage own documents — edit/delete (PRM-03, PRM-04)
        Gate::define('manage-own-documents', function ($user) {
            return $user->status === 'Current';
        });

        // Edit any document regardless of ownership (PRM-03: documents.edit_any)
        Gate::define('edit-any-documents', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Documents Admin']);
        });

        // Delete any document regardless of ownership (PRM-04: documents.delete_any)
        Gate::define('delete-any-documents', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Documents Admin']);
        });

        // Share documents with others (PRM-05 — seeded now, used in Phase 6)
        Gate::define('share-documents', function ($user) {
            return $user->status === 'Current';
        });

        // Review and approve documents (PRM-06 — used in Phase 8)
        Gate::define('approve-documents', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Documents Admin', 'HOD']);
        });

        // Publish approved documents (PRM-07 — used in Phase 8)
        Gate::define('publish-documents', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Documents Admin', 'HOD']);
        });

        // Manage categories and tags (PRM-08 — used in Phase 4)
        Gate::define('manage-document-categories', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Documents Admin']);
        });

        // Manage institutional folders (PRM-09 — used in Phase 3)
        Gate::define('manage-institutional-folders', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Documents Admin']);
        });

        // View audit logs (PRM-10 — used in Phase 9)
        Gate::define('view-document-audit', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Documents Admin']);
        });

        // Configure module settings (PRM-11 — used in Phase 10)
        Gate::define('manage-document-settings', function ($user) {
            return $user->hasAnyRoles(['Administrator']);
        });

        // Manage user storage quotas (PRM-12 — used in Phase 9)
        Gate::define('manage-document-quotas', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Documents Admin']);
        });

        // ==================== SCHEMES OF WORK GATES ====================

        // Base access to Schemes of Work module (sidebar, index)
        Gate::define('access-schemes', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Academic Admin', 'HOD', 'Teacher', 'Scheme Admin', 'Scheme View']);
        });

        // Syllabus workspace access (list, create, and read-only edit screen)
        Gate::define('manage-syllabi', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Academic Admin', 'HOD', 'Teacher']);
        });

        // Syllabus mutations (update/delete/cache sync/topic CRUD) are restricted to HOD and administrators
        Gate::define('edit-syllabi', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'HOD']);
        });

        // Review and approve schemes (HOD and above only)
        Gate::define('review-schemes', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Academic Admin', 'HOD']);
        });

        // Admin-level scheme access (Administrator and Academic Admin only)
        Gate::define('admin-schemes', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Academic Admin']);
        });

        // Manage standard schemes (create, edit, approve, publish, distribute)
        Gate::define('manage-standard-schemes', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Academic Admin', 'HOD', 'Scheme Admin']);
        });

        // View standard schemes (admin-level only — teachers receive distributed copies, not direct access)
        Gate::define('view-standard-schemes', function ($user) {
            return $user->hasAnyRoles(['Administrator', 'Academic Admin', 'HOD', 'Scheme Admin', 'Scheme View']);
        });
    }
}
