<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Order;
use App\Traits\FcmNotificationTrait;
use App\Traits\HandleResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\SendMessageEvent;

class MessageController extends Controller
{
    use HandleResponseTrait, FcmNotificationTrait;

    public function send(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                "message" => "required|string|max:1000"
            ]);

            if($validator->fails()){
                return $this->handleResponse(
                    false,"",[$validator->errors()->first()],[],[]
                );
            }

            $customer = $request->user();
            $order = Order::whereHas('placeOrder', function($query) use($customer){
                $query->where('customer_id', $customer->id);
            })->whereNotIn('status', ['completed', 'cancelled_user', 'cancelled_delivery'])->latest()->first();
            if(!$order){
                return $this->handleResponse(
                    false,
                    __("order.no ongoing"),
                    [],
                    [],
                    []
                );
            }
            
            // Check if order is completed
            if($order->status === 'completed' || $order->status === 'cancelled_user' || $order->status === 'cancelled_delivery'){
                return $this->handleResponse(
                    false,
                    __("Chat is not available as the order has been completed"),
                    [],
                    [],
                    []
                );
            }
            $message = Message::create([
                "order_id" => $order->id,
                "sender_type" => "customer",
                "sender_id" => $customer->id,
                "message" => $request->message
            ]);

            $this->sendNotification(
                $order->delivery->fcm_token,
                "تلقيت رسالة من " . $customer->first_name,
                $request->message,
                $order->delivery_id
            );
            broadcast(new SendMessageEvent($message))->toOthers();

            return $this->handleResponse(
                true,
                __('order.message sent'),
                [],
                [],
                []
            );
        } catch (\Exception $e){
            return $this->handleResponse(
                false,
                __('wallet.server error'),
                [
                    // $e->getMessage()
                ],
                [],
                []
            );
        }
    }

    public function get(Request $request){
        $customer = $request->user();

        $order = Order::whereHas('placeOrder', function($query) use($customer){
            $query->where('customer_id', $customer->id);
        })->whereNotIn('status', ['completed', 'cancelled_user', 'cancelled_delivery'])->latest()->first();
        if($request->order_id){
            $order = Order::findOrFail($request->order_id);
        }
        if(!$order){
            return $this->handleResponse(
                false,
                __("order.no ongoing"),
                [],
                [],
                []
            );
        }
        
        // Check if order is completed - if so, no messages should be available
        if($order->status === 'completed' || $order->status === 'cancelled_user' || $order->status === 'cancelled_delivery'){
            return $this->handleResponse(
                false,
                __("Chat ended as the order has been completed"),
                [],
                [
                    "messages" => []
                ],
                []
            );
        }
        
        $messages = Message::where('order_id', $order->id)->orderBy('created_at', 'asc')->get();
        
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "messages" => $messages
            ],
            []
        );
    }
}
