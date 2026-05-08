<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RazorpayService
{
    protected string $keyId;
    protected string $keySecret;

    public function __construct()
    {
        $this->keyId = SiteSetting::get('razorpay_key_id', '');
        $this->keySecret = SiteSetting::get('razorpay_key_secret', '');
    }

    public function isEnabled(): bool
    {
        return SiteSetting::get('razorpay_enabled', '0') === '1'
            && !empty($this->keyId)
            && !empty($this->keySecret);
    }

    public function getKeyId(): string
    {
        return $this->keyId;
    }

    public function generateTransactionId(): string
    {
        return 'TXN' . strtoupper(Str::random(8)) . time();
    }

    public function createOrder(array $params): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'Razorpay is not configured.'];
        }

        $amount = (int) round($params['amount'] * 100); // Convert to paise

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount'   => $amount,
                    'currency' => 'INR',
                    'receipt'  => $params['transaction_id'],
                    'notes'    => ['citizen_id' => $params['user_id'] ?? ''],
                ]);

            $data = $response->json();

            Log::info('Razorpay Create Order Response', [
                'transaction_id' => $params['transaction_id'],
                'response'       => $data,
            ]);

            if ($response->successful() && isset($data['id'])) {
                return [
                    'success'  => true,
                    'order_id' => $data['id'],
                    'amount'   => $amount,
                    'data'     => $data,
                ];
            }

            return [
                'success' => false,
                'message' => $data['error']['description'] ?? 'Order creation failed.',
                'data'    => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay Create Order Error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Payment gateway error: ' . $e->getMessage()];
        }
    }

    public function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
        return hash_equals($expected, $signature);
    }

    public function fetchPayment(string $paymentId): array
    {
        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->get("https://api.razorpay.com/v1/payments/{$paymentId}");

            $data = $response->json();

            Log::info('Razorpay Fetch Payment', ['payment_id' => $paymentId, 'data' => $data]);

            if ($response->successful()) {
                return [
                    'success'      => true,
                    'status'       => $data['status'] ?? 'unknown',
                    'is_completed' => ($data['status'] ?? '') === 'captured',
                    'data'         => $data,
                ];
            }

            return [
                'success' => false,
                'message' => $data['error']['description'] ?? 'Fetch failed.',
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay Fetch Payment Error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
