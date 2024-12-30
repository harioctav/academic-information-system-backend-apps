<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Auth\Notifications\ResetPassword;

class ResetPasswordNotification extends ResetPassword implements ShouldQueue
{
  use Queueable;

  /**
   * Create a new notification instance.
   */
  public function __construct($token)
  {
    parent::__construct($token);
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param  mixed  $notifiable
   * @return array<int, string>
   */
  public function via($notifiable): array
  {
    return ['mail'];
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param  mixed  $notifiable
   */
  public function toMail($notifiable): MailMessage
  {
    $url = url(config('app.frontend_url') . '/auth/reset-password?' . http_build_query([
      'token' => $this->token,
      'email' => $notifiable->email,
    ]));

    return (new MailMessage)
      ->subject('Reset Password Notification')
      ->greeting('Hello!')
      ->line('You are receiving this email because we received a password reset request for your account.')
      ->action('Reset Password', $url)
      ->line('This password reset link will expire in ' . config('auth.passwords.users.expire') . ' minutes.')
      ->line('If you did not request a password reset, no further action is required.')
      ->salutation('Regards, ' . config('app.name'));
  }
}
