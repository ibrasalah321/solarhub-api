<?php

namespace App\Models;

use App\Models\Concerns\FlushesPublicCatalogCache;
use App\Support\PublicCatalogCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use FlushesPublicCatalogCache;

    public static function publicCatalogCacheGroups(): array
    {
        return [PublicCatalogCache::BRANDS];
    }

    use HasFactory;

    protected $table = 'brands';

    protected $fillable = [
        'name',
        'logo',
        'website',
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

    public function masterProducts(): HasMany
    {
        return $this->hasMany(MasterProduct::class, 'brand_id');
    }
}
