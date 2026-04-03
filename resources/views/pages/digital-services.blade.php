@extends('layouts.app')

@section('title', 'Digital Services - ' . ($settings['site_name'] ?? 'Gram Panchayat'))

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>Digital Services</h1>
        <p>Access government services online, anytime and from anywhere</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="content-card">
            <h2>Our Digital Services</h2>
            <p>As part of the Digital India initiative, {{ $settings['site_name'] ?? 'Gram Panchayat' }} offers a range of online services to make it easier for citizens to access government services without visiting the office.</p>

            <div class="ds-grid">
                <div class="ds-card">
                    <div class="ds-icon" style="background: linear-gradient(135deg, #10b981, #34d399);">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3>Property Tax</h3>
                    <p>View your property tax records, check outstanding balances, and pay your annual property tax conveniently online.</p>
                    <a href="{{ auth('citizen')->check() ? route('citizen.property-tax') : route('citizen.login') }}" class="btn btn-primary">View Property Tax</a>
                </div>

                <div class="ds-card">
                    <div class="ds-icon" style="background: linear-gradient(135deg, #3b82f6, #60a5fa);">
                        <i class="fas fa-tint"></i>
                    </div>
                    <h3>Water Tax</h3>
                    <p>View your water supply charges, check monthly bills, and pay your water tax online using UPI, Net Banking, or Cards.</p>
                    <a href="{{ auth('citizen')->check() ? route('citizen.water-tax') : route('citizen.login') }}" class="btn btn-primary">View Water Tax</a>
                </div>

                <div class="ds-card">
                    <div class="ds-icon" style="background: linear-gradient(135deg, #ef4444, #f87171);">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h3>Grievance Redressal</h3>
                    <p>Report issues such as water leakage, road damage, or other civic problems online. Track the status of your complaints.</p>
                    <a href="{{ route('grievance.create') }}" class="btn btn-primary">Report an Issue</a>
                </div>
            </div>

            <h2>How to Get Started</h2>
            <div class="steps-list">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Login to the Citizen Portal</h3>
                        <p>Use your registered mobile number to log in via OTP verification. No password needed.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>View Your Tax Records</h3>
                        <p>Your Water Tax and Property Tax records are automatically linked to your account.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Make Secure Payments</h3>
                        <p>Pay your dues online securely. Download your receipt immediately after payment.</p>
                    </div>
                </div>
            </div>

            <div class="ds-cta">
                <h2>Need Assistance?</h2>
                <p>If you face any difficulty using our digital services, please contact our office.</p>
                <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-top: 16px;">
                    <a href="{{ route('contact') }}" class="btn btn-primary">
                        <i class="fas fa-envelope"></i> Contact Us
                    </a>
                    <a href="{{ route('about') }}" class="btn btn-outline">
                        <i class="fas fa-info-circle"></i> About Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .ds-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 24px;
        margin: 32px 0;
    }

    .ds-card {
        background: var(--color-gray-50, #f8fafc);
        border-radius: var(--radius-lg, 16px);
        padding: 28px 24px;
        border: 1px solid var(--color-gray-200, #e2e8f0);
        display: flex;
        flex-direction: column;
        gap: 12px;
        transition: box-shadow 0.25s ease, transform 0.25s ease;
    }

    .ds-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        transform: translateY(-4px);
    }

    .ds-icon {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #1e3a5f, #2d5a8e);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ds-icon i {
        font-size: 22px;
        color: white;
    }

    .ds-card h3 {
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--color-gray-900, #1e293b);
        margin: 0;
    }

    .ds-card p {
        font-size: 0.9rem;
        color: var(--color-gray-600, #64748b);
        flex: 1;
        margin: 0;
    }

    .ds-card .btn {
        align-self: flex-start;
        margin-top: 8px;
        font-size: 14px;
        padding: 8px 18px;
    }

    /* Steps */
    .steps-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin: 24px 0 32px;
    }

    .step-item {
        display: flex;
        gap: 20px;
        align-items: flex-start;
    }

    .step-number {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .step-content h3 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--color-gray-900, #1e293b);
        margin-bottom: 4px;
    }

    .step-content p {
        font-size: 0.9rem;
        color: var(--color-gray-600, #64748b);
        margin: 0;
    }

    /* CTA */
    .ds-cta {
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 100%);
        border-radius: var(--radius-lg, 16px);
        padding: 36px 32px;
        color: white;
        margin-top: 32px;
    }

    .ds-cta h2 {
        color: white;
        margin-bottom: 8px;
    }

    .ds-cta p {
        color: rgba(255,255,255,0.85);
    }

    @media (max-width: 640px) {
        .ds-grid {
            grid-template-columns: 1fr;
        }

        .ds-cta {
            padding: 24px 20px;
        }
    }
</style>
@endpush
