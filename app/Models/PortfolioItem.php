<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioItem extends Model
{
    use HasFactory;

    protected $table = 'portfolio_items';

    protected $fillable = [
        'engineer_id',
        'governorate_id',
        'service_type_id',
        'project_title',
        'project_type',
        'system_capacity',
        'description',
        'image_path',
        'file_path',
        'address_text',
        'location_coordinates',
    ];

    protected function casts(): array
    {
        return [
            'engineer_id' => 'integer',
            'governorate_id' => 'integer',
            'service_type_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(EngineerProfile::class, 'engineer_id');
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'governorate_id');
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }
}