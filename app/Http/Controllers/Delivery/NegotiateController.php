<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderNegotiation;
use App\Models\PlaceOrder;
use App\Models\Transaction;
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
        ]);
        
        if ($validator->fails()) {
            return $this->handleResponse(false, '', [$validator->errors()->first()],[],[]);
        }

        $placeOrder = PlaceOrder::findOrFail($request->place_order_id);

        $proposer = $request->user();
        
        // Check if delivery has sufficient wallet balance (must be >= -10)
        $wallet = $proposer->wallet;
        if (!$wallet || $wallet->balance < -10) {
            return $this->handleResponse(
                false,
                __("wallet.wallet recharge required"),
                [],
                [],
                []
            );
        }
        // Create a new negotiation proposal
        $negotiation = OrderNegotiation::create([
            'place_order_id' => $placeOrder->id,
            'delivery_id' => $proposer->id,
            'proposed_price' => $request->proposed_price,
            'status' => 'pending',
        ]);
        $customer = Customer::findOrFail($placeOrder->customer_id);
        $notification = $this->sendNotification(
            $customer->fcm_token,
            "تلقيت عرض سعر من " . $proposer->first_name,
            "تلقيت عرض سعر على طلبك بقيمة " . $request->proposed_price . " جنيه",
            $customer->id,
            "/api/customer/order/get-proposal/single?negotiation_id=" . $negotiation->id,
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
 
        $delivery = $request->user();
        if ($negotiation->delivery_id == $delivery->id && !$negotiation->customer_id){
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
            // Check if delivery has sufficient wallet balance (must be >= -10)
            $deliveryWallet = $delivery->wallet;
            if (!$deliveryWallet || $deliveryWallet->balance < -10) {
                return $this->handleResponse(
                    false,
                    __("wallet.wallet recharge required"),
                    [],
                    [],
                    []
                );
            }
            
            if($placeOrder->payment_method == "wallet"){
                $wallet = Wallet::where("customer_id", $placeOrder->customer_id)->first();
                $wallet->balance -=  $negotiation->proposed_price;
                $wallet->save();
                $receiver = $delivery->wallet()->first();
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
                "delivery_id" => $delivery->id,
                "price" => $negotiation->proposed_price
            ]);

            $this->sendNotification(
                Customer::find($negotiation->customer_id)->fcm_token,
                "تم قبول عرض السعر الخاص بك",
                "قبل رجل التوصيل عرض السعر الخاص بك وتم بدأ الطلب",
                $negotiation->customer_id
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
        $delivery = $request->user();
        $negotiations = OrderNegotiation::where('delivery_id', $delivery->id)->whereNotNull('customer_id')
        ->whereHas('placeOrder', function ($query){
            $query->where('status', 'pending');
        })->orderBy('created_at', 'desc')
        ->with(['placeOrder','customer' => function ($q){
            $q->select('id', 'first_name', 'last_name', 'phone', 'customer_rate');
        }])->get();
        if(count($negotiations) > 0){
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


