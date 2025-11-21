<?php

namespace App\Http\Controllers\Delivery;

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


    public function register(Request $request) {
        try {
        $validator = Validator::make($request->all(), [
            "full_name" => ['required', "string", 'regex:/^([A-Za-zÀ-ÖØ-öø-ÿ]+(?:\s+[A-Za-zÀ-ÖØ-öø-ÿ]+){3})$/'],
            "username"=> ['required', 'unique:customers,username'],
            "national_id" => ['required', 'string', 'numeric' ,'digits:14'],
            'email' => ['nullable','email'],
            'phone' => ['required',
            'string','unique:customers,phone', 'numeric', 'digits:11'],
            'id_front'=> ['required','image'],
            'id_back'=> ['required','image'],
            'selfie'=> ['required','image'],
            'password' => ['required',
            'string','min:8',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W])[A-Za-z\d\W]+$/u',
            'confirmed'],
            'fcm_token'=> ['nullable','string']
        ], [
            "password.regex" => __('validation.regex'),
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
        $explode = explode(' ', $request->full_name);
        $firstName = $explode[0];
        $lastName = $explode[1];
        $user = Customer::create([
            'full_name' => $request->full_name,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username'=> $request->username,
            "national_id" => $request->national_id,
            "id_front" => $request->file('id_front')->store('/storage/docs', 'public'),
            "id_back" => $request->file('id_back')->store('/storage/docs', 'public'),
            "selfie" => $request->file('selfie')->store('/storage/docs', 'public'),
            'email' => $request->email,
            'phone'=> $request->phone,
            'password' => Hash::make($request->password),
            "delivery" => 1,
            "delivery_status" => "waiting",
            'fcm_token'=> $request->fcm_token ?? null
        ]);

        if ($user) {
            $code = rand(1000, 9999);

            $user->last_otp = Hash::make($code);
            $user->last_otp_expire = Carbon::now()->addMinutes(10)->timezone('Africa/Cairo');
            $user->save();
            $message = __("registration.Your Authentication Code is") . $code;

            // $this->sendEmail($user->email,"OTP", $message);
        }


        $token = $user->createToken('token')->plainTextToken;




        return $this->handleResponse(
            true,
            __('registration.signed up successfully'),
            [],
            [
                "user" => $user,
                "token" => $token,
                // "fcm_token"=> $user->fcm_token
            ],
            []
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

