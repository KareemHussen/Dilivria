<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppNotification;
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = AppNotification::where('customer_id', auth()->id())
            ->orWhereNull('customer_id')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    public function markAsRead(AppNotification $notification)
    {
        // Check if the notification belongs to the authenticated user
        if ($notification->customer_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => __('Unauthorized access')
            ], 403);
        }

        $notification->update(['opened' => true]);

        return response()->json([
            'success' => true,
            'message' => __('Notification marked as read')
        ]);
    }


    public function markAllAsRead()
    {
        AppNotification::where('customer_id', auth()->id())
            ->where('opened', false)
            ->update(['opened' => true]);

        return response()->json([
            'success' => true,
            'message' => __('All notifications marked as read')
        ]);
    }

    public function destroy(AppNotification $notification)
    {
        // Check if the notification belongs to the authenticated user
        if ($notification->customer_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => __('Unauthorized access')
            ], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => __('Notification deleted successfully')
        ]);
    }

    public function destroyAll()
    {
        AppNotification::where('customer_id', auth()->id())->delete();

        return response()->json([
            'success' => true,
            'message' => __('All notifications deleted successfully')
        ]);
    }
}
