<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Beranda') - ULTEBBPJ</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="{{ asset('css/admin-layout.css') }}">
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://code.highcharts.com/modules/drilldown.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')
</head>

<body class="admin-body ">
    {{-- Diberi ID agar mudah ditarget oleh JavaScript --}}
    <div id="adminWrapper" class="admin-wrapper ">
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}">
                    <span class="logo-full">ULTEBBPJ</span>
                    <span class="logo-mini">U</span>
                </a>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('admin.dashboard') }}" title="Beranda">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h7.5" />
                            </svg>
                            <span class="nav-text">Beranda</span>
                        </a>
                    </li>
                </ul>
                <div class="nav-category">Layanan</div>
                <ul>
                    <li class="{{ request()->routeIs('admin.permohonan.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.permohonan.index') }}" title="Permohonan">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span class="nav-text">Permohonan Layanan</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin.pengaduan.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.pengaduan.index') }}" title="Pengaduan">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span class="nav-text">Pengaduan</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin.skm.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.skm.index') }}" title="skm">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span class="nav-text text-wrap">Survei Kepuasan Masyarakat</span>
                        </a>
                    </li>
                </ul>
                <div class="nav-category">Statistik</div>
                <ul>
                    <li class="{{ request()->routeIs('admin.statistik.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.statistik.index') }}" title="Statistik">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75c0 .621-.504 1.125-1.125 1.125h-2.25c-.621 0-1.125-.504-1.125-1.125v-6.75zM12 3v18m8.25-12.75a1.125 1.125 0 00-1.125-1.125H15c-.621 0-1.125.504-1.125 1.125v6.75c0 .621.504 1.125 1.125 1.125h2.25c.621 0 1.125-.504 1.125-1.125V10.5z" />
                            </svg>
                            <span class="nav-text">Statistik Layanan</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin.statistik-pengaduan.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.statistik-pengaduan.index') }}" title="Statistik Pengaduan">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75c0 .621-.504 1.125-1.125 1.125h-2.25c-.621 0-1.125-.504-1.125-1.125v-6.75zM12 3v18m8.25-12.75a1.125 1.125 0 00-1.125-1.125H15c-.621 0-1.125.504-1.125 1.125v6.75c0 .621.504 1.125 1.125 1.125h2.25c.621 0 1.125-.504 1.125-1.125V10.5z" />
                            </svg>
                            <span class="nav-text">Statistik Pengaduan</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin.statistik-skm.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.statistik-skm.index') }}" title="Statistik SKM">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75c0 .621-.504 1.125-1.125 1.125h-2.25c-.621 0-1.125-.504-1.125-1.125v-6.75zM12 3v18m8.25-12.75a1.125 1.125 0 00-1.125-1.125H15c-.621 0-1.125.504-1.125 1.125v6.75c0 .621.504 1.125 1.125 1.125h2.25c.621 0 1.125-.504 1.125-1.125V10.5z" />
                            </svg>
                            <span class="nav-text">Statistik SKM</span>
                        </a>
                    </li>
                </ul>
                <div class="nav-category">Area Administrator</div>
                <ul>
                    <li class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.users.index') }}" title="Pengguna">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.071M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.675c.312-.1.63-.195.947-.285a6.375 6.375 0 01-4.246-3.11M12 2.25c-2.35 0-4.25 1.9-4.25 4.25v.375c0 .777.313 1.5.875 2.037a4.25 4.25 0 006.75 0c.563-.537.875-1.26.875-2.037v-.375c0-2.35-1.9-4.25-4.25-4.25z" />
                            </svg>
                            <span class="nav-text">Manajemen Pengguna</span>
                        </a>
                    </li>
                </ul>
                <ul>
                    <li class="{{ request()->is('admin/forms*') ? 'active' : '' }} mt-50">
                        <a href="{{ route('admin.forms.daftar-input') }}" title="Pengguna">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings-icon lucide-settings"><path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915"/><circle cx="12" cy="12" r="3"/></svg>
                            <span class="nav-text ms-3">Pengaturan</span>
                        </a>
                    </li>
                </ul>
                
            </nav>
            <div class="sidebar-footer">
                <button class="sidebar-toggle" id="sidebarToggleBtn" title="Minimize Sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
                    </svg>
                </button>
            </div>
        </aside>

        <div class="main-content-wrapper ">
            <header class="content-header">
                <h1 class="header-title sm:!text-[20px] !text-[12px]">@yield('header-title')</h1>
                <div class="user-menu">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg></div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </header>

            <main class="content-body min-h-screen">
                @yield('content')
            </main>

            <footer class="mt-6 border-t border-gray-200 py-4 text-center text-sm text-gray-500">
                © 2025 ULTE | Balai Bahasa Provinsi Jambi
            </footer>



        </div>
    </div>

    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
            const adminWrapper = document.getElementById('adminWrapper');
            const storageKey = 'sidebar_minimized';

            if (sidebarToggleBtn && adminWrapper) {
                // Cek status dari localStorage saat halaman dimuat
                if (localStorage.getItem(storageKey) === 'true') {
                    adminWrapper.classList.add('sidebar-minimized');
                }

                sidebarToggleBtn.addEventListener('click', () => {
                    adminWrapper.classList.toggle('sidebar-minimized');
                    // Simpan status ke localStorage
                    const isMinimized = adminWrapper.classList.contains('sidebar-minimized');
                    localStorage.setItem(storageKey, isMinimized);
                });
            }
        });
    </script>
</body>

</html>