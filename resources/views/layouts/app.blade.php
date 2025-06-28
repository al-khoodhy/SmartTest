<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* Sidebar styles */
        #sidebar {
            min-width: 220px;
            max-width: 220px;
            min-height: 100vh;
            background: #fff;
            box-shadow: 2px 0 8px rgba(0,0,0,0.06);
            border-right: 1px solid #e5e5e5;
            transition: margin-left 0.3s, box-shadow 0.3s;
            z-index: 1040;
        }
        #sidebar.collapsed {
            margin-left: -220px;
            box-shadow: none;
        }
        #sidebar .sidebar-header {
            font-size: 1.2rem;
            letter-spacing: 1px;
            background: #f8f9fa;
        }
        #sidebar ul.nav {
            margin-top: 1rem;
        }
        #sidebar ul.nav .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #333;
            border-radius: 6px;
            margin-bottom: 0.25rem;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }
        #sidebar ul.nav .nav-link.active, #sidebar ul.nav .nav-link:active {
            background: #e9ecef;
            color: #0d6efd;
            font-weight: 500;
        }
        #sidebar ul.nav .nav-link:hover {
            background: #f1f3f5;
            color: #0d6efd;
        }
        #sidebar ul.nav .nav-link:after {
            content: '';
            position: absolute;
            left: 50%;
            top: 50%;
            width: 0;
            height: 0;
            background: rgba(13,110,253,0.15);
            border-radius: 100%;
            transform: translate(-50%, -50%);
            transition: width 0.3s, height 0.3s;
            z-index: 0;
        }
        #sidebar ul.nav .nav-link:active:after {
            width: 200%;
            height: 500%;
        }
        /* Toggle button: always show */
        #sidebar-wrapper {
            position: relative;
        }
        #sidebar-toggle {
            position: absolute;
            top: 1rem;
            right: -18px;
            left: auto;
            z-index: 1100;
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: right 0.3s, left 0.3s;
        }
        #sidebar.collapsed + #sidebar-toggle {
            right: calc(100% - 28px);
            left: auto;
        }
        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                height: 100%;
                left: 0;
                top: 0;
            }
            #sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.3);
                z-index: 1039;
            }
            #sidebar.show + #sidebar-backdrop {
                display: block;
            }
            .sidebar-expanded main.flex-grow-1 {
                margin-left: 0;
            }
            #sidebar-toggle {
                top: 1rem;
                right: -28px;
            }
            #sidebar.collapsed + #sidebar-toggle {
                right: calc(100% - 28px);
                left: auto;
            }
        }
        .pagination {
            justify-content: center;
            flex-wrap: wrap;
        }
        .pagination .page-item .page-link {
            border-radius: 6px;
            margin: 0 2px;
            min-width: 36px;
            text-align: center;
        }
        
        /* Navbar dropdown styling */
        .dropdown-header {
            padding: 0.75rem 1rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        
        .dropdown-item {
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
            transform: translateX(2px);
        }
        
        .dropdown-item.text-danger:hover {
            background-color: #fef2f2;
            color: #dc2626 !important;
        }
        
        .dropdown-divider {
            margin: 0.5rem 0;
        }
        
        /* Navbar brand styling */
        .navbar-brand {
            font-weight: 600;
            color: #0d6efd !important;
        }
        
        /* User info in navbar */
        .navbar-nav .nav-link .fw-semibold {
            font-size: 0.9rem;
        }
        
        .navbar-nav .nav-link small {
            font-size: 0.75rem;
        }
        
        /* Notification dropdown styling */
        .dropdown-item.d-flex.align-items-start {
            border-bottom: 1px solid #f1f3f4;
        }
        
        .dropdown-item.d-flex.align-items-start:last-child {
            border-bottom: none;
        }
        
        .dropdown-item.d-flex.align-items-start:hover {
            background-color: #f8f9fa;
        }
        
        .bg-primary.rounded-circle {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .bg-success.rounded-circle {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Badge positioning */
        .position-relative .badge {
            transform: translate(50%, -50%);
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .navbar-nav .nav-link {
                padding: 0.5rem 0.75rem;
            }
            
            .btn-sm .d-none.d-md-inline {
                display: none !important;
            }
            
            .dropdown-menu {
                margin-top: 0.5rem;
            }
            
            /* Ensure dropdowns are clickable on mobile */
            .dropdown-menu {
                z-index: 1050;
            }
            
            /* Make sure navbar elements are clickable */
            .navbar-nav .nav-link,
            .navbar-nav .dropdown-toggle {
                cursor: pointer;
                pointer-events: auto;
            }
        }
        
        /* Ensure dropdowns work properly */
        .dropdown-menu {
            z-index: 1050;
            pointer-events: auto;
        }
        
        .dropdown-toggle {
            cursor: pointer;
        }
        
        /* Fix for navbar click issues */
        .navbar-nav .nav-link {
            pointer-events: auto;
            cursor: pointer;
        }
        
        .navbar-nav .dropdown-item {
            pointer-events: auto;
            cursor: pointer;
        }
        
        /* Ensure navbar is properly positioned */
        .navbar {
            position: relative;
            z-index: 1030;
        }
        
        .navbar-collapse {
            z-index: 1031;
        }
        
        /* Fix for mobile navbar */
        @media (max-width: 768px) {
            .navbar-collapse {
                background: white;
                border-top: 1px solid #e9ecef;
                margin-top: 0.5rem;
                padding-top: 0.5rem;
            }
            
            .navbar-nav .nav-item {
                margin-bottom: 0.25rem;
            }
            
            .navbar-nav .nav-link {
                padding: 0.75rem 1rem;
                border-radius: 0.375rem;
            }
            
            .navbar-nav .nav-link:hover {
                background-color: #f8f9fa;
            }
        }
        
        /* Ensure dropdown shows when show class is present */
        .dropdown-menu.show {
            display: block !important;
        }
        
        /* Additional dropdown styling */
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            left: auto;
            margin-top: 0.125rem;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        /* Ensure dropdown positioning */
        .dropdown {
            position: relative;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            left: auto;
            z-index: 1050;
            float: left;
            min-width: 10rem;
            padding: 0.5rem 0;
            margin: 0.125rem 0 0;
            font-size: 1rem;
            color: #212529;
            text-align: left;
            list-style: none;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        /* Ensure dropdown items are clickable */
        .dropdown-item {
            display: block;
            width: 100%;
            padding: 0.5rem 1rem;
            clear: both;
            font-weight: 400;
            color: #212529;
            text-align: inherit;
            text-decoration: none;
            white-space: nowrap;
            background-color: transparent;
            border: 0;
            cursor: pointer;
        }
        
        .dropdown-item:hover {
            color: #1e2125;
            background-color: #e9ecef;
        }
        
        /* Ensure dropdown toggle is clickable */
        .dropdown-toggle {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                    <i class="bi bi-mortarboard-fill fs-4 text-primary"></i>
                    <span>{{ config('app.name', 'Laravel') }}</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        @auth
                            @if(Auth::user()->role_id == 2)
                                <!-- Quick Action Button for Dosen -->
                                <li class="nav-item me-2">
                                    <a class="btn btn-primary btn-sm d-flex align-items-center gap-1" href="{{ route('dosen.tugas.create') }}" title="Buat Tugas Baru">
                                        <i class="bi bi-plus-lg"></i>
                                        <span class="d-none d-md-inline">Tugas Baru</span>
                                    </a>
                                </li>
                                
                                <!-- Notification Bell for Dosen -->
                                <li class="nav-item dropdown me-2">
                                    <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-bell fs-5"></i>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                            3
                                        </span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 300px; z-index: 1050;">
                                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold">Notifikasi</span>
                                            <a href="#" class="text-decoration-none small">Tandai semua dibaca</a>
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item d-flex align-items-start gap-2 p-3" href="#">
                                            <div class="bg-primary rounded-circle p-1">
                                                <i class="bi bi-journal-text text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold small">Tugas baru dikumpulkan</div>
                                                <div class="text-muted small">Mahasiswa mengumpulkan tugas "Pemrograman Web"</div>
                                                <div class="text-muted small">2 menit yang lalu</div>
                                            </div>
                                        </a>
                                        <a class="dropdown-item d-flex align-items-start gap-2 p-3" href="#">
                                            <div class="bg-success rounded-circle p-1">
                                                <i class="bi bi-check-circle text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold small">Auto grading selesai</div>
                                                <div class="text-muted small">15 jawaban berhasil dinilai otomatis</div>
                                                <div class="text-muted small">1 jam yang lalu</div>
                                            </div>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-center text-primary" href="#">
                                            Lihat semua notifikasi
                                        </a>
                                    </div>
                                </li>
                            @endif
                        @endauth
                        
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <div class="d-flex flex-column align-items-start d-none d-md-flex">
                                        <span class="fw-semibold">{{ Auth::user()->name }}</span>
                                        <small class="text-muted">
                                            @if(Auth::user()->role_id == 1)
                                                Administrator
                                            @elseif(Auth::user()->role_id == 2)
                                                Dosen
                                            @elseif(Auth::user()->role_id == 3)
                                                Mahasiswa
                                            @endif
                                        </small>
                                    </div>
                                    <span class="d-md-none fw-semibold">{{ Auth::user()->name }}</span>
                                    <i class="bi bi-person-circle fs-4 text-primary"></i>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="navbarDropdown" style="min-width: 200px; z-index: 1050;">
                                    <div class="dropdown-header">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-person-circle fs-4 text-primary"></i>
                                            <div>
                                                <div class="fw-semibold">{{ Auth::user()->name }}</div>
                                                <small class="text-muted">{{ Auth::user()->email }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    
                                    @if(Auth::user()->role_id == 2)
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('dosen.dashboard') }}">
                                            <i class="bi bi-speedometer2"></i>
                                            <span>Dashboard</span>
                                        </a>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('dosen.tugas.index') }}">
                                            <i class="bi bi-journal-text"></i>
                                            <span>Kelola Tugas</span>
                                        </a>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('dosen.penilaian.index') }}">
                                            <i class="bi bi-clipboard-check"></i>
                                            <span>Penilaian</span>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="#">
                                            <i class="bi bi-person-gear"></i>
                                            <span>Profil</span>
                                        </a>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="#">
                                            <i class="bi bi-gear"></i>
                                            <span>Pengaturan</span>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                    @elseif(Auth::user()->role_id == 3)
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('mahasiswa.dashboard') }}">
                                            <i class="bi bi-speedometer2"></i>
                                            <span>Dashboard</span>
                                        </a>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('mahasiswa.tugas.index') }}">
                                            <i class="bi bi-journal-text"></i>
                                            <span>Tugas</span>
                                        </a>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('mahasiswa.nilai.index') }}">
                                            <i class="bi bi-bar-chart"></i>
                                            <span>Nilai</span>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                    @endif
                                    
                                    <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="#" onclick="logout()">
                                        <i class="bi bi-box-arrow-right"></i>
                                        <span>{{ __('Logout') }}</span>
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                            
                            <!-- Mobile-friendly logout button -->
                            <li class="nav-item d-md-none">
                                <a class="nav-link text-danger" href="#" onclick="logout()">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Logout</span>
                                </a>
                                <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <div class="d-flex" id="sidebar-container">
            @auth
                <!-- Sidebar & Backdrop -->
                <div id="sidebar-wrapper">
                    <div id="sidebar" class="d-md-block">
                        <div class="sidebar-header p-3 border-bottom fw-bold">Menu</div>
                        <ul class="nav flex-column p-2">
                            @if(Auth::user()->role_id == 2)
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('dosen.dashboard') ? 'active' : '' }}" href="{{ route('dosen.dashboard') }}">
                                        <i class="bi bi-speedometer2"></i> Dashboard Dosen
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('dosen.tugas.*') ? 'active' : '' }}" href="{{ route('dosen.tugas.index') }}">
                                        <i class="bi bi-journal-text"></i> Tugas
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('dosen.penilaian.*') ? 'active' : '' }}" href="{{ route('dosen.penilaian.index') }}">
                                        <i class="bi bi-clipboard-check"></i> Penilaian
                                    </a>
                                </li>
                            @elseif(Auth::user()->role_id == 3)
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('mahasiswa.dashboard') ? 'active' : '' }}" href="{{ route('mahasiswa.dashboard') }}">
                                        <i class="bi bi-speedometer"></i> Dashboard Mahasiswa
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('mahasiswa.tugas.*') ? 'active' : '' }}" href="{{ route('mahasiswa.tugas.index') }}">
                                        <i class="bi bi-journal-text"></i> Tugas
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('mahasiswa.nilai.*') ? 'active' : '' }}" href="{{ route('mahasiswa.nilai.index') }}">
                                        <i class="bi bi-bar-chart"></i> Nilai
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('mahasiswa.ujian.*') ? 'active' : '' }}" href="{{ route('mahasiswa.ujian.index') }}">
                                        <i class="bi bi-pencil-square"></i> Ujian
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <button id="sidebar-toggle" class="btn btn-outline-secondary" type="button" onclick="toggleSidebar()">
                        <i id="sidebar-toggle-icon" class="bi bi-list" style="font-size:1.5rem;"></i>
                    </button>
                </div>
                <div id="sidebar-backdrop" onclick="toggleSidebar(false)"></div>
            @endauth

            <!-- Main Content -->
            <main class="py-4 flex-grow-1">
                @yield('content')
            </main>
        </div>
    </div>
    <script>
        // Robust dropdown implementation
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing dropdowns...');
            
            // Initialize all dropdowns
            initializeDropdowns();
            
            // Handle logout clicks
            document.addEventListener('click', function(e) {
                if (e.target.closest('.dropdown-item') && e.target.closest('.dropdown-item').classList.contains('text-danger')) {
                    e.preventDefault();
                    logout();
                }
            });
        });
        
        function initializeDropdowns() {
            // Get all dropdown toggles
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            
            dropdownToggles.forEach(function(toggle) {
                // Remove any existing event listeners
                const newToggle = toggle.cloneNode(true);
                toggle.parentNode.replaceChild(newToggle, toggle);
                
                // Add click event listener
                newToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const dropdown = this.closest('.dropdown');
                    const menu = dropdown.querySelector('.dropdown-menu');
                    
                    // Close all other dropdowns
                    document.querySelectorAll('.dropdown-menu.show').forEach(function(openMenu) {
                        if (openMenu !== menu) {
                            openMenu.classList.remove('show');
                            openMenu.previousElementSibling.setAttribute('aria-expanded', 'false');
                        }
                    });
                    
                    // Toggle current dropdown
                    menu.classList.toggle('show');
                    this.setAttribute('aria-expanded', menu.classList.contains('show'));
                    
                    console.log('Dropdown toggled:', menu.classList.contains('show'));
                });
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                        menu.classList.remove('show');
                        const toggle = menu.previousElementSibling;
                        if (toggle) {
                            toggle.setAttribute('aria-expanded', 'false');
                        }
                    });
                }
            });
            
            // Close dropdowns on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                        menu.classList.remove('show');
                        const toggle = menu.previousElementSibling;
                        if (toggle) {
                            toggle.setAttribute('aria-expanded', 'false');
                        }
                    });
                }
            });
        }
        
        function logout() {
            console.log('Logout function called');
            const form = document.getElementById('logout-form') || document.getElementById('logout-form-mobile');
            if (form) {
                form.submit();
            } else {
                window.location.href = '{{ route("logout") }}';
            }
        }
        
        // Test function to verify dropdown is working
        function testDropdown() {
            console.log('Testing dropdown...');
            const dropdown = document.getElementById('navbarDropdown');
            const menu = dropdown ? dropdown.nextElementSibling : null;
            
            if (dropdown && menu) {
                console.log('Dropdown found:', dropdown);
                console.log('Menu found:', menu);
                console.log('Menu classes:', menu.className);
                
                // Manually show dropdown for testing
                menu.classList.add('show');
                dropdown.setAttribute('aria-expanded', 'true');
                console.log('Dropdown manually shown for testing');
            } else {
                console.log('Dropdown or menu not found');
            }
        }
        
        // Call test function after a delay
        setTimeout(testDropdown, 3000);
        
        function toggleSidebar(force) {
            var sidebar = document.getElementById('sidebar');
            var backdrop = document.getElementById('sidebar-backdrop');
            var icon = document.getElementById('sidebar-toggle-icon');
            var container = document.getElementById('sidebar-container');
            var isCollapsed = sidebar.classList.contains('collapsed');
            if (force === undefined) {
                sidebar.classList.toggle('collapsed');
            } else if (force === false) {
                sidebar.classList.add('collapsed');
            } else {
                sidebar.classList.remove('collapsed');
            }
            // Toggle class for main content margin
            if (sidebar.classList.contains('collapsed')) {
                container.classList.add('sidebar-collapsed');
                container.classList.remove('sidebar-expanded');
                icon.className = 'bi bi-list';
            } else {
                container.classList.remove('sidebar-collapsed');
                container.classList.add('sidebar-expanded');
                icon.className = 'bi bi-x-lg';
            }
            // Backdrop hanya untuk mobile
            if (window.innerWidth < 768) {
                backdrop.style.display = sidebar.classList.contains('collapsed') ? 'none' : 'block';
            } else {
                backdrop.style.display = 'none';
            }
        }
        // Show sidebar by default on desktop
        document.addEventListener('DOMContentLoaded', function() {
            var sidebar = document.getElementById('sidebar');
            var icon = document.getElementById('sidebar-toggle-icon');
            var container = document.getElementById('sidebar-container');
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('collapsed');
                container.classList.add('sidebar-expanded');
                icon.className = 'bi bi-x-lg';
            } else {
                sidebar.classList.add('collapsed');
                container.classList.add('sidebar-collapsed');
                icon.className = 'bi bi-list';
            }
        });
        window.addEventListener('resize', function() {
            var sidebar = document.getElementById('sidebar');
            var icon = document.getElementById('sidebar-toggle-icon');
            var container = document.getElementById('sidebar-container');
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('collapsed');
                container.classList.add('sidebar-expanded');
                container.classList.remove('sidebar-collapsed');
                icon.className = 'bi bi-x-lg';
                document.getElementById('sidebar-backdrop').style.display = 'none';
            } else {
                sidebar.classList.add('collapsed');
                container.classList.add('sidebar-collapsed');
                container.classList.remove('sidebar-expanded');
                icon.className = 'bi bi-list';
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
