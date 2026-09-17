<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_id')->constrained('commissions')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('planning'); // 'planning', 'ongoing', 'completed', 'on_hold', 'cancelled'
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->foreignId('lead_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['commission_id', 'status']);
        });

        Schema::create('commission_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_id')->constrained('commissions')->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->string('category')->default('General'); // 'Minutes', 'Guidelines', 'Financial', 'Report', 'Proposal', 'General'
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['commission_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_documents');
        Schema::dropIfExists('commission_projects');
    }
};

