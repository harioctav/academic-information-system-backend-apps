<?php

namespace App\Notifications\Evaluations\Recommendations;

use App\Enums\NotificationType;
use App\Enums\UserRole;
use App\Helpers\Helper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecommendationUpdated extends Notification
{
  use Queueable;

  /**
   * Create a new notification instance.
   */
  public function __construct(
    protected $recommendation,
    protected $student,
    protected $updater,
    protected $oldNote,
    protected $newNote
  ) {
    //
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
    $updaterRole = UserRole::from($this->updater->roles->first()->name)->label();

    $message = "{$this->updater->name} ({$updaterRole}) telah mengubah status rekomendasi matakuliah {$this->recommendation->subject->name} untuk mahasiswa {$this->student->name} ({$this->student->nim}) dari {$this->oldNote} menjadi {$this->newNote}";

    return [
      'recommendation_id' => $this->recommendation->id,
      'student_name' => $this->student->name,
      'student_nim' => $this->student->nim,
      'subject_name' => $this->recommendation->subject->name,
      'updater_name' => $this->updater->name,
      'updater_role' => $updaterRole,
      'old_note' => $this->oldNote,
      'new_note' => $this->newNote,
      'title' => "Update Rekomendasi",
      'notification_type' => NotificationType::Academic->value,
      'message' => $message . " pada " . Helper::formatIndonesianDate(now())
    ];
  }
}
