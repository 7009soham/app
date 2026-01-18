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
        Schema::create('tax_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // House Tax, Water Tax
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('monthly_rate', 10, 2)->default(0);
            $table->decimal('quarterly_rate', 10, 2)->default(0);
            $table->decimal('yearly_rate', 10, 2)->default(0);
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_types');
    }
};
