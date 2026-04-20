<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="fas fa-clock me-2 text-primary"></i>Retention Policies</h5>
    <a href="{{ route('documents.retention-policies.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> New Policy
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($policies->isEmpty())
    <div class="empty-state">
        <i class="fas fa-clock d-block"></i>
        <h5>No Retention Policies</h5>
        <p>Create a retention policy to automate document lifecycle management.</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover policy-table mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Retention</th>
                    <th>Grace Period</th>
                    <th>Action</th>
                    <th>Status</th>
                    <th>Last Run</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($policies as $policy)
                    <tr>
                        <td>
                            <strong>{{ $policy->name }}</strong>
                            @if ($policy->description)
                                <br><small class="text-muted">{{ Str::limit($policy->description, 60) }}</small>
                            @endif
                        </td>
                        <td>
                            @if (!empty($policy->conditions['category_id']))
                                {{ \App\Models\DocumentCategory::find($policy->conditions['category_id'])?->name ?? 'Unknown' }}
                            @else
                                <span class="text-muted">All Categories</span>
                            @endif
                        </td>
                        <td>{{ number_format($policy->retention_days) }} days</td>
                        <td>{{ $policy->grace_period_days }} days</td>
                        <td>
                            @switch($policy->action)
                                @case('archive')
                                    <span class="badge-action badge-archive">Archive</span>
                                    @break
                                @case('delete')
                                    <span class="badge-action badge-delete">Delete</span>
                                    @break
                                @case('notify_owner')
                                    <span class="badge-action badge-notify">Notify Owner</span>
                                    @break
                            @endswitch
                        </td>
                        <td>
                            <span class="badge-status {{ $policy->is_active ? 'badge-active' : 'badge-inactive' }}">
                                {{ $policy->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @if ($policy->last_run_at)
                                <span title="{{ $policy->last_run_at->format('d M Y H:i') }}">
                                    {{ $policy->last_run_at->diffForHumans() }}
                                </span>
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('documents.retention-policies.edit', $policy) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('documents.retention-policies.destroy', $policy) }}" method="POST" class="d-inline delete-policy-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
