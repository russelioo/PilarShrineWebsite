<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffUserSeeder extends Seeder
{
    public function run(): void
    {
        $staffUsers = [
            [
                'name' => env('STAFF_ONE_NAME', 'Maria Santos'),
                'email' => env('STAFF_ONE_EMAIL', 'maria.staff@pilarshrine.test'),
                'password' => env('STAFF_ONE_PASSWORD', 'Staff123!'),
                'phone' => '0928 123 4567',
            ],
            [
                'name' => env('STAFF_TWO_NAME', 'Pedro Cruz'),
                'email' => env('STAFF_TWO_EMAIL', 'pedro.staff@pilarshrine.test'),
                'password' => env('STAFF_TWO_PASSWORD', 'Staff123!'),
                'phone' => '0918 123 4567',
            ],
        ];

        foreach ($staffUsers as $staff) {
            User::query()->updateOrCreate(
                ['email' => $staff['email']],
                [
                    'name' => $staff['name'],
                    'password_hash' => Hash::make($staff['password']),
                    'role' => 'staff',
                    'phone' => $staff['phone'],
                    'is_verified' => true,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}