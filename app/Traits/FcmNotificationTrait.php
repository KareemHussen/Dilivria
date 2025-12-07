<?php

namespace App\Traits;

use App\Models\AppNotification;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


trait FcmNotificationTrait
{
    protected static function getFcmProjectId()
    {
        return "dilivria-41882";
    }

    protected static function getFcmCredentialsPath()
    {
        return Storage::path("dilivria-41882-69a17e7c32ab.json");
    }

    protected static function getGoogleClient()
    {
        $client = new GoogleClient();
        $client->setAuthConfig(self::getFcmCredentialsPath());
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        return $client;
    }

    protected static function getAccessToken()
    {
        $client = self::getGoogleClient();
        $token = $client->getAccessToken();
        return $token['access_token'];
    }

    protected static function sendFcmNotification($fcmToken, $title, $body, $url = null, $method = null)
    {
        $projectId = self::getFcmProjectId();
        $accessToken = self::getAccessToken();

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type' => 'application/json',
        ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                // 'data' => [
                //     'url' => $url ?? null,
                //     'method' => $method ?? null
                // ]
            ],
        ]);

        Log::info('FCM response:', ['response' => $response]);

        return $response->json();
    }

    public function sendNotification($fcmToken, $title, $body, $customer_id = null, $url = null, $method = null)
    {
        try {
            $response = self::sendFcmNotification($fcmToken, $title, $body, $url ?? null, $method ?? null);
            //Save Notification to database
            Log::info('FCM response:', ['response' => $response]);

            if($customer_id){
            AppNotification::create([
                'customer_id' => $customer_id,
                "title" => $title,
                "body" => $body
            ]);
            }
            Log::info('Notification sent successfully:', ['response' => $response]);
            return [
                'success' => true,
                'message' => 'Notification sent successfully',
                'response' => $response,
            ];
        } catch (\Exception $e) {
            Log::error('Error processing notification:', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Error processing notification: ' . $e->getMessage()
            ];
        }
    }

}