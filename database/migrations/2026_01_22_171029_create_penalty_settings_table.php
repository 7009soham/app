<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalty_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tax_type'); // water_tax, property_tax
            $table->string('name'); // e.g., "Late Fee Tier 1"
            $table->integer('grace_days')->default(10); // Days after due date before penalty applies
            $table->decimal('penalty_percentage', 5, 2)->default(5.00); // Percentage of original amount
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalty_settings');
    }
};
