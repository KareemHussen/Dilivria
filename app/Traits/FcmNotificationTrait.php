<?php

namespace App\Traits;

use App\Models\AppNotification;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;


trait FcmNotificationTrait
{
    protected static function getFcmProjectId()
    {
        return "dillivria-c5524";
    }

    protected static function getFcmCredentialsPath()
    {
        return Storage::path('dillivria-c5524-firebase-adminsdk-3ysmo-b94b32dd67.json');
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
        ])->post("https://fcm.googleapis.com/v1/projects/dillivria-c5524/messages:send", [
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

        return $response->json();
    }

    public function sendNotification($fcmToken, $title, $body, $customer_id = null, $url = null, $method = null)
    {
        try {
            $response = self::sendFcmNotification($fcmToken, $title, $body, $url ?? null, $method ?? null);
            //Save Notification to database
            if($customer_id){
            AppNotification::create([
                'customer_id' => $customer_id,
                "title" => $title,
                "body" => $body
            ]);
            }
            return [
                'success' => true,
                'message' => 'Notification sent successfully',
                'response' => $response,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error processing notification: ' . $e->getMessage()
            ];
        }
    }

}