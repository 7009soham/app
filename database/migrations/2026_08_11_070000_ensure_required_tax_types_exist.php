<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Guarantees the two tax types the payment flow looks up by slug.
 *
 * PaymentController resolves a TaxType by slug before it will create a
 * payment - water-tax for water records, property-tax for property ones - and
 * aborts with "Tax Type configuration not found" when it cannot. There is no
 * admin screen for tax types, so an operator had no way to create them, and
 * production had none at all: every online payment failed at that check.
 *
 * The seeder also created the property row as "house-tax", which the lookup
 * never matches, so seeding alone would have left property payments broken.
 * Existing rows are renamed rather than replaced, keeping the id that
 * tax_payments.tax_type_id points at.
 */
return new class extends Migration
{
    private const REQUIRED = [
        'property-tax' => [
            'legacy_slug' => 'house-tax',
            'name' => 'Property Tax',
            'description' => 'House tax for residential and commercial properties',
            'icon' => 'fa-home',
        ],
        'water-tax' => [
            'legacy_slug' => null,
            'name' => 'Water Tax',
            'description' => 'Water supply and maintenance charges',
            'icon' => 'fa-tint',
        ],
    ];

    public function up(): void
    {
        foreach (self::REQUIRED as $slug => $spec) {
            if (DB::table('tax_types')->where('slug', $slug)->exists()) {
                continue;
            }

            // Rename in place so any payment already pointing at the old row
            // keeps its foreign key.
            if ($spec['legacy_slug']
                && DB::table('tax_types')->where('slug', $spec['legacy_slug'])->exists()) {
                DB::table('tax_types')
                    ->where('slug', $spec['legacy_slug'])
                    ->update([
                        'slug' => $slug,
                        'name' => $spec['name'],
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);

                continue;
            }

            DB::table('tax_types')->insert([
                'name' => $spec['name'],
                'slug' => $slug,
                'description' => $spec['description'],
                // Amounts always come from the citizen's record balance; these
                // rate columns are not read anywhere in the payment flow.
                'monthly_rate' => 0,
                'quarterly_rate' => 0,
                'yearly_rate' => 0,
                'icon' => $spec['icon'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Deliberately not removed: tax_payments.tax_type_id references these
        // rows, and dropping them would orphan real payment history.
    }
};
