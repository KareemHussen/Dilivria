<?php

namespace App\Traits;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

trait SendSMSTrait
{
    private $baseWhatsUrl = 'https://app.hypersender.com/api/whatsapp/v1';
    private $baseSMSUrl = 'https://app.hypersender.com/api/sms/v1';
    private $apiKey;
    private $instanceId;
    
    /**
     * Initialize the trait properties
     */
    private function initializeHypersender()
    {
        $this->apiKey = Config::get('hypersender-config.sms_api_key', env('HYPERSENDER_SMS_API_KEY'));
        $this->SMSInstanceId = Config::get('hypersender-config.sms_instance_id', env('HYPERSENDER_SMS_INSTANCE_ID'));
        $this->WhatsAppInstanceId = Config::get('hypersender-config.whatsapp_instance_id', env('HYPERSENDER_WHATSAPP_INSTANCE_ID'));

    }

    /**
     * Send OTP via SMS using Hypersender
     *
     * @param string $to Phone number to send SMS to
     * @param string $otp OTP code to send
     * @return array Response with status and message
     */

    public function sendSMS($to, $otp)
    {
        try {
            // Initialize Hypersender configuration
            $this->initializeHypersender();
            // Format the phone number for international format if needed
            $to = $this->formatPhoneNumber($to);
            
            // Create message content with OTP
            $message = "Your OTP code is {$otp}";
            
            // Send SMS using Hypersender
            $messageId = Str::uuid();
            
            
            // Make direct HTTP request with bearer token authentication
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->withToken($this->apiKey)
              ->post("{$this->baseSMSUrl}/{$this->SMSInstanceId}/send-message", [
                'content' => $message,
                'request_id' => $messageId,
                'to' => $to,
            ]);
                
            $responseData = $response->json();
            
            if ($response->successful()) {
                Log::info('SMS sent successfully', ['response' => $responseData]);
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'messageId' => $responseData['messageId'] ?? 'unknown'
                ];
            } else {
                Log::error('Failed to send SMS', ['status' => $response->status(), 'body' => $response->body()]);
                return [
                    'success' => false,
                    'message' => 'Failed to send SMS: ' . $response->body()
                ];
            }
        } catch (Exception $e) {
            Log::error('Failed to send SMS', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Send OTP via WhatsApp using Hypersender
     *
     * @param string $to Phone number to send WhatsApp message to
     * @param string $otp OTP code to send
     * @return array Response with status and message
     */
    public function sendWhatsAppOTP($to, $otp)
    {
        try {
            // Initialize Hypersender configuration
            $this->initializeHypersender();
            // Format the phone number for international format
            $to = $this->formatPhoneNumber($to);
            
            // Create message content with OTP
            $message = "Your OTP code is {$otp}";
            
            // Make direct HTTP request with bearer token authentication
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->withToken($this->apiKey)
              ->post("{$this->baseWhatsUrl}/{$this->WhatsAppInstanceId}/send-text-safe", [
                'text' => $message,
                'chatId' => $to.'@c.us',
              ]);
                
            $responseData = $response->json();
            
            if ($response->successful()) {
                Log::info('WhatsApp OTP sent successfully', ['response' => $responseData]);
                return [
                    'success' => true,
                    'message' => 'WhatsApp OTP sent successfully',
                    'messageId' => $responseData['messageId'] ?? 'unknown'
                ];
            } else {
                Log::error('Failed to send WhatsApp OTP', ['status' => $response->status(), 'body' => $response->body()]);
                return [
                    'success' => false,
                    'message' => 'Failed to send WhatsApp OTP: ' . $response->body()
                ];
            }
           
        } catch (Exception $e) {
            Log::error('Failed to send WhatsApp OTP', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to send WhatsApp OTP: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Format phone number to international format
     * 
     * @param string $phoneNumber The phone number to format
     * @return string Formatted phone number
     */
    protected function formatPhoneNumber($phoneNumber)
    {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        
        // Add the plus sign at the beginning
        return "2".$phoneNumber;
    }
}
