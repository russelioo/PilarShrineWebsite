<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_when_accessing_parishioner_donation_request(): void
    {
        $response = $this->get('/parishioner/donations/request');
        $response->assertRedirect('/login');
    }

    public function test_parishioner_can_view_donation_request_form(): void
    {
        $user = User::factory()->create([
            'role' => 'parishioner',
        ]);

        $response = $this->actingAs($user)->get('/parishioner/donations/request');
        $response->assertOk();
        $response->assertSee('Request Donation Acknowledgment / Receipt');
        $response->assertSee('Submit for Verification');
        $response->assertSee('09214309753');
        $response->assertSee('JOSE BURT SARE');
    }

    public function test_parishioner_can_submit_donation_with_proof(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name' => 'Maria Clara',
            'email' => 'maria@example.com',
            'role' => 'parishioner',
        ]);

        $file = UploadedFile::fake()->image('receipt.jpg', 600, 800)->size(2048); // 2MB

        $payload = [
            'donor_name' => 'Maria Clara',
            'purpose' => 'Altar Restoration & Sanctuary Care',
            'amount' => '2500.00',
            'donation_date' => now()->toDateString(),
            'payment_method' => 'GCash',
            'reference_number' => 'REF-GCASH-998811',
            'proof_of_payment' => $file,
            'email' => 'maria@example.com',
            'contact_number' => '09171234567',
            'notes' => 'Offering for thanksgiving mass.',
        ];

        $response = $this->actingAs($user)->post('/parishioner/donations', $payload);

        $response->assertRedirect('/parishioner/donations');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('donations', [
            'user_id' => $user->id,
            'donor_name' => 'Maria Clara',
            'amount' => 2500.00,
            'method' => 'GCash',
            'payment_reference' => 'REF-GCASH-998811',
            'status' => 'pending_verification',
        ]);

        $donation = Donation::first();
        $this->assertNotNull($donation->proof_of_payment);
        Storage::disk('public')->assertExists($donation->proof_of_payment);
    }

    public function test_parishioner_can_view_submitted_donations_list(): void
    {
        $user = User::factory()->create(['role' => 'parishioner']);

        Donation::create([
            'user_id' => $user->id,
            'donor_name' => 'Juan Dela Cruz',
            'purpose' => 'General Parish Support & Mission',
            'amount' => 1000.00,
            'donation_date' => now()->toDateString(),
            'payment_method' => 'InstaPay',
            'reference_number' => 'INSTA-12345',
            'status' => 'pending_verification',
            'email' => 'juan@example.com',
            'contact_number' => '09201234567',
        ]);

        $response = $this->actingAs($user)->get('/parishioner/donations');
        $response->assertOk();
        $response->assertSee('My Donations &amp; Receipts', false);
        $response->assertSee('INSTA-12345');
        $response->assertSee('Pending Verification');
    }

    public function test_admin_can_view_donations_management_and_filter(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Donation::create([
            'donor_name' => 'Donor Alpha',
            'purpose' => 'Poor and Feeding Ministries',
            'amount' => 500.00,
            'donation_date' => now()->toDateString(),
            'payment_method' => 'GCash',
            'reference_number' => 'REF-001',
            'status' => 'pending_verification',
            'email' => 'alpha@example.com',
            'contact_number' => '09181112233',
        ]);

        $response = $this->actingAs($admin)->get('/admin/donations');
        $response->assertOk();
        $response->assertSee('Parish Donations');
        $response->assertSee('Donor Alpha');
        $response->assertSee('REF-001');
    }

    public function test_admin_can_verify_donation_and_assign_receipt(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $donation = Donation::create([
            'donor_name' => 'Generous Supporter',
            'purpose' => 'Church Maintenance & Utilities',
            'amount' => 5000.00,
            'donation_date' => now()->toDateString(),
            'payment_method' => 'InstaPay',
            'reference_number' => 'INSTA-999',
            'status' => 'pending_verification',
            'email' => 'generous@example.com',
            'contact_number' => '09228889999',
        ]);

        $response = $this->actingAs($admin)->patch("/admin/donations/{$donation->id}/status", [
            'status' => 'receipt_ready',
            'receipt_number' => 'OR-2026-00042',
            'admin_notes' => 'Received with sincere gratitude. Official electronic receipt prepared.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $donation->refresh();
        $this->assertEquals('receipt_ready', $donation->status);
        $this->assertEquals('OR-2026-00042', $donation->receipt_number);
        $this->assertEquals($admin->id, $donation->verified_by);
        $this->assertNotNull($donation->verified_at);
    }

    public function test_admin_can_fetch_donor_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $d1 = Donation::create([
            'donor_name' => 'Recurring Donor',
            'purpose' => 'General Support',
            'amount' => 1000,
            'donation_date' => '2026-08-01',
            'payment_method' => 'GCash',
            'reference_number' => 'REF-REC-1',
            'status' => 'verified',
            'email' => 'recurring@example.com',
            'contact_number' => '09200000000',
        ]);

        $d2 = Donation::create([
            'donor_name' => 'Recurring Donor',
            'purpose' => 'Shrine Restoration',
            'amount' => 2000,
            'donation_date' => '2026-09-01',
            'payment_method' => 'InstaPay',
            'reference_number' => 'REF-REC-2',
            'status' => 'receipt_ready',
            'email' => 'recurring@example.com',
            'contact_number' => '09200000000',
        ]);

        $response = $this->actingAs($admin)->get("/admin/donations/{$d1->id}/history");
        $response->assertOk();
        $response->assertJsonStructure([
            'donor_name',
            'total_verified_amount',
            'total_verified_donations',
            'donations',
        ]);
        $response->assertJson([
            'donor_name' => 'Recurring Donor',
            'total_verified_amount' => 3000,
            'total_verified_donations' => 2,
        ]);
    }
}
