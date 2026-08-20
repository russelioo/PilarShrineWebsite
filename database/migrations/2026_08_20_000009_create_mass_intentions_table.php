<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mass_intentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mass_schedule_id')->constrained()->restrictOnDelete();
            $table->string('requested_by');
            $table->enum('intention_type', ['living', 'deceased', 'thanksgiving', 'special']);
            $table->text('names');
            $table->decimal('offering_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'offered', 'completed'])->default('pending');
            $table->date('requested_date');
            $table->date('offered_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mass_intentions');
    }
};
