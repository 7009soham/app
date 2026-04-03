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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class PaymentController extends Controller
{
    protected $phonePeService;

    public function __construct(PhonePeService $phonePeService)
    {
        $this->phonePeService = $phonePeService;
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

        // Check if PhonePe is enabled
        if (!$this->phonePeService->isEnabled()) {
            return back()->with('error', 'Payment gateway is currently not available. Please try again later.');
        }

        // Generate transaction ID
        $transactionId = $this->phonePeService->generateTransactionId();

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
        $payment = TaxPayment::create([
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
            'payment_method'  => 'phonepe',
            'status'          => 'pending',
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
        ]);

        // Initiate PhonePe payment (total = tax + convenience fee)
        $result = $this->phonePeService->initiatePayment([
            'transaction_id' => $transactionId,
            'amount' => $totalAmount,
            'callback_url' => route('citizen.payment.callback'),
            'redirect_url' => route('citizen.payment.redirect'),
            'mobile' => $citizen->phone,
            'user_id' => 'CITIZEN' . $citizenId,
        ]);

        if ($result['success'] && isset($result['redirect_url'])) {
            // Store payment ID in session for verification
            Session::put('pending_payment_id', $payment->id);
            
            return redirect()->away($result['redirect_url']);
        }

        // Payment initiation failed
        $payment->update([
            'status' => 'failed',
            'payment_data' => array_merge($payment->payment_data ?? [], [
                'error' => $result['message'] ?? 'Payment initiation failed',
            ]),
        ]);

        return back()->with('error', $result['message'] ?? 'Unable to initiate payment. Please try again.');
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

        $this->updatePaymentStatus($payment, $paymentState, $decodedResponse);

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
            $this->updatePaymentStatus($payment, $statusResult['payment_status'], $statusResult['data'] ?? []);

            if ($statusResult['is_completed']) {
                return redirect()->route('citizen.payment-history')
                    ->with('success', 'Payment successful! Transaction ID: ' . $payment->transaction_id);
            } elseif ($statusResult['is_pending']) {
                return redirect()->route('citizen.payment-history')
                    ->with('warning', 'Payment is being processed. Please check back in a few minutes.');
            }
        }

        return redirect()->route('citizen.payment-history')
            ->with('error', 'Payment failed or was cancelled. Please try again.');
    }

    /**
     * Update payment status and related records
     */
    protected function updatePaymentStatus(TaxPayment $payment, string $status, array $responseData = [])
    {
        $newStatus = match ($status) {
            'COMPLETED', 'SUCCESS', 'PAYMENT_SUCCESS' => 'success',
            'PENDING' => 'pending',
            'FAILED', 'DECLINED', 'CANCELLED' => 'failed',
            default => 'pending',
        };

        $providerTransactionId = $responseData['data']['transactionId'] ?? null;

        $updateData = [
            'status' => $newStatus,
            'payment_status' => $newStatus === 'success' ? 'completed' : $newStatus,
            'provider_transaction_id' => $providerTransactionId,
            'phonepe_transaction_id' => $providerTransactionId,
            'payment_data' => array_merge($payment->payment_data ?? [], [
                'status_response' => $responseData,
                'status_updated_at' => now()->toDateTimeString(),
            ]),
        ];

        // Set paid_at timestamp when payment is successful
        if ($newStatus === 'success') {
            $updateData['paid_at'] = now();
        }

        $payment->update($updateData);

        Log::info('Payment Status Updated', [
            'payment_id' => $payment->id,
            'transaction_id' => $payment->transaction_id,
            'status' => $newStatus,
            'payment_status' => $updateData['payment_status'],
        ]);

        // If payment is successful, update the tax record
        if ($newStatus === 'success') {
            $this->updateTaxRecord($payment);
        }
    }

    /**
     * Update tax record after successful payment
     */
    protected function updateTaxRecord(TaxPayment $payment)
    {
        $existingPaymentData = $payment->payment_data ?? [];
        if (!empty($existingPaymentData['ledger_updated_at'])) {
            Log::info('Payment already processed for ledger update; skipping duplicate post-processing.', [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
            ]);
            return;
        }

        // Extract the actual tax amount (excluding convenience fee)
        $paymentData = $existingPaymentData;
        $convenienceFee = floatval($paymentData['convenience_fee'] ?? 0);
        $taxAmount = floatval($paymentData['tax_amount'] ?? ($payment->amount - $convenienceFee));
        $record = null;
        $monthlyBill = null;
        $annualBill = null;

        if ($payment->tax_type === 'water_tax') {
            $record = WaterTaxRecord::find($payment->record_id);

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
            // Property Tax – annual billing
            $record = PropertyTaxRecord::find($payment->record_id);

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

        // Create unified payment record (full amount including convenience fee)
        Payment::create([
            'citizen_id'     => $payment->citizen_id,
            'tax_type'       => $payment->tax_type,
            'bill_id'        => $monthlyBill?->id,
            'amount'         => $payment->amount,
            'payment_method' => 'online',
            'transaction_id' => $payment->transaction_id,
            'status'         => 'completed',
            'paid_at'        => now(),
            'remarks'        => $convenienceFee > 0 
                ? 'Online payment via PhonePe (Tax: ₹' . number_format($taxAmount, 2) . ', Convenience Fee: ₹' . number_format($convenienceFee, 2) . ')' 
                : 'Online payment via PhonePe',
        ]);

        $payment->update([
            'payment_data' => array_merge($existingPaymentData, [
                'ledger_updated_at' => now()->toDateTimeString(),
            ]),
        ]);

        $citizen = $payment->citizen;
        $this->sendPaymentConfirmationEmail(
            $payment,
            $citizen,
            $record,
            $monthlyBill,
            $annualBill,
            $taxAmount,
            $convenienceFee
        );

        Log::info('Tax Record Updated After Online Payment', [
            'record_id'       => $payment->record_id,
            'payment_id'      => $payment->id,
            'tax_type'        => $payment->tax_type,
            'total_amount'    => $payment->amount,
            'tax_amount'      => $taxAmount,
            'convenience_fee' => $convenienceFee,
        ]);
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

        // If still pending, check with PhonePe
        if ($payment->status === 'pending') {
            $statusResult = $this->phonePeService->checkPaymentStatus($transactionId);
            
            if ($statusResult['success']) {
                $this->updatePaymentStatus($payment, $statusResult['payment_status'], $statusResult['data'] ?? []);
                $payment->refresh();
            }
        }

        return response()->json([
            'success' => true,
            'status' => $payment->status,
            'transaction_id' => $payment->transaction_id,
            'amount' => $payment->amount,
        ]);
    }
}
