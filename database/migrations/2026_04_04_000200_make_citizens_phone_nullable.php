<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('citizens') || !Schema::hasColumn('citizens', 'phone')) {
            return;
        }

        $driver = DB::getDriverName();

        // Avoid requiring doctrine/dbal for a simple nullability change.
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `citizens` MODIFY `phone` VARCHAR(255) NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE citizens ALTER COLUMN phone DROP NOT NULL');
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement('ALTER TABLE citizens ALTER COLUMN phone NVARCHAR(255) NULL');
            return;
        }

        // SQLite has limited ALTER COLUMN support; skipping keeps migrations runnable in tests.
        if ($driver === 'sqlite') {
            return;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('citizens') || !Schema::hasColumn('citizens', 'phone')) {
            return;
        }

        $driver = DB::getDriverName();

        // Best-effort reversal. NOTE: This may fail if NULL values exist.
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `citizens` MODIFY `phone` VARCHAR(255) NOT NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE citizens ALTER COLUMN phone SET NOT NULL');
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement('ALTER TABLE citizens ALTER COLUMN phone NVARCHAR(255) NOT NULL');
            return;
        }

        if ($driver === 'sqlite') {
            return;
        }
    }
};
