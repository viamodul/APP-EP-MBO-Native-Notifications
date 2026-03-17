<?php

namespace App\Console\Commands;

use App\Models\WebhookLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupWebhookLogs extends Command
{
    protected $signature = 'webhooks:cleanup
                            {--days= : Number of days to retain logs (default from config)}
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Delete webhook logs older than the retention period';

    public function handle(): int
    {
        $days = $this->option('days') ?? config('services.epages.webhook_log_retention_days', 20);
        $dryRun = $this->option('dry-run');

        $cutoffDate = now()->subDays($days);

        $query = WebhookLog::where('created_at', '<', $cutoffDate);
        $count = $query->count();

        if ($count === 0) {
            $this->info('No webhook logs older than ' . $days . ' days found.');
            return 0;
        }

        if ($dryRun) {
            $this->info("[DRY RUN] Would delete {$count} webhook logs older than {$days} days.");
            return 0;
        }

        // Delete in chunks to avoid memory issues with large datasets
        $deleted = 0;
        $chunkSize = 1000;

        while (true) {
            $affected = WebhookLog::where('created_at', '<', $cutoffDate)
                ->limit($chunkSize)
                ->delete();

            if ($affected === 0) {
                break;
            }

            $deleted += $affected;
            $this->line("Deleted {$deleted} of {$count} logs...");
        }

        $this->info("Successfully deleted {$deleted} webhook logs older than {$days} days.");

        Log::info('Webhook logs cleanup completed', [
            'deleted_count' => $deleted,
            'retention_days' => $days,
            'cutoff_date' => $cutoffDate->toDateTimeString(),
        ]);

        return 0;
    }
}
