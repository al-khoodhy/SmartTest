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
                                    <span>{{ Auth::user()->name }}</span>
                                    <i class="bi bi-person-circle fs-5"></i>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
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
                            @if(Auth::user()->user_role == 'dosen')
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
                            @elseif(Auth::user()->user_role == 'mahasiswa')
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
</body>
</html>
