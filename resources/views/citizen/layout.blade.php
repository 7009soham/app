<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Citizen Portal') - Gram Panchayat</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #1e3a5f;
            --primary-light: #2d5a8e;
            --primary-dark: #0f1f33;
            --secondary: #f97316;
            --secondary-light: #fb923c;
            --accent: #10b981;
            --accent-light: #34d399;
            --background: #f8fafc;
            --surface: #ffffff;
            --surface-secondary: #f1f5f9;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05);
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
            --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
            --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
            --radius-sm: 6px;
            --radius: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--background);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Layout */
        .citizen-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .citizen-sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1000;
            transition: var(--transition);
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: white;
        }

        .sidebar-logo i {
            font-size: 28px;
            color: var(--secondary);
        }

        .sidebar-logo span {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .sidebar-nav {
            padding: 20px 12px;
        }

        .nav-section {
            margin-bottom: 24px;
        }

        .nav-section-title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.4);
            padding: 0 12px;
            margin-bottom: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: var(--radius);
            transition: var(--transition);
            margin-bottom: 4px;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .nav-link.active {
            background: var(--secondary);
            color: white;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 18px;
        }

        .nav-link span {
            font-size: 14px;
            font-weight: 500;
        }

        /* Main Content */
        .citizen-main {
            flex: 1;
            margin-left: 280px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Bar */
        .citizen-topbar {
            background: var(--surface);
            padding: 16px 32px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--text-primary);
            cursor: pointer;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: var(--surface-secondary);
            border-radius: var(--radius);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .user-phone {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .logout-btn:hover {
            background: #fee2e2;
            border-color: #fecaca;
            color: #dc2626;
        }

        /* Content Area */
        .citizen-content {
            flex: 1;
            padding: 32px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .citizen-sidebar {
                transform: translateX(-100%);
            }

            .citizen-sidebar.active {
                transform: translateX(0);
            }

            .citizen-main {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }

            .sidebar-overlay.active {
                display: block;
            }
        }

        @media (max-width: 640px) {
            .citizen-content {
                padding: 16px;
            }

            .citizen-topbar {
                padding: 12px 16px;
            }

            .page-title {
                font-size: 18px;
            }

            .user-info {
                display: none;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="citizen-layout">
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        <aside class="citizen-sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('citizen.dashboard') }}" class="sidebar-logo">
                    <i class="fas fa-landmark"></i>
                    <span>Gram Panchayat</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main Menu</div>
                    <a href="{{ route('citizen.dashboard') }}" class="nav-link {{ request()->routeIs('citizen.dashboard*') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Tax Records</div>
                    <a href="{{ route('citizen.water-tax') }}" class="nav-link {{ request()->routeIs('citizen.water-tax') ? 'active' : '' }}">
                        <i class="fas fa-tint"></i>
                        <span>Water Tax</span>
                    </a>
                    <a href="{{ route('citizen.property-tax') }}" class="nav-link {{ request()->routeIs('citizen.property-tax') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Property Tax</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Record History</div>
                    <a href="{{ route('citizen.transactions') }}" class="nav-link {{ request()->routeIs('citizen.transactions*') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Transactions</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <a href="{{ route('citizen.profile') }}" class="nav-link {{ request()->routeIs('citizen.profile') ? 'active' : '' }}">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                    <form action="{{ route('citizen.logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="nav-link" style="width: 100%; border: none; background: none; cursor: pointer; text-align: left;">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="citizen-main">
            <!-- Top Bar -->
            <header class="citizen-topbar">
                <div class="topbar-left">
                    <button class="mobile-menu-btn" id="mobileMenuBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="topbar-right">
                    @if(isset($citizen))
                    <div class="user-info">
                        <div class="user-avatar">
                            {{ strtoupper(substr($citizen->name ?? 'C', 0, 1)) }}
                        </div>
                        <div class="user-details">
                            <span class="user-name">{{ $citizen->name ?? 'Citizen' }}</span>
                            <span class="user-phone">{{ $citizen->phone ?? '' }}</span>
                        </div>
                    </div>
                    @endif
                    <form action="{{ route('citizen.logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Content -->
            <div class="citizen-content">
                @if(session('success'))
                <div style="padding: 16px; background: #dcfce7; border: 1px solid #86efac; border-radius: var(--radius); margin-bottom: 24px; color: #166534;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div style="padding: 16px; background: #fee2e2; border: 1px solid #fecaca; border-radius: var(--radius); margin-bottom: 24px; color: #dc2626;">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
