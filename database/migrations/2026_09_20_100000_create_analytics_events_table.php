<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->timestamp('occurred_at')->index();
            $table->char('visitor_id', 64)->index();
            $table->uuid('visit_id')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('audience', 10);
            $table->string('area', 10);
            $table->string('event_type', 24);
            $table->string('page', 160);
            $table->string('action', 80)->nullable();
            $table->char('deduplication_key', 64)->nullable()->unique();
            $table->index(['area', 'occurred_at']);
            $table->index(['event_type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
