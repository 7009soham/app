<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who did what to the records that carry money or authority.
 *
 * Before this there was no way to answer "who deleted this property's tax
 * liability" or "who changed the PayU salt", which on a portal collecting
 * statutory tax is an accountability gap rather than a logging preference.
 *
 * Deliberately append-only in practice: nothing in the application updates or
 * deletes rows here, and admin_id is set null on delete so removing a staff
 * account cannot erase the trail of what they did.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('admin_email')->nullable();   // kept verbatim, survives account deletion
            $table->string('admin_role')->nullable();

            $table->string('action', 60);                // settings.payment.update, water_tax.delete
            $table->string('subject_type')->nullable();  // model class
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_label')->nullable(); // human handle: customer no, bill no

            // Secrets are masked before they reach here.
            $table->json('before')->nullable();
            $table->json('after')->nullable();

            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['action', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('admin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
    }
};
