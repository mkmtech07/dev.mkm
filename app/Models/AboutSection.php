<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutSection extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'image',
        'mission',
        'vision',
        'years_of_experience',
        'projects_completed',
        'clients_served',
        'team_members',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'years_of_experience' => 'integer',
            'projects_completed' => 'integer',
            'clients_served' => 'integer',
            'team_members' => 'integer',
        ];
    }
}
