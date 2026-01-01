<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ULTEBBPJ') - Balai Bahasa Provinsi Jambi</title>

    <link rel="stylesheet" href="{{ asset('css/app-layout.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    @push('styles')
        {{-- CSS UNTUK DROPDOWN MENU --}}
        <style>
            /* BARU: Menambahkan Flexbox untuk mensejajarkan menu utama */
            .main-nav ul {
                display: flex;
                align-items: center;
                list-style: none;
                margin: 0;
                padding: 0;
            }

            /* Styling untuk dropdown container */
            .main-nav .dropdown {
                position: relative;
            }

            /* Styling untuk link utama dropdown */
            .main-nav .dropdown .dropdown-toggle {
                display: flex;
                align-items: center;
                cursor: pointer;
                text-decoration: none;
                color: inherit;
            }

            /* Submenu dropdown (disembunyikan secara default) */
            .main-nav .dropdown .dropdown-menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                background-color: white;
                min-width: 250px;
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
                border-radius: 8px;
                z-index: 1000;
                list-style: none;
                padding: 0.5rem 0;
                margin-top: 10px;
                border: 1px solid #f0f0f0;
            }

            /* Tampilkan submenu saat parent memiliki kelas 'is-active' (dikontrol oleh JS) */
            .main-nav .dropdown.is-active>.dropdown-menu {
                display: block;
            }

            /* Styling untuk item di dalam dropdown */
            .main-nav .dropdown-menu li a {
                padding: 0.75rem 1.5rem;
                display: block;
                color: #333;
                white-space: nowrap;
                transition: background-color 0.2s;
            }

            /* Efek hover pada item dropdown */
            .main-nav .dropdown-menu li a:hover {
                background-color: #f5f5f5;
                color: #0056b3;
            }

            /* ================================================= */
            /* ## CSS BARU UNTUK NESTED DROPDOWN (SUDAH DIPERBAIKI) ## */
            /* ================================================= */

            .main-nav .nested-dropdown>.dropdown-toggle {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            /* PERBAIKAN: Selector lebih spesifik untuk menimpa gaya dasar */
            .main-nav .nested-dropdown>.nested-menu {
                top: -0.5rem;
                /* Atur posisi vertikal agar sejajar */
                left: 100%;
                /* Posisikan di sebelah kanan item induk */
                margin-top: 0;
                /* Reset margin-top */
                margin-left: 1px;
                /* Jarak kecil antar menu */
            }

            /* Menampilkan menu nested saat parent-nya aktif */
            .main-nav .nested-dropdown.is-active>.nested-menu {
                display: block;
            }

            @media (max-width: 768px) {

                /* UL utama (bukan submenu) */
                .main-nav.is-active>ul {
                    flex-direction: column;
                    align-items: center;
                }

                .main-nav.is-active>ul>li {
                    width: 100%;
                    align-self: stretch;
                }

                /* HANYA link di menu utama yang center */
                .main-nav.is-active>ul>li>a {
                    display: block;
                    width: 100%;
                    text-align: center;
                }

                /* Toggle dropdown utama (Layanan & Pengaduan) */
                #permohonanDropdown>a.dropdown-toggle,
                #pengaduanDropdown>a.dropdown-toggle {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    width: 100%;
                    position: relative;
                }

                /* Submenu full */
                .main-nav .dropdown .dropdown-menu {
                    position: static;
                    width: 100%;
                    box-sizing: border-box;
                    padding-left: 0;
                    margin-top: 0;
                }

                /* Semua item submenu rata kiri + padding rapi */
                .main-nav .dropdown-menu li>a {
                    text-align: left;
                    padding: 0.75rem 1.5rem;
                    white-space: normal;
                }

                /* KHUSUS: “Standar Pelayanan” (nested dropdown toggle) */
                .main-nav .dropdown-menu li.nested-dropdown>a.dropdown-toggle {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    width: 100%;
                    padding: 0.75rem 1.5rem;
                    /* ini yang bikin nggak mentok kiri */
                    text-align: left;
                }

                /* Nested menu indent */
                .main-nav .nested-dropdown>.nested-menu {
                    width: 100%;
                    padding-left: 1rem;
                }
            }
        </style>
    @endpush
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    @stack('styles')
</head>

<body>
    <header class="main-header">
        <div class="header-container">
            <div class="logo">
                <a href="{{ route('beranda') }}">
                    <img src="https://balaibahasajambi.kemendikdasmen.go.id/wp-content/uploads/2024/12/LOGO-BALAI-1-e1734681386289.png"
                        alt="Logo BBP Jambi"
                        onerror="this.onerror=null;this.src='https://placehold.co/200x50/0056b3/FFFFFF?text=Logo+BBPJ';">
                    <span>

                    </span>
                </a>
            </div>

            <nav class="main-nav" id="mainNav">
                <ul>
                    <li><a href="{{ route('beranda') }}">Beranda</a></li>

                    <li class="dropdown" id="permohonanDropdown">
                        <a class="dropdown-toggle">Layanan</a>
                        <ul class="dropdown-menu">
                            <li><a href="{{ route('permohonan.create') }}">Permohonan Layanan</a></li>

                            {{-- ## STRUKTUR DROPDOWN BERSARANG YANG SUDAH DIMODIFIKASI ## --}}
                            <li class="dropdown nested-dropdown">
                                <a class="dropdown-toggle">Standar Pelayanan</a>
                                <ul class="dropdown-menu nested-menu">
                                    <li><a href="/standar-pelayanan/penerjemahan">Penerjemahan</a></li>
                                    <li><a href="/standar-pelayanan/perpustakaan">Perpustakaan</a></li>
                                    <li><a href="/standar-pelayanan/fasilitas-bantuan-teknis">Fasilitas Bantuan
                                            Teknis</a></li>
                                    <li><a href="/standar-pelayanan/praktik-kerja-lapangan">Praktik Kerja Lapangan</a>
                                    </li>
                                    <li><a href="/standar-pelayanan/pesapra">Peminjaman Sarana dan Prasarana</a></li>
                                    <li><a href="/standar-pelayanan/kunjungan-edukasi">Kunjungan Edukasi</a></li>
                                </ul>
                            </li>

                            <li><a href="{{ route('skm.create') }}">Survei Kepuasan Masyarakat</a></li>
                        </ul>
                    </li>

                    <li><a href="{{ route('status.index') }}">Lacak Layanan</a></li>
                    <li><a href="{{ route('pengumuman') }}">Pengumuman</a></li>

                    <li class="dropdown" id="pengaduanDropdown">
                        <a class="dropdown-toggle">Pengaduan</a>
                        <ul class="dropdown-menu">
                            <li><a href="https://www.lapor.go.id/" target="_blank" rel="noopener noreferrer">SP4N
                                    Lapor</a></li>
                            <li><a href="{{ route('pengaduan.create') }}">Pengaduan Layanan</a></li>
                        </ul>
                    </li>

                    <li><a href="{{ route('hubungi-kami') }}">Hubungi Kami</a></li>
                </ul>
            </nav>

            <button class="hamburger-menu" id="hamburgerBtn" aria-label="Toggle Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <main class="main-content">
        @yield('content')
    </main>

    <footer class="bg-slate-900 text-slate-100 mt-10">
        <div class="max-w-6xl mx-auto px-6 py-10">
            <div class="grid gap-8 md:grid-cols-3">
                {{-- Navigasi --}}
                <div>
                    <h3 class="text-lg font-semibold mb-4">Navigasi</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('beranda') }}" class="hover:underline">Beranda</a></li>
                        <li><a href="{{ route('permohonan.create') }}" class="hover:underline">Permohonan Layanan</a>
                        </li>
                        <li><a href="{{ route('status.index') }}" class="hover:underline">Lacak Layanan</a></li>
                        <li><a href="{{ route('pengumuman') }}" class="hover:underline">Pengumuman</a></li>
                        <li><a href="{{ route('pengaduan.create') }}" class="hover:underline">Pengaduan Layanan</a></li>
                        <li><a href="{{ route('hubungi-kami') }}" class="hover:underline">Hubungi Kami</a></li>
                        <li><a href="https://www.lapor.go.id/" target="_blank" rel="noopener noreferrer"
                                class="hover:underline">SP4N Lapor</a></li>
                    </ul>
                </div>

                {{-- Kontak --}}
                <div>
                    <h3 class="text-lg font-semibold mb-4">Kontak</h3>
                    <div class="text-sm space-y-3 text-slate-200">
                        <p>
                            Jalan Arif Rahman Hakim No. 101, Telanaipura, Jambi, Indonesia, 36124
                        </p>
                        <p>
                            Email:
                            <a href="mailto:bahasajambi@kemdikbud.go.id" class="hover:underline">
                                bahasajambi@kemdikbud.go.id
                            </a>
                        </p>
                        <p>
                            Telepon:
                            <a href="tel:+62741669466" class="hover:underline">
                                (0741) 669466
                            </a>
                        </p>
                    </div>
                </div>

                {{-- Info / Brand --}}
                <div>
                    <h3 class="text-lg font-semibold mb-4">Balai Bahasa Provinsi Jambi</h3>
                    <p class="text-sm text-slate-200 leading-relaxed">
                        Layanan ULTE untuk mendukung informasi layanan, permohonan, pengaduan, dan publikasi Balai
                        Bahasa Provinsi Jambi.
                    </p>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-700">
            <div class="max-w-6xl mx-auto px-6 py-4 text-center text-xs text-slate-300">
                © 2025 ULTE | Balai Bahasa Provinsi Jambi
            </div>
        </div>
    </footer>


    @stack('scripts')
    <script>
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const mainNav = document.getElementById('mainNav');
        hamburgerBtn.addEventListener('click', () => {
            mainNav.classList.toggle('is-active');
        });

        document.addEventListener('click', function (event) {
            const isDropdownToggle = event.target.matches('.dropdown-toggle');
            const activeDropdown = document.querySelector('.dropdown.is-active');
            if (!isDropdownToggle && activeDropdown) {
                document.querySelectorAll('.dropdown.is-active').forEach(d => d.classList.remove('is-active'));
                return;
            }

            if (isDropdownToggle) {
                const currentDropdown = event.target.closest('.dropdown');

                currentDropdown.classList.toggle('is-active');

                document.querySelectorAll('.dropdown.is-active').forEach(dropdown => {
                    if (dropdown !== currentDropdown && !dropdown.contains(currentDropdown)) {
                        dropdown.classList.remove('is-active');
                    }
                });
            }
        });
    </script>
</body>

</html>