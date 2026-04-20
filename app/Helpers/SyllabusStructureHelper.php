<?php

namespace App\Helpers;

use InvalidArgumentException;
use JsonException;

class SyllabusStructureHelper
{
    private const OBJECTIVE_TEXT_KEYS = [
        'text',
        'objective_text',
        'objective',
        'description',
        'statement',
        'content',
        'title',
        'name',
    ];

    private const OBJECTIVE_GROUP_VALUE_KEYS = [
        'objectives',
        'items',
        'entries',
        'values',
    ];

    private const DIRECT_OBJECTIVE_KEYS = [
        'general_objectives',
        'specific_objectives',
        'objectives',
        'learning_objectives',
        'outcomes',
        'competencies',
    ];

    private const SUBTOPIC_KEYS = [
        'subtopics',
        'sub_topics',
        'children',
        'topics',
    ];

    public static function parsePayload(?string $payload): ?array
    {
        if (blank($payload)) {
            return null;
        }

        try {
            $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException('The structured syllabus JSON is invalid.', 0, $e);
        }

        if (!is_array($decoded)) {
            throw new InvalidArgumentException('The structured syllabus payload must decode to an object.');
        }

        return self::normalize($decoded);
    }

    public static function normalize(array $payload): array
    {
        $structure = isset($payload['syllabus']) && is_array($payload['syllabus'])
            ? $payload['syllabus']
            : $payload;

        if (!is_array($structure)) {
            throw new InvalidArgumentException('The structured syllabus payload must contain a syllabus object.');
        }

        return [
            'title' => self::extractTitle($structure, ['title', 'name'], 'Syllabus'),
            'sections' => self::normalizeSections($structure),
        ];
    }

    public static function hasSections(?array $structure): bool
    {
        return is_array($structure) && !empty($structure['sections']) && is_array($structure['sections']);
    }

    public static function toPrettyJson(?array $structure): string
    {
        if (!$structure) {
            return '';
        }

        return (string) json_encode(
            ['syllabus' => self::normalize($structure)],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * @param array<int, string> $path
     */
    public static function pathLabel(array $path): string
    {
        $cleaned = array_values(array_filter(array_map(static function ($item): ?string {
            $value = trim((string) $item);

            return $value === '' ? null : $value;
        }, $path)));

        return implode(' > ', $cleaned);
    }

    /**
     * @param array<int, string> $path
     */
    public static function buildTopicDescription(
        ?string $sectionForm,
        ?string $unitId,
        ?string $unitTitle,
        array $path,
        ?string $topicDescription = null
    ): ?string {
        $parts = [];
        $sectionForm = trim((string) $sectionForm);
        $unitId = trim((string) $unitId);
        $unitTitle = trim((string) $unitTitle);
        $pathLabel = self::pathLabel($path);
        $topicDescription = trim((string) $topicDescription);

        if ($sectionForm !== '') {
            $parts[] = 'Section: ' . $sectionForm;
        }

        if ($unitId !== '' && $unitTitle !== '') {
            $parts[] = 'Unit: [' . $unitId . '] ' . $unitTitle;
        } elseif ($unitId !== '') {
            $parts[] = 'Unit: [' . $unitId . ']';
        } elseif ($unitTitle !== '') {
            $parts[] = 'Unit: ' . $unitTitle;
        }

        if ($pathLabel !== '') {
            $parts[] = 'Path: ' . $pathLabel;
        }

        if ($topicDescription !== '') {
            $parts[] = 'Notes: ' . $topicDescription;
        }

        return empty($parts) ? null : implode(' | ', $parts);
    }

    /**
     * @return array{section_form:?string, unit_id:?string, unit_title:?string, path:array<int, string>, notes:?string}
     */
    public static function parseTopicDescription(?string $description): array
    {
        $context = [
            'section_form' => null,
            'unit_id' => null,
            'unit_title' => null,
            'path' => [],
            'notes' => null,
        ];

        $description = trim((string) $description);
        if ($description === '') {
            return $context;
        }

        foreach (preg_split('/\s*\|\s*/', $description) ?: [] as $part) {
            if (str_starts_with($part, 'Section: ')) {
                $context['section_form'] = self::nullableString(substr($part, 9));
                continue;
            }

            if (str_starts_with($part, 'Unit: ')) {
                $unit = trim(substr($part, 6));
                if (preg_match('/^\[(.+?)\]\s*(.*)$/', $unit, $matches) === 1) {
                    $context['unit_id'] = self::nullableString($matches[1]);
                    $context['unit_title'] = self::nullableString($matches[2]);
                } else {
                    $context['unit_title'] = self::nullableString($unit);
                }
                continue;
            }

            if (str_starts_with($part, 'Path: ')) {
                $context['path'] = array_values(array_filter(array_map(
                    static fn ($segment) => self::nullableString($segment),
                    preg_split('/\s*>\s*/', substr($part, 6)) ?: []
                )));
                continue;
            }

            if (str_starts_with($part, 'Notes: ')) {
                $context['notes'] = self::nullableString(substr($part, 7));
            }
        }

        return $context;
    }

    /**
     * @param array<int, string> $path
     */
    public static function buildPlannerKey(?string $sectionForm, ?string $unitId, ?string $unitTitle, array $path): string
    {
        $segments = array_merge(
            [
                self::normalizeComparison($sectionForm),
                self::normalizeComparison($unitId),
                self::normalizeComparison($unitTitle),
            ],
            array_map([self::class, 'normalizeComparison'], $path)
        );

        return implode('|', array_filter($segments, static fn ($segment) => $segment !== ''));
    }

    private static function normalizeSections(array $structure): array
    {
        $rawSections = [];

        if (is_array($structure['sections'] ?? null)) {
            $rawSections = array_values($structure['sections']);
        } elseif (is_array($structure['units'] ?? null)) {
            $rawSections = [[
                'form' => self::extractTitle($structure, ['form', 'section', 'title', 'name'], 'Section 1'),
                'units' => $structure['units'],
            ]];
        } elseif (is_array($structure['topics'] ?? null) || self::payloadHasTopicContent($structure)) {
            $rawSections = [[
                'form' => self::extractTitle($structure, ['form', 'section', 'title', 'name'], 'Section 1'),
                'units' => [[
                    'id' => self::nullableString($structure['id'] ?? $structure['code'] ?? null) ?? '',
                    'title' => self::extractTitle($structure, ['unit_title', 'title', 'name'], 'Unit 1'),
                    'topics' => is_array($structure['topics'] ?? null) ? $structure['topics'] : [$structure],
                ]],
            ]];
        }

        $sections = [];

        foreach ($rawSections as $sectionIndex => $section) {
            if (!is_array($section)) {
                continue;
            }

            $units = self::normalizeUnits($section, $sectionIndex + 1);
            if (empty($units)) {
                continue;
            }

            $sections[] = [
                'form' => self::extractTitle($section, ['form', 'title', 'name'], 'Section ' . ($sectionIndex + 1)),
                'units' => $units,
            ];
        }

        return $sections;
    }

    private static function normalizeUnits(array $section, int $sectionNumber): array
    {
        $rawUnits = [];

        if (is_array($section['units'] ?? null)) {
            $rawUnits = array_values($section['units']);
        } elseif (is_array($section['topics'] ?? null) || self::payloadHasTopicContent($section)) {
            $rawUnits = [[
                'id' => self::nullableString($section['id'] ?? $section['code'] ?? null) ?? '',
                'title' => self::extractTitle($section, ['unit_title', 'title', 'name'], 'Unit 1'),
                'topics' => is_array($section['topics'] ?? null) ? $section['topics'] : [$section],
            ]];
        }

        $units = [];

        foreach ($rawUnits as $unitIndex => $unit) {
            if (!is_array($unit)) {
                continue;
            }

            $topics = self::normalizeTopics($unit, []);
            if (empty($topics)) {
                continue;
            }

            $units[] = [
                'id' => self::nullableString($unit['id'] ?? $unit['code'] ?? null) ?? '',
                'title' => self::extractTitle($unit, ['title', 'name', 'label'], 'Unit ' . ($unitIndex + 1)),
                'topics' => $topics,
            ];
        }

        if (empty($units) && is_array($section['topics'] ?? null)) {
            $units[] = [
                'id' => '',
                'title' => 'Unit ' . $sectionNumber,
                'topics' => self::normalizeTopics(['topics' => $section['topics']], []),
            ];
        }

        return $units;
    }

    /**
     * @param array<int, string> $pathPrefix
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeTopics(array $unit, array $pathPrefix): array
    {
        $rawTopics = [];

        if (is_array($unit['topics'] ?? null)) {
            $rawTopics = array_values($unit['topics']);
        } elseif (self::payloadHasTopicContent($unit)) {
            $rawTopics = [$unit];
        }

        $topics = [];
        foreach ($rawTopics as $topicIndex => $topic) {
            if (!is_array($topic)) {
                continue;
            }

            $topics[] = self::normalizeTopicNode($topic, $topicIndex + 1, $pathPrefix);
        }

        return $topics;
    }

    /**
     * @param array<int, string> $pathPrefix
     * @return array<string, mixed>
     */
    private static function normalizeTopicNode(array $topic, int $topicNumber, array $pathPrefix): array
    {
        $title = self::extractTitle($topic, ['title', 'name', 'label', 'topic'], 'Topic ' . $topicNumber);
        $path = array_values(array_filter(array_merge($pathPrefix, [$title]), static fn ($item) => $item !== ''));
        $subtopics = [];

        foreach (self::collectSubtopicNodes($topic) as $subtopicIndex => $subtopic) {
            if (!is_array($subtopic)) {
                continue;
            }

            $subtopics[] = self::normalizeTopicNode($subtopic, $subtopicIndex + 1, $path);
        }

        return [
            'title' => $title,
            'description' => self::nullableString($topic['description'] ?? $topic['notes'] ?? $topic['summary'] ?? null),
            'path' => $path,
            'objective_groups' => self::normalizeObjectiveGroups($topic),
            'subtopics' => $subtopics,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeObjectiveGroups(array $topic): array
    {
        $groups = [];

        if (is_array($topic['objective_groups'] ?? null)) {
            foreach (self::normalizeFlexibleObjectiveValue('objective_groups', $topic['objective_groups']) as $group) {
                $groups[] = $group;
            }
        }

        foreach (self::DIRECT_OBJECTIVE_KEYS as $groupKey) {
            if (!array_key_exists($groupKey, $topic)) {
                continue;
            }

            foreach (self::normalizeFlexibleObjectiveValue($groupKey, $topic[$groupKey]) as $group) {
                $groups[] = $group;
            }
        }

        return array_values(array_filter($groups, static function (array $group): bool {
            return !empty($group['objectives']);
        }));
    }

    /**
     * @param mixed $value
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeFlexibleObjectiveValue(string $groupKey, $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        if (self::looksLikeObjectiveItem($value)) {
            $group = self::normalizeObjectiveGroup($groupKey, $value, 1, true);

            return empty($group['objectives']) ? [] : [$group];
        }

        if (self::isList($value)) {
            $containsGroupObjects = false;
            foreach ($value as $item) {
                if (is_array($item) && self::looksLikeObjectiveGroup($item)) {
                    $containsGroupObjects = true;
                    break;
                }
            }

            if ($containsGroupObjects) {
                $groups = [];
                foreach (array_values($value) as $index => $group) {
                    if (!is_array($group)) {
                        continue;
                    }

                    $normalizedGroup = self::normalizeObjectiveGroup($groupKey, $group, $index + 1, false);
                    if (!empty($normalizedGroup['objectives'])) {
                        $groups[] = $normalizedGroup;
                    }
                }

                return $groups;
            }

            $group = self::normalizeObjectiveGroup($groupKey, $value, 1, true);

            return empty($group['objectives']) ? [] : [$group];
        }

        if (self::looksLikeObjectiveGroup($value)) {
            $group = self::normalizeObjectiveGroup($groupKey, $value, 1, false);

            return empty($group['objectives']) ? [] : [$group];
        }

        $groups = [];
        foreach ($value as $nestedKey => $nestedValue) {
            if (!is_array($nestedValue)) {
                continue;
            }

            foreach (self::normalizeFlexibleObjectiveValue((string) $nestedKey, $nestedValue) as $group) {
                $groups[] = $group;
            }
        }

        return $groups;
    }

    /**
     * @param array<string, mixed>|array<int, mixed> $value
     * @return array<string, mixed>
     */
    private static function normalizeObjectiveGroup(string $groupKey, array $value, int $index, bool $valueIsList): array
    {
        $items = $valueIsList ? $value : null;

        if (!$valueIsList) {
            foreach (self::OBJECTIVE_GROUP_VALUE_KEYS as $itemKey) {
                if (is_array($value[$itemKey] ?? null)) {
                    $items = $value[$itemKey];
                    break;
                }
            }
        }

        $normalizedKey = self::normalizeGroupKey((string) ($value['key'] ?? $groupKey), $index);
        $label = self::nullableString($value['label'] ?? $value['title'] ?? null)
            ?? self::humanizeKey($groupKey);

        return [
            'key' => $normalizedKey,
            'label' => $label,
            'objectives' => self::normalizeObjectiveList(is_array($items) ? $items : []),
        ];
    }

    /**
     * @param array<int, mixed> $items
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeObjectiveList(array $items): array
    {
        $objectives = [];
        $sequence = 1;

        foreach ($items as $item) {
            $objective = self::normalizeObjective($item, $sequence);
            if ($objective === null) {
                continue;
            }

            $objectives[] = $objective;
            $sequence++;
        }

        return $objectives;
    }

    /**
     * @param mixed $item
     * @return array<string, mixed>|null
     */
    private static function normalizeObjective($item, int $sequence): ?array
    {
        if (is_string($item) || is_numeric($item)) {
            $text = self::nullableString((string) $item);
            if ($text === null) {
                return null;
            }

            return [
                'text' => $text,
                'code' => null,
                'cognitive_level' => null,
                'sequence' => $sequence,
                'meta' => [],
            ];
        }

        if (!is_array($item)) {
            return null;
        }

        $text = null;
        foreach (self::OBJECTIVE_TEXT_KEYS as $textKey) {
            $text = self::nullableString($item[$textKey] ?? null);
            if ($text !== null) {
                break;
            }
        }

        if ($text === null) {
            return null;
        }

        $meta = $item;
        foreach (array_merge(self::OBJECTIVE_TEXT_KEYS, ['code', 'cognitive_level', 'level', 'taxonomy', 'sequence']) as $consumedKey) {
            unset($meta[$consumedKey]);
        }

        return [
            'text' => $text,
            'code' => self::nullableString($item['code'] ?? null),
            'cognitive_level' => self::nullableString($item['cognitive_level'] ?? $item['level'] ?? $item['taxonomy'] ?? null),
            'sequence' => is_numeric($item['sequence'] ?? null) ? (int) $item['sequence'] : $sequence,
            'meta' => self::sanitizeMeta($meta),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private static function collectSubtopicNodes(array $topic): array
    {
        $nodes = [];

        foreach (self::SUBTOPIC_KEYS as $key) {
            if ($key === 'topics' && array_key_exists('objective_groups', $topic)) {
                continue;
            }

            if (!is_array($topic[$key] ?? null)) {
                continue;
            }

            foreach ($topic[$key] as $child) {
                $nodes[] = $child;
            }
        }

        return $nodes;
    }

    private static function payloadHasTopicContent(array $payload): bool
    {
        foreach (self::DIRECT_OBJECTIVE_KEYS as $key) {
            if (array_key_exists($key, $payload) && is_array($payload[$key])) {
                return true;
            }
        }

        foreach (self::SUBTOPIC_KEYS as $key) {
            if (is_array($payload[$key] ?? null)) {
                return true;
            }
        }

        return self::nullableString($payload['description'] ?? null) !== null
            && self::extractTitle($payload, ['title', 'name', 'label', 'topic'], '') !== '';
    }

    private static function looksLikeObjectiveGroup(array $value): bool
    {
        foreach (self::OBJECTIVE_GROUP_VALUE_KEYS as $key) {
            if (is_array($value[$key] ?? null)) {
                return true;
            }
        }

        return isset($value['label']) || isset($value['title']);
    }

    private static function looksLikeObjectiveItem(array $value): bool
    {
        foreach (self::OBJECTIVE_TEXT_KEYS as $key) {
            if (self::nullableString($value[$key] ?? null) !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $meta
     * @return array<string, mixed>
     */
    private static function sanitizeMeta(array $meta): array
    {
        $clean = [];

        foreach ($meta as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_scalar($value) || is_array($value)) {
                $clean[(string) $key] = $value;
            }
        }

        return $clean;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<int, string> $keys
     */
    private static function extractTitle(array $payload, array $keys, string $default): string
    {
        foreach ($keys as $key) {
            $value = self::nullableString($payload[$key] ?? null);
            if ($value !== null) {
                return $value;
            }
        }

        return $default;
    }

    private static function nullableString($value): ?string
    {
        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private static function normalizeGroupKey(string $groupKey, int $index): string
    {
        $normalized = strtolower(trim($groupKey));
        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized) ?? $normalized;
        $normalized = trim($normalized, '_');

        return $normalized !== '' ? $normalized : ('group_' . $index);
    }

    private static function humanizeKey(string $key): string
    {
        $label = preg_replace('/[_-]+/', ' ', trim($key)) ?? $key;
        $label = preg_replace('/\s+/', ' ', $label) ?? $label;

        return ucwords($label !== '' ? $label : 'Objectives');
    }

    private static function normalizeComparison(?string $value): string
    {
        $normalized = mb_strtolower(trim((string) $value));
        $normalized = preg_replace('/[^\pL\pN]+/u', ' ', $normalized) ?? $normalized;

        return preg_replace('/\s+/', ' ', $normalized) ?? '';
    }

    /**
     * @param mixed $value
     */
    private static function isList($value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        return array_keys($value) === range(0, count($value) - 1);
    }
}
