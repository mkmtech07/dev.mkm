<?php

use App\Http\Controllers\Admin\AboutSectionController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\HeroSliderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\WebsiteSettingController;
use App\Http\Controllers\Frontend\BlogController as FrontendBlogController;
use App\Http\Controllers\ProfileController;
use App\Models\AboutSection;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\HeroSlider;
use App\Models\Page;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Route;

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

Route::get('/api/blogs', [FrontendBlogController::class, 'index'])
    ->name('frontend.blogs.index');
Route::get('/api/blogs/{slug}', [FrontendBlogController::class, 'show'])
    ->where('slug', '[A-Za-z0-9-]+')
    ->name('frontend.blogs.show');

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

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::redirect('/', '/admin/dashboard')->name('index');
        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
        Route::get('/settings', [WebsiteSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [WebsiteSettingController::class, 'update'])->name('settings.update');
        Route::resource('/hero-sliders', HeroSliderController::class)->except('show');
        Route::resource('/services', ServiceController::class)->except('show');
        Route::resource('/about', AboutSectionController::class)
            ->parameters(['about' => 'aboutSection'])
            ->except('show');
        Route::patch('/gallery/{gallery}/toggle-status', [GalleryController::class, 'toggleStatus'])
            ->name('gallery.toggle-status');
        Route::resource('/gallery', GalleryController::class)->except('show');
        Route::patch('/testimonials/{testimonial}/toggle-status', [TestimonialController::class, 'toggleStatus'])
            ->name('testimonials.toggle-status');
        Route::resource('/testimonials', TestimonialController::class)->except('show');
        Route::patch('/team-members/{teamMember}/toggle-status', [TeamMemberController::class, 'toggleStatus'])
            ->name('team-members.toggle-status');
        Route::resource('/team-members', TeamMemberController::class)
            ->parameters(['team-members' => 'teamMember'])
            ->except('show');
        Route::patch('/pages/{page}/toggle-status', [PageController::class, 'toggleStatus'])
            ->name('pages.toggle-status');
        Route::resource('/pages', PageController::class)->except('show');
        Route::patch('/faqs/{faq}/toggle-status', [FaqController::class, 'toggleStatus'])
            ->name('faqs.toggle-status');
        Route::resource('/faqs', FaqController::class)->except('show');
        Route::delete('/blog-categories/bulk-delete', [BlogCategoryController::class, 'bulkDelete'])
            ->name('blog-categories.bulk-delete');
        Route::patch('/blog-categories/{blogCategory}/restore', [BlogCategoryController::class, 'restore'])
            ->withTrashed()
            ->name('blog-categories.restore');
        Route::patch('/blog-categories/{blogCategory}/toggle-status', [BlogCategoryController::class, 'toggleStatus'])
            ->name('blog-categories.toggle-status');
        Route::resource('/blog-categories', BlogCategoryController::class)
            ->parameters(['blog-categories' => 'blogCategory'])
            ->except('show');
        Route::patch('/blogs/{blog}/toggle-status', [BlogController::class, 'toggleStatus'])
            ->name('blogs.toggle-status');
        Route::patch('/blogs/{blog}/toggle-featured', [BlogController::class, 'toggleFeatured'])
            ->name('blogs.toggle-featured');
        Route::resource('/blogs', BlogController::class)->except('show');
    });
});

require __DIR__.'/auth.php';

Route::get('/{any?}', function () {
    return view('frontend.app');
})->where('any', '^(?!admin|login|register|dashboard|logout|password).*$');
