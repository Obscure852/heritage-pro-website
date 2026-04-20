@php
    $smsEnabled = $communicationChannels['sms_enabled'] ?? false;
    $whatsappEnabled = $communicationChannels['whatsapp_enabled'] ?? false;
@endphp
<div class="table-responsive">
    <table id="datatable-icons" class="table table-striped align-middle">
    <thead>
        <tr>
            <th>Name</th>
            <th>Gender</th>
            <th>Email</th>
            <th>Date Of Birth</th>
            <th>ID Number</th>
            <th>Position</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($users as $user)
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-circle me-3">
                            @if ($user->avatar)
                                <img src="{{ URL::asset('storage/' . $user->avatar) }}" alt="{{ $user->full_name }}"
                                    class="rounded-circle">
                            @else
                                <div class="avatar-placeholder">
                                    {{ strtoupper(substr($user->full_name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                        <a href="{{ route('staff.staff-view', $user->id) }}" class="text-body">{{ $user->full_name }}</a>
                    </div>
                </td>
                <td>
                    @if ($user->gender == 'M')
                        <span class="gender-male"><i class="fas fa-mars"></i> {{ $user->gender }}</span>
                    @else
                        <span class="gender-female"><i class="fas fa-venus"></i> {{ $user->gender }}</span>
                    @endif
                </td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->formatted_date_of_birth }}</td>
                <td>{{ $user->formatted_id_number ?? '' }}</td>
                <td>{{ $user->position }}</td>
                <td>
                    <div class="action-buttons">
                        <a href="{{ route('staff.staff-view', $user->id) }}" class="btn btn-sm btn-outline-info"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
                            <i class="bx bx-edit-alt"></i>
                        </a>
                        @can('manage-hr')
                            @if ($user->hasValidEmail())
                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                    data-bs-target="#sendEmailModal" data-recipient-email="{{ $user->email }}"
                                    data-recipient-id="{{ $user->id }}" data-recipient-type="user"
                                    data-bs-custom-tooltip="true" data-bs-placement="top" title="Send Email">
                                    <i class="bx bx-envelope"></i>
                                </button>
                            @endif
                            @if ($user->hasValidPhoneNumber() && $smsEnabled)
                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                    data-bs-target="#sendSmsModal" data-recipient-id="{{ $user->id }}"
                                    data-recipient-type="user" data-bs-custom-tooltip="true" data-bs-placement="top"
                                    title="Send SMS">
                                    <i class="fas fa-sms"></i>
                                </button>
                            @endif
                            @if ($user->hasValidPhoneNumber() && $whatsappEnabled)
                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                    data-bs-target="#sendWhatsappModal" data-recipient-id="{{ $user->id }}"
                                    data-recipient-type="user" data-bs-custom-tooltip="true" data-bs-placement="top"
                                    title="Send WhatsApp">
                                    <i class="bx bxl-whatsapp"></i>
                                </button>
                            @endif
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">No staff found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>
