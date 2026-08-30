<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $table = 'service_requests';

    protected $fillable = [
        'customer_id',
        'service_type_id',
        'governorate_id',
        'system_capacity_estimate',
        'location_details',
        'description',
        'attachment_file',
        'status',
        'location_coordinates',
    ];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'service_type_id' => 'integer',
            'governorate_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'governorate_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'service_request_id');
    }

    public function rating(): HasOne
    {
        return $this->hasOne(EngineerRating::class, 'service_request_id');
    }
}