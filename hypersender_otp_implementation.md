# Hypersender OTP Implementation Guide

## Overview
This document provides instructions for implementing OTP verification using Hypersender for both SMS and WhatsApp in your Laravel application.

## Changes Required

### 1. Update SendSMSTrait.php

The `SendSMSTrait.php` file has been updated to use Hypersender for sending OTPs via both SMS and WhatsApp:

```php
<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Log;
use Hypersender\Facades\Sms;
use Hypersender\Facades\Whatsapp;

trait SendSMSTrait
{
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
            // Format the phone number for international format if needed
            $to = $this->formatPhoneNumber($to);
            
            // Create message content with OTP
            $message = "Your verification code is {$otp}. This code expires in 3 minutes.";
            
            // Send SMS using Hypersender
            $response = Sms::to($to)
                ->message($message)
                ->send();
                
            if ($response->successful()) {
                Log::info('SMS sent successfully', ['messageId' => $response->messageId]);
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'messageId' => $response->messageId
                ];
            } else {
                Log::error('Failed to send SMS', ['error' => $response->error]);
                return [
                    'success' => false,
                    'message' => 'Failed to send SMS: ' . ($response->error ?? 'Unknown error')
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
            // Format the phone number for international format
            $to = $this->formatPhoneNumber($to);
            
            // Send WhatsApp message with OTP
            // Using text template with OTP parameter
            $response = Whatsapp::to($to)
                ->template('otp_verification')
                ->params([
                    'otp' => $otp,
                    'expiration_time' => '10 minutes' // Optional parameter if your template includes it
                ])
                ->send();
                
            if ($response->successful()) {
                Log::info('WhatsApp OTP sent successfully', ['messageId' => $response->messageId]);
                return [
                    'success' => true,
                    'message' => 'WhatsApp OTP sent successfully',
                    'messageId' => $response->messageId
                ];
            } else {
                Log::error('Failed to send WhatsApp OTP', ['error' => $response->error]);
                return [
                    'success' => false,
                    'message' => 'Failed to send WhatsApp OTP: ' . ($response->error ?? 'Unknown error')
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
        
        // If the number doesn't start with country code (e.g., for Egypt it's 20)
        // add it. This is assuming Egyptian numbers by default
        if (substr($phoneNumber, 0, 2) !== '20') {
            $phoneNumber = '20' . $phoneNumber;
        }
        
        // Add the plus sign at the beginning
        return '+' . $phoneNumber;
    }
}
```

### 2. Update Login Method in Global AuthController

Update the login method in `AuthController.php` to use the new OTP methods:

```php
// Generate OTP code
$code = rand(1000, 9999);

$user->last_otp = Hash::make($code); // Store hashed OTP
$user->last_otp_expire = Carbon::now()->addMinutes(3)->timezone('Africa/Cairo');

if($request->fcm_token) {
    $user->fcm_token = $request->fcm_token;
}

$user->save();

// Send OTP via SMS using Hypersender
$smsResult = $this->sendSMS($user->phone, $code);

// Optionally send via WhatsApp too if preferred channel is WhatsApp
$preferWhatsApp = $request->channel === 'whatsapp';
if ($preferWhatsApp) {
    $whatsappResult = $this->sendWhatsAppOTP($user->phone, $code);
}
```

### 3. Update Registration Method in Customer AuthController

Update the registration method to use OTP verification before granting access:

```php
if ($user) {
    // Generate OTP code
    $code = rand(1000, 9999);

    // Store hashed OTP
    $user->last_otp = Hash::make($code);
    $user->last_otp_expire = Carbon::now()->addMinutes(3)->timezone('Africa/Cairo');
    $user->verified = false; // Set user as unverified until OTP is confirmed
    $user->save();
    
    // Send OTP via SMS using Hypersender
    $smsResult = $this->sendSMS($user->phone, $code);
    
    // Optionally send via WhatsApp too if preferred channel is WhatsApp
    $preferWhatsApp = $request->channel === 'whatsapp';
    if ($preferWhatsApp) {
        $whatsappResult = $this->sendWhatsAppOTP($user->phone, $code);
    }
}
```

### 4. Add Verify Registration OTP Method to Customer AuthController

Add this method to verify registration OTP:

```php
/**
 * Verify registration OTP
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function verifyRegisterOtp(Request $request) {
    $validator = Validator::make($request->all(), [
        "phone" => ["required"],
        "code" => ["required"],
    ], [
        "required" => __('validation.required')
    ]);

    if ($validator->fails()) {
        return $this->handleResponse(
            false,
            "",
            [$validator->errors()->first()],
            [],
            []
        );
    }

    $user = Customer::where("phone", $request->phone)->first();
    $code = $request->code;

    if (!$user) {
        return $this->handleResponse(
            false,
            "",
            [__('registration.you are not registered')],
            [],
            []
        );
    }

    if (!Hash::check($code, $user->last_otp ? $user->last_otp : Hash::make('0000'))) {
        return $this->handleResponse(
            false,
            "",
            [__('registration.incorrect code')],
            [],
            []
        );
    } 
    
    $timezone = 'Africa/Cairo';
    $verificationTime = new Carbon($user->last_otp_expire, $timezone);
    
    if ($verificationTime->isPast()) {
        return $this->handleResponse(
            false,
            "",
            [__('registration.this code is expired')],
            [],
            []
        );
    }

    // OTP is valid, mark user as verified and generate token
    $user->verified = true;
    $user->save();
    
    $token = $user->createToken('token')->plainTextToken;
    $userType = $user->delivery ? "delivery" : "customer";

    return $this->handleResponse(
        true,
        __("registration.account verified successfully"),
        [],
        [
           "user" => $user,
           "token" => $token,
           "user_type" => $userType,
           "fcm_token"=> $user->fcm_token
        ],
        []
    );
}
```

### 5. Update Routes in customerAPI.php

Add a route for the new verify registration OTP method:

```php
Route::prefix('customer')->group(function () {
    //Auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-register-otp', [AuthController::class, 'verifyRegisterOtp']);

    // ... existing routes ...
});
```

### 6. Configure Hypersender in .env

Add the following environment variables to your `.env` file:

```
# Hypersender WhatsApp Configuration
HYPERSENDER_WHATSAPP_API_KEY=your_whatsapp_api_key
HYPERSENDER_WHATSAPP_INSTANCE_ID=your_whatsapp_instance_id
HYPERSENDER_WHATSAPP_WEBHOOK_AUTHORIZATION_SECRET=your_webhook_secret

# Hypersender SMS Configuration
HYPERSENDER_SMS_API_KEY=your_sms_api_key
HYPERSENDER_SMS_INSTANCE_ID=your_sms_instance_id
HYPERSENDER_SMS_WEBHOOK_AUTHORIZATION_SECRET=your_webhook_secret
```

## WhatsApp Template Setup

For WhatsApp OTP messages, you need to create an approved template in your WhatsApp Business account. The template should be named 'otp_verification' and include placeholders for the OTP code and expiration time.

Example template:
"Your verification code is {{1}}. This code will expire in {{2}}."

## Testing

1. Test registration flow by sending a POST request to `/customer/register`
2. You should receive an OTP via SMS or WhatsApp
3. Use that OTP to verify your account by sending a POST request to `/customer/verify-register-otp`
4. Similar process for login using `/login` and `/verify-login-otp`
