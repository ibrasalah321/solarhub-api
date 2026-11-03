<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()
            ->where('email', 'admin@solarhub.com')
            ->first();

        if ($admin === null) {
            $password = env('SOLARHUB_ADMIN_PASSWORD');

            if (! is_string($password) || $password === '') {
                throw new RuntimeException(
                    'Set SOLARHUB_ADMIN_PASSWORD before creating the admin account.'
                );
            }

            $admin = User::query()->create([
                'name' => 'مدير النظام',
                'email' => 'admin@solarhub.com',
                'email_verified_at' => now(),
                'phone' => '777000000',
                'password' => Hash::make($password),
                'status' => 'active',
            ]);
        }

        if ($admin->email_verified_at === null) {
            $admin->forceFill(['email_verified_at' => now()])->save();
        }

        $admin->syncRoles(['admin']);
    }
}
