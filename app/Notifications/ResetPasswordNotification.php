<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $mailMessage = new MailMessage();
        $mailMessage->markdown('emails.signup');

        return $mailMessage
        ->subject('Password reset')
        ->line('You have requested a password reset.')
        ->line('Here is your 6-digit code: ' . $this->code)
        ->line('This code will expire in 15 minutes.')
        ->line('If you did not request this password reset, you can ignore this email.')
        ->line('Thank you for using our application!');
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $notifiable->id,
            'message' => 'You have requested a password reset. Here is your 6-digit code: **' . $this->code . '**.'
        ];
    }
}
