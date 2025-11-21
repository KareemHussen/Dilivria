# Order Channel Subscription Guide

This guide explains how to subscribe to the `order.{orderId}` private channel after the recent security improvement.

## What Changed?

The `order.{orderId}` broadcasting channel has been updated to include proper authorization:

- Previously, the channel was public (`return true;` in `channels.php`), allowing anyone to listen to order messages
- Now, the channel is properly restricted to only allow the customer who placed the order and the assigned delivery person

## Channel Authorization Implementation

The authorization logic was implemented in `routes/channels.php`:

```php
Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = Order::findOrFail($orderId);
    
    // Check if user is either the customer who placed the order or the delivery person
    $isCustomer = $order->placeOrder->customer_id == $user->id;
    $isDelivery = $order->delivery_id == $user->id;
    
    return $isCustomer || $isDelivery;
});
```

## Frontend Implementation

### Files Provided

These files have been added to help implement the private channel:

1. **`resources/js/order-chat.js`**:
   - Example implementation for subscribing to the private channel
   - Functions for sending/receiving messages and rendering in UI

2. **`resources/views/chat-example.blade.php`**:
   - Example chat UI template with styling
   - Shows how to initialize the chat module with order and user info

### How to Use in Your Application

#### 1. Verify Echo Setup

Your application already has Laravel Echo setup in `resources/js/echo.js`:

```javascript
import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

#### 2. Implement Private Channel Subscription

When a user enters a chat for an order, subscribe them to the private channel:

```javascript
// Get the current order ID and user information
const orderId = 123; // Replace with your actual order ID
const userId = 456;  // Current user ID
const userType = 'customer'; // 'customer' or 'delivery'

// Subscribe to the private channel
window.Echo.private(`order.${orderId}`)
    .listen('SendMessageEvent', (event) => {
        // Only handle messages from other users (not our own echo)
        if (event.sender_id !== userId || event.sender_type !== userType) {
            // Add the message to the UI
            addMessageToChat(event);
        }
    });
```

#### 3. Full Implementation

See the provided `resources/js/order-chat.js` for a complete implementation with:

- Channel subscription
- Message sending and receiving
- Chat history loading
- UI updating

You can use this module directly or adapt it to your needs.

## IMPORTANT: Authorization Requirements

For the private channel to work properly:

1. The user must be authenticated with Laravel's auth system
2. Laravel must include the auth user in the requests to the websocket server
3. The CSRF token must be properly included in the page

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

```javascript
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
```

## Troubleshooting

If you encounter issues with channel subscription:

1. **403 Unauthorized errors**: Make sure the user is properly authenticated and is either the customer or delivery person for the order
2. **Connection issues**: Check your broadcasting configuration in `.env` and `config/broadcasting.php`
3. **Message not received**: Verify the broadcast is working by checking the network tab in dev tools
4. **Laravel logs**: Check for auth failures in Laravel logs

For debugging, add these logs to your JavaScript:

```javascript
// Debug channel subscription
window.Echo.private(`order.${orderId}`)
    .listen('SendMessageEvent', (e) => { console.log('Message received:', e); })
    .error((error) => { console.error('Channel subscription error:', error); });
```
