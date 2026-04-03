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
        if (!Schema::hasColumn('citizens', 'email')) {
            Schema::table('citizens', function (Blueprint $table) {
                $table->string('email')->nullable()->unique()->after('phone');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('citizens', 'email')) {
            Schema::table('citizens', function (Blueprint $table) {
                $table->dropUnique('citizens_email_unique');
                $table->dropColumn('email');
            });
        }
    }
};
