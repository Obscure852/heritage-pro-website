@extends('layouts.master')

@section('title')
    WhatsApp Templates
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('notifications.index') }}">Communications</a>
        @endslot
        @slot('title')
            WhatsApp Templates
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-all me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">Synced WhatsApp Templates</h5>
                <small class="text-muted">Templates are sourced from Twilio Content and filtered for WhatsApp sends.</small>
            </div>
            <form method="POST" action="{{ route('whatsapp-templates.sync') }}">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-rotate me-1"></i> Sync Templates
                </button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Language</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Preview</th>
                            <th>Synced</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($templates as $template)
                            <tr>
                                <td>{{ $template->name }}</td>
                                <td>{{ strtoupper($template->language) }}</td>
                                <td>{{ ucfirst($template->category ?? 'utility') }}</td>
                                <td>
                                    <span class="badge {{ in_array($template->status, ['approved', 'active']) ? 'bg-success' : 'bg-secondary' }}">
                                        {{ strtoupper($template->status) }}
                                    </span>
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($template->body_preview ?? 'No preview available', 80) }}</td>
                                <td>{{ $template->last_synced_at?->format('Y-m-d H:i') ?? 'Never' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No WhatsApp templates have been synced yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $templates->links() }}
            </div>
        </div>
    </div>
@endsection
