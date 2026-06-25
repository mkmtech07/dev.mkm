<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailTemplateRequest;
use App\Models\EmailTemplate;
use App\Services\ActivityLogger;
use App\Services\AdminNotificationService;
use App\Services\EmailTemplateService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $templates = $this->applyFilters(EmailTemplate::query(), $filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.email-templates.index', [
            'templates' => $templates,
            ...$filters,
        ]);
    }

    public function create(EmailTemplateService $service): View
    {
        return view('admin.email-templates.create', [
            'template' => new EmailTemplate([
                'type' => 'custom',
                'status' => true,
                'is_default' => false,
                'available_variables' => $service->allVariables(),
            ]),
            'variables' => $service->allVariables(),
        ]);
    }

    public function store(EmailTemplateRequest $request): RedirectResponse
    {
        $template = EmailTemplate::create($request->validated());

        return to_route('admin.email-templates.show', $template)
            ->with('success', 'Email template created successfully.');
    }

    public function show(EmailTemplate $emailTemplate): View
    {
        return view('admin.email-templates.show', ['template' => $emailTemplate]);
    }

    public function edit(EmailTemplate $emailTemplate, EmailTemplateService $service): View
    {
        return view('admin.email-templates.edit', [
            'template' => $emailTemplate,
            'variables' => $service->allVariables(),
        ]);
    }

    public function update(
        EmailTemplateRequest $request,
        EmailTemplate $emailTemplate,
        AdminNotificationService $notifications
    ): RedirectResponse {
        $wasDefault = (bool) $emailTemplate->is_default;
        $emailTemplate->update($request->validated());

        if ($wasDefault || $emailTemplate->is_default) {
            $notifications->notifyAllAdmins(
                'Default Email Template Updated',
                "{$emailTemplate->name} email template was updated.",
                'warning',
                'email_templates',
                route('admin.email-templates.show', $emailTemplate, false),
                ['email_template_id' => $emailTemplate->id, 'type' => $emailTemplate->type]
            );
        }

        return to_route('admin.email-templates.show', $emailTemplate)
            ->with('success', 'Email template updated successfully.');
    }

    public function destroy(EmailTemplate $emailTemplate, AdminNotificationService $notifications): RedirectResponse
    {
        if ($emailTemplate->is_default) {
            return back()->with('error', 'Default email templates cannot be deleted.');
        }

        $name = $emailTemplate->name;
        $type = $emailTemplate->type;
        $emailTemplate->delete();

        $notifications->notifyAllAdmins(
            'Email Template Deleted',
            "{$name} email template was deleted.",
            'warning',
            'email_templates',
            route('admin.email-templates.index', absolute: false),
            ['email_template_id' => $emailTemplate->id, 'type' => $type]
        );

        return to_route('admin.email-templates.index')
            ->with('success', 'Email template deleted successfully.');
    }

    public function preview(
        EmailTemplate $emailTemplate,
        EmailTemplateService $service,
        ActivityLogger $logger
    ): View {
        $logger->log(
            'preview',
            'email_templates',
            'Previewed email template.',
            $emailTemplate,
            null,
            ['slug' => $emailTemplate->slug, 'type' => $emailTemplate->type]
        );

        return view('admin.email-templates.preview', [
            'template' => $emailTemplate,
            'subjectPreview' => $service->renderSubject($emailTemplate, $service->getSampleData()),
            'bodyPreview' => $service->renderBody($emailTemplate, $service->getSampleData()),
            'sampleData' => $service->getSampleData(),
        ]);
    }

    public function toggleStatus(EmailTemplate $emailTemplate): RedirectResponse
    {
        $emailTemplate->update(['status' => ! $emailTemplate->status]);

        return back()->with('success', 'Email template status updated successfully.');
    }

    /** @return array<string, string> */
    private function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search')),
            'type' => in_array($request->query('type'), EmailTemplate::TYPES, true) ? (string) $request->query('type') : '',
            'status' => in_array($request->query('status'), ['active', 'inactive'], true) ? (string) $request->query('status') : '',
        ];
    }

    /** @param array<string, string> $filters */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($filters) {
                $search = $filters['search'];
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            }))
            ->when($filters['type'] !== '', fn (Builder $query) => $query->where('type', $filters['type']))
            ->when($filters['status'] === 'active', fn (Builder $query) => $query->where('status', true))
            ->when($filters['status'] === 'inactive', fn (Builder $query) => $query->where('status', false));
    }
}
