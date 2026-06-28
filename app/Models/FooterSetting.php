<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class FooterSetting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'footer_logo',
        'footer_description',
        'phone',
        'email',
        'whatsapp',
        'address',
        'copyright_text',
        'newsletter_status',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'newsletter_status' => 'boolean',
            'status' => 'boolean',
        ];
    }
}
