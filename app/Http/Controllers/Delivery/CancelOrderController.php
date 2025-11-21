<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderCancel;
use App\Models\Wallet;
use App\Traits\FcmNotificationTrait;
use App\Traits\HandleResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CancelOrderController extends Controller
{
    use HandleResponseTrait, FcmNotificationTrait;

    public function getRequests(Request $request){
        $delivery = $request->user();
        $lastOrder = $delivery->orders()
        ->whereNotIn('status', ['completed', 'cancelled_user', 'cancelled_delivery'])
        ->latest()->first();
        $cancel = OrderCancel::where('order_id', $lastOrder->id)->where('status', 'pending')->latest()->first();
        if ($lastOrder && $cancel) {
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    "cancel" => $cancel
                ],
                []
                );
        }
        return $this->handleResponse(
            false,
            __("order.no cancel requests"),
            [],
            [],
            []
        );
    }

    public function respond(Request $request){
        $validator = Validator::make($request->all(), [
            "cancel_request_id" => "required|numeric|exists:order_cancels,id",
            'status' => 'required|in:accepted,rejected',
        ]);

        if($validator->fails()){
            return $this->handleResponse(false, "", [$validator->errors()->first()],[],[]);
        }
        $cancelRequest = OrderCancel::where('id', $request->cancel_request_id)->where('status', 'pending')->first();
        if ($cancelRequest){
            $order = Order::where("id", $cancelRequest->order_id)->whereNotIn('status', ['completed', 'cancelled_user', 'cancelled_delivery'])->first();
            if($order){
            if($request->status == "accepted"){
                //Cancel Order
                $order->status = "cancelled_user";
                $order->save();
                //Set Cancel Request Status
                $cancelRequest->status = "accepted";
                $cancelRequest->save();
                if($order->placeOrder->payment_method == "wallet" && $order->placeOrder->paid == 1){
                    $wallet = Wallet::where('customer_id', $order->placeOrder->customer_id)->first();
                    $wallet->balance += $order->price;
                    $wallet->save();

                    $transaction = $wallet->sender()->where('type', 'pay')->latest()->first();
                    $transaction->status = "failed";
                    $transaction->save();
                }
                $this->sendNotification(
                    $order->customer->fcm_token,
                    "تم قبول طلب الإلغاء",
                    "لقد تم قبول طلب الالغاء الخاص بك",
                    $order->customer->id
                );
                return $this->handleResponse(
                    true,
                    __("order.cancelled"),
                    [],
                    [],
                    []
                );
            } else {
                // Reminder: Send Notification to user

                //Set Request Rejected
                $cancelRequest->status = "rejected";
                $cancelRequest->save();

                return $this->handleResponse(
                    true,
                    __("order.cancel rejected"),
                    [],
                    [],
                    []
                );
            }
            }
        }
        return $this->handleResponse(
            false,
            __("order.no cancel or active order"),
            [],
            [],
            []
        );
    }

    public function sendRequest(Request $request){
        $validator = Validator::make($request->all(), [
            "order_id" => "required|numeric|exists:orders,id",
            "reason" => "required|string|max:1000"
        ]);

        if($validator->fails()){
            return $this->handleResponse(false, "", [$validator->errors()->first()],[],[]);
        }

        $delivery = $request->user();
        $order = Order::where('id', $request->order_id)->whereNotIn('status', ['completed', 'cancelled_user', 'cancelled_delivery'])->first();
        if($order){
        $cancel = OrderCancel::create([
            "order_id" => $request->order_id,
            "delivery_id" => $delivery->id,
            "reason" => $request->reason
        ]);

        $this->sendNotification(
            $order->customer->fcm_token,
            "تلقيت طلب الإلغاء",
            $request->reason,
            $order->customer->id
        );
        return $this->handleResponse(
            true,
            __("order.cancel request sent"),
            [],
            [],
            []
        );
        }
        return $this->handleResponse(
            false,
            __('order.not available'),
            [],
            [],
            []
        );
    }
}
