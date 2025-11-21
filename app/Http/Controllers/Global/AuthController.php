<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Traits\HandleResponseTrait;
use App\Traits\SendMailTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthController extends Controller
{
    use HandleResponseTrait, SendMailTrait;
    
    public function askEmailCode(Request $request) {
        $user = $request->user();
        if ($user) {
            $code = rand(1000, 9999);

            $user->last_otp = Hash::make($code);
            $user->last_otp_expire = Carbon::now()->addMinutes(10)->timezone('Africa/Cairo');
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
                $user->last_otp_expire = Carbon::now()->addMinutes(10)->timezone('Africa/Cairo');
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
        $credentials = $request->only('phone', 'password');
        if (Auth::guard('customer')->attempt(['phone' => $request->phone, 'password' => $request->password])) {
            $user = Auth::guard('customer')->user();
            $token = $user->createToken('token')->plainTextToken;
            $userType = $user->delivery ? "delivery" : "customer";

            if($request->fcm_token){
                $user->fcm_token = $request->fcm_token;
                $user->save();
            }


        }else  {
                return $this->handleResponse(
                false,
                "",
                [__('registration.Invalid Credentials')],
                [],
                []
            );
        }


        // return response()->json(compact('token'));
        return $this->handleResponse(
            true,
            __("registration.You are Loged In"),
            [],
            [
               "token" => $token,
               "user_type" => $userType
            ],
            []
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
