<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('citizens', function (Blueprint $table) {
            $table->string('aadhar_card', 12)->nullable()->after('address');
            $table->unsignedTinyInteger('demand_number')->nullable()->after('aadhar_card');
        });

        // Seed existing records with random demand numbers (1-8)
        $citizens = DB::table('citizens')->get();
        foreach ($citizens as $citizen) {
            DB::table('citizens')
                ->where('id', $citizen->id)
                ->update(['demand_number' => rand(1, 8)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citizens', function (Blueprint $table) {
            $table->dropColumn(['aadhar_card', 'demand_number']);
        });
    }
};
