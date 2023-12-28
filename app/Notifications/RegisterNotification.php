<?php

namespace App\Notifications;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisterNotification extends Notification
{
    use Queueable;
    public $user;
    /**
     * Create a new notification instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
        info($this->user);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     $verificationUrl = $this->verificationUrl($notifiable);

    //     return (new MailMessage)
    //         ->line('Hello ' . $this->user->name)
    //         ->line('Welcome to our application. Please click the button below to verify your email address.')
    //         ->action('Verify Email Address', $verificationUrl)
    //         ->line('Thank you for using our application!');
    // }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
        ->line('Hello '. $this->user->name )
                    ->line('Welcome ')
                     ->action('Verification', url(env('FRONTEND_URL')))
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
            'username' => $this->user->name
        ];
    }
}
