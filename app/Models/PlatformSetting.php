<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    use HasFactory;

    protected $table = 'platform_settings';

    protected $fillable = [
        'platform_name',
        'support_phone',
        'support_email',
        'logo_path',
        'favicon_path',
        'currency',
        'is_maintenance_mode',
    ];

    protected function casts(): array
    {
        return [
            'is_maintenance_mode' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}