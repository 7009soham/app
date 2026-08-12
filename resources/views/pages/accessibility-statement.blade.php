@extends('layouts.app')

@section('title', __('messages.accessibility_statement') . ' - ' . ($settings['site_name'] ?? 'Gram Panchayat'))

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>{{ __('messages.accessibility_statement') }}</h1>
        <p>Last updated: {{ date('F d, Y') }}</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="content-card">
            <h2>1. Our Commitment</h2>
            <p>{{ $settings['site_name'] ?? 'Gram Panchayat' }} is committed to ensuring that this portal is accessible to all citizens, including persons with disabilities, in line with the Rights of Persons with Disabilities Act, 2016 and the Guidelines for Indian Government Websites (GIGW).</p>
            <p>We aim to conform to the Web Content Accessibility Guidelines (WCAG) 2.1 Level AA. Accessibility is treated as an ongoing effort, and we continue to improve the portal as we identify barriers.</p>

            <h2>2. Accessibility Features</h2>
            <p>This website includes the following features to help all users:</p>
            <ul>
                <li><strong>Multilingual content:</strong> the portal is available in English, Hindi and Marathi so citizens can use it in the language they are most comfortable with</li>
                <li><strong>Keyboard navigation:</strong> interactive elements can be reached and operated using the Tab and Enter keys without a mouse</li>
                <li><strong>Descriptive link text:</strong> links are written to make sense when read on their own by a screen reader</li>
                <li><strong>Text alternatives:</strong> meaningful images carry alternative text describing their content</li>
                <li><strong>Responsive layout:</strong> pages adapt to mobile phones, tablets and desktop screens</li>
                <li><strong>Browser zoom:</strong> text can be enlarged using your browser's zoom without loss of content</li>
            </ul>

            <h2>3. Adjusting Text Size in Your Browser</h2>
            <p>You can increase or decrease the size of text on this website using your browser:</p>
            <ul>
                <li><strong>Windows:</strong> Press <kbd>Ctrl</kbd> and <kbd>+</kbd> to enlarge, <kbd>Ctrl</kbd> and <kbd>-</kbd> to reduce</li>
                <li><strong>Mac:</strong> Press <kbd>Cmd</kbd> and <kbd>+</kbd> to enlarge, <kbd>Cmd</kbd> and <kbd>-</kbd> to reduce</li>
                <li><strong>Mobile:</strong> Use the pinch-to-zoom gesture, or increase the default font size in your device display settings</li>
            </ul>

            <h2>4. Known Limitations</h2>
            <p>We are transparent about areas where accessibility is still being improved:</p>
            <ul>
                <li>Some documents published as scanned PDFs may not be fully readable by screen readers. If you need such a document in an accessible format, please contact us and we will provide it.</li>
                <li>Certain third-party components, such as payment gateway pages, are outside our control and may not fully meet WCAG 2.1 AA.</li>
                <li>Some older uploaded content may lack complete text alternatives; these are being reviewed and corrected progressively.</li>
            </ul>

            <h2>5. Assistance for Citizens</h2>
            <p>If you are unable to access any part of this portal, or need help completing a tax payment or submitting a grievance, our office staff will assist you in person or over the telephone during working hours. No citizen will be denied a service because of difficulty in using this website.</p>

            <h2>6. Accessibility Feedback</h2>
            <p>We welcome your feedback. If you encounter an accessibility barrier on this portal, please tell us the page address, the problem you experienced, and the assistive technology or browser you were using. We will acknowledge your report and aim to respond within 15 working days.</p>
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
