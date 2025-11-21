<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PlaceOrder;
use App\Traits\HandleResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use HandleResponseTrait;

    public function getActive(Request $request){
        $user = $request->user();
        $lastOrder = PlaceOrder::where('customer_id', $user->id)
        ->whereHas('order', function ($query){
            $query->whereNotIn('status', ['completed', 'cancelled_user', 'cancelled_delivery']);

        })->with(['order', 'order.delivery' => function ($q){
            $q->select( "id","full_name", "phone", "delivery_rate","lng", "lat",);
        }])->latest()->first();
        if($lastOrder){
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    "active_order" => $lastOrder
                ],
                []
            );
        }
        return $this->handleResponse(
            true,
            __("order.no ongoing"),
            [],
            [],
            []
        );
    }

    public function getLast(Request $request){
        $user = $request->user();
        $lastOrder = PlaceOrder::where('customer_id', $user->id)
        ->whereHas('order', function ($query){
            $query->where('status', 'completed');

        })->with(['order', 'order.delivery' => function ($q){
            $q->select( "id","full_name", "phone", "delivery_rate","lng", "lat",);
        }])->latest()->first();
        if($lastOrder){
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    "last_order" => $lastOrder->order
                ],
                [
                    "اخر طلب مكتمل عشان تاخد منه رقم الاوردر للتقييم"
                ]
            );
        }
        return $this->handleResponse(
            false,
            __("order.last not completed"),
            [],
            [],
            []
        );
    }

    public function rate(Request $request){
        $validator = Validator::make($request->all(), [
            "rate" => "required|in:1,2,3,4,5",
            "order_id" => "required|exists:orders,id"
        ]);

        if($validator->fails()){
            return $this->handleResponse(false, "", [$validator->errors()->first()],[],[]);
        }

        $order = Order::where('id', $request->order_id)->where('status', 'completed')->first();

        if($order){
            $order->rate_delivery = $request->rate;
            $order->save();
            $driver = Customer::findOrFail($order->delivery_id);
            $averageRating = Order::where('delivery_id', $driver->id)
                 ->whereNotNull('rate_delivery') // Only include orders that have been rated
                 ->avg('rate_delivery');
         
                // Update the driver's rate column with the average rating
                $driver->delivery_rate = $averageRating ?? 0; // Set 0 if no ratings exist
                $driver->save();
            
            return $this->handleResponse(
                true,
                __("order.rate sent"),
                [],
                [
                    "order" => $order
                ],
                []
            );
        }
        return $this->handleResponse(
            false,
            __("order.can't rate this order"),
            [],
            [],
            ["العميل ميقدرش يقيم الطلبات غير المكتملة (ميقدرش يقيم طلب ملغي او قيد التنفيذ)"]
        );
    }
}
