<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantRequest;
use App\Models\AboutSection;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\HomepageSection;
use App\Models\Lead;
use App\Models\MediaFile;
use App\Models\Menu;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\Testimonial;
use App\Services\ActivityLogger;
use App\Services\AdminNotificationService;
use App\Services\TenantManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TenantController extends Controller
{
    /** @var array<string, class-string<Model>> */
    private const COUNT_MODELS = [
        'Pages' => Page::class,
        'Page Blocks' => PageBlock::class,
        'Menus' => Menu::class,
        'Services' => Service::class,
        'About Sections' => AboutSection::class,
        'Gallery Items' => Gallery::class,
        'Testimonials' => Testimonial::class,
        'FAQs' => Faq::class,
        'Homepage Sections' => HomepageSection::class,
        'Blogs' => Blog::class,
        'Blog Categories' => BlogCategory::class,
        'Media Files' => MediaFile::class,
        'Leads' => Lead::class,
        'Contact Messages' => ContactMessage::class,
    ];

    /** @var array<string, string> */
    private const MODULE_OPTIONS = [
        'website_settings' => 'Website Settings',
        'theme_settings' => 'Theme Customizer',
        'homepage_sections' => 'Homepage Sections',
        'hero_sliders' => 'Hero Sliders',
        'pages' => 'Pages',
        'page_blocks' => 'Page Blocks',
        'menus' => 'Menus',
        'footer' => 'Footer',
        'services' => 'Services',
        'gallery' => 'Gallery',
        'testimonials' => 'Testimonials',
        'faq' => 'FAQ',
        'blog' => 'Blog',
        'seo' => 'SEO',
        'media_library' => 'Media Library',
        'leads' => 'Leads',
        'newsletter' => 'Newsletter',
        'contact_messages' => 'Contact Messages',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = in_array($request->query('status'), Tenant::STATUSES, true) ? $request->query('status') : '';
        $demo = in_array($request->query('demo'), ['0', '1'], true) ? $request->query('demo') : '';

        $tenants = Tenant::query()
            ->with('setting')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('subdomain', 'like', "%{$search}%")
                    ->orWhere('custom_domain', 'like', "%{$search}%")
                    ->orWhere('client_email', 'like', "%{$search}%");
            }))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($demo !== '', fn ($query) => $query->where('is_demo', (bool) $demo))
            ->orderByDesc('is_demo')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.tenants.index', compact('tenants', 'search', 'status', 'demo'));
    }

    public function create(): View
    {
        return view('admin.tenants.create', $this->formData(new Tenant([
            'status' => Tenant::STATUS_ACTIVE,
            'is_demo' => true,
        ])));
    }

    public function store(TenantRequest $request, AdminNotificationService $notifications): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $tenant = Tenant::create($data);
        $tenant->setting()->create([
            'timezone' => config('app.timezone', 'UTC'),
            'locale' => config('app.locale', 'en'),
        ]);

        $notifications->notifyAllAdmins(
            'Tenant created',
            $tenant->name.' is ready for demo content.',
            'success',
            'tenants',
            route('admin.tenants.show', $tenant),
            ['tenant_id' => $tenant->id, 'is_demo' => $tenant->is_demo],
        );

        return to_route('admin.tenants.show', $tenant)->with('success', 'Tenant created successfully.');
    }

    public function show(Tenant $tenant): View
    {
        return view('admin.tenants.show', [
            'tenant' => $tenant->load('setting'),
            'counts' => $this->contentCounts($tenant),
        ]);
    }

    public function edit(Tenant $tenant): View
    {
        return view('admin.tenants.edit', $this->formData($tenant));
    }

    public function update(TenantRequest $request, Tenant $tenant, AdminNotificationService $notifications): RedirectResponse
    {
        $data = $request->validated();

        if ($tenant->isDefault()) {
            $data['slug'] = 'default';
            $data['status'] = Tenant::STATUS_ACTIVE;
            $data['is_demo'] = false;
            $data['demo_expires_at'] = null;
        }

        $tenant->update($data);

        if (! $tenant->isPubliclyAvailable()) {
            $notifications->notifyAllAdmins(
                'Tenant unavailable',
                $tenant->name.' is not publicly available.',
                'warning',
                'tenants',
                route('admin.tenants.show', $tenant),
                ['tenant_id' => $tenant->id, 'status' => $tenant->status],
            );
        }

        return to_route('admin.tenants.show', $tenant)->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant, TenantManager $tenants): RedirectResponse
    {
        abort_if($tenant->isDefault(), 422, 'The default tenant cannot be deleted.');

        $tenant->delete();

        if ($tenants->selectedTenant()?->id === $tenant->id && $defaultTenant = $tenants->defaultTenant()) {
            $tenants->switchTo($defaultTenant);
        }

        return to_route('admin.tenants.index')->with('success', 'Tenant deleted successfully.');
    }

    public function switch(Request $request, TenantManager $tenants, ActivityLogger $logger): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
        ]);

        $tenant = $tenants->switchTo((int) $validated['tenant_id']);

        if (! $tenant) {
            return back()->with('error', 'Unable to switch tenant.');
        }

        $logger->log('switch', 'tenants', 'Switched admin tenant to '.$tenant->name.'.', $tenant);

        return back()->with('success', 'Switched to '.$tenant->name.'.');
    }

    /** @return array<string, mixed> */
    private function formData(Tenant $tenant): array
    {
        return [
            'tenant' => $tenant,
            'statuses' => Tenant::STATUSES,
            'moduleOptions' => self::MODULE_OPTIONS,
        ];
    }

    /** @return array<int, array{label: string, count: int}> */
    private function contentCounts(Tenant $tenant): array
    {
        $counts = [];

        foreach (self::COUNT_MODELS as $label => $class) {
            $model = new $class();
            if (! Schema::hasTable($model->getTable()) || ! Schema::hasColumn($model->getTable(), 'tenant_id')) {
                continue;
            }

            $counts[] = [
                'label' => $label,
                'count' => $class::query()
                    ->withoutGlobalScope('tenant')
                    ->where($model->qualifyColumn('tenant_id'), $tenant->id)
                    ->count(),
            ];
        }

        return $counts;
    }
}
