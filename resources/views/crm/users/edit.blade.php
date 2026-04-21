@extends('layouts.crm')

@section('title', 'Edit CRM User')
@section('crm_heading', $user->name)
@section('crm_subheading', 'Update the internal CRM account, role assignment, and activation state for this team member.')

@section('crm_actions')
    <a href="{{ route('crm.users.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to users
    </a>
@endsection

@section('content')
    <div class="crm-grid cols-2">
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">User details</p>
                    <h2>Edit account</h2>
                </div>
            </div>

            @include('crm.users._form', [
                'action' => route('crm.users.update', $user),
                'method' => 'PATCH',
                'submitLabel' => 'Save changes',
                'submitIcon' => 'fas fa-save',
                'cancelUrl' => route('crm.users.index'),
                'deleteUrl' => route('crm.users.destroy', $user),
                'user' => $user,
            ])
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Account summary</p>
                    <h2>Current status</h2>
                </div>
            </div>

            <div class="crm-meta-list">
                <div class="crm-meta-row">
                    <span>Name</span>
                    <strong>{{ $user->name }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Email</span>
                    <strong>{{ $user->email }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Role</span>
                    <strong>{{ $roles[$user->role] ?? ucfirst($user->role) }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Status</span>
                    <strong>{{ $user->active ? 'Active' : 'Inactive' }}</strong>
                </div>
                <div class="crm-meta-row">
                    <span>Created</span>
                    <strong>{{ $user->created_at?->format('d M Y H:i') ?: 'Unknown' }}</strong>
                </div>
            </div>
        </section>
    </div>
@endsection
