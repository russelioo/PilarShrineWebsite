<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_redirects_to_vue_register_portal(): void
    {
        $this->get('/register')->assertRedirect('/#/register');
    }

    public function test_parishioner_can_register_with_valid_details(): void
    {
        $response = $this->postJson('/register', [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'date_of_birth' => '1995-10-12',
            'email' => 'juan.delacruz@example.com',
            'phone' => '0917 123 4567',
            'country' => 'Philippines',
            'region' => 'Bicol Region',
            'province' => 'Sorsogon',
            'municipality_city' => 'Pilar',
            'barangay' => 'Poblacion',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'redirect' => '/#/login',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Juan Dela Cruz',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan.delacruz@example.com',
            'phone' => '0917 123 4567',
            'country' => 'Philippines',
            'region' => 'Bicol Region',
            'province' => 'Sorsogon',
            'municipality_city' => 'Pilar',
            'barangay' => 'Poblacion',
            'role' => 'user',
        ]);
    }

    public function test_registration_fails_if_date_of_birth_is_in_the_future(): void
    {
        $response = $this->postJson('/register', [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'date_of_birth' => '2099-01-01',
            'email' => 'future.user@example.com',
            'phone' => '0917 123 4567',
            'country' => 'Philippines',
            'region' => 'Bicol Region',
            'province' => 'Sorsogon',
            'municipality_city' => 'Pilar',
            'barangay' => 'Poblacion',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date_of_birth']);
    }

    public function test_registration_fails_if_passwords_do_not_match(): void
    {
        $response = $this->postJson('/register', [
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'date_of_birth' => '1998-05-20',
            'email' => 'maria.santos@example.com',
            'phone' => '0918 987 6543',
            'country' => 'Philippines',
            'region' => 'Bicol Region',
            'province' => 'Sorsogon',
            'municipality_city' => 'Pilar',
            'barangay' => 'Danlog',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_ncr_parishioner_can_register_without_province(): void
    {
        $response = $this->postJson('/register', [
            'first_name' => 'Carlos',
            'last_name' => 'Reyes',
            'date_of_birth' => '1990-03-15',
            'email' => 'carlos.reyes@example.com',
            'phone' => '0920 111 2233',
            'country' => 'Philippines',
            'region' => 'NCR',
            'province' => null,
            'municipality_city' => 'Quezon City',
            'barangay' => 'Batasan Hills',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'carlos.reyes@example.com',
            'region' => 'NCR',
            'province' => null,
            'municipality_city' => 'Quezon City',
            'barangay' => 'Batasan Hills',
            'role' => 'user',
        ]);
    }
}
