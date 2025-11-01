<?php

namespace App\Notifications\Finances\Registrations;

use App\Helpers\WhatsAppHelper;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class StudentSubmitRegistration extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * Determine if this notification should be queued.
   * Can be controlled via .env: WA_NOTIFICATION_QUEUE=false to disable queueing for testing
   */
  public function shouldQueue(): bool
  {
    // Allow disabling queue for this specific notification via config (for testing only)
    // Set WA_NOTIFICATION_QUEUE=false in .env to run synchronously
    return config('services.whatsapp.use_queue', true);
  }

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
    return ['whatsapp'];
  }

  /**
   * Send WhatsApp notification to student
   */
  public function toWhatsApp(object $notifiable): void
  {
    try {
      // Ensure relationships are loaded
      if (!$this->registration->relationLoaded('student')) {
        $this->registration->load('student');
      }
      if (!$this->registration->relationLoaded('registrationBatch')) {
        $this->registration->load('registrationBatch');
      }

      $student = $this->registration->student;

      if (!$student || !$student->phone) {
        Log::warning('WhatsApp notification skipped: student or phone missing', [
          'registration_id' => $this->registration->id,
          'student_id' => $student->id ?? null,
          'phone' => $student->phone ?? null,
        ]);
        return;
      }

      $batchName = $this->registration->registrationBatch->name ?? 'Pendaftaran';

      $message = "Terima kasih {$student->name}, Anda telah berhasil mengisi form pendaftaran {$batchName}.\n\n";
      $message .= "Untuk informasi pembayaran akan diinfokan lagi. Terima kasih.";

      $result = WhatsAppHelper::sendMessage($student->phone, $message);

      if (!$result['success']) {
        Log::error('WhatsApp sending failed', [
          'registration_id' => $this->registration->id,
          'student_id' => $student->id,
          'phone' => $student->phone,
          'error' => $result['message'] ?? 'Unknown error',
        ]);
      }
    } catch (\Exception $e) {
      Log::error('WhatsApp notification exception', [
        'registration_id' => $this->registration->id ?? null,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
    }
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
    return [
      //
    ];
  }
}
