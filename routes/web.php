<?php

use App\Http\Controllers\Admin\HeroSliderController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\WebsiteSettingController;
use App\Http\Controllers\ProfileController;
use App\Models\HeroSlider;
use App\Models\Service;
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
    });
});

require __DIR__.'/auth.php';

Route::get('/{any?}', function () {
    return view('frontend.app');
})->where('any', '^(?!admin|login|register|dashboard|logout|password).*$');
