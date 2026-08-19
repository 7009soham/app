<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * citizens.customer_no is unique and NOT NULL, but 54 customer numbers in the
 * property ledger are shared by two different owners. A citizen registering by
 * OTP therefore hit a duplicate-key error at login when the number picked up
 * from their tax record already belonged to someone else, and saw a 500 instead
 * of their bills.
 *
 * Nothing in the portal joins on citizens.customer_no: tax records are linked
 * through citizen_id. So the column is genuinely optional, and allowing NULL
 * lets the citizen in while leaving the collision for the office to reconcile.
 * MySQL permits many NULLs under a unique index, so uniqueness still holds for
 * every real value.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('citizens') || !Schema::hasColumn('citizens', 'customer_no')) {
            return;
        }

        // Raw DDL to avoid requiring doctrine/dbal for a nullability change.
        match (DB::getDriverName()) {
            'mysql' => DB::statement('ALTER TABLE `citizens` MODIFY `customer_no` VARCHAR(255) NULL'),
            'pgsql' => DB::statement('ALTER TABLE citizens ALTER COLUMN customer_no DROP NOT NULL'),
            'sqlsrv' => DB::statement('ALTER TABLE citizens ALTER COLUMN customer_no NVARCHAR(255) NULL'),
            // SQLite cannot ALTER a column's nullability; the test schema is
            // rebuilt from the create migration each run, so this is a no-op.
            default => null,
        };
    }

    public function down(): void
    {
        if (!Schema::hasTable('citizens') || !Schema::hasColumn('citizens', 'customer_no')) {
            return;
        }

        // Only reversible while no NULLs exist.
        match (DB::getDriverName()) {
            'mysql' => DB::statement('ALTER TABLE `citizens` MODIFY `customer_no` VARCHAR(255) NOT NULL'),
            'pgsql' => DB::statement('ALTER TABLE citizens ALTER COLUMN customer_no SET NOT NULL'),
            'sqlsrv' => DB::statement('ALTER TABLE citizens ALTER COLUMN customer_no NVARCHAR(255) NOT NULL'),
            default => null,
        };
    }
};
