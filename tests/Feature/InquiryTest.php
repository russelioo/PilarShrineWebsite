<?php

namespace Tests\Feature;

use App\Models\InquiryAttachment;
use App\Models\InquiryMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_and_admin_can_open_their_inquiries_pages(): void
    {
        foreach (['staff', 'admin'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $response = $this->actingAs($user)->get('/'.$role.'/inquiries');
            $response->assertOk();
            $response->assertSee('Inquiries &amp; messages', false);
            $response->assertDontSee('id="global-search-wrap"', false);
            $response->assertDontSee('placeholder="Search modules... (Ctrl+K)"', false);
        }
    }

    public function test_users_can_exchange_private_messages_and_mark_them_read(): void
    {
        [$sender, $recipient, $outsider] = User::factory()->count(3)->create();
        $this->actingAs($sender)->post('/inquiries', ['recipient_id' => $recipient->id, 'body' => 'Choir rehearsal on Sunday?'])->assertRedirect();
        $this->actingAs($recipient)->get('/inquiries?with='.$sender->id)->assertOk()->assertSee('Choir rehearsal on Sunday?');
        $this->assertNotNull(InquiryMessage::first()->read_at);
        $this->post('/inquiries', ['recipient_id' => $sender->id, 'body' => 'Yes, after Mass.'])->assertRedirect();
        $this->actingAs($sender)->get('/parishioner/inquiries?with='.$recipient->id)->assertOk()->assertSee('Yes, after Mass.');
        $this->actingAs($outsider)->get('/inquiries?with='.$sender->id)->assertOk()->assertDontSee('Choir rehearsal on Sunday?');
    }

    public function test_attachments_are_private_and_quotas_apply_across_recipients_and_reset_daily(): void
    {
        Storage::fake('local');
        [$sender, $recipient, $other] = User::factory()->count(3)->create();
        $this->travelTo(now('Asia/Manila')->startOfDay()->addHours(12));
        $this->actingAs($sender);
        for ($i = 0; $i < 3; $i++) {
            $this->post('/inquiries', ['recipient_id' => $recipient->id, 'attachments' => [UploadedFile::fake()->create('schedule.pdf', 10, 'application/pdf')]])->assertSessionHasNoErrors();
        }
        $this->post('/inquiries', ['recipient_id' => $other->id, 'attachments' => [UploadedFile::fake()->create('schedule.pdf', 10, 'application/pdf')]])->assertSessionHasErrors('attachments');
        $this->post('/inquiries', ['recipient_id' => $other->id, 'attachments' => [UploadedFile::fake()->image('choir.png'), UploadedFile::fake()->image('mass.png'), UploadedFile::fake()->image('church.png')]])->assertSessionHasNoErrors();
        $this->post('/inquiries', ['recipient_id' => $other->id, 'attachments' => [UploadedFile::fake()->image('fourth.png')]])->assertSessionHasErrors('attachments');
        $attachment = InquiryAttachment::first();
        $this->actingAs($other)->get('/inquiries/attachments/'.$attachment->id)->assertForbidden();
        $this->actingAs($recipient)->get('/inquiries/attachments/'.$attachment->id)->assertDownload('schedule.pdf');
        $this->travelTo(now('Asia/Manila')->addDay()->startOfDay());
        $this->actingAs($sender)->post('/inquiries', ['recipient_id' => $other->id, 'attachments' => [UploadedFile::fake()->create('schedule.pdf', 10, 'application/pdf')]])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('inquiry_attachments', 7);
    }

    public function test_invalid_messages_and_oversize_files_are_rejected(): void
    {
        Storage::fake('local');
        [$sender, $recipient] = User::factory()->count(2)->create();
        $this->get('/inquiries')->assertRedirect('/login');
        $this->actingAs($sender)->post('/inquiries', ['recipient_id' => $recipient->id, 'body' => '   '])->assertSessionHasErrors('body');
        $this->post('/inquiries', ['recipient_id' => $sender->id, 'body' => 'Hello'])->assertSessionHasErrors('recipient_id');
        $this->post('/inquiries', ['recipient_id' => $recipient->id, 'attachments' => [UploadedFile::fake()->create('big.pdf', 10241, 'application/pdf')]])->assertSessionHasErrors('attachments.0');
        $this->post('/inquiries', ['recipient_id' => $recipient->id, 'attachments' => [UploadedFile::fake()->create('script.html', 1, 'text/html')]])->assertSessionHasErrors('attachments.0');
        $this->assertDatabaseCount('inquiry_messages', 0);
        $this->post('/inquiries', ['recipient_id' => $recipient->id, 'attachments' => [UploadedFile::fake()->create('allowed.pdf', 10240, 'application/pdf')]])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('inquiry_messages', 1);
    }

    public function test_parish_secretary_and_clergy_open_inquiries_with_admin_layout(): void
    {
        $secretary = User::factory()->create([
            'name' => 'Angela Gwyn Mansanero',
            'role' => 'parish_secretary',
            'organization' => 'parish_administration',
            'position' => 'Parish Secretary',
        ]);

        $response = $this->actingAs($secretary)->get('/inquiries');
        $response->assertOk();
        // Uses Admin Portal layout
        $response->assertSee('Admin Navigation', false);
        $response->assertSee('Parish Secretary', false);
        $response->assertDontSee('Parishioner Portal', false);
    }
}
