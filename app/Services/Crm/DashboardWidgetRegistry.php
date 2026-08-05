<?php

namespace App\Services\Crm;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Resolves which dashboard widgets a given user may see, from the declarative list
 * in config('heritage_crm.dashboard.widgets').
 */
class DashboardWidgetRegistry
{
    public function __construct(private readonly CrmModulePermissionService $permissions) {
    }

    /**
     * @return Collection<string, array<string, mixed>>
     */
    public function all(): Collection {
        return collect(config('heritage_crm.dashboard.widgets', []))
            ->map(fn (array $widget, string $key) => $widget + ['key' => $key]);
    }

    /**
     * Widgets this user may see, in declaration order.
     *
     * @param  array<string, bool>  $settings  Dashboard flags a widget can require.
     * @return Collection<int, array<string, mixed>>
     */
    public function visibleFor(User $user, array $settings = []): Collection {
        return $this->all()
            ->filter(fn (array $widget) => $this->isVisible($widget, $user, $settings))
            ->values();
    }

    public function isVisibleTo(string $key, User $user, array $settings = []): bool {
        $widget = $this->all()->get($key);

        return $widget !== null && $this->isVisible($widget, $user, $settings);
    }

    /**
     * Group the visible widgets into rows for rendering: a 'full' widget owns its row,
     * while consecutive 'half' widgets pair up. Filtering happens first, so a hidden
     * widget closes the gap rather than leaving a half-empty row behind it.
     *
     * @return array<int, array<int, array<string, mixed>>>
     */
    public function rowsFor(User $user, array $settings = []): array {
        $rows = [];
        $pending = [];

        foreach ($this->visibleFor($user, $settings) as $widget) {
            if (($widget['size'] ?? 'full') === 'full') {
                if ($pending !== []) {
                    $rows[] = $pending;
                    $pending = [];
                }

                $rows[] = [$widget];

                continue;
            }

            $pending[] = $widget;

            if (count($pending) === 2) {
                $rows[] = $pending;
                $pending = [];
            }
        }

        if ($pending !== []) {
            $rows[] = $pending;
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $widget
     * @param  array<string, bool>  $settings
     */
    private function isVisible(array $widget, User $user, array $settings): bool {
        // Switched off in config: still declared and ready, just not on the page.
        if (($widget['enabled'] ?? true) === false) {
            return false;
        }

        $module = $widget['module'] ?? null;

        if (is_string($module) && ! $this->permissions->hasAccess($user, $module, $widget['level'] ?? 'view')) {
            return false;
        }

        $roles = $widget['roles'] ?? null;

        if (is_array($roles) && ! in_array($user->role, $roles, true)) {
            return false;
        }

        $setting = $widget['setting'] ?? null;

        if (is_string($setting) && ! ($settings[$setting] ?? false)) {
            return false;
        }

        return true;
    }
}
