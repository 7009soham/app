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
        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            // Nullable because a citizen can register by OTP before the office
            // has a customer number for them, and because 54 numbers in the
            // property ledger are shared by two owners. See
            // 2026_08_19_090000_make_citizens_customer_no_nullable, which applies
            // the same relaxation to databases created before this change.
            $table->string('customer_no')->nullable()->unique();
            $table->string('name');
            $table->string('phone')->nullable()->unique();
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('otp')->nullable(); // Temporary OTP storage (for fallback)
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citizens');
    }
};
