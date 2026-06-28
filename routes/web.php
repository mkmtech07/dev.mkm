<?php

use App\Http\Controllers\Admin\AboutSectionController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\Api\AdminNotificationApiController;
use App\Http\Controllers\Admin\Api\MediaPickerController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\EmailAutomationSettingController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\HeroSliderController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\LeadNoteController;
use App\Http\Controllers\Admin\MailLogController;
use App\Http\Controllers\Admin\MailSettingController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\NewsletterSubscriberController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\TenantSettingController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\Website\FooterLinkController;
use App\Http\Controllers\Admin\Website\FooterSectionController;
use App\Http\Controllers\Admin\Website\FooterSettingController;
use App\Http\Controllers\Admin\Website\FooterSocialLinkController;
use App\Http\Controllers\Admin\Website\HomepageSectionController;
use App\Http\Controllers\Admin\Website\MediaLibraryController;
use App\Http\Controllers\Admin\Website\MaintenanceSettingController;
use App\Http\Controllers\Admin\Website\SchemaMarkupController;
use App\Http\Controllers\Admin\Website\PageBlockController;
use App\Http\Controllers\Admin\Website\SeoPageController;
use App\Http\Controllers\Admin\Website\SeoSettingController;
use App\Http\Controllers\Admin\Website\ThemeSettingController;
use App\Http\Controllers\Admin\Website\WebsiteSettingController;
use App\Http\Controllers\Frontend\BlogController as FrontendBlogController;
use App\Http\Controllers\Frontend\ContactMessageController as FrontendContactMessageController;
use App\Http\Controllers\Frontend\FooterController as FrontendFooterController;
use App\Http\Controllers\Frontend\HomepageSectionController as FrontendHomepageSectionController;
use App\Http\Controllers\Frontend\MaintenanceStatusController as FrontendMaintenanceStatusController;
use App\Http\Controllers\Frontend\MenuController as FrontendMenuController;
use App\Http\Controllers\Frontend\ThemeSettingController as FrontendThemeSettingController;
use App\Http\Controllers\Frontend\WebsiteSettingController as FrontendWebsiteSettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicLeadController;
use App\Http\Controllers\PublicNewsletterSubscriberController;
use App\Http\Controllers\PublicSeoController;
use App\Models\AboutSection;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\HeroSlider;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [PublicSeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/robots.txt', [PublicSeoController::class, 'robots'])->name('seo.robots');
Route::get('/api/maintenance-status', [FrontendMaintenanceStatusController::class, 'show'])
    ->name('frontend.maintenance-status.show');
Route::get('/api/seo', [PublicSeoController::class, 'show'])->name('seo.show');
Route::get('/api/seo/schema', [PublicSeoController::class, 'schema'])->name('seo.schema');
Route::post('/api/leads', [PublicLeadController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('frontend.leads.store');
Route::post('/api/newsletter/subscribe', [PublicNewsletterSubscriberController::class, 'subscribe'])
    ->middleware('throttle:6,1')
    ->name('frontend.newsletter.subscribe');
Route::post('/api/newsletter/unsubscribe', [PublicNewsletterSubscriberController::class, 'unsubscribe'])
    ->middleware('throttle:10,1')
    ->name('frontend.newsletter.unsubscribe');

Route::get('/api/hero-sliders', function () {
    $sliders = HeroSlider::query()
        ->where('status', true)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get()
        ->map(fn (HeroSlider $slider) => [
            'id' => $slider->id,
            'title' => $slider->title,
            'subtitle' => $slider->subtitle,
            'button_text' => $slider->button_text,
            'button_url' => $slider->button_url,
            'image_url' => $slider->image ? asset($slider->image) : null,
        ]);

    return response()->json(['data' => $sliders]);
})->name('frontend.hero-sliders.index');

Route::get('/api/services', function () {
    $services = Service::query()
        ->where('status', true)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get()
        ->map(fn (Service $service) => [
            'id' => $service->id,
            'title' => $service->title,
            'slug' => $service->slug,
            'short_description' => $service->short_description,
            'description' => $service->description,
            'icon' => $service->icon,
            'image_url' => $service->image ? asset($service->image) : null,
        ]);

    return response()->json(['data' => $services]);
})->name('frontend.services.index');

Route::get('/api/about-section', function () {
    $aboutSection = AboutSection::query()
        ->where('status', true)
        ->latest('updated_at')
        ->first();

    if (! $aboutSection) {
        return response()->json(['data' => null]);
    }

    return response()->json([
        'data' => [
            'id' => $aboutSection->id,
            'title' => $aboutSection->title,
            'subtitle' => $aboutSection->subtitle,
            'description' => $aboutSection->description,
            'image_url' => $aboutSection->image ? asset($aboutSection->image) : null,
            'mission' => $aboutSection->mission,
            'vision' => $aboutSection->vision,
            'years_of_experience' => $aboutSection->years_of_experience,
            'projects_completed' => $aboutSection->projects_completed,
            'clients_served' => $aboutSection->clients_served,
            'team_members' => $aboutSection->team_members,
        ],
    ]);
})->name('frontend.about-section.show');

Route::get('/api/gallery', function () {
    $gallery = Gallery::query()
        ->where('status', true)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get()
        ->map(fn (Gallery $item) => [
            'id' => $item->id,
            'title' => $item->title,
            'category' => $item->category,
            'image_url' => asset($item->image),
            'alt_text' => $item->alt_text,
        ]);

    return response()->json(['data' => $gallery]);
})->name('frontend.gallery.index');

Route::get('/api/testimonials', function () {
    $testimonials = Testimonial::query()
        ->where('status', true)
        ->orderByDesc('featured')
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get()
        ->map(fn (Testimonial $testimonial) => [
            'id' => $testimonial->id,
            'client_name' => $testimonial->client_name,
            'company' => $testimonial->company,
            'designation' => $testimonial->designation,
            'review' => $testimonial->review,
            'rating' => $testimonial->rating,
            'image_url' => $testimonial->image ? asset($testimonial->image) : null,
            'featured' => $testimonial->featured,
        ]);

    return response()->json(['data' => $testimonials]);
})->name('frontend.testimonials.index');

Route::get('/api/team-members', function () {
    $teamMembers = TeamMember::query()
        ->where('status', true)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get()
        ->map(fn (TeamMember $teamMember) => [
            'id' => $teamMember->id,
            'name' => $teamMember->name,
            'designation' => $teamMember->designation,
            'bio' => $teamMember->bio,
            'image_url' => $teamMember->image ? asset($teamMember->image) : null,
            'email' => $teamMember->email,
            'phone' => $teamMember->phone,
            'facebook_url' => $teamMember->facebook_url,
            'linkedin_url' => $teamMember->linkedin_url,
            'twitter_url' => $teamMember->twitter_url,
        ]);

    return response()->json(['data' => $teamMembers]);
})->name('frontend.team-members.index');

Route::get('/api/faqs', function () {
    $faqs = Faq::query()
        ->where('status', true)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get(['id', 'question', 'answer', 'category']);

    return response()->json(['data' => $faqs]);
})->name('frontend.faqs.index');

Route::get('/api/menus/header', [FrontendMenuController::class, 'header'])
    ->name('frontend.menus.header');

Route::get('/api/footer', [FrontendFooterController::class, 'show'])
    ->name('frontend.footer.show');

Route::get('/api/website-settings', [FrontendWebsiteSettingController::class, 'show'])
    ->name('frontend.website-settings.show');
Route::get('/api/theme-settings', [FrontendThemeSettingController::class, 'show'])
    ->name('frontend.theme-settings.show');

Route::get('/api/homepage-sections', [FrontendHomepageSectionController::class, 'index'])
    ->name('frontend.homepage-sections.index');

Route::get('/api/blogs', [FrontendBlogController::class, 'index'])
    ->name('frontend.blogs.index');
Route::get('/api/blogs/{slug}', [FrontendBlogController::class, 'show'])
    ->where('slug', '[A-Za-z0-9-]+')
    ->name('frontend.blogs.show');

Route::post('/contact-messages', [FrontendContactMessageController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('frontend.contact-messages.store');

Route::get('/api/pages/{slug}', function (string $slug) {
    $page = Page::query()
        ->where('slug', $slug)
        ->where('status', true)
        ->first();

    if (! $page) {
        return response()->json(['message' => 'Page not found.'], 404);
    }

    return response()->json([
        'data' => [
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'featured_image' => $page->featured_image ? asset($page->featured_image) : null,
        ],
    ]);
})->where('slug', '[A-Za-z0-9-]+')->name('frontend.pages.show');

Route::get('/api/pages/{slug}/blocks', function (string $slug) {
    $page = Page::query()
        ->where('slug', $slug)
        ->where('status', true)
        ->first();

    if (! $page) {
        return response()->json(['message' => 'Page not found.'], 404);
    }

    $blocks = $page->activeBlocks()
        ->get()
        ->map(fn (PageBlock $block) => [
            'title' => $block->title,
            'subtitle' => $block->subtitle,
            'block_key' => $block->block_key,
            'type' => $block->type,
            'content' => $block->publicContent(),
            'image' => $block->image ? asset($block->image) : null,
            'background_image' => $block->background_image ? asset($block->background_image) : null,
            'button_text' => $block->button_text,
            'button_url' => $block->button_url,
            'secondary_button_text' => $block->secondary_button_text,
            'secondary_button_url' => $block->secondary_button_url,
            'background_color' => $block->background_color,
            'text_color' => $block->text_color,
            'settings' => $block->settings ?: new stdClass(),
        ]);

    return response()->json(['blocks' => $blocks]);
})->where('slug', '[A-Za-z0-9-]+')->name('frontend.pages.blocks.index');

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'permission:dashboard.view'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::redirect('/', '/admin/dashboard')->name('index');
        Route::view('/dashboard', 'admin.dashboard')->middleware('permission:dashboard.view')->name('dashboard');

        Route::get('/tenants', [TenantController::class, 'index'])
            ->middleware('permission:tenants.view')
            ->name('tenants.index');
        Route::get('/tenants/create', [TenantController::class, 'create'])
            ->middleware('permission:tenants.create')
            ->name('tenants.create');
        Route::post('/tenants', [TenantController::class, 'store'])
            ->middleware('permission:tenants.create')
            ->name('tenants.store');
        Route::post('/tenants/switch', [TenantController::class, 'switch'])
            ->middleware('permission:tenants.switch')
            ->name('tenants.switch');
        Route::get('/tenants/{tenant}', [TenantController::class, 'show'])
            ->middleware('permission:tenants.view')
            ->name('tenants.show');
        Route::get('/tenants/{tenant}/edit', [TenantController::class, 'edit'])
            ->middleware('permission:tenants.edit')
            ->name('tenants.edit');
        Route::put('/tenants/{tenant}', [TenantController::class, 'update'])
            ->middleware('permission:tenants.edit')
            ->name('tenants.update');
        Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])
            ->middleware('permission:tenants.delete')
            ->name('tenants.destroy');
        Route::get('/tenants/{tenant}/settings', [TenantSettingController::class, 'edit'])
            ->middleware('permission:tenants.settings')
            ->name('tenants.settings.edit');
        Route::put('/tenants/{tenant}/settings', [TenantSettingController::class, 'update'])
            ->middleware('permission:tenants.settings')
            ->name('tenants.settings.update');

        Route::prefix('api/notifications')->name('api.notifications.')->group(function () {
            Route::get('/', [AdminNotificationApiController::class, 'index'])
                ->middleware('permission:notifications.view')->name('index');
            Route::get('/unread-count', [AdminNotificationApiController::class, 'unreadCount'])
                ->middleware('permission:notifications.view')->name('unread-count');
            Route::post('/mark-all-as-read', [AdminNotificationApiController::class, 'markAllRead'])
                ->middleware('permission:notifications.mark_read')->name('mark-all-read');
            Route::post('/{notification}/mark-as-read', [AdminNotificationApiController::class, 'markRead'])
                ->middleware('permission:notifications.mark_read')->name('mark-read');
            Route::delete('/{notification}', [AdminNotificationApiController::class, 'destroy'])
                ->middleware('permission:notifications.delete')->name('destroy');
        });
        Route::get('/api/media-picker', [MediaPickerController::class, 'index'])
            ->middleware('permission:media_library.view,media_picker.use')
            ->name('api.media-picker.index');
        Route::get('/api/media-picker/{mediaFile}', [MediaPickerController::class, 'show'])
            ->middleware('permission:media_library.view,media_picker.use')
            ->name('api.media-picker.show');
        Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllRead'])
            ->middleware('permission:notifications.mark_read')->name('notifications.mark-all-read');
        Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markRead'])
            ->middleware('permission:notifications.mark_read')->name('notifications.mark-read');
        Route::delete('/notifications/bulk-delete', [NotificationController::class, 'bulkDestroy'])
            ->middleware('permission:notifications.delete')->name('notifications.bulk-destroy');
        Route::resource('/notifications', NotificationController::class)
            ->only(['index', 'show', 'destroy'])
            ->middlewareFor(['index', 'show'], 'permission:notifications.view')
            ->middlewareFor('destroy', 'permission:notifications.delete');
        Route::get('/email-templates/{emailTemplate}/preview', [EmailTemplateController::class, 'preview'])
            ->middleware('permission:email_templates.preview')->name('email-templates.preview');
        Route::patch('/email-templates/{emailTemplate}/toggle-status', [EmailTemplateController::class, 'toggleStatus'])
            ->middleware('permission:email_templates.edit')->name('email-templates.toggle-status');
        Route::resource('/email-templates', EmailTemplateController::class)
            ->parameters(['email-templates' => 'emailTemplate'])
            ->middlewareFor(['index', 'show'], 'permission:email_templates.view')
            ->middlewareFor(['create', 'store'], 'permission:email_templates.create')
            ->middlewareFor(['edit', 'update'], 'permission:email_templates.edit')
            ->middlewareFor('destroy', 'permission:email_templates.delete');
        Route::get('/email-automation', [EmailAutomationSettingController::class, 'edit'])
            ->middleware('permission:email_automation.view')->name('email-automation.edit');
        Route::put('/email-automation', [EmailAutomationSettingController::class, 'update'])
            ->middleware('permission:email_automation.edit')->name('email-automation.update');
        Route::get('/mail-settings', [MailSettingController::class, 'edit'])
            ->middleware('permission:mail_settings.view')->name('mail-settings.edit');
        Route::put('/mail-settings', [MailSettingController::class, 'update'])
            ->middleware('permission:mail_settings.edit')->name('mail-settings.update');
        Route::post('/mail-settings/test', [MailSettingController::class, 'test'])
            ->middleware('permission:mail_settings.test')->name('mail-settings.test');
        Route::resource('/mail-logs', MailLogController::class)
            ->parameters(['mail-logs' => 'mailLog'])
            ->only(['index', 'show', 'destroy'])
            ->middlewareFor(['index', 'show'], 'permission:mail_logs.view')
            ->middlewareFor('destroy', 'permission:mail_logs.delete');
        Route::get('/settings', [WebsiteSettingController::class, 'edit'])
            ->middleware('permission:website_settings.view')->name('settings.edit');
        Route::put('/settings', [WebsiteSettingController::class, 'update'])
            ->middleware('permission:website_settings.edit')->name('settings.update');
        Route::get('/website/settings', [WebsiteSettingController::class, 'edit'])
            ->middleware('permission:website_settings.view')->name('website.settings.edit');
        Route::put('/website/settings', [WebsiteSettingController::class, 'update'])
            ->middleware('permission:website_settings.edit')->name('website.settings.update');
        Route::get('/website/theme-settings', [ThemeSettingController::class, 'edit'])
            ->middleware('permission:theme_settings.view')->name('website.theme-settings.edit');
        Route::put('/website/theme-settings', [ThemeSettingController::class, 'update'])
            ->middleware('permission:theme_settings.edit')->name('website.theme-settings.update');
        Route::post('/website/theme-settings/reset', [ThemeSettingController::class, 'reset'])
            ->middleware('permission:theme_settings.edit')->name('website.theme-settings.reset');
        Route::get('/website/maintenance', [MaintenanceSettingController::class, 'edit'])
            ->middleware('permission:maintenance.view')->name('website.maintenance.edit');
        Route::put('/website/maintenance', [MaintenanceSettingController::class, 'update'])
            ->middleware('permission:maintenance.edit')->name('website.maintenance.update');
        Route::patch('/website/homepage-sections/{homepageSection}/toggle-status', [HomepageSectionController::class, 'toggleStatus'])
            ->middleware('permission:homepage_sections.edit')
            ->name('website.homepage-sections.toggle-status');
        Route::resource('/website/homepage-sections', HomepageSectionController::class)
            ->parameters(['homepage-sections' => 'homepageSection'])
            ->except('show')
            ->names('website.homepage-sections')
            ->middlewareFor('index', 'permission:homepage_sections.view')
            ->middlewareFor(['create', 'store'], 'permission:homepage_sections.create')
            ->middlewareFor(['edit', 'update'], 'permission:homepage_sections.edit')
            ->middlewareFor('destroy', 'permission:homepage_sections.delete');
        Route::patch('/website/page-blocks/{pageBlock}/toggle-status', [PageBlockController::class, 'toggleStatus'])
            ->middleware('permission:page_blocks.edit')
            ->name('website.page-blocks.toggle-status');
        Route::resource('/website/page-blocks', PageBlockController::class)
            ->parameters(['page-blocks' => 'pageBlock'])
            ->names('website.page-blocks')
            ->middlewareFor(['index', 'show'], 'permission:page_blocks.view')
            ->middlewareFor(['create', 'store'], 'permission:page_blocks.create')
            ->middlewareFor(['edit', 'update'], 'permission:page_blocks.edit')
            ->middlewareFor('destroy', 'permission:page_blocks.delete');
        Route::patch('/website/media-library/{mediaFile}/toggle-status', [MediaLibraryController::class, 'toggleStatus'])
            ->middleware('permission:media_library.edit')
            ->name('website.media-library.toggle-status');
        Route::resource('/website/media-library', MediaLibraryController::class)
            ->parameters(['media-library' => 'mediaFile'])
            ->names('website.media-library')
            ->middlewareFor(['index', 'show'], 'permission:media_library.view')
            ->middlewareFor(['create', 'store'], 'permission:media_library.create')
            ->middlewareFor(['edit', 'update'], 'permission:media_library.edit')
            ->middlewareFor('destroy', 'permission:media_library.delete');
        Route::patch('/leads/{lead}/status', [LeadController::class, 'updateStatus'])
            ->middleware('permission:leads.edit')->name('leads.status.update');
        Route::post('/leads/{lead}/notes', [LeadNoteController::class, 'store'])
            ->middleware('permission:leads.edit')->name('leads.notes.store');
        Route::delete('/leads/{lead}/notes/{leadNote}', [LeadNoteController::class, 'destroy'])
            ->middleware('permission:leads.edit')
            ->name('leads.notes.destroy');
        Route::resource('/leads', LeadController::class)
            ->middlewareFor(['index', 'show'], 'permission:leads.view')
            ->middlewareFor(['create', 'store'], 'permission:leads.create')
            ->middlewareFor(['edit', 'update'], 'permission:leads.edit')
            ->middlewareFor('destroy', 'permission:leads.delete');
        Route::get('/backups/{backup}/download', [BackupController::class, 'download'])
            ->middleware('permission:backups.download')->name('backups.download');
        Route::resource('/backups', BackupController::class)
            ->only(['index', 'create', 'store', 'show', 'destroy'])
            ->middlewareFor(['index', 'show'], 'permission:backups.view')
            ->middlewareFor(['create', 'store'], 'permission:backups.create')
            ->middlewareFor('destroy', 'permission:backups.delete');
        Route::resource('/activity-logs', ActivityLogController::class)
            ->parameters(['activity-logs' => 'activityLog'])
            ->only(['index', 'show'])
            ->middleware('permission:activity_logs.view');
        Route::get('/newsletter-subscribers/export', [NewsletterSubscriberController::class, 'export'])
            ->middleware('permission:newsletter.view')
            ->name('newsletter-subscribers.export');
        Route::patch('/newsletter-subscribers/{newsletterSubscriber}/status', [NewsletterSubscriberController::class, 'updateStatus'])
            ->middleware('permission:newsletter.edit')
            ->name('newsletter-subscribers.status.update');
        Route::resource('/newsletter-subscribers', NewsletterSubscriberController::class)
            ->parameters(['newsletter-subscribers' => 'newsletterSubscriber'])
            ->middlewareFor(['index', 'show'], 'permission:newsletter.view')
            ->middlewareFor(['create', 'store'], 'permission:newsletter.create')
            ->middlewareFor(['edit', 'update'], 'permission:newsletter.edit')
            ->middlewareFor('destroy', 'permission:newsletter.delete');
        Route::prefix('website/seo')->name('website.seo.')->group(function () {
            Route::patch('/pages/{seoPage}/toggle-status', [SeoPageController::class, 'toggleStatus'])
                ->middleware('permission:seo.edit')
                ->name('pages.toggle-status');
            Route::resource('/pages', SeoPageController::class)
                ->parameters(['pages' => 'seoPage'])
                ->except('show')
                ->names('pages')
                ->middlewareFor('index', 'permission:seo.view')
                ->middlewareFor(['create', 'store'], 'permission:seo.create')
                ->middlewareFor(['edit', 'update'], 'permission:seo.edit')
                ->middlewareFor('destroy', 'permission:seo.delete');
            Route::get('/settings', [SeoSettingController::class, 'edit'])
                ->middleware('permission:seo.view')->name('settings.edit');
            Route::put('/settings', [SeoSettingController::class, 'update'])
                ->middleware('permission:seo.edit')->name('settings.update');
            Route::patch('/schema/{schemaMarkup}/toggle-status', [SchemaMarkupController::class, 'toggleStatus'])
                ->middleware('permission:seo.edit')
                ->name('schema.toggle-status');
            Route::resource('/schema', SchemaMarkupController::class)
                ->parameters(['schema' => 'schemaMarkup'])
                ->except('show')
                ->names('schema')
                ->middlewareFor('index', 'permission:seo.view')
                ->middlewareFor(['create', 'store'], 'permission:seo.create')
                ->middlewareFor(['edit', 'update'], 'permission:seo.edit')
                ->middlewareFor('destroy', 'permission:seo.delete');
        });
        Route::resource('/hero-sliders', HeroSliderController::class)->except('show')
            ->middlewareFor('index', 'permission:hero_sliders.view')
            ->middlewareFor(['create', 'store'], 'permission:hero_sliders.create')
            ->middlewareFor(['edit', 'update'], 'permission:hero_sliders.edit')
            ->middlewareFor('destroy', 'permission:hero_sliders.delete');
        Route::resource('/services', ServiceController::class)->except('show')
            ->middlewareFor('index', 'permission:services.view')
            ->middlewareFor(['create', 'store'], 'permission:services.create')
            ->middlewareFor(['edit', 'update'], 'permission:services.edit')
            ->middlewareFor('destroy', 'permission:services.delete');
        Route::resource('/about', AboutSectionController::class)
            ->parameters(['about' => 'aboutSection'])
            ->except('show')
            ->middlewareFor('index', 'permission:about.view')
            ->middlewareFor(['create', 'store'], 'permission:about.create')
            ->middlewareFor(['edit', 'update'], 'permission:about.edit')
            ->middlewareFor('destroy', 'permission:about.delete');
        Route::patch('/gallery/{gallery}/toggle-status', [GalleryController::class, 'toggleStatus'])
            ->middleware('permission:gallery.edit')
            ->name('gallery.toggle-status');
        Route::resource('/gallery', GalleryController::class)->except('show')
            ->middlewareFor('index', 'permission:gallery.view')
            ->middlewareFor(['create', 'store'], 'permission:gallery.create')
            ->middlewareFor(['edit', 'update'], 'permission:gallery.edit')
            ->middlewareFor('destroy', 'permission:gallery.delete');
        Route::patch('/testimonials/{testimonial}/toggle-status', [TestimonialController::class, 'toggleStatus'])
            ->middleware('permission:testimonials.edit')
            ->name('testimonials.toggle-status');
        Route::resource('/testimonials', TestimonialController::class)->except('show')
            ->middlewareFor('index', 'permission:testimonials.view')
            ->middlewareFor(['create', 'store'], 'permission:testimonials.create')
            ->middlewareFor(['edit', 'update'], 'permission:testimonials.edit')
            ->middlewareFor('destroy', 'permission:testimonials.delete');
        Route::patch('/team-members/{teamMember}/toggle-status', [TeamMemberController::class, 'toggleStatus'])
            ->middleware('permission:team_members.edit')
            ->name('team-members.toggle-status');
        Route::resource('/team-members', TeamMemberController::class)
            ->parameters(['team-members' => 'teamMember'])
            ->except('show')
            ->middlewareFor('index', 'permission:team_members.view')
            ->middlewareFor(['create', 'store'], 'permission:team_members.create')
            ->middlewareFor(['edit', 'update'], 'permission:team_members.edit')
            ->middlewareFor('destroy', 'permission:team_members.delete');
        Route::patch('/pages/{page}/toggle-status', [PageController::class, 'toggleStatus'])
            ->middleware('permission:pages.edit')
            ->name('pages.toggle-status');
        Route::resource('/pages', PageController::class)->except('show')
            ->middlewareFor('index', 'permission:pages.view')
            ->middlewareFor(['create', 'store'], 'permission:pages.create')
            ->middlewareFor(['edit', 'update'], 'permission:pages.edit')
            ->middlewareFor('destroy', 'permission:pages.delete');
        Route::patch('/menus/{menu}/toggle-status', [MenuController::class, 'toggleStatus'])
            ->middleware('permission:menus.edit')
            ->name('menus.toggle-status');
        Route::patch('/menus/{menu}/items/{menuItem}/toggle-status', [MenuItemController::class, 'toggleStatus'])
            ->scopeBindings()
            ->middleware('permission:menus.edit')
            ->name('menus.items.toggle-status');
        Route::resource('menus.items', MenuItemController::class)
            ->parameters(['items' => 'menuItem'])
            ->scoped()
            ->only(['index', 'store', 'edit', 'update', 'destroy'])
            ->middlewareFor('index', 'permission:menus.view')
            ->middlewareFor('store', 'permission:menus.create')
            ->middlewareFor(['edit', 'update'], 'permission:menus.edit')
            ->middlewareFor('destroy', 'permission:menus.delete');
        Route::resource('/menus', MenuController::class)->except('show')
            ->middlewareFor('index', 'permission:menus.view')
            ->middlewareFor(['create', 'store'], 'permission:menus.create')
            ->middlewareFor(['edit', 'update'], 'permission:menus.edit')
            ->middlewareFor('destroy', 'permission:menus.delete');
        Route::prefix('website/footer')->name('website.footer.')->group(function () {
            Route::get('/settings', [FooterSettingController::class, 'edit'])
                ->middleware('permission:footer.view')->name('settings.edit');
            Route::put('/settings', [FooterSettingController::class, 'update'])
                ->middleware('permission:footer.edit')->name('settings.update');
            Route::patch('/sections/{footerSection}/toggle-status', [FooterSectionController::class, 'toggleStatus'])
                ->middleware('permission:footer.edit')
                ->name('sections.toggle-status');
            Route::resource('/sections', FooterSectionController::class)
                ->parameters(['sections' => 'footerSection'])
                ->except('show')
                ->middlewareFor('index', 'permission:footer.view')
                ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'permission:footer.edit');
            Route::patch('/links/{footerLink}/toggle-status', [FooterLinkController::class, 'toggleStatus'])
                ->middleware('permission:footer.edit')
                ->name('links.toggle-status');
            Route::resource('/links', FooterLinkController::class)
                ->parameters(['links' => 'footerLink'])
                ->except('show')
                ->middlewareFor('index', 'permission:footer.view')
                ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'permission:footer.edit');
            Route::patch('/social/{footerSocialLink}/toggle-status', [FooterSocialLinkController::class, 'toggleStatus'])
                ->middleware('permission:footer.edit')
                ->name('social.toggle-status');
            Route::resource('/social', FooterSocialLinkController::class)
                ->parameters(['social' => 'footerSocialLink'])
                ->except('show')
                ->middlewareFor('index', 'permission:footer.view')
                ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'permission:footer.edit');
        });
        Route::patch('/faqs/{faq}/toggle-status', [FaqController::class, 'toggleStatus'])
            ->middleware('permission:faq.edit')
            ->name('faqs.toggle-status');
        Route::resource('/faqs', FaqController::class)->except('show')
            ->middlewareFor('index', 'permission:faq.view')
            ->middlewareFor(['create', 'store'], 'permission:faq.create')
            ->middlewareFor(['edit', 'update'], 'permission:faq.edit')
            ->middlewareFor('destroy', 'permission:faq.delete');
        Route::delete('/blog-categories/bulk-delete', [BlogCategoryController::class, 'bulkDelete'])
            ->middleware('permission:blog_categories.delete')
            ->name('blog-categories.bulk-delete');
        Route::patch('/blog-categories/{blogCategory}/restore', [BlogCategoryController::class, 'restore'])
            ->withTrashed()
            ->middleware('permission:blog_categories.edit')
            ->name('blog-categories.restore');
        Route::patch('/blog-categories/{blogCategory}/toggle-status', [BlogCategoryController::class, 'toggleStatus'])
            ->middleware('permission:blog_categories.edit')
            ->name('blog-categories.toggle-status');
        Route::resource('/blog-categories', BlogCategoryController::class)
            ->parameters(['blog-categories' => 'blogCategory'])
            ->except('show')
            ->middlewareFor('index', 'permission:blog_categories.view')
            ->middlewareFor(['create', 'store'], 'permission:blog_categories.create')
            ->middlewareFor(['edit', 'update'], 'permission:blog_categories.edit')
            ->middlewareFor('destroy', 'permission:blog_categories.delete');
        Route::patch('/blogs/{blog}/toggle-status', [BlogController::class, 'toggleStatus'])
            ->middleware('permission:blog.edit')
            ->name('blogs.toggle-status');
        Route::patch('/blogs/{blog}/toggle-featured', [BlogController::class, 'toggleFeatured'])
            ->middleware('permission:blog.edit')
            ->name('blogs.toggle-featured');
        Route::resource('/blogs', BlogController::class)->except('show')
            ->middlewareFor('index', 'permission:blog.view')
            ->middlewareFor(['create', 'store'], 'permission:blog.create')
            ->middlewareFor(['edit', 'update'], 'permission:blog.edit')
            ->middlewareFor('destroy', 'permission:blog.delete');
        Route::patch('/contact-messages/{contactMessage}/toggle-read', [ContactMessageController::class, 'toggleRead'])
            ->middleware('permission:contact_messages.view')
            ->name('contact-messages.toggle-read');
        Route::post('/contact-messages/{contactMessage}/convert-to-lead', [ContactMessageController::class, 'convertToLead'])
            ->middleware('permission:leads.create')
            ->name('contact-messages.convert-to-lead');
        Route::resource('/contact-messages', ContactMessageController::class)
            ->parameters(['contact-messages' => 'contactMessage'])
            ->only(['index', 'show', 'update', 'destroy'])
            ->middlewareFor(['index', 'show', 'update'], 'permission:contact_messages.view')
            ->middlewareFor('destroy', 'permission:contact_messages.delete');

        Route::patch('/roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])
            ->middleware('permission:roles.edit')->name('roles.toggle-status');
        Route::resource('/roles', RoleController::class)
            ->middlewareFor(['index', 'show'], 'permission:roles.view')
            ->middlewareFor(['create', 'store'], 'permission:roles.create')
            ->middlewareFor(['edit', 'update'], 'permission:roles.edit')
            ->middlewareFor('destroy', 'permission:roles.delete');
        Route::patch('/permissions/{permission}/toggle-status', [PermissionController::class, 'toggleStatus'])
            ->middleware('permission:permissions.edit')->name('permissions.toggle-status');
        Route::resource('/permissions', PermissionController::class)
            ->except('show')
            ->middlewareFor('index', 'permission:permissions.view')
            ->middlewareFor(['create', 'store'], 'permission:permissions.create')
            ->middlewareFor(['edit', 'update'], 'permission:permissions.edit')
            ->middlewareFor('destroy', 'permission:permissions.delete');
        Route::resource('/user-roles', UserRoleController::class)
            ->parameters(['user-roles' => 'user'])
            ->only(['index', 'edit', 'update'])
            ->middleware('permission:users.roles.manage');
    });
});

require __DIR__.'/auth.php';

Route::get('/{any?}', function () {
    return view('frontend.app');
})->where('any', '^(?!admin|login|register|dashboard|logout|password).*$');
