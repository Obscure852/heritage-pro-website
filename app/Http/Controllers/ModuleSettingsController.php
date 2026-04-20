<?php

namespace App\Http\Controllers;

use App\Models\StaffAttendance\StaffAttendanceSetting;
use App\Services\ModuleVisibilityService;
use Illuminate\Http\Request;

class ModuleSettingsController extends Controller
{
    protected ModuleVisibilityService $moduleService;

    public function __construct(ModuleVisibilityService $moduleService)
    {
        $this->middleware(['auth', 'can:access-setup']);
        $this->moduleService = $moduleService;
    }

    public function index()
    {
        $modules = $this->moduleService->getModuleVisibilityStatus();

        return view('settings.module-settings', [
            'modules' => $modules,
        ]);
    }

    public function update(Request $request)
    {
        $allModules = $this->moduleService->getAllModules();
        $moduleKeys = array_keys($allModules);

        foreach ($moduleKeys as $moduleKey) {
            $visible = $request->has("modules.{$moduleKey}");
            $this->moduleService->updateModuleVisibility($moduleKey, $visible);
        }

        // If staff attendance module was disabled, also turn off self clock-in
        if (!$request->has('modules.staff_attendance')) {
            StaffAttendanceSetting::updateOrCreate(
                ['key' => 'self_clock_in_enabled'],
                ['value' => ['enabled' => false]]
            );
        }

        $this->moduleService->clearCache();

        return redirect()->route('setup.module-settings')
            ->with('message', 'Module visibility settings updated successfully.');
    }
}
