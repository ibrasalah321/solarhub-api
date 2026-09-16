<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailOtp extends Model
{
    use HasFactory;

    protected $table = 'email_otps';

    protected $fillable = [
        'user_id',
        'email',
        'otp',
        'attempts',
        'expires_at',
        'verified_at',
    ];

    protected $hidden = [
        'otp',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'attempts' => 'integer',
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}