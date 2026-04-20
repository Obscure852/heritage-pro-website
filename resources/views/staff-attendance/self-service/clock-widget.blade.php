{{-- Self-Service Attendance Clock Widget --}}
<style>
    .clock-widget-container {
        margin-bottom: 1.5rem;
    }

    .clock-widget-inner {
        background: white;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .clock-widget-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 1rem 1.25rem;
    }

    .clock-widget-header h5 {
        font-size: 1rem;
    }

    .live-time {
        font-size: 1.5rem;
        font-weight: 700;
        font-family: 'Courier New', Courier, monospace;
        letter-spacing: 1px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .clock-widget-body {
        padding: 1.25rem;
    }

    .status-display {
        text-align: center;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 3px;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .status-badge.loading {
        background: #f3f4f6;
        color: #6b7280;
    }

    .status-badge.not-clocked {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.clocked-in {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.complete {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-badge.late {
        background: #fee2e2;
        color: #991b1b;
    }

    .clock-info {
        background: #f9fafb;
        border-radius: 3px;
        padding: 0.75rem 1rem;
        margin-bottom: 0.75rem;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.375rem 0;
    }

    .info-row:not(:last-child) {
        border-bottom: 1px solid #e5e7eb;
    }

    .info-label {
        color: #6b7280;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
    }

    .info-value {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.875rem;
    }

    .schedule-info {
        text-align: center;
        padding: 0.5rem;
        background: #f3f4f6;
        border-radius: 3px;
    }

    .clock-actions {
        display: flex;
        gap: 0.75rem;
    }

    .btn-clock {
        flex: 1;
        padding: 0.75rem 1rem;
        border-radius: 3px;
        font-weight: 600;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }

    .btn-clock:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-clock-in {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .btn-clock-in:hover:not(:disabled) {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-clock-out {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .btn-clock-out:hover:not(:disabled) {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .btn-clock i {
        font-size: 1.125rem;
    }

    @media (max-width: 576px) {
        .clock-actions {
            flex-direction: column;
        }

        .live-time {
            font-size: 1.25rem;
        }
    }
</style>

<div class="clock-widget-container">
    <div class="clock-widget-inner">
        <div class="clock-widget-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold text-white">
                    <i class="bx bx-time-five me-2"></i>Attendance Clock
                </h5>
                <div class="live-time" id="liveTime">--:--:--</div>
            </div>
        </div>

        <div class="clock-widget-body">
            {{-- Status Display --}}
            <div class="status-display mb-3" id="statusDisplay">
                <span class="status-badge loading">
                    <span class="spinner-border spinner-border-sm me-1"></span>
                    Loading...
                </span>
            </div>

            {{-- Clock Info (hidden until loaded) --}}
            <div class="clock-info" id="clockInfo" style="display: none;">
                <div class="info-row">
                    <span class="info-label"><i class="bx bx-log-in-circle me-1"></i>Clock In:</span>
                    <span class="info-value" id="clockInTime">--</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bx bx-log-out-circle me-1"></i>Clock Out:</span>
                    <span class="info-value" id="clockOutTime">--</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bx bx-timer me-1"></i>Hours:</span>
                    <span class="info-value" id="hoursWorked">--</span>
                </div>
            </div>

            {{-- Work Schedule Info --}}
            <div class="schedule-info" id="scheduleInfo">
                <small class="text-muted">
                    <i class="bx bx-calendar me-1"></i>
                    Work hours: <span id="workStart">--</span> - <span id="workEnd">--</span>
                </small>
            </div>

            {{-- Action Buttons --}}
            <div class="clock-actions mt-3">
                <button type="button" class="btn btn-clock btn-clock-in" id="clockInBtn" disabled>
                    <i class="bx bx-log-in-circle"></i>
                    <span class="btn-text">Clock In</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm"></span>
                    </span>
                </button>
                <button type="button" class="btn btn-clock btn-clock-out" id="clockOutBtn" disabled>
                    <i class="bx bx-log-out-circle"></i>
                    <span class="btn-text">Clock Out</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    // Elements
    const liveTimeEl = document.getElementById('liveTime');
    const statusDisplayEl = document.getElementById('statusDisplay');
    const clockInfoEl = document.getElementById('clockInfo');
    const clockInTimeEl = document.getElementById('clockInTime');
    const clockOutTimeEl = document.getElementById('clockOutTime');
    const hoursWorkedEl = document.getElementById('hoursWorked');
    const workStartEl = document.getElementById('workStart');
    const workEndEl = document.getElementById('workEnd');
    const clockInBtn = document.getElementById('clockInBtn');
    const clockOutBtn = document.getElementById('clockOutBtn');

    // Check if elements exist (guard against multiple inclusions)
    if (!liveTimeEl || !clockInBtn) {
        console.error('Clock widget elements not found');
        return;
    }

    // CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Update live time every second
    function updateLiveTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        liveTimeEl.textContent = `${hours}:${minutes}:${seconds}`;
    }

    // Start live time update
    updateLiveTime();
    setInterval(updateLiveTime, 1000);

    // Update status display
    function updateStatusDisplay(status, isLate = false) {
        let badgeClass = 'loading';
        let statusText = 'Loading...';

        switch (status) {
            case 'not_clocked':
                badgeClass = 'not-clocked';
                statusText = 'Not Clocked In';
                break;
            case 'clocked_in':
                badgeClass = isLate ? 'late' : 'clocked-in';
                statusText = isLate ? 'Clocked In (Late)' : 'Clocked In';
                break;
            case 'complete':
                badgeClass = 'complete';
                statusText = 'Day Complete';
                break;
        }

        statusDisplayEl.innerHTML = `<span class="status-badge ${badgeClass}">${statusText}</span>`;
    }

    // Update clock info display
    function updateClockInfo(data) {
        if (data.clock_in_time || data.clock_out_time) {
            clockInfoEl.style.display = 'block';
            clockInTimeEl.textContent = data.clock_in_time || '--';
            clockOutTimeEl.textContent = data.clock_out_time || '--';
            // Format hours_worked if it's a number
            if (data.hours_worked !== null && data.hours_worked !== undefined) {
                const hours = Math.floor(data.hours_worked);
                const minutes = Math.round((data.hours_worked - hours) * 60);
                hoursWorkedEl.textContent = `${hours}h ${minutes}m`;
            } else {
                hoursWorkedEl.textContent = '--';
            }
        } else {
            clockInfoEl.style.display = 'none';
        }

        // Update work schedule (service returns work_start/work_end)
        if (data.work_start) {
            workStartEl.textContent = data.work_start;
        }
        if (data.work_end) {
            workEndEl.textContent = data.work_end;
        }
    }

    // Update button states
    function updateButtons(canClockIn, canClockOut) {
        clockInBtn.disabled = !canClockIn;
        clockOutBtn.disabled = !canClockOut;
    }

    // Set button loading state
    function setButtonLoading(button, loading) {
        const btnText = button.querySelector('.btn-text');
        const btnSpinner = button.querySelector('.btn-spinner');

        if (loading) {
            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');
            button.disabled = true;
        } else {
            btnText.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
        }
    }

    // Fetch current status
    async function fetchStatus() {
        try {
            const response = await fetch('/staff-attendance/self-service/status', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            // Determine display status based on clocked_in/clocked_out
            let displayStatus = 'not_clocked';
            if (data.clocked_in && data.clocked_out) {
                displayStatus = 'complete';
            } else if (data.clocked_in) {
                displayStatus = 'clocked_in';
            }

            updateStatusDisplay(displayStatus, data.is_late);
            updateClockInfo(data);

            // Derive button states: can clock in if not clocked in, can clock out if clocked in but not out
            const canClockIn = !data.clocked_in;
            const canClockOut = data.clocked_in && !data.clocked_out;
            updateButtons(canClockIn, canClockOut);
        } catch (error) {
            console.error('Failed to fetch status:', error);
            updateStatusDisplay('not_clocked');
            updateButtons(true, false);
        }
    }

    // Clock In action
    async function clockIn() {
        setButtonLoading(clockInBtn, true);

        try {
            const response = await fetch('/staff-attendance/self-service/clock-in', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Clocked In!',
                    html: `
                        <p class="mb-2">You have successfully clocked in.</p>
                        <p class="mb-0"><strong>Time:</strong> ${data.clock_in}</p>
                        ${data.is_late ? '<p class="text-danger mb-0"><strong>Note:</strong> You are late by ' + data.late_minutes + ' minutes</p>' : ''}
                    `,
                    confirmButtonColor: '#10b981'
                });

                // Refresh status
                await fetchStatus();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Clock In Failed',
                    text: data.error || 'Unable to clock in. Please try again.',
                    confirmButtonColor: '#ef4444'
                });
            }
        } catch (error) {
            console.error('Clock in failed:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An unexpected error occurred. Please try again.',
                confirmButtonColor: '#ef4444'
            });
        } finally {
            setButtonLoading(clockInBtn, false);
            fetchStatus(); // Refresh to update button states
        }
    }

    // Clock Out action
    async function clockOut() {
        setButtonLoading(clockOutBtn, true);

        try {
            const response = await fetch('/staff-attendance/self-service/clock-out', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Clocked Out!',
                    html: `
                        <p class="mb-2">You have successfully clocked out.</p>
                        <p class="mb-1"><strong>Clock Out Time:</strong> ${data.clock_out}</p>
                        <p class="mb-0"><strong>Hours Worked:</strong> ${data.hours_worked}</p>
                    `,
                    confirmButtonColor: '#10b981'
                });

                // Refresh status
                await fetchStatus();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Clock Out Failed',
                    text: data.error || 'Unable to clock out. Please try again.',
                    confirmButtonColor: '#ef4444'
                });
            }
        } catch (error) {
            console.error('Clock out failed:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An unexpected error occurred. Please try again.',
                confirmButtonColor: '#ef4444'
            });
        } finally {
            setButtonLoading(clockOutBtn, false);
            fetchStatus(); // Refresh to update button states
        }
    }

    // Event listeners
    clockInBtn.addEventListener('click', clockIn);
    clockOutBtn.addEventListener('click', clockOut);

    // Initial status fetch
    fetchStatus();
})();
</script>
