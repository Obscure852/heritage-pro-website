<?php

use App\Support\SyllabusSeedRegistry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable('subjects') ||
            !Schema::hasTable('syllabi') ||
            !Schema::hasColumn('subjects', 'syllabus_url') ||
            !Schema::hasColumn('syllabi', 'source_url')
        ) {
            return;
        }

        $subjects = DB::table('subjects')
            ->select('id', 'level', 'syllabus_url')
            ->whereNull('deleted_at')
            ->whereNotNull('syllabus_url')
            ->whereIn('level', ['Junior', 'Senior'])
            ->orderBy('id')
            ->get();

        foreach ($subjects as $subject) {
            $payload = SyllabusSeedRegistry::canonicalSyllabusPayload(
                (int) $subject->id,
                $subject->level,
                $subject->syllabus_url
            );

            if (!$payload) {
                continue;
            }

            $this->syncCanonicalSyllabus((int) $subject->id, (string) $subject->level, $payload);
        }
    }

    public function down(): void
    {
        if (
            !Schema::hasTable('subjects') ||
            !Schema::hasTable('syllabi') ||
            !Schema::hasColumn('subjects', 'syllabus_url') ||
            !Schema::hasColumn('syllabi', 'source_url')
        ) {
            return;
        }

        $subjects = DB::table('subjects')
            ->select('id', 'level', 'syllabus_url')
            ->whereNull('deleted_at')
            ->whereNotNull('syllabus_url')
            ->whereIn('level', ['Junior', 'Senior'])
            ->orderBy('id')
            ->get();

        foreach ($subjects as $subject) {
            $payload = SyllabusSeedRegistry::canonicalSyllabusPayload(
                (int) $subject->id,
                $subject->level,
                $subject->syllabus_url
            );

            if (!$payload) {
                continue;
            }

            $activeRows = DB::table('syllabi')
                ->where('subject_id', $subject->id)
                ->where('level', $subject->level)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get();

            $canonicalIds = $activeRows
                ->filter(fn ($row) => $this->rowMatchesCanonicalPayload($row, $payload))
                ->pluck('id')
                ->all();

            if (!empty($canonicalIds)) {
                DB::table('syllabi')->whereIn('id', $canonicalIds)->delete();
            }
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function syncCanonicalSyllabus(int $subjectId, string $level, array $payload): void
    {
        $activeRows = DB::table('syllabi')
            ->where('subject_id', $subjectId)
            ->where('level', $level)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        if ($this->hasSingleCanonicalActiveRow($activeRows, $payload)) {
            return;
        }

        if ($activeRows->isNotEmpty()) {
            DB::table('syllabi')
                ->whereIn('id', $activeRows->pluck('id')->all())
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        DB::table('syllabi')->insert($payload);
    }

    /**
     * @param Collection<int, object> $activeRows
     * @param array<string, mixed> $payload
     */
    private function hasSingleCanonicalActiveRow(Collection $activeRows, array $payload): bool
    {
        return $activeRows->count() === 1
            && $this->rowMatchesCanonicalPayload($activeRows->first(), $payload);
    }

    /**
     * @param object $row
     * @param array<string, mixed> $payload
     */
    private function rowMatchesCanonicalPayload(object $row, array $payload): bool
    {
        $expectedGrades = json_decode((string) ($payload['grades'] ?? '[]'), true);
        $actualGrades = json_decode((string) ($row->grades ?? '[]'), true);

        return (int) $row->subject_id === (int) $payload['subject_id']
            && (string) $row->level === (string) $payload['level']
            && $actualGrades === $expectedGrades
            && (string) ($row->source_url ?? '') === (string) ($payload['source_url'] ?? '')
            && (int) ($row->is_active ?? 0) === 1
            && is_null($row->document_id)
            && is_null($row->description)
            && is_null($row->cached_structure)
            && is_null($row->cached_at)
            && is_null($row->deleted_at);
    }
};
