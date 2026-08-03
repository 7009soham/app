{{-- Shared settings navigation. $active is the current section slug. --}}
@php
    $sections = [
        'general' => ['route' => 'admin.settings.general', 'icon' => 'fas fa-cog', 'label' => 'General Settings'],
        'social' => ['route' => 'admin.settings.social', 'icon' => 'fas fa-share-alt', 'label' => 'Social Links'],
        'firebase' => ['route' => 'admin.settings.firebase', 'icon' => 'fas fa-mobile-alt', 'label' => 'Firebase OTP'],
        'payment' => ['route' => 'admin.settings.payment', 'icon' => 'fas fa-credit-card', 'label' => 'Payment Gateway'],
        'notifications' => ['route' => 'admin.settings.notifications', 'icon' => 'fas fa-bell', 'label' => 'Notifications'],
    ];
@endphp

<div class="settings-nav card">
    <div class="card-body">
        <ul class="settings-menu">
            @foreach($sections as $slug => $section)
                <li class="{{ ($active ?? '') === $slug ? 'active' : '' }}">
                    <a href="{{ route($section['route']) }}">
                        <i class="{{ $section['icon'] }}"></i> {{ $section['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
