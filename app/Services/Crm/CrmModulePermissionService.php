<?php

namespace App\Services\Crm;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CrmModulePermissionService
{
    private const LEVEL_PRIORITY = [
        'view' => 1,
        'edit' => 2,
        'admin' => 3,
    ];

    public function modules(): Collection
    {
        return collect(config('heritage_crm.modules', []))
            ->map(fn (array $module, string $key) => $module + ['key' => $key]);
    }

    public function permissionChoices(): array
    {
        return config('heritage_crm.permission_levels', []);
    }

    public function module(string $moduleKey): ?array
    {
        return $this->modules()->firstWhere('key', $moduleKey);
    }

    public function defaultPermissionLevelForRole(string $role, string $moduleKey): ?string
    {
        $module = $this->module($moduleKey);

        return $module['default_permissions'][$role] ?? null;
    }

    public function effectivePermissionLevel(User $user, string $moduleKey): ?string
    {
        if (! $this->storedPermissionsAvailable()) {
            return $this->defaultPermissionLevelForRole($user->role, $moduleKey);
        }

        $relationValue = $user->relationLoaded('modulePermissions')
            ? $user->modulePermissions->firstWhere('module_key', $moduleKey)?->permission_level
            : null;

        if (is_string($relationValue) && $relationValue !== '') {
            return $relationValue;
        }

        $storedValue = $user->modulePermissions()
            ->where('module_key', $moduleKey)
            ->value('permission_level');

        if (is_string($storedValue) && $storedValue !== '') {
            return $storedValue;
        }

        return $this->defaultPermissionLevelForRole($user->role, $moduleKey);
    }

    public function hasAccess(User $user, string $moduleKey, string $requiredLevel = 'view'): bool
    {
        $actualLevel = $this->effectivePermissionLevel($user, $moduleKey);

        if ($actualLevel === null) {
            return false;
        }

        return ($this->priorityFor($actualLevel) >= $this->priorityFor($requiredLevel));
    }

    public function moduleForRoute(?string $routeName): ?array
    {
        if (! is_string($routeName) || $routeName === '') {
            return null;
        }

        return $this->modules()->first(function (array $module) use ($routeName) {
            foreach ($module['match'] ?? [$module['route']] as $pattern) {
                if (Str::is($pattern, $routeName)) {
                    return true;
                }
            }

            return false;
        });
    }

    public function requiredPermissionLevelForRoute(?string $routeName, string $method = 'GET'): ?string
    {
        $module = $this->moduleForRoute($routeName);

        if ($module === null) {
            return null;
        }

        foreach ($module['route_permissions'] ?? [] as $routePermission) {
            foreach ($routePermission['match'] ?? [] as $pattern) {
                if (Str::is($pattern, (string) $routeName)) {
                    return $routePermission['level'] ?? 'view';
                }
            }
        }

        return $this->inferredPermissionLevel($routeName, $method);
    }

    public function inferredPermissionLevel(?string $routeName, string $method = 'GET'): string
    {
        $httpMethod = strtoupper($method);

        if ($httpMethod === 'DELETE') {
            return 'admin';
        }

        if (in_array($httpMethod, ['POST', 'PUT', 'PATCH'], true)) {
            return 'edit';
        }

        if (is_string($routeName) && Str::is([
            '*.create',
            '*.edit',
            '*.share.create',
            '*.sales.create',
            '*.support.create',
            '*.edit-currency',
        ], $routeName)) {
            return 'edit';
        }

        return 'view';
    }

    public function syncPermissions(User $user, array $levels): void
    {
        if (! $this->storedPermissionsAvailable()) {
            return;
        }

        $validLevels = array_keys($this->permissionChoices());
        $rows = [];
        $moduleKeys = [];

        foreach ($this->modules() as $module) {
            $moduleKey = $module['key'];
            $level = $levels[$moduleKey] ?? null;

            $moduleKeys[] = $moduleKey;

            if (! in_array($level, $validLevels, true)) {
                continue;
            }

            $rows[] = [
                'user_id' => $user->id,
                'module_key' => $moduleKey,
                'permission_level' => $level,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $user->modulePermissions()->whereIn('module_key', $moduleKeys)->delete();

        if ($rows !== []) {
            $user->modulePermissions()->upsert(
                $rows,
                ['user_id', 'module_key'],
                ['permission_level', 'updated_at']
            );
        }

        $user->unsetRelation('modulePermissions');
    }

    public function syncDefaultsForRole(User $user, ?string $role = null): void
    {
        $role = $role ?: $user->role;
        $levels = [];

        foreach ($this->modules() as $module) {
            $defaultLevel = $module['default_permissions'][$role] ?? null;

            if (is_string($defaultLevel) && $defaultLevel !== '') {
                $levels[$module['key']] = $defaultLevel;
            }
        }

        $this->syncPermissions($user, $levels);
    }

    private function priorityFor(string $level): int
    {
        return self::LEVEL_PRIORITY[$level] ?? 0;
    }

    private function storedPermissionsAvailable(): bool
    {
        static $available;

        if ($available !== null) {
            return $available;
        }

        return $available = Schema::hasTable('crm_user_module_permissions');
    }
}
