<?php

namespace App\Notifications\Finances\Registrations;

use App\Enums\NotificationType;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StudentRegistrationSubmitted extends Notification
{
  use Queueable;

  /**
   * Create a new notification instance.
   */
  public function __construct(
    protected Registration $registration
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
   * Get the array representation of the notification.
   *
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    $student = $this->registration->student;
    $batchName = $this->registration->registrationBatch->name ?? 'Pendaftaran';
    
    return [
      'registration_id' => $this->registration->id,
      'registration_number' => $this->registration->registration_number,
      'student_id' => $student->id,
      'student_nim' => $student->nim,
      'student_name' => $student->name,
      'batch_name' => $batchName,
      'title' => 'Mahasiswa Mengisi Pendaftaran',
      'notification_type' => NotificationType::System->value,
      'message' => "Mahasiswa {$student->name} (NIM: {$student->nim}) telah mengisi form pendaftaran {$batchName} dengan nomor pendaftaran {$this->registration->registration_number}."
    ];
  }
}

