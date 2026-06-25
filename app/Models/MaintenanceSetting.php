<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MaintenanceSetting extends Model
{
    public const MODE_FRONTEND_ONLY = 'frontend_only';
    public const MODE_FULL_SITE = 'full_site';
    public const MODES = [self::MODE_FRONTEND_ONLY, self::MODE_FULL_SITE];
    public const ROBOTS = ['index', 'noindex'];

    private const DEFAULT_EXCLUDED_PATHS = [
        '/admin',
        '/dashboard',
        '/profile',
        '/login',
        '/logout',
        '/register',
        '/forgot-password',
        '/password',
        '/email',
        '/verify-email',
        '/api/maintenance-status',
        '/assets',
        '/build',
        '/storage',
        '/favicon.ico',
        '/robots.txt',
        '/sitemap.xml',
        '/up',
    ];

    protected $fillable = [
        'status',
        'mode',
        'title',
        'message',
        'image',
        'button_text',
        'button_url',
        'start_at',
        'end_at',
        'allowed_ips',
        'excluded_paths',
        'retry_after_minutes',
        'meta_robots',
        'custom_css',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'retry_after_minutes' => 'integer',
        ];
    }

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'status' => false,
            'mode' => self::MODE_FRONTEND_ONLY,
            'retry_after_minutes' => 60,
            'meta_robots' => 'noindex',
        ];
    }

    public static function firstSetting(): ?self
    {
        if (! Schema::hasTable('maintenance_settings')) {
            return null;
        }

        return self::query()->oldest('id')->first();
    }

    public static function firstOrCreateSetting(): self
    {
        return self::query()->oldest('id')->firstOrCreate([], self::defaults());
    }

    public function isCurrentlyActive(?Carbon $now = null): bool
    {
        if (! $this->status) {
            return false;
        }

        $now ??= now();

        if ($this->start_at && $this->start_at->greaterThan($now)) {
            return false;
        }

        if ($this->end_at && $this->end_at->lessThan($now)) {
            return false;
        }

        return true;
    }

    public function shouldBlockRequest(Request $request, ?string $path = null): bool
    {
        if (! $this->isCurrentlyActive() || $this->requestIsBypassed($request, $path)) {
            return false;
        }

        if ($this->mode === self::MODE_FULL_SITE) {
            return true;
        }

        return $this->isFrontendPageRequest($request, $path);
    }

    public function requestIsBypassed(Request $request, ?string $path = null): bool
    {
        return $this->ipIsAllowed($request->ip())
            || $this->pathIsExcluded($path ?? $request->path());
    }

    public function ipIsAllowed(?string $ip): bool
    {
        if (! $ip) {
            return false;
        }

        return in_array($ip, $this->allowedIpList(), true);
    }

    public function pathIsExcluded(string $path): bool
    {
        $path = $this->normalizePath($path);

        foreach ($this->excludedPathList(includeDefaults: true) as $excludedPath) {
            if ($this->pathMatches($path, $excludedPath)) {
                return true;
            }
        }

        return false;
    }

    /** @return array<int, string> */
    public function allowedIpList(): array
    {
        return $this->splitLinesAndCommas($this->allowed_ips);
    }

    /** @return array<int, string> */
    public function excludedPathList(bool $includeDefaults = false): array
    {
        $paths = array_map($this->normalizePath(...), $this->splitLinesAndCommas($this->excluded_paths));

        if ($includeDefaults) {
            $paths = [...self::DEFAULT_EXCLUDED_PATHS, ...$paths];
        }

        return array_values(array_unique(array_filter($paths)));
    }

    public function retryAfterSeconds(): int
    {
        return max(1, (int) ($this->retry_after_minutes ?: 60)) * 60;
    }

    /** @return array<string, mixed> */
    public function publicPayload(bool $enabled): array
    {
        if (! $enabled) {
            return [
                'enabled' => false,
                'mode' => self::MODE_FRONTEND_ONLY,
                'title' => '',
                'message' => '',
                'image' => '',
                'button_text' => '',
                'button_url' => '',
                'start_at' => '',
                'end_at' => '',
                'retry_after_minutes' => 60,
                'meta_robots' => 'noindex',
                'custom_css' => '',
            ];
        }

        return [
            'enabled' => true,
            'mode' => $this->mode ?: self::MODE_FRONTEND_ONLY,
            'title' => $this->title ?: '',
            'message' => $this->message ?: '',
            'image' => $this->image ? asset($this->image) : '',
            'button_text' => $this->button_text ?: '',
            'button_url' => $this->button_url ?: '',
            'start_at' => $this->start_at?->toIso8601String() ?: '',
            'end_at' => $this->end_at?->toIso8601String() ?: '',
            'retry_after_minutes' => max(1, (int) ($this->retry_after_minutes ?: 60)),
            'meta_robots' => in_array($this->meta_robots, self::ROBOTS, true) ? $this->meta_robots : 'noindex',
            'custom_css' => $this->safeCss($this->custom_css),
        ];
    }

    private function isFrontendPageRequest(Request $request, ?string $path = null): bool
    {
        $path = $this->normalizePath($path ?? $request->path());

        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            return ! Str::startsWith($path, '/api');
        }

        return false;
    }

    /** @return array<int, string> */
    private function splitLinesAndCommas(?string $value): array
    {
        if (! $value) {
            return [];
        }

        return collect(preg_split('/[\r\n,]+/', $value) ?: [])
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function normalizePath(string $path): string
    {
        $path = trim($path);
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $path = '/'.ltrim($path, '/');
        $path = rtrim($path, '/');

        return $path === '' ? '/' : Str::lower($path);
    }

    private function pathMatches(string $path, string $excludedPath): bool
    {
        $excludedPath = $this->normalizePath($excludedPath);

        if ($excludedPath === '/') {
            return $path === '/';
        }

        if (Str::endsWith($excludedPath, '/*')) {
            $prefix = rtrim(Str::beforeLast($excludedPath, '/*'), '/');

            return $path === $prefix || Str::startsWith($path, $prefix.'/');
        }

        if (in_array($excludedPath, self::DEFAULT_EXCLUDED_PATHS, true)) {
            return $path === $excludedPath || Str::startsWith($path, $excludedPath.'/');
        }

        return $path === $excludedPath;
    }

    private function safeCss(?string $css): string
    {
        if (! $css) {
            return '';
        }

        if (preg_match('~</?style|</?script|javascript\s*:|expression\s*\(|@import|-moz-binding|behavior\s*:|url\s*\(\s*["\']?\s*(?:javascript|data)\s*:~i', $css)) {
            return '';
        }

        return $css;
    }
}
