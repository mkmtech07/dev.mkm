<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class AdminNotificationService
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(
        string $title,
        ?string $message = null,
        string $type = 'info',
        ?string $module = null,
        ?string $actionUrl = null,
        array $data = [],
        ?int $userId = null,
    ): ?AdminNotification {
        if (! Schema::hasTable('admin_notifications')) {
            return null;
        }

        $payload = [
            'title' => strip_tags($title),
            'message' => $message === null ? null : strip_tags($message),
            'type' => $type,
            'module' => $module ? Str::limit(Str::snake($module), 100, '') : null,
            'action_url' => $this->safeActionUrl($actionUrl),
            'data' => $this->sanitizeData($data),
            'user_id' => $userId,
            'created_by' => Auth::id(),
        ];

        $validator = Validator::make($payload, [
            'title' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'type' => ['required', 'in:info,success,warning,danger,system'],
            'module' => ['nullable', 'string', 'max:100'],
            'action_url' => ['nullable', 'string', 'max:500'],
            'data' => ['nullable', 'array'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if ($validator->fails()) {
            Log::warning('Admin notification validation failed.', [
                'errors' => $validator->errors()->keys(),
            ]);

            return null;
        }

        try {
            return AdminNotification::create($validator->validated());
        } catch (Throwable $exception) {
            Log::warning('Admin notification creation failed.', ['exception' => $exception::class]);

            return null;
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function notifyAllAdmins(
        string $title,
        ?string $message = null,
        string $type = 'info',
        ?string $module = null,
        ?string $actionUrl = null,
        array $data = [],
    ): ?AdminNotification {
        return $this->create($title, $message, $type, $module, $actionUrl, $data);
    }

    public function markAsRead(AdminNotification $notification): bool
    {
        if ($notification->is_read) {
            return false;
        }

        return $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAllAsRead(?User $user = null): int
    {
        if (! Schema::hasTable('admin_notifications')) {
            return 0;
        }

        return $this->visibleQuery($user)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function unreadCount(?User $user = null): int
    {
        if (! Schema::hasTable('admin_notifications')) {
            return 0;
        }

        return $this->visibleQuery($user)->unread()->count();
    }

    /** @return Collection<int, AdminNotification> */
    public function latest(?User $user = null, int $limit = 10): Collection
    {
        if (! Schema::hasTable('admin_notifications')) {
            return collect();
        }

        return $this->visibleQuery($user)
            ->latest()
            ->limit(max(1, min($limit, 50)))
            ->get();
    }

    /** @return Collection<int, AdminNotification> */
    public function latestUnread(?User $user = null, int $limit = 10): Collection
    {
        if (! Schema::hasTable('admin_notifications')) {
            return collect();
        }

        return $this->visibleQuery($user)
            ->unread()
            ->latest()
            ->limit(max(1, min($limit, 50)))
            ->get();
    }

    public function visibleQuery(?User $user = null): Builder
    {
        return AdminNotification::query()->visibleTo($user);
    }

    private function safeActionUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $url = trim($url);
        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);
        $host = parse_url($url, PHP_URL_HOST);

        if ($host && $host !== request()?->getHost()) {
            return null;
        }

        $path = '/'.ltrim((string) ($path ?: $url), '/');
        $path = str_replace('\\', '/', $path);

        if (str_contains($path, '..') || (! str_starts_with($path, '/admin') && $path !== '/dashboard')) {
            return null;
        }

        $safe = $path.($query ? '?'.$query : '');

        return Str::limit($safe, 500, '');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function sanitizeData(array $data): array
    {
        $sanitized = [];
        foreach (array_slice($data, 0, 50, true) as $key => $value) {
            $key = Str::limit((string) $key, 100, '');
            if ($this->sensitiveKey($key)) {
                $sanitized[$key] = '[REDACTED]';
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);
            } elseif (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
                $sanitized[$key] = $value;
            } else {
                $sanitized[$key] = Str::limit(strip_tags((string) $value), 1000, '');
            }
        }

        return $sanitized;
    }

    private function sensitiveKey(string $key): bool
    {
        return (bool) preg_match('/(?:password|passwd|remember_token|api_?key|secret|credential|authorization|access_?token|refresh_?token|unsubscribe_token|database_url|db_password|env)/i', $key);
    }
}
