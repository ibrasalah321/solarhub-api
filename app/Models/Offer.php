<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    use HasFactory;

    protected $table = 'offers';

    protected $fillable = [
        'engineer_id',
        'request_id',
        'price',
        'message',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(
            EngineerProfile::class,
            'engineer_id'
        );
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(
            ServiceRequest::class,
            'request_id'
        );
    }
}