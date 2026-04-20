<style>
    .app-search .position-relative {
        width: 240px;
    }

    .app-search .form-control {
        height: 38px;
        padding: 6px 16px;
        font-size: 13px;
        border-radius: 30px;
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid #e9ebef;
    }

    .app-search .form-control:focus {
        background-color: #fff;
        border-color: #556ee6;
        box-shadow: 0 0 0 0.15rem rgba(85, 110, 230, 0.15);
    }

    .app-search .search-results {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(30, 32, 37, 0.12);
        z-index: 1050;
        max-height: calc(100vh - 150px);
        overflow-y: auto;
        border: 1px solid #e9ebef;
    }

    .app-search .search-section {
        border-bottom: 1px solid #e9ebef;
    }

    .app-search .search-section:last-child {
        border-bottom: none;
    }

    .app-search .section-header {
        padding: 10px 16px;
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 13px;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .app-search .section-header i {
        font-size: 16px;
        color: #74788d;
    }

    .app-search .result-item {
        padding: 10px 16px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .app-search .result-item:hover {
        background-color: #f3f3f9;
    }

    .app-search .result-name {
        font-weight: 500;
        color: #495057;
        font-size: 13px;
        margin-bottom: 3px;
    }

    .app-search .result-details {
        font-size: 12px;
        color: #74788d;
        line-height: 1.4;
    }

    .app-search .result-details span:not(:last-child)::after {
        content: "•";
        margin: 0 6px;
        color: #adb5bd;
    }

    .app-search .no-results,
    .app-search .loading-results {
        padding: 12px 16px;
        text-align: center;
        color: #74788d;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .app-search .loading-spinner {
        width: 18px;
        height: 18px;
        border: 2px solid #e9ebef;
        border-top-color: #556ee6;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    @media (min-width: 992px) {
        .app-search .position-relative {
            width: 280px;
        }
    }

    @media (min-width: 1200px) {
        .app-search .position-relative {
            width: 320px;
        }
    }
</style>
<header id="page-topbar">
    <div class="navbar-header bg-soft-light">
        <div class="d-flex">
            <div class="navbar-brand-box">
                <a href="{{ route('student.dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ asset('assets/images/heritage-pro-logo.jpg') }}" alt="" height="24">
                    </span>
                    <span class="logo-lg">
                        {{-- <img src="assets/images/logo-sm.svg" alt="" height="24">  --}}
                        <img src="{{ asset('assets/images/heritage-pro-logo.jpg') }}" alt="" height="24">
                        <span class="logo-txt">Heritage Pro</span>
                    </span>
                </a>

                <a href="{{ route('student.dashboard') }}" class="logo logo-light">
                    <span class="logo-sm">
                        {{-- <img src="assets/images/logo-sm.svg" alt="" height="24"> --}}
                        <img src="{{ asset('assets/images/heritage-pro-logo.jpg') }}" alt="" height="24">
                    </span>
                    <span class="logo-lg">
                        {{-- <img src="assets/images/logo-sm.svg" alt="" height="24">  --}}
                        <img src="{{ asset('assets/images/heritage-pro-logo.jpg') }}" alt="" height="24">
                        <span class="logo-txt">Heritage Pro</span>
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item" id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>

            {{-- <form class="app-search d-none d-lg-block" onsubmit="return false;">
                <div class="position-relative">
                    <input type="text" class="form-control form-control-sm" placeholder="Search ..."
                        id="global-search" autocomplete="off" aria-label="Global Search">
                    <div class="search-results" id="search-results" style="display: none;"></div>
                </div>
            </form> --}}
        </div>

        <div class="d-flex">
            {{-- <div class="dropdown d-inline-block d-lg-none ms-2">
                <button type="button" class="btn header-item btn-sm" id="page-header-search-dropdown"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="search" class="icon-lg"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                    aria-labelledby="page-header-search-dropdown">

                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search kkk..."
                                    aria-label="Search Result">
                                <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>



            <div class="dropdown d-none d-sm-inline-block">
                <button type="button" class="btn header-item" id="mode-setting-btn">
                    <i data-feather="moon" class="icon-lg layout-mode-dark"></i>
                    <i data-feather="sun" class="icon-lg layout-mode-light"></i>
                </button>
            </div>

                        --}}

            {{-- <div class="dropdown d-none d-lg-inline-block ms-1">
                <button type="button" class="btn header-item" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    <i data-feather="grid" class="icon-lg"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <div class="p-2">
                        <div class="row g-0">
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/github.png" alt="Github">
                                    <span>GitHub</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/bitbucket.png" alt="bitbucket">
                                    <span>Bitbucket</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/dribbble.png" alt="dribbble">
                                    <span>Dribbble</span>
                                </a>
                            </div>
                        </div>

                        <div class="row g-0">
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/dropbox.png" alt="dropbox">
                                    <span>Dropbox</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/mail_chimp.png" alt="mail_chimp">
                                    <span>Mail Chimp</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/slack.png" alt="slack">
                                    <span>Slack</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            {{-- <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon position-relative"
                    id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    <i data-feather="bell" class="icon-lg"></i>
                    <span class="badge bg-danger rounded-pill">5</span>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                    aria-labelledby="page-header-notifications-dropdown">
                    <div class="p-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-0"> Notifications </h6>
                            </div>
                            <div class="col-auto">
                                <a href="#!" class="small text-reset text-decoration-underline"> Unread (3)</a>
                            </div>
                        </div>
                    </div>
                    <div data-simplebar style="max-height: 230px;">
                        <a href="#!" class="text-reset notification-item">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">James Lemire</h6>
                                    <div class="font-size-13 text-muted">
                                        <p class="mb-1">It will seem like simplified English.</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span>1 hours
                                                ago</span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="#!" class="text-reset notification-item">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Your order is placed</h6>
                                    <div class="font-size-13 text-muted">
                                        <p class="mb-1">If several languages coalesce the grammar</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span>3 min
                                                ago</span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="#!" class="text-reset notification-item">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Your item is shipped</h6>
                                    <div class="font-size-13 text-muted">
                                        <p class="mb-1">If several languages coalesce the grammar</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span>2 min
                                                ago</span></p>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <a href="#!" class="text-reset notification-item">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Salena Layfield</h6>
                                    <div class="font-size-13 text-muted">
                                        <p class="mb-1">As a skeptical Cambridge friend of mine occidental.</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span>1 min
                                                ago</span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="p-2 border-top d-grid">
                        <a class="btn btn-sm btn-link font-size-14 text-center" href="javascript:void(0)">
                            <i class="mdi mdi-arrow-right-circle me-1"></i> <span>View More..</span>
                        </a>
                    </div>
                </div>
            </div> --}}
            {{-- 
            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item right-bar-toggle me-2">
                    <i data-feather="settings" class="icon-lg"></i>
                </button>
            </div> --}}


            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item bg-soft-light border-start border-end"
                    id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user"
                        src="{{ asset('assets/images/users/default-profile.png') }}" alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-1 fw-medium">{{ auth('student')->user()->full_name ?? '' }}

                    </span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i><br>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <a class="dropdown-item" href="{{ route('student.profile') }}"><i class="bx bxs-user font-size-16 align-middle me-1"></i>
                        Profile</a>

                    <a class="dropdown-item" href="{{ route('setup.video-tutorials') }}"><i
                            class="bx bxs-videos font-size-16 align-middle me-1"></i>
                        Tutorials</a>

                    <a class="dropdown-item text-danger" href="{{ route('student.logout') }}"
                        onclick="logoutAndClearStorage(event)"><i
                            class="mdi mdi-logout font-size-16 align-middle me-1"></i> <span
                            key="t-logout">Logout</span></a>

                </div>
            </div>
        </div>
    </div>
</header>
