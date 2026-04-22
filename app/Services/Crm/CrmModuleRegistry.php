<?php

namespace App\Services\Crm;

use App\Models\User;
use Illuminate\Support\Collection;

class CrmModuleRegistry
{
    public function __construct(
        private readonly CrmModulePermissionService $permissionService
    ) {
    }

    public function modulesFor(User $user): array
    {
        return $this->permissionService->modules()
            ->filter(fn (array $module) => $this->permissionService->hasAccess(
                $user,
                $module['key'],
                $module['minimum_permission'] ?? 'view'
            ))
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
                'url' => route($this->defaultRouteFor($user, $module)),
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

    public function childrenFor(User $user, array $module): Collection
    {
        return collect($module['children'] ?? [])
            ->filter(fn (array $child) => $this->itemIsVisibleToUser($user, $module['key'], $child))
            ->values();
    }

    public function defaultRouteFor(User $user, array $module): string
    {
        $firstVisibleChild = $this->childrenFor($user, $module)->first();

        return $firstVisibleChild['route'] ?? $module['route'];
    }

    private function itemIsVisibleToUser(User $user, string $moduleKey, array $item): bool
    {
        return $this->permissionService->hasAccess(
            $user,
            $moduleKey,
            $item['minimum_permission'] ?? 'view'
        );
    }
}
