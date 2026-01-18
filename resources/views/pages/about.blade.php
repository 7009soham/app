@extends('layouts.app')

@section('title', 'About Us - ' . ($settings['site_name'] ?? 'Gram Panchayat'))

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>About Us</h1>
        <p>Serving our community with dedication and transparency</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="content-card">
            <h2>Welcome to {{ $settings['site_name'] ?? 'Gram Panchayat' }}</h2>
            <p>{{ $settings['site_name'] ?? 'Gram Panchayat' }} is the local self-government institution serving our village community. As the grassroots level of democracy in India, we are committed to providing essential services, maintaining infrastructure, and ensuring the overall development and welfare of our citizens.</p>

            <h2>Our Mission</h2>
            <p>To ensure transparent, efficient, and citizen-centric governance for the holistic development of our village, while preserving our cultural heritage and promoting sustainable growth.</p>

            <h2>Our Vision</h2>
            <p>To transform our village into a model of self-sufficient, digitally-enabled, and environmentally sustainable community that provides a high quality of life for all its residents.</p>

            <h2>Our Core Values</h2>
            <div class="values-grid">
                <div class="value-item">
                    <i class="fas fa-balance-scale"></i>
                    <h3>Transparency</h3>
                    <p>We believe in open governance and make all our decisions and financial records accessible to citizens.</p>
                </div>
                <div class="value-item">
                    <i class="fas fa-hands-helping"></i>
                    <h3>Service</h3>
                    <p>We are dedicated to serving every citizen equally, regardless of their social or economic status.</p>
                </div>
                <div class="value-item">
                    <i class="fas fa-users"></i>
                    <h3>Participation</h3>
                    <p>We encourage active participation of all villagers in the decision-making process through Gram Sabha.</p>
                </div>
                <div class="value-item">
                    <i class="fas fa-leaf"></i>
                    <h3>Sustainability</h3>
                    <p>We promote environmentally sustainable practices for the benefit of current and future generations.</p>
                </div>
            </div>

            <h2>Our Responsibilities</h2>
            <ul>
                <li><strong>Infrastructure Development:</strong> Construction and maintenance of roads, drains, and public buildings</li>
                <li><strong>Water Supply:</strong> Ensuring clean drinking water supply to all households</li>
                <li><strong>Sanitation:</strong> Maintaining cleanliness and proper waste management</li>
                <li><strong>Street Lighting:</strong> Installation and maintenance of public lighting</li>
                <li><strong>Health & Education:</strong> Supporting local health centers and schools</li>
                <li><strong>Social Welfare:</strong> Implementing various government welfare schemes</li>
                <li><strong>Tax Collection:</strong> Collection of House Tax and Water Tax for village development</li>
            </ul>

            <h2>Digital Initiative</h2>
            <p>As part of the Digital India initiative, we have launched this online portal to make government services more accessible to our citizens. Through this portal, you can:</p>
            <ul>
                <li>Pay House Tax and Water Tax online</li>
                <li>View your payment history</li>
                <li>Download payment receipts</li>
                <li>Get updates on village development activities</li>
            </ul>

            <h2>Contact Us</h2>
            <p>We are always here to help. Feel free to reach out to us:</p>
            <div class="contact-box">
                <p><strong>{{ $settings['site_name'] ?? 'Gram Panchayat' }} Office</strong></p>
                <p><i class="fas fa-map-marker-alt"></i> {{ $settings['address'] ?? '' }}</p>
                <p><i class="fas fa-phone"></i> {{ $settings['contact_phone'] ?? '' }}</p>
                <p><i class="fas fa-envelope"></i> {{ $settings['contact_email'] ?? '' }}</p>
                <p><i class="fas fa-clock"></i> Office Hours: Monday to Saturday, 10:00 AM to 5:00 PM</p>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 24px;
        margin: 24px 0;
    }
    
    .value-item {
        text-align: center;
        padding: 24px;
        background: var(--color-gray-50);
        border-radius: var(--radius-lg);
    }
    
    .value-item i {
        font-size: 2.5rem;
        color: var(--color-primary);
        margin-bottom: 12px;
    }
    
    .value-item h3 {
        font-size: 1.1rem;
        margin-bottom: 8px;
        color: var(--color-gray-900);
    }
    
    .value-item p {
        font-size: 0.9rem;
        color: var(--color-gray-600);
    }
</style>
@endpush
