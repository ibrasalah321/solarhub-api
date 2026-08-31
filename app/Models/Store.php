<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $table = 'stores';

    protected $fillable = [
        'user_id',
        'company_name',
        'commercial_registry',
        'commercial_file_path',
        'tax_number',
        'bio',
        'company_logo_path',
        'approval_status',
        'rejection_reason',
        'store_type',
        'address_details',
        'location_coordinates',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'approved_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function storeProducts(): HasMany
    {
        return $this->hasMany(StoreProduct::class, 'store_id');
    }

    public function orderStores(): HasMany
    {
        return $this->hasMany(OrderStore::class, 'store_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(StoreRating::class, 'store_id');
    }
}