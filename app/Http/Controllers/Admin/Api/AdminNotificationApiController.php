<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Services\ActivityLogger;
use App\Services\AdminNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNotificationApiController extends Controller
{
    public function index(Request $request, AdminNotificationService $notifications): JsonResponse
    {
        $limit = (int) $request->query('limit', 10);
        $items = $notifications->latestUnread($request->user(), max(1, min($limit, 20)))
            ->map(fn (AdminNotification $notification) => $this->payload($notification));

        return response()->json([
            'data' => $items,
            'unread_count' => $notifications->unreadCount($request->user()),
        ]);
    }

    public function unreadCount(Request $request, AdminNotificationService $notifications): JsonResponse
    {
        return response()->json([
            'unread_count' => $notifications->unreadCount($request->user()),
        ]);
    }

    public function markRead(
        Request $request,
        AdminNotification $notification,
        AdminNotificationService $notifications,
        ActivityLogger $logger
    ): JsonResponse {
        $this->ensureVisible($request, $notification);
        $changed = $notifications->markAsRead($notification);

        if ($changed) {
            $logger->log('mark_read', 'notifications', 'Notification marked as read.', $notification);
        }

        return response()->json([
            'message' => $changed ? 'Notification marked as read.' : 'Notification was already read.',
            'notification' => $this->payload($notification->fresh()),
            'unread_count' => $notifications->unreadCount($request->user()),
        ]);
    }

    public function markAllRead(
        Request $request,
        AdminNotificationService $notifications,
        ActivityLogger $logger
    ): JsonResponse {
        $count = $notifications->markAllAsRead($request->user());
        $logger->log('mark_all_read', 'notifications', 'All visible notifications marked as read.', new AdminNotification(), null, [
            'count' => $count,
        ]);

        return response()->json([
            'message' => 'Notifications marked as read.',
            'marked_count' => $count,
            'unread_count' => 0,
        ]);
    }

    public function destroy(
        Request $request,
        AdminNotification $notification,
        AdminNotificationService $notifications,
        ActivityLogger $logger
    ): JsonResponse {
        $this->ensureVisible($request, $notification);
        $notification->delete();
        $logger->log('delete', 'notifications', 'Notification deleted.', $notification);

        return response()->json([
            'message' => 'Notification deleted successfully.',
            'unread_count' => $notifications->unreadCount($request->user()),
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(AdminNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'message' => str($notification->message ?: '')->limit(140)->toString(),
            'type' => $notification->type,
            'module' => $notification->module,
            'url' => $notification->targetUrl(),
            'show_url' => route('admin.notifications.show', $notification, false),
            'is_read' => $notification->is_read,
            'created_at' => $notification->created_at?->toIso8601String(),
            'time_ago' => $notification->created_at?->diffForHumans(),
        ];
    }

    private function ensureVisible(Request $request, AdminNotification $notification): void
    {
        abort_unless(
            $notification->user_id === null || (int) $notification->user_id === (int) $request->user()->getKey(),
            404
        );
    }
}
