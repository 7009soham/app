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
        Schema::table('tax_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('tax_payments', 'status')) {
                $table->string('status')->default('pending')->after('period_end');
            }

            if (!Schema::hasColumn('tax_payments', 'failure_reason')) {
                $table->text('failure_reason')->nullable()->after('payment_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_payments', function (Blueprint $table) {
            if (Schema::hasColumn('tax_payments', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('tax_payments', 'failure_reason')) {
                $table->dropColumn('failure_reason');
            }
        });
    }
};
