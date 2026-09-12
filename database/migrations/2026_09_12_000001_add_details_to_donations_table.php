<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('purpose')->nullable()->after('donor_name');
            $table->date('donation_date')->nullable()->after('amount');
            $table->string('email')->nullable()->after('donor_name');
            $table->string('contact_number')->nullable()->after('email');
            $table->string('proof_of_payment')->nullable()->after('payment_reference');
            $table->text('notes')->nullable()->after('proof_of_payment');
            $table->text('admin_notes')->nullable()->after('notes');
            $table->string('status')->default('pending_verification')->after('payment_status');
            $table->string('receipt_number')->nullable()->after('receipt_issued');
            $table->timestamp('verified_at')->nullable()->after('receipt_number');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'purpose',
                'donation_date',
                'email',
                'contact_number',
                'proof_of_payment',
                'notes',
                'admin_notes',
                'status',
                'receipt_number',
                'verified_at',
                'verified_by',
            ]);
        });
    }
};

