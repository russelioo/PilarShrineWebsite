<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiry_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users');
            $table->foreignId('recipient_id')->constrained('users');
            $table->text('body')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['sender_id', 'recipient_id', 'id']);
        });
        Schema::create('inquiry_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('inquiry_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('path');
            $table->string('name');
            $table->string('kind');
            $table->unsignedBigInteger('size');
            $table->date('upload_day');
            $table->timestamps();
            $table->index(['user_id', 'upload_day', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiry_attachments');
        Schema::dropIfExists('inquiry_messages');
    }
};
