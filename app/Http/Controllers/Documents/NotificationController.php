<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\DocumentNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles document notification AJAX endpoints.
 *
 * Provides notification listing, unread count polling,
 * mark-as-read, and mark-all-as-read functionality
 * for the navbar notification bell dropdown.
 */
class NotificationController extends Controller {
    /**
     * Return latest 20 document notifications for the authenticated user.
     *
     * Response includes formatted notification list and unread count
     * for the dropdown display.
     */
    public function index(Request $request): JsonResponse {
        $userId = auth()->id();

        $notifications = DocumentNotification::forUser($userId)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(function (DocumentNotification $notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'url' => $notification->url,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at?->toIso8601String(),
                    'created_at' => $notification->created_at->toIso8601String(),
                    'time_ago' => $notification->created_at->diffForHumans(),
                ];
            });

        $unreadCount = DocumentNotification::forUser($userId)
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Return just the unread notification count.
     *
     * Lightweight endpoint for badge polling (called every 60 seconds).
     */
    public function unreadCount(Request $request): JsonResponse {
        $count = DocumentNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark a single notification as read.
     *
     * Verifies the notification belongs to the authenticated user
     * before marking it as read.
     */
    public function markAsRead(Request $request, int $id): JsonResponse {
        $notification = DocumentNotification::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all unread notifications as read for the authenticated user.
     *
     * Returns the count of notifications that were marked.
     */
    public function markAllAsRead(Request $request): JsonResponse {
        $count = DocumentNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }
}
