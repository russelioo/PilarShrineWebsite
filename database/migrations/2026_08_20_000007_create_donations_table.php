<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('donor_name');
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['GCash', 'Bank', 'Card', 'Cash']);
            $table->string('payment_reference')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('transaction_id')->nullable()->unique();
            $table->string('received_by')->nullable();
            $table->boolean('receipt_issued')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
