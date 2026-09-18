<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EngineerProfile extends Model
{
    use HasFactory;

    protected $table = 'engineer_profile';

    protected $fillable = [
        'user_id',
        'license_number',
        'years_of_experience',
        'bio',
        'cv_path',
        'profile_photo_path',
        'approval_status',
        'rejection_reason',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'years_of_experience' => 'integer',
            'approved_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(
            Specialization::class,
            'engineer_specializations',
            'engineer_id',
            'specialization_id'
        );
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(
            EngineerCertificate::class,
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

    public function portfolioItems(): HasMany
    {
        return $this->hasMany(
            PortfolioItem::class,
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