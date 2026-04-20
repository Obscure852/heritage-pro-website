<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="{{ request()->routeIs('sponsor.dashboard*') ? 'mm-active' : '' }}">
                    <a href="{{ route('sponsor.dashboard') }}">
                        <i data-feather="home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('sponsor.student.show') || request()->routeIs('sponsor.students.index') ? 'mm-active' : '' }}">
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="users"></i>
                        <span>Students</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        @forelse($sponsorStudents ?? [] as $child)
                            @php
                                $isStudentActive = request()->routeIs('sponsor.student.show') && request()->route('student') == $child->id;
                            @endphp
                            <li class="{{ $isStudentActive ? 'mm-active' : '' }}">
                                <a href="{{ route('sponsor.student.show', $child->id) }}">
                                    {{ $child->first_name }} {{ $child->last_name }}
                                </a>
                            </li>
                        @empty
                            <li>
                                <a href="#" class="text-muted">
                                    No students linked
                                </a>
                            </li>
                        @endforelse
                    </ul>
                </li>
                <li class="{{ request()->routeIs('sponsor.assessment*') ? 'mm-active' : '' }}">
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="bar-chart-2"></i>
                        <span>Assessment</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        @forelse($sponsorStudents ?? [] as $child)
                            @php
                                $isAssessmentActive = request()->routeIs('sponsor.assessment.student*') && request()->route('student') == $child->id;
                            @endphp
                            <li class="{{ $isAssessmentActive ? 'mm-active' : '' }}">
                                <a href="{{ route('sponsor.assessment.student', $child->id) }}">
                                    {{ $child->first_name }} {{ $child->last_name }}
                                </a>
                            </li>
                        @empty
                            <li>
                                <a href="#" class="text-muted">
                                    No students linked
                                </a>
                            </li>
                        @endforelse
                    </ul>
                </li>
                <li class="{{ request()->routeIs('sponsor.fees*') ? 'mm-active' : '' }}">
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="dollar-sign"></i>
                        <span>Fees & Payments</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        @forelse($sponsorStudents ?? [] as $child)
                            @php
                                $isFeesActive = request()->routeIs('sponsor.fees.student*') && request()->route('student') == $child->id;
                            @endphp
                            <li class="{{ $isFeesActive ? 'mm-active' : '' }}">
                                <a href="{{ route('sponsor.fees.student', $child->id) }}">
                                    {{ $child->first_name }} {{ $child->last_name }}
                                </a>
                            </li>
                        @empty
                            <li>
                                <a href="#" class="text-muted">
                                    No students linked
                                </a>
                            </li>
                        @endforelse
                    </ul>
                </li>
                <li class="{{ request()->routeIs('sponsor.profile*') ? 'mm-active' : '' }}">
                    <a href="{{ route('sponsor.profile') }}">
                        <i data-feather="settings"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Sidebar Menu End -->
    </div>
</div>
