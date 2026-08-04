<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PhonePeService
{
    protected $merchantId;
    protected $saltKey;
    protected $saltIndex;
    protected $environment;
    protected $baseUrl;

    public function __construct()
    {
        $this->merchantId = SiteSetting::get('phonepe_merchant_id', '');
        $this->saltKey = SiteSetting::get('phonepe_salt_key', '');
        $this->saltIndex = SiteSetting::get('phonepe_salt_index', '1');
        $this->environment = SiteSetting::get('phonepe_env', 'sandbox');
        
        $this->baseUrl = $this->environment === 'production'
            ? 'https://api.phonepe.com/apis/hermes'
            : 'https://api-preprod.phonepe.com/apis/pg-sandbox';
            
        // Debug logging
        Log::info('PhonePe Service Initialized', [
            'merchant_id' => $this->merchantId ? substr($this->merchantId, 0, 5) . '***' : 'EMPTY',
            'salt_key_set' => !empty($this->saltKey),
            'salt_index' => $this->saltIndex,
            'environment' => $this->environment,
            'base_url' => $this->baseUrl,
        ]);
    }

    /**
     * Check if PhonePe is enabled and configured
     */
    public function isEnabled(): bool
    {
        return SiteSetting::get('phonepe_enabled', '0') === '1' 
            && !empty($this->merchantId) 
            && !empty($this->saltKey);
    }

    /**
     * Generate unique transaction ID
     */
    public function generateTransactionId(): string
    {
        return 'TXN' . strtoupper(Str::random(8)) . time();
    }

    /**
     * Generate checksum for PhonePe v2 API
     */
    protected function generateChecksum(string $payload, string $endpoint): string
    {
        $base64Payload = base64_encode($payload);
        $stringToSign = $base64Payload . $endpoint . $this->saltKey;
        $sha256Hash = hash('sha256', $stringToSign);
        
        return $sha256Hash . '###' . $this->saltIndex;
    }

    /**
     * Initiate Payment - PhonePe v2 API
     */
    public function initiatePayment(array $params): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Payment gateway is not configured.',
            ];
        }

        $transactionId = $params['transaction_id'] ?? $this->generateTransactionId();
        $amount = $params['amount'] * 100; // Convert to paise
        $callbackUrl = $params['callback_url'];
        $redirectUrl = $params['redirect_url'];
        $mobileNumber = $params['mobile'] ?? '';
        $userId = $params['user_id'] ?? 'USER' . time();

        $payload = [
            'merchantId' => $this->merchantId,
            'merchantTransactionId' => $transactionId,
            'merchantUserId' => $userId,
            'amount' => $amount,
            'redirectUrl' => $redirectUrl,
            'redirectMode' => 'REDIRECT',
            'callbackUrl' => $callbackUrl,
            'mobileNumber' => $mobileNumber,
            'paymentInstrument' => [
                'type' => 'PAY_PAGE',
            ],
        ];

        $jsonPayload = json_encode($payload);
        $base64Payload = base64_encode($jsonPayload);
        $endpoint = '/pg/v1/pay';
        
        $checksum = $this->generateChecksum($jsonPayload, $endpoint);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-VERIFY' => $checksum,
            ])->post($this->baseUrl . $endpoint, [
                'request' => $base64Payload,
            ]);

            $responseData = $response->json();

            Log::info('PhonePe Initiate Payment Response', [
                'transaction_id' => $transactionId,
                'response' => $responseData,
            ]);

            if ($response->successful() && isset($responseData['success']) && $responseData['success']) {
                $redirectUrl = $responseData['data']['instrumentResponse']['redirectInfo']['url'] ?? null;
                
                return [
                    'success' => true,
                    'transaction_id' => $transactionId,
                    'redirect_url' => $redirectUrl,
                    'data' => $responseData,
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'Payment initiation failed.',
                'data' => $responseData,
            ];

        } catch (\Exception $e) {
            Log::error('PhonePe Payment Error', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);

            return [
                'success' => false,
                'message' => 'Payment gateway error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check Payment Status - PhonePe v2 API
     */
    public function checkPaymentStatus(string $transactionId): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Payment gateway is not configured.',
            ];
        }

        $endpoint = "/pg/v1/status/{$this->merchantId}/{$transactionId}";
        $stringToSign = $endpoint . $this->saltKey;
        $sha256Hash = hash('sha256', $stringToSign);
        $checksum = $sha256Hash . '###' . $this->saltIndex;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-VERIFY' => $checksum,
                'X-MERCHANT-ID' => $this->merchantId,
            ])->get($this->baseUrl . $endpoint);

            $responseData = $response->json();

            Log::info('PhonePe Status Check Response', [
                'transaction_id' => $transactionId,
                'response' => $responseData,
            ]);

            if ($response->successful() && isset($responseData['success']) && $responseData['success']) {
                $paymentState = $responseData['data']['state'] ?? 'UNKNOWN';
                
                return [
                    'success' => true,
                    'payment_status' => $paymentState,
                    'is_completed' => $paymentState === 'COMPLETED',
                    'is_pending' => $paymentState === 'PENDING',
                    'is_failed' => in_array($paymentState, ['FAILED', 'DECLINED', 'CANCELLED', 'PAYMENT_ERROR'], true),
                    'transaction_id' => $responseData['data']['merchantTransactionId'] ?? $transactionId,
                    'provider_transaction_id' => $responseData['data']['transactionId'] ?? null,
                    'amount' => ($responseData['data']['amount'] ?? 0) / 100, // Convert from paise
                    'data' => $responseData,
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'Status check failed.',
                'data' => $responseData,
            ];

        } catch (\Exception $e) {
            Log::error('PhonePe Status Check Error', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);

            return [
                'success' => false,
                'message' => 'Status check error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify callback checksum
     */
    public function verifyCallback(array $callbackData): bool
    {
        if (!isset($callbackData['response']) || !isset($callbackData['checksum'])) {
            return false;
        }

        $response = $callbackData['response'];
        $receivedChecksum = $callbackData['checksum'];

        $stringToSign = $response . $this->saltKey;
        $calculatedHash = hash('sha256', $stringToSign);
        $expectedChecksum = $calculatedHash . '###' . $this->saltIndex;

        // Constant-time comparison: a plain === leaks how much of the checksum
        // matched through response timing.
        return hash_equals($expectedChecksum, (string) $receivedChecksum);
    }

    /**
     * Decode callback response
     */
    public function decodeCallback(string $response): array
    {
        $decodedResponse = base64_decode($response);
        return json_decode($decodedResponse, true) ?? [];
    }

    /**
     * Process refund - PhonePe v2 API
     */
    public function initiateRefund(array $params): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Payment gateway is not configured.',
            ];
        }

        $originalTransactionId = $params['original_transaction_id'];
        $refundTransactionId = $params['refund_transaction_id'] ?? 'REF' . strtoupper(Str::random(8)) . time();
        $amount = $params['amount'] * 100; // Convert to paise
        $callbackUrl = $params['callback_url'] ?? url('/citizen/refund/callback');

        $payload = [
            'merchantId' => $this->merchantId,
            'merchantUserId' => $params['user_id'] ?? 'USER' . time(),
            'originalTransactionId' => $originalTransactionId,
            'merchantTransactionId' => $refundTransactionId,
            'amount' => $amount,
            'callbackUrl' => $callbackUrl,
        ];

        $jsonPayload = json_encode($payload);
        $base64Payload = base64_encode($jsonPayload);
        $endpoint = '/pg/v1/refund';
        
        $checksum = $this->generateChecksum($jsonPayload, $endpoint);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-VERIFY' => $checksum,
            ])->post($this->baseUrl . $endpoint, [
                'request' => $base64Payload,
            ]);

            $responseData = $response->json();

            Log::info('PhonePe Refund Response', [
                'refund_transaction_id' => $refundTransactionId,
                'original_transaction_id' => $originalTransactionId,
                'response' => $responseData,
            ]);

            if ($response->successful() && isset($responseData['success']) && $responseData['success']) {
                return [
                    'success' => true,
                    'refund_transaction_id' => $refundTransactionId,
                    'data' => $responseData,
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'Refund initiation failed.',
                'data' => $responseData,
            ];

        } catch (\Exception $e) {
            Log::error('PhonePe Refund Error', [
                'error' => $e->getMessage(),
                'original_transaction_id' => $originalTransactionId,
            ]);

            return [
                'success' => false,
                'message' => 'Refund error: ' . $e->getMessage(),
            ];
        }
    }
}
