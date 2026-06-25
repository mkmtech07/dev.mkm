<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailLog;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MailLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $mailLogs = $this->applyFilters(MailLog::query()->with('creator:id,name,email'), $filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.mail-logs.index', [
            'mailLogs' => $mailLogs,
            'mailTypes' => $this->mailTypes(),
            'summary' => $this->summary(),
            ...$filters,
        ]);
    }

    public function show(MailLog $mailLog): View
    {
        $mailLog->load('creator:id,name,email');

        return view('admin.mail-logs.show', [
            'mailLog' => $mailLog,
        ]);
    }

    public function destroy(MailLog $mailLog, ActivityLogger $logger): RedirectResponse
    {
        $snapshot = [
            'mail_log_id' => $mailLog->id,
            'recipient' => $mailLog->recipient,
            'subject' => $mailLog->subject,
            'mail_type' => $mailLog->mail_type,
            'status' => $mailLog->status,
        ];

        $mailLog->delete();

        $logger->log(
            'delete',
            'mail_logs',
            'Mail log deleted.',
            $mailLog,
            $snapshot,
        );

        return to_route('admin.mail-logs.index')
            ->with('success', 'Mail log deleted successfully.');
    }

    /** @return array<string, string> */
    private function filters(Request $request): array
    {
        return [
            'search' => Str::limit(strip_tags(trim((string) $request->query('search'))), 255, ''),
            'status' => in_array($request->query('status'), MailLog::STATUSES, true) ? (string) $request->query('status') : '',
            'mailType' => Str::limit(Str::snake(strip_tags(trim((string) $request->query('mail_type')))), 255, ''),
            'dateFrom' => $this->dateFilter($request->query('date_from')),
            'dateTo' => $this->dateFilter($request->query('date_to')),
        ];
    }

    /** @param array<string, string> $filters */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($filters) {
                $search = $filters['search'];
                $query->where('recipient', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('template_slug', 'like', "%{$search}%")
                    ->orWhere('mail_type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            }))
            ->when($filters['status'] !== '', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when($filters['mailType'] !== '', fn (Builder $query) => $query->where('mail_type', $filters['mailType']))
            ->when($filters['dateFrom'] !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $filters['dateFrom']))
            ->when($filters['dateTo'] !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $filters['dateTo']));
    }

    /** @return array<int, string> */
    private function mailTypes(): array
    {
        return MailLog::query()
            ->whereNotNull('mail_type')
            ->distinct()
            ->orderBy('mail_type')
            ->pluck('mail_type')
            ->all();
    }

    /** @return array<string, int> */
    private function summary(): array
    {
        return [
            'total' => MailLog::query()->count(),
            'sent' => MailLog::query()->where('status', 'sent')->count(),
            'failed' => MailLog::query()->where('status', 'failed')->count(),
            'pending' => MailLog::query()->where('status', 'pending')->count(),
        ];
    }

    private function dateFilter(mixed $value): string
    {
        $value = trim((string) $value);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : '';
    }
}
