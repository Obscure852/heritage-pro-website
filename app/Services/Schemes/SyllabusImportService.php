<?php

namespace App\Services\Schemes;

use App\Helpers\SyllabusStructureHelper;
use App\Models\Schemes\Syllabus;
use App\Models\Schemes\SyllabusObjective;
use App\Models\Schemes\SyllabusTopic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SyllabusImportService
{
    private const TOPIC_MATCH_THRESHOLD = 30;
    private const OBJECTIVE_MATCH_THRESHOLD = 40;

    public function summarizeStructure(?array $structure): array
    {
        if (!is_array($structure)) {
            return ['topics' => 0, 'objectives' => 0];
        }

        $normalized = SyllabusStructureHelper::normalize($structure);
        $remoteTopics = $this->flattenStructure($normalized);

        return [
            'topics' => count($remoteTopics),
            'objectives' => array_sum(array_map(
                static fn (array $topic): int => count($topic['objectives']),
                $remoteTopics
            )),
        ];
    }

    public function populateFromCachedStructure(Syllabus $syllabus): array
    {
        if (!is_array($syllabus->cached_structure)) {
            throw new RuntimeException('No cached syllabus JSON is available. Refresh the cache first.');
        }

        return $this->populateFromStructure($syllabus, $syllabus->cached_structure);
    }

    public function syncFromCachedStructure(Syllabus $syllabus): array
    {
        if (!is_array($syllabus->cached_structure)) {
            throw new RuntimeException('No cached syllabus JSON is available. Refresh the cache first.');
        }

        return $this->syncFromStructure($syllabus, $syllabus->cached_structure);
    }

    public function previewSyncFromCachedStructure(Syllabus $syllabus): array
    {
        if (!is_array($syllabus->cached_structure)) {
            throw new RuntimeException('No cached syllabus JSON is available. Refresh the cache first.');
        }

        return $this->previewSyncFromStructure($syllabus, $syllabus->cached_structure);
    }

    public function populateFromStructure(Syllabus $syllabus, array $structure): array
    {
        $normalized = SyllabusStructureHelper::normalize($structure);

        if (!SyllabusStructureHelper::hasSections($normalized)) {
            throw new RuntimeException('The cached syllabus JSON does not contain any sections to import.');
        }

        $remoteTopics = $this->flattenStructure($normalized);

        return DB::transaction(function () use ($syllabus, $remoteTopics): array {
            $lockedSyllabus = $this->lockSyllabus($syllabus->id);

            if ($lockedSyllabus->topics()->exists()) {
                throw new RuntimeException(
                    'This syllabus already has local topics. Cached JSON import is limited to empty syllabi to avoid overwriting linked planning data.'
                );
            }

            $topicCount = 0;
            $objectiveCount = 0;

            foreach ($remoteTopics as $remoteTopic) {
                $createdTopic = $this->createTopicFromRemoteData($lockedSyllabus, $remoteTopic);
                $topicCount++;

                foreach ($remoteTopic['objectives'] as $remoteObjective) {
                    $this->createObjectiveFromRemoteData($createdTopic, $remoteObjective);
                    $objectiveCount++;
                }
            }

            return [
                'topics' => $topicCount,
                'objectives' => $objectiveCount,
            ];
        });
    }

    public function syncFromStructure(Syllabus $syllabus, array $structure): array
    {
        $normalized = SyllabusStructureHelper::normalize($structure);

        if (!SyllabusStructureHelper::hasSections($normalized)) {
            throw new RuntimeException('The cached syllabus JSON does not contain any sections to import.');
        }

        $remoteTopics = $this->flattenStructure($normalized);

        $result = DB::transaction(function () use ($syllabus, $remoteTopics): array {
            $lockedSyllabus = $this->lockSyllabus($syllabus->id);

            return $this->runSync($lockedSyllabus, $remoteTopics);
        });

        return $result['summary'];
    }

    public function previewSyncFromStructure(Syllabus $syllabus, array $structure): array
    {
        $normalized = SyllabusStructureHelper::normalize($structure);

        if (!SyllabusStructureHelper::hasSections($normalized)) {
            throw new RuntimeException('The cached syllabus JSON does not contain any sections to import.');
        }

        $remoteTopics = $this->flattenStructure($normalized);

        DB::beginTransaction();

        try {
            $lockedSyllabus = $this->lockSyllabus($syllabus->id);
            $result = $this->runSync($lockedSyllabus, $remoteTopics);
            DB::rollBack();

            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function lockSyllabus(int $syllabusId): Syllabus
    {
        return Syllabus::query()
            ->with(['topics.objectives'])
            ->lockForUpdate()
            ->findOrFail($syllabusId);
    }

    private function buildObjectiveCode(string $unitId, int $topicSequence, string $groupKey, int $index): string
    {
        $normalizedUnitId = strtoupper((string) preg_replace('/[^A-Za-z0-9]+/', '', $unitId));
        $prefix = $normalizedUnitId !== '' ? $normalizedUnitId : ('T' . $topicSequence);
        $normalizedGroupKey = strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '', $groupKey));
        if (str_contains($normalizedGroupKey, 'general')) {
            $groupPrefix = 'G';
        } elseif (str_contains($normalizedGroupKey, 'specific')) {
            $groupPrefix = 'S';
        } else {
            $groupPrefix = strtoupper(substr($normalizedGroupKey, 0, 2));
            $groupPrefix = $groupPrefix !== '' ? $groupPrefix : 'OB';
        }

        return substr($prefix . '-' . $groupPrefix . $index, 0, 30);
    }

    private function buildTopicDescription(array $remoteTopic): ?string
    {
        return SyllabusStructureHelper::buildTopicDescription(
            $remoteTopic['section_form'],
            $remoteTopic['unit_id'],
            $remoteTopic['unit_title'],
            $remoteTopic['path'],
            $remoteTopic['notes']
        );
    }

    private function flattenStructure(array $normalized): array
    {
        $remoteTopics = [];
        $topicSequence = 1;

        foreach ($normalized['sections'] ?? [] as $section) {
            foreach ($section['units'] ?? [] as $unit) {
                foreach ($unit['topics'] ?? [] as $topic) {
                    if (!is_array($topic)) {
                        continue;
                    }

                    $this->flattenTopicNode($topic, $section, $unit, $remoteTopics, $topicSequence);
                }
            }
        }

        return $remoteTopics;
    }

    private function flattenTopicNode(
        array $topic,
        array $section,
        array $unit,
        array &$remoteTopics,
        int &$topicSequence
    ): void {
        $currentSequence = $topicSequence;
        $remoteTopics[] = $this->buildRemoteTopic($topic, $section, $unit, $currentSequence);
        $topicSequence++;

        foreach ($topic['subtopics'] ?? [] as $subtopic) {
            if (!is_array($subtopic)) {
                continue;
            }

            $this->flattenTopicNode($subtopic, $section, $unit, $remoteTopics, $topicSequence);
        }
    }

    private function buildRemoteTopic(array $topic, array $section, array $unit, int $topicSequence): array
    {
        $sectionForm = trim((string) ($section['form'] ?? ''));
        $unitId = trim((string) ($unit['id'] ?? ''));
        $unitTitle = trim((string) ($unit['title'] ?? ''));
        $path = array_values(array_filter(array_map(
            static fn ($segment) => trim((string) $segment),
            $topic['path'] ?? [trim((string) ($topic['title'] ?? ('Topic ' . $topicSequence)))]
        )));
        $name = trim((string) ($topic['title'] ?? ('Topic ' . $topicSequence)));
        $notes = trim((string) ($topic['description'] ?? ''));
        $pathLabel = SyllabusStructureHelper::pathLabel($path);

        return [
            'sequence' => $topicSequence,
            'name' => $name,
            'description' => SyllabusStructureHelper::buildTopicDescription($sectionForm, $unitId, $unitTitle, $path, $notes),
            'notes' => $notes !== '' ? $notes : null,
            'section_form' => $sectionForm,
            'unit_id' => $unitId,
            'unit_title' => $unitTitle,
            'path' => $path,
            'path_label' => $pathLabel !== '' ? $pathLabel : $name,
            'context_key' => SyllabusStructureHelper::buildPlannerKey($sectionForm, $unitId, $unitTitle, $path),
            'objectives' => $this->flattenRemoteObjectives($topic, $unitId, $topicSequence),
        ];
    }

    private function flattenRemoteObjectives(array $topic, string $unitId, int $topicSequence): array
    {
        $remoteObjectives = [];
        $objectiveSequence = 1;

        foreach ($topic['objective_groups'] ?? [] as $groupIndex => $group) {
            if (!is_array($group)) {
                continue;
            }

            $groupKey = trim((string) ($group['key'] ?? ('group_' . ($groupIndex + 1))));
            foreach (($group['objectives'] ?? []) as $objectiveIndex => $objective) {
                if (!is_array($objective)) {
                    continue;
                }

                $objectiveText = trim((string) ($objective['text'] ?? ''));
                if ($objectiveText === '') {
                    continue;
                }

                $remoteObjectives[] = [
                    'sequence' => is_numeric($objective['sequence'] ?? null)
                        ? (int) $objective['sequence']
                        : $objectiveSequence,
                    'code' => trim((string) ($objective['code'] ?? '')) !== ''
                        ? trim((string) $objective['code'])
                        : $this->buildObjectiveCode($unitId, $topicSequence, $groupKey, $objectiveIndex + 1),
                    'objective_text' => $objectiveText,
                    'cognitive_level' => trim((string) ($objective['cognitive_level'] ?? '')) !== ''
                        ? trim((string) $objective['cognitive_level'])
                        : null,
                ];
                $objectiveSequence++;
            }
        }

        usort($remoteObjectives, static function (array $left, array $right): int {
            return [$left['sequence'], $left['code'], $left['objective_text']]
                <=> [$right['sequence'], $right['code'], $right['objective_text']];
        });

        return array_values($remoteObjectives);
    }

    private function runSync(Syllabus $syllabus, array $remoteTopics): array
    {
        $syllabus->loadMissing(['topics.objectives']);

        $localTopics = $syllabus->topics->values();
        $allLocalObjectives = $localTopics
            ->flatMap(static fn (SyllabusTopic $topic): Collection => $topic->objectives)
            ->keyBy('id');

        $matchedTopicIds = [];
        $matchedObjectiveIds = [];
        $summary = $this->emptySyncSummary();
        $changes = $this->emptySyncChanges();

        foreach ($remoteTopics as $remoteTopic) {
            $matchedTopic = $this->findBestTopicMatch($remoteTopic, $localTopics, $matchedTopicIds);

            if ($matchedTopic) {
                $changes['topics']['updated'][] = [
                    'from' => $this->topicSnapshot($matchedTopic),
                    'to' => $this->remoteTopicSnapshot($remoteTopic),
                ];

                $matchedTopic->update([
                    'sequence' => $remoteTopic['sequence'],
                    'name' => $remoteTopic['name'],
                    'description' => $remoteTopic['description'],
                    'suggested_weeks' => null,
                ]);

                $matchedTopicIds[$matchedTopic->id] = true;
                $summary['topics_updated']++;

                $this->syncTopicObjectives(
                    $matchedTopic,
                    $remoteTopic,
                    $allLocalObjectives,
                    $matchedObjectiveIds,
                    $summary,
                    $changes
                );

                continue;
            }

            $changes['topics']['created'][] = $this->remoteTopicSnapshot($remoteTopic);
            $createdTopic = $this->createTopicFromRemoteData($syllabus, $remoteTopic);
            $summary['topics_created']++;

            foreach ($remoteTopic['objectives'] as $remoteObjective) {
                $changes['objectives']['created'][] = $this->remoteObjectiveSnapshot($remoteObjective, $remoteTopic['path_label']);
                $this->createObjectiveFromRemoteData($createdTopic, $remoteObjective);
                $summary['objectives_created']++;
            }
        }

        $nextLegacyTopicSequence = count($remoteTopics) + 1;

        foreach ($localTopics as $localTopic) {
            if (isset($matchedTopicIds[$localTopic->id])) {
                continue;
            }

            if ($this->topicHasReferences($localTopic)) {
                $changes['topics']['preserved'][] = $this->topicSnapshot($localTopic) + [
                    'reason' => 'Linked to schemes or tests',
                    'new_sequence' => $nextLegacyTopicSequence,
                ];
                $localTopic->update(['sequence' => $nextLegacyTopicSequence]);
                $nextLegacyTopicSequence++;
                $summary['topics_preserved']++;
                continue;
            }

            foreach ($localTopic->objectives as $localObjective) {
                $changes['objectives']['deleted'][] = $this->objectiveSnapshot($localObjective, $localTopic->name) + [
                    'reason' => 'Unlinked local objective',
                ];
            }

            $changes['topics']['deleted'][] = $this->topicSnapshot($localTopic) + [
                'reason' => 'Unlinked local topic',
            ];

            $summary['objectives_deleted'] += $localTopic->objectives()->count();
            $localTopic->delete();
            $summary['topics_deleted']++;
        }

        return [
            'summary' => $summary,
            'changes' => $changes,
        ];
    }

    private function createTopicFromRemoteData(Syllabus $syllabus, array $remoteTopic): SyllabusTopic
    {
        return $syllabus->topics()->create([
            'sequence' => $remoteTopic['sequence'],
            'name' => $remoteTopic['name'],
            'description' => $remoteTopic['description'],
            'suggested_weeks' => null,
        ]);
    }

    private function createObjectiveFromRemoteData(SyllabusTopic $topic, array $remoteObjective): SyllabusObjective
    {
        return $topic->objectives()->create([
            'sequence' => $remoteObjective['sequence'],
            'code' => $remoteObjective['code'],
            'objective_text' => $remoteObjective['objective_text'],
            'cognitive_level' => $remoteObjective['cognitive_level'],
        ]);
    }

    private function findBestTopicMatch(array $remoteTopic, Collection $localTopics, array $matchedTopicIds): ?SyllabusTopic
    {
        $unmatchedTopics = $localTopics->reject(static fn (SyllabusTopic $localTopic): bool => isset($matchedTopicIds[$localTopic->id]));
        $exactContextMatches = $unmatchedTopics->filter(function (SyllabusTopic $localTopic) use ($remoteTopic): bool {
            return $this->localTopicContextKey($localTopic) === $remoteTopic['context_key'];
        });

        if ($exactContextMatches->count() === 1) {
            return $exactContextMatches->first();
        }

        $candidate = $unmatchedTopics
            ->map(function (SyllabusTopic $localTopic) use ($remoteTopic): array {
                return [
                    'topic' => $localTopic,
                    'score' => $this->scoreTopicMatch($remoteTopic, $localTopic),
                    'sequence_gap' => abs(((int) $localTopic->sequence) - ((int) $remoteTopic['sequence'])),
                ];
            })
            ->sortBy([
                ['score', 'desc'],
                ['sequence_gap', 'asc'],
            ])
            ->first();

        if (!$candidate || $candidate['score'] < self::TOPIC_MATCH_THRESHOLD) {
            return null;
        }

        return $candidate['topic'];
    }

    private function scoreTopicMatch(array $remoteTopic, SyllabusTopic $localTopic): int
    {
        $score = 0;

        if ($this->localTopicContextKey($localTopic) === $remoteTopic['context_key']) {
            $score += 100;
        }

        if ($this->normalizeComparison($localTopic->name) === $this->normalizeComparison($remoteTopic['name'])) {
            $score += 35;
        }

        if ($this->normalizeComparison($localTopic->description) !== ''
            && $this->normalizeComparison($localTopic->description) === $this->normalizeComparison($remoteTopic['description'])) {
            $score += 25;
        }

        if ((int) $localTopic->sequence === (int) $remoteTopic['sequence']) {
            $score += 5;
        }

        foreach ($remoteTopic['objectives'] as $remoteObjective) {
            foreach ($localTopic->objectives as $localObjective) {
                if ($this->normalizeComparison($localObjective->code) !== ''
                    && $this->normalizeComparison($localObjective->code) === $this->normalizeComparison($remoteObjective['code'])) {
                    $score += 25;
                    continue 2;
                }

                if ($this->normalizeComparison($localObjective->objective_text) === $this->normalizeComparison($remoteObjective['objective_text'])) {
                    $score += 15;
                    continue 2;
                }
            }
        }

        return $score;
    }

    private function syncTopicObjectives(
        SyllabusTopic $topic,
        array $remoteTopic,
        Collection $allLocalObjectives,
        array &$matchedObjectiveIds,
        array &$summary,
        array &$changes
    ): void {
        $topic->loadMissing('objectives');
        $localTopicObjectives = $topic->objectives->values();

        foreach ($remoteTopic['objectives'] as $remoteObjective) {
            $matchedObjective = $this->findBestObjectiveMatch(
                $remoteObjective,
                $localTopicObjectives,
                $allLocalObjectives,
                $matchedObjectiveIds
            );

            if ($matchedObjective) {
                $changes['objectives']['updated'][] = [
                    'from' => $this->objectiveSnapshot($matchedObjective, $matchedObjective->topic?->name),
                    'to' => $this->remoteObjectiveSnapshot($remoteObjective, $remoteTopic['path_label']),
                ];

                $matchedObjective->update([
                    'syllabus_topic_id' => $topic->id,
                    'sequence' => $remoteObjective['sequence'],
                    'code' => $remoteObjective['code'],
                    'objective_text' => $remoteObjective['objective_text'],
                    'cognitive_level' => $remoteObjective['cognitive_level'] ?? $matchedObjective->cognitive_level,
                ]);

                $matchedObjectiveIds[$matchedObjective->id] = true;
                $summary['objectives_updated']++;
                continue;
            }

            $changes['objectives']['created'][] = $this->remoteObjectiveSnapshot($remoteObjective, $remoteTopic['path_label']);
            $this->createObjectiveFromRemoteData($topic, $remoteObjective);
            $summary['objectives_created']++;
        }

        $nextLegacyObjectiveSequence = count($remoteTopic['objectives']) + 1;

        foreach ($localTopicObjectives as $localObjective) {
            if (isset($matchedObjectiveIds[$localObjective->id])) {
                continue;
            }

            if ($this->objectiveHasReferences($localObjective->id)) {
                $changes['objectives']['preserved'][] = $this->objectiveSnapshot($localObjective, $topic->name) + [
                    'reason' => 'Linked to schemes or tests',
                    'new_sequence' => $nextLegacyObjectiveSequence,
                ];
                $localObjective->update(['sequence' => $nextLegacyObjectiveSequence]);
                $nextLegacyObjectiveSequence++;
                $summary['objectives_preserved']++;
                continue;
            }

            $changes['objectives']['deleted'][] = $this->objectiveSnapshot($localObjective, $topic->name) + [
                'reason' => 'Unlinked local objective',
            ];
            $localObjective->delete();
            $summary['objectives_deleted']++;
        }
    }

    private function findBestObjectiveMatch(
        array $remoteObjective,
        Collection $localTopicObjectives,
        Collection $allLocalObjectives,
        array $matchedObjectiveIds
    ): ?SyllabusObjective {
        $unmatchedTopicObjectives = $localTopicObjectives
            ->reject(static fn (SyllabusObjective $localObjective): bool => isset($matchedObjectiveIds[$localObjective->id]));

        $exactCodeMatches = $unmatchedTopicObjectives->filter(function (SyllabusObjective $localObjective) use ($remoteObjective): bool {
            return $this->normalizeComparison($remoteObjective['code']) !== ''
                && $this->normalizeComparison($localObjective->code) === $this->normalizeComparison($remoteObjective['code']);
        });
        if ($exactCodeMatches->count() === 1) {
            return $exactCodeMatches->first();
        }

        $exactTextMatches = $unmatchedTopicObjectives->filter(function (SyllabusObjective $localObjective) use ($remoteObjective): bool {
            return $this->normalizeComparison($localObjective->objective_text)
                === $this->normalizeComparison($remoteObjective['objective_text']);
        });
        if ($exactTextMatches->count() === 1) {
            return $exactTextMatches->first();
        }

        $candidate = $unmatchedTopicObjectives
            ->map(function (SyllabusObjective $localObjective) use ($remoteObjective): array {
                return [
                    'objective' => $localObjective,
                    'score' => $this->scoreObjectiveMatch($remoteObjective, $localObjective),
                    'sequence_gap' => abs(((int) $localObjective->sequence) - ((int) $remoteObjective['sequence'])),
                ];
            })
            ->sortBy([
                ['score', 'desc'],
                ['sequence_gap', 'asc'],
            ])
            ->first();

        if ($candidate && $candidate['score'] >= self::OBJECTIVE_MATCH_THRESHOLD) {
            return $candidate['objective'];
        }

        $globalCodeMatches = $allLocalObjectives
            ->reject(static fn (SyllabusObjective $localObjective): bool => isset($matchedObjectiveIds[$localObjective->id]))
            ->filter(function (SyllabusObjective $localObjective) use ($remoteObjective): bool {
                return $this->normalizeComparison($remoteObjective['code']) !== ''
                    && $this->normalizeComparison($localObjective->code) === $this->normalizeComparison($remoteObjective['code']);
            });
        if ($globalCodeMatches->count() === 1) {
            return $globalCodeMatches->first();
        }

        $globalTextMatches = $allLocalObjectives
            ->reject(static fn (SyllabusObjective $localObjective): bool => isset($matchedObjectiveIds[$localObjective->id]))
            ->filter(function (SyllabusObjective $localObjective) use ($remoteObjective): bool {
                return $this->normalizeComparison($localObjective->objective_text)
                    === $this->normalizeComparison($remoteObjective['objective_text']);
            });
        if ($globalTextMatches->count() === 1) {
            return $globalTextMatches->first();
        }

        return null;
    }

    private function scoreObjectiveMatch(array $remoteObjective, SyllabusObjective $localObjective): int
    {
        $score = 0;

        if ($this->normalizeComparison($remoteObjective['code']) !== ''
            && $this->normalizeComparison($localObjective->code) === $this->normalizeComparison($remoteObjective['code'])) {
            $score += 50;
        }

        if ($this->normalizeComparison($localObjective->objective_text)
            === $this->normalizeComparison($remoteObjective['objective_text'])) {
            $score += 40;
        }

        if ((int) $localObjective->sequence === (int) $remoteObjective['sequence']) {
            $score += 5;
        }

        return $score;
    }

    private function topicHasReferences(SyllabusTopic $topic): bool
    {
        if (DB::table('scheme_of_work_entries')
            ->whereNull('deleted_at')
            ->where('syllabus_topic_id', $topic->id)
            ->exists()) {
            return true;
        }

        $objectiveIds = $topic->objectives()->pluck('id');
        if ($objectiveIds->isEmpty()) {
            return false;
        }

        return DB::table('scheme_entry_objectives')
            ->whereIn('syllabus_objective_id', $objectiveIds)
            ->exists()
            || DB::table('test_syllabus_objectives')
                ->whereIn('syllabus_objective_id', $objectiveIds)
                ->exists();
    }

    private function objectiveHasReferences(int $objectiveId): bool
    {
        return DB::table('scheme_entry_objectives')
            ->where('syllabus_objective_id', $objectiveId)
            ->exists()
            || DB::table('test_syllabus_objectives')
                ->where('syllabus_objective_id', $objectiveId)
                ->exists();
    }

    private function localTopicContextKey(SyllabusTopic $topic): string
    {
        $context = SyllabusStructureHelper::parseTopicDescription($topic->description);
        $path = $context['path'];
        if (empty($path)) {
            $path = [trim((string) $topic->name)];
        }

        return SyllabusStructureHelper::buildPlannerKey(
            $context['section_form'],
            $context['unit_id'],
            $context['unit_title'],
            $path
        );
    }

    private function normalizeComparison(?string $value): string
    {
        $normalized = mb_strtolower(trim((string) $value));
        $normalized = preg_replace('/[^\pL\pN]+/u', ' ', $normalized) ?? $normalized;

        return preg_replace('/\s+/', ' ', $normalized) ?? '';
    }

    private function emptySyncSummary(): array
    {
        return [
            'topics_created' => 0,
            'topics_updated' => 0,
            'topics_deleted' => 0,
            'topics_preserved' => 0,
            'objectives_created' => 0,
            'objectives_updated' => 0,
            'objectives_deleted' => 0,
            'objectives_preserved' => 0,
        ];
    }

    private function emptySyncChanges(): array
    {
        return [
            'topics' => [
                'created' => [],
                'updated' => [],
                'deleted' => [],
                'preserved' => [],
            ],
            'objectives' => [
                'created' => [],
                'updated' => [],
                'deleted' => [],
                'preserved' => [],
            ],
        ];
    }

    private function topicSnapshot(SyllabusTopic $topic): array
    {
        return [
            'name' => (string) $topic->name,
            'sequence' => (int) $topic->sequence,
            'description' => $topic->description,
        ];
    }

    private function remoteTopicSnapshot(array $remoteTopic): array
    {
        return [
            'name' => $remoteTopic['name'],
            'sequence' => $remoteTopic['sequence'],
            'description' => $remoteTopic['description'],
        ];
    }

    private function objectiveSnapshot(SyllabusObjective $objective, ?string $topicName): array
    {
        return [
            'topic_name' => $topicName,
            'code' => (string) $objective->code,
            'sequence' => (int) $objective->sequence,
            'objective_text' => (string) $objective->objective_text,
            'cognitive_level' => $objective->cognitive_level,
        ];
    }

    private function remoteObjectiveSnapshot(array $remoteObjective, string $topicName): array
    {
        return [
            'topic_name' => $topicName,
            'code' => $remoteObjective['code'],
            'sequence' => $remoteObjective['sequence'],
            'objective_text' => $remoteObjective['objective_text'],
            'cognitive_level' => $remoteObjective['cognitive_level'],
        ];
    }
}
