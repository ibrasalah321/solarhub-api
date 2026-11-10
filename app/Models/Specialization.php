<?php

namespace App\Models;

use App\Models\Concerns\FlushesPublicCatalogCache;
use App\Support\PublicCatalogCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Specialization extends Model
{
    use FlushesPublicCatalogCache;

    public static function publicCatalogCacheGroups(): array
    {
        return [PublicCatalogCache::SPECIALIZATIONS];
    }

    use HasFactory;

    protected $table = 'specializations';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function engineerProfiles(): BelongsToMany
    {
        return $this->belongsToMany(
            EngineerProfile::class,
            'engineer_specializations',
            'specialization_id',
            'engineer_id'
        );
    }
}
