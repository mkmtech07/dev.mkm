<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsletterSubscriberRequest;
use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterSubscriberController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $subscribers = $this->applyFilters(NewsletterSubscriber::query(), $filters)
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();
        $summary = [
            'total' => NewsletterSubscriber::query()->count(),
            'subscribed' => NewsletterSubscriber::query()->where('status', 'subscribed')->count(),
            'unsubscribed' => NewsletterSubscriber::query()->where('status', 'unsubscribed')->count(),
            'pending' => NewsletterSubscriber::query()->where('status', 'pending')->count(),
            'blocked' => NewsletterSubscriber::query()->where('status', 'blocked')->count(),
            'today' => NewsletterSubscriber::query()->whereDate('subscribed_at', today())->count(),
        ];

        return view('admin.newsletter-subscribers.index', [
            'subscribers' => $subscribers,
            'summary' => $summary,
            ...$filters,
        ]);
    }

    public function create(): View
    {
        return view('admin.newsletter-subscribers.create', [
            'newsletterSubscriber' => new NewsletterSubscriber([
                'source' => 'manual', 'status' => 'subscribed', 'status_active' => true,
            ]),
        ]);
    }

    public function store(NewsletterSubscriberRequest $request): RedirectResponse
    {
        $data = $this->withStatusDates($request->validated());
        $subscriber = NewsletterSubscriber::create($data);

        return to_route('admin.newsletter-subscribers.show', $subscriber)
            ->with('success', 'Newsletter subscriber created successfully.');
    }

    public function show(NewsletterSubscriber $newsletterSubscriber): View
    {
        return view('admin.newsletter-subscribers.show', compact('newsletterSubscriber'));
    }

    public function edit(NewsletterSubscriber $newsletterSubscriber): View
    {
        return view('admin.newsletter-subscribers.edit', compact('newsletterSubscriber'));
    }

    public function update(NewsletterSubscriberRequest $request, NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        $newsletterSubscriber->update($this->withStatusDates($request->validated(), $newsletterSubscriber));

        return to_route('admin.newsletter-subscribers.show', $newsletterSubscriber)
            ->with('success', 'Newsletter subscriber updated successfully.');
    }

    public function destroy(NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        $newsletterSubscriber->delete();

        return to_route('admin.newsletter-subscribers.index')
            ->with('success', 'Newsletter subscriber deleted successfully.');
    }

    public function updateStatus(Request $request, NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(NewsletterSubscriber::STATUSES)],
        ]);
        $newsletterSubscriber->update($this->withStatusDates($validated, $newsletterSubscriber));

        return back()->with('success', 'Subscriber status updated successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->applyFilters(NewsletterSubscriber::query(), $this->filters($request))
            ->oldest('created_at');

        return response()->streamDownload(function () use ($query): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['Name', 'Email', 'Phone', 'Source', 'Status', 'Subscribed At', 'Unsubscribed At', 'Created At']);

            foreach ($query->cursor() as $subscriber) {
                fputcsv($stream, array_map($this->csvValue(...), [
                    $subscriber->name,
                    $subscriber->email,
                    $subscriber->phone,
                    NewsletterSubscriber::label($subscriber->source),
                    NewsletterSubscriber::label($subscriber->status),
                    $subscriber->subscribed_at?->format('Y-m-d H:i:s'),
                    $subscriber->unsubscribed_at?->format('Y-m-d H:i:s'),
                    $subscriber->created_at?->format('Y-m-d H:i:s'),
                ]));
            }

            fclose($stream);
        }, 'newsletter-subscribers-'.now()->format('Y-m-d-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /** @return array<string, mixed> */
    private function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search')),
            'source' => in_array($request->query('source'), NewsletterSubscriber::SOURCES, true) ? $request->query('source') : '',
            'status' => in_array($request->query('status'), NewsletterSubscriber::STATUSES, true) ? $request->query('status') : '',
            'dateFrom' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('date_from')) ? $request->query('date_from') : '',
            'dateTo' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('date_to')) ? $request->query('date_to') : '',
        ];
    }

    /** @param array<string, mixed> $filters */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($filters) {
                $search = $filters['search'];
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            }))
            ->when($filters['source'] !== '', fn (Builder $query) => $query->where('source', $filters['source']))
            ->when($filters['status'] !== '', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when($filters['dateFrom'] !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $filters['dateFrom']))
            ->when($filters['dateTo'] !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $filters['dateTo']));
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function withStatusDates(array $data, ?NewsletterSubscriber $subscriber = null): array
    {
        if ($data['status'] === 'subscribed') {
            $data['subscribed_at'] = $subscriber?->subscribed_at ?? now();
            $data['unsubscribed_at'] = null;
        } elseif ($data['status'] === 'unsubscribed') {
            $data['unsubscribed_at'] = $subscriber?->status === 'unsubscribed'
                ? ($subscriber->unsubscribed_at ?? now())
                : now();
        }

        return $data;
    }

    private function csvValue(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/^[=+\-@]/', $value) ? "'{$value}" : $value;
    }
}
