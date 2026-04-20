<?php

namespace App\Services\Invigilation;

use App\Models\Invigilation\InvigilationSeries;
use App\Models\User;
use App\Services\Messaging\StaffMessagingFeatureService;
use App\Services\Messaging\StaffMessagingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class InvigilationPublishNotificationService
{
    public function __construct(
        protected StaffMessagingService $staffMessagingService,
        protected StaffMessagingFeatureService $staffMessagingFeatureService,
    ) {
    }

    public function notifyAssignedTeachers(InvigilationSeries $series, ?User $actor): array
    {
        $summary = [
            'recipient_count' => 0,
            'sent_count' => 0,
            'skipped_count' => 0,
            'failed_count' => 0,
            'enabled' => false,
        ];

        if (!$actor || !$this->messagingInfrastructureAvailable()) {
            return $summary;
        }

        $summary['enabled'] = $this->staffMessagingFeatureService->directMessagesEnabled();

        if (!$summary['enabled']) {
            return $summary;
        }

        $series->loadMissing([
            'term',
            'sessions.gradeSubject.subject',
            'sessions.gradeSubject.grade',
            'sessions.rooms.venue',
            'sessions.rooms.assignments.user',
        ]);

        $manifest = $this->buildTeacherDutyManifest($series);
        $summary['recipient_count'] = $manifest->count();

        foreach ($manifest as $entry) {
            /** @var User|null $recipient */
            $recipient = $entry['teacher'] ?? null;
            $duties = $entry['duties'] ?? [];

            if (!$recipient || empty($duties)) {
                $summary['skipped_count']++;
                continue;
            }

            if ((int) $recipient->id === (int) $actor->id) {
                $summary['skipped_count']++;
                continue;
            }

            try {
                $this->staffMessagingService->startConversation(
                    $actor,
                    $recipient,
                    $this->buildDirectMessageBody($series, $actor, $duties)
                );

                $summary['sent_count']++;
            } catch (\Throwable $exception) {
                $summary['failed_count']++;

                Log::error('Failed to send invigilation publish direct message.', [
                    'invigilation_series_id' => $series->id,
                    'actor_id' => $actor->id,
                    'teacher_id' => $recipient->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        Log::info('Processed invigilation publish direct messages.', [
            'invigilation_series_id' => $series->id,
            'actor_id' => $actor->id,
            'recipient_count' => $summary['recipient_count'],
            'sent_count' => $summary['sent_count'],
            'skipped_count' => $summary['skipped_count'],
            'failed_count' => $summary['failed_count'],
        ]);

        return $summary;
    }

    protected function messagingInfrastructureAvailable(): bool
    {
        return Schema::hasTable('s_m_s_api_settings')
            && Schema::hasTable('staff_direct_conversations')
            && Schema::hasTable('staff_direct_messages');
    }

    protected function buildTeacherDutyManifest(InvigilationSeries $series): Collection
    {
        return $series->sessions
            ->flatMap(function ($session) {
                return $session->rooms->flatMap(function ($room) use ($session) {
                    return $room->assignments
                        ->filter(fn ($assignment) => (int) ($assignment->user_id ?? 0) > 0 && $assignment->user)
                        ->map(function ($assignment) use ($session, $room) {
                            return [
                                'teacher_id' => (int) $assignment->user_id,
                                'teacher' => $assignment->user,
                                'duty' => [
                                    'date' => $session->exam_date?->format('d M Y') ?? '',
                                    'start_time' => substr((string) $session->start_time, 0, 5),
                                    'end_time' => substr((string) $session->end_time, 0, 5),
                                    'session' => $session->display_name,
                                    'grade' => $session->gradeSubject?->grade?->name ?? 'No grade',
                                    'venue' => $room->venue?->name ?? 'No venue',
                                    'group' => $room->resolved_group_label,
                                ],
                            ];
                        });
                });
            })
            ->groupBy('teacher_id')
            ->map(function (Collection $items) {
                $teacher = $items->first()['teacher'] ?? null;

                $duties = $items
                    ->pluck('duty')
                    ->sortBy([
                        ['date', 'asc'],
                        ['start_time', 'asc'],
                        ['venue', 'asc'],
                    ])
                    ->values()
                    ->all();

                return [
                    'teacher' => $teacher,
                    'duties' => $duties,
                ];
            })
            ->values();
    }

    protected function buildDirectMessageBody(InvigilationSeries $series, User $actor, array $duties): string
    {
        $lines = [
            sprintf(
                '%s published the invigilation roster "%s" for Term %s %s.',
                $actor->full_name ?: 'A staff member',
                $series->name,
                $series->term?->term ?? '—',
                $series->term?->year ?? '—'
            ),
            '',
            'Your assigned duties:',
        ];

        foreach ($duties as $duty) {
            $lines[] = sprintf(
                '- %s | %s - %s | %s | %s | %s | %s',
                $duty['date'] ?: 'No date',
                $duty['start_time'] ?: '--:--',
                $duty['end_time'] ?: '--:--',
                $duty['session'] ?: 'Exam session',
                $duty['grade'] ?: 'No grade',
                $duty['venue'] ?: 'No venue',
                $duty['group'] ?: 'No group'
            );
        }

        $lines[] = '';
        $lines[] = 'Open the published teacher roster in the app:';
        $lines[] = route('invigilation.view.teacher-roster', ['series_id' => $series->id]);

        return implode("\n", $lines);
    }
}
