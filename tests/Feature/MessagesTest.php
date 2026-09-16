<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\Conversation;
use App\Models\InquiryAttachment;
use App\Models\InquiryMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_messaging_apis(): void
    {
        $this->getJson('/api/messages/conversations')->assertUnauthorized();
        $this->getJson('/api/messages/directory')->assertUnauthorized();
        $this->getJson('/api/messages/sync')->assertUnauthorized();
    }

    public function test_user_can_start_direct_conversation(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $response = $this->actingAs($sender)->postJson('/api/messages/conversations', [
            'recipient_id' => $recipient->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('conversations', [
            'type' => 'direct',
        ]);
        $this->assertDatabaseCount('conversation_participants', 2);
    }

    public function test_user_can_send_message_in_conversation(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $convResponse = $this->actingAs($sender)->postJson('/api/messages/conversations', [
            'recipient_id' => $recipient->id,
        ]);
        $conversationId = $convResponse->json('conversation.id');

        $sendResponse = $this->actingAs($sender)->postJson("/api/messages/conversations/{$conversationId}/messages", [
            'body' => 'Hello from modern messaging!',
        ]);

        $sendResponse->assertCreated();
        $this->assertEquals('Hello from modern messaging!', $sendResponse->json('message.body'));

        // Recipient can see the message
        $listResponse = $this->actingAs($recipient)->getJson("/api/messages/conversations/{$conversationId}/messages");
        $listResponse->assertOk();
        $this->assertCount(1, $listResponse->json('messages'));
        $this->assertEquals('Hello from modern messaging!', $listResponse->json('messages.0.body'));
    }

    public function test_commission_conversations_are_private_to_commission_members_and_clergy(): void
    {
        $commission = Commission::create([
            'name' => 'Commission on Youth',
            'slug' => 'commission-on-youth',
            'is_active' => true,
        ]);
        $member = User::factory()->create(['commission_id' => $commission->id, 'role' => 'commission_admin']);
        $outsider = User::factory()->create(['role' => 'parishioner']);
        $priest = User::factory()->create(['role' => 'parish_priest']);

        // Member creates commission conversation
        $convResponse = $this->actingAs($member)->postJson('/api/messages/conversations', [
            'commission_id' => $commission->id,
        ]);
        $convResponse->assertCreated();
        $conversationId = $convResponse->json('conversation.id');

        // Member sends message
        $this->actingAs($member)->postJson("/api/messages/conversations/{$conversationId}/messages", [
            'body' => 'Youth fellowship meeting agenda',
        ])->assertCreated();

        // Outsider attempts to view conversation details or messages -> 403 Forbidden
        $this->actingAs($outsider)->getJson("/api/messages/conversations/{$conversationId}")->assertForbidden();
        $this->actingAs($outsider)->getJson("/api/messages/conversations/{$conversationId}/messages")->assertForbidden();
        $this->actingAs($outsider)->postJson("/api/messages/conversations/{$conversationId}/messages", [
            'body' => 'Intruder message',
        ])->assertForbidden();

        // Priest (parish administration) CAN view and participate
        $this->actingAs($priest)->getJson("/api/messages/conversations/{$conversationId}")->assertOk();
        $this->actingAs($priest)->getJson("/api/messages/conversations/{$conversationId}/messages")->assertOk();
    }

    public function test_realtime_sync_endpoint_delivers_new_messages_and_read_status(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        $convRes = $this->actingAs($alice)->postJson('/api/messages/conversations', [
            'recipient_id' => $bob->id,
        ]);
        $convId = $convRes->json('conversation.id');

        // Alice sends message 1
        $msg1Res = $this->actingAs($alice)->postJson("/api/messages/conversations/{$convId}/messages", [
            'body' => 'Message 1',
        ]);
        $msg1Id = $msg1Res->json('message.id');

        // Bob syncs with no last_message_id
        $bobSync = $this->actingAs($bob)->getJson("/api/messages/sync?active_conversation_id={$convId}");
        $bobSync->assertOk();
        $this->assertCount(1, $bobSync->json('new_messages'));

        // Bob marks conversation as read
        $this->actingAs($bob)->postJson("/api/messages/conversations/{$convId}/read")->assertOk();

        // Alice syncs with last_message_id = msg1Id
        $aliceSync = $this->actingAs($alice)->getJson("/api/messages/sync?active_conversation_id={$convId}&last_message_id={$msg1Id}");
        $aliceSync->assertOk();
        $this->assertCount(0, $aliceSync->json('new_messages'));
        // Alice should receive read_status_updates containing msg1Id
        $this->assertContains($msg1Id, $aliceSync->json('read_status_updates'));
    }

    public function test_conversation_archive_toggle(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        $convRes = $this->actingAs($alice)->postJson('/api/messages/conversations', [
            'recipient_id' => $bob->id,
        ]);
        $convId = $convRes->json('conversation.id');

        // Archive
        $archiveRes = $this->actingAs($alice)->postJson("/api/messages/conversations/{$convId}/archive");
        $archiveRes->assertOk();
        $this->assertTrue($archiveRes->json('is_archived'));

        // Appears under archived filter
        $archivedList = $this->actingAs($alice)->getJson('/api/messages/conversations?filter=archived');
        $this->assertCount(1, $archivedList->json('conversations'));

        // Unarchive
        $unarchiveRes = $this->actingAs($alice)->postJson("/api/messages/conversations/{$convId}/archive");
        $unarchiveRes->assertOk();
        $this->assertFalse($unarchiveRes->json('is_archived'));
    }

    public function test_attachments_upload_and_preview_via_api(): void
    {
        Storage::fake('local');
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        $convRes = $this->actingAs($alice)->postJson('/api/messages/conversations', [
            'recipient_id' => $bob->id,
        ]);
        $convId = $convRes->json('conversation.id');

        $response = $this->actingAs($alice)->postJson("/api/messages/conversations/{$convId}/messages", [
            'body' => 'Look at this photo',
            'attachments' => [
                UploadedFile::fake()->image('bulletin.png'),
            ],
        ]);

        $response->assertCreated();
        $this->assertCount(1, $response->json('message.attachments'));
        $attachmentId = $response->json('message.attachments.0.id');

        // Preview stream
        $previewRes = $this->actingAs($bob)->get("/inquiries/attachments/{$attachmentId}?preview=1");
        $previewRes->assertOk();
    }

    public function test_directory_returns_all_users_and_supports_name_search(): void
    {
        $viewer = User::factory()->create(['name' => 'John Viewer', 'role' => 'user']);
        $secretary = User::factory()->create(['name' => 'Angela Gwyn Mansanero', 'role' => 'parish_secretary']);
        $admin = User::factory()->create(['name' => 'Father Roberto', 'role' => 'admin']);
        $parishioner = User::factory()->create(['name' => 'Maria Santos', 'role' => 'parishioner']);

        // Default directory call returns users and categories
        $response = $this->actingAs($viewer)->getJson('/api/messages/directory');
        $response->assertOk();
        $response->assertJsonStructure([
            'users',
            'commissions',
            'ministries',
            'categories' => [
                'all',
                'parishioner',
                'staff',
                'administrator',
            ],
        ]);

        $users = collect($response->json('users'));
        $this->assertTrue($users->contains('name', 'Angela Gwyn Mansanero'));
        $this->assertTrue($users->contains('name', 'Father Roberto'));
        $this->assertTrue($users->contains('name', 'Maria Santos'));

        // Parish Secretary is accessible under both administrator and staff categories
        $admins = collect($response->json('categories.administrator'));
        $this->assertTrue($admins->contains('name', 'Angela Gwyn Mansanero'));

        $staff = collect($response->json('categories.staff'));
        $this->assertTrue($staff->contains('name', 'Angela Gwyn Mansanero'));

        // Search query q=Angela specifically returns Angela
        $searchRes = $this->actingAs($viewer)->getJson('/api/messages/directory?q=Angela');
        $searchRes->assertOk();
        $searchResults = collect($searchRes->json('users'));
        $this->assertTrue($searchResults->contains('name', 'Angela Gwyn Mansanero'));
        $this->assertFalse($searchResults->contains('name', 'Maria Santos'));
    }
}
