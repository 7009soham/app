<?php

use App\Helpers\Transliterate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stores a romanised copy of every searchable name so staff can find a
 * Devanagari record by typing on an English keyboard.
 */
return new class extends Migration
{
    /** table => [source column => romanised column] */
    private const TARGETS = [
        'property_tax_records' => ['customer_name' => 'customer_name_roman'],
        'water_tax_records' => ['customer_name' => 'customer_name_roman'],
        'citizens' => ['name' => 'name_roman'],
        'grievances' => ['name' => 'name_roman'],
        'tax_payments' => ['citizen_name' => 'citizen_name_roman'],
    ];

    public function up(): void
    {
        foreach (self::TARGETS as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $columns) {
                foreach ($columns as $source => $target) {
                    if (!Schema::hasColumn($table, $target)) {
                        $blueprint->string($target)->nullable()->after($source);
                        $blueprint->index($target);
                    }
                }
            });

            $this->backfill($table, $columns);
        }
    }

    public function down(): void
    {
        foreach (self::TARGETS as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $columns) {
                foreach ($columns as $target) {
                    if (Schema::hasColumn($table, $target)) {
                        $blueprint->dropIndex([$target]);
                        $blueprint->dropColumn($target);
                    }
                }
            });
        }
    }

    /**
     * Chunked so the property tax table (thousands of rows) does not load
     * entirely into memory during deployment.
     */
    private function backfill(string $table, array $columns): void
    {
        foreach ($columns as $source => $target) {
            DB::table($table)
                ->select('id', $source)
                ->whereNotNull($source)
                ->where($source, '!=', '')
                ->orderBy('id')
                ->chunk(500, function ($rows) use ($table, $source, $target) {
                    foreach ($rows as $row) {
                        DB::table($table)
                            ->where('id', $row->id)
                            ->update([$target => Transliterate::toSearchKey($row->{$source})]);
                    }
                });
        }
    }
};
