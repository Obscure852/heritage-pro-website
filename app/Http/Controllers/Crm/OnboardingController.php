<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\CrmOnboardingIdentityRequest;
use App\Http\Requests\Crm\CrmOnboardingWorkRequest;
use App\Services\Crm\CrmUserProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class OnboardingController extends CrmController
{
    public function __construct(
        private readonly CrmUserProfileService $profileService
    ) {
    }

    public function editProfile(): View|RedirectResponse
    {
        $user = $this->crmUser();

        if (! $user->requiresCrmOnboarding()) {
            return redirect()->route('crm.dashboard');
        }

        if ((int) ($user->crm_onboarding_step ?? 1) > 1) {
            return redirect()->route('crm.onboarding.work');
        }

        return view('crm.onboarding.profile', [
            'user' => $user,
            'genders' => array_intersect_key(
                config('heritage_crm.user_genders', []),
                array_flip(['male', 'female'])
            ),
            'canSkipProfile' => $this->profileService->canSkipIdentity($user),
        ]);
    }

    public function updateProfile(CrmOnboardingIdentityRequest $request): RedirectResponse
    {
        $user = $this->crmUser();

        DB::transaction(function () use ($request, $user) {
            $this->profileService->syncIdentity($user, $request->validated());
            $user->advanceCrmOnboardingToWork();
        });

        return redirect()
            ->route('crm.onboarding.work')
            ->with('crm_success', 'Identity details saved. Continue with your work profile.');
    }

    public function editWork(): View|RedirectResponse
    {
        $user = $this->crmUser();

        if (! $user->requiresCrmOnboarding()) {
            return redirect()->route('crm.dashboard');
        }

        if ((int) ($user->crm_onboarding_step ?? 1) < 2) {
            return redirect()->route('crm.onboarding.profile');
        }

        return view('crm.onboarding.work', [
            'user' => $user,
            'departments' => $this->profileService->departmentOptions(),
            'positions' => $this->profileService->positionOptions(),
            'reportingUsers' => $this->profileService->reportingUserOptions($user),
            'employmentStatuses' => config('heritage_crm.user_employment_statuses'),
            'canSkipWork' => $this->profileService->canSkipWork($user),
        ]);
    }

    public function skipProfile(): RedirectResponse
    {
        $user = $this->crmUser();

        abort_unless($user->requiresCrmOnboarding(), 403);
        abort_unless((int) ($user->crm_onboarding_step ?? 1) < 2, 403);
        abort_unless($this->profileService->canSkipIdentity($user), 403);

        $user->advanceCrmOnboardingToWork();

        return redirect()
            ->route('crm.onboarding.work')
            ->with('crm_success', 'Existing identity details detected. Continue with your work profile.');
    }

    public function updateWork(CrmOnboardingWorkRequest $request): RedirectResponse
    {
        $user = $this->crmUser();

        DB::transaction(function () use ($request, $user) {
            $this->profileService->syncWork($user, $request->validated());
            $user->completeCrmOnboarding();
        });

        return redirect()
            ->intended(route('crm.dashboard'))
            ->with('crm_success', 'Profile setup complete. Welcome to the CRM.');
    }

    public function skipWork(): RedirectResponse
    {
        $user = $this->crmUser();

        abort_unless($user->requiresCrmOnboarding(), 403);
        abort_unless((int) ($user->crm_onboarding_step ?? 1) >= 2, 403);
        abort_unless($this->profileService->canSkipWork($user), 403);

        $user->completeCrmOnboarding();

        return redirect()
            ->intended(route('crm.dashboard'))
            ->with('crm_success', 'Existing work details detected. CRM access unlocked.');
    }
}
