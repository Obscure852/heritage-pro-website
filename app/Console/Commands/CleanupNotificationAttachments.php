<?php

namespace App\Console\Commands;

use App\Models\Email;
use App\Models\NotificationAttachment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanupNotificationAttachments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup-attachments
                            {--days=30 : Number of days to keep attachments}
                            {--dry-run : Run without actually deleting files}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old notification and email attachments';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Cleaning up attachments older than {$days} days (before {$cutoffDate->toDateString()})");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No files will be deleted');
        }

        // Cleanup email attachments
        $emailStats = $this->cleanupEmailAttachments($cutoffDate, $dryRun);

        // Cleanup notification attachments
        $notificationStats = $this->cleanupNotificationAttachments($cutoffDate, $dryRun);

        // Cleanup orphaned files (files without database records)
        $orphanedStats = $this->cleanupOrphanedFiles($dryRun);

        // Display summary
        $this->newLine();
        $this->info('=== Cleanup Summary ===');
        $this->table(
            ['Category', 'Files Deleted', 'Space Freed'],
            [
                ['Email Attachments', $emailStats['count'], $this->formatBytes($emailStats['size'])],
                ['Notification Attachments', $notificationStats['count'], $this->formatBytes($notificationStats['size'])],
                ['Orphaned Files', $orphanedStats['count'], $this->formatBytes($orphanedStats['size'])],
                ['TOTAL',
                    $emailStats['count'] + $notificationStats['count'] + $orphanedStats['count'],
                    $this->formatBytes($emailStats['size'] + $notificationStats['size'] + $orphanedStats['size'])
                ],
            ]
        );

        if ($dryRun) {
            $this->warn('This was a DRY RUN. No files were actually deleted.');
            $this->info('Run without --dry-run to actually delete files.');
        } else {
            $this->info('Cleanup completed successfully!');
        }

        return Command::SUCCESS;
    }

    /**
     * Clean up email attachments
     *
     * @param Carbon $cutoffDate
     * @param bool $dryRun
     * @return array
     */
    protected function cleanupEmailAttachments(Carbon $cutoffDate, bool $dryRun): array
    {
        $this->info('Scanning email attachments...');

        $oldEmails = Email::where('created_at', '<', $cutoffDate)
            ->whereNotNull('attachment_path')
            ->get();

        $deletedCount = 0;
        $freedSpace = 0;

        $bar = $this->output->createProgressBar($oldEmails->count());
        $bar->start();

        foreach ($oldEmails as $email) {
            if (Storage::exists($email->attachment_path)) {
                $fileSize = Storage::size($email->attachment_path);

                if (!$dryRun) {
                    Storage::delete($email->attachment_path);
                    $email->update(['attachment_path' => null]);
                }

                $deletedCount++;
                $freedSpace += $fileSize;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return ['count' => $deletedCount, 'size' => $freedSpace];
    }

    /**
     * Clean up notification attachments
     *
     * @param Carbon $cutoffDate
     * @param bool $dryRun
     * @return array
     */
    protected function cleanupNotificationAttachments(Carbon $cutoffDate, bool $dryRun): array
    {
        $this->info('Scanning notification attachments...');

        $oldAttachments = NotificationAttachment::whereHas('notification', function ($query) use ($cutoffDate) {
                $query->where('created_at', '<', $cutoffDate);
            })
            ->get();

        $deletedCount = 0;
        $freedSpace = 0;

        $bar = $this->output->createProgressBar($oldAttachments->count());
        $bar->start();

        foreach ($oldAttachments as $attachment) {
            if (Storage::exists($attachment->file_path)) {
                $fileSize = Storage::size($attachment->file_path);

                if (!$dryRun) {
                    Storage::delete($attachment->file_path);
                    $attachment->delete();
                }

                $deletedCount++;
                $freedSpace += $fileSize;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return ['count' => $deletedCount, 'size' => $freedSpace];
    }

    /**
     * Clean up orphaned files (files without database records)
     *
     * @param bool $dryRun
     * @return array
     */
    protected function cleanupOrphanedFiles(bool $dryRun): array
    {
        $this->info('Scanning for orphaned files...');

        $deletedCount = 0;
        $freedSpace = 0;

        // Check email_attachments directory
        $emailFiles = Storage::files('email_attachments');
        $emailPaths = Email::whereNotNull('attachment_path')->pluck('attachment_path')->toArray();

        foreach ($emailFiles as $file) {
            if (!in_array($file, $emailPaths)) {
                $fileSize = Storage::size($file);

                if (!$dryRun) {
                    Storage::delete($file);
                }

                $deletedCount++;
                $freedSpace += $fileSize;
            }
        }

        // Check notification_attachments directory
        $notificationFiles = Storage::files('notification_attachments');
        $notificationPaths = NotificationAttachment::pluck('file_path')->toArray();

        foreach ($notificationFiles as $file) {
            if (!in_array($file, $notificationPaths)) {
                $fileSize = Storage::size($file);

                if (!$dryRun) {
                    Storage::delete($file);
                }

                $deletedCount++;
                $freedSpace += $fileSize;
            }
        }

        return ['count' => $deletedCount, 'size' => $freedSpace];
    }

    /**
     * Format bytes to human-readable size
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
