<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
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
    
    public function askEmailCode(Request $request) {
        $user = $request->user();
        if ($user) {
            $code = rand(1000, 9999);

            $user->last_otp = Hash::make($code);
            $user->last_otp_expire = Carbon::now()->addMinutes(3)->timezone('Africa/Cairo');
            $user->save();

            $message = __("registration.Your Authentication Code is") . $code;
            $this->sendEmail($user->email,"Dsito otp", $message);
            return $this->handleResponse(
                true,
                __('registration.auth code sent'),
                [],
                [],
                [
                    "code get expired after 10 minuts",
                    "the same endpoint you can use for ask resend email"
                ]
            );
        }

        return $this->handleResponse(
            false,
            "",
            [__("registration.sorry couldn't send your code")],
            [],
            [
                "code get expired after 10 minuts",
                "the same endpoint you can use for ask resend email"
            ]
        );
    }

    public function verifyEmail(Request $request) {
        $validator = Validator::make($request->all(), [
            "code" => ["required"],
        ], [
            "code.required" => __('validation.required'),
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

        $user = $request->user();
        $code = $request->code;

        if ($user) {
            if (!Hash::check($code, $user->last_otp ? $user->last_otp : Hash::make(0000))) {
                return $this->handleResponse(
                    false,
                    "",
                    [__('registration.incorrect code')],
                    [],
                    []
                );
            } else {
                $timezone = 'Africa/Cairo'; // Replace with your specific timezone if different
                $verificationTime = new Carbon($user->last_otp_expire, $timezone);
                if ($verificationTime->isPast()) {
                    return $this->handleResponse(
                        false,
                        "",
                        [__('registration.this code is expired')],
                        [],
                        []
                    );
                } else {
                    $user->verified = true;
                    $user->save();



                    if ($user) {
                        return $this->handleResponse(
                            true,
                            __('registration.code verified'),
                            [],
                            [],
                            []
                        );
                    }
                }
            }
        }
    }

    public function changePassword(Request $request) {
        $validator = Validator::make($request->all(), [
            "old_password" => 'required',
            'password' => 'required|string|min:8|
            regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/u
            |confirmed',
            ], [
            "password.regex" => __('validation.regex'),
            "required"=> __('validation.required'),
            "string"=> __('validation.string'),
            "min"=> __('validation.min.string'),
            "confirmed"=> __('validation.confirmed')
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

        $user = $request->user();
        $old_password = $request->old_password;

        if ($user) {
            if (!Hash::check($old_password, $user->password)) {
                return $this->handleResponse(
                    false,
                    "",
                    [__("registration.current password is invalid")],
                    [],
                    []
                );
            }
            if($old_password == $request->password){
                return $this->handleResponse(
                    false,
                    __("registration.new password can't match the old password"),
                    [],
                    [],
                    []
                );
            }

            $user->password = Hash::make($request->password);
            $user->save();

            return $this->handleResponse(
                true,
                __("registration.password changed successfully"),
                [],
                [],
                []
            );
        }
    }

    public function sendForgetPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            "phone" => 'required|',
        ],[
            "required"=> __('validation.required'),
            "regex"=> __('validation.regex')
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


            if ($user) {
                $code = rand(1000, 9999);

                $user->last_otp = Hash::make($code);
                $user->last_otp_expire = Carbon::now()->addMinutes(3)->timezone('Africa/Cairo');
                $user->save();
    
    
                $message = __("registration.Your Authentication Code is") . $code;

                $this->sendEmail($user->email,"OTP", $message);
    
    
                return $this->handleResponse(
                    true,
                    __("registration.auth code sent"),
                    [],
                    [],
                    [
                        "code get expired after 10 minuts",
                        "the same endpoint you can use for ask resend email"
                    ]
                );
            }
            else {
                return $this->handleResponse(
                    false,
                    "",
                    [__("registration.you are not registered")],
                    [],
                    []
                );
            }
    }
    public function forgetPasswordCheckCode(Request $request) {
        $validator = Validator::make($request->all(), [
            "phone" => ["required"],
            "code" => ["required"],
        ], [
            "required"=> __('validation.required'),
            "regex"=> __('validation.regex')
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


        if ($user) {
            if (!Hash::check($code, $user->last_otp ? $user->last_otp : Hash::make(0000))) {
                return $this->handleResponse(
                    false,
                    "",
                    [__("registration.incorrect code")],
                    [],
                    []
                );
            } else {
                $timezone = 'Africa/Cairo'; // Replace with your specific timezone if different
                $verificationTime = new Carbon($user->last_otp_expire, $timezone);
                if ($verificationTime->isPast()) {
                    return $this->handleResponse(
                        false,
                        "",
                        [__("registration.this code is expired")],
                        [],
                        []
                    );
                } else {
                    if ($user) {
                        return $this->handleResponse(
                            true,
                            __("registration.code verified"),
                            [],
                            [],
                            []
                        );
                    }
                }
            }
        } else {
            return $this->handleResponse(
                false,
                "",
                [__("registration.you are not registered")],
                [],
                []
            );
        }


    }

    public function forgetPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            "phone" => ["required"],
            'password' => [
                'required', // Required only if joined_with is 1
                'min:8',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/u',
                'confirmed'
            ],
        ], [
            "email.required" => __('validation.required'),
            "password.required" => __('validation.required'),
            "password.min" => __('validation.min.string'),
            "password.regex" => __('validation.regex'),
            "password.confirmed" => __('validation.confirmed'),
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


        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();


            if ($user) {
                return $this->handleResponse(
                    true,
                    __("registration.password changed successfully"),
                    [],
                    [],
                    []
                );
            }
        }
        else {
            return $this->handleResponse(
                false,
                "",
                [__("registration.you are not registered")],
                [],
                []
            );
        }


    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'regex:/^01[0-2,5]\d{8}$/'],
            'channel' => ['required' , 'string' , 'in:whatsapp,sms'],
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

        if (!$user) {
            return $this->handleResponse(
                false,
                "",
                [__('registration.you are not registered')],
                [],
                []
            );
        }

        // Generate OTP code
        $code = rand(1000, 9999);

        $user->last_otp = $code;
        $user->last_otp_expire = Carbon::now()->addMinutes(3)->timezone('Africa/Cairo');
        
        $user->save();
        
        // Optionally send via WhatsApp too if preferred channel is WhatsApp
        $preferWhatsApp = $request->channel === 'whatsapp';
        if ($preferWhatsApp) {
            $whatsappResult = $this->sendWhatsAppOTP($user->phone, $code);
        }else {
            $smsResult = $this->sendSMS($user->phone, $code);
        }

        return $this->handleResponse(
            true,
            __('registration.auth code sent to your phone') . $code,
            [],
            [],
            [
                __("registration.code expires in 3 minutes"),
                __("registration.verify otp to login")
            ]
        );
    }

    public function updateFCM(Request $request) {
        $validator = Validator::make($request->all(), [
            "fcm_token" => ["required"],
        ], [
            "fcm_token.required" => __('validation.required'),
    
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

        $user = $request->user();

        if ($user) {
            $user->fcm_token = $request->fcm_token;
            $user->save();

            return $this->handleResponse(
                true,
                __("registration.fcm updated successfully"),
                [],
                [],
                []
            );
        } else {
            return $this->handleResponse(
                false,
                "",
                [__("registration.you are not registered")],
                [],
                []
            );
        }


    }

    public function verifyLoginOtp(Request $request) {
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

        // OTP is valid, log the user in
        $token = $user->createToken('token')->plainTextToken;
        $userType = $user->delivery ? "delivery" : "customer";

        return $this->handleResponse(
            true,
            __("registration.You are Logged In"),
            [],
            [
               "token" => $token,
               "user_type" => $userType
            ],
            []
        );
    }

    public function askOtp(Request $request) {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'regex:/^01[0-2,5]\d{8}$/'],
            'channel' => ['required', 'string', 'in:whatsapp,sms'],
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
        
        if (!$user) {
            return $this->handleResponse(
                false,
                "",
                [__('registration.you are not registered')],
                [],
                []
            );
        }

        // Generate OTP code
        $code = rand(1000, 9999);

        $user->last_otp = Hash::make($code);
        $user->last_otp_expire = Carbon::now()->addMinutes(3)->timezone('Africa/Cairo');
        
        if($request->fcm_token) {
            $user->fcm_token = $request->fcm_token;
        }
        
        $user->save();
        
        // Optionally send via WhatsApp too if preferred channel is WhatsApp
        $preferWhatsApp = $request->channel === 'whatsapp';
        if ($preferWhatsApp) {
            $whatsappResult = $this->sendWhatsAppOTP($user->phone, $code);
        } else {
            $smsResult = $this->sendSMS($user->phone, $code);
        }

        return $this->handleResponse(
            true,
            __('registration.auth code sent to your phone'),
            [],
            [],
            [
                __("registration.code expires in 3 minutes")
            ]
        );
    }

    public function logout(Request $request) {
        $user = $request->user();

        if ($user) {
            if ($user->tokens())
                $user->tokens()->delete();
        }
        $user->fcm_token = null;
        $user->save();


        return $this->handleResponse(
            true,
            __("registration.logged out"),
            [],
            [
            ],
            [
                "On logout" => "كل التوكينز بتتمسح انت كمان امسحها من الكاش عندك"
            ]
        );
    }
}
