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
    <div class="navbar-header">
        <div class="d-flex">
            <div class="navbar-brand-box">
                <a href="#" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ asset('assets/images/heritage-pro-logo.jpg') }}" alt="" height="24">
                    </span>
                    <span class="logo-lg">
                        {{-- <img src="assets/images/logo-sm.svg" alt="" height="24">  --}}
                        <img src="{{ asset('assets/images/heritage-pro-logo.jpg') }}" alt="" height="24">
                        <span class="logo-txt">Heritage Pro</span>
                    </span>
                </a>

                <a href="#" class="logo logo-light">
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

            @auth
            <div class="dropdown d-inline-block" id="doc-notification-dropdown">
                <button type="button" class="btn header-item noti-icon position-relative"
                    id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    <i data-feather="bell" class="icon-lg"></i>
                    <span class="badge bg-danger rounded-pill" id="doc-notification-count" style="display: none;">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                    aria-labelledby="page-header-notifications-dropdown">
                    <div class="p-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-0">Notifications</h6>
                            </div>
                            <div class="col-auto">
                                <a href="#" id="doc-mark-all-read" class="small text-reset text-decoration-underline">Mark all read</a>
                            </div>
                        </div>
                    </div>
                    <div data-simplebar style="max-height: 280px;" id="doc-notification-list">
                        <div class="text-center py-3 text-muted" id="doc-notification-empty">
                            <i class="bx bx-bell-off" style="font-size: 24px;"></i>
                            <p class="mb-0 mt-1">No notifications</p>
                        </div>
                    </div>
                    <div class="p-2 border-top d-grid">
                        <a class="btn btn-sm btn-link font-size-14 text-center" href="#"
                           onclick="event.preventDefault();">
                            <i class="bx bx-right-arrow-circle me-1"></i> View All
                        </a>
                    </div>
                </div>
            </div>
            @endauth
            {{-- 
            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item right-bar-toggle me-2">
                    <i data-feather="settings" class="icon-lg"></i>
                </button>
            </div> --}}


            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item bg-soft-light border-start border-end"
                    id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="assets/images/users/avatar-1.jpg"
                        alt="Header Avatar">
                    <span
                        class="d-none d-xl-inline-block ms-1 fw-medium">{{ auth('sponsor')->user()->full_name ?? '' }}</span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <a class="dropdown-item" href="#"><i class="bx bxs-user font-size-16 align-middle me-1"></i>
                        Profile</a>

                    <a class="dropdown-item text-danger" href="javascript:void();"
                        onclick="logoutAndClearStorage(event)"><i
                            class="mdi mdi-logout font-size-16 align-middle me-1"></i> <span
                            key="t-logout">Logoutjj</span></a>
                    <form id="logout-form" action="{{ route('sponsor.logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>

        </div>
    </div>
</header>

<div class="topnav">
    <div class="container-fluid">
        <nav class="navbar navbar-light navbar-expand-lg topnav-menu">

            <div class="collapse navbar-collapse" id="topnav-menu-content">
                <ul class="navbar-nav">

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="index" id="topnav-dashboard"
                            role="button">
                            <i data-feather="home"></i><span data-key="t-dashboards">Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-uielement"
                            role="button">
                            <i data-feather="briefcase"></i>
                            <span data-key="t-elements">Elements</span>
                            <div class="arrow-down"></div>
                        </a>

                        <div class="dropdown-menu mega-dropdown-menu px-2 dropdown-mega-menu-xl"
                            aria-labelledby="topnav-uielement">
                            <div class="ps-2 p-lg-0">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div>
                                            <div class="menu-title">Elements</div>
                                            <div class="row g-0">
                                                <div class="col-lg-5">
                                                    <div>
                                                        <a href="ui-alerts" class="dropdown-item"
                                                            data-key="t-alerts">Alerts</a>
                                                        <a href="ui-buttons" class="dropdown-item"
                                                            data-key="t-buttons">Buttons</a>
                                                        <a href="ui-cards" class="dropdown-item"
                                                            data-key="t-cards">Cards</a>
                                                        <a href="ui-carousel" class="dropdown-item"
                                                            data-key="t-carousel">Carousel</a>
                                                        <a href="ui-dropdowns" class="dropdown-item"
                                                            data-key="t-dropdowns">Dropdowns</a>
                                                        <a href="ui-grid" class="dropdown-item"
                                                            data-key="t-grid">Grid</a>
                                                        <a href="ui-images" class="dropdown-item"
                                                            data-key="t-images">Images</a>
                                                        <a href="ui-modals" class="dropdown-item"
                                                            data-key="t-modals">Modals</a>
                                                    </div>
                                                </div>
                                                <div class="col-lg-5">
                                                    <div>
                                                        <a href="ui-offcanvas" class="dropdown-item"
                                                            data-key="t-offcanvas">Offcanvas</a>
                                                        <a href="ui-progressbars" class="dropdown-item"
                                                            data-key="t-progress-bars">Progress Bars</a>
                                                        <a href="ui-tabs-accordions" class="dropdown-item"
                                                            data-key="t-tabs-accordions">Tabs & Accordions</a>
                                                        <a href="ui-typography" class="dropdown-item"
                                                            data-key="t-typography">Typography</a>
                                                        <a href="ui-video" class="dropdown-item"
                                                            data-key="t-video">Video</a>
                                                        <a href="ui-general" class="dropdown-item"
                                                            data-key="t-general">General</a>
                                                        <a href="ui-colors" class="dropdown-item"
                                                            data-key="t-colors">Colors</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div>
                                            <div class="menu-title">Extended</div>
                                            <div>
                                                <a href="extended-lightbox" class="dropdown-item"
                                                    data-key="t-lightbox">Lightbox</a>
                                                <a href="extended-rangeslider" class="dropdown-item"
                                                    data-key="t-range-slider">Range Slider</a>
                                                <a href="extended-sweet-alert" class="dropdown-item"
                                                    data-key="t-sweet-alert">SweetAlert 2</a>
                                                <a href="extended-session-timeout" class="dropdown-item"
                                                    data-key="t-session-timeout">Session Timeout</a>
                                                <a href="extended-rating" class="dropdown-item"
                                                    data-key="t-rating">Rating</a>
                                                <a href="extended-notifications" class="dropdown-item"
                                                    data-key="t-notifications">Notifications</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-pages"
                            role="button">
                            <i data-feather="grid"></i><span data-key="t-apps">Apps</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">

                            <a href="apps-calendar" class="dropdown-item" data-key="t-calendar">Calendar</a>
                            <a href="apps-chat" class="dropdown-item" data-key="t-chat">Chat</a>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-email"
                                    role="button">
                                    <span data-key="t-email">Email</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-email">
                                    <a href="apps-email-inbox" class="dropdown-item" data-key="t-inbox">Inbox</a>
                                    <a href="apps-email-read" class="dropdown-item" data-key="t-read-email">Read
                                        Email</a>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#"
                                    id="topnav-invoice" role="button">
                                    <span data-key="t-invoices">Invoices</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-invoice">
                                    <a href="apps-invoices-list" class="dropdown-item"
                                        data-key="t-invoice-list">Invoice
                                        List</a>
                                    <a href="apps-invoices-detail" class="dropdown-item"
                                        data-key="t-invoice-detail">Invoice Detail</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#"
                                    id="topnav-contact" role="button">
                                    <span data-key="t-contacts">Contacts</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-contact">
                                    <a href="apps-contacts-grid" class="dropdown-item" data-key="t-user-grid">User
                                        Grid</a>
                                    <a href="apps-contacts-list" class="dropdown-item" data-key="t-user-list">User
                                        List</a>
                                    <a href="apps-contacts-profile" class="dropdown-item"
                                        data-key="t-profile">Profile</a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-components"
                            role="button">
                            <i data-feather="box"></i><span data-key="t-components">Components</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-components">
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-form"
                                    role="button">
                                    <span data-key="t-forms">Forms</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-form">
                                    <a href="form-elements" class="dropdown-item" data-key="t-form-elements">Basic
                                        Elements</a>
                                    <a href="form-validation" class="dropdown-item"
                                        data-key="t-form-validation">Validation</a>
                                    <a href="form-advanced" class="dropdown-item" data-key="t-form-advanced">Advanced
                                        Plugins</a>
                                    <a href="form-editors" class="dropdown-item"
                                        data-key="t-form-editors">Editors</a>
                                    <a href="form-uploads" class="dropdown-item" data-key="t-form-upload">File
                                        Upload</a>
                                    <a href="form-wizard" class="dropdown-item" data-key="t-form-wizard">Wizard</a>
                                    <a href="form-mask" class="dropdown-item" data-key="t-form-mask">Mask</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-table"
                                    role="button">
                                    <span data-key="t-tables">Tables</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-table">
                                    <a href="tables-basic" class="dropdown-item" data-key="t-basic-tables">Bootstrap
                                        Basic</a>
                                    <a href="tables-datatable" class="dropdown-item" data-key="t-data-tables">Data
                                        Tables</a>
                                    <a href="tables-responsive" class="dropdown-item"
                                        data-key="t-responsive-table">Responsive</a>
                                    <a href="tables-editable" class="dropdown-item"
                                        data-key="t-editable-table">Editable</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-charts"
                                    role="button">
                                    <span data-key="t-charts">Charts</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-charts">
                                    <a href="charts-apex" class="dropdown-item" data-key="t-apex-charts">Apex
                                        charts</a>
                                    <a href="charts-echart" class="dropdown-item" data-key="t-e-charts">E
                                        charts</a>
                                    <a href="charts-chartjs" class="dropdown-item"
                                        data-key="t-chartjs-charts">Chartjs</a>
                                    <a href="charts-knob" class="dropdown-item" data-key="t-knob-charts">Jquery
                                        Knob</a>
                                    <a href="charts-sparkline" class="dropdown-item"
                                        data-key="t-sparkline-charts">Sparkline</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-icons"
                                    role="button">
                                    <span data-key="t-icons">Icons</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-icons">
                                    <a href="icons-boxicons" class="dropdown-item" data-key="t-boxicons">Boxicons</a>
                                    <a href="icons-materialdesign" class="dropdown-item"
                                        data-key="t-material-design">Material Design</a>
                                    <a href="icons-dripicons" class="dropdown-item"
                                        data-key="t-dripicons">Dripicons</a>
                                    <a href="icons-fontawesome" class="dropdown-item" data-key="t-font-awesome">Font
                                        Awesome 5</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-map"
                                    role="button">
                                    <span data-key="t-maps">Maps</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-map">
                                    <a href="maps-google" class="dropdown-item" data-key="t-g-maps">Google</a>
                                    <a href="maps-vector" class="dropdown-item" data-key="t-v-maps">Vector</a>
                                    <a href="maps-leaflet" class="dropdown-item" data-key="t-l-maps">Leaflet</a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-more"
                            role="button">
                            <i data-feather="file-text"></i><span data-key="t-extra-pages">Extra Pages</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-more">

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-auth"
                                    role="button">
                                    <span data-key="t-authentication">Authentication</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-auth">
                                    <a href="auth-login" class="dropdown-item" data-key="t-login">Login</a>
                                    <a href="auth-register" class="dropdown-item" data-key="t-register">Register</a>
                                    <a href="auth-recoverpw" class="dropdown-item"
                                        data-key="t-recover-password">Recover
                                        Password</a>
                                    <a href="auth-lock-screen" class="dropdown-item" data-key="t-lock-screen">Lock
                                        Screen</a>
                                    <a href="auth-logout" class="dropdown-item" data-key="t-logout">Log Out</a>
                                    <a href="auth-confirm-mail" class="dropdown-item"
                                        data-key="t-confirm-mail">Confirm
                                        Mail</a>
                                    <a href="auth-email-verification" class="dropdown-item"
                                        data-key="t-email-verification">Email verification</a>
                                    <a href="auth-two-step-verification" class="dropdown-item"
                                        data-key="t-two-step-verification">Two step verification</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#"
                                    id="topnav-utility" role="button">
                                    <span data-key="t-utility">Utility</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-utility">
                                    <a href="pages-starter" class="dropdown-item" data-key="t-starter-page">Starter
                                        Page</a>
                                    <a href="pages-maintenance" class="dropdown-item"
                                        data-key="t-maintenance">Maintenance</a>
                                    <a href="pages-comingsoon" class="dropdown-item" data-key="t-coming-soon">Coming
                                        Soon</a>
                                    <a href="pages-timeline" class="dropdown-item" data-key="t-timeline">Timeline</a>
                                    <a href="pages-faqs" class="dropdown-item" data-key="t-faqs">FAQs</a>
                                    <a href="pages-pricing" class="dropdown-item" data-key="t-pricing">Pricing</a>
                                    <a href="pages-404" class="dropdown-item" data-key="t-error-404">Error 404</a>
                                    <a href="pages-500" class="dropdown-item" data-key="t-error-500">Error 500</a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="layouts-horizontal" role="button">
                            <i data-feather="layout"></i><span data-key="t-horizontal">Horizontal</span>
                        </a>
                    </li>

                </ul>
            </div>
        </nav>
    </div>
</div>
