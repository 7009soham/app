<?php

namespace App\Services;

use App\Models\SiteSetting;

/**
 * Single source of truth for which payment gateways a citizen may actually use.
 *
 * The pay-bill page used to hardcode PhonePe: both radio buttons posted
 * "phonepe", the form was hidden unless PhonePe specifically was configured,
 * and PaymentController ignored the posted value anyway and read
 * active_payment_gateway from settings. So the choice shown to citizens was
 * decorative and could contradict what the admin had configured.
 *
 * "Available" here means the gateway can take money right now: switched on,
 * credentials present, AND implemented end to end. A gateway that is merely
 * configured is not offered - sending a citizen to a checkout that does not
 * exist loses the payment and the trust.
 */
class PaymentGatewayRegistry
{
    public function __construct(
        private PhonePeService $phonePe,
        private RazorpayService $razorpay,
    ) {
    }

    /**
     * Every known gateway with its presentation details and whether it can be
     * used for this tax type. Unavailable ones carry a reason for the admin.
     *
     * @param string|null $taxType 'water' or 'property'; PayU credentials are
     *                             per tax head, so availability depends on it.
     */
    public function all(?string $taxType = null): array
    {
        return [
            'phonepe' => [
                'key' => 'phonepe',
                'label' => 'PhonePe',
                'description' => 'UPI, Cards, Wallets, Net Banking',
                'icon' => 'fas fa-mobile-alt',
                'colour' => '#5f259f',
                'available' => $this->phonePe->isEnabled(),
                'reason' => $this->phonePe->isEnabled() ? null : 'Not enabled or missing credentials.',
            ],
            'razorpay' => [
                'key' => 'razorpay',
                'label' => 'Razorpay',
                'description' => 'UPI, Cards, NetBanking, Wallets',
                'icon' => 'fas fa-credit-card',
                'colour' => '#3b82f6',
                'available' => $this->razorpay->isEnabled(),
                'reason' => $this->razorpay->isEnabled() ? null : 'Not enabled or missing credentials.',
            ],
            'payu' => $this->payuEntry($taxType),
        ];
    }

    /** Only the gateways a citizen can be sent to right now. */
    public function available(?string $taxType = null): array
    {
        return array_filter(
            $this->all($taxType),
            fn (array $gateway) => $gateway['available']
        );
    }

    public function hasAny(?string $taxType = null): bool
    {
        return $this->available($taxType) !== [];
    }

    /**
     * The gateway a payment will use when the citizen expresses no preference.
     *
     * Falls back to the first available one rather than the configured value,
     * so switching the admin setting to a gateway that is not ready does not
     * silently break checkout for everyone.
     */
    public function default(?string $taxType = null): ?string
    {
        $available = $this->available($taxType);

        if ($available === []) {
            return null;
        }

        $configured = SiteSetting::get('active_payment_gateway', 'phonepe');

        return isset($available[$configured])
            ? $configured
            : array_key_first($available);
    }

    /** Whether a citizen-supplied choice may be honoured. */
    public function isSelectable(?string $gateway, ?string $taxType = null): bool
    {
        return $gateway !== null && isset($this->available($taxType)[$gateway]);
    }

    private function payuEntry(?string $taxType): array
    {
        $entry = [
            'key' => 'payu',
            'label' => 'PayU',
            'description' => 'UPI, Cards, NetBanking',
            'icon' => 'fas fa-university',
            'colour' => '#00838f',
            'available' => false,
            'reason' => null,
        ];

        try {
            $service = PayuService::forTaxType($taxType ?? PayuService::TAX_PROPERTY);
        } catch (\InvalidArgumentException) {
            $entry['reason'] = 'Unknown tax type for PayU credentials.';

            return $entry;
        }

        if (!$service->isEnabled()) {
            $entry['reason'] = 'Not enabled, or no merchant key and salt for this tax head.';

            return $entry;
        }

        // Configured correctly but the signed-form checkout and callback route
        // do not exist yet, so it must not be offered.
        if (!$service->isReady()) {
            $entry['reason'] = 'Credentials saved, but the PayU checkout flow is not implemented yet.';

            return $entry;
        }

        $entry['available'] = true;

        return $entry;
    }
}
