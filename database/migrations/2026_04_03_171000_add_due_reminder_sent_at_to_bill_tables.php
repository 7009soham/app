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
            if (!Schema::hasColumn('monthly_tax_bills', 'due_reminder_sent_at')) {
                $table->timestamp('due_reminder_sent_at')->nullable()->after('due_date');
                $table->index('due_reminder_sent_at');
            }
        });

        Schema::table('property_tax_annual_bills', function (Blueprint $table) {
            if (!Schema::hasColumn('property_tax_annual_bills', 'due_reminder_sent_at')) {
                $table->timestamp('due_reminder_sent_at')->nullable()->after('due_date');
                $table->index('due_reminder_sent_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_tax_bills', function (Blueprint $table) {
            if (Schema::hasColumn('monthly_tax_bills', 'due_reminder_sent_at')) {
                $table->dropIndex(['due_reminder_sent_at']);
                $table->dropColumn('due_reminder_sent_at');
            }
        });

        Schema::table('property_tax_annual_bills', function (Blueprint $table) {
            if (Schema::hasColumn('property_tax_annual_bills', 'due_reminder_sent_at')) {
                $table->dropIndex(['due_reminder_sent_at']);
                $table->dropColumn('due_reminder_sent_at');
            }
        });
    }
};
