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
        Schema::table('monthly_tax_bills', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['citizen_id']);
            
            // Modify citizen_id to be nullable
            $table->unsignedBigInteger('citizen_id')->nullable()->change();
            
            // Re-add the foreign key constraint with nullable support
            $table->foreign('citizen_id')
                  ->references('id')
                  ->on('citizens')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_tax_bills', function (Blueprint $table) {
            // Drop the nullable foreign key
            $table->dropForeign(['citizen_id']);
            
            // Revert to non-nullable
            $table->unsignedBigInteger('citizen_id')->nullable(false)->change();
            
            // Re-add the original foreign key constraint
            $table->foreign('citizen_id')
                  ->references('id')
                  ->on('citizens')
                  ->onDelete('cascade');
        });
    }
};
