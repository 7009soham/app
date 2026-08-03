@extends('layouts.app')

@section('title', __('messages.hyperlinking_policy') . ' - ' . ($settings['site_name'] ?? 'Gram Panchayat'))

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>{{ __('messages.hyperlinking_policy') }}</h1>
        <p>Last updated: {{ date('F d, Y') }}</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="content-card">
            <h2>1. Links to External Websites</h2>
            <p>At many places on this portal you will find links to websites and pages maintained by other government departments, agencies and third-party organisations. These links are placed for your convenience and to provide access to further information.</p>
            <p>{{ $settings['site_name'] ?? 'Gram Panchayat' }} is <strong>not responsible</strong> for the contents, accuracy, availability or privacy practices of linked external websites, and does not necessarily endorse the views expressed in them. The mere presence of a link should not be taken as an endorsement of any kind.</p>
            <p>We cannot guarantee that external links will work at all times and we have no control over the availability of linked pages.</p>

            <h2>2. Links to Our Website From Other Websites</h2>
            <p>We do not object to you linking directly to the information hosted on this portal, and <strong>no prior permission is required</strong> for such linking.</p>

            <h2>3. Conditions for Linking to Us</h2>
            <p>If you link to this website, the following conditions apply:</p>
            <ul>
                <li>Our pages must load into the full window of the user's browser. Our content must <strong>not</strong> be loaded into frames, iframes or any other framed presentation on your site.</li>
                <li>The user must be able to clearly see that they have navigated to a Gram Panchayat website, with our URL visible in the browser address bar.</li>
                <li>You must not present our content in a way that suggests it is your own, or that implies any association, partnership or endorsement that does not exist.</li>
                <li>You must not alter, mirror or cache our content, or present it in a misleading context.</li>
                <li>Our logo, emblem or name must not be used on your site without prior written permission.</li>
            </ul>

            <h2>4. Withdrawal of Permission</h2>
            <p>We reserve the right to require the removal of any link to this website, and to withdraw the permission granted above, at our discretion and without stating reasons. On receiving such a request, you are required to remove the link promptly.</p>

            <h2>5. Reporting a Broken or Inappropriate Link</h2>
            <p>If you find a link on our portal that is broken, outdated or leads to inappropriate content, please report it to us using the contact details below so we can review and correct it.</p>
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
