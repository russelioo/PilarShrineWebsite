<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppc_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_title'); // 'Parish Priest', 'Parochial Vicar', 'Lay Co-Chair', 'PPC Secretary', 'PPC Treasurer', 'Commission Chairperson', 'Representative', 'Member'
            $table->foreignId('commission_id')->nullable()->constrained('commissions')->nullOnDelete();
            $table->date('term_start')->nullable();
            $table->date('term_end')->nullable();
            $table->string('status')->default('active'); // 'active', 'inactive'
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['commission_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppc_members');
    }
};

