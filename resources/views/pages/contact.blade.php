@extends('layouts.app')

@section('title', 'Contact Us - ' . ($settings['site_name'] ?? 'Gram Panchayat'))

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>Contact Us</h1>
        <p>We're here to help you with any questions or concerns</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="contact-layout">
            <!-- Contact Information -->
            <div class="contact-info-section">
                <div class="content-card">
                    <h2>Get In Touch</h2>
                    <p>Have questions about tax payments, services, or need assistance? We're here to help!</p>
                    
                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Office Address</h3>
                                <p>{{ $settings['address'] ?? 'Gram Panchayat Office, Village Center' }}</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Phone Number</h3>
                                <p><a href="tel:{{ $settings['contact_phone'] ?? '' }}">{{ $settings['contact_phone'] ?? 'Not available' }}</a></p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Email Address</h3>
                                <p><a href="mailto:{{ $settings['contact_email'] ?? '' }}">{{ $settings['contact_email'] ?? 'Not available' }}</a></p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Office Hours</h3>
                                <p>Monday - Saturday: 10:00 AM - 5:00 PM</p>
                                <p>Sunday: Closed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="contact-form-section">
                <div class="content-card">
                    <h2>Send Us a Message</h2>
                    <p>Fill out the form below and we'll get back to you as soon as possible.</p>
                    
                    <form class="contact-form" id="contactForm" action="#" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label for="name">Full Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" required placeholder="Enter your full name">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required placeholder="Enter your email address">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" placeholder="Enter your phone number">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject <span class="required">*</span></label>
                            <select id="subject" name="subject" required>
                                <option value="">Select a subject</option>
                                <option value="tax-inquiry">Tax Payment Inquiry</option>
                                <option value="payment-issue">Payment Issue</option>
                                <option value="refund-request">Refund Request</option>
                                <option value="property-update">Property Information Update</option>
                                <option value="general">General Inquiry</option>
                                <option value="complaint">Complaint</option>
                                <option value="suggestion">Suggestion</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message <span class="required">*</span></label>
                            <textarea id="message" name="message" rows="5" required placeholder="Type your message here..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Map Section (Placeholder) -->
        <div class="content-card map-section">
            <h2>Find Us</h2>
            <div class="map-placeholder">
                <i class="fas fa-map-marked-alt"></i>
                <p>Map will be displayed here</p>
                <small>Google Maps integration can be added with your location coordinates</small>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .contact-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 32px;
        margin-bottom: 32px;
    }
    
    .contact-details {
        margin-top: 24px;
    }
    
    .contact-item {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .contact-icon {
        width: 50px;
        height: 50px;
        background: var(--color-primary);
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .contact-icon i {
        font-size: 1.25rem;
        color: var(--color-white);
    }
    
    .contact-text h3 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--color-gray-900);
        margin-bottom: 4px;
    }
    
    .contact-text p {
        font-size: 0.9rem;
        color: var(--color-gray-600);
        margin: 0;
    }
    
    .contact-text a {
        color: var(--color-primary);
    }
    
    .contact-form select {
        width: 100%;
        padding: var(--spacing-3) var(--spacing-4);
        font-size: var(--font-size-base);
        border: 1px solid var(--color-gray-300);
        border-radius: var(--radius);
        background: var(--color-white);
    }
    
    .map-placeholder {
        height: 300px;
        background: var(--color-gray-100);
        border-radius: var(--radius);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--color-gray-400);
    }
    
    .map-placeholder i {
        font-size: 4rem;
        margin-bottom: 16px;
    }
    
    @media (max-width: 768px) {
        .contact-layout {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
