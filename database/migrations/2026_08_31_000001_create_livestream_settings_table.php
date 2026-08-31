<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('livestream_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_live')->default(false);
            $table->string('title')->default('Pilar Shrine is live');
            $table->string('url')->default('https://www.facebook.com/PilarShrineSorsogon');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('livestream_settings')->insert([
            'is_live' => false,
            'title' => 'Pilar Shrine is live',
            'url' => 'https://www.facebook.com/PilarShrineSorsogon',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('livestream_settings');
    }
};
