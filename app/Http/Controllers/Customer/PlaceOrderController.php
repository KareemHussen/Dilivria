<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyNearbyDriversJob;
use App\Models\Favorite;
use App\Models\PlaceOrder;
use App\Models\Setting;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Http\Request;
use App\Traits\HandleResponseTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class PlaceOrderController extends Controller
{
    use HandleResponseTrait;

    public function getCostPerKM(){
        $cost = Setting::select('cost_per_km')->first();
        if($cost){
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    $cost
                ],
                []
            );
        }
        return response()->json(['please run DB seeders on the server, or set the cost\km in admin panel'], 400);
    }

    public function placeOrder(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                "favorite_from" => "nullable|numeric|exists:favorites,id",
                "favorite_to" => "nullable|numeric|exists:favorites,id",
                "address_from" => "required_if:favorite_from,null",
                "lng_from" => "required_if:favorite_from,null",
                "lat_from" => "required_if:favorite_from,null",
                "address_to" => "required_if:favorite_to,null",
                "lng_to" => "required_if:favorite_to,null",
                "lat_to" => "required_if:favorite_to,null",
                "price" => "required|numeric",
                "details" => "required|string|max:1000",
                "payment_method" => "required|in:cash,wallet",
                "pin" => "required_if:payment_method,wallet|numeric|digits:6"
            ],[
                "numeric" => __('validation.numeric'),
                "exists" => __('validation.exists'),
                "required" => __("validation.required"),
                "string" => __("validation.string"),
                "max" => __("validation.max.string"),
                "payment_method.in" => "choose cash or wallet with the same spelling"
            ]);

            if($validator->fails()){
                return $this->handleResponse(
                    false,
                    "",
                    [$validator->errors()->first()],
                    [],
                    []
                );
            }

            $user = $request->user();
            //Check if customer placed an order and still active
            $lastPlacedOrder = PlaceOrder::where('customer_id', $user->id)
            ->where('status', 'pending')->latest()->first();
            //Check if customer has an order accepted by a driver
            $lastOrder = PlaceOrder::where('customer_id', $user->id)
            ->whereHas('order', function ($query){
                $query->whereNotIn('status', ['completed', 'cancelled_user', 'cancelled_delivery']);
            })->with('order', 'order.delivery')->latest()->first();
            //Customer can't place an order while he's on an active one
            if($lastOrder ||  $lastPlacedOrder){
                return $this->handleResponse(
                    false,
                    __('order.many at the moment'),
                    [],
                    [],
                    []
                );
            }
            if($request->payment_method == "wallet"){
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
                if($wallet->balance < $request->price){
                    return $this->handleResponse(
                        false,
                        __("order.no enough balance"),
                        [],
                        [],
                        []
                    );
                }
            }
            $addressFrom = $lngFrom = $latFrom = null;
            $addressTo = $lngTo = $latTo = null;
            if($request->favorite_from){
                $favoriteFrom = Favorite::find($request->favorite_from);
                if($favoriteFrom){
                    $addressFrom = $favoriteFrom->address;
                    $lngFrom = $favoriteFrom->lng;
                    $latFrom = $favoriteFrom->lat;
                } else {
                return $this->handleResponse(
                    false,
                    __('favorite.undefined address') . " from",
                    [],
                    [],
                    []
                );
                }
            }
            if($request->favorite_to){
                $favoriteTo = Favorite::find($request->favorite_to);
                if($favoriteTo){
                    $addressTo = $favoriteTo->address;
                    $lngTo = $favoriteTo->lng;
                    $latTo = $favoriteTo->lat;
                } else {
                return $this->handleResponse(
                    false,
                    __('favorite.undefined address') . " to",
                    [],
                    [],
                    []
                );
                }
            }

            $placeOrder = PlaceOrder::create([
                "customer_id" => $user->id,
                "address_from" => $request->favorite_from ? $addressFrom : $request->address_from,
                "lng_from" => $request->favorite_from ? $lngFrom : $request->lng_from,
                "lat_from" => $request->favorite_from ? $latFrom : $request->lat_from,
                "address_to" => $request->favorite_to ? $addressTo : $request->address_to,
                "lng_to" => $request->favorite_to ? $lngTo : $request->lng_to,
                "lat_to" => $request->favorite_to ? $latTo : $request->lat_to,
                "price" => $request->price,
                "details" => $request->details,
                "payment_method" => $request->payment_method
            ]);
            
            // Dispatch job to notify nearby drivers in background
            NotifyNearbyDriversJob::dispatch($placeOrder->id);
            
            return $this->handleResponse(
                true,
                __('order.placed successfully'),
                [],
                [],
                $placeOrder
            );

        } catch (\Exception $e) {

            return $this->handleResponse(
                false,
                "",
                [$e->getMessage()],
                [],
                []
            );
        }
    }

    public function getActive(Request $request){
        $customer = $request->user();
        $placeOrder = PlaceOrder::where('customer_id', $customer->id)->where('status', 'pending')->latest()->first();
        if($placeOrder){
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    "active_request" => $placeOrder
                ],
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

    public function cancel(Request $request){
        $customer = $request->user();
        $placeOrder = PlaceOrder::where('customer_id', $customer->id)->where('status', 'pending')->latest()->first();
        if($placeOrder){
            $placeOrder->status = "cancelled";
            $placeOrder->save();
            return $this->handleResponse(
                true,
                __("order.request cancelled"),
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
}

