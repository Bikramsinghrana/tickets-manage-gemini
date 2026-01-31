<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request)
    {
        $notifications = $this->notificationService->getPaginated(auth()->user(), 20);
        
        if ($request->wantsJson()) {
            return response()->json([
                'notifications' => $notifications->items(),
                'unread_count' => $this->notificationService->getUnreadCount(auth()->user()),
                'has_more' => $notifications->hasMorePages(),
            ]);
        }

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get unread notification count (AJAX)
     */
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => $this->notificationService->getUnreadCount(auth()->user()),
        ]);
    }

    /**
     * Get recent notifications for dropdown (AJAX)
     */
    public function recent(): JsonResponse
    {
        $user = auth()->user();
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'general',
                    'message' => $notification->data['message'] ?? '',
                    'url' => $notification->data['url'] ?? '#',
                    'is_read' => !is_null($notification->read_at),
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }),
            'unread_count' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    /**
     * Mark a notification as read (AJAX)
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $success = $this->notificationService->markAsRead(auth()->user(), $id);

        return response()->json([
            'success' => $success,
            'unread_count' => $this->notificationService->getUnreadCount(auth()->user()),
        ]);
    }

    /**
     * Mark all notifications as read (AJAX)
     */
    public function markAllAsRead(): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead(auth()->user());

        return response()->json([
            'success' => true,
            'marked_count' => $count,
            'unread_count' => 0,
        ]);
    }
}
