@extends('layouts.app')

@section('title', 'Privacy Policy - ' . ($settings['site_name'] ?? 'Gram Panchayat'))

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>Privacy Policy</h1>
        <p>Last updated: {{ date('F d, Y') }}</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="content-card">
            <h2>1. Introduction</h2>
            <p>{{ $settings['site_name'] ?? 'Gram Panchayat' }} ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our online tax payment services.</p>

            <h2>2. Information We Collect</h2>
            <h3>Personal Information</h3>
            <p>We may collect personal information that you voluntarily provide to us when you:</p>
            <ul>
                <li>Register for an account</li>
                <li>Make tax payments online</li>
                <li>Contact us through our website</li>
                <li>Subscribe to our notifications</li>
            </ul>
            
            <p>This information may include:</p>
            <ul>
                <li>Full name</li>
                <li>Phone number</li>
                <li>Email address</li>
                <li>Residential address</li>
                <li>Property details</li>
                <li>Payment information (processed securely through PhonePe)</li>
            </ul>

            <h2>3. How We Use Your Information</h2>
            <p>We use the information we collect to:</p>
            <ul>
                <li>Process your tax payments</li>
                <li>Send payment confirmations and receipts</li>
                <li>Provide customer support</li>
                <li>Send important notices regarding your tax obligations</li>
                <li>Improve our services</li>
                <li>Comply with legal obligations</li>
            </ul>

            <h2>4. Information Security</h2>
            <p>We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. All payment transactions are processed through secure payment gateways (PhonePe) and we do not store your complete payment card details.</p>

            <h2>5. Information Sharing</h2>
            <p>We do not sell, trade, or rent your personal information to third parties. We may share your information only:</p>
            <ul>
                <li>With government authorities as required by law</li>
                <li>With payment processors to complete transactions</li>
                <li>To comply with legal obligations</li>
                <li>To protect our rights and property</li>
            </ul>

            <h2>6. Data Retention</h2>
            <p>We retain your personal information for as long as necessary to fulfill the purposes for which it was collected and to comply with legal and regulatory requirements. Tax payment records are maintained as per government regulations.</p>

            <h2>7. Your Rights</h2>
            <p>You have the right to:</p>
            <ul>
                <li>Access your personal information</li>
                <li>Correct inaccurate information</li>
                <li>Request information about how your data is used</li>
                <li>Lodge a complaint with relevant authorities</li>
            </ul>

            <h2>8. Cookies</h2>
            <p>Our website uses cookies to enhance your browsing experience. You can choose to disable cookies through your browser settings, but this may affect certain functionality of our website.</p>

            <h2>9. Changes to This Policy</h2>
            <p>We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated revision date.</p>

            <h2>10. Contact Us</h2>
            <p>If you have any questions about this Privacy Policy, please contact us at:</p>
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
