<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendFcmpushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $title;
    protected $body;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $title, $body, $data = [])
    {
        $this->userId = $userId;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);

        if (!$user || empty($user->expo_push_token)) {
            return; // Push token yoksa iptal
        }

        $token = $user->expo_push_token;

        // Expo Push API Payload
        $message = [
            'to' => $token,
            'sound' => 'default',
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
        ];

        try {
            $response = Http::post('https://exp.host/--/api/v2/push/send', $message);

            if (!$response->successful()) {
                Log::error('Expo Push Notification Failed', [
                    'user_id' => $this->userId,
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Expo Push Notification Exception', [
                'user_id' => $this->userId,
                'message' => $e->getMessage()
            ]);
        }
    }
}
