<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
                <li>
                    <a href="{{ route('student.dashboard') }}">
                        <i data-feather="home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Learning Management -->
                <li class="menu-title">Learning</li>

                <li>
                    <a href="{{ route('student.lms.my-courses') }}">
                        <i data-feather="book-open"></i>
                        <span>My Courses</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('student.lms.courses') }}">
                        <i data-feather="search"></i>
                        <span>Browse Courses</span>
                    </a>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="map"></i>
                        <span>Learning Paths</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('student.lms.my-learning-paths') }}">My Learning Paths</a></li>
                        <li><a href="{{ route('student.lms.learning-paths') }}">Browse Paths</a></li>
                    </ul>
                </li>

                <li>
                    <a href="{{ route('student.lms.calendar.index') }}">
                        <i data-feather="calendar"></i>
                        <span>My Calendar</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('student.lms.messages.inbox') }}">
                        <i data-feather="mail"></i>
                        <span>Messages</span>
                        <span class="badge rounded-pill bg-danger ms-auto" id="unread-messages-badge" style="display: none;"></span>
                    </a>
                </li>

                <!-- Academic Performance -->
                <li class="menu-title">Academic</li>

                <li>
                    <a href="{{ route('student.academic.index') }}">
                        <i data-feather="bar-chart-2"></i>
                        <span>My Performance</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('student.academic.report-cards') }}">
                        <i data-feather="file-text"></i>
                        <span>Report Cards</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('student.academic.books') }}">
                        <i data-feather="book"></i>
                        <span>My Books</span>
                    </a>
                </li>

                <!-- Help & Support -->
                <li class="menu-title">Support</li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="help-circle"></i>
                        <span>Help</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Support Tickets</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
