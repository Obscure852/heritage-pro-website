<?php

namespace App\Services\Crm;

use App\Models\User;
use Illuminate\Support\Collection;

class CrmModuleRegistry
{
    public function modulesFor(User $user): array
    {
        return collect(config('heritage_crm.modules', []))
            ->map(function (array $module, string $key) {
                $module['key'] = $key;

                return $module;
            })
            ->filter(fn (array $module) => in_array($user->role, $module['roles'] ?? [], true))
            ->values()
            ->all();
    }

    public function launcherModulesFor(User $user): array
    {
        return collect($this->modulesFor($user))
            ->map(fn (array $module) => [
                'key' => $module['key'],
                'label' => $module['label'],
                'caption' => $module['caption'] ?? null,
                'icon' => $module['icon'] ?? 'bx bx-grid-alt',
                'url' => route($module['route']),
            ])
            ->values()
            ->all();
    }

    public function sidebarModulesFor(User $user): array
    {
        return $this->modulesFor($user);
    }

    public function groupedSidebarModulesFor(User $user): array
    {
        $modules = collect($this->sidebarModulesFor($user));

        return [
            'workspace' => $modules
                ->reject(fn (array $module) => in_array($module['key'], ['users', 'settings'], true))
                ->values()
                ->all(),
            'administration' => $modules
                ->filter(fn (array $module) => in_array($module['key'], ['users', 'settings'], true))
                ->values()
                ->all(),
        ];
    }

    public function matchPatterns(array $module): array
    {
        return $module['match'] ?? [$module['route']];
    }

    public function childrenFor(array $module): Collection
    {
        return collect($module['children'] ?? []);
    }
}
