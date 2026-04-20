@extends('layouts.master')

@section('title', 'LTI Tools')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0"><i class="fas fa-plug me-2"></i>LTI Tools</h4>
                <p class="text-muted mb-0">External tools integrated via Learning Tools Interoperability</p>
            </div>
            <a href="{{ route('lms.lti.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Tool
            </a>
        </div>
    </div>

    @if($tools->count())
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tool</th>
                                <th>Version</th>
                                <th>Privacy</th>
                                <th class="text-center">Resource Links</th>
                                <th class="text-center">Launches</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tools as $tool)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($tool->icon_url)
                                                <img src="{{ $tool->icon_url }}" class="me-2" width="32" height="32" alt="">
                                            @else
                                                <div class="bg-light rounded p-2 me-2">
                                                    <i class="fas fa-external-link-alt text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <strong>{{ $tool->name }}</strong>
                                                @if($tool->description)
                                                    <br><small class="text-muted">{{ Str::limit($tool->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $tool->lti_version === '1.3' ? 'primary' : 'secondary' }}">
                                            LTI {{ $tool->lti_version }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $tool->privacy_level === 'public' ? 'success' : ($tool->privacy_level === 'name_only' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $tool->privacy_level)) }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $tool->resource_links_count }}</td>
                                    <td class="text-center">{{ $tool->launches_count }}</td>
                                    <td>
                                        @if($tool->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('lms.lti.show', $tool) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('lms.lti.edit', $tool) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-plug fa-4x text-muted mb-3"></i>
                <h5>No LTI Tools Configured</h5>
                <p class="text-muted mb-3">Add external learning tools to integrate with your courses.</p>
                <a href="{{ route('lms.lti.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add First Tool
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
