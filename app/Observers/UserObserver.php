<?php

namespace App\Observers;

use App\Models\User;
use App\Services\Documents\FolderService;
use App\Services\Pdp\PdpRolloutService;
use Illuminate\Support\Facades\Schema;

class UserObserver {
    protected FolderService $folderService;
    protected PdpRolloutService $rolloutService;

    public function __construct(FolderService $folderService, PdpRolloutService $rolloutService) {
        $this->folderService = $folderService;
        $this->rolloutService = $rolloutService;
    }

    /**
     * Handle the User "created" event.
     *
     * Auto-creates a personal "My Documents" root folder for new staff users (FLD-09).
     */
    public function created(User $user): void {
        if ($user->status === 'Current' && Schema::hasTable('document_folders')) {
            $this->folderService->ensurePersonalRootFolder($user);
        }

        if (Schema::hasTable('pdp_rollouts')) {
            $this->rolloutService->provisionUserIfEligible($user);
        }
    }

    public function updated(User $user): void
    {
        if (
            $user->status === 'Current'
            && $user->wasChanged('status')
            && Schema::hasTable('document_folders')
        ) {
            $this->folderService->ensurePersonalRootFolder($user);
        }

        if (!Schema::hasTable('pdp_rollouts')) {
            return;
        }

        if (!$user->wasChanged(['status', 'active', 'reporting_to'])) {
            return;
        }

        $this->rolloutService->provisionUserIfEligible($user);
    }
}
