<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngineerCertificate extends Model
{
    use HasFactory;

    protected $table = 'engineer_certificates';

    protected $fillable = [
        'engineer_id',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'engineer_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(EngineerProfile::class, 'engineer_id');
    }
}