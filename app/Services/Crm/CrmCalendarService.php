<?php

namespace App\Services\Crm;

use App\Models\Contact;
use App\Models\CrmCalendar;
use App\Models\CrmCalendarEvent;
use App\Models\CrmCalendarMembership;
use App\Models\CrmRequest;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CrmCalendarService
{
    public function visibleCalendarsFor(User $user): Collection
    {
        if ($user->canManageOperationalRecords()) {
            $this->ensurePersonalCalendars(
                User::query()
                    ->where('active', true)
                    ->whereIn('role', array_keys(config('heritage_crm.roles', [])))
                    ->orderBy('email')
                    ->get(['id', 'firstname', 'lastname', 'username', 'email', 'role', 'active'])
            );
        } else {
            $this->ensurePersonalCalendar($user);
        }

        $calendars = CrmCalendar::query()
            ->with([
                'owner:id,firstname,lastname,username,email,role',
                'memberships.user:id,firstname,lastname,username,email,role',
            ])
            ->where('is_active', true)
            ->where(function (Builder $query) use ($user) {
                if ($user->canManageOperationalRecords()) {
                    $query->where('type', 'personal')
                        ->orWhere('type', 'shared');

                    return;
                }

                $query->where(function (Builder $personalQuery) use ($user) {
                    $personalQuery->where('type', 'personal')
                        ->where('owner_id', $user->id);
                })->orWhere(function (Builder $sharedQuery) use ($user) {
                    $sharedQuery->where('type', 'shared')
                        ->where(function (Builder $memberQuery) use ($user) {
                            $memberQuery->where('owner_id', $user->id)
                                ->orWhere('created_by_id', $user->id)
                                ->orWhereHas('memberships', function (Builder $membershipQuery) use ($user) {
                                    $membershipQuery->where('user_id', $user->id);
                                });
                        });
                });
            })
            ->orderByRaw("case when type = 'personal' then 0 else 1 end")
            ->orderBy('name')
            ->get();

        return $calendars->map(function (CrmCalendar $calendar) use ($user) {
            $permission = $this->permissionFor($user, $calendar);

            $calendar->setAttribute('viewer_permission', $permission);
            $calendar->setAttribute('can_edit', in_array($permission, ['edit', 'manage'], true));
            $calendar->setAttribute('can_manage', $permission === 'manage');
            $calendar->setAttribute('owner_label', $calendar->owner?->name ?: 'Shared');
            $calendar->setAttribute('group_label', $calendar->owner_id === $user->id || $permission === 'manage' ? 'my' : 'other');

            return $calendar;
        });
    }

    public function visibleEventsForRange(
        User $user,
        CarbonInterface $rangeStart,
        CarbonInterface $rangeEnd,
        array $calendarIds = []
    ): Collection {
        $visibleCalendars = $this->visibleCalendarsFor($user);
        $allowedCalendarIds = $visibleCalendars->pluck('id')->map(fn ($id) => (int) $id)->all();

        $selectedCalendarIds = collect($calendarIds)
            ->filter(fn ($calendarId) => in_array((int) $calendarId, $allowedCalendarIds, true))
            ->map(fn ($calendarId) => (int) $calendarId)
            ->values()
            ->all();

        if ($selectedCalendarIds === []) {
            $selectedCalendarIds = $allowedCalendarIds;
        }

        return CrmCalendarEvent::query()
            ->with([
                'calendar.owner:id,firstname,lastname,username,email,role',
                'owner:id,firstname,lastname,username,email,role',
                'createdBy:id,firstname,lastname,username,email,role',
                'lead:id,company_name',
                'customer:id,company_name',
                'contact:id,name,email,phone,lead_id,customer_id',
                'request:id,title,owner_id,lead_id,customer_id',
                'attendees.user:id,firstname,lastname,username,email,role',
                'attendees.contact:id,name,email',
            ])
            ->whereIn('calendar_id', $selectedCalendarIds)
            ->where(function (Builder $query) use ($rangeStart, $rangeEnd) {
                $query->whereBetween('starts_at', [$rangeStart, $rangeEnd])
                    ->orWhereBetween('ends_at', [$rangeStart, $rangeEnd])
                    ->orWhere(function (Builder $spanQuery) use ($rangeStart, $rangeEnd) {
                        $spanQuery->where('starts_at', '<=', $rangeStart)
                            ->where('ends_at', '>=', $rangeEnd);
                    });
            })
            ->orderBy('starts_at')
            ->get();
    }

    public function permissionFor(User $user, CrmCalendar $calendar): string
    {
        if ($user->canManageOperationalRecords()) {
            return 'manage';
        }

        if ((int) $calendar->owner_id === (int) $user->id || (int) $calendar->created_by_id === (int) $user->id) {
            return 'manage';
        }

        if ($calendar->type === 'personal') {
            return 'view';
        }

        $membership = $calendar->relationLoaded('memberships')
            ? $calendar->memberships->firstWhere('user_id', $user->id)
            : $calendar->memberships()->where('user_id', $user->id)->first();

        return $membership?->permission ?? 'view';
    }

    public function canViewCalendar(User $user, CrmCalendar $calendar): bool
    {
        return in_array($calendar->id, $this->visibleCalendarsFor($user)->pluck('id')->all(), true);
    }

    public function canEditCalendar(User $user, CrmCalendar $calendar): bool
    {
        return in_array($this->permissionFor($user, $calendar), ['edit', 'manage'], true);
    }

    public function canEditEvent(User $user, CrmCalendarEvent $event): bool
    {
        $event->loadMissing('calendar.memberships');

        if ($this->canEditCalendar($user, $event->calendar)) {
            return true;
        }

        return (int) $event->owner_id === (int) $user->id || (int) $event->created_by_id === (int) $user->id;
    }

    public function canViewEventSensitiveDetails(User $user, CrmCalendarEvent $event): bool
    {
        if ($event->visibility === 'standard') {
            return true;
        }

        $event->loadMissing('calendar.memberships');

        if ($user->canManageOperationalRecords()) {
            return true;
        }

        if ((int) $event->owner_id === (int) $user->id || (int) $event->created_by_id === (int) $user->id) {
            return true;
        }

        return (int) $event->calendar->owner_id === (int) $user->id || $this->canEditCalendar($user, $event->calendar);
    }

    public function ensurePersonalCalendars(iterable $users): void
    {
        foreach ($users as $user) {
            if (! $user instanceof User) {
                continue;
            }

            $this->ensurePersonalCalendar($user);
        }
    }

    public function ensurePersonalCalendar(User $user): CrmCalendar
    {
        $calendar = CrmCalendar::query()->firstOrCreate(
            [
                'owner_id' => $user->id,
                'type' => 'personal',
            ],
            [
                'created_by_id' => $user->id,
                'updated_by_id' => $user->id,
                'name' => trim($user->name . ' Calendar'),
                'slug' => 'personal-' . $user->id,
                'color' => $this->defaultColorFor($user),
                'description' => 'Personal CRM schedule for ' . $user->name . '.',
                'is_active' => true,
                'is_default' => true,
            ]
        );

        CrmCalendarMembership::query()->updateOrCreate(
            [
                'calendar_id' => $calendar->id,
                'user_id' => $user->id,
            ],
            [
                'permission' => 'manage',
                'is_visible' => true,
            ]
        );

        return $calendar;
    }

    public function createSharedCalendar(User $creator, array $attributes, array $memberIds = []): CrmCalendar
    {
        $calendar = DB::transaction(function () use ($creator, $attributes, $memberIds) {
            $calendar = CrmCalendar::query()->create([
                'owner_id' => $creator->id,
                'created_by_id' => $creator->id,
                'updated_by_id' => $creator->id,
                'name' => $attributes['name'],
                'slug' => Str::slug($attributes['name']) . '-' . Str::lower(Str::random(6)),
                'type' => 'shared',
                'color' => $attributes['color'] ?? $this->defaultColorFor($creator),
                'description' => $attributes['description'] ?? null,
                'is_active' => true,
                'is_default' => false,
            ]);

            $uniqueMemberIds = collect($memberIds)
                ->map(fn ($memberId) => (int) $memberId)
                ->push($creator->id)
                ->unique()
                ->values();

            foreach ($uniqueMemberIds as $memberId) {
                CrmCalendarMembership::query()->create([
                    'calendar_id' => $calendar->id,
                    'user_id' => $memberId,
                    'permission' => $memberId === (int) $creator->id ? 'manage' : 'edit',
                    'is_visible' => true,
                ]);
            }

            return $calendar;
        });

        return $calendar->load([
            'owner:id,firstname,lastname,username,email,role',
            'memberships.user:id,firstname,lastname,username,email,role',
        ]);
    }

    public function syncAttendees(CrmCalendarEvent $event, array $attendeeUserIds = [], ?Contact $linkedContact = null): void
    {
        $attendeeUserIds = collect($attendeeUserIds)
            ->map(fn ($userId) => (int) $userId)
            ->filter()
            ->unique()
            ->values();

        $event->attendees()->delete();

        foreach ($attendeeUserIds as $userId) {
            $user = User::query()->find($userId);

            if ($user === null) {
                continue;
            }

            $event->attendees()->create([
                'user_id' => $user->id,
                'display_name' => $user->name,
                'email' => $user->email,
                'role' => 'required',
                'response_status' => 'pending',
            ]);
        }

        if ($linkedContact !== null) {
            $event->attendees()->create([
                'contact_id' => $linkedContact->id,
                'display_name' => $linkedContact->name,
                'email' => $linkedContact->email,
                'role' => 'required',
                'response_status' => 'pending',
            ]);
        }
    }

    public function normalizeEventPayload(User $actor, array $data): array
    {
        $timezone = $data['timezone'] ?? config('app.timezone');
        $startsAt = Carbon::parse($data['starts_at'], $timezone);
        $endsAt = Carbon::parse($data['ends_at'], $timezone);

        if ((bool) ($data['all_day'] ?? false)) {
            $startsAt = $startsAt->copy()->startOfDay();
            $endsAt = $endsAt->copy()->endOfDay();
        }

        return [
            'calendar_id' => (int) $data['calendar_id'],
            'owner_id' => filled($data['owner_id'] ?? null)
                ? (int) $data['owner_id']
                : $actor->id,
            'lead_id' => $this->nullableInt($data['lead_id'] ?? null),
            'customer_id' => $this->nullableInt($data['customer_id'] ?? null),
            'contact_id' => $this->nullableInt($data['contact_id'] ?? null),
            'request_id' => $this->nullableInt($data['request_id'] ?? null),
            'title' => trim((string) $data['title']),
            'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
            'location' => filled($data['location'] ?? null) ? trim((string) $data['location']) : null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'all_day' => (bool) ($data['all_day'] ?? false),
            'status' => $data['status'],
            'visibility' => $data['visibility'],
            'timezone' => $timezone,
            'reminders' => collect($data['reminder_minutes'] ?? [])
                ->map(fn ($minutes) => (int) $minutes)
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all(),
        ];
    }

    public function resolveLinkedRecords(array $data): array
    {
        return [
            'lead' => isset($data['lead_id']) && $data['lead_id'] ? Lead::query()->findOrFail((int) $data['lead_id']) : null,
            'customer' => isset($data['customer_id']) && $data['customer_id'] ? Customer::query()->findOrFail((int) $data['customer_id']) : null,
            'contact' => isset($data['contact_id']) && $data['contact_id'] ? Contact::query()->findOrFail((int) $data['contact_id']) : null,
            'request' => isset($data['request_id']) && $data['request_id'] ? CrmRequest::query()->findOrFail((int) $data['request_id']) : null,
        ];
    }

    public function defaultColorFor(User $user): string
    {
        $palette = array_values(config('heritage_crm.calendar_default_colors', ['#5156be']));

        return $palette[$user->id % count($palette)];
    }

    private function nullableInt(mixed $value): ?int
    {
        return blank($value) ? null : (int) $value;
    }
}
