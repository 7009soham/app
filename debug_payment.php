<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TaxPayment;
use App\Models\WaterTaxRecord;
use App\Models\Payment;

$transactionId = 'TXNEKYSXPQO1774848573';
$tp = TaxPayment::where('transaction_id', $transactionId)->first();

$output = "TaxPayment Details:\n";
$output .= "ID: " . $tp->id . "\n";
$output .= "Citizen ID: " . $tp->citizen_id . "\n";
$output .= "Tax Type: " . $tp->tax_type . "\n";
$output .= "Record ID: " . $tp->record_id . "\n";
$output .= "Amount: " . $tp->amount . "\n";
$output .= "Status: " . $tp->status . "\n";
$output .= "Payment Status: " . $tp->payment_status . "\n";
$output .= "Paid At: " . $tp->paid_at . "\n";

$record = null;
if ($tp->tax_type === 'water_tax') {
    $record = WaterTaxRecord::find($tp->record_id);
} elseif ($tp->tax_type === 'property_tax') {
    $record = \App\Models\PropertyTaxRecord::find($tp->record_id);
}

if ($record) {
    $output .= "\nRelated Tax Record:\n";
    $output .= "ID: " . $record->id . "\n";
    $output .= "Customer No: " . $record->customer_no . "\n";
    $output .= "Balance: " . $record->balance . "\n";
    $output .= "Amount Paid (Total): " . ($record->amount_paid ?? 'N/A') . "\n";
} else {
    $output .= "\nRelated record NOT FOUND for record_id: " . $tp->record_id . "\n";
}

$p = Payment::where('transaction_id', $transactionId)->first();
if ($p) {
    $output .= "\nUnified Payment Record:\n";
    $output .= "Amount: " . $p->amount . "\n";
    $output .= "Remarks: " . $p->remarks . "\n";
}

file_put_contents('debug_output.txt', $output);
echo "Output saved to debug_output.txt\n";
