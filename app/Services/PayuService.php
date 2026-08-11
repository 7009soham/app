<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * PayU (India) gateway configuration and hashing helpers.
 *
 * Property tax and water tax settle into different bank accounts, and PayU maps
 * exactly one bank account per merchant ID. So each tax head has its own MID,
 * key and salt, and this service is always constructed for a specific head:
 *
 *     PayuService::forTaxType('property')->getMerchantKey();
 *
 * Getting this wrong moves real money into the wrong department's account, so
 * an unknown tax type throws rather than silently falling back to a default.
 *
 * PayU has no create-order API for hosted checkout: the citizen's browser
 * form-POSTs a SHA-512 signed field set and PayU posts the outcome back with a
 * reverse hash. This class holds the credentials and the hash helpers only -
 * see isReady() for what is still missing.
 */
class PayuService
{
    public const TAX_PROPERTY = 'property';
    public const TAX_WATER = 'water';

    /** Human labels, used by the admin UI and error messages. */
    public const TAX_TYPES = [
        self::TAX_PROPERTY => 'Property Tax',
        self::TAX_WATER => 'Water Tax',
    ];

    protected string $taxType;
    protected string $merchantKey;
    protected string $salt;
    protected string $merchantId;
    protected string $environment;

    public function __construct(string $taxType)
    {
        $this->taxType = self::normaliseTaxType($taxType);

        $prefix = "payu_{$this->taxType}_";

        $this->merchantKey = (string) SiteSetting::get($prefix . 'merchant_key', '');
        $this->salt = (string) SiteSetting::get($prefix . 'merchant_salt', '');
        $this->merchantId = (string) SiteSetting::get($prefix . 'merchant_id', '');

        // Sandbox/production is an account-wide choice, not a per-head one.
        $this->environment = (string) SiteSetting::get('payu_env', 'sandbox');
    }

    public static function forTaxType(string $taxType): self
    {
        return new self($taxType);
    }

    /**
     * Accepts the request value ("water"), the stored value ("water_tax") or
     * the label, and returns the canonical key.
     *
     * @throws InvalidArgumentException on anything unrecognised - never guess
     *         which bank account a payment belongs to.
     */
    public static function normaliseTaxType(string $taxType): string
    {
        // Separators are unified before the suffix is stripped, so "Water Tax",
        // "water-tax" and "water_tax" all reduce to "water".
        $key = Str::of($taxType)->lower()->trim()->replace(['-', ' '], '_')->toString();
        $key = Str::endsWith($key, '_tax') ? Str::beforeLast($key, '_tax') : $key;

        if (!array_key_exists($key, self::TAX_TYPES)) {
            throw new InvalidArgumentException(
                "Unknown PayU tax type [{$taxType}]. Expected one of: " . implode(', ', array_keys(self::TAX_TYPES))
            );
        }

        return $key;
    }

    /** Setting keys owned by one tax head. */
    public static function settingKeysFor(string $taxType): array
    {
        $prefix = 'payu_' . self::normaliseTaxType($taxType) . '_';

        return [
            'key' => $prefix . 'merchant_key',
            'salt' => $prefix . 'merchant_salt',
            'mid' => $prefix . 'merchant_id',
        ];
    }

    public function getTaxType(): string
    {
        return $this->taxType;
    }

    public function getLabel(): string
    {
        return self::TAX_TYPES[$this->taxType];
    }

    /**
     * Credentials are present for THIS tax head and PayU is switched on.
     * Property tax being configured says nothing about water tax.
     */
    public function isEnabled(): bool
    {
        return SiteSetting::get('payu_enabled', '0') === '1'
            && $this->hasCredentials();
    }

    public function hasCredentials(): bool
    {
        return $this->merchantKey !== '' && $this->salt !== '';
    }

    /**
     * PayU can actually take a payment for this head. Always false until the
     * checkout page and callback route exist, so callers never route a citizen
     * to a dead end.
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

    public function getPaymentUrl(): string
    {
        return $this->isSandbox()
            ? 'https://test.payu.in/_payment'
            : 'https://secure.payu.in/_payment';
    }

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
     * Request hash for the outgoing form:
     * sha512(key|txnid|amount|productinfo|firstname|email|udf1..udf5||||||salt)
     *
     * The amount must be the exact string posted (rupees, two decimals) - PayU
     * compares it byte for byte.
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
     * Verify the reverse hash on PayU's response: the request sequence
     * reversed, status after the salt, additional_charges prepended when set.
     *
     * Must be called on the service for the SAME tax head that initiated the
     * payment - the other head's salt will never validate.
     */
    public function verifyResponseHash(array $response): bool
    {
        $postedHash = strtolower((string) ($response['hash'] ?? ''));

        if ($postedHash === '' || $this->salt === '') {
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

        return hash_equals(strtolower(hash('sha512', implode('|', $sequence))), $postedHash);
    }

    /**
     * Identify which tax head a PayU response belongs to by finding the only
     * configured head whose salt validates the reverse hash.
     *
     * The callback must not trust a tax type supplied in the response body -
     * that is attacker-controlled. The signature is the only trustworthy
     * indicator of which merchant account the payment actually went to.
     */
    public static function resolveFromResponse(array $response): ?self
    {
        foreach (array_keys(self::TAX_TYPES) as $taxType) {
            $service = new self($taxType);

            if ($service->hasCredentials() && $service->verifyResponseHash($response)) {
                return $service;
            }
        }

        return null;
    }

    /** PayU rejects txnids containing anything outside this set. */
    public function sanitizeTransactionId(string $txnid): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $txnid) ?? '';
    }
}
