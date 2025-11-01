<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppHelper
{
  /**
   * Send WhatsApp message to a phone number
   * 
   * @param string $phone Phone number (with country code, e.g., 6281234567890)
   * @param string $message Message content
   * @return array Response array with success status and message
   */
  public static function sendMessage(string $phone, string $message): array
  {
    try {
      // Always use config() first (recommended Laravel way)
      // Clear config cache if needed to ensure fresh config in queue workers
      $apiUrl = config('services.whatsapp.api_url');
      $apiKey = config('services.whatsapp.api_key');
      $apiToken = config('services.whatsapp.api_token');

      // Fallback: If config is null, try to reload from env directly (for queue workers)
      if (!$apiUrl || !$apiKey) {
        // Force reload config by clearing cache if in queue context
        if (app()->runningInQueue()) {
          config(['services.whatsapp.api_url' => env('WA_API_URL')]);
          config(['services.whatsapp.api_key' => env('WA_API_KEY')]);
          config(['services.whatsapp.api_token' => env('WA_API_TOKEN')]);

          $apiUrl = config('services.whatsapp.api_url');
          $apiKey = config('services.whatsapp.api_key');
          $apiToken = config('services.whatsapp.api_token');
        }

        // Still null? Log detailed debug info
        if (!$apiUrl || !$apiKey) {
          $configCheck = [
            'config_api_url' => config('services.whatsapp.api_url'),
            'config_api_key' => config('services.whatsapp.api_key') ? '***SET***' : null,
            'env_wa_api_url' => env('WA_API_URL') ?: 'null',
            'env_wa_api_key' => env('WA_API_KEY') ? '***SET***' : 'null',
            'running_in_queue' => app()->runningInQueue(),
            'config_cached' => app()->configurationIsCached(),
          ];

          Log::warning('WhatsApp API configuration missing', $configCheck);

          return [
            'success' => false,
            'message' => 'WhatsApp API configuration tidak ditemukan. Pastikan WA_API_URL dan WA_API_KEY sudah di-set di .env dan jalankan: php artisan config:cache'
          ];
        }
      }

      // Format phone number (remove leading + or 0, ensure starts with country code)
      $phone = preg_replace('/^\+/', '', $phone);
      $phone = preg_replace('/^0/', '', $phone);
      if (!str_starts_with($phone, '62')) {
        $phone = '62' . $phone;
      }

      // Watzap.id API format according to documentation
      // https://api.watzap.id/v1/send_message
      $payload = [
        'api_key' => $apiKey,
        'number_key' => $apiToken, // WA_API_TOKEN is used as number_key
        'phone_no' => $phone, // Note: phone_no (not phone)
        'message' => $message,
        'wait_until_send' => '1', // Wait until message is sent
      ];

      $headers = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
      ];

      $response = Http::withHeaders($headers)->post($apiUrl, $payload);

      // Watzap.id returns status 200 with JSON body
      if ($response->successful()) {
        $responseData = $response->json();

        // Check if response indicates success
        // Format: {"status": "200", "message": "Successfully", ...}
        if (isset($responseData['status']) && $responseData['status'] == '200') {
          return [
            'success' => true,
            'message' => 'WhatsApp berhasil dikirim',
            'data' => $responseData
          ];
        }

        // If status is not 200, treat as error
        Log::warning('WhatsApp API returned non-success status', [
          'response' => $responseData,
          'phone' => $phone,
        ]);

        return [
          'success' => false,
          'message' => $responseData['message'] ?? 'Gagal mengirim WhatsApp',
          'data' => $responseData
        ];
      }

      Log::error('WhatsApp API request failed', [
        'status' => $response->status(),
        'response' => $response->body(),
        'phone' => $phone,
      ]);

      return [
        'success' => false,
        'message' => 'Gagal mengirim WhatsApp: ' . $response->body(),
        'status' => $response->status()
      ];
    } catch (\Exception $e) {
      Log::error('WhatsApp sending exception', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'phone' => $phone ?? null,
      ]);

      return [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }
}
