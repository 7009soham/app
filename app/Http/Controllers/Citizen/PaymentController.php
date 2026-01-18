<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Citizen;
use App\Models\TaxPayment;
use App\Models\WaterTaxRecord;
use App\Models\MonthlyTaxBill;
use App\Models\Payment;
use App\Models\PropertyTaxRecord;
use App\Models\TaxType;
use App\Services\PhonePeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        ]);

        $citizenId = Session::get('citizen_id');
        $citizen = Citizen::findOrFail($citizenId);

        // Get the tax record
        if ($validated['tax_type'] === 'water') {
            $record = WaterTaxRecord::findOrFail($validated['record_id']);
            $targetSlug = 'water-tax'; // Try standard slug first
        } else {
            $record = PropertyTaxRecord::findOrFail($validated['record_id']);
            $targetSlug = 'property-tax';
        }

        // Verify amount doesn't exceed balance (allow for small float diffs)
        if ($validated['amount'] > ($record->balance + 1)) {
            return back()->with('error', 'Payment amount cannot exceed the outstanding balance.');
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

        // Determine period dates
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();
        
        // Try to parse period from record if available
        if (!empty($record->period)) {
            // Attempt to parse period string (e.g., "Jan 2026", "2026-01", etc.)
            try {
                $periodDate = \Carbon\Carbon::parse($record->period);
                $periodStart = $periodDate->copy()->startOfMonth();
                $periodEnd = $periodDate->copy()->endOfMonth();
            } catch (\Exception $e) {
                // Keep default dates if parsing fails
            }
        }

        // Create pending payment record
        $payment = TaxPayment::create([
            'citizen_id' => $citizenId,
            'citizen_name' => $citizen->name,
            'citizen_phone' => $citizen->phone,
            'citizen_address' => $citizen->address ?? '-',
            'tax_type' => $validated['tax_type'] . '_tax',
            'tax_type_id' => $taxTypeModel->id,
            'record_id' => $validated['record_id'],
            'transaction_id' => $transactionId,
            'amount' => $validated['amount'],
            'period_type' => 'monthly',
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'payment_method' => 'phonepe',
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_data' => [
                'customer_name' => $record->customer_name,
                'customer_no' => $record->customer_no,
                'initiated_at' => now()->toDateTimeString(),
            ],
        ]);

        // Initiate PhonePe payment
        $result = $this->phonePeService->initiatePayment([
            'transaction_id' => $transactionId,
            'amount' => $validated['amount'],
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
            'COMPLETED', 'SUCCESS' => 'success',
            'PENDING' => 'pending',
            'FAILED', 'DECLINED', 'CANCELLED' => 'failed',
            default => 'pending',
        };

        $payment->update([
            'status' => $newStatus,
            'provider_transaction_id' => $responseData['data']['transactionId'] ?? null,
            'payment_data' => array_merge($payment->payment_data ?? [], [
                'status_response' => $responseData,
                'status_updated_at' => now()->toDateTimeString(),
            ]),
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
        if ($payment->tax_type === 'water_tax') {
            $record = WaterTaxRecord::find($payment->record_id);
        } else {
            $record = PropertyTaxRecord::find($payment->record_id);
        }

        if ($record) {
            $record->update([
                'amount_paid' => $record->amount_paid + $payment->amount,
                'balance' => max(0, $record->balance - $payment->amount),
            ]);

            // Create unified payment record for Transactions history
            Payment::create([
                'citizen_id' => $payment->citizen_id,
                'tax_type' => $payment->tax_type,
                'amount' => $payment->amount,
                'payment_method' => 'online',
                'transaction_id' => $payment->transaction_id,
                'status' => 'completed',
                'paid_at' => now(),
                'remarks' => 'Online payment via PhonePe',
            ]);

            // Try to find and update monthly bill for current month if balance becomes 0
            $monthlyBill = MonthlyTaxBill::where('record_id', $record->id)
                ->where('tax_type', $payment->tax_type)
                ->where('bill_year', date('Y'))
                ->where('bill_month', date('n'))
                ->first();

            if ($monthlyBill) {
                $monthlyBill->update([
                    'paid_amount' => $monthlyBill->paid_amount + $payment->amount,
                    'balance' => max(0, $monthlyBill->balance - $payment->amount),
                    'status' => ($monthlyBill->balance - $payment->amount) <= 0 ? 'paid' : 'partial',
                    'payment_method' => 'online',
                    'paid_date' => now(),
                ]);
            }

            Log::info('Tax Record and Payment History Updated After Payment', [
                'record_id' => $record->id,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
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
