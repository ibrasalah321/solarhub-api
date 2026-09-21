<?php

namespace App\Services\Auth;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    /**
     * Authenticate a user via a unified `login` field (email or phone) and
     * issue a named Sanctum token.
     *
     * @param  array{login: string, password: string}  $credentials
     * @return array{user: User, token: string}
     */
    public function login(array $credentials): array
    {
        $user = $this->findByLogin($credentials['login']);

        if ($user === null) {
            throw ValidationException::withMessages([
                'login' => ['No account was found with these credentials.'],
            ]);
        }

        if (! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The password is incorrect.'],
            ]);
        }

        abort_if(
            $user->email_verified_at === null,
            403,
            'Please verify your email address before logging in.'
        );

        abort_unless(
            $user->status === 'active',
            403,
            'Your account is suspended or inactive. Please contact support.'
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Revoke the access token used for the current request.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function getAuthenticatedUser(User $user): UserResource
    {
        $user->loadMissing(['governorate', 'store', 'engineerProfile']);

        return new UserResource($user);
    }

    private function findByLogin(string $login): ?User
    {
        $field = Str::contains($login, '@') ? 'email' : 'phone';

        return User::query()->where($field, $login)->first();
    }
}
