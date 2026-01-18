@extends('layouts.app')

@section('title', 'Terms & Conditions - ' . ($settings['site_name'] ?? 'Gram Panchayat'))

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>Terms & Conditions</h1>
        <p>Last updated: {{ date('F d, Y') }}</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="content-card">
            <h2>1. Acceptance of Terms</h2>
            <p>By accessing and using the {{ $settings['site_name'] ?? 'Gram Panchayat' }} online portal, you accept and agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use our services.</p>

            <h2>2. Description of Services</h2>
            <p>The {{ $settings['site_name'] ?? 'Gram Panchayat' }} portal provides the following online services:</p>
            <ul>
                <li>Online payment of House Tax</li>
                <li>Online payment of Water Tax</li>
                <li>Viewing of tax dues and payment history</li>
                <li>Downloading of payment receipts</li>
                <li>Other government services as may be added from time to time</li>
            </ul>

            <h2>3. User Responsibilities</h2>
            <p>As a user of this portal, you agree to:</p>
            <ul>
                <li>Provide accurate and complete information</li>
                <li>Maintain the confidentiality of your account credentials</li>
                <li>Notify us immediately of any unauthorized use of your account</li>
                <li>Use the portal only for lawful purposes</li>
                <li>Not attempt to interfere with the proper functioning of the portal</li>
            </ul>

            <h2>4. Payment Terms</h2>
            <h3>4.1 Payment Processing</h3>
            <p>All payments are processed through PhonePe payment gateway. By making a payment, you agree to PhonePe's terms and conditions.</p>
            
            <h3>4.2 Payment Confirmation</h3>
            <p>A payment is considered complete only when you receive a confirmation from both the payment gateway and our portal. Please save your transaction ID for future reference.</p>
            
            <h3>4.3 Payment Disputes</h3>
            <p>In case of any payment disputes, please contact our office within 7 days of the transaction with your transaction ID and payment details.</p>

            <h2>5. Tax Calculation</h2>
            <p>Tax amounts displayed on the portal are calculated based on the information available in our records. If you believe there is an error in the tax calculation, please contact our office for verification before making payment.</p>

            <h2>6. Service Availability</h2>
            <p>While we strive to maintain the portal's availability 24/7, we do not guarantee uninterrupted access. The portal may be temporarily unavailable due to:</p>
            <ul>
                <li>Scheduled maintenance</li>
                <li>Technical issues</li>
                <li>Force majeure events</li>
            </ul>

            <h2>7. Limitation of Liability</h2>
            <p>{{ $settings['site_name'] ?? 'Gram Panchayat' }} shall not be liable for:</p>
            <ul>
                <li>Any indirect, incidental, or consequential damages</li>
                <li>Loss of data or interruption of service</li>
                <li>Errors or omissions in the content</li>
                <li>Delays or failures in payment processing due to third-party services</li>
            </ul>

            <h2>8. Intellectual Property</h2>
            <p>All content on this portal, including text, graphics, logos, and software, is the property of {{ $settings['site_name'] ?? 'Gram Panchayat' }} and is protected by applicable intellectual property laws.</p>

            <h2>9. Governing Law</h2>
            <p>These terms and conditions are governed by and construed in accordance with the laws of India. Any disputes arising from the use of this portal shall be subject to the exclusive jurisdiction of courts in the district.</p>

            <h2>10. Modifications</h2>
            <p>We reserve the right to modify these Terms and Conditions at any time. Changes will be effective immediately upon posting on the portal. Your continued use of the portal after changes are posted constitutes your acceptance of the modified terms.</p>

            <h2>11. Contact Information</h2>
            <p>For any questions regarding these Terms and Conditions, please contact:</p>
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
