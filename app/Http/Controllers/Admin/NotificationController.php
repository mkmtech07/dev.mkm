<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Services\ActivityLogger;
use App\Services\AdminNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request, AdminNotificationService $notifications): View
    {
        $filters = $this->filters($request);
        $baseQuery = $notifications->visibleQuery($request->user());
        $notificationQuery = $this->applyFilters(clone $baseQuery, $filters);

        $adminNotifications = $notificationQuery
            ->with('user:id,name,email')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summaryBase = clone $baseQuery;
        $summary = [
            'total' => (clone $summaryBase)->count(),
            'unread' => (clone $summaryBase)->where('is_read', false)->count(),
            'today' => (clone $summaryBase)->whereDate('created_at', today())->count(),
            'warning' => (clone $summaryBase)->where('type', 'warning')->count(),
            'danger' => (clone $summaryBase)->where('type', 'danger')->count(),
        ];

        $modules = (clone $baseQuery)
            ->whereNotNull('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        return view('admin.notifications.index', [
            'adminNotifications' => $adminNotifications,
            'summary' => $summary,
            'modules' => $modules,
            ...$filters,
        ]);
    }

    public function show(
        Request $request,
        AdminNotification $notification,
        AdminNotificationService $notifications,
        ActivityLogger $logger
    ): View {
        $this->ensureVisible($request, $notification);
        $changed = $notifications->markAsRead($notification);

        if ($changed) {
            $logger->log('mark_read', 'notifications', 'Notification marked as read.', $notification);
        }

        return view('admin.notifications.show', [
            'notification' => $notification->fresh(),
        ]);
    }

    public function markRead(
        Request $request,
        AdminNotification $notification,
        AdminNotificationService $notifications,
        ActivityLogger $logger
    ): RedirectResponse {
        $this->ensureVisible($request, $notification);
        $changed = $notifications->markAsRead($notification);

        if ($changed) {
            $logger->log('mark_read', 'notifications', 'Notification marked as read.', $notification);
        }

        return back()->with('success', $changed ? 'Notification marked as read.' : 'Notification was already read.');
    }

    public function markAllRead(
        Request $request,
        AdminNotificationService $notifications,
        ActivityLogger $logger
    ): RedirectResponse {
        $count = $notifications->markAllAsRead($request->user());

        $logger->log('mark_all_read', 'notifications', 'All visible notifications marked as read.', new AdminNotification(), null, [
            'count' => $count,
        ]);

        return back()->with('success', "{$count} notifications marked as read.");
    }

    public function destroy(
        Request $request,
        AdminNotification $notification,
        ActivityLogger $logger
    ): RedirectResponse {
        $this->ensureVisible($request, $notification);
        $notification->delete();
        $logger->log('delete', 'notifications', 'Notification deleted.', $notification);

        return to_route('admin.notifications.index')->with('success', 'Notification deleted successfully.');
    }

    public function bulkDestroy(Request $request, AdminNotificationService $notifications, ActivityLogger $logger): RedirectResponse
    {
        $validated = $request->validate([
            'notifications' => ['required', 'array', 'min:1'],
            'notifications.*' => ['integer'],
        ]);

        $query = $notifications->visibleQuery($request->user())
            ->whereIn('id', array_unique($validated['notifications']));

        $count = $query->count();
        $query->delete();

        $logger->log('delete', 'notifications', 'Selected notifications deleted.', new AdminNotification(), null, [
            'count' => $count,
        ]);

        return back()->with('success', "{$count} notifications deleted successfully.");
    }

    /** @return array<string, string> */
    private function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search')),
            'type' => in_array($request->query('type'), AdminNotification::TYPES, true) ? $request->query('type') : '',
            'module' => preg_match('/^[A-Za-z0-9_.-]{1,100}$/', (string) $request->query('module')) ? (string) $request->query('module') : '',
            'status' => in_array($request->query('status'), ['read', 'unread'], true) ? $request->query('status') : '',
            'dateFrom' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('date_from')) ? (string) $request->query('date_from') : '',
            'dateTo' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('date_to')) ? (string) $request->query('date_to') : '',
        ];
    }

    /** @param array<string, string> $filters */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($filters) {
                $search = $filters['search'];
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            }))
            ->when($filters['type'] !== '', fn (Builder $query) => $query->where('type', $filters['type']))
            ->when($filters['module'] !== '', fn (Builder $query) => $query->where('module', $filters['module']))
            ->when($filters['status'] === 'read', fn (Builder $query) => $query->where('is_read', true))
            ->when($filters['status'] === 'unread', fn (Builder $query) => $query->where('is_read', false))
            ->when($filters['dateFrom'] !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $filters['dateFrom']))
            ->when($filters['dateTo'] !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $filters['dateTo']));
    }

    private function ensureVisible(Request $request, AdminNotification $notification): void
    {
        abort_unless(
            $notification->user_id === null || (int) $notification->user_id === (int) $request->user()->getKey(),
            404
        );
    }
}
