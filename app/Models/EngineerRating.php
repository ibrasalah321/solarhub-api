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
        'engineer_id',
        'customer_id',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'customer_id'
        );
    }
}