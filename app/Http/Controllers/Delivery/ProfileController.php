<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Traits\HandleResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    use HandleResponseTrait;

    public function get(Request $request){
        $user = Customer::where('id', $request->user()->id)
        ->select("id", "full_name", "username", "phone", "delivery_rate", "picture", "delivery", "verified")
        ->first();

        $orders = $user->orders()->get();

        // Count the completed and canceled orders
        $completed = $orders->where('status', 'completed')->count();
        $cancelled = $orders->whereIn('status', ['cancelled_user', 'cancelled_delivery'])->count();
        $onGoing = $orders->whereNotIn('status', ['cancelled_user', 'cancelled_delivery', 'completed'])->count();

        // Calculate the total number of orders
        $totalOrders = $orders->count();

        // Avoid division by zero
        $completedPercentage = $totalOrders > 0 ? ($completed / $totalOrders) * 100 : 0;
        $cancelledPercentage = $totalOrders > 0 ? ($cancelled / $totalOrders) * 100 : 0;
        $onGoingPercentage = $totalOrders > 0 ? ($onGoing / $totalOrders) * 100 : 0; 

        // Store the data in the user object or return as needed
        $user->completed_orders = $completed;
        $user->cancelled_orders = $cancelled;
        $user->on_going_orders = $onGoing;
        $user->completed_percentage = (float) number_format($completedPercentage, 2);
        $user->cancelled_percentage = (float) number_format($cancelledPercentage, 2);
        $user->on_going_percentage = (float) number_format($onGoingPercentage, 2);
        $user->total_orders = $totalOrders;
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "profile" => $user
            ],
            []
        );
    }

    public function edit(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                "full_name" => ['nullable', "string", 'regex:/^([A-Za-zÀ-ÖØ-öø-ÿ]+(?:\s+[A-Za-zÀ-ÖØ-öø-ÿ]+){3})$/'],
                'phone' => ['nullable','string','unique:customers,phone'],
                'picture'=> 'nullable|image|mimes:jpeg,png,jpg,gif'
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
            if($request->full_name){
            $user->full_name = $request->full_name;
            }
            if($request->phone){
            $user->phone = $request->phone;
            }
            if ($request->picture) {
                $imagePath = $request->file('picture')->store('/storage/profile', 'public');
                $user->picture = $imagePath;
            }
            $user->save();
            
            return $this->handleResponse(
                true,
                __("profile.info updated"),
                [],
                [
                   "user" => $user,
                ],
                []
            );
            } catch (\Exception $e) {
                return $this->handleResponse(
                    false,
                    __("profile.error info update"),
                    [
                        // $e->getMessage()
                    ],
                    [],
                    []
                );
            }
    }
}
