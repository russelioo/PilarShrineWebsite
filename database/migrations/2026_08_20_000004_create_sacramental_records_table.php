<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sacramental_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('record_type', ['baptism', 'confirmation', 'marriage', 'first_communion']);
            $table->date('date_performed');
            $table->string('officiating_priest');
            $table->string('certificate_number')->unique();
            $table->text('notes')->nullable();
            $table->boolean('certificate_issued')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sacramental_records');
    }
};
