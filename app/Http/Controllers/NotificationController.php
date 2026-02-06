<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get user's notifications (JSON for AJAX).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $notifications = $user->notifications()
            ->take(20)
            ->get();

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Show all notifications page.
     */
    public function all(Request $request)
    {
        $user = auth()->user();
        
        $notifications = $user->notifications()->paginate(30);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
