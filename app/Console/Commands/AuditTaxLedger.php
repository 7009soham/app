<?php

namespace App\Console\Commands;

use App\Models\Citizen;
use App\Models\MonthlyTaxBill;
use App\Models\PropertyTaxAnnualBill;
use App\Models\PropertyTaxRecord;
use App\Models\TaxPayment;
use App\Models\WaterTaxRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Read-only integrity report over the tax ledger.
 *
 * Written because the office reported "phone number, email and customer number
 * are getting duplicate" and the answer was not obvious: citizens enforces all
 * three as unique, so nothing can duplicate there. The duplication is in the
 * property ledger, where the same customer_no is held by two different owners,
 * and that is what made registration fail.
 *
 * Changes nothing. Safe to run on production at any time.
 */
class AuditTaxLedger extends Command
{
    protected $signature = 'tax:audit {--csv= : Write the collision list to this path}';

    protected $description = 'Report duplicate customer numbers, unlinked records and unsettled payments';

    public function handle(): int
    {
        $this->components->info('Tax ledger integrity report');

        $collisions = $this->customerNoCollisions();
        $this->reportCollisions($collisions);
        $this->reportUnlinked();
        $this->reportPayments();

        if ($path = $this->option('csv')) {
            $this->writeCsv($path, $collisions);
        }

        return self::SUCCESS;
    }

    /**
     * Customer numbers held by more than one distinct owner name.
     *
     * Two rows sharing a customer number is not automatically wrong: one owner
     * can hold several properties. It is only a collision when the names differ,
     * which means the number identifies two different people.
     */
    private function customerNoCollisions(): array
    {
        $shared = PropertyTaxRecord::select('customer_no')
            ->whereNotNull('customer_no')
            ->groupBy('customer_no')
            ->havingRaw('COUNT(DISTINCT customer_name) > 1')
            ->pluck('customer_no');

        $out = [];

        foreach ($shared as $number) {
            $rows = PropertyTaxRecord::where('customer_no', $number)
                ->get(['id', 'customer_no', 'property_no', 'customer_name', 'balance', 'phone', 'citizen_id']);

            $out[] = ['customer_no' => $number, 'rows' => $rows];
        }

        return $out;
    }

    private function reportCollisions(array $collisions): void
    {
        $this->newLine();
        $this->line('<options=bold>Customer numbers held by more than one owner</>');

        if ($collisions === []) {
            $this->line('  none');

            return;
        }

        $this->line(sprintf('  %d colliding numbers, %d records affected',
            count($collisions),
            collect($collisions)->sum(fn ($c) => $c['rows']->count())
        ));
        $this->newLine();

        foreach (array_slice($collisions, 0, 10) as $c) {
            $this->line('  <fg=yellow>' . $c['customer_no'] . '</>');

            foreach ($c['rows'] as $r) {
                $this->line(sprintf('      id=%-6s prop=%-16s bal=%-10s %s',
                    $r->id,
                    mb_strimwidth((string) $r->property_no, 0, 16, '..'),
                    number_format((float) $r->balance, 2),
                    mb_strimwidth((string) $r->customer_name, 0, 30, '..')
                ));
            }
        }

        if (count($collisions) > 10) {
            $this->line(sprintf('  ... and %d more. Use --csv to get the full list.', count($collisions) - 10));
        }
    }

    private function reportUnlinked(): void
    {
        $this->newLine();
        $this->line('<options=bold>Records a citizen cannot see</>');

        foreach ([
            'property' => PropertyTaxRecord::class,
            'water' => WaterTaxRecord::class,
        ] as $label => $model) {
            $total = $model::count();
            $unlinked = $model::whereNull('citizen_id')->count();
            $noPhone = $model::whereNull('citizen_id')
                ->where(fn ($q) => $q->whereNull('phone')->orWhere('phone', ''))
                ->count();

            $this->line(sprintf('  %-9s %d of %d unlinked, of which %d have no phone at all',
                $label . ':', $unlinked, $total, $noPhone));
        }

        $this->line('  A record with no phone can never be linked by OTP login, so the');
        $this->line('  office has to add the number before that citizen can pay online.');
    }

    private function reportPayments(): void
    {
        $this->newLine();
        $this->line('<options=bold>Successful payments and the bills they settled</>');

        $success = TaxPayment::where('status', 'success');
        $total = $success->count();
        $noBill = TaxPayment::where('status', 'success')->whereNull('bill_id')->count();

        $this->line(sprintf('  %d successful payments, %d not attached to a bill', $total, $noBill));

        if ($noBill > 0) {
            $this->line('  Unattached payments reduce the record balance but leave the bill');
            $this->line('  untouched, which is what "not added in the bill" describes.');
        }

        // Counter receipts are legitimate: the office records cash at the desk,
        // so paid_amount without an online payment is expected, not an error.
        $this->line(sprintf('  water bills marked part/fully paid:    %d', MonthlyTaxBill::where('paid_amount', '>', 0)->count()));
        $this->line(sprintf('  property bills marked part/fully paid: %d', PropertyTaxAnnualBill::where('paid_amount', '>', 0)->count()));

        $drifted = MonthlyTaxBill::whereColumn('paid_amount', '>=', 'bill_amount')
            ->where('status', '!=', 'paid')
            ->count();
        $this->line(sprintf('  water bills fully paid but not marked paid: %d', $drifted));

        $this->newLine();
        $this->line(sprintf('  citizens: %d, of which %d have no customer number',
            Citizen::count(), Citizen::whereNull('customer_no')->count()));
    }

    private function writeCsv(string $path, array $collisions): void
    {
        $fh = fopen($path, 'w');
        fputcsv($fh, ['customer_no', 'record_id', 'property_no', 'customer_name', 'balance', 'phone', 'citizen_id']);

        foreach ($collisions as $c) {
            foreach ($c['rows'] as $r) {
                fputcsv($fh, [
                    $c['customer_no'], $r->id, $r->property_no, $r->customer_name,
                    $r->balance, $r->phone, $r->citizen_id,
                ]);
            }
        }

        fclose($fh);
        $this->newLine();
        $this->components->info('Collision list written to ' . $path);
    }
}
