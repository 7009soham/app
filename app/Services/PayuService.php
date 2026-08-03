<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Str;

/**
 * PayU (India) gateway configuration and hashing helpers.
 *
 * PayU does not expose a create-order API for its hosted checkout. Instead the
 * citizen's browser form-POSTs a SHA-512 signed field set to PayU, and PayU
 * posts the outcome back with a reverse hash we verify. This class holds the
 * credentials and the hash helpers only.
 *
 * NOTE: the checkout page and callback route are not implemented yet, so PayU
 * cannot process payments even when enabled here. See isReady().
 */
class PayuService
{
    protected string $merchantKey;
    protected string $salt;
    protected string $merchantId;
    protected string $environment;

    public function __construct()
    {
        $this->merchantKey = SiteSetting::get('payu_merchant_key', '');
        $this->salt = SiteSetting::get('payu_merchant_salt', '');
        $this->merchantId = SiteSetting::get('payu_merchant_id', '');
        $this->environment = SiteSetting::get('payu_env', 'sandbox');
    }

    /**
     * Credentials are present and the gateway is switched on in the admin panel.
     */
    public function isEnabled(): bool
    {
        return SiteSetting::get('payu_enabled', '0') === '1'
            && !empty($this->merchantKey)
            && !empty($this->salt);
    }

    /**
     * PayU can actually take a payment. Always false until the checkout page and
     * callback route are built, so callers never route citizens to a dead end.
     */
    public function isReady(): bool
    {
        return false;
    }

    public function getMerchantKey(): string
    {
        return $this->merchantKey;
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function isSandbox(): bool
    {
        return $this->environment !== 'production';
    }

    /**
     * Base URL the signed checkout form posts to.
     */
    public function getPaymentUrl(): string
    {
        return $this->isSandbox()
            ? 'https://test.payu.in/_payment'
            : 'https://secure.payu.in/_payment';
    }

    /**
     * Server-to-server verification endpoint.
     */
    public function getVerifyUrl(): string
    {
        return $this->isSandbox()
            ? 'https://test.payu.in/merchant/postservice.php?form=2'
            : 'https://info.payu.in/merchant/postservice.php?form=2';
    }

    public function generateTransactionId(): string
    {
        return 'TXN' . strtoupper(Str::random(8)) . time();
    }

    /**
     * Request hash PayU expects on the outgoing form:
     * sha512(key|txnid|amount|productinfo|firstname|email|udf1..udf5||||||salt)
     *
     * The amount must be the same string that is posted, in rupees with two
     * decimals - PayU compares it byte for byte.
     */
    public function generateRequestHash(array $params): string
    {
        $sequence = [
            $this->merchantKey,
            $params['txnid'] ?? '',
            $params['amount'] ?? '',
            $params['productinfo'] ?? '',
            $params['firstname'] ?? '',
            $params['email'] ?? '',
            $params['udf1'] ?? '',
            $params['udf2'] ?? '',
            $params['udf3'] ?? '',
            $params['udf4'] ?? '',
            $params['udf5'] ?? '',
            '', '', '', '', '',
            $this->salt,
        ];

        return strtolower(hash('sha512', implode('|', $sequence)));
    }

    /**
     * Verify the reverse hash PayU signs its response with. The sequence is the
     * request sequence reversed, with the status inserted after the salt, and
     * additional_charges prepended when PayU applied any.
     */
    public function verifyResponseHash(array $response): bool
    {
        $postedHash = strtolower((string) ($response['hash'] ?? ''));

        if ($postedHash === '' || empty($this->salt)) {
            return false;
        }

        $sequence = [
            $this->salt,
            $response['status'] ?? '',
            '', '', '', '', '',
            $response['udf5'] ?? '',
            $response['udf4'] ?? '',
            $response['udf3'] ?? '',
            $response['udf2'] ?? '',
            $response['udf1'] ?? '',
            $response['email'] ?? '',
            $response['firstname'] ?? '',
            $response['productinfo'] ?? '',
            $response['amount'] ?? '',
            $response['txnid'] ?? '',
            $this->merchantKey,
        ];

        if (!empty($response['additional_charges'])) {
            array_unshift($sequence, $response['additional_charges']);
        }

        $calculated = strtolower(hash('sha512', implode('|', $sequence)));

        return hash_equals($calculated, $postedHash);
    }

    /**
     * PayU rejects txnids containing anything outside this set.
     */
    public function sanitizeTransactionId(string $txnid): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $txnid) ?? '';
    }
}
