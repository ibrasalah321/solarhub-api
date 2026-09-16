<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EngineerSpecialization extends Pivot
{
    protected $table = 'engineer_specializations';
    public $timestamps = false;

    protected $fillable = [
        'engineer_id',
        'specialization_id',
    ];

    protected function casts(): array
    {
        return [
            'engineer_id' => 'integer',
            'specialization_id' => 'integer',
        ];
    }
}