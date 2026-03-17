<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class SendTrialReminders extends Command
{
    protected $signature = 'subscriptions:send-trial-reminders';

    protected $description = 'Send reminder emails to users with expiring trials';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $reminderDays = config('subscription.trial_reminder_days', [3, 1, 0]);

        $totalSent = 0;

        foreach ($reminderDays as $days) {
            $users = $subscriptionService->getUsersWithExpiringTrials($days);

            foreach ($users as $user) {
                $subscriptionService->sendTrialExpiringNotification($user, $days);
                $totalSent++;

                $this->line("Sent {$days}-day reminder to: {$user->email}");
            }
        }

        $this->info("Sent {$totalSent} trial reminder emails.");

        return self::SUCCESS;
    }
}
