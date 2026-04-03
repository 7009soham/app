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
        Schema::table('property_tax_records', function (Blueprint $table) {
            // Drop incompatible columns from old structure
            $table->dropColumn([
                'monthly_bill',
                'period',
                'oversize_charge',
                'bill_no',
                'receipt_no',
                'payment_date',
                'amount_paid',
                'shera'
            ]);
        });

        Schema::table('property_tax_records', function (Blueprint $table) {
            // Add new property tax specific columns after customer_name
            $table->string('property_no')->after('customer_no'); // Property Number (e.g., 1634, 111/1/2)
            $table->string('property_type')->default('RCC')->after('property_no'); // Property Type (RCC, etc.)
            
            // Previous Year Tax Details
            $table->decimal('previous_house_tax', 10, 2)->default(0)->after('property_type');
            $table->decimal('previous_electricity_tax', 10, 2)->default(0)->after('previous_house_tax');
            $table->decimal('previous_health_tax', 10, 2)->default(0)->after('previous_electricity_tax');
            $table->decimal('previous_total', 10, 2)->default(0)->after('previous_health_tax');
            
            // Current Year Tax Details
            $table->decimal('current_house_tax', 10, 2)->default(0)->after('previous_total');
            $table->decimal('current_electricity_tax', 10, 2)->default(0)->after('current_house_tax');
            $table->decimal('current_health_tax', 10, 2)->default(0)->after('current_electricity_tax');
            $table->decimal('current_total', 10, 2)->default(0)->after('current_health_tax');
            
            // Aadhaar number
            $table->string('aadhaar_no')->nullable()->after('phone');
            
            // Add indexes
            $table->index('property_no');
            $table->index('aadhaar_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_tax_records', function (Blueprint $table) {
            // Remove new columns
            $table->dropIndex(['property_no']);
            $table->dropIndex(['aadhaar_no']);
            
            $table->dropColumn([
                'property_no',
                'property_type',
                'previous_house_tax',
                'previous_electricity_tax',
                'previous_health_tax',
                'previous_total',
                'current_house_tax',
                'current_electricity_tax',
                'current_health_tax',
                'current_total',
                'aadhaar_no'
            ]);
        });

        Schema::table('property_tax_records', function (Blueprint $table) {
            // Restore old columns
            $table->decimal('monthly_bill', 10, 2)->default(0);
            $table->string('period')->nullable();
            $table->decimal('oversize_charge', 10, 2)->default(0);
            $table->string('bill_no')->nullable();
            $table->string('receipt_no')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('shera')->nullable();
        });
    }
};
