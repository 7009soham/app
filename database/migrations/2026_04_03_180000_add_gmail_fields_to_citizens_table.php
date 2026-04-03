<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citizens', function (Blueprint $table) {
            $table->boolean('is_gmail_connected')->default(false)->after('email');
            $table->text('gmail_access_token')->nullable()->after('is_gmail_connected');
            $table->text('gmail_refresh_token')->nullable()->after('gmail_access_token');
            $table->boolean('banner_dismissed')->default(false)->after('gmail_refresh_token');
        });
    }

    public function down(): void
    {
        Schema::table('citizens', function (Blueprint $table) {
            $table->dropColumn(['is_gmail_connected', 'gmail_access_token', 'gmail_refresh_token', 'banner_dismissed']);
        });
    }
};
