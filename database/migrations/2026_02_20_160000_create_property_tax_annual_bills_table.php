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
        Schema::create('property_tax_annual_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('citizen_id')->nullable()->constrained('citizens')->onDelete('set null');
            $table->foreignId('record_id')->constrained('property_tax_records')->onDelete('cascade');
            $table->string('customer_no');
            $table->string('customer_name');

            // Financial Year (e.g. "2025-26")
            $table->string('financial_year', 10); // "2025-26"
            $table->date('bill_period_start');     // April 1 current year
            $table->date('bill_period_end');       // March 31 next year

            // Tax breakdown (copied from record at time of billing)
            $table->decimal('house_tax', 10, 2)->default(0);
            $table->decimal('electricity_tax', 10, 2)->default(0);
            $table->decimal('health_tax', 10, 2)->default(0);
            $table->decimal('previous_balance', 10, 2)->default(0); // Carried-over arrears

            // Bill totals
            $table->decimal('bill_amount', 10, 2)->default(0); // Total annual bill
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);

            // Bill status
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->string('payment_method')->nullable(); // cash, online, cheque, etc.

            // Dates
            $table->date('due_date')->nullable();  // e.g. June 30 of the financial year
            $table->date('paid_date')->nullable();

            // References
            $table->string('bill_no')->unique()->nullable(); // Auto-generated bill number
            $table->string('transaction_id')->nullable();    // For online payments

            // Admin
            $table->unsignedBigInteger('marked_by')->nullable(); // Admin who marked paid
            $table->text('remarks')->nullable();

            $table->timestamps();

            // Unique constraint: one annual bill per property per financial year
            $table->unique(['record_id', 'financial_year'], 'unique_property_annual_bill');
            $table->index('citizen_id');
            $table->index('financial_year');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_tax_annual_bills');
    }
};
