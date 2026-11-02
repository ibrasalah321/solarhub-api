<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * List user accounts (administrative use).
     */
    public function list(): LengthAwarePaginator
    {
        $perPage = (int) request()->input('per_page', 20);

        return User::query()
            ->with(['governorate', 'roles.permissions', 'permissions', 'store', 'engineerProfile'])
            ->latest()
            ->paginate($perPage > 0 ? min($perPage, 100) : 20);
    }

    /**
     * Create a new administrator account.
     */
    public function createAdmin(array $data): User
    {
        $user = new User();
        $user->name = $data['name'];
        $user->email = mb_strtolower(trim($data['email']));
        $user->phone = trim($data['phone']);
        $user->password = Hash::make($data['password']);
        $user->governorate_id = $data['governorate_id'] ?? null;
        $user->status = $data['status'] ?? 'active';
        $user->default_coordinates = $data['default_coordinates'] ?? null;
        $user->email_verified_at = now();
        $user->save();

        $user->syncRoles(['admin']);

        return $user->load(['governorate', 'roles']);
    }

    /**
     * Update the authenticated user's own profile.
     * Changing the password requires the current password to be supplied and correct.
     */
    public function updateProfile(User $user, array $data): User
    {
        if (isset($data['password'])) {
            abort_unless(
                Hash::check($data['current_password'], $user->password),
                422,
                'The current password is incorrect.'
            );

            $data['password'] = Hash::make($data['password']);
        }

        unset($data['current_password']);

        $user->update($data);

        return $user->fresh(['governorate', 'store', 'engineerProfile']);
    }

    /**
     * Update a user's account status (administrative action).
     *
     * NOTE: intended for platform administrators. See the module-level note
     * regarding the current absence of a role/permission system.
     */
    public function updateStatus(User $target, string $status): User
    {
        $target->update(['status' => $status]);

        // Revoke all active sessions when an account is deactivated.
        if ($status !== 'active') {
            $target->tokens()->delete();
        }

        return $target;
    }

    /**
     * Soft-delete a user account.
     */
    public function deleteAccount(User $user): void
    {
        $user->tokens()->delete();
        $user->delete();
    }
}
