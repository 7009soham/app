<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Admin Panel</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Admin CSS (loaded after Bootstrap to override) -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    
    @stack('styles')
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-logo">
                    <i class="fas fa-landmark"></i>
                    <span>Gram Panchayat</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.analytics.index') }}" class="{{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                    @if($currentAdmin->isSuperAdmin())
                    <li>
                        <a href="{{ route('admin.activity-logs.index') }}" class="{{ request()->routeIs('admin.activity-logs*') ? 'active' : '' }}">
                            <i class="fas fa-history"></i>
                            <span>Activity Logs</span>
                        </a>
                    </li>
                    @endif
                    
                    <!-- Tax Collection Section -->
                    @if($currentAdmin->isSuperAdmin() || $currentAdmin->hasPermission('water_tax.view') || $currentAdmin->hasPermission('property_tax.view'))
                    <li class="nav-section">Tax Collection</li>
                    @endif
                    
                    @if($currentAdmin->isSuperAdmin() || $currentAdmin->hasPermission('water_tax.view') || $currentAdmin->hasPermission('property_tax.view'))
                    <li>
                        <a href="{{ route('admin.tax-collection.index') }}" class="{{ request()->routeIs('admin.tax-collection*') ? 'active' : '' }}">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Monthly Collection</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments*') ? 'active' : '' }}">
                            <i class="fas fa-receipt"></i>
                            <span>Payments Panel</span>
                        </a>
                    </li>
                    @endif
                    
                    @if($currentAdmin->isSuperAdmin() || $currentAdmin->hasPermission('water_tax.view'))
                    <li>
                        <a href="{{ route('admin.water-tax.index') }}" class="{{ request()->routeIs('admin.water-tax*') ? 'active' : '' }}">
                            <i class="fas fa-tint"></i>
                            <span>Water Tax</span>
                        </a>
                    </li>
                    @endif
                    
                    @if($currentAdmin->isSuperAdmin() || $currentAdmin->hasPermission('property_tax.view'))
                    <li>
                        <a href="{{ route('admin.property-tax.index') }}" class="{{ request()->routeIs('admin.property-tax*') ? 'active' : '' }}">
                            <i class="fas fa-home"></i>
                            <span>Property Tax</span>
                        </a>
                    </li>
                    @endif
                    
                    @if($currentAdmin->isSuperAdmin() || $currentAdmin->hasPermission('property_tax.view'))
                    <li>
                        <a href="{{ route('admin.property-assessments.index') }}" class="{{ request()->routeIs('admin.property-assessments*') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Property Assessment</span>
                        </a>
                    </li>
                    @endif
                    
                    <!-- Grievances -->
                    @if($currentAdmin->isSuperAdmin() || $currentAdmin->hasPermission('grievances.view'))
                    <li>
                        <a href="{{ route('admin.grievances.index') }}" class="{{ request()->routeIs('admin.grievances*') ? 'active' : '' }}">
                            <i class="fas fa-bullhorn"></i>
                            <span>Grievances</span>
                        </a>
                    </li>
                    @endif
                    
                    <!-- Content Management -->
                    @if($currentAdmin->isSuperAdmin() || $currentAdmin->hasPermission('sliders.view') || $currentAdmin->hasPermission('quick_links.view'))
                    <li class="nav-section">Content Management</li>
                    @endif
                    
                    @if($currentAdmin->isSuperAdmin() || $currentAdmin->hasPermission('sliders.view'))
                    <li>
                        <a href="{{ route('admin.sliders.index') }}" class="{{ request()->routeIs('admin.sliders*') ? 'active' : '' }}">
                            <i class="fas fa-images"></i>
                            <span>Sliders</span>
                        </a>
                    </li>
                    @endif
                    
                    @if($currentAdmin->isSuperAdmin() || $currentAdmin->hasPermission('quick_links.view'))
                    <li>
                        <a href="{{ route('admin.quick-links.index') }}" class="{{ request()->routeIs('admin.quick-links*') ? 'active' : '' }}">
                            <i class="fas fa-link"></i>
                            <span>Quick Links</span>
                        </a>
                    </li>
                    @endif
                    
                    <!-- User Management -->
                    @if($currentAdmin->isSuperAdmin() || $currentAdmin->hasPermission('citizens.view'))
                    <li class="nav-section">User Management</li>
                    @endif
                    
                    @if($currentAdmin->isSuperAdmin())
                    <li>
                        <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles*') ? 'active' : '' }}">
                            <i class="fas fa-user-tag"></i>
                            <span>Roles</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="{{ route('admin.admins.index') }}" class="{{ request()->routeIs('admin.admins*') ? 'active' : '' }}">
                            <i class="fas fa-users-cog"></i>
                            <span>Admins</span>
                        </a>
                    </li>
                    @endif

                    @if($currentAdmin->isSuperAdmin() || $currentAdmin->hasPermission('citizens.view'))
                    <li>
                        <a href="{{ route('admin.citizens.index') }}" class="{{ request()->routeIs('admin.citizens*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i>
                            <span>Citizens</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.demands.index') }}" class="{{ request()->routeIs('admin.demands*') ? 'active' : '' }}">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Demands</span>
                        </a>
                    </li>
                    @endif
                    
                    <!-- Configuration -->
                    @if($currentAdmin->isSuperAdmin() || $currentAdmin->hasPermission('settings.view'))
                    <li class="nav-section">Configuration</li>
                    
                    <li>
                        <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                    @endif
                    
                    @if($currentAdmin->isSuperAdmin() || $currentAdmin->hasPermission('penalty.manage'))
                    <li>
                        <a href="{{ route('admin.penalty-settings.index') }}" class="{{ request()->routeIs('admin.penalty-settings*') ? 'active' : '' }}">
                            <i class="fas fa-percent"></i>
                            <span>Penalty Settings</span>
                        </a>
                    </li>
                    @endif
                    
                    @if($currentAdmin->isSuperAdmin())
                    <li>
                        <a href="{{ route('admin.tax-rate-adjustment.index') }}" class="{{ request()->routeIs('admin.tax-rate-adjustment*') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i>
                            <span>Tax Rate Adjustment</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                @if(session('original_admin_id'))
                <div class="impersonation-notice" style="background: #fef3c7; color: #92400e; padding: 10px 16px; font-size: 12px; text-align: center; margin-bottom: 10px; border-radius: 6px;">
                    <i class="fas fa-user-secret"></i> Impersonating
                    <form action="{{ route('admin.admins.stop-impersonate') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" style="background: none; border: none; color: #dc2626; cursor: pointer; font-weight: 600; text-decoration: underline;">Back</button>
                    </form>
                </div>
                @endif
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <span class="user-name">{{ $currentAdmin->name ?? 'Admin' }}</span>
                        <span class="user-role">{{ $currentAdmin->role->name ?? 'Administrator' }}</span>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            <header class="admin-header">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="header-right">
                    @php
                        $headerSwitchableAdmins = collect();
                        if ($currentAdmin && $currentAdmin->isSuperAdmin()) {
                            $headerSwitchableAdmins = \App\Models\Admin::with('role')
                                ->where('is_active', true)
                                ->where('id', '!=', $currentAdmin->id)
                                ->whereHas('role', function ($query) {
                                    $query->whereIn('type', ['admin', 'superadmin']);
                                })
                                ->orderBy('name')
                                ->get();
                        }
                    @endphp

                    <a href="{{ route('home') }}" class="btn btn-outline btn-sm" target="_blank">
                        <i class="fas fa-external-link-alt"></i> View Site
                    </a>
                    
                    <div class="dropdown">
                        <button class="dropdown-toggle" id="userDropdown">
                            <span>{{ $currentAdmin->name ?? 'Admin' }}</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu" id="userMenu">
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-user"></i> Profile
                            </a>

                            @if($currentAdmin && $currentAdmin->isSuperAdmin())
                                <div class="dropdown-divider"></div>
                                <div class="dropdown-item" style="font-size: 12px; color: #64748b; cursor: default;">
                                    <i class="fas fa-user-shield"></i> Switch Account
                                </div>

                                @foreach($headerSwitchableAdmins as $switchAdmin)
                                    <form action="{{ route('admin.admins.impersonate', $switchAdmin) }}" method="POST" class="dropdown-form">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-start">
                                            <i class="fas fa-user-secret"></i>
                                            {{ $switchAdmin->name }}
                                            <span style="font-size: 11px; color: #64748b;">({{ ucfirst($switchAdmin->role->type ?? 'admin') }})</span>
                                        </button>
                                    </form>
                                @endforeach

                                @if(session('original_admin_id'))
                                    <form action="{{ route('admin.admins.stop-impersonate') }}" method="POST" class="dropdown-form">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-warning text-start">
                                            <i class="fas fa-undo"></i> Return to Original Account
                                        </button>
                                    </form>
                                @endif
                            @endif

                            <div class="dropdown-divider"></div>
                            <form action="{{ route('admin.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="page-content">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <script>
        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        
        sidebarToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });
        
        // User dropdown
        const userDropdown = document.getElementById('userDropdown');
        const userMenu = document.getElementById('userMenu');
        
        userDropdown?.addEventListener('click', (e) => {
            e.stopPropagation();
            userMenu.classList.toggle('show');
        });
        
        document.addEventListener('click', () => {
            userMenu?.classList.remove('show');
        });
    </script>
    
    <!-- Bootstrap 5 JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
