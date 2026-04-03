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
        Schema::table('citizens', function (Blueprint $table) {
            $table->dropColumn('demand_number');
            $table->foreignId('demand_id')->nullable()->constrained('demands')->nullOnDelete();
        });

        Schema::table('water_tax_records', function (Blueprint $table) {
            $table->foreignId('demand_id')->nullable()->constrained('demands')->nullOnDelete();
        });

        Schema::table('property_tax_records', function (Blueprint $table) {
            $table->foreignId('demand_id')->nullable()->constrained('demands')->nullOnDelete();
        });

        Schema::table('property_assessments', function (Blueprint $table) {
            $table->foreignId('demand_id')->nullable()->constrained('demands')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
