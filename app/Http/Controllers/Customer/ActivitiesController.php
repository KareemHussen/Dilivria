<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Traits\HandleResponseTrait;
use Illuminate\Http\Request;

class ActivitiesController extends Controller
{
    use HandleResponseTrait;

    public function getCompletedAll(Request $request){
        $customer = $request->user();
        $orders = $customer->placeOrders()
        ->whereRelation('order', 'status', 'completed')
        ->with('order')
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
        $customer = $request->user();
        $orders = $customer->placeOrders()
        ->whereRelation('order', 'status', 'completed')
        ->with('order')
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
        $customer = $request->user();
        $orders = $customer->placeOrders()
        ->whereRelation('order', 'status', 'like', 'cancelled%')
        ->with('order')
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
        $customer = $request->user();
        $orders = $customer->placeOrders()
        ->whereRelation('order', 'status', 'like', 'cancelled%')
        ->with('order')
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
