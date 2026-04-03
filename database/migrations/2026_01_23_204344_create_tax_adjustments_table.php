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
        Schema::create('tax_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('tax_type'); // water, property, both
            $table->decimal('percentage', 5, 2);
            $table->string('apply_to'); // all, selected, customer
            $table->string('filters')->nullable(); // JSON stored as string for specific IDs/Numbers
            $table->integer('affected_records_count')->default(0);
            $table->unsignedBigInteger('performed_by')->nullable(); // Admin ID
            $table->boolean('is_reverted')->default(false);
            $table->timestamp('reverted_at')->nullable();
            $table->unsignedBigInteger('reverted_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_adjustments');
    }
};
