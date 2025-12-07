<?php

namespace App\Http\Controllers\Delivery;

use App\Traits\FcmNotificationTrait;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Message;
use App\Models\Order;
use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Traits\HandleResponseTrait;
use Illuminate\Support\Facades\Validator;
use App\Models\PlaceOrder;
use App\Models\Setting;
use App\Models\Transaction;
use Carbon\Carbon;

class OrderController extends Controller
{
    use HandleResponseTrait, FcmNotificationTrait;

    public function nearbyOrders(Request $request){
        $delivery = $request->user();
        $setting = Setting::firstOrFail();
        $distanceLimit = $setting->delivery_coverage;
        $earthRadius = 6371; // Radius of Earth in kilometers

        $placeOrders = PlaceOrder::select('*')
        ->selectRaw("($earthRadius * ACOS(
            COS(RADIANS(?)) * COS(RADIANS(lat_from)) *
            COS(RADIANS(lng_from) - RADIANS(?)) +
            SIN(RADIANS(?)) * SIN(RADIANS(lat_from))
        )) AS distance", [$delivery->lat, $delivery->lng, $delivery->lat])
        ->having('distance', '<=', $distanceLimit)
        ->where('status', 'pending')
        ->whereNot('customer_id', $delivery->id)
        ->with(['customer' => function ($query) {
            $query->select('id','first_name', 'last_name','username', 'phone', 'customer_rate');
        }])->orderBy('distance')
        ->get();

        if(count($placeOrders) > 0){
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    "nearby_orders" => $placeOrders
                ],
                []
            );
        }
        return $this->handleResponse(
            true,
            __("order.no nearby orders"),
            [],
            [],
            []
        );
    }

    public function accept(Request $request){
        $validator = Validator::make($request->all(), [
            "place_order_id" => "required|exists:place_orders,id"
        ]);
        if($validator->fails()){
            return $this->handleResponse(false, "", [$validator->errors()->first()],[],[]);
        }

        $placeOrder = PlaceOrder::findOrFail($request->place_order_id);
        $delivery = $request->user();
        $lastOrder = $delivery->orders()->
        whereNotIn('status', ['completed', 'cancelled_user', 'cancelled_delivery'])
        ->latest()->first();
        if ($lastOrder){
            return $this->handleResponse(
                false,
                __("order.many at the moment"),
                [],
                [],
                []
            );
        }
        //Check placed order status (must be pending)
        if($placeOrder->status == "pending"){
            $order = Order::create([
                "place_order_id" => $placeOrder->id,
                "delivery_id" => $delivery->id,
                "price" => $placeOrder->price
            ]);
            $placeOrder->update(["status" => "accepted"]);
            if($placeOrder->payment_method == "wallet"){
                $wallet = Wallet::where('customer_id', $placeOrder->customer_id)->first();
                $wallet->balance -= $placeOrder->price;
                $wallet->save();
                $placeOrder->paid = 1;
                $placeOrder->save();

                $receiver = $delivery->wallet()->first();
                Transaction::create([
                    "sender" => $wallet->id,
                    "receiver" => $receiver->id,
                    "amount" => $order->price,
                    "type" => "pay"
                ]);
            }
            $order->load("placeOrder");

            $notifyCustomer = $this->sendNotification(
                $placeOrder->customer->fcm_token,
                "لقد وجدت رجل توصيل!",
                "قبل " . $delivery->first_name . " طلبك",
                $placeOrder->customer->id               
            );
            return $this->handleResponse(
                true,
                __("order.start order"),
                [],
                [
                    "order" => $order,
                ],
                []
            );
        }
        return $this->handleResponse(
            false,
            __("order.not available"),
            [],
            [],
            []
        );

    }

    public function getActive(Request $request){
        $delivery = $request->user();
        $lastOrder = $delivery->orders()
        ->whereNotIn('status', ['completed', 'cancelled_user', 'cancelled_delivery'])
        ->with(['placeOrder.customer' => function ($query) {
            $query->select('id','first_name', 'last_name','username', 'phone', 'customer_rate');
        }])
        ->latest()->first();
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
        $delivery = $request->user();
        $lastOrder = $delivery->orders()
        ->where('status', 'completed')
        ->with(['placeOrder.customer' => function ($query) {
            $query->select('id','first_name', 'last_name','username', 'phone', 'customer_rate');
        }])
        ->latest()->first();
        if($lastOrder){
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    "last_order" => $lastOrder
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

    public function setFirstPoint(Request $request){
        $delivery = $request->user();
        $lastOrder = $delivery->orders()
        ->whereNotIn('status', ['completed', 'cancelled_user', 'cancelled_delivery'])
        ->with('placeOrder')
        ->latest()->first();
        if($lastOrder){
            $lastOrder->status = "first_point";
            $lastOrder->save();
            return $this->handleResponse(
                true,
                __("order.status updated"),
                [],
                [$lastOrder],
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
    public function setReceived(Request $request){
        $delivery = $request->user();
        $lastOrder = $delivery->orders()
        ->whereNotIn('status', ['completed', 'cancelled_user', 'cancelled_delivery'])
        ->with('placeOrder')
        ->latest()->first();
        if($lastOrder){
            $lastOrder->status = "received";
            $lastOrder->save();
            return $this->handleResponse(
                true,
                __("order.status updated"),
                [],
                [$lastOrder],
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
    public function setSecPoint(Request $request){
        $delivery = $request->user();
        $lastOrder = $delivery->orders()
        ->whereNotIn('status', ['completed', 'cancelled_user', 'cancelled_delivery'])
        ->with('placeOrder')
        ->latest()->first();
        if($lastOrder){
            $lastOrder->status = "sec_point";
            $lastOrder->save();
            return $this->handleResponse(
                true,
                __("order.status updated"),
                [],
                [$lastOrder],
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
    public function setCompleted(Request $request){
        $delivery = $request->user();
        $setting = Setting::firstOrFail();

        $wallet = Wallet::where('customer_id', $delivery->id)->first();
        $lastOrder = $delivery->orders()
        ->whereNotIn('status', ['completed', 'cancelled_user', 'cancelled_delivery'])
        ->with('placeOrder')
        ->latest()->first();

        $customer = Customer::find($lastOrder->placeOrder->customer_id);
        if($lastOrder){
            $placeOrder = PlaceOrder::findOrFail($lastOrder->place_order_id);
            switch ($placeOrder->payment_method) {
                case 'wallet':
                    $wallet->balance += $lastOrder->price;
                    $wallet->save();
            
                    $lastTransaction = $wallet->receiver()
                        ->where('type', 'pay')
                        ->latest()
                        ->first();
            
                    if ($lastTransaction) {
                        $lastTransaction->status = 'completed';
                        $lastTransaction->save();
                    }
                    break;
            
                case 'cash':
                default:
                    $validator = Validator::make($request->all(), [
                        'paid' => 'required|numeric|in:0,1',
                    ]);
                    if($validator->fails()){
                        return $this->handleResponse(false,"", [$validator->errors()->first()],[],["لازم تقول وصلك الفلوس ولا لا لو وسيلة الدفع نقدي"]);
                    }
                    if ($request->paid) {
                        $placeOrder->paid = 1;
                        $placeOrder->save();
                    }
                    break;
            }
            $lastOrder->status = "completed";
            $lastOrder->delivery_time = Carbon::now(); 
            $lastOrder->save();
            
            // Delete all messages for this order to implement "chat ends automatically"
            Message::where('order_id', $lastOrder->id)->delete();
            
            //notify customer
            $notifyCustomer = $this->sendNotification(
                $customer->fcm_token,
                "شحنتك وصلت بنجاح",
                "لقد تم توصيل شحنتك بنجاح",
                $customer->id               
            );
            $wallet->balance -= $setting->company_share;
            $wallet->save();
            return $this->handleResponse(
                true,
                __("order.status updated"),
                [],
                [$lastOrder,
                 $notifyCustomer
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
            $order->rate_customer = $request->rate;
            $order->save();
            $customer = Customer::findOrFail($order->placeOrder->customer_id);
            $averageRating = Order::whereHas('placeOrder', function ($query) use($customer){
                $query->where('customer_id', $customer->id);
            })
                 ->whereNotNull('rate_customer') // Only include orders that have been rated
                 ->avg('rate_customer');
         
                // Update the driver's rate column with the average rating
                $customer->customer_rate = $averageRating ?? 0; // Set 0 if no ratings exist
                $customer->save();
            
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
