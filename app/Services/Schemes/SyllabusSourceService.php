<?php

namespace App\Services\Schemes;

use App\Helpers\SyllabusStructureHelper;
use App\Models\Schemes\Syllabus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class SyllabusSourceService
{
    public function getDisplayStructure(?Syllabus $syllabus): ?array
    {
        if (!$syllabus) {
            return null;
        }

        $cached = $this->normalizedCachedStructure($syllabus);

        if ($cached) {
            return $cached;
        }

        if (blank($syllabus->source_url)) {
            return null;
        }

        try {
            return $this->refresh($syllabus);
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    public function refresh(Syllabus $syllabus): array
    {
        if (blank($syllabus->source_url)) {
            throw new RuntimeException('Add a syllabus source URL before refreshing the cache.');
        }

        $response = Http::timeout(10)
            ->acceptJson()
            ->get($syllabus->source_url);

        if (!$response->successful()) {
            throw new RuntimeException(
                'Unable to fetch the remote syllabus right now (HTTP ' . $response->status() . ').'
            );
        }

        $structure = SyllabusStructureHelper::parsePayload($response->body());

        if (!SyllabusStructureHelper::hasSections($structure)) {
            throw new RuntimeException('The remote syllabus JSON does not contain any sections.');
        }

        DB::transaction(function () use ($syllabus, $structure): void {
            $lockedSyllabus = Syllabus::query()->lockForUpdate()->findOrFail($syllabus->id);

            $lockedSyllabus->forceFill([
                'cached_structure' => $structure,
                'cached_at' => now(),
            ])->save();
        });

        return $structure;
    }

    public function clearCache(Syllabus $syllabus): void
    {
        DB::transaction(function () use ($syllabus): void {
            $lockedSyllabus = Syllabus::query()->lockForUpdate()->findOrFail($syllabus->id);

            $lockedSyllabus->forceFill([
                'cached_structure' => null,
                'cached_at' => null,
            ])->save();
        });
    }

    private function normalizedCachedStructure(Syllabus $syllabus): ?array
    {
        if (!is_array($syllabus->cached_structure)) {
            return null;
        }

        $structure = SyllabusStructureHelper::normalize($syllabus->cached_structure);

        return SyllabusStructureHelper::hasSections($structure) ? $structure : null;
    }
}
