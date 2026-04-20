<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SyllabusDocumentSync
{
    public const FOLDER_NAME = 'Syllabi Documents';
    public const LEGACY_FOLDER_NAME = 'Syllabi Docouments';

    public static function sync(): void
    {
        if (!Schema::hasTable('documents') || !Schema::hasTable('document_folders') || !Schema::hasTable('syllabi') || !Schema::hasTable('subjects')) {
            return;
        }

        $ownerId = self::resolveOwnerId();
        $timestamp = now();
        $folderId = self::ensureFolder($ownerId, $timestamp);

        $subjects = DB::table('subjects')
            ->select('id', 'name', 'abbrev', 'level')
            ->whereNull('deleted_at')
            ->get();

        foreach ($subjects as $subject) {
            $url = SyllabusDocumentSeedRegistry::urlFor($subject->level, $subject->abbrev, $subject->name);
            if (blank($url)) {
                continue;
            }

            $documentId = self::upsertDocumentForSubject($subject, $ownerId, $folderId, $url, $timestamp);

            DB::table('syllabi')
                ->where('subject_id', $subject->id)
                ->where('level', $subject->level)
                ->whereNull('deleted_at')
                ->update([
                    'document_id' => $documentId,
                    'updated_at' => $timestamp,
                ]);
        }

        self::refreshFolderStats($folderId, $timestamp);
    }

    public static function clearSeededLinksAndDocuments(): void
    {
        if (!Schema::hasTable('documents') || !Schema::hasTable('document_folders') || !Schema::hasTable('syllabi') || !Schema::hasTable('subjects')) {
            return;
        }

        foreach (DB::table('subjects')->select('id', 'name', 'abbrev', 'level')->whereNull('deleted_at')->get() as $subject) {
            $url = SyllabusDocumentSeedRegistry::urlFor($subject->level, $subject->abbrev, $subject->name);
            if (blank($url)) {
                continue;
            }

            DB::table('syllabi')
                ->where('subject_id', $subject->id)
                ->where('level', $subject->level)
                ->whereNull('deleted_at')
                ->update([
                    'document_id' => null,
                    'updated_at' => now(),
                ]);

            DB::table('documents')
                ->where('source_type', 'external_url')
                ->where('external_url', $url)
                ->delete();
        }

        foreach (self::folderCandidates() as $folder) {
            $remainingDocuments = DB::table('documents')
                ->where('folder_id', $folder->id)
                ->whereNull('deleted_at')
                ->count();

            if ($remainingDocuments === 0) {
                DB::table('document_folders')->where('id', $folder->id)->delete();
                continue;
            }

            self::refreshFolderStats((int) $folder->id, now());
        }
    }

    public static function resolveOwnerId(): int
    {
        $systemAdminId = DB::table('users')
            ->where('email', 'obscure852@gmail.com')
            ->whereNull('deleted_at')
            ->value('id');

        if ($systemAdminId) {
            return (int) $systemAdminId;
        }

        $adminId = DB::table('users')
            ->join('role_users', 'role_users.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_users.role_id')
            ->where('users.status', 'Current')
            ->whereNull('users.deleted_at')
            ->whereNull('role_users.deleted_at')
            ->where('roles.name', 'Administrator')
            ->orderBy('users.id')
            ->value('users.id');

        if ($adminId) {
            return (int) $adminId;
        }

        $documentsAdminId = DB::table('users')
            ->join('role_users', 'role_users.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_users.role_id')
            ->where('users.status', 'Current')
            ->whereNull('users.deleted_at')
            ->whereNull('role_users.deleted_at')
            ->where('roles.name', 'Documents Admin')
            ->orderBy('users.id')
            ->value('users.id');

        if ($documentsAdminId) {
            return (int) $documentsAdminId;
        }

        throw new \RuntimeException('Unable to seed syllabus documents because no eligible owner user was found.');
    }

    public static function ensureFolder(int $ownerId, $timestamp): int
    {
        $folder = DB::table('document_folders')
            ->where('name', self::FOLDER_NAME)
            ->whereNull('parent_id')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->first();
        $legacyFolder = DB::table('document_folders')
            ->where('name', self::LEGACY_FOLDER_NAME)
            ->whereNull('parent_id')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->first();

        if ($folder && $legacyFolder && (int) $folder->id !== (int) $legacyFolder->id) {
            DB::table('documents')
                ->where('folder_id', $legacyFolder->id)
                ->update([
                    'folder_id' => $folder->id,
                    'updated_at' => $timestamp,
                ]);

            DB::table('document_folders')
                ->where('id', $legacyFolder->id)
                ->delete();
        }

        if (!$folder && $legacyFolder) {
            DB::table('document_folders')
                ->where('id', $legacyFolder->id)
                ->update([
                    'name' => self::FOLDER_NAME,
                    'updated_at' => $timestamp,
                ]);

            $folder = DB::table('document_folders')->where('id', $legacyFolder->id)->first();
        }

        if ($folder) {
            DB::table('document_folders')
                ->where('id', $folder->id)
                ->update([
                    'description' => 'Shared syllabus documents for junior and senior schools.',
                    'owner_id' => $ownerId,
                    'repository_type' => 'institutional',
                    'visibility' => 'internal',
                    'inherit_permissions' => true,
                    'sort_order' => 0,
                    'updated_at' => $timestamp,
                ]);

            return (int) $folder->id;
        }

        $folderId = (int) DB::table('document_folders')->insertGetId([
            'ulid' => (string) Str::ulid(),
            'name' => self::FOLDER_NAME,
            'description' => 'Shared syllabus documents for junior and senior schools.',
            'parent_id' => null,
            'owner_id' => $ownerId,
            'repository_type' => 'institutional',
            'department_id' => null,
            'visibility' => 'internal',
            'inherit_permissions' => true,
            'sort_order' => 0,
            'path' => null,
            'depth' => 0,
            'document_count' => 0,
            'total_size_bytes' => 0,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'deleted_at' => null,
        ]);

        DB::table('document_folders')
            ->where('id', $folderId)
            ->update([
                'path' => '/' . $folderId,
                'depth' => 0,
                'updated_at' => $timestamp,
            ]);

        return $folderId;
    }

    public static function upsertDocumentForSubject(object $subject, int $ownerId, int $folderId, string $url, $timestamp): int
    {
        $title = SyllabusDocumentSeedRegistry::titleFor((string) $subject->name, (string) $subject->level);
        $metadata = ExternalDocumentMetadata::fromUrl($url, $title);

        $linkedExternalDocument = DB::table('documents')
            ->join('syllabi', 'syllabi.document_id', '=', 'documents.id')
            ->where('syllabi.subject_id', $subject->id)
            ->where('syllabi.level', $subject->level)
            ->whereNull('syllabi.deleted_at')
            ->where('documents.source_type', 'external_url')
            ->whereNull('documents.deleted_at')
            ->select('documents.id')
            ->orderByDesc('documents.id')
            ->first();

        $document = null;
        if ($linkedExternalDocument) {
            $document = DB::table('documents')->where('id', $linkedExternalDocument->id)->first();
        }

        if (!$document) {
            $document = DB::table('documents')
                ->where('source_type', 'external_url')
                ->where('external_url', $url)
                ->whereNull('deleted_at')
                ->orderByDesc('id')
                ->first();
        }

        if (!$document) {
            $document = DB::table('documents')
                ->where('source_type', 'external_url')
                ->where('title', $title)
                ->where('folder_id', $folderId)
                ->whereNull('deleted_at')
                ->orderByDesc('id')
                ->first();
        }

        $payload = [
            'title' => $title,
            'description' => null,
            'source_type' => 'external_url',
            'external_url' => $url,
            'storage_disk' => null,
            'storage_path' => null,
            'original_name' => $metadata['original_name'],
            'mime_type' => $metadata['mime_type'],
            'extension' => $metadata['extension'],
            'size_bytes' => null,
            'checksum_sha256' => null,
            'folder_id' => $folderId,
            'category_id' => null,
            'owner_id' => $ownerId,
            'status' => 'published',
            'visibility' => 'internal',
            'current_version' => '1.0',
            'version_count' => 1,
            'effective_date' => null,
            'expiry_date' => null,
            'published_at' => $document && !empty($document->published_at) ? $document->published_at : $timestamp,
            'archived_at' => null,
            'is_featured' => false,
            'is_template' => false,
            'is_locked' => false,
            'locked_by_user_id' => null,
            'locked_at' => null,
            'legal_hold' => false,
            'legal_hold_reason' => null,
            'legal_hold_by_user_id' => null,
            'legal_hold_at' => null,
            'download_count' => $document->download_count ?? 0,
            'view_count' => $document->view_count ?? 0,
            'content_indexed_at' => null,
            'content_text' => null,
            'published_roles' => null,
            'expiry_warning_sent_at' => null,
            'grace_period_notification_sent_at' => null,
            'updated_at' => $timestamp,
            'deleted_at' => null,
        ];

        if ($document) {
            DB::table('documents')
                ->where('id', $document->id)
                ->update($payload);

            return (int) $document->id;
        }

        return (int) DB::table('documents')->insertGetId(array_merge($payload, [
            'ulid' => (string) Str::ulid(),
            'created_at' => $timestamp,
        ]));
    }

    public static function refreshFolderStats(int $folderId, $timestamp): void
    {
        DB::table('document_folders')
            ->where('id', $folderId)
            ->update([
                'document_count' => DB::table('documents')
                    ->where('folder_id', $folderId)
                    ->whereNull('deleted_at')
                    ->count(),
                'total_size_bytes' => (int) DB::table('documents')
                    ->where('folder_id', $folderId)
                    ->whereNull('deleted_at')
                    ->sum(DB::raw('COALESCE(size_bytes, 0)')),
                'updated_at' => $timestamp,
            ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    private static function folderCandidates()
    {
        return DB::table('document_folders')
            ->whereIn('name', [self::FOLDER_NAME, self::LEGACY_FOLDER_NAME])
            ->whereNull('parent_id')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();
    }
}
