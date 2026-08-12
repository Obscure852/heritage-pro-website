<?php

namespace App\Services\ClientSetup;

use App\Models\CrmClientSetupSubmission;
use RuntimeException;

class ClientSetupWizardService
{
    public function stages(): array
    {
        return config('client_setup.stages', []);
    }

    public function stage(string $stageKey): array
    {
        foreach ($this->stages() as $stage) {
            if ($stage['key'] === $stageKey) {
                return $stage;
            }
        }

        throw new RuntimeException('Unknown client setup stage.');
    }

    public function navigation(CrmClientSetupSubmission $submission, string $currentStage): array
    {
        $progress = $submission->stageProgress->keyBy('stage_key');
        $requiredIncomplete = false;
        $navigation = [];

        foreach ($this->stages() as $stage) {
            $savedProgress = $progress->get($stage['key']);
            $status = $savedProgress?->status ?: 'not_started';
            $complete = $status === 'complete';
            $locked = ! $stage['optional'] && $requiredIncomplete && ! $complete;

            $navigation[] = array_merge($stage, [
                'status' => $status,
                'complete' => $complete,
                'locked' => $locked,
                'current' => $stage['key'] === $currentStage,
                'state' => $stage['key'] === $currentStage
                    ? 'current'
                    : ($locked ? 'locked' : ($complete ? 'complete' : $status)),
                'last_saved_at' => $savedProgress?->last_saved_at,
            ]);

            if ($stage['required_for_academic'] && ! $complete) {
                $requiredIncomplete = true;
            }
        }

        return $navigation;
    }

    public function progress(CrmClientSetupSubmission $submission): array
    {
        $requiredStages = array_values(array_filter(
            $this->stages(),
            static fn (array $stage): bool => $stage['required_for_academic']
        ));
        $completedKeys = array_flip($submission->completed_stages ?? []);
        $completed = count(array_filter(
            $requiredStages,
            static fn (array $stage): bool => isset($completedKeys[$stage['key']])
        ));
        $total = count($requiredStages);

        return [
            'completed' => $completed,
            'total' => $total,
            'percentage' => $total === 0 ? 0 : (int) round(($completed / $total) * 100),
        ];
    }

    public function firstIncompleteRequiredStage(CrmClientSetupSubmission $submission): string
    {
        $completedKeys = array_flip($submission->completed_stages ?? []);

        foreach ($this->stages() as $stage) {
            if ($stage['required_for_academic'] && ! isset($completedKeys[$stage['key']])) {
                return $stage['key'];
            }
        }

        return $this->stages()[0]['key'];
    }

    public function nextStage(CrmClientSetupSubmission $submission, string $currentStage): ?string
    {
        $navigation = $this->navigation($submission, $currentStage);
        $currentIndex = array_search($currentStage, array_column($navigation, 'key'), true);

        if ($currentIndex === false) {
            return null;
        }

        foreach (array_slice($navigation, $currentIndex + 1) as $stage) {
            if (! $stage['locked']) {
                return $stage['key'];
            }
        }

        return null;
    }

    public function isAcademicStage(string $stageKey): bool
    {
        return (bool) ($this->stage($stageKey)['required_for_academic'] ?? false);
    }
}
