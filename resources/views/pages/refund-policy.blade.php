@extends('layouts.app')

@section('title', 'Refund Policy - ' . ($settings['site_name'] ?? 'Gram Panchayat'))

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>Refund Policy</h1>
        <p>Last updated: {{ date('F d, Y') }}</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="content-card">
            <h2>1. Introduction</h2>
            <p>This Refund Policy outlines the conditions under which refunds may be issued for payments made through the {{ $settings['site_name'] ?? 'Gram Panchayat' }} online portal for House Tax and Water Tax.</p>

            <h2>2. General Policy</h2>
            <p>Tax payments made through our online portal are generally <strong>non-refundable</strong> as they are statutory payments to the government. However, refunds may be considered in the following exceptional circumstances:</p>

            <h2>3. Eligible Refund Scenarios</h2>
            
            <h3>3.1 Duplicate Payment</h3>
            <p>If the same tax amount has been debited multiple times for a single transaction due to technical issues, the duplicate amount will be refunded.</p>
            
            <h3>3.2 Excess Payment</h3>
            <p>If an amount greater than the actual tax due has been paid due to a system error, the excess amount will be refunded upon verification.</p>
            
            <h3>3.3 Payment for Wrong Property</h3>
            <p>If payment has been made for a property that does not belong to the payer due to incorrect information, a refund may be considered after proper verification.</p>
            
            <h3>3.4 Failed Transaction with Amount Debited</h3>
            <p>If the payment amount has been debited from your account but the transaction failed and no receipt was generated, the amount will be refunded or the transaction will be reconciled.</p>

            <h2>4. Non-Refundable Scenarios</h2>
            <p>Refunds will <strong>NOT</strong> be provided in the following cases:</p>
            <ul>
                <li>Voluntary overpayment or advance payment</li>
                <li>Payment made with incorrect details provided by the user</li>
                <li>Change of mind after successful payment</li>
                <li>Disputes regarding tax calculation (must be resolved before payment)</li>
            </ul>

            <h2>5. Refund Process</h2>
            
            <h3>5.1 How to Apply for Refund</h3>
            <ol>
                <li>Submit a written application to the {{ $settings['site_name'] ?? 'Gram Panchayat' }} office</li>
                <li>Include the following documents:
                    <ul>
                        <li>Original payment receipt or transaction ID</li>
                        <li>Bank statement showing the debit</li>
                        <li>Copy of ID proof</li>
                        <li>Property documents (if applicable)</li>
                    </ul>
                </li>
                <li>Describe the reason for refund request in detail</li>
            </ol>
            
            <h3>5.2 Processing Time</h3>
            <ul>
                <li>Refund requests will be acknowledged within 3 working days</li>
                <li>Verification process: 7-14 working days</li>
                <li>Refund processing (if approved): 7-10 working days</li>
                <li>Total time: Up to 30 working days from application</li>
            </ul>
            
            <h3>5.3 Refund Method</h3>
            <p>Approved refunds will be processed through:</p>
            <ul>
                <li>Original payment method (if possible)</li>
                <li>Bank transfer to the account from which payment was made</li>
                <li>Cheque in the name of the applicant (in exceptional cases)</li>
            </ul>

            <h2>6. Auto-Refund for Failed Transactions</h2>
            <p>In case of failed transactions where the amount has been debited:</p>
            <ul>
                <li>The payment gateway (PhonePe) automatically initiates refund within 5-7 working days</li>
                <li>If not received within 7 days, please contact us with your transaction details</li>
            </ul>

            <h2>7. Adjustment Against Future Tax</h2>
            <p>In cases of overpayment, instead of a refund, the excess amount may be adjusted against your future tax dues, subject to your consent.</p>

            <h2>8. Contact for Refund Queries</h2>
            <p>For refund-related queries, please contact:</p>
            <div class="contact-box">
                <p><strong>{{ $settings['site_name'] ?? 'Gram Panchayat' }}</strong></p>
                <p>{{ $settings['address'] ?? '' }}</p>
                <p>Email: {{ $settings['contact_email'] ?? '' }}</p>
                <p>Phone: {{ $settings['contact_phone'] ?? '' }}</p>
                <p><em>Office Hours: Monday to Saturday, 10:00 AM to 5:00 PM</em></p>
            </div>

            <div class="notice-box">
                <h3><i class="fas fa-exclamation-triangle"></i> Important Note</h3>
                <p>Please verify all payment details before making a transaction. Ensure that the property details, tax type, and amount are correct. Once payment is made, it may not be possible to get a refund except in the circumstances mentioned above.</p>
            </div>
        </div>
    </div>
</section>
@endsection
