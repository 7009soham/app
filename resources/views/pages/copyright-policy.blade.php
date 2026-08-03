@extends('layouts.app')

@section('title', __('messages.copyright_policy') . ' - ' . ($settings['site_name'] ?? 'Gram Panchayat'))

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>{{ __('messages.copyright_policy') }}</h1>
        <p>Last updated: {{ date('F d, Y') }}</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="content-card">
            <h2>1. Ownership of Material</h2>
            <p>The material featured on this website, including text, graphics, logos, emblems, documents and downloadable forms, is owned by {{ $settings['site_name'] ?? 'Gram Panchayat' }} unless otherwise stated. Copyright in such material vests with the Gram Panchayat.</p>

            <h2>2. Permission to Reproduce</h2>
            <p>Material published on this website may be reproduced free of charge in any format or medium, provided that it is reproduced accurately and not used in a misleading context. Wherever the material is being published or issued to others, the source must be prominently acknowledged as {{ $settings['site_name'] ?? 'Gram Panchayat' }}.</p>
            <p>The permission to reproduce this material does <strong>not</strong> extend to any material on this site which is identified as being the copyright of a third party. Authorisation to reproduce such material must be obtained from the copyright holder concerned.</p>

            <h2>3. Restrictions on Use</h2>
            <p>The following uses are expressly prohibited:</p>
            <ul>
                <li>Reproduction of material for commercial gain or sale</li>
                <li>Use of the Gram Panchayat name, emblem or logo in a manner that implies official endorsement</li>
                <li>Modification of content in a way that misrepresents the original meaning</li>
                <li>Use of any material for unlawful or fraudulent purposes</li>
            </ul>

            <h2>4. Government Emblems</h2>
            <p>The State Emblem of India and other official emblems displayed on this portal are protected under the State Emblem of India (Prohibition of Improper Use) Act, 2005. Any improper use is a punishable offence.</p>

            <h2>5. Third-Party Content</h2>
            <p>Where this site reproduces content sourced from other government departments or third parties, the copyright of the original owner is acknowledged and retained by them.</p>

            <h2>6. Reporting Infringement</h2>
            <p>If you believe that any content on this website infringes your copyright, please write to us with details of the material concerned and proof of your ownership. We will review and, where appropriate, remove the material promptly.</p>

            <h2>7. Contact Us</h2>
            <p>For permissions and copyright queries, please contact:</p>
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
