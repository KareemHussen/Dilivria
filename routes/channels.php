<?php

use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = Order::findOrFail($orderId);
    
    // Check if user is either the customer who placed the order or the delivery person
    $isCustomer = $order->placeOrder->customer_id == $user->id;
    $isDelivery = $order->delivery_id == $user->id;
    
    return $isCustomer || $isDelivery;
});
