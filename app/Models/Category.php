<?php

namespace App\Models;

use App\Models\Concerns\FlushesPublicCatalogCache;
use App\Support\PublicCatalogCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use FlushesPublicCatalogCache;

    public static function publicCatalogCacheGroups(): array
    {
        return [PublicCatalogCache::CATEGORIES];
    }

    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'parent_id',
        'name_ar',
        'name_en',
        'slug',
        'icon',
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function masterProducts(): HasMany
    {
        return $this->hasMany(MasterProduct::class, 'category_id');
    }
}
