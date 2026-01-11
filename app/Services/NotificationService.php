<?php

namespace App\Services;

use App\Models\Queue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public static function sendQueueCalledNotification(Queue $queue)
    {
        $user = $queue->patient->user;
        
        if (!$user->fcm_token) {
            return;
        }

        $title = 'Nomor Antrian Dipanggil';
        $body = "Nomor antrian Anda ({$queue->queue_number}) di {$queue->department->name} sedang dipanggil. Silakan menuju ruangan.";

        self::sendFCM($user->fcm_token, $title, $body, [
            'queue_id' => $queue->id,
            'type' => 'queue_called',
        ]);
    }

    public static function sendQueueReminderNotification(Queue $queue)
    {
        $user = $queue->patient->user;
        
        if (!$user->fcm_token) {
            return;
        }

        $title = 'Pengingat Antrian Besok';
        $body = "Anda memiliki antrian besok di {$queue->department->name} dengan nomor {$queue->queue_number}. Jangan lupa datang!";

        self::sendFCM($user->fcm_token, $title, $body, [
            'queue_id' => $queue->id,
            'type' => 'queue_reminder',
        ]);
    }

    private static function sendFCM($token, $title, $body, $data = [])
    {
        $serverKey = env('FCM_SERVER_KEY');
        
        if (!$serverKey) {
            Log::warning('FCM_SERVER_KEY not configured');
            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                ],
                'data' => $data,
                'priority' => 'high',
            ]);

            if (!$response->successful()) {
                Log::error('FCM send failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('FCM exception', ['error' => $e->getMessage()]);
        }
    }
}
