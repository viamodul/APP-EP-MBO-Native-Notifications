<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UsageThresholdReached extends Notification implements ShouldQueue
{
    use Queueable;

    protected int $threshold;

    public function __construct(int $threshold)
    {
        $this->threshold = $threshold;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tierName = $notifiable->getTierName();
        $limit = $notifiable->getWebhooksLimit();
        $used = $notifiable->webhooks_sent_this_period;
        $remaining = $notifiable->getRemainingWebhooks();

        $message = (new MailMessage)
            ->subject($this->getSubject())
            ->greeting("Hello {$notifiable->name},");

        if ($this->threshold === 100) {
            $message
                ->line("You've reached 100% of your webhook limit for this billing period.")
                ->line("**Used:** {$used} / {$limit} webhooks")
                ->line('New order notifications are currently paused until your billing period resets or you upgrade your plan.')
                ->action('Upgrade Your Plan', route('billing.index'))
                ->line('Upgrading will immediately increase your limits and resume webhook delivery.');
        } else {
            $message
                ->line("You've used {$this->threshold}% of your monthly webhook limit.")
                ->line("**Current usage:** {$used} / {$limit} webhooks")
                ->line("**Remaining:** {$remaining} webhooks")
                ->line('Consider upgrading your plan to ensure uninterrupted service.')
                ->action('View Billing', route('billing.index'));
        }

        return $message
            ->line("Your current plan: **{$tierName}**");
    }

    protected function getSubject(): string
    {
        if ($this->threshold === 100) {
            return 'Webhook Limit Reached - Action Required';
        }

        return "Webhook Usage Alert: {$this->threshold}% of limit used";
    }

    public function toArray(object $notifiable): array
    {
        return [
            'threshold' => $this->threshold,
            'webhooks_sent' => $notifiable->webhooks_sent_this_period,
            'webhooks_limit' => $notifiable->getWebhooksLimit(),
        ];
    }
}
