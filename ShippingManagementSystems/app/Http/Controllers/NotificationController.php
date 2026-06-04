<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $perPage = $request->per_page ?? 15;
        $notifications = $user->notifications()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => $user->unreadNotifications()->count()
            ]
        ]);
    }


    public function unreadCount(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'success' => true,
            'count' => $user->unreadNotifications()->count()
        ]);
    }


    public function unread(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $perPage = $request->per_page ?? 15;
        $notifications = $user->unreadNotifications()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ]
        ]);
    }


    public function markAsRead(Request $request, $notificationId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $notification = $user->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }


    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }


    public function destroy(Request $request, $notificationId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $notification = $user->notifications()->findOrFail($notificationId);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }


    public function destroyAll(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications deleted successfully'
        ]);
    }
}
