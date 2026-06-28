<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSetting extends Model
{
    protected $fillable = [
        'tenant_id',
        'logo',
        'favicon',
        'primary_color',
        'secondary_color',
        'accent_color',
        'contact_email',
        'contact_phone',
        'whatsapp',
        'address',
        'meta_title',
        'meta_description',
        'custom_css',
        'custom_js',
        'timezone',
        'locale',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
