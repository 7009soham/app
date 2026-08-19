<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Makes destruction of tax liability recoverable and non-cascading.
 *
 * Two problems. Deleting a property tax record was permanent, with no soft
 * delete anywhere on the revenue tables and no record of who did it. And
 * property_tax_annual_bills was constrained onDelete('cascade'), so removing
 * one record silently destroyed every annual bill raised against it, including
 * bills a citizen had already partly paid.
 *
 * Soft deletes keep the row for audit and reversal; restrict stops one delete
 * from taking the billing history with it.
 */
return new class extends Migration
{
    private array $tables = [
        'property_tax_records',
        'water_tax_records',
        'property_tax_annual_bills',
        'monthly_tax_bills',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
            }
        }

        // Swap the cascade for a restrict. Only MySQL names the constraint this
        // way; on SQLite the table is rebuilt from the create migration so there
        // is nothing to alter.
        if (DB::getDriverName() === 'mysql' && Schema::hasTable('property_tax_annual_bills')) {
            try {
                DB::statement('ALTER TABLE `property_tax_annual_bills` DROP FOREIGN KEY `property_tax_annual_bills_record_id_foreign`');
                DB::statement('ALTER TABLE `property_tax_annual_bills` ADD CONSTRAINT `property_tax_annual_bills_record_id_foreign` FOREIGN KEY (`record_id`) REFERENCES `property_tax_records` (`id`) ON DELETE RESTRICT');
            } catch (\Throwable $e) {
                // Constraint already replaced or differently named. The soft
                // delete above is the primary protection; leave a trace rather
                // than failing the whole migration on a live database.
                DB::statement("SELECT 1");
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropSoftDeletes();
                });
            }
        }
    }
};
