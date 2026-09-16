<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    use HasFactory;

    protected $table = 'offers';

    protected $fillable = [
        'service_request_id',
        'engineer_id',
        'proposed_cost',
        'execution_time_days',
        'technical_proposal',
        'proposal_file',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'service_request_id' => 'integer',
            'engineer_id' => 'integer',
            'proposed_cost' => 'decimal:2',
            'execution_time_days' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(EngineerProfile::class, 'engineer_id');
    }
}