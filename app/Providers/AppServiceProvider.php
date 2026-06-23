<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ThemeSetting;
use App\Models\WebsiteSetting;
use App\Models\SeoSetting;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ActivityLogger::class);
    }

    public function boot(): void
    {
        $this->registerActivityListeners();

        View::composer('layouts.admin', function ($view): void {
            $view->with('websiteSettings', $this->websiteSettings());
        });

        View::composer('frontend.app', function ($view): void {
            $settings = $this->websiteSettings(activeOnly: true);
            $seoSettings = Schema::hasTable('seo_settings')
                ? SeoSetting::query()->oldest('id')->first()
                : null;
            $view->with('websiteSettings', $settings)
                ->with('seoSettings', $seoSettings ?? new SeoSetting(SeoSetting::defaults()));
        });
    }

    private function registerActivityListeners(): void
    {
        Event::listen('eloquent.created: *', function (string $event, array $models): void {
            $model = $models[0] ?? null;
            if (! $model instanceof Model || ! $this->shouldLogAdminWrite($model)) {
                return;
            }

            $logger = app(ActivityLogger::class);
            $module = $logger->moduleForModel($model);
            $logger->created($module, $model, 'Created '.class_basename($model).' #'.$model->getKey().'.');
        });

        Event::listen('eloquent.updated: *', function (string $event, array $models): void {
            $model = $models[0] ?? null;
            if (! $model instanceof Model || ! $this->shouldLogAdminWrite($model)) {
                return;
            }

            $changes = collect($model->getChanges())->except('updated_at')->all();
            if ($changes === []) {
                return;
            }
            $oldValues = [];
            foreach (array_keys($changes) as $key) {
                $oldValues[$key] = $model->getOriginal($key);
            }

            $logger = app(ActivityLogger::class);
            $module = $logger->moduleForModel($model);
            $statusFields = ['status', 'status_active', 'is_read', 'is_featured', 'featured', 'newsletter_status', 'show_in_menu'];
            $statusCompanionFields = ['subscribed_at', 'unsubscribed_at'];
            $changedKeys = array_keys($changes);
            $isStatusChange = array_intersect($changedKeys, $statusFields) !== []
                && array_diff($changedKeys, [...$statusFields, ...$statusCompanionFields]) === [];
            $description = ($isStatusChange ? 'Changed status for ' : 'Updated ')
                .class_basename($model).' #'.$model->getKey().'.';

            if ($isStatusChange) {
                $logger->statusChanged($module, $model, $oldValues, $changes, $description);
            } elseif (in_array($module, ['website_settings', 'seo_settings'], true)) {
                $logger->log('settings', $module, $description, $model, $oldValues, $changes);
            } else {
                $logger->updated($module, $model, $oldValues, $changes, $description);
            }
        });

        Event::listen('eloquent.deleted: *', function (string $event, array $models): void {
            $model = $models[0] ?? null;
            if (! $model instanceof Model || ! $this->shouldLogAdminWrite($model)) {
                return;
            }

            $logger = app(ActivityLogger::class);
            $module = $logger->moduleForModel($model);
            $logger->deleted($module, $model, 'Deleted '.class_basename($model).' #'.$model->getKey().'.');
        });

        Event::listen(Login::class, function (Login $event): void {
            app(ActivityLogger::class)->log(
                'login',
                'authentication',
                'User logged in successfully.',
                status: 'success',
                user: $event->user,
            );
        });
        Event::listen(Logout::class, function (Logout $event): void {
            app(ActivityLogger::class)->log(
                'logout',
                'authentication',
                'User logged out.',
                status: 'success',
                user: $event->user,
            );
        });
        Event::listen(Failed::class, function (Failed $event): void {
            $email = data_get($event->credentials, 'email');
            $description = $email
                ? 'Failed login attempt for '.str($email)->limit(150, '').'.'
                : 'Failed login attempt.';
            app(ActivityLogger::class)->failed('authentication', $description, $event->user);
        });
    }

    private function shouldLogAdminWrite(Model $model): bool
    {
        if ($model instanceof ActivityLog || $model instanceof Role || $model instanceof Permission || $model instanceof ThemeSetting
            || ! Auth::check() || ! app()->bound('request')) {
            return false;
        }

        $routeName = request()->route()?->getName();

        return is_string($routeName)
            && str_starts_with($routeName, 'admin.')
            && in_array(request()->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }

    private function websiteSettings(bool $activeOnly = false): WebsiteSetting
    {
        $settings = null;

        if (Schema::hasTable('website_settings')) {
            $settings = WebsiteSetting::query()
                ->when(
                    $activeOnly && Schema::hasColumn('website_settings', 'status'),
                    fn ($query) => $query->where('status', true)
                )
                ->first();
        }

        return $settings ?? new WebsiteSetting([
            'site_name' => 'CMS Website',
            'site_tagline' => 'Professional Website CMS',
            'meta_title' => 'CMS Website',
            'meta_description' => 'Professional website powered by Laravel and Vue',
            'status' => true,
        ]);
    }
}
