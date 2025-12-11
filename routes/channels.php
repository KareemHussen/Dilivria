<?php

use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    Log::channel('daily')->info('Channel Auth: App.Models.User.' . $id, [
        'user_id' => $user->id,
        'user_type' => get_class($user),
        'requested_id' => $id,
        'result' => (int) $user->id === (int) $id
    ]);
    return (int) $user->id === (int) $id;
});

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    Log::channel('daily')->info('Channel Auth: order.' . $orderId, [
        'user_id' => $user->id,
        'user_type' => get_class($user),
    ]);
    
    try {
        $order = Order::findOrFail($orderId);
        
        // Check if user is either the customer who placed the order or the delivery person
        $isCustomer = $order->placeOrder->customer_id == $user->id;
        $isDelivery = $order->delivery_id == $user->id;
        
        Log::channel('daily')->info('Channel Auth order result:', [
            'order_id' => $orderId,
            'placeOrder_customer_id' => $order->placeOrder->customer_id ?? 'NULL',
            'delivery_id' => $order->delivery_id,
            'isCustomer' => $isCustomer,
            'isDelivery' => $isDelivery,
            'result' => $isCustomer || $isDelivery
        ]);
        
        return $isCustomer || $isDelivery;
    } catch (\Exception $e) {
        Log::channel('daily')->error('Channel Auth order error: ' . $e->getMessage());
        return false;
    }
});

