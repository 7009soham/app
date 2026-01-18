<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tax_payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('citizen_id')->nullable(); // For later citizen login
            $table->string('citizen_name');
            $table->string('citizen_phone');
            $table->string('citizen_address')->nullable();
            $table->foreignId('tax_type_id')->constrained('tax_types')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('period_type', ['monthly', 'quarterly', 'yearly']);
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('payment_method')->default('phonepe');
            $table->string('phonepe_transaction_id')->nullable();
            $table->json('payment_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_payments');
    }
};
