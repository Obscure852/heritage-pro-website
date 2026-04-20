<style>
    /* Welcome Container */
    .welcome-container {
        background: white;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .welcome-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 28px;
    }

    .welcome-header h2 {
        margin: 0 0 8px 0;
        font-weight: 600;
    }

    .welcome-header p {
        margin: 0;
        opacity: 0.9;
        font-size: 14px;
    }

    .welcome-body {
        padding: 24px;
    }

    /* Setup Guide Container */
    .setup-container {
        background: white;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .setup-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 20px 24px;
    }

    .setup-header h5 {
        margin: 0;
        font-weight: 600;
    }

    .setup-body {
        padding: 24px;
    }

    .setup-step {
        transition: all 0.3s ease;
        background: #f8f9fa;
        border-radius: 3px;
    }

    .setup-step:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .setup-step.completed {
        background: #f0fdf4;
        border-color: #10b981 !important;
    }

    .setup-step.active {
        background: #eff6ff;
        border-color: #3b82f6 !important;
    }

    .setup-step.pending {
        background: #f8f9fa;
        border-color: #e5e7eb !important;
    }

    /* Progress Bar */
    .progress {
        height: 8px;
        border-radius: 3px;
        background: #e5e7eb;
    }

    .progress-bar {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        border-radius: 3px;
    }

    /* Info Cards */
    .info-card {
        background: white;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        height: 100%;
        overflow: hidden;
    }

    .info-card-header {
        background: #f9fafb;
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .info-card-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 15px;
        color: #1f2937;
    }

    .info-card-body {
        padding: 20px;
    }

    /* Stats Container */
    .stats-container {
        background: white;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .stats-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 20px 24px;
    }

    .stats-header h5 {
        margin: 0;
        font-weight: 600;
    }

    .stats-body {
        padding: 24px;
    }

    .stat-card {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        border-radius: 3px;
        padding: 20px;
        text-align: center;
        color: white;
        height: 100%;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
    }

    .stat-card i {
        font-size: 32px;
        opacity: 0.8;
        margin-bottom: 8px;
    }

    .stat-card h3 {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0 0 4px 0;
    }

    .stat-card h6 {
        font-size: 14px;
        margin: 0;
        opacity: 0.9;
    }

    .stat-card small {
        font-size: 12px;
        opacity: 0.75;
    }

    /* Quick Stats Summary */
    .quick-stats {
        background: #f9fafb;
        border-radius: 3px;
        padding: 16px;
        margin-top: 20px;
    }

    .quick-stat-item {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
    }

    .quick-stat-item i {
        font-size: 20px;
        margin-right: 8px;
    }

    /* Step List Items */
    .step-list-item {
        display: flex;
        align-items: center;
        padding: 10px 12px;
        background: #f9fafb;
        border-radius: 3px;
        margin-bottom: 8px;
        transition: all 0.2s ease;
    }

    .step-list-item:hover {
        background: #f3f4f6;
    }

    .step-list-item i {
        color: #4e73df;
        margin-right: 10px;
    }

    /* Custom Button Styling */
    .btn-welcome {
        padding: 10px 20px;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-welcome-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .btn-welcome-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }

    .btn-welcome-light {
        background: white;
        color: #1f2937;
    }

    .btn-welcome-light:hover {
        background: #f3f4f6;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        color: #1f2937;
    }

    .btn-welcome-outline-light {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.5);
        color: white;
    }

    .btn-welcome-outline-light:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: white;
        transform: translateY(-1px);
        color: white;
    }

    .btn-welcome-outline-primary {
        background: transparent;
        border: 1px solid #3b82f6;
        color: #3b82f6;
    }

    .btn-welcome-outline-primary:hover {
        background: #3b82f6;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }

    .btn-welcome-outline-success {
        background: transparent;
        border: 1px solid #10b981;
        color: #10b981;
    }

    .btn-welcome-outline-success:hover {
        background: #10b981;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        color: white;
    }

    .btn-welcome-outline-secondary {
        background: transparent;
        border: 1px solid #d1d5db;
        color: #6b7280;
    }

    .btn-welcome-outline-secondary:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
        color: #374151;
    }

    .btn-welcome-outline-secondary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    @media (max-width: 768px) {
        .welcome-header,
        .setup-header,
        .stats-header {
            padding: 20px;
        }

        .welcome-body,
        .setup-body,
        .stats-body {
            padding: 16px;
        }

        .stat-card h3 {
            font-size: 1.5rem;
        }

        .quick-stat-item {
            margin-bottom: 12px;
        }
    }
</style>

<!-- Welcome Hero Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-container">
            <div class="welcome-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2><i class="bx bx-buildings me-2"></i>Welcome to Heritage Pro</h2>
                        <p>Your journey to streamlined school administration starts here. Let's get your system set up and ready to go!</p>
                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('setup.school-setup') }}" class="btn-welcome btn-welcome-light">
                                <i class="bx bx-rocket"></i>Start Setup
                            </a>
                            <a href="{{ route('setup.data-importing') }}" class="btn-welcome btn-welcome-outline-light">
                                <i class="bx bx-upload"></i>Import Data
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 text-center d-none d-md-block">
                        <i class="bx bx-buildings" style="font-size: 80px; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Setup Progress & Guide -->
<div class="row mb-4">
    <div class="col-12">
        <div class="setup-container">
            <div class="setup-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h5><i class="bx bx-list-check me-2"></i>Setup Guide</h5>
                    <span class="badge bg-white text-primary">1 of 6 Complete</span>
                </div>
            </div>
            <div class="setup-body">
                <!-- Progress Bar -->
                <div class="mb-4">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 16.67%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block">Setup Progress: 16% Complete</small>
                </div>

                <!-- Setup Steps -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-start p-3 rounded border setup-step completed">
                            <div class="flex-shrink-0 me-3">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bx bx-check font-size-18"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">1. School Brand Setup</h6>
                                <p class="text-muted mb-2 small">Configure your school's basic information and branding</p>
                                <a href="{{ route('setup.school-setup') }}" class="btn-welcome btn-welcome-outline-success">
                                    <i class="bx bx-edit"></i>Go to Setup
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex align-items-start p-3 rounded border setup-step active">
                            <div class="flex-shrink-0 me-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bx bx-user-plus font-size-18"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">2. Create Teacher Accounts</h6>
                                <p class="text-muted mb-2 small">Add teaching staff and administrative users</p>
                                <a href="{{ route('staff.index') }}" class="btn-welcome btn-welcome-primary">
                                    <i class="bx bx-plus"></i>Add Teachers
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex align-items-start p-3 rounded border setup-step pending">
                            <div class="flex-shrink-0 me-3">
                                <div class="rounded-circle bg-light text-muted d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bx bx-home font-size-18"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 text-muted">3. Set Up Classes</h6>
                                <p class="text-muted mb-2 small">Create class structures and grade levels</p>
                                <button class="btn-welcome btn-welcome-outline-secondary" disabled>
                                    <i class="bx bx-time me-1"></i>Pending
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex align-items-start p-3 rounded border setup-step pending">
                            <div class="flex-shrink-0 me-3">
                                <div class="rounded-circle bg-light text-muted d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bx bx-upload font-size-18"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 text-muted">4. Import Student Data</h6>
                                <p class="text-muted mb-2 small">Bulk import students, parents, and academic records</p>
                                <a href="{{ route('setup.data-importing') }}" class="btn-welcome btn-welcome-outline-secondary">
                                    <i class="bx bx-upload"></i>Import Data
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex align-items-start p-3 rounded border setup-step pending">
                            <div class="flex-shrink-0 me-3">
                                <div class="rounded-circle bg-light text-muted d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bx bx-calendar font-size-18"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 text-muted">5. Configure School Terms</h6>
                                <p class="text-muted mb-2 small">Set up academic calendar and term structure</p>
                                <button class="btn-welcome btn-welcome-outline-secondary" disabled>
                                    <i class="bx bx-time me-1"></i>Pending
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex align-items-start p-3 rounded border setup-step pending">
                            <div class="flex-shrink-0 me-3">
                                <div class="rounded-circle bg-light text-muted d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bx bx-bell font-size-18"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 text-muted">6. Set Up Notifications</h6>
                                <p class="text-muted mb-2 small">Configure system notifications and announcements</p>
                                <button class="btn-welcome btn-welcome-outline-secondary" disabled>
                                    <i class="bx bx-time me-1"></i>Pending
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions & Data Import -->
<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="info-card">
            <div class="info-card-header">
                <h5><i class="bx bx-upload me-2 text-primary"></i>Quick Data Import</h5>
            </div>
            <div class="info-card-body">
                <p class="text-muted mb-3">Get started quickly by importing your existing data:</p>

                <div class="step-list-item">
                    <i class="bx bx-download"></i>
                    <span class="small">1. Download import templates</span>
                </div>
                <div class="step-list-item">
                    <i class="bx bx-edit"></i>
                    <span class="small">2. Fill in your data</span>
                </div>
                <div class="step-list-item">
                    <i class="bx bx-cloud-upload"></i>
                    <span class="small">3. Upload completed files</span>
                </div>

                <div class="mt-3">
                    <a href="{{ route('setup.data-importing') }}" class="btn-welcome btn-welcome-primary" style="width: 100%; justify-content: center;">
                        <i class="bx bx-upload"></i>Start Import Process
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="info-card">
            <div class="info-card-header">
                <h5><i class="bx bx-help-circle me-2 text-primary"></i>Need Help?</h5>
            </div>
            <div class="info-card-body">
                <p class="text-muted mb-3">Get support and resources to help you get started:</p>

                <div class="d-grid gap-2">
                    <a href="#" class="btn-welcome btn-welcome-outline-primary" style="justify-content: center;">
                        <i class="bx bx-book"></i>User Documentation
                    </a>
                    <a href="#" class="btn-welcome btn-welcome-outline-primary" style="justify-content: center;">
                        <i class="bx bx-video"></i>Video Tutorials
                    </a>
                    <a href="#" class="btn-welcome btn-welcome-outline-primary" style="justify-content: center;">
                        <i class="bx bx-support"></i>Contact Support
                    </a>
                </div>

                <div class="mt-3 p-2 bg-light rounded">
                    <small class="text-muted">
                        <i class="bx bx-info-circle me-1"></i>
                        Our support team is available 24/7 to help you get started.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Statistics -->
<div class="row mb-4">
    <div class="col-12">
        <div class="stats-container">
            <div class="stats-header">
                <h5><i class="bx bx-bar-chart-alt-2 me-2"></i>System Overview</h5>
            </div>
            <div class="stats-body">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <div class="stat-card">
                            <i class="bx bx-user"></i>
                            <h3>{{ $users->count() ?? 0 }}</h3>
                            <h6>Teachers</h6>
                            <small>Active Staff Members</small>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="stat-card">
                            <i class="bx bx-home"></i>
                            <h3>0</h3>
                            <h6>Classes</h6>
                            <small>Active Classrooms</small>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="stat-card">
                            <i class="bx bx-group"></i>
                            <h3>0</h3>
                            <h6>Students</h6>
                            <small>Enrolled Students</small>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="stat-card">
                            <i class="bx bx-bell"></i>
                            <h3>0</h3>
                            <h6>Notifications</h6>
                            <small>Active Alerts</small>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Summary -->
                <div class="quick-stats">
                    <div class="row text-center">
                        <div class="col-md-3 col-6">
                            <div class="quick-stat-item">
                                <i class="bx bx-trending-up text-success"></i>
                                <div class="text-start">
                                    <small class="text-muted d-block">Setup Progress</small>
                                    <strong>16% Complete</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="quick-stat-item">
                                <i class="bx bx-time text-warning"></i>
                                <div class="text-start">
                                    <small class="text-muted d-block">Estimated Time</small>
                                    <strong>~30 minutes</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="quick-stat-item">
                                <i class="bx bx-check-circle text-primary"></i>
                                <div class="text-start">
                                    <small class="text-muted d-block">Next Step</small>
                                    <strong>Add Teachers</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="quick-stat-item">
                                <i class="bx bx-support text-info"></i>
                                <div class="text-start">
                                    <small class="text-muted d-block">Support Status</small>
                                    <strong>Available 24/7</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
