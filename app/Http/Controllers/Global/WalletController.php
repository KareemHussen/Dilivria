<?php

namespace App\Http\Controllers\Global;

use App\Traits\FcmNotificationTrait;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletRecharge;
use App\Models\WalletWithdrawal;
use App\Traits\HandleResponseTrait;
use App\Traits\SendMailTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class WalletController extends Controller
{
    use HandleResponseTrait, FcmNotificationTrait, SendMailTrait;

    public function setPIN(Request $request){
        try{
        $validator = Validator::make($request->all(), [
            "pin" => [
                "required",
                "numeric",
                "digits:6",
                "confirmed",
                "regex:/^(?!.*(\d)\1{5})(?!123456|234567|345678|456789|567890|987654|876543|765432|654321|543210|111111|222222|333333|444444|555555|666666|777777|888888|999999|000000)\d{6}$/"
                ]
        ],[
            "regex" => __("wallet.pin must complex")
        ]);

        if($validator->fails()){
            return $this->handleResponse(false,"",[$validator->errors()->first()],[],
            [
                "مينفعش ترتيب ارقام او نفس الرقم 6 مرات"
            ]);
        }

        $user = $request->user();

        $user->pin = Hash::make($request->pin);
        $user->save();

        return $this->handleResponse(
            true,
            __("wallet.pin set"),
            [],
            [],
            []
        );
        } catch (\Exception $e){
            return $this->handleResponse(
                false,
                __("wallet.server error"),
                [
                    $e->getMessage()
                ],
                [],
                []
            );
        }
    }
    public function sendCode(Request $request) {

        $user = $request->user();


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

    public function checkCode(Request $request) {
        $validator = Validator::make($request->all(), [
            "code" => ["required", "digits:4"],
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




        $user = $request->user();
        $code = $request->code;


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

    }


    public function get(Request $request){
        $user = $request->user();
        $wallet = Wallet::where('customer_id', $user->id)
        ->select('id', 'customer_id', 'balance')
        ->first();
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "wallet" => $wallet
            ],
            []
        );
    }

    public function getRechargesAll(Request $request){
        $user = $request->user();
        $wallet = $user->wallet()->first();
        $recharges = $wallet->recharges()->get();
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "recharge_requests" => $recharges
            ],
            []
        );
    }
    public function getRechargesPaginate(Request $request){
        $user = $request->user();
        $perPage = $request->per_page ?: 10; 
        $wallet = $user->wallet()->first();
        $recharges = $wallet->recharges()->paginate($perPage);
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "recharge_requests" => $recharges
            ],
            []
        );
    }

    public function getSenderAll(Request $request){
        $user = $request->user();
        $wallet = $user->wallet()->first();
        $sent = $wallet->sender()->get();
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "sent_money" => $sent
            ],
            []
        );
    }
    public function getSenderPaginate(Request $request){
        $user = $request->user();
        $perPage = $request->per_page ?: 10; 
        $wallet = $user->wallet()->first();
        $sent = $wallet->sender()->paginate($perPage);
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "sent_money" => $sent
            ],
            []
        );
    }

    public function getReceiverAll(Request $request){
        $user = $request->user();
        $wallet = $user->wallet()->first();
        $received = $wallet->receiver()->get();
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "received_money" => $received
            ],
            []
        );
    }
    public function getReceiverPaginate(Request $request){
        $user = $request->user();
        $perPage = $request->per_page ?: 10; 
        $wallet = $user->wallet()->first();
        $receiver = $wallet->receiver()->paginate($perPage);
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "received_money" => $receiver
            ],
            []
        );
    }

    public function deposit(Request $request){
        try{
        $validator = Validator::make($request->all(), [
            "photo" => 'required|image|mimes:jpeg,png,jpg,gif',
            "payment_type" => 'required|in:vodafone_cash,instapay',
            "payment_number" => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->payment_type == 'vodafone_cash') {
                        // Validate Egyptian phone number (01X followed by 8 digits)
                        if (!preg_match('/^01[0-2,5]{1}[0-9]{8}$/', $value)) {
                            $fail('Payment number must be a valid Egyptian phone number (e.g., 01017046725) for Vodafone Cash');
                        }
                    } elseif ($request->payment_type == 'instapay') {
                        // Validate Instapay address (starts with @ followed by at least 3 alphanumeric chars)
                        if (!preg_match('/^@[a-zA-Z0-9_]{3,}$/', $value)) {
                            $fail('Payment number must be a valid Instapay address starting with @ (e.g., @test123)');
                        }
                    }
                },
            ]
        ]);

        if($validator->fails()){
            return $this->handleResponse(false,"",[$validator->errors()->first()],[],[]);
        }

        $user = $request->user();
        $wallet = $user->wallet()->first();

        // Store in recharges folder directly without the extra 'storage' directory
        $imagePath = $request->file('photo')->store('recharges', 'public');
        $recharge = WalletRecharge::create([
            "wallet_id" => $wallet->id,
            "photo" => $imagePath,
            "payment_type" => $request->payment_type,
            "payment_number" => $request->payment_number
        ]);

        return $this->handleResponse(
            true,
            __("wallet.deposit sent"),
            [],
            [
                "recharge" => $recharge
            ],
            []
        );
    } catch (\Exception $e) {
        return $this->handleResponse(
            false,
            __("wallet.server error"),
            [
                // $e->getMessage()
            ],
            [],
            []
        );
    }
    }

    public function getCustomerByPhone(Request $request){
        $validator = Validator::make($request->all(), [
            "phone" => "required|string|numeric|digits:11|exists:customers,phone"
        ],[
            "phone.exists" => __("wallet.phone not registered")
        ]);

        if($validator->fails()){
            return $this->handleResponse(false,"",[$validator->errors()->first()],[],[]);
        }

        $customer = Customer::where('phone', $request->phone)
        ->select('id', 'first_name', 'last_name', 'username','phone')
        ->first();
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "customer" => $customer
            ],
            [
             "تقدر تستخدمها لو عايز تعرض اسم المستخدم الذي سيتم التحويل اليه، للتأكيد من ان الرقم صحيح"
            ]
        );
    }

    public function transfer(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                "phone" => "required|string|numeric|digits:11|exists:customers,phone",
                "amount" => "required|numeric",
                "pin" => "required|numeric|digits:6"
            ],[
                "phone.exists" => __("wallet.phone not registered")
            ]);

            if($validator->fails()){
                return $this->handleResponse(false,"",[$validator->errors()->first()],[],[]);
            }

            $user = $request->user();

            if (!Hash::check($request->pin, $user->pin)) {
                return $this->handleResponse(
                    false,
                    __("wallet.invalid pin"),
                    [],
                    [],
                    []
                );
            }

            $sender = $user->wallet()->first();
            if($sender->balance < $request->amount){
                return $this->handleResponse(
                    false,
                    __("wallet.no enough balance"),
                    [],
                    [],
                    []
                );
            }
            $receiverUser = Customer::where('phone', $request->phone)->first();
            $receiver = $receiverUser->wallet()->first();

            $transaction = Transaction::create([
                'sender' => $sender->id,
                'receiver' => $receiver->id,
                'amount' => $request->amount,
                'type' => 'transfer',
            ]);

            //Take amount from sender
            $sender->balance -= $transaction->amount;
            $sender->save();
            //Give amount to receiver
            $receiver->balance += $transaction->amount;
            $receiver->save();
            //Set transaction completed
            $transaction->status = "completed";
            $transaction->save();
            //Send Notification after success
            //Sender
            $this->sendNotification(
                $user->fcm_token,
                "تم ارسال مبلغ " . $transaction->amount . " جنيه بنجاح",
                "لقد قمت بارسال مبلغ " . $transaction->amount . " جنيه بنجاح الى " . $receiverUser->phone . " في تاريخ " . $transaction->created_at,
                $user->id
            );
            //Receiver
             $this->sendNotification(
                $receiverUser->fcm_token,
                "تم ارستقبال مبلغ " . $transaction->amount . " جنيه بنجاح",
                "تم استقبال مبلغ " . $transaction->amount . " جنيه بنجاح من " . $user->phone . " في تاريخ " . $transaction->created_at,
                $receiverUser->id
            );

            return $this->handleResponse(
                true,
                __("wallet.send money success"),
                [],
                [
                    "wallet" => $sender->only('id', 'customer_id', 'balance'),
                    "transaction" => $transaction
                ],
                []
            );
            
        } catch (\Exception $e){
            return $this->handleResponse(
                false,
                __("wallet.server error"),
                [
                    // $e->getMessage()
                ],
                [],
                []
            );
        }
    }

    public function withdraw(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                "amount" => 'required|numeric|min:1',
                "payment_type" => 'required|in:vodafone_cash,instapay',
                "payment_number" => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->payment_type == 'vodafone_cash') {
                            // Validate Egyptian phone number (01X followed by 8 digits)
                            if (!preg_match('/^01[0-2,5]{1}[0-9]{8}$/', $value)) {
                                $fail('Payment number must be a valid Egyptian phone number (e.g., 01017046725) for Vodafone Cash');
                            }
                        } elseif ($request->payment_type == 'instapay') {
                            // Validate Instapay address (starts with @ followed by at least 3 alphanumeric chars)
                            if (!preg_match('/^@[a-zA-Z0-9_]{3,}$/', $value)) {
                                $fail('Payment number must be a valid Instapay address starting with @ (e.g., @test123)');
                            }
                        }
                    },
                ],
                "pin" => "required|numeric|digits:6"
            ]);

            if($validator->fails()){
                return $this->handleResponse(false,"",[$validator->errors()->first()],[],[]);
            }

            $user = $request->user();
            
            if (!Hash::check($request->pin, $user->pin)) {
                return $this->handleResponse(
                    false,
                    __("wallet.invalid pin"),
                    [],
                    [],
                    []
                );
            }

            $wallet = $user->wallet()->first();

            if($wallet->balance < $request->amount){
                return $this->handleResponse(
                    false,
                    __("wallet.no enough balance"),
                    [],
                    [],
                    []
                );
            }

            $withdrawal = WalletWithdrawal::create([
                "wallet_id" => $wallet->id,
                "amount" => $request->amount,
                "payment_type" => $request->payment_type,
                "payment_number" => $request->payment_number,
                "status" => "pending"
            ]);

            return $this->handleResponse(
                true,
                __("wallet.withdrawal request sent"),
                [],
                [
                    "withdrawal" => $withdrawal
                ],
                []
            );
        } catch (\Exception $e) {
            return $this->handleResponse(
                false,
                __("wallet.server error"),
                [
                    $e->getMessage()
                ],
                [],
                []
            );
        }
    }

    public function getWithdrawalsAll(Request $request){
        $user = $request->user();
        $wallet = $user->wallet()->first();
        $withdrawals = $wallet->withdrawals()->get();
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "withdrawal_requests" => $withdrawals
            ],
            []
        );
    }

    public function getWithdrawalsPaginate(Request $request){
        $user = $request->user();
        $perPage = $request->per_page ?: 10; 
        $wallet = $user->wallet()->first();
        $withdrawals = $wallet->withdrawals()->paginate($perPage);
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "withdrawal_requests" => $withdrawals
            ],
            []
        );
    }

}
