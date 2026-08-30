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
        'certificate_name',
        'issuing_organization',
        'certificate_number',
        'issue_date',
        'expiry_date',
        'certificate_file',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
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
}