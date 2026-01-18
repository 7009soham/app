<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $col) {
            $col->id();
            $col->unsignedBigInteger('citizen_id')->nullable();
            $col->string('tax_type')->comment('water_tax, property_tax');
            $col->unsignedBigInteger('bill_id')->nullable()->comment('Link to monthly_tax_bills');
            $col->decimal('amount', 15, 2);
            $col->string('payment_method')->comment('cash, online, cheque, bank_transfer');
            $col->string('transaction_id')->unique()->nullable();
            $col->string('status')->default('completed');
            $col->timestamp('paid_at')->useCurrent();
            $col->unsignedBigInteger('processed_by')->nullable()->comment('Admin ID if manual');
            $col->text('remarks')->nullable();
            $col->timestamps();

            $col->index(['citizen_id', 'tax_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
