<?php

namespace App\Notifications\Evaluations\Recommendations;

use App\Enums\NotificationType;
use App\Enums\UserRole;
use App\Helpers\Helper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class RecommendationDeleted extends Notification
{
  use Queueable;

  /**
   * Create a new notification instance.
   */
  public function __construct(
    protected Collection $recommendations,
    protected $student,
    protected $creator
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
    $subjectCount = $this->recommendations->count();
    $subjectList = $this->recommendations->map(function ($rec) {
      return $rec->subject->name;
    })->join(', ');

    $creatorRole = UserRole::from($this->creator->roles->first()->name)->label();

    $message = $subjectCount === 1
      ? "{$this->creator->name} ({$creatorRole}) telah menghapus rekomendasi matakuliah {$subjectList} untuk mahasiswa {$this->student->name} ({$this->student->nim})"
      : "{$this->creator->name} ({$creatorRole}) telah menghapus {$subjectCount} rekomendasi matakuliah ({$subjectList}) untuk mahasiswa {$this->student->name} ({$this->student->nim})";

    return [
      'student_name' => $this->student->name,
      'subject_count' => $subjectCount,
      'subjects' => $subjectList,
      'creator_name' => $this->creator->name,
      'creator_role' => $creatorRole,
      'title' => "Hapus Rekomendasi",
      'notification_type' => NotificationType::Academic->value,
      'message' => $message . " pada " . Helper::formatIndonesianDate(now())
    ];
  }
}
