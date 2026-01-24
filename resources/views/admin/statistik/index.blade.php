@extends('layouts.admin')

@section('title', 'Statistik Permohonan')
@section('header-title', 'Statistik & Laporan')

@push('styles')
    <style>
        .filter-input {
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.875rem;
        }

        .filter-input-number {
            padding: 0.4rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.875rem;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
        }

        /* === CSS BARU UNTUK STATISTIK YANG MENYAMPING === */
        .stat-container {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            /* Default 1 kolom untuk mobile */
            gap: 1px;
            background-color: var(--border-color);
            overflow: hidden;
            border-radius: 12px;
        }

        @media (min-width: 640px) {

            /* 2 kolom untuk tablet */
            .stat-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {

            /* 4 kolom untuk desktop */
            .stat-container {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .stat-item {
            background-color: var(--card-bg);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 48px;
            width: 48px;
            border-radius: 50%;
        }

        .stat-icon svg {
            height: 24px;
            width: 24px;
            color: white;
        }

        .stat-text .title {
            font-weight: 500;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .stat-text .value {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .custom-dropdown {
            position: relative;
            display: inline-block;
            width: 220px;
            /* Lebar fix agar tidak terlalu lebar */
            vertical-align: middle;
        }

        /* 3. Modifikasi Tombol Dropdown (Gabungan filter-input + flexbox) */
        .dropdown-btn {
            /* Mewarisi style .filter-input, kita tambah flex agar panah di kanan */
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            white-space: nowrap;
            /* Mencegah teks turun baris */
        }

        /* 4. Isi Menu Dropdown (Popup) */
        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            /* Muncul tepat di bawah */
            left: 0;
            background-color: #fff;
            width: 100%;
            /* Lebar mengikuti wrapper */
            min-width: 200px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 50;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            /* Samakan radius dengan input */
            margin-top: 4px;
        }

        /* Tampilkan saat hover */
        .dropdown-content.show {
            display: block;
        }

        .has-submenu:hover .submenu {
            display: block;
        }

        /* Item dalam Dropdown */
        .dropdown-item {
            padding: 10px 16px;
            /* Padding sedikit lebih besar agar mudah diklik */
            font-size: 0.875rem;
            color: #374151;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
            position: relative;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background-color: #f9fafb;
            color: #000;
        }

        /* 5. Submenu (Menu Bersarang ke Samping) */
        .has-submenu .submenu {
            display: none;
            position: absolute;
            left: 100%;
            /* Muncul di KANAN */
            top: -5px;
            background-color: #fff;
            min-width: 160px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            /* Jarak sedikit dari induk */
        }

        .has-submenu:hover .submenu {
            display: block;
        }

        /* Ikon Panah Kecil */
        .arrow-right {
            float: right;
            font-size: 15px;
            color: #9ca3af;
        }
    </style>
@endpush

@section('content')

    {{-- AREA FILTER TANGGAL --}}
    <div class="card mb-6 flex sm:flex-row flex-col justify-between items-center">
        <form action="{{ route('admin.statistik.index') }}" method="GET" id="filterForm"
            class="flex items-center gap-2 flex-wrap">

            <label class="font-medium text-sm">Pilih Periode Waktu:</label>
            <input type="hidden" name="date_filter" id="realDateFilterInput" value="{{ request('date_filter') }}">

            <div class="custom-dropdown">
                <div class="filter-input dropdown-btn" id="dropdownLabel" onclick="toggleDropdown()">
                    <span id="labelText">
                        @php
                            $filter = request('date_filter');
                            $label = '1 Bulan Terakhir'; // Default Value

                            if ($filter == 'today') {
                                $label = 'Hari Ini';
                            } elseif ($filter == 'last_7_days') {
                                $label = '7 Hari Terakhir';
                            } elseif ($filter == 'last_month') {
                                $label = '1 Bulan Terakhir';
                            } elseif ($filter == 'whole_year') {
                                $label = '1 Tahun Terakhir';
                            } elseif ($filter == 'all_time') {
                                $label = 'Semua Waktu';
                            } elseif ($filter == 'custom') {
                                $label = 'Rentang Kustom';

                                // --- LOGIKA TRIWULAN ---
                            } elseif ($filter == 'all_triwulan') {
                                $label = 'Semua Triwulan';
                            } elseif (Str::startsWith($filter, 'triwulan_')) {
                                // Ambil angka setelah underscore (1, 2, 3, 4)
                                $romawi = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
                                $num = explode('_', $filter)[1] ?? 1;
                                $label = 'Triwulan ' . ($romawi[$num] ?? '');

                                // --- LOGIKA SEMESTER ---
                            } elseif ($filter == 'all_semester') {
                                $label = 'Semua Semester';
                            } elseif (Str::startsWith($filter, 'semester_')) {
                                // Ambil angka setelah underscore (1, 2)
                                $romawi = [1 => 'I', 2 => 'II'];
                                $num = explode('_', $filter)[1] ?? 1;
                                $label = 'Semester ' . ($romawi[$num] ?? '');
                            }
                        @endphp

                        {{ $label }}
                    </span>
                    <span style="font-size: 15px; color: #6b7280;">&#9662;</span>
                </div>

                <div id="myDropdown" class="dropdown-content">
                    <div class="dropdown-item" onclick="selectOption('all_time', 'Semua Waktu')">Semua Waktu</div>
                    <div class="dropdown-item" onclick="selectOption('today', 'Hari Ini')">Hari Ini</div>
                    <div class="dropdown-item" onclick="selectOption('last_7_days', '7 Hari Terakhir')">7 Hari Terakhir
                    </div>
                    <div class="dropdown-item" onclick="selectOption('last_month', '1 Bulan Terakhir')">1 Bulan Terakhir
                    </div>
                    <div class="dropdown-item" onclick="selectOption('whole_year', '1 Tahun Terakhir')">1 Tahun Terakhir
                    </div>

                    <div class="dropdown-item has-submenu">
                        <span>Berdasarkan Triwulan</span> <span class="arrow-right">&#9656;</span>
                        <div class="submenu">
                            <div class="dropdown-item" onclick="selectOption('all_triwulan', 'Semua Triwulan')">Semua
                                Triwulan</div>
                            @foreach([1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'] as $k => $v)
                                <div class="dropdown-item" onclick="selectOption('triwulan_{{ $k }}', 'Triwulan {{ $v }}')">
                                    Triwulan {{ $v }}</div>
                            @endforeach
                        </div>
                    </div>

                    <div class="dropdown-item has-submenu">
                        <span>Berdasarkan Semester</span> <span class="arrow-right">&#9656;</span>
                        <div class="submenu">
                            <div class="dropdown-item" onclick="selectOption('all_semester', 'Semua Semester')">
                                Semua Semester
                            </div>

                            @foreach([1 => 'I', 2 => 'II'] as $k => $v)
                                <div class="dropdown-item" onclick="selectOption('semester_{{ $k }}', 'Semester {{ $v }}')">
                                    Semester {{ $v }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="dropdown-item" onclick="selectOption('custom', 'Rentang Kustom')">Rentang Kustom</div>
                </div>
            </div>

            <input type="number" name="year" class="filter-input" style="width: 100px;"
                value="{{ request('year', date('Y')) }}" placeholder="Tahun">

            <div id="customDateWrapper"
                class="items-center gap-2 {{ request('date_filter') == 'custom' ? 'flex' : 'hidden' }}">
                <input type="date" name="start_date" class="filter-input" value="{{ request('start_date') }}">
                <span class="text-gray-400">-</span>
                <input type="date" name="end_date" class="filter-input" value="{{ request('end_date') }}">
            </div>

            <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 shadow-sm transition cursor-pointer">
                Terapkan
            </button>

        </form>

        <div class="ms-auto mt-auto">
            <form action="{{ route('admin.statistik.export') }}" method="GET" class="p-6 flex inline-block">
                <input type="hidden" name="date_filter" value="{{ request('date_filter') }}">
                <input type="hidden" name="year" value="{{ request('year') }}">
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">

                <select name="type" onchange="this.form.submit()"
                    class="border border-gray-300 py-1 rounded-md filter-input">
                    <option value="">Pilih Export</option>
                    <option value="excel">Excel</option>
                    <option value="pdf">PDF</option>
                    <option value="print">Print</option>
                </select>
            </form>

        </div>
    </div>

    {{-- KARTU STATISTIK UTAMA (DENGAN TATA LETAK BARU) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 mb-4">
        <div class="stat-item">
            <div class="stat-icon" style="background-color: #16a34a;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-text">
                <div class="title">Selesai</div>
                <div class="value">{{ $permohonanSelesai }}</div>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon" style="background-color: #6b7280;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h7.5m3-9-3-3m0 0-3 3m3-3v12.75" />
                </svg>
            </div>
            <div class="stat-text">
                <div class="title">Diajukan</div>
                <div class="value">{{ $permohonanDiajukan }}</div>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon" style="background-color: #f59e0b;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0011.667 0l3.181-3.183m-4.991-2.691V5.25a2.25 2.25 0 00-2.25-2.25H4.5A2.25 2.25 0 002.25 5.25v4.992m19.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-text">
                <div class="title">Diproses</div>
                <div class="value">{{ $permohonanDiproses }}</div>
            </div>
        </div>

        <div class="stat-item">
            <div class="stat-icon" style="background-color: #dc2626;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-text">
                <div class="title">Ditolak</div>
                <div class="value">{{ $permohonanDitolak }}</div>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon" style="background-color: #3b82f6;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                </svg>
            </div>
            <div class="stat-text">
                <div class="title">Total Permohonan</div>
                <div class="value">{{ $totalPermohonan }}</div>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon" style="background-color: #a9c037;">
                <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 512 512">
                    <path fill="currentColor" fill-rule="evenodd"
                        d="M448 64H298.667v149.333H448zM234.667 85.333H85.333v149.334h149.334zm-149.334 192h149.334v149.334H85.333zm341.334 0H277.333v149.334h149.334z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="stat-text">
                <div class="title">Kategori Layanan Terbanyak</div>
                <div class="value line-clamp-1">{{ $topLayananKpi }}</div>
            </div>
        </div>
    </div>

    {{-- BAGIAN GRAFIK (Tidak berubah) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="card lg:col-span-2">
            <div class="h-96">
                <div id="trenHarianChart"></div>
            </div>
        </div>
        <div class="card lg:col-span-1">
            <div class="h-96">
                <div id="distribusiStatusChart"></div>
            </div>
        </div>
        <div class="lg:col-span-3 grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="card col-span-3">
                <div class="h-96 relative" data-ikhtisar-wrap>
                    <button type="button" data-ikhtisar-toggle aria-expanded="false" title="Ikhtisar"
                        class="absolute top-3 right-0 z-50 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48">
                            <path fill="currentColor" fill-rule="evenodd"
                                d="M24 1.5c-7.403 0-12.592.239-15.857.466S2.281 4.66 1.991 7.96C1.742 10.794 1.5 15.074 1.5 21v22.652c0 2.99 3.507 4.603 5.778 2.657l6.96-5.966c2.687.093 5.927.157 9.762.157c7.403 0 12.592-.239 15.857-.466s5.862-2.693 6.152-5.993c.249-2.835.491-7.115.491-13.041s-.242-10.206-.491-13.041c-.29-3.3-2.887-5.765-6.152-5.993C36.592 1.74 31.403 1.5 24 1.5m2.882 25.452c2.218-1.238 3.36-2.588 3.538-4.88a96 96 0 0 1-2.028-.027c-1.33-.034-2.326-1.032-2.361-2.362a99 99 0 0 1-.031-2.61c0-1.16.015-2.069.035-2.77c.036-1.252.939-2.202 2.191-2.254C28.921 12.021 29.828 12 31 12s2.079.02 2.774.05c1.252.05 2.155 1.001 2.191 2.254c.02.695.035 1.594.035 2.74v5.03h-.023c-.296 4.235-3.425 6.759-6.97 7.85c-.499.153-1.05.08-1.427-.281c-.438-.419-.813-.937-1.088-1.368c-.295-.464-.09-1.055.39-1.323m-10.462-4.88c-.178 2.292-1.32 3.642-3.538 4.88c-.48.268-.685.86-.39 1.323c.275.431.65.949 1.088 1.368c.378.36.928.434 1.427.28c3.545-1.09 6.674-3.614 6.97-7.85H22v-5.029c0-1.146-.015-2.045-.035-2.74c-.036-1.253-.939-2.204-2.191-2.255C19.079 12.021 18.172 12 17 12s-2.079.02-2.774.05c-1.252.05-2.155 1.001-2.191 2.254c-.02.7-.035 1.608-.035 2.768c0 1.075.013 1.933.03 2.61c.036 1.33 1.031 2.329 2.362 2.363c.546.013 1.215.024 2.028.027"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    {{-- Chart --}}
                    <div class="w-full h-full pr-2"> {{-- pr-12 = 48px, bisa kamu adjust --}}
                        <div id="layananPerKategoriChart" class="w-full h-full"></div>
                    </div>

                    {{-- Ikhtisar / Table (dinamis ikut drilldown) --}}
                    <div class="absolute top-8 right-0 z-50 w-[380px] max-w-[92%] max-h-[85%] overflow-auto hidden"
                        data-ikhtisar-panel>
                        <div class="rounded-xl p-4 card">
                            <div id="layananIkhtisarTitle" class="font-semibold text-slate-800 ">
                                BERDASARKAN KATEGORI
                            </div>

                            <div id="layananIkhtisarTotal" class="mt-1 text-sm text-slate-600 ">
                                Total Data=0
                            </div>

                            <div id="layananIkhtisarList" class="mt-4 space-y-2"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="lg:col-span-3 grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="card">
                <div class="h-96 relative" data-ikhtisar-wrap>
                    <button type="button" data-ikhtisar-toggle aria-expanded="false" title="Ikhtisar"
                        class="absolute top-1 right-0 z-50 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48">
                            <path fill="currentColor" fill-rule="evenodd"
                                d="M24 1.5c-7.403 0-12.592.239-15.857.466S2.281 4.66 1.991 7.96C1.742 10.794 1.5 15.074 1.5 21v22.652c0 2.99 3.507 4.603 5.778 2.657l6.96-5.966c2.687.093 5.927.157 9.762.157c7.403 0 12.592-.239 15.857-.466s5.862-2.693 6.152-5.993c.249-2.835.491-7.115.491-13.041s-.242-10.206-.491-13.041c-.29-3.3-2.887-5.765-6.152-5.993C36.592 1.74 31.403 1.5 24 1.5m2.882 25.452c2.218-1.238 3.36-2.588 3.538-4.88a96 96 0 0 1-2.028-.027c-1.33-.034-2.326-1.032-2.361-2.362a99 99 0 0 1-.031-2.61c0-1.16.015-2.069.035-2.77c.036-1.252.939-2.202 2.191-2.254C28.921 12.021 29.828 12 31 12s2.079.02 2.774.05c1.252.05 2.155 1.001 2.191 2.254c.02.695.035 1.594.035 2.74v5.03h-.023c-.296 4.235-3.425 6.759-6.97 7.85c-.499.153-1.05.08-1.427-.281c-.438-.419-.813-.937-1.088-1.368c-.295-.464-.09-1.055.39-1.323m-10.462-4.88c-.178 2.292-1.32 3.642-3.538 4.88c-.48.268-.685.86-.39 1.323c.275.431.65.949 1.088 1.368c.378.36.928.434 1.427.28c3.545-1.09 6.674-3.614 6.97-7.85H22v-5.029c0-1.146-.015-2.045-.035-2.74c-.036-1.253-.939-2.204-2.191-2.255C19.079 12.021 18.172 12 17 12s-2.079.02-2.774.05c-1.252.05-2.155 1.001-2.191 2.254c-.02.7-.035 1.608-.035 2.768c0 1.075.013 1.933.03 2.61c.036 1.33 1.031 2.329 2.362 2.363c.546.013 1.215.024 2.028.027"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    {{-- Chart --}}
                    <div class="w-full h-full pr-4"> {{-- pr-12 = 48px, bisa kamu adjust --}}
                        <div id="topLayananChart" class="w-full h-full"></div>
                    </div>

                    {{-- Ikhtisar --}}
                    <div class="absolute top-8 right-0 z-50 w-[380px] max-w-[92%] max-h-[85%] overflow-auto hidden"
                        data-ikhtisar-panel>
                        @php
                            $totalTop = $topLayanan->sum('total');
                        @endphp

                        <div class="rounded-xl bg-white card p-4">
                            <div class="font-semibold text-slate-800 ">
                                5 LAYANAN TERATAS
                            </div>

                            <div class="mt-1 text-sm text-slate-600 ">
                                TOTAL (TOP 5)={{ $totalTop }}
                            </div>

                            <div class="mt-4 space-y-2">
                                @forelse($topLayanan->sortByDesc('total') as $row)
                                    @php
                                        $rawName = $row->layanan_dibutuhkan ?? '';
                                        $keyName = strtoupper(trim($rawName));

                                        // Cek Map Warna, fallback ke Default
                                        $c = $topLayananColorMap[$keyName] ?? $topLayananDefaultColor;

                                        // Hitung total keseluruhan (ambil dari collection asli)
                                        $totalTop = $topLayanan->sum('total');
                                        $pct = $totalTop ? ($row->total / $totalTop * 100) : 0;
                                    @endphp

                                    <div class="flex items-start gap-2">
                                        {{-- Dot Warna --}}
                                        <span class="w-3 h-3 rounded-full shrink-0 mt-1"
                                            style="background-color: {{ $c }}"></span>

                                        <div class="text-sm text-slate-700 flex-1 min-w-0 break-words">
                                            <span class="font-semibold">
                                                {{ mb_strtoupper($rawName, 'UTF-8') }}
                                            </span>:
                                            {{ $row->total }} ({{ number_format($pct, 2) }}%)
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-sm text-slate-500">TIDAK ADA DATA.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="h-96 relative" data-ikhtisar-wrap>
                    <button type="button" data-ikhtisar-toggle aria-expanded="false" title="Ikhtisar"
                        class="absolute top-1 right-0 z-50 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48">
                            <path fill="currentColor" fill-rule="evenodd"
                                d="M24 1.5c-7.403 0-12.592.239-15.857.466S2.281 4.66 1.991 7.96C1.742 10.794 1.5 15.074 1.5 21v22.652c0 2.99 3.507 4.603 5.778 2.657l6.96-5.966c2.687.093 5.927.157 9.762.157c7.403 0 12.592-.239 15.857-.466s5.862-2.693 6.152-5.993c.249-2.835.491-7.115.491-13.041s-.242-10.206-.491-13.041c-.29-3.3-2.887-5.765-6.152-5.993C36.592 1.74 31.403 1.5 24 1.5m2.882 25.452c2.218-1.238 3.36-2.588 3.538-4.88a96 96 0 0 1-2.028-.027c-1.33-.034-2.326-1.032-2.361-2.362a99 99 0 0 1-.031-2.61c0-1.16.015-2.069.035-2.77c.036-1.252.939-2.202 2.191-2.254C28.921 12.021 29.828 12 31 12s2.079.02 2.774.05c1.252.05 2.155 1.001 2.191 2.254c.02.695.035 1.594.035 2.74v5.03h-.023c-.296 4.235-3.425 6.759-6.97 7.85c-.499.153-1.05.08-1.427-.281c-.438-.419-.813-.937-1.088-1.368c-.295-.464-.09-1.055.39-1.323m-10.462-4.88c-.178 2.292-1.32 3.642-3.538 4.88c-.48.268-.685.86-.39 1.323c.275.431.65.949 1.088 1.368c.378.36.928.434 1.427.28c3.545-1.09 6.674-3.614 6.97-7.85H22v-5.029c0-1.146-.015-2.045-.035-2.74c-.036-1.253-.939-2.204-2.191-2.255C19.079 12.021 18.172 12 17 12s-2.079.02-2.774.05c-1.252.05-2.155 1.001-2.191 2.254c-.02.7-.035 1.608-.035 2.768c0 1.075.013 1.933.03 2.61c.036 1.33 1.031 2.329 2.362 2.363c.546.013 1.215.024 2.028.027"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    {{-- Chart --}}
                    <div class="w-full h-full pr-4"> {{-- pr-12 = 48px, bisa kamu adjust --}}
                        <div id="layananPerPendidikanChart" class="w-full h-full"></div>
                    </div>

                    {{-- Table --}}
                    <div class="absolute top-8 right-0 z-50 w-[380px] max-w-[92%] max-h-[85%] overflow-auto hidden"
                        data-ikhtisar-panel>
                        @php
                            $totalData = $layananPerPendidikan->sum('total');
                        @endphp

                        <div class="rounded-xl p-4 card">
                            <div class="font-semibold text-slate-800 ">
                                BERDASARKAN PENDIDIKAN
                            </div>

                            <div class="mt-1 text-sm text-slate-600 ">
                                Total Data={{ $totalData }}
                            </div>

                            <div class="mt-4 space-y-2">
                                @forelse($layananPerPendidikan as $row)
                                    @php
                                        // Ambil nama kategori (Pastikan Uppercase/Trim agar cocok dengan key array warna)
                                        $catName = mb_strtoupper(trim($row->pendidikan_kategori ?? ''), 'UTF-8');

                                        // Ambil warna dari array, fallback ke abu-abu jika tidak ketemu
                                        $c = $pendidikanColors[$catName] ?? '#cccccc';

                                        $totalData = $layananPerPendidikan->sum('total'); // Atau variable total global Anda
                                        $pct = $totalData ? ($row->total / $totalData * 100) : 0;
                                    @endphp

                                    <div class="flex items-start gap-2">
                                        {{-- Dot Warna --}}
                                        <span class="w-3 h-3 rounded-full shrink-0 mt-1"
                                            style="background-color: {{ $c }}"></span>

                                        <div class="text-sm text-slate-700 flex-1 min-w-0 break-words">
                                            <span class="font-semibold">
                                                {{ $catName }}
                                            </span>:
                                            {{ $row->total }} ({{ number_format($pct, 2) }}%)
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-sm text-slate-500">TIDAK ADA DATA.</div>
                                @endforelse
                            </div>

                        </div>
                    </div>


                </div>

            </div>



        </div>

        <div class="lg:col-span-3 grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="card">
                <div class="h-96 relative" data-ikhtisar-wrap>
                    <button type="button" data-ikhtisar-toggle aria-expanded="false" title="Ikhtisar"
                        class="absolute top-1 right-0 z-50 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48">
                            <path fill="currentColor" fill-rule="evenodd"
                                d="M24 1.5c-7.403 0-12.592.239-15.857.466S2.281 4.66 1.991 7.96C1.742 10.794 1.5 15.074 1.5 21v22.652c0 2.99 3.507 4.603 5.778 2.657l6.96-5.966c2.687.093 5.927.157 9.762.157c7.403 0 12.592-.239 15.857-.466s5.862-2.693 6.152-5.993c.249-2.835.491-7.115.491-13.041s-.242-10.206-.491-13.041c-.29-3.3-2.887-5.765-6.152-5.993C36.592 1.74 31.403 1.5 24 1.5m2.882 25.452c2.218-1.238 3.36-2.588 3.538-4.88a96 96 0 0 1-2.028-.027c-1.33-.034-2.326-1.032-2.361-2.362a99 99 0 0 1-.031-2.61c0-1.16.015-2.069.035-2.77c.036-1.252.939-2.202 2.191-2.254C28.921 12.021 29.828 12 31 12s2.079.02 2.774.05c1.252.05 2.155 1.001 2.191 2.254c.02.695.035 1.594.035 2.74v5.03h-.023c-.296 4.235-3.425 6.759-6.97 7.85c-.499.153-1.05.08-1.427-.281c-.438-.419-.813-.937-1.088-1.368c-.295-.464-.09-1.055.39-1.323m-10.462-4.88c-.178 2.292-1.32 3.642-3.538 4.88c-.48.268-.685.86-.39 1.323c.275.431.65.949 1.088 1.368c.378.36.928.434 1.427.28c3.545-1.09 6.674-3.614 6.97-7.85H22v-5.029c0-1.146-.015-2.045-.035-2.74c-.036-1.253-.939-2.204-2.191-2.255C19.079 12.021 18.172 12 17 12s-2.079.02-2.774.05c-1.252.05-2.155 1.001-2.191 2.254c-.02.7-.035 1.608-.035 2.768c0 1.075.013 1.933.03 2.61c.036 1.33 1.031 2.329 2.362 2.363c.546.013 1.215.024 2.028.027"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    {{-- Chart --}}
                    <div class="w-full h-full pr-4"> {{-- pr-12 = 48px, bisa kamu adjust --}}
                        <div id="berdasarkanGenderChart" class="w-full h-full"></div>
                    </div>

                    {{-- Ikhtisar --}}
                    <div class="absolute top-8 right-0 z-50 w-[380px] max-w-[92%] max-h-[85%] overflow-auto hidden" data-ikhtisar-panel>
                    @php
                        // Hitung Total Otomatis dari Collection
                        $totalData = $distribusiGender->sum('total');
                    @endphp

                    <div class="rounded-xl p-4 card">
                        <div class="font-semibold text-slate-800 ">
                            BERDASARKAN JENIS KELAMIN
                        </div>

                        <div class="mt-1 text-sm text-slate-600 ">
                            TOTAL DATA={{ $totalData }}
                        </div>

                        <div class="mt-4 space-y-2">
                            {{-- Loop Data Dinamis --}}
                            @forelse($distribusiGender as $i => $row)
                                @php
                                    $countColors = count($warna2Chart);
                                    $c = $warna2Chart[$i % $countColors];
                                    
                                    $pct = $totalData ? ($row->total / $totalData * 100) : 0;
                                @endphp

                                <div class="flex items-start gap-2">
                                    <span class="w-3 h-3 rounded-full shrink-0 mt-1" style="background-color: {{ $c }}"></span>

                                    <div class="text-sm text-slate-700 flex-1 min-w-0 break-words">
                                        <span class="font-semibold">{{ strtoupper($row->label) }}</span>:
                                        {{ $row->total }} ({{ number_format($pct, 2) }}%)
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-slate-500">TIDAK ADA DATA.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                </div>

            </div>

            <div class="card">
                <div class="h-96 relative" data-ikhtisar-wrap>
                    <button type="button" data-ikhtisar-toggle aria-expanded="false" title="Ikhtisar"
                        class="absolute top-1 right-0 z-50 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48">
                            <path fill="currentColor" fill-rule="evenodd"
                                d="M24 1.5c-7.403 0-12.592.239-15.857.466S2.281 4.66 1.991 7.96C1.742 10.794 1.5 15.074 1.5 21v22.652c0 2.99 3.507 4.603 5.778 2.657l6.96-5.966c2.687.093 5.927.157 9.762.157c7.403 0 12.592-.239 15.857-.466s5.862-2.693 6.152-5.993c.249-2.835.491-7.115.491-13.041s-.242-10.206-.491-13.041c-.29-3.3-2.887-5.765-6.152-5.993C36.592 1.74 31.403 1.5 24 1.5m2.882 25.452c2.218-1.238 3.36-2.588 3.538-4.88a96 96 0 0 1-2.028-.027c-1.33-.034-2.326-1.032-2.361-2.362a99 99 0 0 1-.031-2.61c0-1.16.015-2.069.035-2.77c.036-1.252.939-2.202 2.191-2.254C28.921 12.021 29.828 12 31 12s2.079.02 2.774.05c1.252.05 2.155 1.001 2.191 2.254c.02.695.035 1.594.035 2.74v5.03h-.023c-.296 4.235-3.425 6.759-6.97 7.85c-.499.153-1.05.08-1.427-.281c-.438-.419-.813-.937-1.088-1.368c-.295-.464-.09-1.055.39-1.323m-10.462-4.88c-.178 2.292-1.32 3.642-3.538 4.88c-.48.268-.685.86-.39 1.323c.275.431.65.949 1.088 1.368c.378.36.928.434 1.427.28c3.545-1.09 6.674-3.614 6.97-7.85H22v-5.029c0-1.146-.015-2.045-.035-2.74c-.036-1.253-.939-2.204-2.191-2.255C19.079 12.021 18.172 12 17 12s-2.079.02-2.774.05c-1.252.05-2.155 1.001-2.191 2.254c-.02.7-.035 1.608-.035 2.768c0 1.075.013 1.933.03 2.61c.036 1.33 1.031 2.329 2.362 2.363c.546.013 1.215.024 2.028.027"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    {{-- Chart --}}
                    <div class="w-full h-full pr-4"> {{-- pr-12 = 48px, bisa kamu adjust --}}
                        <div id="distribusiProfesiChart" class="w-full h-full"></div>
                    </div>

                    {{-- Ikhtisar --}}
                    <div class="absolute top-8 right-0 z-50 w-[380px] max-w-[92%] max-h-[85%] overflow-auto hidden"
                        data-ikhtisar-panel>
                        @php
                            $totalData = $distribusiProfesi->sum('total');
                        @endphp

                        <div class="rounded-xl bg-white card p-4">
                            <div class="font-semibold text-slate-800 ">
                                BERDASARKAN PROFESI
                            </div>

                            <div class="mt-1 text-sm text-slate-600 ">
                                TOTAL DATA={{ $totalData }}
                            </div>

                            <div class="mt-4 space-y-2">
                                @forelse($distribusiProfesi as $row)
                                    @php
                                        // 1. Ambil Label (Uppercase)
                                        $labelRaw = $row->profesi_kategori ?? 'LAINNYA';
                                        $label = mb_strtoupper($labelRaw, 'UTF-8');

                                        // 2. Ambil Warna dari Controller
                                        $c = $profesiColors[$label] ?? '#cccccc'; // Fallback ke abu-abu jika tidak ketemu

                                        $pct = $totalData ? ($row->total / $totalData * 100) : 0;
                                    @endphp

                                    <div class="flex items-start gap-2">
                                        <span class="w-3 h-3 rounded-full shrink-0 mt-1"
                                            style="background-color: {{ $c }}"></span>
                                        <div class="text-sm text-slate-700 flex-1 min-w-0 break-words">
                                            <span class="font-semibold">{{ $label }} </span>: {{ (int) $row->total }}
                                            ({{ number_format($pct, 2) }}%)
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-sm text-slate-500">TIDAK ADA DATA.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>






    </div>

@endsection


@push('scripts')
    {{-- Import library dari CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>

    <script>
        function toggleDropdown() {
            document.getElementById("myDropdown").classList.toggle("show");
        }

        // 2. Fungsi saat Opsi Dipilih
        function selectOption(value, text) {
            // Isi nilai ke input hidden agar bisa dikirim ke controller
            document.getElementById('realDateFilterInput').value = value;

            // Ubah teks label biar user tau apa yang dipilih
            document.getElementById('labelText').innerText = text;

            // Logika Tampilkan/Sembunyikan Input Tanggal Custom
            const customWrapper = document.getElementById('customDateWrapper');
            if (value === 'custom') {
                customWrapper.classList.remove('hidden');
                customWrapper.classList.add('flex'); // Pakai flex biar rapi
            } else {
                customWrapper.classList.add('hidden');
                customWrapper.classList.remove('flex');

                // OPSIONAL: Langsung submit form jika bukan 'custom'
                // document.getElementById('filterForm').submit(); 
            }

            // Tutup dropdown setelah memilih
            document.getElementById("myDropdown").classList.remove("show");
        }

        document.addEventListener('DOMContentLoaded', function () {
            // A. Tutup dropdown jika klik di luar area dropdown
            window.onclick = function (event) {
                if (!event.target.matches('.dropdown-btn') && !event.target.matches('.dropdown-btn *')) {
                    var dropdowns = document.getElementsByClassName("dropdown-content");
                    for (var i = 0; i < dropdowns.length; i++) {
                        var openDropdown = dropdowns[i];
                        if (openDropdown.classList.contains('show')) {
                            openDropdown.classList.remove('show');
                        }
                    }
                }
            }

            // B. Handle Filter Status (Jika masih ada elemen select status)
            const statusFilterSelect = document.getElementById('statusFilterSelect');
            const filterForm = document.getElementById('filterForm');

            if (statusFilterSelect && filterForm) {
                statusFilterSelect.addEventListener('change', function () {
                    filterForm.submit();
                });
            }

            document.querySelectorAll('[data-ikhtisar-toggle]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const wrap = btn.closest('[data-ikhtisar-wrap]');
                    const panel = wrap?.querySelector('[data-ikhtisar-panel]');
                    if (!panel) return;

                    const willShow = panel.classList.contains('hidden');
                    panel.classList.toggle('hidden');
                    btn.setAttribute('aria-expanded', willShow ? 'true' : 'false');
                });
            });

            // --- Pengaturan Grafik ApexCharts ---
            const isDarkMode = document.body.classList.contains('dark');
            const chartTheme = {
                mode: isDarkMode ? 'dark' : 'light',
                palette: 'palette1',
                background: 'transparent'
            };
            const textColors = isDarkMode ? 'rgba(229, 231, 235, 0.8)' : 'rgba(55, 65, 81, 0.8)';
            const gridColors = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : '#e0e0e0';

            // 1. Grafik Tren Harian (Area Chart)
            const trenOptions = {
                title: {
                    text: 'Tren Permohonan Harian',
                    style: {
                        fontFamily: 'Inter, sans-serif',
                        color: '#000',
                        fontWeight: 600,
                        fontSize: '17px'
                    }
                },
                series: [{
                    name: 'Jumlah Permohonan',
                    data: @json($dataTren)
                }],
                chart: {

                    type: 'area',
                    height: '100%',
                    ...chartTheme,
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                xaxis: {
                    type: 'datetime',
                    categories: @json($labelsTren),
                    labels: {
                        style: {
                            colors: textColors
                        }
                    }
                },
                yaxis: {
                    tickAmount: 5,
                    min: 0,
                    labels: {
                        style: {
                            colors: textColors
                        },
                        formatter: (val) => {
                            return val.toFixed(0)
                        }
                    }
                },
                grid: {
                    borderColor: gridColors
                },
                tooltip: {
                    theme: isDarkMode ? 'dark' : 'light'
                }
            };
            const trenChart = new ApexCharts(document.querySelector("#trenHarianChart"), trenOptions);
            trenChart.render();

            // 2. Grafik Distribusi Status (Donut Chart)
            // ambil total per status dari Laravel jadi object: { "Selesai": 10, "Diproses": 5, ... }
            const statusCounts = @json($distribusiStatus->pluck('total', 'status'));

            const statusOrder = ['Selesai', 'Diajukan', 'Diproses', 'Ditolak',];
            const statusLabels = ['SELESAI', 'DIAJUKAN', 'DIPROSES', 'DITOLAK',];
            const statusColors = ['#16a34a', '#6b7280', '#f59e0b', '#dc2626',]; // hijau, orange, merah

            const statusOptions = {
                title: {
                    text: 'Berdasarkan Status',
                    style: {
                        fontFamily: 'Inter, sans-serif',
                        color: '#000',
                        fontWeight: 600,
                        fontSize: '17px'
                    }
                },
                subtitle: {
                    text: @json(
                        \Carbon\Carbon::parse($startDate)->locale('id')->translatedFormat('d F Y')
                        . ' – ' .
                        \Carbon\Carbon::parse($endDate)->locale('id')->translatedFormat('d F Y')
                    ),
                    style: {
                        fontFamily: 'Inter, sans-serif',
                        color: '#4B5563', // text-gray-600
                        fontWeight: 400,
                        fontSize: '14px'
                    }
                },


                series: statusOrder.map(function (s) {
                    return statusCounts[s] ? statusCounts[s] : 0; // aman (tanpa ??)
                }),
                labels: statusLabels,
                chart: {
                    type: 'donut',
                    height: '100%',
                    ...chartTheme,
                    toolbar: {
                        show: true,
                        export: {
                            png: { filename: 'Berdasarkan Status' },
                            svg: { filename: 'Berdasarkan Status' },
                            csv: { filename: 'Berdasarkan Status' }
                        }
                    }
                },
                colors: statusColors,
                legend: {
                    position: 'bottom',
                    labels: { colors: textColors }
                },
                tooltip: {
                    theme: isDarkMode ? 'dark' : 'light'
                }
            };


            const statusChart = new ApexCharts(document.querySelector("#distribusiStatusChart"), statusOptions);
            statusChart.render();

            // 3. Grafik Top 5 Layanan (Horizontal Bar Chart)
            // 1. Ambil Data & Map Warna
            const topLayananRawData = @json($topLayanan->values());
            const topLayananMap = @json($topLayananColorMap ?? []);
            const topLayananDefault = @json($topLayananDefaultColor ?? '#ffb81d');

            // 2. Map Data & Warna
            let processedData = (Array.isArray(topLayananRawData) ? topLayananRawData : [])
                .map(item => {
                    const rawName = item.layanan_dibutuhkan || '';
                    const keyName = rawName.trim().toUpperCase();
                    const valTotal = Number(item.total) || 0;
                    const specificColor = topLayananMap[keyName] || topLayananDefault;

                    return {
                        x: rawName.toUpperCase(),
                        y: valTotal,
                        color: specificColor
                    };
                });

            // ✅ LANGKAH KUNCI: SORTING DI SISI JS
            // Urutkan dari nilai (y) Terbesar ke Terkecil agar Bar Terpanjang ada di Paling Atas
            processedData.sort((a, b) => b.y - a.y);

            // 3. Pisahkan Data Series dan Array Warna (Setelah di-sort)
            const finalSeriesData = processedData.map(item => ({ x: item.x, y: item.y }));
            const finalColors = processedData.map(item => item.color);

            // 4. Konfigurasi Chart
            const topLayananOptions = {
                title: {
                    text: '5 Layanan Teratas',
                    style: { fontFamily: 'Inter, sans-serif', fontWeight: 600, fontSize: '18px', color: '#000' }
                },
                subtitle: {
                    text: @json(
                        \Carbon\Carbon::parse($startDate)->locale('id')->translatedFormat('d F Y') . ' – ' .
                        \Carbon\Carbon::parse($endDate)->locale('id')->translatedFormat('d F Y')
                    ),
                    style: { fontFamily: 'Inter, sans-serif', color: '#4B5563', fontSize: '14px' }
                },
                series: [{
                    name: 'JUMLAH',
                    data: finalSeriesData
                }],
                responsive: [{
                    breakpoint: 480, // Untuk layar HP (lebar di bawah 480px)
                    options: {
                        chart: {
                            height: 350 // Paksa tinggi fix biar tidak gepeng
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true, // Tetap horizontal
                                barHeight: '80%', // Batang lebih tebal dikit
                                dataLabels: {
                                    position: 'bottom' // Label angka geser agar aman
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                show: true,
                                maxWidth: 100, // PENTING: Batasi lebar label maksimal 100px
                                style: {
                                    fontSize: '10px', // Font dikecilin
                                    colors: textColors
                                },
                                formatter: function (value) {
                                    // Potong teks jika lebih dari 15 karakter biar muat
                                    if (typeof value === 'string' && value.length > 15) {
                                        return value.substring(0, 15) + '...';
                                    }
                                    return value;
                                }
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            offsetX: 0,
                            style: {
                                fontSize: '10px'
                            }
                        }
                    }
                }],
                chart: {
                    type: 'bar',
                    height: '100%',
                    toolbar: { show: true },
                    ...chartTheme
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true,
                        distributed: true,
                        barHeight: '70%',
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },

                // Array warna mengikuti urutan data yang sudah di-sort
                colors: finalColors,

                dataLabels: {
                    enabled: true,
                    formatter: (val) => val,
                    offsetX: 10,
                    style: { fontSize: '12px', colors: ['#000'] }
                },
                xaxis: {
                    tickAmount: 5,
                    labels: {
                        style: { colors: textColors },
                        formatter: (val) => val.toFixed(0)
                    }
                },
                yaxis: {
                    labels: { style: { colors: textColors } }
                },
                legend: { show: false }
            };

            if (document.querySelector("#topLayananChart")) {
                new ApexCharts(document.querySelector("#topLayananChart"), topLayananOptions).render();
            }

    // ============================================================
    // 1. TERIMA VARIABEL DARI CONTROLLER
    // ============================================================
    // Pastikan variabel ini dikirim dari Controller (index method)
    const mapServiceColors    = @json($serviceColors ?? []);
    // Variable ini harus berisi 'colorMap' yang dibuat di controller
    const mapSubServiceColors = @json($subServiceColors ?? []); 
    
    // Data Dinamis dari Database
    const mainSeriesRaw       = @json($mainSeries ?? []);
    const drillSeriesRaw      = @json($drilldownSeries ?? []);

    // ============================================================
    // 2. LOGIKA PEWARNAAN OTOMATIS (Sesuai kode Anda)
    // ============================================================

    function normalizePointLayanan(p) {
        let name = '';
        let val = 0;
        let drillId = undefined;

        // Cek format data (Array vs Object)
        if (Array.isArray(p)) {
            name = p[0];
            val = Number(p[1]) || 0;
        } else {
            name = p.name || '';
            val = Number(p.y) || 0;
            drillId = p.drilldown;
        }

        // Tentukan Warna
        let assignedColor = '#cbd5e1'; // Default

        // A. Cek di Map Induk
        if (mapServiceColors[name]) {
            assignedColor = mapServiceColors[name];
        }
        // B. Cek di Map Anak (Sub-layanan)
        else if (mapSubServiceColors[name]) {
            assignedColor = mapSubServiceColors[name];
        }
        // C. Fallback: Jika controller sudah kirim warna di properti object (Main Series)
        else if (!Array.isArray(p) && p.color) {
            assignedColor = p.color;
        }

        return {
            name: name,
            y: val,
            color: assignedColor,
            drilldown: drillId
        };
    }

    function applyColorsLayanan(points) {
        if (!Array.isArray(points)) return [];
        return points.map(p => normalizePointLayanan(p));
    }

    // ============================================================
    // 3. PROSES DATA (BERSIHKAN 0 & BERI WARNA)
    // ============================================================

    function filterZero(dataArr) {
        return dataArr.filter(p => p.y > 0);
    }

    // A. Proses Main Series
    // Clone agar aman
    const processedMainSeries = JSON.parse(JSON.stringify(mainSeriesRaw));
    
    // Pastikan array ada isinya sebelum diproses
    if (processedMainSeries.length > 0) {
        processedMainSeries[0].data = filterZero(applyColorsLayanan(processedMainSeries[0].data));
    }

    // B. Proses Drilldown Series
    const processedDrillSeries = (drillSeriesRaw || []).map(s => ({
        ...s,
        data: filterZero(applyColorsLayanan(s.data || []))
    })).filter(s => s.data.length > 0);

    // ============================================================
    // 4. IKHTISAR & HIGHCHARTS (STYLE TETAP - TAILWIND)
    // ============================================================

    function sumY(points) {
        return points.reduce((acc, p) => acc + (Number(p.y) || 0), 0);
    }

    function renderIkhtisarLayanan(title, points) {
        const titleEl = document.getElementById('layananIkhtisarTitle');
        const totalEl = document.getElementById('layananIkhtisarTotal');
        const listEl = document.getElementById('layananIkhtisarList');

        if (!titleEl || !totalEl || !listEl) return;

        const total = sumY(points);
        titleEl.textContent = title;
        totalEl.textContent = `Total Data=${total}`;

        if (!points.length) {
            listEl.innerHTML = `<div class="text-sm text-slate-500">TIDAK ADA DATA.</div>`;
            return;
        }

        // Sort data descending agar yang banyak di atas
        const sortedPoints = [...points].sort((a, b) => b.y - a.y);

        // STYLE INI TIDAK SAYA UBAH (Sesuai Request)
        listEl.innerHTML = sortedPoints.map(p => {
            const c = p.color;
            const pct = total ? (p.y / total * 100) : 0;
            const name = (p.name || '').toString().toLocaleUpperCase('id-ID');

            return `
            <div class="flex items-start gap-2">
                <span class="w-3 h-3 rounded-full shrink-0 mt-1" style="background-color:${c}"></span>
                <div class="text-sm text-slate-700 flex-1 min-w-0 break-words">
                    <span class="font-semibold">${name}</span>:
                    ${p.y} (${pct.toFixed(2)}%)
                </div>
            </div>
            `;
        }).join('');
    }

    // Initial Render
    if (processedMainSeries.length > 0) {
        renderIkhtisarLayanan('BERDASARKAN KATEGORI', processedMainSeries[0].data);
    }

    Highcharts.chart('layananPerKategoriChart', {
        chart: {
            type: 'bar',
            events: {
                drilldown: function (e) {
                    if (!e.seriesOptions) return;
                    
                    // Kita ambil data drilldown yang SUDAH kita proses warnanya di atas
                    // Cari series di processedDrillSeries yang id-nya sama
                    const targetSeries = processedDrillSeries.find(s => s.id === e.seriesOptions.id);

                    if (targetSeries) {
                        // Gunakan nama point induk sebagai judul detail
                        const parentName = (e.point.name || '').toUpperCase();
                        renderIkhtisarLayanan(`DETAIL: ${parentName}`, targetSeries.data);
                    }
                },
                drillup: function () {
                    // Kembali ke Main
                    renderIkhtisarLayanan('BERDASARKAN KATEGORI', processedMainSeries[0].data);
                }
            }
        },

        title: {
            text: 'Berdasarkan Kategori',
            align: 'left',
            style: {
                fontFamily: 'Inter, sans-serif',
                color: '#000',
                fontWeight: 600,
                fontSize: '18px'
            }
        },
        subtitle: {
            text: @json(
                \Carbon\Carbon::parse($startDate)->locale('id')->translatedFormat('d F Y')
                . ' – ' .
                \Carbon\Carbon::parse($endDate)->locale('id')->translatedFormat('d F Y')
            ),
            style: {
                fontFamily: 'Inter, sans-serif',
                color: '#4B5563',
                fontWeight: 400,
                fontSize: '14px'
            }
        },
        exporting: {
            enabled: true,
            filename: "Berdasarkan Kategori",
            buttons: {
                contextButton: {
                    menuItems: ['downloadPNG', 'downloadSVG', 'downloadCSV']
                }
            }
        },
        xAxis: {
            type: 'category',
            labels: {
                formatter: function () {
                    return (this.value ?? '').toString().toLocaleUpperCase('id-ID');
                }
            }
        },
        yAxis: { title: { text: 'Jumlah' }, allowDecimals: false },
        legend: { enabled: false },
        tooltip: {
            formatter: function () {
                return `<b>${(this.point.name ?? '').toString().toLocaleUpperCase('id-ID')}</b>: ${this.y}`;
            }
        },
        plotOptions: {
            series: {
                borderWidth: 0,
                colorByPoint: true,
                dataLabels: { enabled: true, format: '{point.y}' },
                pointPadding: 0.03,
                groupPadding: 0.03
            }
        },

        // Gunakan Data yang Sudah Diproses
        series: processedMainSeries,

        drilldown: {
            breadcrumbs: { position: { align: 'right' } },
            series: processedDrillSeries
        }
    });

            // 1. Ambil Data & Map Warna
            const pendidikanRawData = @json($layananPerPendidikan->values());
            const pendidikanMap = @json($pendidikanColors ?? []);

            // 2. Proses Data: Ekstrak Kategori, Total, dan Tentukan Warnanya
            // Kita map data yang sudah urut dari PHP
            const processedPendidikan = (Array.isArray(pendidikanRawData) ? pendidikanRawData : [])
                .map(item => {
                    const rawName = item.pendidikan_kategori || '';
                    const keyName = rawName.trim().toUpperCase();

                    // Ambil warna spesifik
                    const specificColor = pendidikanMap[keyName] || '#cccccc';

                    return {
                        x: rawName.toUpperCase(), // Kategori
                        y: Number(item.total) || 0, // Total
                        color: specificColor      // Warna yang cocok
                    };
                });

            // 3. Pisahkan menjadi Array Series dan Array Warna yang Sinkron
            const pendidikanSeriesData = processedPendidikan.map(item => item.y);
            const pendidikanCategories = processedPendidikan.map(item => item.x);
            const pendidikanColorsArr = processedPendidikan.map(item => item.color);

            // 4. Konfigurasi Chart
            const layananPerPendidikanOptions = {
                title: {
                    text: 'Berdasarkan Pendidikan',
                    align: 'left',
                    style: { fontFamily: 'Inter, sans-serif', color: '#000', fontWeight: 600, fontSize: '18px' }
                },
                subtitle: {
                    text: @json(
                        \Carbon\Carbon::parse($startDate)->locale('id')->translatedFormat('d F Y') . ' – ' .
                        \Carbon\Carbon::parse($endDate)->locale('id')->translatedFormat('d F Y')
                    ),
                    style: { fontFamily: 'Inter, sans-serif', color: '#4B5563', fontWeight: 400, fontSize: '14px' }
                },
                series: [{
                    name: 'JUMLAH',
                    data: pendidikanSeriesData // Data angka
                }],
                chart: {
                    type: 'bar',
                    height: '100%',
                    toolbar: {
                        show: true,
                        export: {
                            png: { filename: 'Berdasarkan Pendidikan' },
                            svg: { filename: 'Berdasarkan Pendidikan' },
                            csv: { filename: 'Berdasarkan Pendidikan' }
                        }
                    },
                    ...chartTheme
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true,
                        distributed: true,
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                colors: pendidikanColorsArr,
                dataLabels: {
                    enabled: true,
                    formatter: (val) => val,
                    offsetX: 10,
                    style: { fontSize: '12px', colors: ['#000'] }
                },
                xaxis: {
                    categories: pendidikanCategories, // Kategori (SD, SMP, dll)
                    tickAmount: 5,
                    labels: {
                        style: { colors: textColors },
                        formatter: (val) => val.toFixed(0)
                    }
                },
                yaxis: {
                    labels: { style: { colors: textColors } }
                },
                grid: { borderColor: gridColors },
                tooltip: { theme: isDarkMode ? 'dark' : 'light' },
                legend: { show: false }
            };

            const layananPerPendidikanChart = new ApexCharts(
                document.querySelector("#layananPerPendidikanChart"),
                layananPerPendidikanOptions
            );
            layananPerPendidikanChart.render();

            // GRAFIK BARU: BERDASARKAN PROFESI (Pie Chart)
            // 1. Urutkan Warna sesuai Data Query
            const orderedProfesiColors = @json(
                $distribusiProfesi->map(function ($item) use ($profesiColors) {
                    $key = mb_strtoupper($item->profesi_kategori ?? 'LAINNYA');
                    return $profesiColors[$key] ?? '#cccccc';
                })
            );

            const profesiOptions = {
                title: {
                    text: 'Berdasarkan Profesi',
                    style: {
                        fontFamily: 'Inter, sans-serif',
                        color: '#000',
                        fontWeight: 600,
                        fontSize: '17px'
                    }
                },
                subtitle: {
                    text: @json(
                        \Carbon\Carbon::parse($startDate)->locale('id')->translatedFormat('d F Y')
                        . ' – ' .
                        \Carbon\Carbon::parse($endDate)->locale('id')->translatedFormat('d F Y')
                    ),
                    style: {
                        fontFamily: 'Inter, sans-serif',
                        color: '#4B5563', // text-gray-600
                        fontWeight: 400,
                        fontSize: '14px'
                    }
                },

                series: @json($distribusiProfesi->pluck('total')),
                labels: @json($distribusiProfesi->pluck('profesi_kategori')->map(fn($v) => mb_strtoupper($v ?? '', 'UTF-8'))),

                chart: {
                    type: 'pie',
                    height: '100%',
                    ...chartTheme,
                    toolbar: {
                        show: true,
                        export: {
                            png: { filename: 'Berdasarkan Profesi' },
                            svg: { filename: 'Berdasarkan Profesi' },
                            csv: { filename: 'Berdasarkan Profesi' }
                        }
                    }
                },

                // ✅ GUNAKAN VARIABLE YANG SUDAH DIURUTKAN
                colors: orderedProfesiColors,

                legend: {
                    position: 'bottom',
                    labels: { colors: textColors }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) { return val.toFixed(0) + '%'; } // persen bulat
                },
                tooltip: {
                    theme: isDarkMode ? 'dark' : 'light',
                    y: { formatter: (val) => val }
                }
            };

            new ApexCharts(document.querySelector("#distribusiProfesiChart"), profesiOptions).render();


            // 3. Grafik Distribusi Jenis Kelamin (Donut Chart)
            
            // Siapkan Data dari PHP
            const genderData = @json($distribusiGender);
            
            // Extract Labels & Series
            const genderLabels = genderData.map(item => item.label.toUpperCase());
            const genderSeries = genderData.map(item => Number(item.total));

            const proporsiRespondenOptions = {
                title: {
                    text: 'Berdasarkan Jenis Kelamin',
                    align: 'left',
                    style: {
                        fontFamily: 'Inter, sans-serif',
                        color: '#000',
                        fontWeight: 600,
                        fontSize: '18px'
                    }
                },
                subtitle: {
                    text: @json(
                        \Carbon\Carbon::parse($startDate)->locale('id')->translatedFormat('d F Y')
                        . ' – ' .
                        \Carbon\Carbon::parse($endDate)->locale('id')->translatedFormat('d F Y')
                    ),
                    style: {
                        fontFamily: 'Inter, sans-serif',
                        color: '#4B5563', 
                        fontWeight: 400,
                        fontSize: '14px'
                    }
                },
                
                // ✅ GUNAKAN DATA DINAMIS DISINI
                series: genderSeries,
                labels: genderLabels,
                
                colors: @json($warna2Chart), 
                
                chart: {
                    type: 'pie',
                    height: '100%',
                    ...chartTheme,
                    toolbar: {
                        show: true,
                        export: {
                            png: { filename: 'Berdasarkan Jenis Kelamin' },
                            svg: { filename: 'Berdasarkan Jenis Kelamin' },
                            csv: { filename: 'Berdasarkan Jenis Kelamin' }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        colors: textColors
                    }
                },
            };

            const proporsiRespondenChart = new ApexCharts(document.querySelector(
                "#berdasarkanGenderChart"), proporsiRespondenOptions);
            proporsiRespondenChart.render();


        });
    </script>
@endpush