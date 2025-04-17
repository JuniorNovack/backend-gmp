<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResendPinRegisterUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $newPin;

    public function __construct($newPin)
    {
        $this->newPin = $newPin;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = new MailMessage();
        $mailMessage->markdown('emails.signup');

        return $mailMessage
            ->subject('New pin verify your account')
            ->line('You have requested a New pin verify your account.')
            ->line('Here is your 6-digit code: **' . $this->newPin . '**.')
            ->line('This code will expire in 15 minutes.')
            ->line('If you did not request this New pin verify your account, you can ignore this email.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id'=> $notifiable->id,
            'message'=> 'Here is your 6-digit code: **' . $this->newPin . '**.'
        ];
    }
}
