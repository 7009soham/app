<?php

namespace App\Console\Commands;

use App\Models\TaxPayment;
use App\Services\PayuService;
use App\Services\PhonePeService;
use App\Services\RazorpayService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Resolves payments that never reached a final state.
 *
 * A citizen whose connection drops between paying and returning leaves a
 * "pending" row: the money left their account but their balance still shows
 * the full amount. Nothing else in the system ever revisits those, so without
 * this they stay wrong until somebody complains.
 *
 * Runs against the gateway's own status API, which is authoritative - never
 * against anything the browser reported.
 */
class ReconcilePendingPayments extends Command
{
    protected $signature = 'payments:reconcile
        {--minutes=15 : Only consider payments older than this, so live checkouts are left alone}
        {--limit=200 : Maximum payments to examine in one run}
        {--dry-run : Report what would change without writing}';

    protected $description = 'Resolve pending payments against the gateway, so a dropped connection cannot lose a payment';

    public function handle(PhonePeService $phonePe, RazorpayService $razorpay): int
    {
        $olderThan = now()->subMinutes((int) $this->option('minutes'));
        $dryRun = (bool) $this->option('dry-run');

        $pending = TaxPayment::where('payment_status', 'pending')
            ->where('created_at', '<=', $olderThan)
            ->orderBy('id')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($pending->isEmpty()) {
            $this->info('Nothing pending to reconcile.');

            return self::SUCCESS;
        }

        $this->info("Examining {$pending->count()} pending payment(s) older than {$this->option('minutes')} minutes.");

        $resolved = 0;
        $stillPending = 0;
        $failed = 0;

        foreach ($pending as $payment) {
            try {
                $outcome = $this->resolve($payment, $phonePe, $razorpay);
            } catch (\Throwable $e) {
                // One bad row must not stop the run.
                Log::error('Reconcile failed for a payment', [
                    'transaction_id' => $payment->transaction_id,
                    'exception' => $e->getMessage(),
                ]);
                $this->warn("  {$payment->transaction_id}: error — {$e->getMessage()}");
                $failed++;
                continue;
            }

            $this->line(sprintf(
                '  %-22s %-9s %s',
                $payment->transaction_id,
                $payment->payment_method,
                $outcome
            ));

            if ($outcome === 'still pending') {
                $stillPending++;
            } elseif (str_starts_with($outcome, 'unresolved')) {
                $failed++;
            } elseif (!$dryRun) {
                $resolved++;
            }
        }

        $this->newLine();
        $this->info("Resolved: {$resolved}   Still pending: {$stillPending}   Unresolved: {$failed}");

        if ($dryRun) {
            $this->comment('Dry run — nothing was written.');
        }

        return self::SUCCESS;
    }

    private function resolve(TaxPayment $payment, PhonePeService $phonePe, RazorpayService $razorpay): string
    {
        $dryRun = (bool) $this->option('dry-run');

        [$status, $data] = match ($payment->payment_method) {
            'razorpay' => $this->razorpayStatus($payment, $razorpay),
            'payu' => $this->payuStatus($payment),
            default => $this->phonePeStatus($payment, $phonePe),
        };

        if ($status === null) {
            return 'unresolved (gateway did not answer)';
        }

        if ($status === 'PENDING') {
            return 'still pending';
        }

        if ($dryRun) {
            return 'would mark ' . strtolower($status);
        }

        // Same path a live callback takes, so it is transactional, locked and
        // idempotent - reconciling a payment a callback already handled is a
        // no-op rather than a second credit.
        app(\App\Http\Controllers\Citizen\PaymentController::class)
            ->reconcilePaymentStatus($payment, $status, $data);

        return 'marked ' . strtolower($status);
    }

    /** @return array{0: ?string, 1: array} */
    private function phonePeStatus(TaxPayment $payment, PhonePeService $phonePe): array
    {
        if (!$phonePe->isEnabled()) {
            return [null, []];
        }

        $result = $phonePe->checkPaymentStatus($payment->transaction_id);

        if (!($result['success'] ?? false)) {
            return [null, []];
        }

        if ($result['is_completed'] ?? false) {
            return ['SUCCESS', $result['data'] ?? []];
        }

        if ($result['is_pending'] ?? false) {
            return ['PENDING', []];
        }

        return ['FAILED', $result['data'] ?? []];
    }

    /** @return array{0: ?string, 1: array} */
    private function razorpayStatus(TaxPayment $payment, RazorpayService $razorpay): array
    {
        $providerId = $payment->provider_transaction_id;

        // Nothing was ever captured, so there is no payment to look up.
        if (!$providerId || !$razorpay->isEnabled()) {
            return [$providerId ? null : 'FAILED', []];
        }

        $result = $razorpay->fetchPayment($providerId);

        if (!($result['success'] ?? false)) {
            return [null, []];
        }

        return [($result['is_completed'] ?? false) ? 'SUCCESS' : 'FAILED', $result['data'] ?? []];
    }

    /**
     * PayU exposes verification through a POST form service rather than REST.
     *
     * @return array{0: ?string, 1: array}
     */
    private function payuStatus(TaxPayment $payment): array
    {
        try {
            $payu = PayuService::forTaxType($payment->tax_type);
        } catch (\InvalidArgumentException) {
            return [null, []];
        }

        if (!$payu->isEnabled()) {
            return [null, []];
        }

        $command = 'verify_payment';
        $hash = hash('sha512', $payu->getMerchantKey() . '|' . $command . '|' . $payment->transaction_id . '|' . $payu->getSalt());

        $response = Http::timeout(25)->connectTimeout(8)->asForm()->post($payu->getVerifyUrl(), [
            'key' => $payu->getMerchantKey(),
            'command' => $command,
            'var1' => $payment->transaction_id,
            'hash' => $hash,
        ]);

        if (!$response->successful()) {
            return [null, []];
        }

        $body = $response->json();
        $details = $body['transaction_details'][$payment->transaction_id] ?? null;

        if (!is_array($details)) {
            return [null, []];
        }

        $status = strtolower((string) ($details['status'] ?? ''));

        return match ($status) {
            'success', 'captured' => ['SUCCESS', ['payu_response' => $details]],
            'pending', 'in progress', 'initiated' => ['PENDING', []],
            '' => [null, []],
            default => ['FAILED', ['payu_response' => $details]],
        };
    }
}
