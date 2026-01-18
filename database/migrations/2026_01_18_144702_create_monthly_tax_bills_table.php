<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_tax_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('citizen_id')->constrained()->onDelete('cascade');
            $table->enum('tax_type', ['water_tax', 'property_tax']);
            $table->foreignId('record_id'); // Links to water_tax_records or property_tax_records
            $table->string('customer_no');
            $table->string('customer_name');
            $table->year('bill_year');
            $table->tinyInteger('bill_month'); // 1-12
            $table->decimal('bill_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance', 10, 2);
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue'])->default('pending');
            $table->enum('payment_method', ['online', 'cash', 'cheque', 'bank_transfer'])->nullable();
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('admins')->onDelete('set null'); // Admin who marked as paid
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['citizen_id', 'tax_type', 'bill_year', 'bill_month']);
            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_tax_bills');
    }
};
