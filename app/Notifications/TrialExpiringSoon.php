<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialExpiringSoon extends Notification implements ShouldQueue
{
    use Queueable;

    protected int $daysRemaining;

    public function __construct(int $daysRemaining)
    {
        $this->daysRemaining = $daysRemaining;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->getSubject())
            ->greeting("Hello {$notifiable->name},");

        if ($this->daysRemaining === 0) {
            $message
                ->line('Your free trial expires **today**.')
                ->line('To continue receiving webhook notifications for your ePages shop orders, please upgrade to a paid plan.')
                ->line('After your trial expires:')
                ->line('- New order notifications will be paused')
                ->line('- Your shop connections will remain intact')
                ->line('- You can upgrade at any time to resume service');
        } elseif ($this->daysRemaining === 1) {
            $message
                ->line('Your free trial expires **tomorrow**.')
                ->line("You've been using the webhook notification service to receive real-time order updates. Don't let it stop!");
        } else {
            $message
                ->line("Your free trial expires in **{$this->daysRemaining} days**.")
                ->line('We hope you\'ve been enjoying the webhook notification service for your ePages shop.');
        }

        $webhooksUsed = $notifiable->webhooks_sent_this_period;
        if ($webhooksUsed > 0) {
            $message->line("During your trial, you've received **{$webhooksUsed} webhook notifications**.");
        }

        return $message
            ->action('Choose a Plan', route('billing.index'))
            ->line('Plans start at just EUR 5/month. Pick the one that fits your needs.');
    }

    protected function getSubject(): string
    {
        if ($this->daysRemaining === 0) {
            return 'Your Trial Expires Today';
        }

        if ($this->daysRemaining === 1) {
            return 'Your Trial Expires Tomorrow';
        }

        return "Your Trial Expires in {$this->daysRemaining} Days";
    }

    public function toArray(object $notifiable): array
    {
        return [
            'days_remaining' => $this->daysRemaining,
        ];
    }
}
