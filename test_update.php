<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WaterTaxRecord;

$record = WaterTaxRecord::find(1);
echo "Before Update:\n";
echo "Balance: " . $record->balance . "\n";
echo "Paid: " . $record->amount_paid . "\n";

$taxAmount = 100; // Let's try 100
$record->update([
    'amount_paid' => ($record->amount_paid ?? 0) + $taxAmount,
    'balance'     => max(0, $record->balance - $taxAmount),
]);

$record->refresh();
echo "\nAfter Update:\n";
echo "Balance: " . $record->balance . "\n";
echo "Paid: " . $record->amount_paid . "\n";
