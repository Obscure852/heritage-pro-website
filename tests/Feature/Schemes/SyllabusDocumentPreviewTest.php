<?php

namespace Tests\Feature\Schemes;

use App\Models\Document;
use App\Models\DocumentAudit;
use App\Models\Role;
use App\Models\Schemes\Syllabus;
use App\Models\Subject;
use App\Models\User;
use App\Services\Documents\ExternalDocumentCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SyllabusDocumentPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_external_syllabus_preview_streams_cached_pdf_and_reuses_local_cache(): void
    {
        Storage::fake('local');
        $user = $this->actingTeacher();
        $subject = Subject::query()->firstOrCreate(
            [
                'level' => 'Senior',
                'abbrev' => 'ENG',
            ],
            [
                'name' => 'English',
                'components' => false,
                'description' => 'Senior English',
                'department' => 'Languages',
                'syllabus_url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/English_Language_Syllabus.json',
            ]
        );

        $document = Document::create([
            'title' => 'Senior English Syllabus PDF',
            'source_type' => Document::SOURCE_EXTERNAL_URL,
            'external_url' => 'https://example.com/syllabus.pdf',
            'storage_disk' => null,
            'storage_path' => null,
            'original_name' => 'English-Language.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => null,
            'checksum_sha256' => null,
            'owner_id' => $user->id,
            'status' => Document::STATUS_PUBLISHED,
            'visibility' => Document::VISIBILITY_INTERNAL,
            'current_version' => '1.0',
            'version_count' => 1,
            'published_at' => now(),
        ]);

        $syllabus = Syllabus::create([
            'subject_id' => $subject->id,
            'grades' => ['F4', 'F5'],
            'level' => 'Senior',
            'document_id' => $document->id,
            'is_active' => true,
            'description' => null,
            'source_url' => null,
            'cached_structure' => null,
            'cached_at' => null,
        ]);

        Http::fake([
            'https://example.com/syllabus.pdf' => Http::response('%PDF-1.4 cached syllabus pdf', 200, [
                'Content-Type' => 'application/pdf',
            ]),
        ]);

        $firstResponse = $this->get(route('syllabi.document.preview', $syllabus));

        $firstResponse->assertOk();
        $firstResponse->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('%PDF-1.4 cached syllabus pdf', $firstResponse->streamedContent());
        Http::assertSentCount(1);

        $cacheService = app(ExternalDocumentCacheService::class);
        Storage::disk('local')->assertExists($cacheService->cachePath($document));

        Http::fake([
            '*' => function () {
                throw new \RuntimeException('Remote syllabus should be served from cache on subsequent requests.');
            },
        ]);

        $secondResponse = $this->get(route('syllabi.document.preview', $syllabus));

        $secondResponse->assertOk();
        $secondResponse->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('%PDF-1.4 cached syllabus pdf', $secondResponse->streamedContent());
        $this->assertSame(
            2,
            DocumentAudit::query()
                ->where('document_id', $document->id)
                ->where('action', DocumentAudit::ACTION_PREVIEWED)
                ->count()
        );
    }

    private function actingTeacher(): User
    {
        $user = User::factory()->create([
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'active' => true,
        ]);
        $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher'], ['description' => 'Teacher']);
        $user->roles()->attach($teacherRole->id);
        $this->actingAs($user);

        return $user;
    }
}
