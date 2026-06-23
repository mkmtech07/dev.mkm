<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeadRequest;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $source = in_array($request->query('source'), Lead::SOURCES, true) ? $request->query('source') : '';
        $status = in_array($request->query('status'), Lead::STATUSES, true) ? $request->query('status') : '';
        $priority = in_array($request->query('priority'), Lead::PRIORITIES, true) ? $request->query('priority') : '';
        $followUp = in_array($request->query('follow_up'), ['today', 'overdue', 'upcoming'], true) ? $request->query('follow_up') : '';
        $serviceId = $request->integer('service');
        $dateFrom = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('date_from')) ? $request->query('date_from') : '';
        $dateTo = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('date_to')) ? $request->query('date_to') : '';

        $leads = Lead::query()
            ->with(['service:id,title', 'assignedUser:id,name', 'latestNote'])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('whatsapp', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            }))
            ->when($source !== '', fn ($query) => $query->where('source', $source))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($priority !== '', fn ($query) => $query->where('priority', $priority))
            ->when($serviceId > 0, fn ($query) => $query->where('service_id', $serviceId))
            ->when($followUp === 'today', fn ($query) => $query->whereDate('follow_up_date', today()))
            ->when($followUp === 'overdue', fn ($query) => $query->where('follow_up_date', '<', today()))
            ->when($followUp === 'upcoming', fn ($query) => $query->where('follow_up_date', '>=', tomorrow()))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $dateTo))
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Lead::query()->count(),
            'new' => Lead::query()->where('status', 'new')->count(),
            'follow_up' => Lead::query()->where('status', 'follow_up')->count(),
            'converted' => Lead::query()->where('status', 'converted')->count(),
            'urgent' => Lead::query()->where('priority', 'urgent')->count(),
            'today' => Lead::query()->whereDate('follow_up_date', today())->count(),
        ];
        $services = Service::query()->orderBy('title')->get(['id', 'title']);

        return view('admin.leads.index', compact(
            'leads', 'summary', 'services', 'search', 'source', 'status', 'priority',
            'followUp', 'serviceId', 'dateFrom', 'dateTo'
        ));
    }

    public function create(): View
    {
        return view('admin.leads.create', $this->formData(new Lead([
            'source' => 'manual', 'status' => 'new', 'priority' => 'medium', 'status_active' => true,
        ])));
    }

    public function store(LeadRequest $request): RedirectResponse
    {
        $lead = Lead::create($request->validated());

        return to_route('admin.leads.show', $lead)->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead): View
    {
        $lead->load(['service:id,title', 'assignedUser:id,name,email', 'notes.user:id,name']);

        return view('admin.leads.show', [
            'lead' => $lead,
            'leadNote' => new LeadNote(['note_type' => 'general']),
        ]);
    }

    public function edit(Lead $lead): View
    {
        return view('admin.leads.edit', $this->formData($lead));
    }

    public function update(LeadRequest $request, Lead $lead): RedirectResponse
    {
        $lead->update($request->validated());

        return to_route('admin.leads.show', $lead)->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $lead->delete();

        return to_route('admin.leads.index')->with('success', 'Lead deleted successfully.');
    }

    public function updateStatus(Request $request, Lead $lead): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Lead::STATUSES)],
        ]);
        $lead->update($validated);

        return back()->with('success', 'Lead status updated successfully.');
    }

    /** @return array<string, mixed> */
    private function formData(Lead $lead): array
    {
        return [
            'lead' => $lead,
            'services' => Service::query()->orderBy('title')->get(['id', 'title']),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ];
    }
}
