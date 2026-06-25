<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Stringable;
use Throwable;

class ActivityLogger
{
    /**
     * @param array<string, mixed>|null $oldValues
     * @param array<string, mixed>|null $newValues
     */
    public function log(
        string $action,
        string $module,
        string $description,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        string $status = 'success',
        ?Authenticatable $user = null,
    ): ?ActivityLog {
        if (! Schema::hasTable('activity_logs')) {
            return null;
        }

        try {
            $request = app()->bound('request') ? request() : null;
            $user ??= Auth::user();
            $status = in_array($status, ActivityLog::STATUSES, true) ? $status : 'warning';

            return ActivityLog::withoutEvents(fn () => ActivityLog::create([
                'user_id' => $user?->getAuthIdentifier(),
                'user_name' => $this->userValue($user, 'name'),
                'user_email' => $this->userValue($user, 'email'),
                'action' => Str::limit(Str::snake($action), 100, ''),
                'module' => Str::limit(Str::snake($module), 150, ''),
                'model_type' => $model ? $model::class : null,
                'model_id' => $model?->getKey(),
                'description' => Str::limit(strip_tags($description), 5000, ''),
                'old_values' => $this->sanitize($oldValues),
                'new_values' => $this->sanitize($newValues),
                'ip_address' => $request?->ip(),
                'user_agent' => Str::limit((string) $request?->userAgent(), 2000, ''),
                'url' => $request ? Str::limit($request->url(), 2048, '') : null,
                'method' => $request?->method(),
                'status' => $status,
            ]));
        } catch (Throwable $exception) {
            Log::warning('Activity logging failed.', ['exception' => $exception::class]);

            return null;
        }
    }

    public function created(string $module, Model $model, string $description): ?ActivityLog
    {
        return $this->log('create', $module, $description, $model, null, $model->getAttributes());
    }

    /** @param array<string, mixed> $oldValues @param array<string, mixed> $newValues */
    public function updated(string $module, Model $model, array $oldValues, array $newValues, string $description): ?ActivityLog
    {
        return $this->log('update', $module, $description, $model, $oldValues, $newValues);
    }

    public function deleted(string $module, Model $model, string $description): ?ActivityLog
    {
        return $this->log('delete', $module, $description, $model, $model->getAttributes());
    }

    /** @param array<string, mixed> $oldValues @param array<string, mixed> $newValues */
    public function statusChanged(string $module, Model $model, array $oldValues, array $newValues, string $description): ?ActivityLog
    {
        return $this->log('status', $module, $description, $model, $oldValues, $newValues);
    }

    public function failed(string $module, string $description, ?Authenticatable $user = null): ?ActivityLog
    {
        return $this->log('failed_login', $module, $description, status: 'failed', user: $user);
    }

    public function moduleForModel(Model $model): string
    {
        $class = class_basename($model);
        $map = [
            'WebsiteSetting' => 'website_settings',
            'ThemeSetting' => 'theme_settings',
            'Menu' => 'menu_builder', 'MenuItem' => 'menu_builder',
            'FooterSetting' => 'footer_builder', 'FooterSection' => 'footer_builder',
            'FooterLink' => 'footer_builder', 'FooterSocialLink' => 'footer_builder',
            'MediaFile' => 'media_library',
            'SeoPage' => 'seo', 'SeoSetting' => 'seo_settings', 'SchemaMarkup' => 'seo',
            'MaintenanceSetting' => 'maintenance',
            'Lead' => 'leads', 'LeadNote' => 'leads',
            'NewsletterSubscriber' => 'newsletter',
            'BackupRecord' => 'backups',
            'AdminNotification' => 'notifications',
            'EmailTemplate' => 'email_templates',
            'EmailAutomationSetting' => 'email_automation',
            'MailSetting' => 'mail_settings',
            'MailLog' => 'mail_logs',
            'ContactMessage' => 'contact_messages',
            'HomepageSection' => 'homepage_sections',
            'HeroSlider' => 'hero_sliders',
            'BlogCategory' => 'blog', 'Blog' => 'blog',
        ];

        return $map[$class] ?? Str::snake(Str::pluralStudly($class));
    }

    /**
     * @param array<string, mixed>|null $values
     * @return array<string, mixed>|null
     */
    private function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $sanitized = [];
        foreach (array_slice($values, 0, 150, true) as $key => $value) {
            $key = (string) $key;
            if ($this->sensitiveKey($key)) {
                $sanitized[$key] = '[REDACTED]';
                continue;
            }
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize($value);
            } elseif (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
                $sanitized[$key] = $value;
            } elseif ($value instanceof Stringable) {
                $sanitized[$key] = Str::limit((string) $value, 5000, '…');
            } else {
                $sanitized[$key] = Str::limit((string) $value, 5000, '…');
            }
        }

        return $sanitized;
    }

    private function sensitiveKey(string $key): bool
    {
        return (bool) preg_match('/(?:password|passwd|remember_token|api_?key|secret|credential|authorization|access_?token|refresh_?token|unsubscribe_token|database_url|db_password)/i', $key);
    }

    private function userValue(?Authenticatable $user, string $key): ?string
    {
        if (! $user) {
            return null;
        }
        $value = data_get($user, $key);

        return $value === null ? null : Str::limit((string) $value, 255, '');
    }
}
