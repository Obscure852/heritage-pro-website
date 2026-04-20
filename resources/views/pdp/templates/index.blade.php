@extends('layouts.master')

@section('title', 'PDP Templates')
@section('page_title', 'PDP Templates')
@section('css')
    @include('pdp.partials.theme-css')
    <style>
        .pdp-theme .templates-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .pdp-theme .templates-header-copy {
            flex: 1 1 auto;
            min-width: 0;
        }

        .pdp-theme .templates-stats {
            width: 100%;
            max-width: 360px;
        }

        .pdp-theme .templates-stats .stat-item {
            padding: 10px 0;
            text-align: center;
        }

        .pdp-theme .templates-stats .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0;
            color: #fff;
        }

        .pdp-theme .templates-stats .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.75;
        }

        .pdp-theme .templates-toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }

        .pdp-theme .templates-empty-state {
            padding: 40px 0;
        }

        .pdp-theme .templates-empty-state .empty-icon {
            font-size: 48px;
            opacity: 0.3;
        }

        .pdp-theme .templates-empty-state .empty-copy {
            font-size: 15px;
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .pdp-theme .templates-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .pdp-theme .templates-stats {
                max-width: none;
            }

            .pdp-theme .templates-toolbar {
                justify-content: flex-start;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Staff PDP
        @endslot
        @slot('li_1_url')
            {{ route('staff.pdp.plans.index') }}
        @endslot
        @slot('title')
            PDP Templates
        @endslot
    @endcomponent

    <div class="pdp-theme">
        <div class="page-shell">
            <div class="page-shell-header">
                <div class="templates-header">
                    <div class="templates-header-copy">
                        <div class="page-shell-title">PDP Templates</div>
                        <div class="page-subtitle">
                            Manage draft, published, active, and archived template versions without changing the structure of existing plan records.
                        </div>
                    </div>
                    <div class="templates-stats">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4>{{ $templates->count() }}</h4>
                                    <small>Total</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4>{{ $templates->where('status', 'published')->count() }}</h4>
                                    <small>Published</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4>{{ $templates->where('status', 'draft')->count() }}</h4>
                                    <small>Drafts</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-shell-body">
                <div class="help-text">
                    <div class="help-title">Template Version Control</div>
                    <div class="help-content">
                        Use this page to manage draft and published PDP template versions while keeping older staff plans bound to the template version they were created with.
                    </div>
                </div>

                <div class="templates-toolbar">
                    <a href="{{ route('staff.pdp.templates.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Create Draft Template
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Family</th>
                                <th>Version</th>
                                <th>Status</th>
                                <th>Plans</th>
                                <th>Source</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($templates as $template)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $template->name }}</div>
                                        <div class="text-muted small">{{ $template->code }}</div>
                                    </td>
                                    <td>{{ $template->template_family_key }}</td>
                                    <td>v{{ $template->version }}</td>
                                    <td>
                                        <span class="badge-soft badge-soft-dark">{{ ucfirst($template->status) }}</span>
                                        @if ($template->is_default)
                                            <span class="badge-soft badge-soft-primary ms-1">Active Default</span>
                                        @endif
                                    </td>
                                    <td>{{ $template->plans_count }}</td>
                                    <td>{{ $template->source_reference ?: 'N/A' }}</td>
                                    <td class="text-end">
                                        <div class="action-buttons">
                                            <a href="{{ route('staff.pdp.templates.show', $template) }}" class="btn btn-outline-primary" title="Open Template">
                                                <i class="bx bx-right-arrow-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="text-center text-muted templates-empty-state">
                                            <div>
                                                <i class="bx bx-layer empty-icon"></i>
                                            </div>
                                            <p class="mt-3 empty-copy">No PDP templates are available.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
