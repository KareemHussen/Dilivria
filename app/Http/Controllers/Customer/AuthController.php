<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\RegistrationOtp;
use App\Traits\HandleResponseTrait;
use App\Traits\SendMailTrait;
use App\Traits\SendSMSTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthController extends Controller
{
    use HandleResponseTrait, SendMailTrait, SendSMSTrait;


    public function register(Request $request) {
        try {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required','string','max:255'],
            'last_name' => ['required','string','max:255'],
            "username"=> ['required', 'unique:customers,username'],
            'email' => ['nullable','email'],
            'phone' => ['required','string','unique:customers,phone' , 'regex:/^01[0-2,5]\d{8}$/'],
            'fcm_token'=> ['required','string'],
            'channel'=> ['required','string' , 'in:whatsapp,sms']
        ], [
            "required"=> __('validation.required'),
            "string"=> __('validation.string'),
            "max"=> __('validation.max.string'),
            "email"=> __('validation.email'),
            "unique"=> __('validation.unique'),
            "numeric"=> __('validation.numeric'),
            "regex"=> __('validation.regex'),
            "confirmed"=> __('validation.confirmed'),
            "phone.regex"=> __('validation.regex')

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

        $user = Customer::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'full_name' => $request->first_name . " " . $request->last_name,
            'username'=> $request->username,
            'email' => $request->email,
            'phone'=> $request->phone,
            'fcm_token'=> $request->fcm_token
        ]);

        if ($user) {
            $code = rand(1000, 9999);

            $user->last_otp = $code;
            $user->last_otp_expire = Carbon::now()->addMinutes(3)->timezone('Africa/Cairo');
            $user->save();
            $message = __("registration.Your Authentication Code is");

            // Optionally send via WhatsApp too if preferred channel is WhatsApp
            $preferWhatsApp = $request->channel === 'whatsapp';
            if ($preferWhatsApp) {
                $whatsappResult = $this->sendWhatsAppOTP($user->phone, $code);
            }else {
                $smsResult = $this->sendSMS($user->phone, $code);
            }

            // $this->sendEmail($user->email,"OTP", $message);
        }


        // $token = $user->createToken('token')->plainTextToken;


        return $this->handleResponse(
            true,
            __('registration.signed up successfully'),
            [],
            [],
            [
                __("registration.code expires in 3 minutes"),
                __("registration.verify otp to login")
            ]
        );


        } catch (\Exception $e) {
            return $this->handleResponse(
                false,
                // __('strings.error_signup'),
                "",
                [
                    $e->getMessage()
                ],
                [],
                []
            );
        }


    } 

}

