@extends('layouts.app')

@section('title', __('messages.disclaimer') . ' - ' . ($settings['site_name'] ?? 'Gram Panchayat'))

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>{{ __('messages.disclaimer') }}</h1>
        <p>Last updated: {{ date('F d, Y') }}</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="content-card">
            <h2>1. General Disclaimer</h2>
            <p>This website is designed, developed and maintained by {{ $settings['site_name'] ?? 'Gram Panchayat' }}. While every effort has been made to ensure the accuracy and currency of the information published on this portal, it should not be construed as a statement of law or used for any legal purpose.</p>

            <h2>2. Accuracy of Information</h2>
            <p>The contents of this website, including tax dues, property records and water tax records, are provided for the convenience of citizens. In the event of any discrepancy between the information displayed on this portal and the official records maintained at the Gram Panchayat office, <strong>the official office records shall prevail</strong>.</p>
            <p>{{ $settings['site_name'] ?? 'Gram Panchayat' }} accepts no responsibility in relation to the accuracy, completeness, usefulness or otherwise of the contents. Users are advised to verify critical information with the Gram Panchayat office before acting upon it.</p>

            <h2>3. Online Payments</h2>
            <p>Payments made through this portal are processed by third-party payment gateways. A payment is considered successful only when a valid digital receipt bearing a transaction reference number is generated. Users are advised to retain the receipt for their records.</p>
            <p>{{ $settings['site_name'] ?? 'Gram Panchayat' }} shall not be liable for any loss arising out of failed, delayed or interrupted transactions caused by network failure, bank downtime, or incorrect details entered by the user. For payment disputes, please refer to our <a href="{{ route('refund-policy') }}">{{ __('messages.refund_policy') }}</a>.</p>

            <h2>4. External Links</h2>
            <p>This website contains links to websites maintained by other government bodies and third-party organisations. We are not responsible for the contents or reliability of linked external websites and do not necessarily endorse the views expressed within them. Please refer to our <a href="{{ route('hyperlinking-policy') }}">{{ __('messages.hyperlinking_policy') }}</a> for details.</p>

            <h2>5. Service Availability</h2>
            <p>While we strive to keep this portal available at all times, we do not guarantee uninterrupted access. The portal may be unavailable during scheduled maintenance, upgrades, or due to circumstances beyond our control. We reserve the right to suspend or withdraw any service without prior notice.</p>

            <h2>6. Limitation of Liability</h2>
            <p>In no event shall {{ $settings['site_name'] ?? 'Gram Panchayat' }}, its officers or employees be liable for any direct, indirect, incidental or consequential loss or damage whatsoever arising from the use of, or inability to use, this website.</p>

            <h2>7. Jurisdiction</h2>
            <p>These terms are governed by the laws of India. Any dispute arising out of the use of this website shall be subject to the exclusive jurisdiction of the courts of Maharashtra, India.</p>

            <h2>8. Contact Us</h2>
            <p>If you have any questions regarding this Disclaimer, please contact us at:</p>
            <div class="contact-box">
                <p><strong>{{ $settings['site_name'] ?? 'Gram Panchayat' }}</strong></p>
                <p>{{ $settings['address'] ?? '' }}</p>
                <p>Email: {{ $settings['contact_email'] ?? '' }}</p>
                <p>Phone: {{ $settings['contact_phone'] ?? '' }}</p>
            </div>
        </div>
    </div>
</section>
@endsection
