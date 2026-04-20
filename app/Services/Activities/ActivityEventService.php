<?php

namespace App\Services\Activities;

use App\Models\Activities\Activity;
use App\Models\Activities\ActivityAuditLog;
use App\Models\Activities\ActivityEvent;
use App\Models\Activities\ActivityResult;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivityEventService
{
    public function createEvent(Activity $activity, array $data, User $actor): ActivityEvent
    {
        return DB::transaction(function () use ($activity, $data, $actor) {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);

            $this->assertEventChangesAllowed($lockedActivity);

            $event = $lockedActivity->events()->create([
                ...$this->extractEventPayload($lockedActivity, $data),
                'created_by' => $actor->id,
            ]);

            $this->recordAudit(
                $actor,
                $lockedActivity,
                'event_created',
                null,
                $this->eventSnapshot($event),
                'Activity event created.'
            );

            return $event->fresh(['creator'])->loadCount('results');
        });
    }

    public function updateEvent(Activity $activity, ActivityEvent $event, array $data, User $actor): ActivityEvent
    {
        return DB::transaction(function () use ($activity, $event, $data, $actor) {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);
            $lockedEvent = ActivityEvent::query()->lockForUpdate()->findOrFail($event->id);

            $this->assertEventChangesAllowed($lockedActivity);
            $this->assertEventBelongsToActivity($lockedActivity, $lockedEvent);
            $this->assertResultStateCompatibility($lockedEvent, $data);

            $before = $this->eventSnapshot($lockedEvent);

            $lockedEvent->fill($this->extractEventPayload($lockedActivity, $data));
            $lockedEvent->save();

            $this->recordAudit(
                $actor,
                $lockedActivity,
                'event_updated',
                $before,
                $this->eventSnapshot($lockedEvent->fresh()),
                'Activity event updated.'
            );

            return $lockedEvent->fresh(['creator'])->loadCount('results');
        });
    }

    private function extractEventPayload(Activity $activity, array $data): array
    {
        $startDateTime = Carbon::parse($data['start_date'] . ' ' . $data['start_time']);
        $endDateTime = !empty($data['end_time'])
            ? Carbon::parse(($data['end_date'] ?? $data['start_date']) . ' ' . $data['end_time'])
            : null;

        if ($endDateTime && $endDateTime->lt($startDateTime)) {
            throw ValidationException::withMessages([
                'end_time' => 'The event end must be after the event start.',
            ]);
        }

        if (!empty($data['house_linked']) && !$activity->allow_house_linkage) {
            throw ValidationException::withMessages([
                'house_linked' => 'Enable house-linked reporting on the activity before creating a house-linked event.',
            ]);
        }

        return [
            'title' => $data['title'],
            'event_type' => $data['event_type'],
            'description' => $data['description'] ?? null,
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'location' => $data['location'] ?? $activity->default_location,
            'opponent_or_partner_name' => $data['opponent_or_partner_name'] ?? null,
            'house_linked' => (bool) ($data['house_linked'] ?? false),
            'publish_to_calendar' => (bool) ($data['publish_to_calendar'] ?? false),
            'calendar_sync_status' => !empty($data['publish_to_calendar'])
                ? ActivityEvent::CALENDAR_HELD_LOCALLY
                : ActivityEvent::CALENDAR_NOT_PUBLISHED,
            'status' => $data['status'],
        ];
    }

    private function assertEventChangesAllowed(Activity $activity): void
    {
        if (in_array($activity->status, [Activity::STATUS_CLOSED, Activity::STATUS_ARCHIVED], true)) {
            throw ValidationException::withMessages([
                'activity' => 'Events cannot be changed once an activity is closed or archived.',
            ]);
        }
    }

    private function assertEventBelongsToActivity(Activity $activity, ActivityEvent $event): void
    {
        if ($event->activity_id !== $activity->id) {
            throw ValidationException::withMessages([
                'event' => 'The selected event does not belong to this activity.',
            ]);
        }
    }

    private function assertResultStateCompatibility(ActivityEvent $event, array $data): void
    {
        $hasResults = $event->results()->exists();
        $targetStatus = $data['status'] ?? $event->status;
        $targetHouseLinked = array_key_exists('house_linked', $data)
            ? (bool) $data['house_linked']
            : (bool) $event->house_linked;

        if ($hasResults && $targetStatus !== ActivityEvent::STATUS_COMPLETED) {
            throw ValidationException::withMessages([
                'status' => 'Keep the event marked as completed while recorded results still exist.',
            ]);
        }

        if (
            !$targetHouseLinked
            && $event->results()->where('participant_type', ActivityResult::PARTICIPANT_HOUSE)->exists()
        ) {
            throw ValidationException::withMessages([
                'house_linked' => 'Remove existing house results before turning off house-linked reporting for this event.',
            ]);
        }
    }

    private function eventSnapshot(ActivityEvent $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'event_type' => $event->event_type,
            'start_datetime' => optional($event->start_datetime)->toDateTimeString(),
            'end_datetime' => optional($event->end_datetime)->toDateTimeString(),
            'location' => $event->location,
            'house_linked' => (bool) $event->house_linked,
            'publish_to_calendar' => (bool) $event->publish_to_calendar,
            'calendar_sync_status' => $event->calendar_sync_status,
            'status' => $event->status,
        ];
    }

    private function recordAudit(User $user, Activity $activity, string $action, ?array $oldValues, ?array $newValues, string $notes): void
    {
        ActivityAuditLog::create([
            'user_id' => $user->id,
            'entity_type' => Activity::class,
            'entity_id' => $activity->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'notes' => $notes,
            'ip_address' => request()?->ip(),
            'user_agent' => (string) request()?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
