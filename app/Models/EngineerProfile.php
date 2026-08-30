<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EngineerProfile extends Model
{
    use HasFactory;

    protected $table = 'engineer_profile';

    protected $fillable = [
        'user_id',
        'bio',
        'experience_years',
        'service_area',
        'latitude',
        'longitude',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_available' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function specializations(): HasMany
    {
        return $this->hasMany(
            EngineerSpecialization::class,
            'engineer_id'
        );
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(
            EngineerCertificate::class,
            'engineer_id'
        );
    }

    public function portfolioItems(): HasMany
    {
        return $this->hasMany(
            PortfolioItem::class,
            'engineer_id'
        );
    }

    public function offers(): HasMany
    {
        return $this->hasMany(
            Offer::class,
            'engineer_id'
        );
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(
            EngineerRating::class,
            'engineer_id'
        );
    }
}