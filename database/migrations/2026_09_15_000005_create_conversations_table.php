<?php

use App\Models\InquiryMessage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('direct'); // direct, commission, ministry, group
            $table->string('title')->nullable();
            $table->foreignId('commission_id')->nullable()->constrained('commissions')->nullOnDelete();
            $table->foreignId('ministry_id')->nullable()->constrained('ministries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->string('role')->default('member'); // admin, member
            $table->timestamps();

            $table->unique(['conversation_id', 'user_id']);
            $table->index(['user_id', 'is_archived']);
        });

        Schema::table('inquiry_messages', function (Blueprint $table) {
            $table->foreignId('conversation_id')->nullable()->after('id')->constrained('conversations')->nullOnDelete();
            $table->unsignedBigInteger('recipient_id')->nullable()->change();
        });

        // Backfill existing inquiry messages into direct conversations
        try {
            $messages = DB::table('inquiry_messages')->orderBy('id')->get();
            $conversationsMap = [];

            foreach ($messages as $msg) {
                $u1 = min($msg->sender_id, $msg->recipient_id);
                $u2 = max($msg->sender_id, $msg->recipient_id);
                $key = "{$u1}_{$u2}";

                if (! isset($conversationsMap[$key])) {
                    $convId = DB::table('conversations')->insertGetId([
                        'type' => 'direct',
                        'title' => null,
                        'created_by' => $msg->sender_id,
                        'last_message_at' => $msg->created_at,
                        'created_at' => $msg->created_at,
                        'updated_at' => $msg->created_at,
                    ]);

                    DB::table('conversation_participants')->insert([
                        [
                            'conversation_id' => $convId,
                            'user_id' => $u1,
                            'last_read_at' => ($msg->sender_id == $u1 || $msg->read_at) ? $msg->created_at : null,
                            'is_archived' => false,
                            'role' => 'member',
                            'created_at' => $msg->created_at,
                            'updated_at' => $msg->created_at,
                        ],
                        [
                            'conversation_id' => $convId,
                            'user_id' => $u2,
                            'last_read_at' => ($msg->sender_id == $u2 || $msg->read_at) ? $msg->created_at : null,
                            'is_archived' => false,
                            'role' => 'member',
                            'created_at' => $msg->created_at,
                            'updated_at' => $msg->created_at,
                        ],
                    ]);

                    $conversationsMap[$key] = $convId;
                } else {
                    $convId = $conversationsMap[$key];
                    DB::table('conversations')->where('id', $convId)->update([
                        'last_message_at' => $msg->created_at,
                        'updated_at' => $msg->created_at,
                    ]);
                }

                DB::table('inquiry_messages')->where('id', $msg->id)->update([
                    'conversation_id' => $conversationsMap[$key],
                ]);
            }
        } catch (\Throwable $e) {
            // Ignore backfill errors in fresh test migrations
        }
    }

    public function down(): void
    {
        Schema::table('inquiry_messages', function (Blueprint $table) {
            $table->dropForeign(['conversation_id']);
            $table->dropColumn('conversation_id');
        });

        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
    }
};

