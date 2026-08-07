<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-authored public pages (schemes, notices, committee members) so
     * content can be published without a developer. Linked from the footer via
     * quick_links.
     */
    public function up(): void
    {
        Schema::create('custom_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->boolean('is_published')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_pages');
    }
};
