<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@pilarshrine.test')],
            [
                'name' => env('ADMIN_NAME', 'Parish Administrator'),
                'password_hash' => Hash::make(env('ADMIN_PASSWORD', 'ChangeMe123!')),
                'role' => 'admin',
                'phone' => null,
                'is_verified' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
