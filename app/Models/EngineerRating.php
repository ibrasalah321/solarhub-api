<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngineerRating extends Model
{
    use HasFactory;

    protected $table = 'engineer_ratings';

    protected $fillable = [
        'service_request_id',
        'customer_id',
        'engineer_id',
        'rating',
        'comment',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'service_request_id' => 'integer',
            'customer_id' => 'integer',
            'engineer_id' => 'integer',
            'rating' => 'integer',
            'is_approved' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(EngineerProfile::class, 'engineer_id');
    }
}