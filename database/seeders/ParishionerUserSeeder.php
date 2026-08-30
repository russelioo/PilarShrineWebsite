<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ParishionerUserSeeder extends Seeder
{
    public function run(): void
    {
        $parishioners = [
            [
                'name' => env('PARISHIONER_ONE_NAME', 'Juan Dela Cruz'),
                'email' => env('PARISHIONER_ONE_EMAIL', 'juan.parishioner@pilarshrine.test'),
                'password' => env('PARISHIONER_ONE_PASSWORD', 'Parishioner123!'),
                'phone' => '0917 123 4567',
            ],
            [
                'name' => env('PARISHIONER_TWO_NAME', 'Rosa Lim'),
                'email' => env('PARISHIONER_TWO_EMAIL', 'rosa.parishioner@pilarshrine.test'),
                'password' => env('PARISHIONER_TWO_PASSWORD', 'Parishioner123!'),
                'phone' => '0927 765 4321',
            ],
        ];

        foreach ($parishioners as $parishioner) {
            User::query()->updateOrCreate(
                ['email' => $parishioner['email']],
                [
                    'name' => $parishioner['name'],
                    'password_hash' => Hash::make($parishioner['password']),
                    'role' => 'user',
                    'phone' => $parishioner['phone'],
                    'is_verified' => true,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}