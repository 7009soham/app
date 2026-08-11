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
use App\Services\PayuService;
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

        // Determine active gateway and check it is ready.
        //
        // The else-branch used to assume PhonePe, so picking PayU while
        // PhonePe was switched off failed with "gateway not available" even
        // though PayU was perfectly configured.
        $gateway = $this->activeGateway($validated['payment_method'] ?? null, $validated['tax_type']);

        $transactionId = match ($gateway) {
            'razorpay' => $this->razorpayService->isEnabled()
                ? $this->razorpayService->generateTransactionId()
                : null,
            'payu' => $this->payuTransactionId($validated['tax_type']),
            default => $this->phonePeService->isEnabled()
                ? $this->phonePeService->generateTransactionId()
                : null,
        };

        if ($transactionId === null) {
            Log::warning('Payment initiation blocked: gateway not ready', [
                'gateway' => $gateway,
                'tax_type' => $validated['tax_type'],
            ]);

            return back()->with('error', 'Payment gateway is currently not available. Please try again later.');
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

        // A citizen who taps Pay twice, or comes back after abandoning a
        // checkout, would otherwise leave a trail of pending rows for the same
        // bill - which makes reconciliation ambiguous and the history
        // confusing. Reuse a recent, still-pending attempt for the same record
        // and amount instead of stacking another one up.
        $reusable = TaxPayment::where('citizen_id', $citizenId)
            ->where('record_id', $validated['record_id'])
            ->where('tax_type', $validated['tax_type'] . '_tax')
            ->where('payment_status', 'pending')
            ->where('amount', $totalAmount)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->latest('id')
            ->first();

        if ($reusable) {
            Log::info('Reusing an existing pending payment instead of creating a duplicate', [
                'transaction_id' => $reusable->transaction_id,
                'record_id' => $reusable->record_id,
            ]);

            $payment = $reusable;
            $transactionId = $reusable->transaction_id;
        } else {
            $payment = TaxPayment::create($paymentCreateData);
        }

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

        // --- PayU flow ---
        // No create-order API: the citizen's browser posts a signed form
        // straight to PayU, so all we do here is stash the payment and hand
        // over to the checkout page that renders it.
        if ($gateway === 'payu') {
            Session::put('pending_payment_id', $payment->id);

            return redirect()->route('citizen.payment.payu-checkout');
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
    /**
     * Renders the signed PayU form and auto-submits it.
     *
     * The transaction id and amount come from the stored payment, never from
     * the request, so a citizen cannot re-open this page with a cheaper amount.
     */
    public function payuCheckout()
    {
        $payment = TaxPayment::find(Session::get('pending_payment_id'));

        // Cast both sides: the id arrives as an int from the model but as a
        // string from some drivers, and a strict comparison silently fails.
        // PaymentHistoryController guards ownership the same way.
        if (!$payment || (string) $payment->citizen_id !== (string) Auth::guard('citizen')->id()) {
            return redirect()->route('citizen.payment-history')
                ->with('error', 'Payment session expired. Please start again.');
        }

        if ($payment->payment_status === 'completed') {
            return redirect()->route('citizen.payment-history')
                ->with('error', 'This payment has already been completed.');
        }

        try {
            $payu = PayuService::forTaxType($payment->tax_type);
        } catch (\InvalidArgumentException $e) {
            Log::error('PayU checkout: unknown tax type', ['payment_id' => $payment->id]);

            return redirect()->route('citizen.payment-history')
                ->with('error', 'Payment could not be started. Please contact the Gram Panchayat office.');
        }

        if (!$payu->isEnabled()) {
            $this->markPaymentFailed($payment, 'PayU not configured for ' . $payment->tax_type);

            return redirect()->route('citizen.payment-history')
                ->with('error', 'Payment gateway is currently not available. Please try again later.');
        }

        $citizen = Citizen::find($payment->citizen_id);

        $fields = $payu->buildCheckoutFields([
            'txnid' => $payment->transaction_id,
            'amount' => $payment->amount,
            'productinfo' => $payu->getLabel(),
            // PayU rejects some punctuation in firstname; keep it simple.
            'firstname' => preg_replace('/[^a-zA-Z0-9 ]/', '', $citizen?->name ?: 'Citizen') ?: 'Citizen',
            'email' => $citizen?->email ?: 'noreply@' . request()->getHost(),
            'phone' => $citizen?->phone ?? '',
            'record_id' => $payment->record_id,
            'return_url' => route('citizen.payment.payu-return'),
        ]);

        return view('citizen.payment.payu-checkout', [
            'action' => $payu->getPaymentUrl(),
            'fields' => $fields,
            'amount' => $fields['amount'],
            'label' => $payu->getLabel(),
        ]);
    }

    /**
     * PayU posts the outcome back here, to both the success and failure URLs.
     *
     * Which tax head - and therefore which merchant account - the payment
     * belongs to is decided by whichever configured salt validates the reverse
     * hash. The body is attacker-controlled, so nothing in it is trusted until
     * that signature checks out.
     */
    public function payuReturn(Request $request)
    {
        $response = $request->all();

        Log::info('PayU return received', [
            'txnid' => $response['txnid'] ?? null,
            'status' => $response['status'] ?? null,
        ]);

        $payu = PayuService::resolveFromResponse($response);

        if (!$payu) {
            Log::warning('PayU return rejected: reverse hash did not validate', [
                'txnid' => $response['txnid'] ?? null,
            ]);

            return $this->unverifiedReturnPage(
                'We could not verify this payment result.',
                $response['txnid'] ?? null
            );
        }

        $payment = TaxPayment::where('transaction_id', $response['txnid'] ?? '')->first();

        if (!$payment) {
            Log::error('PayU return: no matching payment', ['txnid' => $response['txnid'] ?? null]);

            return $this->unverifiedReturnPage(
                'We could not find a matching payment record.',
                $response['txnid'] ?? null
            );
        }

        // The signature covers the amount, so a mismatch means the payment we
        // hold is not the one PayU processed.
        if ($payu->formatAmount((float) $payment->amount) !== (string) ($response['amount'] ?? '')) {
            Log::error('PayU return: amount mismatch', [
                'transaction_id' => $payment->transaction_id,
                'expected' => $payu->formatAmount((float) $payment->amount),
                'received' => $response['amount'] ?? null,
            ]);

            $this->markPaymentFailed($payment, 'Amount mismatch on PayU return');

            return $this->unverifiedReturnPage(
                'The amount reported by the gateway did not match our records.',
                $payment->transaction_id
            );
        }

        $status = strtolower((string) ($response['status'] ?? ''));

        // updatePaymentStatus locks the row and credits the ledger in one
        // transaction, so a repeated return post cannot double-credit.
        //
        // Wrapped because this runs AFTER the citizen's money has moved. An
        // uncaught error here would show a 500 to someone who has just paid,
        // leaving them unsure whether to pay again. The transaction rolls back
        // on its own; reconciliation picks the payment up afterwards.
        try {
            $this->updatePaymentStatus(
                $payment,
                $status === 'success' ? 'SUCCESS' : 'FAILED',
                ['payu_response' => $response],
                $status === 'success'
            );
        } catch (\Throwable $e) {
            Log::error('PayU return: failed to record outcome', [
                'transaction_id' => $payment->transaction_id,
                'status' => $status,
                'exception' => $e->getMessage(),
            ]);

            // No session to flash to on this route, so the outcome goes through
            // the signed result page like every other path. Reconciliation will
            // settle the record within ten minutes.
            return redirect()->to($this->paymentResultUrl($payment, 'deferred'));
        }

        // No session here by design (see the route), so the outcome travels in
        // a signed URL rather than a flash message.
        return redirect()->to($this->paymentResultUrl($payment->fresh(), $status, $response));
    }

    /**
     * Rendered when a gateway result cannot be trusted or matched.
     *
     * Returned directly rather than redirected with a flash: this route runs
     * without a session, so flashing would write to a store that is never
     * persisted and the citizen would land on a silent page.
     */
    private function unverifiedReturnPage(string $reason, ?string $reference)
    {
        return response()->view('citizen.payment.unverified', [
            'reason' => $reason,
            'reference' => $reference,
        ], 200);
    }

    /**
     * Signed, short-lived URL carrying the outcome of a PayU payment.
     *
     * Signed because the return handler runs without a session and therefore
     * cannot flash: without a signature anyone could craft a URL claiming a
     * payment had succeeded.
     */
    private function paymentResultUrl(TaxPayment $payment, string $status, array $response = []): string
    {
        $outcome = match (true) {
            // Recorded outside the normal status vocabulary: the gateway said
            // paid but we could not write it down yet.
            $status === 'deferred' => 'deferred',
            $status === 'success' => 'success',
            $this->isCitizenAbandonment($this->resolveFailureCode($status, $response)) => 'cancelled',
            default => 'failed',
        };

        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'citizen.payment.result',
            now()->addMinutes(30),
            ['payment' => $payment->getKey(), 'outcome' => $outcome]
        );
    }

    /**
     * Shows the outcome after returning from a gateway.
     *
     * Not behind citizen.auth: the signature already proves we generated this
     * link, and a citizen whose session did expire must still be told whether
     * their money went through rather than being bounced to a login form.
     */
    public function paymentResult(Request $request, TaxPayment $payment)
    {
        $outcome = $request->query('outcome', 'failed');

        // The signature proves the link is ours, but if someone IS logged in it
        // must still be their own payment.
        $citizenId = Auth::guard('citizen')->id();
        if ($citizenId && (string) $payment->citizen_id !== (string) $citizenId) {
            abort(403);
        }

        $message = match ($outcome) {
            'success' => 'Payment successful. Your balance has been updated.',
            'cancelled' => $this->mapFailureReason('USER_CANCELLED'),
            'deferred' => 'Your payment went through, but we could not update your record immediately. '
                . 'Please do not pay again — it will appear within a few minutes.',
            default => $payment->failure_reason ?: $this->mapFailureReason('UNKNOWN'),
        };

        return view('citizen.payment.result', [
            'payment' => $payment,
            'outcome' => $outcome,
            'message' => $message,
            'isLoggedIn' => (bool) $citizenId,
        ]);
    }

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

        // Runs after the citizen has paid, so a failure here must not become a
        // 500 that leaves them wondering whether to pay again.
        try {
            $this->updatePaymentStatus($payment, $providerStatus, $fetchResult['data'] ?? [], true);
        } catch (\Throwable $e) {
            Log::error('Razorpay return: failed to record outcome', [
                'transaction_id' => $payment->transaction_id,
                'exception' => $e->getMessage(),
            ]);

            return redirect()->route('citizen.payment-history')->with(
                'error',
                'Your payment went through but we could not update your record immediately. '
                . 'Please do not pay again — it will appear shortly. Reference: ' . $payment->transaction_id
            );
        }

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
    /**
     * A transaction id for the PayU account belonging to this tax head, or
     * null when that head is not configured.
     */
    private function payuTransactionId(string $taxType): ?string
    {
        try {
            $payu = PayuService::forTaxType($taxType);
        } catch (\InvalidArgumentException) {
            return null;
        }

        return $payu->isEnabled() ? $payu->generateTransactionId() : null;
    }

    /**
     * Entry point for the reconciliation command.
     *
     * Deliberately routed through the same method a live callback uses, so a
     * reconciled payment gets the identical locking, transaction and
     * idempotency guard. Marked api-confirmed because the caller only ever
     * passes a status it read from the gateway's own status API.
     */
    public function reconcilePaymentStatus(TaxPayment $payment, string $status, array $data = []): void
    {
        $this->updatePaymentStatus($payment, $status, $data, true);
    }

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

        // PayU reports the reason in its own fields, and the response may also
        // arrive wrapped under payu_response. Without these the haystack was
        // just "failure", so a citizen who pressed Cancel was told there had
        // been a technical problem.
        $payu = $responseData['payu_response'] ?? $responseData;
        $payuFields = strtoupper(implode(' ', array_filter(
            [
                $payu['unmappedstatus'] ?? null,
                $payu['error_Message'] ?? null,
                $payu['error'] ?? null,
                // field9 is where PayU puts the bank's own message.
                $payu['field9'] ?? null,
            ],
            fn ($value) => is_string($value) && $value !== ''
        )));

        $haystack = trim($statusText . ' ' . $responseCode . ' ' . $responseMessage . ' ' . $state . ' ' . $payuFields);

        // "usercancelled" has no separator, so a word-boundary match would miss it.
        if (str_contains($haystack, 'CANCEL') || str_contains($haystack, 'ABORT')) {
            return 'USER_CANCELLED';
        }

        if (str_contains($haystack, 'INSUFFICIENT') || str_contains($haystack, 'NOT ENOUGH')) {
            return 'INSUFFICIENT_FUNDS';
        }

        if (str_contains($haystack, 'EXPIRE') || str_contains($haystack, 'EXPIRED')) {
            return 'EXPIRED';
        }

        if (str_contains($haystack, 'DECLIN') || str_contains($haystack, 'REJECT') || str_contains($haystack, 'DO NOT HONOR')) {
            return 'DECLINED';
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

    /**
     * Citizen-facing wording.
     *
     * Cancelling is not an error and must not be dressed as one, or people
     * assume something broke and either give up or pay twice. Every other
     * message says plainly whether money could have left their account.
     */
    protected function mapFailureReason(string $failureCode): string
    {
        return match ($failureCode) {
            'USER_CANCELLED' => 'Payment cancelled. Nothing has been charged — you can try again whenever you are ready.',
            'INSUFFICIENT_FUNDS' => 'The payment was declined for insufficient funds. Nothing has been charged.',
            'DECLINED' => 'Your bank declined the payment. Nothing has been charged. Please try another method or contact your bank.',
            'EXPIRED' => 'The payment session expired before it completed. Nothing has been charged — please start again.',
            'TIMEOUT' => 'The payment gateway did not respond in time. If money was debited it will be reversed automatically within a few days.',
            'NETWORK' => 'A network problem interrupted the payment. If money was debited it will be reversed automatically.',
            'VERIFY_FAIL' => 'We could not verify this payment. Please do not pay again — contact the Gram Panchayat office with your transaction number.',
            default => 'The payment did not complete. If money was debited, please contact the Gram Panchayat office before trying again.',
        };
    }

    /** Cancelling is a normal outcome, not a failure to apologise for. */
    protected function isCitizenAbandonment(string $failureCode): bool
    {
        return in_array($failureCode, ['USER_CANCELLED', 'EXPIRED'], true);
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
