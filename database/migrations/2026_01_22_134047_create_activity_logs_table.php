<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('causer_type')->nullable(); // App\Models\Admin or App\Models\Citizen
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->string('log_name')->nullable(); // e.g., 'payment', 'auth', 'system'
            $table->string('description'); // e.g., 'Admin X marked bill Y as paid'
            $table->nullableMorphs('subject'); // The object being acted upon
            $table->json('properties')->nullable(); // Extra data (changes, old values)
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->index(['causer_type', 'causer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
