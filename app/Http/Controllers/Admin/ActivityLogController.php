<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = str(trim((string) $request->query('search')))->limit(255, '')->toString();
        $module = str(trim((string) $request->query('module')))->limit(150, '')->toString();
        $action = str(trim((string) $request->query('action')))->limit(100, '')->toString();
        $status = in_array($request->query('status'), ActivityLog::STATUSES, true) ? $request->query('status') : '';
        $dateFrom = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('date_from')) ? $request->query('date_from') : '';
        $dateTo = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('date_to')) ? $request->query('date_to') : '';

        $activityLogs = ActivityLog::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('user_name', 'like', "%{$search}%")
                    ->orWhere('user_email', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            }))
            ->when($module !== '', fn ($query) => $query->where('module', $module))
            ->when($action !== '', fn ($query) => $query->where('action', $action))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $dateTo))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'total' => ActivityLog::query()->count(),
            'today' => ActivityLog::query()->today()->count(),
            'create' => ActivityLog::query()->where('action', 'create')->count(),
            'update' => ActivityLog::query()->whereIn('action', ['update', 'settings'])->count(),
            'delete' => ActivityLog::query()->where('action', 'delete')->count(),
            'failed' => ActivityLog::query()->where('status', 'failed')->count(),
        ];
        $modules = ActivityLog::query()->whereNotNull('module')->distinct()->orderBy('module')->pluck('module');
        $actions = ActivityLog::query()->whereNotNull('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.activity-logs.index', compact(
            'activityLogs', 'summary', 'modules', 'actions', 'search', 'module', 'action',
            'status', 'dateFrom', 'dateTo'
        ));
    }

    public function show(ActivityLog $activityLog): View
    {
        return view('admin.activity-logs.show', compact('activityLog'));
    }
}
