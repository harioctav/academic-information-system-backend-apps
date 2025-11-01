<?php

namespace App\Channels;

use App\Helpers\WhatsAppHelper;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
  /**
   * Send the given notification.
   */
  public function send(object $notifiable, Notification $notification): void
  {
    if (method_exists($notification, 'toWhatsApp')) {
      // Get phone number from notifiable
      $phone = $notifiable->routeNotificationForWhatsApp();

      if (!$phone) {
        Log::warning('WhatsApp notification skipped: no phone number', [
          'notifiable_type' => get_class($notifiable),
          'notifiable_id' => $notifiable->id ?? null,
        ]);
        return;
      }

      // Call the notification's toWhatsApp method which will send the message
      $notification->toWhatsApp($notifiable);
    }
  }
}
