<style>
    body {
        font-family: 'Roboto', sans-serif;
        z-index: 100;
    }

    #sidebar-menu .bx {
        font-size: 1.25rem;
        vertical-align: middle;
        transition: transform 0.2s;
    }

    #sidebar-menu li:hover .bx {
        transform: translateY(-2px);
        color: #4a6cf7;
    }
</style>
@php
    $moduleVisibility = app(\App\Services\ModuleVisibilityService::class);
    $messagingChannels = $communicationChannels ?? ['sms_enabled' => false, 'whatsapp_enabled' => false];
    $staffMessaging = $staffMessagingFeatures ?? ['direct_messages_enabled' => false];
@endphp
<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li>
                    <a href="{{ route('dashboard') }}">
                        <i class="bx bxs-grid-alt"></i>
                        <span data-key="t-dashboard">Dashboard</span>
                    </a>
                </li>

                @auth
                    @if ($staffMessaging['direct_messages_enabled'] ?? false)
                        <li>
                            <a href="{{ route('staff.messages.inbox') }}">
                                <i class="bx bx-chat"></i>
                                <span class="d-inline-flex align-items-center gap-2">
                                    <span>Direct Messages</span>
                                    <span class="badge rounded-pill bg-danger" id="staff-messages-sidebar-badge"
                                        style="display: none;"></span>
                                </span>
                            </a>
                        </li>
                    @endif
                @endauth

                {{-- My Leave Section - Available to all authenticated staff --}}
                @if ($moduleVisibility->isModuleVisible('leave'))
                    @auth
                        @php
                            $pendingApprovalsCount = 0;
                            if (Gate::allows('approve-leave-requests')) {
                                $pendingApprovalsCount = \App\Models\Leave\LeaveRequest::where('status', 'pending')
                                    ->whereHas('user', function ($q) {
                                        $q->where('reporting_to', auth()->id());
                                    })
                                    ->count();
                            }
                        @endphp
                        <li>
                            <a href="javascript: void(0);" class="has-arrow">
                                <i class="bx bx-calendar-event"></i>
                                <span data-key="t-my-leave">My Leave
                                    @if ($pendingApprovalsCount > 0)
                                        <span
                                            class="badge rounded-pill bg-danger float-end">{{ $pendingApprovalsCount }}</span>
                                    @endif
                                </span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li>
                                    <a href="{{ route('leave.requests.index') }}">
                                        <span>My Requests</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('leave.calendar.personal') }}">
                                        <span>My Calendar</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('leave.policies.view') }}">
                                        <span>Leave Policies</span>
                                    </a>
                                </li>
                                @can('approve-leave-requests')
                                    <li class="menu-title"
                                        style="padding: 8px 20px; font-size: 11px; color: #74788d; text-transform: uppercase; font-weight: 600;">
                                        Manager
                                    </li>
                                    <li>
                                        <a href="{{ route('leave.requests.pending') }}">
                                            <span>Pending Approvals
                                                @if ($pendingApprovalsCount > 0)
                                                    <span
                                                        class="badge rounded-pill bg-danger">{{ $pendingApprovalsCount }}</span>
                                                @endif
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('leave.calendar.team') }}">
                                            <span>Team Calendar</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('leave.requests.team-history') }}">
                                            <span>Team History</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endauth
                @endif

                @if ($moduleVisibility->isModuleVisible('staff_pdp'))
                    @auth
                        <li>
                            <a href="{{ route('staff.pdp.my') }}">
                                <i class="bx bx-clipboard"></i>
                                <span class="d-inline-flex align-items-center gap-2">
                                    <span>My PDP</span>
                                    <span class="badge rounded-pill badge-soft-danger" key="t-new">New</span>
                                </span>
                            </a>
                        </li>

                        @if (app(\App\Services\Pdp\PdpAccessService::class)->hasElevatedAccess(auth()->user()))
                            <li>
                                <a href="javascript: void(0);" class="has-arrow">
                                    <i class="bx bx-task"></i>
                                    <span class="d-inline-flex align-items-center gap-2">
                                        <span>Staff PDP</span>
                                        <span class="badge rounded-pill badge-soft-danger" key="t-new">New</span>
                                    </span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li>
                                        <a href="{{ route('staff.pdp.plans.index') }}">
                                            <span>Plans</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('staff.pdp.reports.index') }}">
                                            <span>Reports</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('staff.pdp.settings.index') }}">
                                            <span>Settings</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    @endauth
                @endif

                {{-- My Team - Staff Attendance Dashboard --}}
                @if ($moduleVisibility->isModuleVisible('staff_attendance'))
                    @auth
                        @if (auth()->user()->subordinates()->exists() || auth()->user()->position === 'HOD')
                            <li>
                                <a href="{{ route('staff-attendance.manager.dashboard') }}">
                                    <i class="bx bx-group"></i>
                                    <span data-key="t-my-team">My Team</span>
                                </a>
                            </li>
                        @endif
                    @endauth
                @endif

                <!-- Admissions Module -->
                @can('access-admissions')
                    <li>
                        @php
                            $newOnlineAdmissions = App\Models\Admission::where('status', 'New Online')->get();
                        @endphp
                        <a href="javascript: void(0);" class="has-arrow">
                            <i class="bx bxs-user-check"></i>
                            <span data-key="t-apps">Admissions
                                @if ($newOnlineAdmissions->count() > 0)
                                    <span style="background-color: red;"
                                        class="badge badge-pill badge-warning">{{ $newOnlineAdmissions->count() ?? '' }}</span>
                                @endif
                            </span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li>
                                <a href="{{ route('admissions.index') }}">
                                    <span>Admissions Records</span>
                                </a>
                            </li>
                            @can('manage-admissions')
                                <li>
                                    <a href="{{ route('admissions.admissions-new') }}">
                                        <span data-key="t-chat">New Admission</span>
                                    </a>
                                </li>
                                @if (\App\Models\SchoolSetup::isSeniorSchool())
                                    <li>
                                        <a href="{{ route('admissions.placement') }}">
                                            <span>Placements</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admissions.settings') }}">
                                            <span>Settings</span>
                                        </a>
                                    </li>
                                @endif
                            @endcan
                        </ul>
                    </li>
                @endcan
                <!-- Students Module -->
                @can('access-students')
                    <li>
                        <a href="javascript: void(0);" class="has-arrow">
                            <i class="bx bxs-school"></i>
                            <span data-key="t-apps">Students</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li>
                                <a href="{{ route('students.index') }}">
                                    <span data-key="t-calendar">Students Records</span>
                                </a>
                            </li>
                            @can('manage-students')
                                <li>
                                    <a href="{{ route('students.create') }}">
                                        <span data-key="t-chat">New Student</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('students.curriculum-materials') }}">
                                        <span data-key="t-chat">Books</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('students.students-settings') }}">
                                        <span data-key="t-chat">Settings</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan
                <!-- Student Welfare Module -->
                @if ($moduleVisibility->isModuleVisible('welfare'))
                    @can('access-welfare')
                        <li>
                            <a href="javascript: void(0);" class="has-arrow">
                                <i class="fas fa-hospital-user"></i>
                                <span data-key="t-welfare">Student Welfare</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li>
                                    <a href="{{ route('welfare.dashboard') }}">
                                        <span>Dashboard</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('welfare.cases.index') }}">
                                        <span>Welfare Cases</span>
                                    </a>
                                </li>
                                @can('access-safeguarding')
                                    <li>
                                        <a href="{{ route('welfare.safeguarding.index') }}">
                                            <span>Safeguarding Concerns</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('access-counseling')
                                    <li>
                                        <a href="{{ route('welfare.counseling.index') }}">
                                            <span>Schedule Counseling</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('access-disciplinary')
                                    <li>
                                        <a href="{{ route('welfare.disciplinary.index') }}">
                                            <span>Disciplinary Records</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('access-health-incidents')
                                    <li>
                                        <a href="{{ route('welfare.health.index') }}">
                                            <span>Health Incidents</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('access-intervention-plans')
                                    <li>
                                        <a href="{{ route('welfare.intervention-plans.index') }}">
                                            <span>Intervention Plans</span>
                                        </a>
                                    </li>
                                @endcan
                                <li>
                                    <a href="{{ route('welfare.communications.index') }}">
                                        <span>Parent Communications</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan
                @endif
                <!-- Sponsors Module -->
                @can('access-sponsors')
                    <li>
                        <a href="javascript: void(0);" class="has-arrow">
                            <i class="bx bxs-user-badge"></i>
                            <span data-key="t-apps">Sponsors</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li>
                                <a href="{{ route('sponsors.index') }}" data-key="t-user-grid">Sponsors Records</a>
                            </li>
                            @can('manage-sponsors')
                                <li>
                                    <a href="{{ route('sponsors.sponsor-new') }}" data-key="t-user-list">New Sponsor</a>
                                </li>
                                <li>
                                    <a href="{{ route('sponsors.sponsors-settings') }}" data-key="t-user-grid">Settings</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan
                <!-- Human Resources -->
                @can('access-hr')
                    <li>
                        <a href="javascript: void(0);" class="has-arrow">
                            <i class="bx bxs-user-detail"></i>
                            <span data-key="t-apps">Human Resources</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li>
                                <a href="{{ route('staff.index') }}">
                                    <span data-key="t-calendar">Staff Records</span>
                                </a>
                            </li>
                            @can('manage-hr')
                                <li>
                                    <a href="{{ route('staff.roles.allocations') }}">

                                        <span data-key="t-chat">Role Allocations</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('staff.staff-settings') }}">
                                        <span data-key="t-chat">Settings</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan
                <!-- Attendance Module -->
                @can('access-attendance')
                    <li>
                        <a href="javascript: void(0);" class="has-arrow">
                            <i class="bx bxs-time-five"></i>
                            <span data-key="t-invoices">Attendance</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li>
                                <a href="{{ route('attendance.index') }}" data-key="t-invoice-list">Attendance
                                    Register</a>
                            </li>
                            @can('manage-attendance')
                                <li>
                                    <a href="{{ route('attendance.settings') }}"
                                        data-key="t-attendance-settings">Settings</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                {{-- Staff Attendance Administration --}}
                @if ($moduleVisibility->isModuleVisible('staff_attendance'))
                    @can('staff-attendance-administration-access')
                        <li>
                            <a href="javascript: void(0);" class="has-arrow">
                                <i class="bx bx-fingerprint"></i>
                                <span data-key="t-staff-attendance">Staff Attendance</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li>
                                    <a href="{{ route('staff-attendance.manual-register.index') }}">
                                        <span>Manual Register</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('staff-attendance.mapping.index') }}">
                                        <span>Staff Mapping</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('staff-attendance.settings.index') }}">
                                        <span>Settings</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan
                @endif
                <!-- Academic Management Module -->
                @can('access-academic')
                    <li>
                        <a href="javascript: void(0);" class="has-arrow">
                            <i class="bx bxs-cube-alt"></i>
                            <span>Academics Manager</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            @can('access-class-allocations')
                                <li>
                                    <a href="{{ route('academic.index') }}">
                                        <span data-key="t-calendar">Class Allocations</span>
                                    </a>
                                </li>
                            @endcan
                            @if ($schoolType->type !== 'Primary')
                                @can('access-optional')
                                    <li>
                                        <a href="{{ route('optional.index') }}">
                                            <span data-key="t-chat">Optional Classes</span>
                                        </a>
                                    </li>
                                @endcan
                            @endif
                            @can('manage-academic')
                                <li>
                                    <a href="{{ route('academic.show-new') }}">
                                        <span data-key="t-chat">Core Allocations</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('subjects.index') }}">
                                        <span data-key="t-chat">Subject Allocations</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('subjects.master-list') }}">
                                        <span data-key="t-chat">Master Subject List</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('assessment.comment-bank') }}">
                                        <span data-key="t-chat">Venues & Comments</span>
                                    </a>
                                </li>
                                @if ($schoolType->type !== 'Senior')
                                    <li>
                                        <a href="{{ route('academic.configurations') }}">
                                            <span data-key="t-chat">Configuration</span>
                                        </a>
                                    </li>
                                @endif
                            @endcan
                        </ul>
                    </li>
                @endcan
                <!-- Assessment Module -->
                @php
                    $hasAssessmentAccess = auth()->check() && auth()->user()->can('access-assessment');
                    $accessibleMarkbookContexts = auth()->check()
                        ? $schoolModeResolver->accessibleMarkbookContexts(auth()->user(), null, $resolvedSchoolMode)
                        : [];
                    $hasMarkbookAccess = !empty($accessibleMarkbookContexts);
                @endphp
                @if ($hasAssessmentAccess || $hasMarkbookAccess)
                    @if ($schoolModeResolver->usesSplitAssessmentSidebar($resolvedSchoolMode))
                        @if ($hasMarkbookAccess)
                            <li>
                                <a href="javascript: void(0);" class="has-arrow">
                                    <i class="bx bx-book-open"></i>
                                    <span data-key="t-invoice-detail">Markbook</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    @foreach ($accessibleMarkbookContexts as $context)
                                        <li>
                                            <a href="{{ route($schoolModeResolver->markbookRouteName($context, $resolvedSchoolMode)) }}"
                                                data-key="t-invoice-detail">
                                                {{ $schoolModeResolver->assessmentContextSidebarLabel($context) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif

                        @if ($hasAssessmentAccess)
                            <li>
                                <a href="javascript: void(0);" class="has-arrow">
                                    <i class="bx bx-table"></i>
                                    <span data-key="t-invoice-list">Gradebook</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    @foreach ($schoolModeResolver->availableAssessmentContexts($resolvedSchoolMode) as $context)
                                        <li>
                                            <a href="{{ route($schoolModeResolver->gradebookRouteName($context, $resolvedSchoolMode)) }}"
                                                data-key="t-invoice-list">
                                                {{ $schoolModeResolver->assessmentContextSidebarLabel($context) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif

                        @can('view-system-admin')
                            <li>
                                <a href="{{ route('assessment.test-list') }}">
                                    <i class="bx bx-task"></i>
                                    <span data-key="t-invoice-detail">Assessments</span>
                                </a>
                            </li>
                        @endcan
                    @else
                        <li>
                            <a href="javascript: void(0);" class="has-arrow">
                                <i class="bx bxs-chart"></i>
                                <span data-key="t-invoices">Assessment</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                @if ($hasMarkbookAccess)
                                    <li>
                                        <a href="{{ route($schoolModeResolver->markbookRouteName($accessibleMarkbookContexts[0] ?? null, $resolvedSchoolMode)) }}"
                                            data-key="t-invoice-detail">Markbook</a>
                                    </li>
                                @endif
                                @if ($hasAssessmentAccess)
                                    <li>
                                        <a href="{{ route($schoolModeResolver->gradebookRouteName()) }}"
                                            data-key="t-invoice-list">Gradebook</a>
                                    </li>
                                @endif
                                @can('view-system-admin')
                                    <li>
                                        <a href="{{ route('assessment.test-list') }}"
                                            data-key="t-invoice-detail">Assessments</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif
                @endif

                {{-- Schemes of Work Module --}}
                @if ($moduleVisibility->isModuleVisible('schemes'))
                    @can('access-schemes')
                        <li>
                            <a href="javascript: void(0);" class="has-arrow">
                                <i class="menu-icon bx bx-book-content"></i>
                                <span class="d-inline-flex align-items-center gap-2">
                                    <span data-key="t-schemes">Scheme of Work</span>
                                </span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li>
                                    <a href="{{ route('schemes.index') }}">
                                        <span>Schemes</span>
                                    </a>
                                </li>
                                @can('view-standard-schemes')
                                    <li>
                                        <a href="{{ route('standard-schemes.index') }}">
                                            <span>Standard Schemes</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('admin-schemes')
                                    <li>
                                        <a href="{{ route('schemes.admin.dashboard') }}">
                                            <span>Dashboard</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan
                @endif

                @if ($moduleVisibility->isModuleVisible('invigilation'))
                    @if (auth()->user()->can('access-invigilation'))
                        <li>
                            <a href="javascript: void(0);" class="has-arrow">
                                <i class="bx bx-clipboard"></i>
                                <span data-key="t-invigilation">Invigilation Roster</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li>
                                    <a href="{{ route('invigilation.index') }}">
                                        <span>Series Manager</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('invigilation.reports.daily.index') }}">
                                        <span>Daily Roster</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('invigilation.reports.teacher.index') }}">
                                        <span>Teacher Duties</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('invigilation.reports.room.index') }}">
                                        <span>Room Roster</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('invigilation.reports.conflicts.index') }}">
                                        <span>Conflict Report</span>
                                    </a>
                                </li>
                                @can('manage-invigilation')
                                    <li>
                                        <a href="{{ route('invigilation.settings.index') }}">
                                            <span>Settings</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @elseif (auth()->user()->can('access-invigilation-published-roster'))
                        <li>
                            <a href="{{ route('invigilation.view.teacher-roster') }}"
                                class="{{ request()->routeIs('invigilation.view.teacher-roster') ? 'active' : '' }}">
                                <i class="bx bx-clipboard"></i>
                                <span data-key="t-invigilation">Invigilation Roster</span>
                            </a>
                        </li>
                    @endif
                @endif

                {{-- Houses Module --}}
                @can('houses-access')
                    <li>
                        <a href="javascript: void(0);" class="has-arrow">
                            <i class="bx bxs-flag-alt"></i>
                            <span data-key="t-invoices">Houses</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{ route('house.index') }}" data-key="t-invoice-list">Houses Records</a></li>
                            @can('manage-houses')
                                <li><a href="{{ route('house.show') }}" data-key="t-invoice-list">New House</a></li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                @if ($moduleVisibility->isModuleVisible('activities'))
                    @can('access-activities')
                        <li>
                            <a href="javascript: void(0);" class="has-arrow">
                                <i class="bx bx-run"></i>
                                <span data-key="t-activities">Activities</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li>
                                    <a href="{{ route('activities.index') }}">
                                        <span>Activities Records</span>
                                    </a>
                                </li>
                                @can('manage-activities')
                                    <li>
                                        <a href="{{ route('activities.create') }}">
                                            <span>New Activity</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-activity-settings')
                                    <li>
                                        <a href="{{ route('activities.settings.index') }}">
                                            <span>Settings</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan
                @endif

                <!-- Finals Module -->
                @if (app(\App\Services\SchoolModeResolver::class)->supportsFinals(null,
                        \App\Models\SchoolSetup::normalizeType(optional($schoolType)->type)))
                    <!-- BEC exams import and analysis -->
                    <li>
                        <a href="javascript: void(0);" class="has-arrow">
                            <i class="bx bxs-pie-chart-alt-2"></i>
                            <span data-key="t-invoices">Finals</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li>
                                <a href="{{ route('finals.students.index') }}"
                                    data-key="t-invoice-detail">Students</a>
                            </li>
                            <li>
                                <a href="{{ route('finals.classes.index') }}" data-key="t-invoice-list">Classes</a>
                            </li>

                            <li>
                                <a href="{{ route('finals.core.index') }}" data-key="t-invoice-list">Core
                                    Subjects</a>
                            </li>

                            <li>
                                <a href="{{ route('finals.optionals.index') }}" data-key="t-invoice-list">Optional
                                    Subjects</a>
                            </li>
                            <li>
                                <a href="{{ route('finals.subjects.index') }}" data-key="t-invoice-list">Subjects</a>
                            </li>
                            <li>
                                <a href="{{ route('finals.houses.index') }}" data-key="t-invoice-list">Houses</a>
                            </li>
                            <li>
                                <a href="{{ route('finals.import.external-results-import') }}"
                                    data-key="t-invoice-detail">Import Results</a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Communications Module -->
                @if ($moduleVisibility->isModuleVisible('communications'))
                    @can('access-communications')
                        <li>
                            <a href="javascript: void(0);" class="has-arrow">
                                <i class="bx bxs-chat"></i>
                                <span data-key="t-invoices">Communication</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li>
                                    <a href="{{ route('notifications.index') }}"
                                        data-key="t-invoice-list">Notifications</a>
                                </li>
                                @can('sms-communications')
                                    @if (($messagingChannels['sms_enabled'] ?? false) || ($messagingChannels['whatsapp_enabled'] ?? false))
                                        <li>
                                            <a href="{{ route('notifications.bulk-sms-index') }}"
                                                data-key="t-invoice-detail">Messaging</a>
                                        </li>
                                    @endif
                                @endcan
                                @can('email-communications')
                                    <li>
                                        <a href="{{ route('notifications.bulk-mail-index') }}"
                                            data-key="t-invoice-detail">Emailing</a>
                                    </li>
                                @endcan

                                @can('access-setup')
                                    @if ($messagingChannels['whatsapp_enabled'] ?? false)
                                        <li>
                                            <a href="{{ route('whatsapp-templates.index') }}"
                                                data-key="t-whatsapp-templates">WhatsApp Templates</a>
                                        </li>
                                    @endif
                                    <li>
                                        <a href="{{ route('setup.communications-setup') }}">
                                            <span data-key="t-chat">Settings</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan
                @endif

                {{-- Learning Management System Module --}}
                @if ($moduleVisibility->isModuleVisible('lms'))
                    @can('access-lms')
                        <li>
                            <a href="javascript: void(0);" class="has-arrow">
                                <i class="fas fa-graduation-cap"></i>
                                <span data-key="t-lms">Learning Space</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li>
                                    <a href="{{ route('lms.courses.index') }}">
                                        <span>Learning Content</span>
                                    </a>
                                </li>
                                @can('manage-lms-content')
                                    <li>
                                        <a href="{{ route('lms.library.index') }}">
                                            <span>Content Library</span>
                                        </a>
                                    </li>
                                @endcan
                                <li>
                                    <a href="{{ route('lms.calendar.index') }}">
                                        <span>Calendar</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('lms.messaging.inbox') }}">
                                        <span>Messages</span>
                                    </a>
                                </li>
                                @can('manage-lms-content')
                                    <li>
                                        <a href="{{ route('lms.learning-paths.index') }}">
                                            <span>Learning Paths</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-lms-courses')
                                    <li>
                                        <a href="{{ route('lms.analytics.reports') }}">
                                            <span>Reports & Analytics</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-lms-courses')
                                    <li>
                                        <a href="{{ route('lms.settings.index') }}">
                                            <span>Settings</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan
                @endif

                {{-- Asset Management Module --}}

                @if ($moduleVisibility->isModuleVisible('assets'))
                    @can('access-asset-management')
                        <li>
                            <a href="javascript: void(0);">
                                <i class="bx bxs-package"></i>
                                <span class="badge rounded-pill badge-soft-danger float-end" key="t-new">New</span>
                                <span data-key="t-invoices">Assets </span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('assets.index') }}" data-key="t-invoice-list">Assets Records</a>
                                </li>
                                @can('manage-assets')
                                    <li><a href="{{ route('assets.create') }}" data-key="t-invoice-list">New Asset</a></li>
                                    <li><a href="{{ route('assets.settings') }}" data-key="t-invoice-list">Settings</a></li>
                                @endcan
                            </ul>
                        </li>
                    @endcan
                @endif

                @if ($moduleVisibility->isModuleVisible('contacts'))
                    @can('access-asset-management')
                        <li>
                            <a href="javascript: void(0);" class="has-arrow">
                                <i class="bx bx-briefcase-alt-2"></i>
                                <span data-key="t-contacts">Contacts</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('contacts.index') }}">Business Contacts</a></li>
                                @can('manage-assets')
                                    <li><a href="{{ route('contacts.create') }}">New Contact</a></li>
                                    <li><a href="{{ route('contacts.settings') }}">Settings</a></li>
                                @endcan
                            </ul>
                        </li>
                    @endcan
                @endif

                {{-- Document Management Module --}}
                @can('access-documents')
                    <li>
                        <a href="javascript: void(0);" class="has-arrow">
                            <i class="bx bx-folder"></i>
                            <span data-key="t-documents">Documents Manager</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li>
                                <a href="{{ route('documents.dashboard') }}">
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('documents.index') }}">
                                    <span>My Documents</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('documents.shared') }}">
                                    <span>Shared with Me</span>
                                </a>
                            </li>
                            @if (Gate::any([
                                    'manage-document-categories',
                                    'view-document-audit',
                                    'manage-document-quotas',
                                    'manage-document-settings',
                                ]))
                                <li>
                                    <a href="{{ route('documents.settings') }}">
                                        <span>Settings</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endcan

                {{-- Fee Administration Module --}}
                @if ($moduleVisibility->isModuleVisible('fees'))
                    @can('fee-administration-access')
                        <li>
                            <a href="javascript: void(0);" class="has-arrow">
                                <i class="bx bxs-wallet"></i>
                                <span data-key="t-fees">Fees Administration</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                @can('view-fee-reports')
                                    <li>
                                        <a href="{{ route('fees.reports.dashboard') }}">
                                            <span>Fee Dashboard</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('collect-fees')
                                    <li>
                                        <a href="{{ route('fees.balance.outstanding') }}">
                                            <span>Outstanding Balances</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('fees.collection.invoices.index') }}">
                                            <span>Invoices</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('fees.payment-plans.index') }}">
                                            <span>Payment Plans</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-fee-setup')
                                    <li>
                                        <a href="{{ route('fees.discounts.index') }}">
                                            <span>Student Discounts</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('fees.setup.index') }}">
                                            <span>Fee Settings</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan
                @endif

                {{-- Library Module --}}
                @if ($moduleVisibility->isModuleVisible('library'))
                    @can('access-library')
                        <li>
                            <a href="javascript: void(0);" class="has-arrow">
                                <i class="bx bx-library"></i>
                                <span data-key="t-library">Library</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                @can('manage-library')
                                    @if (Route::has('library.dashboard'))
                                        <li>
                                            <a href="{{ route('library.dashboard') }}"
                                                class="{{ request()->routeIs('library.dashboard') ? 'active' : '' }}">
                                                <span>Dashboard</span>
                                            </a>
                                        </li>
                                    @endif
                                @endcan
                                @can('manage-library')
                                    @if (Route::has('library.reservations.index'))
                                        <li>
                                            <a href="{{ route('library.reservations.index') }}">
                                                <span>Reservations</span>
                                            </a>
                                        </li>
                                    @endif
                                @endcan
                                @can('manage-library')
                                    @if (Route::has('library.inventory.index'))
                                        <li>
                                            <a href="{{ route('library.inventory.index') }}">
                                                <span>Inventory</span>
                                            </a>
                                        </li>
                                    @endif
                                @endcan
                                @can('manage-library-settings')
                                    <li>
                                        <a href="{{ route('library.settings.index') }}">
                                            <span>Settings</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan
                @endif

                {{-- Timetable Module --}}
                @if ($moduleVisibility->isModuleVisible('timetable'))
                    @can('access-timetable')
                        <li>
                            <a href="javascript: void(0);" class="has-arrow">
                                <i class="bx bx-calendar-alt"></i>
                                <span data-key="t-timetable">Timetable</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                {{-- View links (visible to all access-timetable users) --}}
                                <li>
                                    <a href="{{ route('timetable.view.class') }}"
                                        class="{{ request()->routeIs('timetable.view.class') ? 'active' : '' }}">
                                        <span>Class Timetable</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('timetable.view.teacher') }}"
                                        class="{{ request()->routeIs('timetable.view.teacher') ? 'active' : '' }}">
                                        <span>Teacher Timetable</span>
                                    </a>
                                </li>
                                @can('manage-timetable')
                                    <li>
                                        <a href="{{ route('timetable.view.master') }}"
                                            class="{{ request()->routeIs('timetable.view.master') ? 'active' : '' }}">
                                            <span>Master Overview</span>
                                        </a>
                                    </li>
                                    @if (Route::has('timetable.index'))
                                        <li>
                                            <a href="{{ route('timetable.index') }}"
                                                class="{{ request()->routeIs('timetable.index') || request()->routeIs('timetable.show') ? 'active' : '' }}">
                                                <span>Manage Timetables</span>
                                            </a>
                                        </li>
                                    @endif
                                    <li>
                                        <a href="{{ route('timetable.period-settings.index') }}"
                                            class="{{ request()->routeIs('timetable.period-settings.*') ? 'active' : '' }}">
                                            <span>Period Settings</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('timetable.generation.settings') }}"
                                            class="{{ request()->routeIs('timetable.generation.settings') ? 'active' : '' }}">
                                            <span>Advanced Settings</span>
                                        </a>
                                    </li>
                                    @php
                                        $firstDraftTimetable = \App\Models\Timetable\Timetable::draft()->first();
                                    @endphp
                                    @if ($firstDraftTimetable)
                                        <li>
                                            <a href="{{ route('timetable.constraints.index', $firstDraftTimetable) }}"
                                                class="{{ request()->routeIs('timetable.constraints.*') ? 'active' : '' }}">
                                                <span>Constraints</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('timetable.generation.index', $firstDraftTimetable) }}"
                                                class="{{ request()->routeIs('timetable.generation.*') ? 'active' : '' }}">
                                                <span>Generate Timetable</span>
                                            </a>
                                        </li>
                                    @endif
                                @endcan
                            </ul>
                        </li>
                    @endcan
                @endif

                {{-- Leave Management Module --}}
                @if ($moduleVisibility->isModuleVisible('leave'))
                    @can('leave-administration-access')
                        <li>
                            <a href="javascript: void(0);" class="has-arrow">
                                <i class="bx bx-calendar-check"></i>
                                <span data-key="t-leave">Leave Manager</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                @can('manage-leave-balances')
                                    <li>
                                        <a href="{{ route('leave.balances.index') }}">
                                            <span>Leave Balances</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-leave-holidays')
                                    <li>
                                        <a href="{{ route('leave.holidays.index') }}">
                                            <span>Public Holidays</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-leave-settings')
                                    <li>
                                        <a href="{{ route('leave.settings.index') }}">
                                            <span>Leave Settings</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan
                @endif
                <!-- Settings Module -->
                @can('access-setup')
                    <li>
                        <a href="javascript: void(0);" class="has-arrow">
                            <i class="bx bx-slider-alt"></i>
                            <span data-key="t-apps">Settings</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li>
                                <a href="{{ route('setup.school-setup') }}">
                                    <span data-key="t-calendar">School Setup</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('setup.module-settings') }}">
                                    <span data-key="t-chat">Module Settings</span>
                                </a>
                            </li>
                            @can('canImportData')
                                <li>
                                    <a href="{{ route('setup.data-importing') }}">
                                        <span data-key="t-chat">Data Importing</span>
                                    </a>
                                </li>
                            @endcan
                            <li>
                                <a href="{{ route('logs.index') }}">
                                    <span data-key="t-chat">System Logs</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('setup.grades-setup') }}">
                                    <span data-key="t-chat">Academic Structure</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->
