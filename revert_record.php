<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = \App\Models\WaterTaxRecord::find(1);
if ($r) {
    $r->balance = 500;
    $r->save();
    echo "REVERTED: Balanced record 1 to 500\n";
} else {
    echo "ERROR: Record 1 not found\n";
}
