<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a quick link own the page it points at, so staff can write the content
 * while creating the link instead of making a page and pasting its URL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quick_links', function (Blueprint $table) {
            $table->foreignId('custom_page_id')
                ->nullable()
                ->after('url')
                // Deleting the page leaves the link in place pointing at a dead
                // URL, which is visible in the admin list, rather than silently
                // removing a link from the footer.
                ->constrained('custom_pages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quick_links', function (Blueprint $table) {
            $table->dropConstrainedForeignId('custom_page_id');
        });
    }
};
