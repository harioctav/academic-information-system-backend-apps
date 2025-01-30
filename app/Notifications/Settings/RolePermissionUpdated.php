<?php

namespace App\Notifications\Settings;

use App\Enums\NotificationType;
use App\Enums\UserRole;
use App\Models\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RolePermissionUpdated extends Notification
{
  use Queueable;

  /**
   * Create a new notification instance.
   */
  public function __construct(
    protected Role $role
  ) {
    $this->role = $role;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    return ['database'];
  }

  /**
   * Get the mail representation of the notification.
   */
  public function toMail(object $notifiable): MailMessage
  {
    return (new MailMessage)
      ->line('The introduction to the notification.')
      ->action('Notification Action', url('/'))
      ->line('Thank you for using our application!');
  }

  /**
   * Get the array representation of the notification.
   *
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    $name = UserRole::from($this->role->name)->label();

    return [
      'role_id' => $this->role->id,
      'role_name' => $name,
      'title' => "Perubahan Hak Akses",
      'notification_type' => NotificationType::Permission->value,
      'message' => "Hak akses untuk peran {$name} telah diperbarui. Silahkan lakukan Refresh untuk memperbarui Hak Akses anda."
    ];
  }
}
