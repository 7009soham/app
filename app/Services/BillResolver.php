<?php

namespace App\Services;

use App\Models\MonthlyTaxBill;
use App\Models\PropertyTaxAnnualBill;
use Illuminate\Database\Eloquent\Model;

/**
 * Works out which bill a payment settles.
 *
 * This used to be inline in the settlement code, derived from the clock:
 * bill_year/bill_month = today for water, currentFinancialYear() for property.
 * That silently mismatched whenever a citizen paid anything other than the
 * current period - arrears, a late bill, a bill raised for last month - and the
 * `if ($bill)` guard around the update meant the money was taken and the bill
 * was left showing Pending.
 *
 * The rule here is oldest-unsettled-first, which is how arrears are meant to
 * clear: a citizen paying off a debt settles the oldest outstanding bill, not
 * whichever one happens to match today's date.
 */
class BillResolver
{
    public const WATER = 'water_monthly';
    public const PROPERTY = 'property_annual';

    /** Bill statuses that still owe money. */
    private const OPEN = ['pending', 'partial', 'overdue'];

    /**
     * Normalise the two spellings of tax type used across the codebase.
     * initiatePayment() validates 'water'|'property'; TaxPayment stores
     * 'water_tax'|'property_tax'.
     */
    public static function billTypeFor(string $taxType): string
    {
        return str_starts_with($taxType, 'water') ? self::WATER : self::PROPERTY;
    }

    /**
     * The oldest still-open bill for a record, or null when the office has not
     * generated one. Null is a legitimate state here: payment must still be
     * allowed to settle against the master record.
     */
    public static function outstandingFor(string $taxType, int|string|null $recordId): ?Model
    {
        if (empty($recordId)) {
            return null;
        }

        if (self::billTypeFor($taxType) === self::WATER) {
            return MonthlyTaxBill::where('record_id', $recordId)
                ->where('tax_type', 'water_tax')
                ->whereIn('status', self::OPEN)
                ->orderBy('bill_year')
                ->orderBy('bill_month')
                ->first();
        }

        return PropertyTaxAnnualBill::where('record_id', $recordId)
            ->whereIn('status', self::OPEN)
            ->orderBy('bill_period_start')
            ->first();
    }

    /**
     * Re-hydrate the bill a payment recorded at initiation. Kept separate from
     * outstandingFor() so settlement never silently drifts to a different bill
     * than the one the citizen was shown.
     */
    public static function find(?string $billType, int|string|null $billId): ?Model
    {
        if (empty($billType) || empty($billId)) {
            return null;
        }

        return match ($billType) {
            self::WATER => MonthlyTaxBill::find($billId),
            self::PROPERTY => PropertyTaxAnnualBill::find($billId),
            default => null,
        };
    }

    /**
     * Apply a payment to a bill.
     *
     * Both bill tables carry paid_amount and balance, and before this the four
     * combinations of (water|property) x (bill|master record) each updated a
     * different subset: the property annual bill never had its balance
     * decremented, so its outstanding stayed at the original figure forever.
     *
     * Clamped at zero so an overpayment cannot drive a balance negative, and
     * the status is derived from the resulting balance rather than recomputed
     * from a stale column.
     */
    public static function applyPayment(Model $bill, float $amount, string $method = 'online'): void
    {
        $paid = round(((float) $bill->paid_amount) + $amount, 2);
        $balance = round(max(0, ((float) $bill->balance) - $amount), 2);

        $bill->forceFill([
            'paid_amount' => $paid,
            'balance' => $balance,
            'status' => $balance <= 0.009 ? 'paid' : 'partial',
            'payment_method' => $method,
            'paid_date' => now(),
        ])->save();
    }
}
