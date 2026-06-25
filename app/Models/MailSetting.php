<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class MailSetting extends Model
{
    public const MAILERS = ['smtp', 'sendmail', 'log', 'array'];
    public const ENCRYPTIONS = ['tls', 'ssl', 'none'];

    protected $fillable = [
        'mailer',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'reply_to_address',
        'reply_to_name',
        'timeout',
        'test_recipient',
        'status',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'port' => 'integer',
            'timeout' => 'integer',
            'status' => 'boolean',
        ];
    }

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'mailer' => 'smtp',
            'encryption' => 'tls',
            'timeout' => 30,
            'status' => true,
        ];
    }

    public static function firstSetting(): ?self
    {
        if (! Schema::hasTable('mail_settings')) {
            return null;
        }

        return self::query()->oldest('id')->first();
    }

    public static function firstOrCreateSetting(): self
    {
        return self::query()->oldest('id')->firstOrCreate([], self::defaults());
    }

    public function isConfigured(): bool
    {
        if (! $this->status || ! in_array($this->mailer, self::MAILERS, true)) {
            return false;
        }

        if ($this->mailer !== 'smtp') {
            return true;
        }

        return filled($this->host);
    }

    public function hasStoredPassword(): bool
    {
        return filled($this->getRawOriginal('password'));
    }

    public static function label(?string $value): string
    {
        return str($value ?: 'none')->replace(['_', '-'], ' ')->title()->toString();
    }
}
