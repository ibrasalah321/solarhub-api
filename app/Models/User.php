<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    use HasRoles {
        hasPermissionTo as spatieHasPermissionTo;
        getAllPermissions as spatieGetAllPermissions;
    }

    protected $table = 'users';

    protected $fillable = [
        'governorate_id',
        'name',
        'email',
        'phone',
        'password',

        'status',
        'default_coordinates',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'governorate_id' => 'integer',
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function emailOtps(): HasMany
    {
        return $this->hasMany(EmailOtp::class, 'user_id');
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'governorate_id');
    }

    public function store(): HasOne
    {
        return $this->hasOne(Store::class, 'user_id');
    }

    public function engineerProfile(): HasOne
    {
        return $this->hasOne(EngineerProfile::class, 'user_id');
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function userWallets(): HasMany
    {
        return $this->hasMany(UserWallet::class, 'user_id');
    }

    public function quoteRequests(): HasMany
    {
        return $this->hasMany(QuoteRequest::class, 'customer_id');
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'customer_id');
    }

    public function storeRatings(): HasMany
    {
        return $this->hasMany(StoreRating::class, 'customer_id');
    }

    public function engineerRatings(): HasMany
    {
        return $this->hasMany(EngineerRating::class, 'customer_id');
    }

    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(OrderPayment::class, 'verified_by');
    }

    /**
     * Resolve the professional approval status for this user based on their role.
     *
     * Approval is a property of the professional profile (engineer profile /
     * store), never of the role itself. Customers and admins are always
     * considered approved because they have no professional profile to gate.
     */
    public function professionalApprovalStatus(): string
    {
        if ($this->hasRole('engineer')) {
            return $this->engineerProfile?->approval_status ?? 'pending';
        }

        if ($this->hasRole('supplier')) {
            return $this->store?->approval_status ?? 'pending';
        }

        return 'approved';
    }

    /**
     * Determine whether the user's professional profile is approved.
     */
    public function isApprovedProfessional(): bool
    {
        return $this->professionalApprovalStatus() === 'approved';
    }

    /**
     * Determine if the user has (one of) the given permission(s).
     *
     * Rules:
     * 1. User ID 1 is always the unrestricted Super Admin with full access.
     * 2. If the user has direct permissions defined in `model_has_permissions`,
     *    those direct permissions take precedence and strictly control access.
     * 3. Otherwise, inherit permissions from assigned roles.
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->id === 1) {
            return true;
        }

        $hasDirect = $this->relationLoaded('permissions')
            ? $this->permissions->isNotEmpty()
            : $this->permissions()->exists();

        if ($hasDirect) {
            return $this->hasDirectPermission($permission);
        }

        return $this->spatieHasPermissionTo($permission, $guardName);
    }

    /**
     * Get all effective permissions for this user.
     */
    public function getAllPermissions(): \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection
    {
        if ($this->id === 1) {
            return \Spatie\Permission\Models\Permission::query()
                ->where('guard_name', 'web')
                ->get();
        }

        $direct = $this->relationLoaded('permissions')
            ? $this->permissions
            : $this->permissions()->get();

        if ($direct->isNotEmpty()) {
            return $direct;
        }

        if ($this->relationLoaded('roles')) {
            return $this->roles->flatMap(fn ($role) => $role->permissions ?? collect())->unique('id')->values();
        }

        return $this->spatieGetAllPermissions();
    }
}