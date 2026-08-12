{{--
    Tailwind + DaisyUI admin layout (pilot).

    Loads ONLY resources/css/admin-ui.css via Vite - deliberately not
    public/css/admin.css. Tailwind's preflight resets base element styles, so
    mixing the two would give a page that is neither one thing nor the other.
    Pages still on the old stylesheet keep extending admin.layouts.app and are
    completely unaffected by this file.
--}}
@php
    $currentAdmin = $currentAdmin ?? auth('admin')->user();

    $navSections = [
        null => [
            ['route' => 'admin.dashboard', 'icon' => 'fa-gauge-high', 'label' => 'Dashboard'],
            ['route' => 'admin.analytics.index', 'icon' => 'fa-chart-line', 'label' => 'Analytics'],
            ['route' => 'admin.activity-logs.index', 'icon' => 'fa-clock-rotate-left', 'label' => 'Activity Logs'],
        ],
        'Tax Collection' => [
            ['route' => 'admin.tax-collection.index', 'icon' => 'fa-money-bill-wave', 'label' => 'Monthly Collection'],
            ['route' => 'admin.payments.index', 'icon' => 'fa-receipt', 'label' => 'Payments Panel'],
            ['route' => 'admin.water-tax.index', 'icon' => 'fa-droplet', 'label' => 'Water Tax'],
            ['route' => 'admin.property-tax.index', 'icon' => 'fa-house', 'label' => 'Property Tax'],
            ['route' => 'admin.property-assessments.index', 'icon' => 'fa-clipboard-list', 'label' => 'Property Assessment'],
            ['route' => 'admin.grievances.index', 'icon' => 'fa-bullhorn', 'label' => 'Grievances'],
        ],
        'Content Management' => [
            ['route' => 'admin.sliders.index', 'icon' => 'fa-images', 'label' => 'Sliders'],
            ['route' => 'admin.quick-links.index', 'icon' => 'fa-link', 'label' => 'Quick Links'],
            ['route' => 'admin.custom-pages.index', 'icon' => 'fa-file-lines', 'label' => 'Pages'],
        ],
        'User Management' => [
            ['route' => 'admin.roles.index', 'icon' => 'fa-user-shield', 'label' => 'Roles'],
            ['route' => 'admin.admins.index', 'icon' => 'fa-user-tie', 'label' => 'Admins'],
            ['route' => 'admin.citizens.index', 'icon' => 'fa-users', 'label' => 'Citizens'],
            ['route' => 'admin.demands.index', 'icon' => 'fa-layer-group', 'label' => 'Demands'],
        ],
        'Configuration' => [
            ['route' => 'admin.settings.index', 'icon' => 'fa-gear', 'label' => 'Settings'],
            ['route' => 'admin.penalty-settings.index', 'icon' => 'fa-triangle-exclamation', 'label' => 'Penalty Settings'],
            ['route' => 'admin.tax-rate-adjustment.index', 'icon' => 'fa-sliders', 'label' => 'Tax Rate Adjustment'],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="neralgov">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Admin Panel</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Devanagari:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite('resources/css/admin-ui.css')
</head>
<body class="bg-base-200 font-sans antialiased">
<div class="drawer lg:drawer-open">
    <input id="admin-drawer" type="checkbox" class="drawer-toggle" />

    <div class="drawer-content flex flex-col min-h-screen">
        {{-- Top bar --}}
        <header class="navbar sticky top-0 z-30 bg-base-100 border-b border-base-300 px-4 gap-2">
            <label for="admin-drawer" class="btn btn-ghost btn-square lg:hidden" aria-label="Open menu">
                <i class="fas fa-bars"></i>
            </label>

            <div class="flex-1">
                <span class="text-base font-semibold">@yield('title', 'Dashboard')</span>
            </div>

            <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-sm btn-ghost gap-2">
                <i class="fas fa-arrow-up-right-from-square"></i>
                <span class="hidden sm:inline">View Site</span>
            </a>

            <div class="dropdown dropdown-end">
                <button class="btn btn-sm btn-ghost gap-2">
                    <div class="avatar avatar-placeholder">
                        <div class="bg-primary text-primary-content w-7 rounded-full">
                            <span class="text-xs">{{ mb_substr($currentAdmin->name ?? 'A', 0, 1) }}</span>
                        </div>
                    </div>
                    <span class="hidden sm:inline">{{ $currentAdmin->name ?? 'Admin' }}</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <ul class="dropdown-content menu bg-base-100 rounded-box z-40 w-52 p-2 shadow-lg border border-base-300">
                    <li>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-error w-full text-left">
                                <i class="fas fa-right-from-bracket"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6">
            @if(session('success'))
                <div role="alert" class="alert alert-success mb-5">
                    <i class="fas fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div role="alert" class="alert alert-error mb-5">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    {{-- Sidebar --}}
    <div class="drawer-side z-40">
        <label for="admin-drawer" aria-label="Close menu" class="drawer-overlay"></label>

        <aside class="bg-primary text-primary-content min-h-full w-64 flex flex-col">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-5 h-16 border-b border-white/10 shrink-0">
                <i class="fas fa-landmark text-xl"></i>
                <span class="font-semibold leading-tight">
                    {{ \App\Models\SiteSetting::get('site_name', 'Gram Panchayat') }}
                </span>
            </a>

            <nav class="flex-1 overflow-y-auto py-3">
                <ul class="menu w-full gap-0.5 px-3">
                    @foreach($navSections as $section => $items)
                        @if($section)
                            <li class="menu-title text-primary-content/50 uppercase tracking-wider text-[11px] pt-4">
                                {{ $section }}
                            </li>
                        @endif
                        @foreach($items as $item)
                            @continue(!\Illuminate\Support\Facades\Route::has($item['route']))
                            @php $active = request()->routeIs(str_replace('.index', '', $item['route']) . '*'); @endphp
                            <li>
                                <a href="{{ route($item['route']) }}"
                                   class="{{ $active ? 'menu-active font-medium' : 'text-primary-content/80 hover:text-primary-content' }}">
                                    <i class="fas {{ $item['icon'] }} w-4 text-center"></i>
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    @endforeach
                </ul>
            </nav>

            <div class="border-t border-white/10 p-4 text-xs text-primary-content/60 shrink-0">
                {{ $currentAdmin->name ?? 'Admin' }}
                <span class="block">{{ $currentAdmin->role->name ?? '' }}</span>
            </div>
        </aside>
    </div>
</div>
@stack('scripts')
</body>
</html>
