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

            <h2>7. Legal Basis and Consent</h2>
            <p>We process your personal data in accordance with the <strong>Digital Personal Data Protection Act, 2023 (DPDP Act)</strong>. Under this Act, {{ $settings['site_name'] ?? 'Gram Panchayat' }} acts as a <strong>Data Fiduciary</strong> and you are the <strong>Data Principal</strong>.</p>
            <p>Your data is processed on the following lawful bases:</p>
            <ul>
                <li><strong>Consent</strong> — where you voluntarily provide information to register an account, submit a grievance, or subscribe to notifications. Consent is sought in clear language and you may withdraw it at any time.</li>
                <li><strong>Legitimate use for State functions</strong> — the assessment, collection and record-keeping of house tax and water tax is a statutory function of the Gram Panchayat. Processing necessary for this purpose is permitted under the Act without separate consent.</li>
            </ul>
            <p>Withdrawing consent will not affect the lawfulness of processing carried out before withdrawal, and does not remove your statutory obligation to pay taxes due, or our obligation to retain payment records as required by law.</p>

            <h2>8. Your Rights as a Data Principal</h2>
            <p>Under the DPDP Act, 2023 you have the right to:</p>
            <ul>
                <li><strong>Access</strong> — obtain a summary of the personal data we hold about you and how it is being processed</li>
                <li><strong>Correction and completion</strong> — have inaccurate or misleading data corrected, and incomplete data completed</li>
                <li><strong>Erasure</strong> — request deletion of your personal data, except where retention is required by law or for a statutory purpose such as tax records</li>
                <li><strong>Grievance redressal</strong> — raise a complaint with our Grievance Officer regarding any act or omission in our handling of your data</li>
                <li><strong>Nominate</strong> — nominate another individual to exercise these rights on your behalf in the event of your death or incapacity</li>
            </ul>
            <p>To exercise any of these rights, contact our Grievance Officer using the details in Section 11. We will respond to your request within the period prescribed under the Act.</p>

            <h2>9. Your Duties as a Data Principal</h2>
            <p>The Act also requires that you provide authentic and accurate information, and that you do not register a false or frivolous grievance or impersonate another person while providing your personal data.</p>

            <h2>10. Cookies</h2>
            <p>Our website uses cookies to enhance your browsing experience. You can choose to disable cookies through your browser settings, but this may affect certain functionality of our website.</p>

            <h2>11. Grievance Officer</h2>
            <p>In accordance with the DPDP Act, 2023, {{ $settings['site_name'] ?? 'Gram Panchayat' }} has designated a Grievance Officer to address questions and complaints regarding the handling of your personal data, and to receive requests to exercise the rights listed in Section 8.</p>
            <div class="contact-box">
                <p><strong>{{ __('messages.grievance_officer') }}</strong></p>
                <p>{{ $settings['site_name'] ?? 'Gram Panchayat' }}</p>
                <p>{{ $settings['address'] ?? '' }}</p>
                <p>Email: {{ $settings['contact_email'] ?? '' }}</p>
                <p>Phone: {{ $settings['contact_phone'] ?? '' }}</p>
            </div>
            <p>If your grievance is not resolved to your satisfaction, you may escalate the matter to the Data Protection Board of India as provided under the Act.</p>

            <h2>12. Changes to This Policy</h2>
            <p>We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated revision date.</p>

            <h2>13. Contact Us</h2>
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
