<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A payment has to know which bill it settles.
 *
 * Until now tax_payments carried only record_id, so the settlement code
 * re-derived the bill from the clock: bill_year/bill_month = today for water,
 * currentFinancialYear() for property. Any payment against a bill for a
 * different period found nothing, and because the update was guarded by
 * `if ($bill)` the money was taken and the bill was silently left unpaid.
 *
 * bill_type is stored alongside because the two bill tables are separate
 * (monthly_tax_bills for water, property_tax_annual_bills for property), so the
 * id alone is ambiguous. No foreign key for the same reason.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('bill_id')->nullable()->after('record_id');
            $table->string('bill_type', 32)->nullable()->after('bill_id');

            $table->index(['bill_type', 'bill_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tax_payments', function (Blueprint $table) {
            $table->dropIndex(['bill_type', 'bill_id']);
            $table->dropColumn(['bill_id', 'bill_type']);
        });
    }
};
