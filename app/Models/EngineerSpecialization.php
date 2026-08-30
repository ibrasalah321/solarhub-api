<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngineerSpecialization extends Model
{
    use HasFactory;

    protected $table = 'engineer_specializations';

    protected $fillable = [
        'engineer_id',
        'specialization_id',
    ];

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(
            EngineerProfile::class,
            'engineer_id'
        );
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(
            Specialization::class,
            'specialization_id'
        );
    }
}