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
        Schema::create('water_tax_records', function (Blueprint $table) {
            $table->id();
            $table->integer('a_no'); // A.No. - Serial number
            $table->string('customer_no'); // Customer number
            $table->string('customer_name');
            $table->decimal('monthly_bill', 10, 2)->default(0); // Monthly Water Bill (Rs.)
            $table->string('period')->nullable(); // Period (e.g., "July 23–March 24")
            $table->decimal('balance', 10, 2)->default(0); // Balance amount
            $table->decimal('oversize_charge', 10, 2)->default(0); // Oversize 10%
            $table->string('bill_no')->nullable();
            $table->string('receipt_no')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0); // Amount Paid (Rs.)
            $table->string('shera')->nullable(); // Remarks/Shera
            $table->string('phone')->nullable();
            $table->foreignId('citizen_id')->nullable()->constrained('citizens')->onDelete('set null');
            $table->timestamps();

            $table->index('customer_no');
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_tax_records');
    }
};
