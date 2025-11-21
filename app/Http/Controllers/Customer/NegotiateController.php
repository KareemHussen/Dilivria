<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderNegotiation;
use App\Models\PlaceOrder;
use App\Models\Transaction;
use App\Models\AppNotification;
use App\Traits\FcmNotificationTrait;
use App\Traits\HandleResponseTrait;
use Illuminate\Support\Facades\Validator;

class NegotiateController extends Controller
{
    use HandleResponseTrait, FcmNotificationTrait;
    public function proposePrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'place_order_id' => 'required|exists:place_orders,id',
            'proposed_price' => 'required|numeric|min:0',
            'delivery_id' => 'required|numeric|exists:customers,id'
        ]);
        
        if ($validator->fails()) {
            return $this->handleResponse(false, '', [$validator->errors()->first()],[],[]);
        }

        $placeOrder = PlaceOrder::findOrFail($request->place_order_id);
        $delivery = Customer::findOrFail($request->delivery_id);
        $proposer = $request->user();
        if($placeOrder->payment_method == "wallet"){
            $wallet = Wallet::where("customer_id", $placeOrder->customer_id)->first();
            if($wallet->balance < $request->proposed_price){
                return $this->handleResponse(
                    false,
                    __("order.no enough balance"),
                    [],
                    [],
                    []
                );
            }
        }
        // Create a new negotiation proposal
        $negotiation = OrderNegotiation::create([
            'place_order_id' => $placeOrder->id,
            'customer_id' => $proposer->id,
            'delivery_id' => $request->delivery_id,
            'proposed_price' => $request->proposed_price,
            'status' => 'pending',
        ]);
        $notification = $this->sendNotification(
            $delivery->fcm_token,
            "تلقيت عرض سعر من " . $proposer->first_name,
            "تلقيت عرض سعر على طلبك بقيمة " . $request->proposed_price . " جنيه",
            $delivery->id,
            "/api/delivery/order/get-proposal/single?negotiation_id=" . $negotiation->id,
            "GET"
        );
        return $this->handleResponse(
            true,
            __("order.propose sent"),
            [],
            [
                'negotiation' => $negotiation,
            ],
            []
        );
    }

    public function respondToProposal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'negotiation_id' => 'required|exists:order_negotiations,id',
            'status' => 'required|in:accepted,rejected',

        ]);
        
        if ($validator->fails()) {
            return $this->handleResponse(false, '', [$validator->errors()->first()],[],[]);
        }
        
        $negotiation = OrderNegotiation::findOrFail($request->negotiation_id);
        $placeOrder = PlaceOrder::findOrFail($negotiation->place_order_id);

 
        $customer = $request->user();
        if ($negotiation->customer_id == $customer->id){
            return $this->handleResponse(
                false,
                __("order.can not respond self"),
                [],
                [],
                []
            );
        }
        // Update negotiation status
        $negotiation->status = $request->status;
        $negotiation->save();

        if ($negotiation->status == 'accepted') {
            if($placeOrder->payment_method == "wallet"){
                $wallet = Wallet::where("customer_id", $placeOrder->customer_id)->first();
                if($wallet->balance < $negotiation->proposed_price){
                    return $this->handleResponse(
                        false,
                        __("order.no enough balance"),
                        [],
                        [],
                        []
                    );
                }
                $wallet->balance -=  $negotiation->proposed_price;
                $wallet->save();
                $receiver = Wallet::where('customer_id', $negotiation->delivery_id)->first();
                Transaction::create([
                    "sender" => $wallet->id,
                    "receiver" => $receiver->id,
                    "amount" => $negotiation->proposed_price,
                    "type" => "pay"
                ]);
            }
            $placeOrder->update(["status" => "accepted"]);
            $order = Order::create([
                "place_order_id" => $negotiation->placeOrder->id,
                "delivery_id" => $negotiation->delivery_id,
                "price" => $negotiation->proposed_price
            ]);
            // Send enhanced notification to driver when client accepts offer
            $delivery = Customer::findOrFail($negotiation->delivery_id);
            $this->sendNotification(
                $delivery->fcm_token,
                "قبل العميل عرض السعر",
                "لقد تم قبول عرض السعر الخاص بك من العميل وبدأ الطلب",
                $negotiation->delivery_id    
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
            true,
            __("order.propose respond recorded"),
            [],
            [
                'negotiation' => $negotiation,
            ],
            []
        );
    }

    public function getProposals(Request $request){
        $user = $request->user();
        $lastOrder = PlaceOrder::where('customer_id', $user->id)
        ->where('status', 'pending')->latest()->first();
        if($lastOrder){
            if($lastOrder->negotiations()){
                $negotiations = $lastOrder->negotiations()->with(['delivery' => function ($q){
                    $q->select('id','full_name', 'phone', 'lng', 'lat', 'delivery_rate');
                }])->get();
                return $this->handleResponse(
                    true,
                    "",
                    [],
                    [
                        "negotiations" => $negotiations
                    ],
                    []
                );
            }
            return $this->handleResponse(
                false,
                __("order.no negotiations yet"),
                [],
                [],
                []
            );
        }
        return $this->handleResponse(
            false,
            __("order.no ongoing"),
            [],
            [],
            []
        );
    }

    public function get(Request $request){
        $validator = Validator::make($request->all(), [
            "negotiation_id" => "required|exists:order_negotiations,id"
        ]);

        if($validator->fails()){
            return $this->handleResponse(
                false, "",  [$validator->errors()->first()],[],[]
            );
        }

        $proposal = OrderNegotiation::findOrFail($request->negotiation_id);

        return $this->handleResponse(
            true,
            "",
            [],
            [
                "negotiation" => $proposal
            ],
            []
        );
    }
}

