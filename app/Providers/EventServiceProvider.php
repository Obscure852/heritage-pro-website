<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogSuccessfulLogout;
use App\Events\Leave\LeaveRequestSubmitted;
use App\Events\Leave\LeaveRequestApproved;
use App\Events\Leave\LeaveRequestCancelled;
use App\Events\Leave\LeaveRequestRejected;
use App\Listeners\Leave\SendLeaveSubmittedNotification;
use App\Listeners\Leave\SendLeaveApprovedNotification;
use App\Listeners\Leave\SendLeaveRejectedNotification;
use App\Listeners\Leave\SendManagerNewRequestNotification;
use App\Listeners\StaffAttendance\SyncLeaveToAttendance;

class EventServiceProvider extends ServiceProvider {
    protected $listen = [

        Login::class => [
            LogSuccessfulLogin::class,
        ],

        Logout::class => [
            LogSuccessfulLogout::class,
        ],

        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Leave events
        LeaveRequestSubmitted::class => [
            SendLeaveSubmittedNotification::class,
            SendManagerNewRequestNotification::class,
        ],

        LeaveRequestApproved::class => [
            SendLeaveApprovedNotification::class,
            SyncLeaveToAttendance::class,
        ],

        LeaveRequestCancelled::class => [
            SyncLeaveToAttendance::class,
        ],

        LeaveRequestRejected::class => [
            SendLeaveRejectedNotification::class,
        ],
    ];

    public function boot(){
        parent::boot();
    }
}
