<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Traits\HandleResponseTrait;
use Illuminate\Http\Request;

class ActivitiesController extends Controller
{
    use HandleResponseTrait;

    public function getCompletedAll(Request $request){
        $delivery = $request->user();
        $orders = $delivery->orders()
        ->where('status', 'completed')
        ->with('placeOrder')
        ->get();
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "orders" => $orders
            ],
            []
        );
    }

    public function getCompletedPaginate(Request $request){
        $perPage = $request->per_page ?: 10;
        $delivery = $request->user();
        $orders = $delivery->orders()
        ->where('status', 'completed')
        ->with('placeOrder')
        ->paginate($perPage);
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "orders" => $orders
            ],
            []
        );
    }

    public function getCancelledAll(Request $request){
        $delivery = $request->user();
        $orders = $delivery->orders()
        ->whereIn('status', ['cancelled_user', 'cancelled_delivery'])
        ->with('placeOrder')
        ->get();
        return $this->handleResponse(
            true,
            "",
            [],
            [
                "orders" => $orders
            ],
            []
        );
    }

    public function getCancelledPaginate(Request $request){
        $perPage = $request->per_page ?: 10;
        $delivery = $request->user();
        $orders = $delivery->orders()
        ->whereIn('status', ['cancelled_user', 'cancelled_delivery'])
        ->with('placeOrder')
        ->paginate($perPage);
        return $this->handleResponse(
        true,
        "",
        [],
        [
            "orders" => $orders
        ],
        []
        );
    }
}
