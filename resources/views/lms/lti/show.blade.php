@extends('layouts.master')

@section('title', $tool->name . ' - LTI Tool')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('lms.lti.index') }}">LTI Tools</a></li>
                    <li class="breadcrumb-item active">{{ $tool->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                @if($tool->icon_url)
                    <img src="{{ $tool->icon_url }}" class="me-3" width="48" height="48" alt="">
                @else
                    <div class="bg-light rounded p-3 me-3">
                        <i class="fas fa-external-link-alt fa-2x text-muted"></i>
                    </div>
                @endif
                <div>
                    <h4 class="mb-0">{{ $tool->name }}</h4>
                    <span class="badge bg-{{ $tool->lti_version === '1.3' ? 'primary' : 'secondary' }} me-2">LTI {{ $tool->lti_version }}</span>
                    @if($tool->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('lms.lti.launch', $tool) }}" class="btn btn-success" target="_blank">
                    <i class="fas fa-rocket me-2"></i>Test Launch
                </a>
                <a href="{{ route('lms.lti.edit', $tool) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit me-2"></i>Edit
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Tool Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Configuration</h6>
                </div>
                <div class="card-body">
                    @if($tool->description)
                        <p class="text-muted mb-4">{{ $tool->description }}</p>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted mb-1">Tool URL</label>
                            <div class="font-monospace small bg-light p-2 rounded">{{ $tool->tool_url }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted mb-1">Privacy Level</label>
                            <div>
                                <span class="badge bg-{{ $tool->privacy_level === 'public' ? 'success' : ($tool->privacy_level === 'name_only' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst(str_replace('_', ' ', $tool->privacy_level)) }}
                                </span>
                                <small class="text-muted ms-2">
                                    @if($tool->privacy_level === 'public')
                                        Sends name and email
                                    @elseif($tool->privacy_level === 'name_only')
                                        Sends name only
                                    @else
                                        No personal data sent
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>

                    @if($tool->isLti13())
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small text-muted mb-1">Login URL</label>
                                <div class="font-monospace small bg-light p-2 rounded">{{ $tool->login_url ?: 'Not configured' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small text-muted mb-1">Client ID</label>
                                <div class="font-monospace small bg-light p-2 rounded">{{ $tool->client_id ?: 'Auto-generated' }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Placements -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-th-large me-2"></i>Placements</h6>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPlacementModal">
                        <i class="fas fa-plus me-1"></i>Add
                    </button>
                </div>
                <div class="card-body">
                    @if($tool->placements->count())
                        <div class="list-group list-group-flush">
                            @foreach($tool->placements as $placement)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong>{{ $placement->label ?? App\Models\Lms\LtiPlacement::$placementTypes[$placement->placement_type] ?? $placement->placement_type }}</strong>
                                        <br><small class="text-muted">{{ $placement->placement_type }}</small>
                                    </div>
                                    @if($placement->icon_url)
                                        <img src="{{ $placement->icon_url }}" width="24" height="24" alt="">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No placements configured. Add placements to control where this tool appears.</p>
                    @endif
                </div>
            </div>

            <!-- Resource Links -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-link me-2"></i>Resource Links</h6>
                </div>
                <div class="card-body">
                    @if($tool->resourceLinks->count())
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Course</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tool->resourceLinks as $link)
                                        <tr>
                                            <td>{{ $link->title }}</td>
                                            <td>
                                                @if($link->course)
                                                    <a href="{{ route('lms.courses.show', $link->course) }}">{{ $link->course->title }}</a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $link->created_at->format('M j, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No resource links created yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Launch Stats -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Launch Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h3 class="mb-0">{{ $launchStats['total'] }}</h3>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col-4">
                            <h3 class="mb-0 text-primary">{{ $launchStats['this_week'] }}</h3>
                            <small class="text-muted">This Week</small>
                        </div>
                        <div class="col-4">
                            <h3 class="mb-0 text-success">{{ $launchStats['today'] }}</h3>
                            <small class="text-muted">Today</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Launches -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Launches</h6>
                </div>
                <div class="card-body p-0">
                    @if($recentLaunches->count())
                        <ul class="list-group list-group-flush">
                            @foreach($recentLaunches as $launch)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $launch->user?->name ?? 'Unknown User' }}</strong>
                                            <br><small class="text-muted">{{ $launch->message_type }}</small>
                                        </div>
                                        <small class="text-muted">{{ $launch->launched_at->diffForHumans() }}</small>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="p-3 text-center text-muted">
                            No launches recorded yet.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Platform Info -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-server me-2"></i>Platform Details</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Issuer</small>
                        <div class="font-monospace small">{{ config('app.url') }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Deployment ID</small>
                        <div class="font-monospace small">{{ $tool->deployment_id ?? '1' }}</div>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted">JWKS URL</small>
                        <div class="font-monospace small">{{ route('lms.lti.jwks') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Placement Modal -->
<div class="modal fade" id="addPlacementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('lms.lti.add-placement', $tool) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Placement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Placement Type</label>
                        <select name="placement_type" class="form-select" required>
                            @foreach(App\Models\Lms\LtiPlacement::$placementTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Custom Label (Optional)</label>
                        <input type="text" name="label" class="form-control">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Icon URL (Optional)</label>
                        <input type="url" name="icon_url" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Placement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
