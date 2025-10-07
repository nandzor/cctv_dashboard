<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\LoggingService;

class WhatsAppHelper {
    public static function sendMessage(
        string $phoneNumber,
        string $message,
        ?string $imagePath = null,
        array $metadata = []
    ): array {
        $startTime = microtime(true);

        try {
            $formattedPhone = self::formatPhoneNumber($phoneNumber);
            $apiUrl = env('WHATSAPP_API_URL');
            $apiKey = env('WHATSAPP_API_KEY');

            if (!$apiUrl || !$apiKey) {
                throw new \Exception('WhatsApp API configuration missing');
            }

            $payload = [
                'phone' => $formattedPhone,
                'message' => $message,
            ];

            if ($imagePath) {
                $payload['image'] = $imagePath;
            }

            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                ->post($apiUrl, $payload);

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            // Log to daily file
            LoggingService::logWhatsAppMessage([
                'event_log_id' => $metadata['event_log_id'] ?? null,
                'branch_id' => $metadata['branch_id'],
                'device_id' => $metadata['device_id'],
                're_id' => $metadata['re_id'] ?? null,
                'phone_number' => $formattedPhone,
                'message_text' => $message,
                'image_path' => $imagePath,
                'status' => $response->successful() ? 'sent' : 'failed',
                'provider_response' => array_merge($response->json() ?? [], [
                    'execution_time_ms' => $executionTime,
                    'http_status' => $response->status()
                ]),
                'error_message' => $response->failed() ? $response->body() : null,
                'retry_count' => 0
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->successful() ? 'sent' : 'failed',
                'response' => $response->json(),
                'execution_time' => $executionTime . 'ms'
            ];
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('WhatsApp send failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'execution_time' => $executionTime . 'ms'
            ]);

            LoggingService::logWhatsAppMessage([
                'event_log_id' => $metadata['event_log_id'] ?? null,
                'branch_id' => $metadata['branch_id'],
                'device_id' => $metadata['device_id'],
                're_id' => $metadata['re_id'] ?? null,
                'phone_number' => $phoneNumber,
                'message_text' => $message,
                'image_path' => $imagePath,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'retry_count' => 0
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'execution_time' => $executionTime . 'ms'
            ];
        }
    }

    public static function formatPhoneNumber(string $phone): string {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add country code if not present (Indonesia +62)
        if (!str_starts_with($phone, '62')) {
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            } else {
                $phone = '62' . $phone;
            }
        }

        return $phone;
    }
}
