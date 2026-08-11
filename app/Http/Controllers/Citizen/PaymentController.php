<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Mail\PaymentConfirmationMail;
use App\Models\Citizen;
use App\Models\SiteSetting;
use App\Models\TaxPayment;
use App\Models\WaterTaxRecord;
use App\Models\MonthlyTaxBill;
use App\Models\Payment;
use App\Models\PropertyTaxRecord;
use App\Models\PropertyTaxAnnualBill;
use App\Models\TaxType;
use App\Services\PhonePeService;
use App\Services\RazorpayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class PaymentController extends Controller
{
    protected $phonePeService;
    protected $razorpayService;
    protected static array $taxPaymentsColumnCache = [];

    public function __construct(PhonePeService $phonePeService, RazorpayService $razorpayService)
    {
        $this->phonePeService = $phonePeService;
        $this->razorpayService = $razorpayService;
    }

    /**
     * The gateway that will process this payment.
     *
     * Honours the citizen's selection only when that gateway is genuinely
     * available, so a tampered or stale form value cannot route a payment to a
     * gateway that is switched off or unimplemented. Otherwise falls back to
     * the configured default.
     */
    protected function activeGateway(?string $requested = null, ?string $taxType = null): string
    {
        $registry = app(\App\Services\PaymentGatewayRegistry::class);

        if ($registry->isSelectable($requested, $taxType)) {
            return $requested;
        }

        return $registry->default($taxType) ?? SiteSetting::get('active_payment_gateway', 'phonepe');
    }

    /**
     * Initiate payment for a tax record
     */
    public function initiatePayment(Request $request)
    {
        $validated = $request->validate([
            'tax_type' => 'required|in:water,property',
            'record_id' => 'required|integer',
            'amount' => 'required|numeric|min:1',
            'convenience_fee' => 'nullable|numeric|min:0',
            // Validated against what is actually available in activeGateway().
            'payment_method' => 'nullable|string|max:32',
        ]);

        $citizenId = Auth::guard('citizen')->id();
        
        if (!$citizenId) {
            return redirect()->route('citizen.login')
                ->with('error', 'Please login to continue with payment.');
        }
        
        $citizen = Citizen::findOrFail($citizenId);

        // Get the tax record
        if ($validated['tax_type'] === 'water') {
            $record = WaterTaxRecord::findOrFail($validated['record_id']);
            $targetSlug = 'water-tax';
        } else {
            $record = PropertyTaxRecord::findOrFail($validated['record_id']);
            $targetSlug = 'property-tax';
        }

        // Calculate convenience fee server-side to prevent tampering
        $convenienceFeePercent = floatval(SiteSetting::get('convenience_fee_percentage', '0'));
        $convenienceFee = floatval($validated['convenience_fee'] ?? 0);
        $totalAmount = floatval($validated['amount']);
        $taxAmount = round($totalAmount - $convenienceFee, 2);

        // Verify tax amount doesn't exceed balance (allow for small float diffs)
        if ($taxAmount > ($record->balance + 1)) {
            return back()->with('error', 'Payment amount cannot exceed the outstanding balance.');
        }

        // Server-side validation of convenience fee
        $expectedFee = round($taxAmount * $convenienceFeePercent / 100, 2);
        if (abs($convenienceFee - $expectedFee) > 1) {
            return back()->with('error', 'Invalid convenience fee calculation. Please try again.');
        }

        // Find Tax Type ID
        $taxTypeModel = TaxType::where('slug', $targetSlug)
            ->orWhere('slug', str_replace('-', '_', $targetSlug))
            ->first();

        if (!$taxTypeModel) {
             return back()->with('error', 'Tax Type configuration not found. Please contact admin.');
        }

        // Determine active gateway and check it is ready
        $gateway = $this->activeGateway($validated['payment_method'] ?? null, $validated['tax_type']);
        if ($gateway === 'razorpay') {
            if (!$this->razorpayService->isEnabled()) {
                return back()->with('error', 'Payment gateway is currently not available. Please try again later.');
            }
            $transactionId = $this->razorpayService->generateTransactionId();
        } else {
            if (!$this->phonePeService->isEnabled()) {
                return back()->with('error', 'Payment gateway is currently not available. Please try again later.');
            }
            $transactionId = $this->phonePeService->generateTransactionId();
        }

        // Determine period dates based on tax type
        if ($validated['tax_type'] === 'property') {
            // Annual billing: April 1 – March 31
            $now = Carbon::now();
            $fyStartYear = $now->month > 3 ? $now->year : $now->year - 1;
            $periodStart = Carbon::create($fyStartYear, 4, 1)->startOfDay();
            $periodEnd   = Carbon::create($fyStartYear + 1, 3, 31)->endOfDay();
            $periodType  = 'yearly';
        } else {
            // Monthly billing for water tax
            $periodStart = Carbon::now()->startOfMonth();
            $periodEnd   = Carbon::now()->endOfMonth();
            $periodType  = 'monthly';
        }

        // Create pending payment record
        $paymentCreateData = [
            'citizen_id'      => $citizenId,
            'citizen_name'    => $citizen->name,
            'citizen_phone'   => $citizen->phone,
            'citizen_address' => $citizen->address ?? '-',
            'tax_type'        => $validated['tax_type'] . '_tax',
            'tax_type_id'     => $taxTypeModel->id,
            'record_id'       => $validated['record_id'],
            'transaction_id'  => $transactionId,
            'amount'          => $totalAmount,
            'period_type'     => $periodType,
            'period_start'    => $periodStart,
            'period_end'      => $periodEnd,
            'payment_method'  => $gateway,
            'payment_status'  => 'pending',
            'payment_data'    => [
                'customer_name'        => $record->customer_name,
                'customer_no'          => $record->customer_no,
                'initiated_at'         => now()->toDateTimeString(),
                'tax_amount'           => $taxAmount,
                'convenience_fee'      => $convenienceFee,
                'convenience_fee_pct'  => $convenienceFeePercent,
                'total_amount'         => $totalAmount,
            ],
        ];

        if ($this->hasTaxPaymentsColumn('status')) {
            $paymentCreateData['status'] = 'pending';
        }

        if ($this->hasTaxPaymentsColumn('failure_reason')) {
            $paymentCreateData['failure_reason'] = null;
        }

        $payment = TaxPayment::create($paymentCreateData);

        // --- Razorpay flow ---
        if ($gateway === 'razorpay') {
            $result = $this->razorpayService->createOrder([
                'transaction_id' => $transactionId,
                'amount'         => $totalAmount,
                'user_id'        => 'CITIZEN' . $citizenId,
            ]);

            if ($result['success']) {
                Session::put('pending_payment_id', $payment->id);
                Session::put('razorpay_order_id', $result['order_id']);

                $payment->update([
                    'payment_data' => array_merge($payment->payment_data ?? [], [
                        'razorpay_order_id' => $result['order_id'],
                    ]),
                ]);

                return redirect()->route('citizen.payment.razorpay-checkout');
            }

            $this->markPaymentFailed($payment, $result['message'] ?? 'Payment initiation failed');
            return back()->with('error', $this->mapFailureReason('UNKNOWN'));
        }

        // --- PhonePe flow (default) ---
        $result = $this->phonePeService->initiatePayment([
            'transaction_id' => $transactionId,
            'amount'         => $totalAmount,
            'callback_url'   => route('citizen.payment.callback'),
            'redirect_url'   => route('citizen.payment.redirect'),
            'mobile'         => $citizen->phone,
            'user_id'        => 'CITIZEN' . $citizenId,
        ]);

        if ($result['success'] && isset($result['redirect_url'])) {
            Session::put('pending_payment_id', $payment->id);
            return redirect()->away($result['redirect_url']);
        }

        $this->markPaymentFailed($payment, $result['message'] ?? 'Payment initiation failed');
        return back()->with('error', $this->mapFailureReason('UNKNOWN'));
    }

    /**
     * Handle PhonePe callback (server-to-server)
     */
    public function callback(Request $request)
    {
        Log::info('PhonePe Callback Received', $request->all());

        $response = $request->input('response');
        $checksum = $request->header('X-VERIFY');

        // Verify checksum
        if (!$this->phonePeService->verifyCallback([
            'response' => $response,
            'checksum' => $checksum,
        ])) {
            Log::warning('PhonePe Callback - Checksum verification failed');
            return response()->json(['status' => 'CHECKSUM_FAILED'], 400);
        }

        // Decode response
        $decodedResponse = $this->phonePeService->decodeCallback($response);
        
        if (empty($decodedResponse)) {
            Log::error('PhonePe Callback - Failed to decode response');
            return response()->json(['status' => 'DECODE_FAILED'], 400);
        }

        $transactionId = $decodedResponse['data']['merchantTransactionId'] ?? null;
        $paymentState = $decodedResponse['data']['state'] ?? 'UNKNOWN';

        if (!$transactionId) {
            return response()->json(['status' => 'INVALID_TRANSACTION'], 400);
        }

        // Find and update payment
        $payment = TaxPayment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            Log::error('PhonePe Callback - Payment not found', ['transaction_id' => $transactionId]);
            return response()->json(['status' => 'PAYMENT_NOT_FOUND'], 404);
        }

        // Confirm callback result with PhonePe status API before marking success.
        $statusResult = $this->phonePeService->checkPaymentStatus($transactionId);

        if ($statusResult['success'] ?? false) {
            $this->updatePaymentStatus(
                $payment,
                $statusResult['payment_status'] ?? $paymentState,
                $statusResult['data'] ?? $decodedResponse,
                true
            );
        } else {
            Log::warning('PhonePe Callback - Status API verification failed, deferring success confirmation', [
                'transaction_id' => $transactionId,
                'callback_state' => $paymentState,
                'status_result' => $statusResult,
            ]);

            $this->updatePaymentStatus($payment, $paymentState, $decodedResponse, false);
        }

        return response()->json(['status' => 'OK']);
    }

    /**
     * Handle redirect from PhonePe
     */
    public function redirect(Request $request)
    {
        $paymentId = Session::get('pending_payment_id');
        Session::forget('pending_payment_id');

        if (!$paymentId) {
            return redirect()->route('citizen.payment-history')
                ->with('error', 'Payment session expired.');
        }

        $payment = TaxPayment::find($paymentId);

        if (!$payment) {
            return redirect()->route('citizen.payment-history')
                ->with('error', 'Payment not found.');
        }

        // Check payment status with PhonePe
        $statusResult = $this->phonePeService->checkPaymentStatus($payment->transaction_id);

        if ($statusResult['success']) {
            $this->updatePaymentStatus($payment, $statusResult['payment_status'], $statusResult['data'] ?? [], true);
            $payment->refresh();

            if ($statusResult['is_completed']) {
                return redirect()->route('citizen.payment-history')
                    ->with('success', 'Payment successful! Transaction ID: ' . $payment->transaction_id);
            } elseif ($statusResult['is_pending']) {
                return redirect()->route('citizen.payment-history')
                    ->with('warning', 'Payment is being processed. Please check back in a few minutes.');
            }

            $latestStatus = $payment->status ?? $payment->payment_status;
            if ($latestStatus === 'failed') {
                return redirect()->route('citizen.payment-history')
                    ->with('error', $payment->failure_reason ?? 'Payment failed. Please try again.');
            }
        }

        $payment->refresh();
        $latestStatus = $payment->status ?? $payment->payment_status;
        if ($latestStatus === 'failed') {
            return redirect()->route('citizen.payment-history')
                ->with('error', $payment->failure_reason ?? 'Payment failed or was cancelled. Please try again.');
        }

        return redirect()->route('citizen.payment-history')
            ->with('error', 'Payment failed or was cancelled. Please try again.');
    }

    /**
     * Show the Razorpay checkout page
     */
    public function razorpayCheckout()
    {
        $paymentId = Session::get('pending_payment_id');
        $orderId   = Session::get('razorpay_order_id');

        if (!$paymentId || !$orderId) {
            return redirect()->route('citizen.payment-history')
                ->with('error', 'Payment session expired.');
        }

        $payment = TaxPayment::find($paymentId);
        if (!$payment) {
            return redirect()->route('citizen.payment-history')
                ->with('error', 'Payment not found.');
        }

        $citizen = Citizen::find($payment->citizen_id);

        return view('citizen.payment.razorpay-checkout', [
            'keyId'       => $this->razorpayService->getKeyId(),
            'orderId'     => $orderId,
            'amount'      => (int) round($payment->amount * 100),
            'description' => ucfirst(str_replace('_', ' ', $payment->tax_type)) . ' Payment',
            'citizenName' => $citizen?->name ?? '',
            'citizenEmail' => $citizen?->email ?? '',
            'citizenPhone' => $citizen?->phone ?? '',
        ]);
    }

    /**
     * Handle the POST return from Razorpay after payment
     */
    public function razorpayReturn(Request $request)
    {
        $paymentId = Session::get('pending_payment_id');
        Session::forget(['pending_payment_id', 'razorpay_order_id']);

        $rzpPaymentId  = $request->input('razorpay_payment_id', '');
        $rzpOrderId    = $request->input('razorpay_order_id', '');
        $rzpSignature  = $request->input('razorpay_signature', '');

        if (!$paymentId) {
            return redirect()->route('citizen.payment-history')
                ->with('error', 'Payment session expired.');
        }

        $payment = TaxPayment::find($paymentId);
        if (!$payment) {
            return redirect()->route('citizen.payment-history')
                ->with('error', 'Payment not found.');
        }

        // If signature is missing, payment failed or was dismissed
        if (empty($rzpSignature) || empty($rzpPaymentId)) {
            $this->updatePaymentStatus($payment, 'FAILED', [], true);
            return redirect()->route('citizen.payment-history')
                ->with('error', 'Payment failed or was cancelled. Please try again.');
        }

        // Verify HMAC signature
        $isValid = $this->razorpayService->verifyPaymentSignature($rzpOrderId, $rzpPaymentId, $rzpSignature);

        if (!$isValid) {
            Log::warning('Razorpay signature verification failed', [
                'payment_id' => $payment->id,
                'rzp_order_id' => $rzpOrderId,
                'rzp_payment_id' => $rzpPaymentId,
            ]);

            $this->updatePaymentStatus($payment, 'FAILED', [], true);
            return redirect()->route('citizen.payment-history')
                ->with('error', 'Payment verification failed. Please contact support.');
        }

        // Fetch payment from Razorpay to confirm captured status
        $fetchResult = $this->razorpayService->fetchPayment($rzpPaymentId);

        $payment->update([
            'payment_data' => array_merge($payment->payment_data ?? [], [
                'razorpay_payment_id' => $rzpPaymentId,
                'razorpay_order_id'   => $rzpOrderId,
                'razorpay_signature'  => $rzpSignature,
            ]),
        ]);

        $isCompleted = $fetchResult['success'] && ($fetchResult['is_completed'] ?? false);
        $providerStatus = $isCompleted ? 'COMPLETED' : (($fetchResult['status'] ?? 'UNKNOWN'));

        $this->updatePaymentStatus($payment, $providerStatus, $fetchResult['data'] ?? [], true);
        $payment->refresh();

        $latestStatus = $payment->status ?? $payment->payment_status;

        if ($latestStatus === 'success' || $latestStatus === 'completed') {
            return redirect()->route('citizen.payment-history')
                ->with('success', 'Payment successful! Transaction ID: ' . $payment->transaction_id);
        }

        return redirect()->route('citizen.payment-history')
            ->with('error', $payment->failure_reason ?? 'Payment failed. Please try again.');
    }

    protected function markPaymentFailed(TaxPayment $payment, string $message): void
    {
        $failureReason = $this->mapFailureReason('UNKNOWN');
        $updateData = [
            'payment_status' => 'failed',
            'payment_data'   => array_merge($payment->payment_data ?? [], [
                'error'          => $message,
                'failure_code'   => 'UNKNOWN',
                'failure_reason' => $failureReason,
            ]),
        ];

        if ($this->hasTaxPaymentsColumn('status')) {
            $updateData['status'] = 'failed';
        }
        if ($this->hasTaxPaymentsColumn('failure_reason')) {
            $updateData['failure_reason'] = $failureReason;
        }

        $payment->update($updateData);
    }

    /**
     * Update payment status and related records
     */
    protected function updatePaymentStatus(
        TaxPayment $payment,
        string $status,
        array $responseData = [],
        bool $isApiConfirmed = false
    ): void
    {
        $newStatus = $this->normalizeProviderStatus($status);

        // Never downgrade a finalized successful payment because of delayed callbacks.
        if ($payment->status === 'success' && $payment->payment_status === 'completed' && $newStatus !== 'success') {
            Log::info('Skipping non-success update for completed payment', [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'incoming_status' => $status,
            ]);
            return;
        }

        // Success must be API-confirmed; otherwise keep payment pending.
        if ($newStatus === 'success' && !$isApiConfirmed && !$this->isConfirmedProviderSuccessPayload($responseData)) {
            Log::warning('Unconfirmed success ignored; keeping payment pending until API confirmation', [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'incoming_status' => $status,
            ]);
            $newStatus = 'pending';
        }

        $providerTransactionId = $responseData['data']['transactionId'] ?? $payment->provider_transaction_id;
        $failureCode = null;
        $failureReason = null;

        if ($newStatus === 'failed') {
            $failureCode = $this->resolveFailureCode($status, $responseData);
            $failureReason = $this->mapFailureReason($failureCode);
        }

        $resolvedPaymentStatus = $newStatus === 'success' ? 'completed' : $newStatus;

        $updateData = [
            'payment_status' => $resolvedPaymentStatus,
            'provider_transaction_id' => $providerTransactionId,
            'phonepe_transaction_id' => $providerTransactionId,
            'payment_data' => array_merge($payment->payment_data ?? [], [
                'status_response' => $responseData,
                'status_updated_at' => now()->toDateTimeString(),
                'failure_code' => $failureCode,
                'failure_reason' => $failureReason,
            ]),
        ];

        if ($this->hasTaxPaymentsColumn('status')) {
            $updateData['status'] = $newStatus;
        }

        if ($this->hasTaxPaymentsColumn('failure_reason')) {
            $updateData['failure_reason'] = $newStatus === 'failed' ? $failureReason : null;
        }

        // Set paid_at only for confirmed successful payments.
        if ($newStatus === 'success') {
            $updateData['paid_at'] = $payment->paid_at ?? now();
        } else {
            $updateData['paid_at'] = null;
        }

        // Marking the payment and crediting the ledger must be one unit of
        // work. Previously they were separate writes: a failure between them
        // left the payment recorded as completed while the citizen's balance
        // was untouched, so they had paid and still owed the money.
        //
        // The row lock serialises concurrent callbacks. Gateways retry, and the
        // citizen's own "check status" poll can arrive at the same moment; two
        // requests reading the same balance and both writing would otherwise
        // lose one of the updates or credit the payment twice.
        $confirmation = null;

        DB::transaction(function () use ($payment, $updateData, $newStatus, &$confirmation) {
            $locked = TaxPayment::whereKey($payment->getKey())->lockForUpdate()->first();

            if (!$locked) {
                return;
            }

            $locked->update($updateData);

            if ($newStatus === 'success') {
                $confirmation = $this->updateTaxRecord($locked);
            }

            // Keep the caller's instance in step with what was committed.
            $payment->setRawAttributes($locked->getAttributes(), true);
        });

        Log::info('Payment Status Updated', [
            'payment_id' => $payment->id,
            'transaction_id' => $payment->transaction_id,
            'status' => $newStatus,
            'payment_status' => $updateData['payment_status'],
        ]);

        // Sent only after the commit. Inside the transaction a later rollback
        // would still leave the citizen holding a receipt for a payment the
        // database no longer records, and mail latency would hold row locks.
        if ($confirmation !== null) {
            $this->sendPaymentConfirmationEmail(...$confirmation);
        }
    }

    protected function normalizeProviderStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'COMPLETED', 'SUCCESS', 'PAYMENT_SUCCESS', 'CAPTURED' => 'success',
            'FAILED', 'DECLINED', 'CANCELLED', 'PAYMENT_ERROR', 'REJECTED' => 'failed',
            default => 'pending',
        };
    }

    protected function resolveFailureCode(string $status, array $responseData = []): string
    {
        $statusText = strtoupper($status);
        $responseCode = strtoupper((string) ($responseData['code'] ?? ''));
        $responseMessage = strtoupper((string) ($responseData['message'] ?? ''));
        $state = strtoupper((string) ($responseData['data']['state'] ?? ''));
        $haystack = $statusText . ' ' . $responseCode . ' ' . $responseMessage . ' ' . $state;

        if (str_contains($haystack, 'CANCEL')) {
            return 'USER_CANCELLED';
        }

        if (str_contains($haystack, 'TIMEOUT') || str_contains($haystack, 'TIMED OUT')) {
            return 'TIMEOUT';
        }

        if (
            str_contains($haystack, 'NETWORK')
            || str_contains($haystack, 'CONNECTION')
            || str_contains($haystack, 'UNAVAILABLE')
        ) {
            return 'NETWORK';
        }

        if (
            str_contains($haystack, 'CHECKSUM')
            || str_contains($haystack, 'INVALID')
            || str_contains($haystack, 'DECODE')
            || str_contains($haystack, 'MISMATCH')
        ) {
            return 'VERIFY_FAIL';
        }

        return 'UNKNOWN';
    }

    protected function mapFailureReason(string $failureCode): string
    {
        return match ($failureCode) {
            'USER_CANCELLED' => 'Payment cancelled by user',
            'TIMEOUT' => 'Payment gateway timeout',
            'NETWORK' => 'Network issue, please retry',
            'VERIFY_FAIL' => 'Payment verification failed',
            default => 'Technical issue, please try again',
        };
    }

    protected function isConfirmedProviderSuccessPayload(array $responseData): bool
    {
        if (($responseData['success'] ?? false) !== true) {
            return false;
        }

        $state = strtoupper((string) ($responseData['data']['state'] ?? ''));
        $code = strtoupper((string) ($responseData['code'] ?? ''));

        return $state === 'COMPLETED' || $code === 'PAYMENT_SUCCESS';
    }

    /**
     * Update tax record after successful payment
     */
    /**
     * Credit a confirmed payment to the citizen's ledger.
     *
     * MUST be called inside a transaction with the payment row already locked -
     * updatePaymentStatus() is the only caller and does both. The guard below
     * is only sound while that lock is held: without it two concurrent
     * callbacks both read an unset ledger_updated_at and both credit the
     * payment.
     *
     * Returns the arguments for the confirmation email, or null when the
     * payment was already credited. The email is deliberately not sent here so
     * it cannot go out for work that is later rolled back.
     */
    protected function updateTaxRecord(TaxPayment $payment): ?array
    {
        $existingPaymentData = $payment->payment_data ?? [];
        if (!empty($existingPaymentData['ledger_updated_at'])) {
            Log::info('Payment already processed for ledger update; skipping duplicate post-processing.', [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
            ]);
            return null;
        }

        // Extract the actual tax amount (excluding convenience fee)
        $paymentData = $existingPaymentData;
        $convenienceFee = floatval($paymentData['convenience_fee'] ?? 0);
        $taxAmount = floatval($paymentData['tax_amount'] ?? ($payment->amount - $convenienceFee));
        $record = null;
        $monthlyBill = null;
        $annualBill = null;

        if ($payment->tax_type === 'water_tax') {
            // Locked: amount_paid below is a read-modify-write.
            $record = WaterTaxRecord::whereKey($payment->record_id)->lockForUpdate()->first();

            if ($record) {
                // Update amount_paid. We keep the original balance for transparency in the invoice history
                $record->amount_paid = ($record->amount_paid ?? 0) + $taxAmount;
                $record->save();

                // Update monthly water tax bill
                $monthlyBill = MonthlyTaxBill::where('record_id', $record->id)
                    ->where('tax_type', 'water_tax')
                    ->where('bill_year', date('Y'))
                    ->where('bill_month', date('n'))
                    ->first();

                if ($monthlyBill) {
                    $monthlyBill->update([
                        'paid_amount'    => $monthlyBill->paid_amount + $taxAmount,
                        'balance'        => max(0, $monthlyBill->balance - $taxAmount),
                        'status'         => ($monthlyBill->balance - $taxAmount) <= 0 ? 'paid' : 'partial',
                        'payment_method' => 'online',
                        'paid_date'      => now(),
                    ]);
                }
            }
        } else {
            // Property Tax – annual billing. Locked: balance below is a
            // read-modify-write.
            $record = PropertyTaxRecord::whereKey($payment->record_id)->lockForUpdate()->first();

            if ($record) {
                // Only apply the tax portion to the balance
                $record->update([
                    'balance' => max(0, $record->balance - $taxAmount),
                ]);

                // Update the annual bill for current FY
                $fy = PropertyTaxAnnualBill::currentFinancialYear();
                $annualBill = PropertyTaxAnnualBill::where('record_id', $record->id)
                    ->where('financial_year', $fy)
                    ->first();

                if ($annualBill) {
                    $annualBill->update([
                        'paid_amount'    => $annualBill->paid_amount + $taxAmount,
                        'status'         => ($annualBill->balance - ($annualBill->paid_amount + $taxAmount)) <= 0 ? 'paid' : 'partial',
                        'payment_method' => 'online',
                        'paid_date'      => now(),
                        'transaction_id' => $payment->transaction_id,
                    ]);
                }
            }
        }

        // Create a single unified payment record (full amount including convenience fee).
        Payment::firstOrCreate(
            ['transaction_id' => $payment->transaction_id],
            [
                'citizen_id'     => $payment->citizen_id,
                'tax_type'       => $payment->tax_type,
                'bill_id'        => $monthlyBill?->id,
                'amount'         => $payment->amount,
                'payment_method' => 'online',
                'status'         => 'completed',
                'paid_at'        => now(),
                'remarks'        => $convenienceFee > 0
                    ? 'Online payment via PhonePe (Tax: ₹' . number_format($taxAmount, 2) . ', Convenience Fee: ₹' . number_format($convenienceFee, 2) . ')'
                    : 'Online payment via PhonePe',
            ]
        );

        $payment->update([
            'payment_data' => array_merge($existingPaymentData, [
                'ledger_updated_at' => now()->toDateTimeString(),
            ]),
        ]);

        Log::info('Tax Record Updated After Online Payment', [
            'record_id'       => $payment->record_id,
            'payment_id'      => $payment->id,
            'tax_type'        => $payment->tax_type,
            'total_amount'    => $payment->amount,
            'tax_amount'      => $taxAmount,
            'convenience_fee' => $convenienceFee,
        ]);

        // Handed back to the caller and sent after commit.
        return [
            $payment,
            $payment->citizen,
            $record,
            $monthlyBill,
            $annualBill,
            $taxAmount,
            $convenienceFee,
        ];
    }

    /**
     * Send payment confirmation email with invoice details.
     */
    protected function sendPaymentConfirmationEmail(
        TaxPayment $payment,
        ?Citizen $citizen,
        $record,
        ?MonthlyTaxBill $monthlyBill,
        ?PropertyTaxAnnualBill $annualBill,
        float $taxAmount,
        float $convenienceFee
    ): void {
        if (!$citizen || empty($citizen->email)) {
            Log::warning('Payment confirmation email skipped: citizen email not available.', [
                'payment_id' => $payment->id,
                'citizen_id' => $payment->citizen_id,
            ]);
            return;
        }

        try {
            $invoiceLink = null;
            $invoiceNumber = null;
            $billingPeriod = null;
            $dueDate = null;

            if ($payment->tax_type === 'water_tax' && $record) {
                $invoiceLink = route('citizen.billing.invoice', $record->id);
                $invoiceNumber = $record->bill_no ?: $payment->transaction_id;
                $billingPeriod = $record->period ?: ($monthlyBill ? $monthlyBill->month_name . ' ' . $monthlyBill->bill_year : null);
                $dueDate = $monthlyBill?->due_date;
            }

            if ($payment->tax_type === 'property_tax' && $annualBill) {
                $invoiceLink = route('citizen.billing.property-invoice', $annualBill->id);
                $invoiceNumber = $annualBill->bill_no ?: $payment->transaction_id;
                $billingPeriod = $annualBill->financial_year;
                $dueDate = $annualBill->due_date;
            }

            Mail::to($citizen->email)->send(new PaymentConfirmationMail([
                'citizen_name' => $citizen->name,
                'tax_type' => $payment->tax_type,
                'transaction_id' => $payment->transaction_id,
                'payment_date' => $payment->paid_at ?? now(),
                'total_amount' => (float) $payment->amount,
                'tax_amount' => $taxAmount,
                'convenience_fee' => $convenienceFee,
                'invoice_number' => $invoiceNumber,
                'invoice_link' => $invoiceLink,
                'billing_period' => $billingPeriod,
                'due_date' => $dueDate,
            ]));

            Log::info('Payment confirmation email sent.', [
                'payment_id' => $payment->id,
                'email' => $citizen->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send payment confirmation email.', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check payment status (AJAX)
     */
    public function checkStatus(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        
        if (!$transactionId) {
            return response()->json(['success' => false, 'message' => 'Transaction ID required']);
        }

        $payment = TaxPayment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found']);
        }

        $currentStatus = $payment->status ?? $payment->payment_status;

        if ($currentStatus === 'pending') {
            if ($payment->payment_method === 'razorpay') {
                $rzpPaymentId = $payment->payment_data['razorpay_payment_id'] ?? null;
                if ($rzpPaymentId) {
                    $statusResult = $this->razorpayService->fetchPayment($rzpPaymentId);
                    if ($statusResult['success']) {
                        $providerStatus = $statusResult['is_completed'] ? 'COMPLETED' : strtoupper($statusResult['status'] ?? 'PENDING');
                        $this->updatePaymentStatus($payment, $providerStatus, $statusResult['data'] ?? [], true);
                        $payment->refresh();
                    }
                }
            } else {
                $statusResult = $this->phonePeService->checkPaymentStatus($transactionId);
                if ($statusResult['success']) {
                    $this->updatePaymentStatus($payment, $statusResult['payment_status'], $statusResult['data'] ?? [], true);
                    $payment->refresh();
                }
            }
        }

        return response()->json([
            'success' => true,
            'status' => $payment->status ?? $payment->payment_status,
            'failure_reason' => $payment->failure_reason,
            'transaction_id' => $payment->transaction_id,
            'amount' => $payment->amount,
        ]);
    }

    private function hasTaxPaymentsColumn(string $column): bool
    {
        if (!array_key_exists($column, self::$taxPaymentsColumnCache)) {
            self::$taxPaymentsColumnCache[$column] = Schema::hasColumn('tax_payments', $column);
        }

        return self::$taxPaymentsColumnCache[$column];
    }
}
