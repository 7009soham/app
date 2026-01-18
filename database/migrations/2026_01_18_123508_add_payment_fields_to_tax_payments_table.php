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
            // Add new columns if they don't exist
            if (!Schema::hasColumn('tax_payments', 'tax_type')) {
                $table->string('tax_type')->nullable()->after('citizen_address');
            }
            if (!Schema::hasColumn('tax_payments', 'record_id')) {
                $table->unsignedBigInteger('record_id')->nullable()->after('tax_type');
            }
            if (!Schema::hasColumn('tax_payments', 'status')) {
                $table->string('status')->default('pending')->after('period_end');
            }
            if (!Schema::hasColumn('tax_payments', 'provider_transaction_id')) {
                $table->string('provider_transaction_id')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('tax_payments', 'payment_data')) {
                $table->json('payment_data')->nullable()->after('phonepe_transaction_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_payments', function (Blueprint $table) {
            $table->dropColumn(['tax_type', 'record_id', 'status', 'provider_transaction_id', 'payment_data']);
        });
    }
};

