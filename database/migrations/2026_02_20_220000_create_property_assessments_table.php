<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_assessments', function (Blueprint $table) {
            $table->id();

            // Assessment type
            $table->enum('assessment_type', ['regular', 'rented', 'extended'])->default('regular');
            $table->string('group_id')->nullable(); // For extended type: groups multiple rows

            // Core fields
            $table->integer('sr_no');
            $table->string('property_number');
            $table->string('description')->nullable(); // Description of property
            $table->string('owner_name');
            $table->string('tenant_name')->nullable(); // Only for rented type

            // Construction
            $table->integer('year_of_construction')->nullable();

            // Measurements
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('square_foot', 10, 2)->nullable();
            $table->decimal('square_meter', 10, 2)->nullable();

            // Ready Reckoner Rates
            $table->decimal('rr_rate_land', 12, 2)->default(0);
            $table->decimal('rr_rate_building', 12, 2)->default(0);

            // Valuation
            $table->decimal('amount_with_depreciation', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // Rates
            $table->decimal('rate_of_education', 8, 4)->default(0); // Depreciation rate
            $table->decimal('rate_of_bearable', 8, 4)->default(0);  // Tax rate (bearable)

            // Tax computation
            $table->decimal('capital_value', 14, 2)->default(0);
            $table->decimal('tax_rate', 8, 4)->default(0);

            // Taxes
            $table->decimal('house_tax', 10, 2)->default(0);
            $table->decimal('light_tax', 10, 2)->default(0);
            $table->decimal('health_tax', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0); // house + light + health

            // Financial year
            $table->string('financial_year', 10); // e.g. "2022-23"

            // Link to property tax record (optional)
            $table->foreignId('record_id')->nullable()
                  ->constrained('property_tax_records')->onDelete('set null');

            $table->timestamps();

            // Indexes
            $table->index('assessment_type');
            $table->index('group_id');
            $table->index('property_number');
            $table->index('financial_year');
            $table->index('owner_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_assessments');
    }
};
