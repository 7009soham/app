<?php

namespace App\Console\Commands;

use App\Models\Citizen;
use App\Models\Grievance;
use App\Models\PropertyTaxRecord;
use App\Models\TaxPayment;
use App\Models\WaterTaxRecord;
use Illuminate\Console\Command;

/**
 * Recomputes the romanised name columns used by admin search.
 *
 * Needed after a bulk import or a direct SQL edit, since those bypass the
 * model event that normally keeps the columns in step.
 */
class RebuildSearchIndex extends Command
{
    protected $signature = 'search:reindex {--model= : Only this model, e.g. PropertyTaxRecord}';

    protected $description = 'Rebuild romanised name columns so English search matches Devanagari records';

    private const MODELS = [
        PropertyTaxRecord::class,
        WaterTaxRecord::class,
        Citizen::class,
        Grievance::class,
        TaxPayment::class,
    ];

    public function handle(): int
    {
        $only = $this->option('model');

        foreach (self::MODELS as $class) {
            if ($only && class_basename($class) !== $only) {
                continue;
            }

            $updated = 0;
            $total = $class::count();

            $this->info(class_basename($class) . ": {$total} rows");

            $class::query()->chunkById(500, function ($rows) use (&$updated) {
                foreach ($rows as $row) {
                    if ($row->refreshRomanisedColumns()) {
                        // saveQuietly: reindexing is not a content change and
                        // should not fire model events or touch timestamps.
                        $row->saveQuietly();
                        $updated++;
                    }
                }
            });

            $this->line("  updated {$updated}");
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
