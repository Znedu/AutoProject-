<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get the unread count and latest 20 notifications for Alpine polling.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $unreadCount = $user->unreadNotifications()->count();

        $notifications = $user->notifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($notification) {
                return [
                    'id'         => $notification->id,
                    'read_at'    => $notification->read_at?->toIso8601String(),
                    'is_read'    => $notification->read(),
                    'created_at' => $notification->created_at->diffForHumans(),
                    'data'       => $notification->data,
                ];
            });

        return response()->json([
            'count'         => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if ($notification !== null) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
