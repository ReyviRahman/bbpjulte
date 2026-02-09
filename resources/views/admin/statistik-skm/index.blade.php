@extends('layouts.admin')

@section('title', 'Statistik SKM')
@section('header-title', 'Statistik & Laporan SKM')

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
                grid-template-columns: repeat(3, 1fr);
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

        .scroll-table {
            border-collapse: collapse;
            width: 100%;
        }

        .scroll-table thead,
        .scroll-table tfoot {
            display: table;
            width: 100%;
            table-layout: fixed;
            /* 👉 memastikan kolom sama */
        }

        .scroll-table tbody {
            display: block;
            max-height: 550px;
            /* 👉 Ubah sesuai kebutuhan */
            overflow-y: auto;
            width: 100%;
        }

        .scroll-table tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
            /* 👉 wajib agar lebar kolom stabil */
        }

        /* Small scrollbar for tbody only */
        .scroll-table tbody::-webkit-scrollbar {
            width: 5px;
            /* 👉 perkecil ukuran scrollbar */
        }

        .scroll-table tbody::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .scroll-table tbody::-webkit-scrollbar-thumb {
            background: #b5b5b5;
            border-radius: 10px;
        }

        .scroll-table tbody::-webkit-scrollbar-thumb:hover {
            background: #9a9a9a;
        }

        /* Firefox */
        .scroll-table tbody {
            scrollbar-width: thin;
            /* kecil */
            scrollbar-color: #b5b5b5 #f1f1f1;
            /* thumb | track */
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
    @if (session('error'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            {{ session('error') }}
        </div>
    @endif
    <div class="card mb-6 flex sm:flex-row flex-col justify-between items-center">
        <form action="{{ route('admin.statistik-skm.index') }}" method="GET" id="filterForm"
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
            <form action="{{ route('admin.statistik-skm.export') }}" method="GET" class="p-6 flex inline-block">
                {{-- 1. WAJIB: Kirim Filter & Tahun agar Controller tau logikanya (all_time, semester, dll) --}}
                <input type="hidden" name="date_filter" value="{{ request('date_filter') }}">
                <input type="hidden" name="year" value="{{ request('year') }}">

                {{-- 2. Kirim Tanggal (untuk jaga-jaga jika filter = custom) --}}
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

    {{-- KARTU STATISTIK UTAMA --}}
    <div class="stat-container mb-8">
        <div class="stat-item">
            <div class="stat-icon" style="background-color: #3aeae7;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 48 48" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                        d="M24 20a7 7 0 1 0 0-14a7 7 0 0 0 0 14M6 40.8V42h36v-1.2c0-4.48 0-6.72-.872-8.432a8 8 0 0 0-3.496-3.496C35.92 28 33.68 28 29.2 28H18.8c-4.48 0-6.72 0-8.432.872a8 8 0 0 0-3.496 3.496C6 34.08 6 36.32 6 40.8" />
                </svg>
            </div>
            <div class="stat-text">
                <div class="title">Total Responden</div>
                <div class="value">{{ $totalResponden }}</div>
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
                <div class="title">Nilai Rerata Tertimbang</div>
                <div class="value">{{ number_format($jumlahNRRTertimbang, 2, ',', '.') }}</div>
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
                <div class="title">Unsur Tertinggi Pelayanan</div>
                @foreach ($sortedNrr as $kode => $nilai)
                    <tr class="hover:bg-gray-50">
                        <div class="value line-clamp-1">{{ $namaUnsur[$kode] }}</div>
                    </tr>
                    @break
                @endforeach

            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-icon" style="background-color: #16a34a;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 16" stroke-width="0.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M5 5h5.999V4H5zM3 5h1V4H3zm0 3h1V7H3zm6.022-1l-.15.333l-.737-.078l-.467-.05l-.33.342a5 5 0 0 0-.39.453H5V7zm-3.005 3L6 10.056l.306.411l.399.533H5v-1zM3 11h1v-1H3z" />
                    <path fill="#ffffff"
                        d="m13 7.05l-.162-.359l-.2-.447l-.47-.11L12 6.098V2H2v11h4.36c.157.354.355.69.59 1H1V1h12z" />
                    <path fill="#ffffff"
                        d="M11.004 7q.485 0 .966.109l.595 1.293l1.465-.152c.457.462.786 1.016.969 1.61l-.87 1.14l.871 1.141a4 4 0 0 1-.387.859a4 4 0 0 1-.583.75l-1.465-.152l-.594 1.292a4.4 4.4 0 0 1-1.941.001l-.594-1.293l-1.466.152a3.95 3.95 0 0 1-.969-1.61l.87-1.14L7 9.86a3.95 3.95 0 0 1 .97-1.61l1.466.152l.593-1.292a4.4 4.4 0 0 1 .975-.11M11 12a1 1 0 1 0 .002-1.998A1 1 0 0 0 11 12" />
                </svg>
            </div>
            <div class="stat-text">
                <div class="title">Indeks Kepuasan Masyarakat</div>
                <div class="value">{{ number_format($ikm, 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon" style="background-color: #6b7280;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.5 11L12 2l5.5 9zm11 11q-1.875 0-3.187-1.312T13 17.5t1.313-3.187T17.5 13t3.188 1.313T22 17.5t-1.312 3.188T17.5 22M3 21.5v-8h8v8zM17.5 20q1.05 0 1.775-.725T20 17.5t-.725-1.775T17.5 15t-1.775.725T15 17.5t.725 1.775T17.5 20M5 19.5h4v-4H5zM10.05 9h3.9L12 5.85zm7.45 8.5" />
                </svg>
            </div>
            <div class="stat-text">
                <div class="title">Kategori Mutu Pelayanan</div>
                <div class="value">{{ $mutu }}</div>
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
                <div class="value">{{ $layananTerbanyak?->layanan_didapat ?? '-' }}</div>
            </div>
        </div>
    </div>


    {{-- BAGIAN GRAFIK --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="card lg:col-span-2">
            <div class="h-96 relative" data-ikhtisar-wrap>
    
                {{-- 1. Toggle Icon --}}
                <button type="button" data-ikhtisar-toggle aria-expanded="false" title="Ikhtisar"
                    class="absolute top-[4px] right-0 z-50 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48"><path fill="currentColor" fill-rule="evenodd" d="M24 1.5c-7.403 0-12.592.239-15.857.466S2.281 4.66 1.991 7.96C1.742 10.794 1.5 15.074 1.5 21v22.652c0 2.99 3.507 4.603 5.778 2.657l6.96-5.966c2.687.093 5.927.157 9.762.157c7.403 0 12.592-.239 15.857-.466s5.862-2.693 6.152-5.993c.249-2.835.491-7.115.491-13.041s-.242-10.206-.491-13.041c-.29-3.3-2.887-5.765-6.152-5.993C36.592 1.74 31.403 1.5 24 1.5m2.882 25.452c2.218-1.238 3.36-2.588 3.538-4.88a96 96 0 0 1-2.028-.027c-1.33-.034-2.326-1.032-2.361-2.362a99 99 0 0 1-.031-2.61c0-1.16.015-2.069.035-2.77c.036-1.252.939-2.202 2.191-2.254C28.921 12.021 29.828 12 31 12s2.079.02 2.774.05c1.252.05 2.155 1.001 2.191 2.254c.02.695.035 1.594.035 2.74v5.03h-.023c-.296 4.235-3.425 6.759-6.97 7.85c-.499.153-1.05.08-1.427-.281c-.438-.419-.813-.937-1.088-1.368c-.295-.464-.09-1.055.39-1.323m-10.462-4.88c-.178 2.292-1.32 3.642-3.538 4.88c-.48.268-.685.86-.39 1.323c.275.431.65.949 1.088 1.368c.378.36.928.434 1.427.28c3.545-1.09 6.674-3.614 6.97-7.85H22v-5.029c0-1.146-.015-2.045-.035-2.74c-.036-1.253-.939-2.204-2.191-2.255C19.079 12.021 18.172 12 17 12s-2.079.02-2.774.05c-1.252.05-2.155 1.001-2.191 2.254c-.02.7-.035 1.608-.035 2.768c0 1.075.013 1.933.03 2.61c.036 1.33 1.031 2.329 2.362 2.363c.546.013 1.215.024 2.028.027" clip-rule="evenodd"/></svg>
                </button>

                {{-- 2. Chart Container --}}
                <div class="w-full h-full pr-4"> 
                    <div id="ikmChart" class="w-full h-full"></div>
                </div>

                {{-- 3. IKHTISAR (FLOATING OVERLAY) --}}
                <div class="absolute top-6 right-0 z-50 w-[380px] max-w-[92%] max-h-[85%] overflow-auto hidden" data-ikhtisar-panel>
    <div class="rounded-xl p-4 card"> 
        
        <div class="font-semibold text-slate-800">
            Tren IKM
        </div>
        
        <div class="mt-4 space-y-4">
            @php
                $colors = $warnaTrenIKM;
                $twKeys = ['TW1', 'TW2', 'TW3', 'TW4'];
                $hasAnyData = false;
                // Definisi Romawi
                $romawi = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
            @endphp

            @foreach($ikmYears as $idxYear => $year)
                <div>
                    <div class="text-xs font-bold text-slate-600 mb-2 uppercase">
                        TAHUN {{ $year }}
                    </div>

                    <div class="space-y-2">
                        @foreach($twKeys as $idxTw => $twKey)
                            @php
                                $val  = $ikmSeries[$twKey][$idxYear] ?? null;
                                $meta = $metaSeries[$twKey][$idxYear] ?? '-';
                                $c    = $colors[$idxTw];
                                
                                if ($val !== null) $hasAnyData = true;

                                // UBAH LABEL DISINI: (idxTw mulai dari 0, jadi +1)
                                $labelTriwulan = "Triwulan " . ($romawi[$idxTw + 1] ?? '');
                            @endphp

                            @if($val !== null)
                                <div class="flex items-start gap-2">
                                    {{-- Dot Warna --}}
                                    <span class="w-3 h-3 rounded-full shrink-0 mt-1" style="background-color: {{ $c }}"></span>
                                    
                                    <div class="text-sm text-slate-700 flex-1 min-w-0 break-words">
                                        {{-- Tampilkan Label Romawi --}}
                                        <span class="font-semibold">{{ $labelTriwulan }}</span>: {{ number_format($val, 2) }}
                                        
                                        <div class="mt-0.5">
                                            Kategori Terbanyak: {{ $meta }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if(!$hasAnyData)
                <div class="text-sm text-slate-500">TIDAK ADA DATA.</div>
            @endif
        </div>

    </div>
</div>
            </div>
        </div>

        <div class="card lg:col-span-1">
            <div class="h-96">
                <div id="distribusiStatusChart"></div>
            </div>
        </div>

        <div class="lg:col-span-3 grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="card">
                <div class="h-96 relative" data-ikhtisar-wrap> {{-- Toggle Icon (pojok kanan atas) --}}
                    <button type="button" data-ikhtisar-toggle aria-expanded="false" title="Ikhtisar"
                        class="absolute top-[4px] right-0 z-50 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48"><path fill="currentColor" fill-rule="evenodd" d="M24 1.5c-7.403 0-12.592.239-15.857.466S2.281 4.66 1.991 7.96C1.742 10.794 1.5 15.074 1.5 21v22.652c0 2.99 3.507 4.603 5.778 2.657l6.96-5.966c2.687.093 5.927.157 9.762.157c7.403 0 12.592-.239 15.857-.466s5.862-2.693 6.152-5.993c.249-2.835.491-7.115.491-13.041s-.242-10.206-.491-13.041c-.29-3.3-2.887-5.765-6.152-5.993C36.592 1.74 31.403 1.5 24 1.5m2.882 25.452c2.218-1.238 3.36-2.588 3.538-4.88a96 96 0 0 1-2.028-.027c-1.33-.034-2.326-1.032-2.361-2.362a99 99 0 0 1-.031-2.61c0-1.16.015-2.069.035-2.77c.036-1.252.939-2.202 2.191-2.254C28.921 12.021 29.828 12 31 12s2.079.02 2.774.05c1.252.05 2.155 1.001 2.191 2.254c.02.695.035 1.594.035 2.74v5.03h-.023c-.296 4.235-3.425 6.759-6.97 7.85c-.499.153-1.05.08-1.427-.281c-.438-.419-.813-.937-1.088-1.368c-.295-.464-.09-1.055.39-1.323m-10.462-4.88c-.178 2.292-1.32 3.642-3.538 4.88c-.48.268-.685.86-.39 1.323c.275.431.65.949 1.088 1.368c.378.36.928.434 1.427.28c3.545-1.09 6.674-3.614 6.97-7.85H22v-5.029c0-1.146-.015-2.045-.035-2.74c-.036-1.253-.939-2.204-2.191-2.255C19.079 12.021 18.172 12 17 12s-2.079.02-2.774.05c-1.252.05-2.155 1.001-2.191 2.254c-.02.7-.035 1.608-.035 2.768c0 1.075.013 1.933.03 2.61c.036 1.33 1.031 2.329 2.362 2.363c.546.013 1.215.024 2.028.027" clip-rule="evenodd"/></svg>
                    </button>
                    {{-- Chart --}}
                    <div class="w-full h-full pr-4">  {{-- pr-12 = 48px, bisa kamu adjust --}}
                        <div id="skmPerPendidikanDonut" class="w-full h-full"></div>
                    </div>


                    {{--IKHTISAR (FLOATING OVERLAY) --}}
                    <div class="absolute top-6 right-0 z-50 w-[380px] max-w-[92%] max-h-[85%] overflow-auto hidden"
                        data-ikhtisar-panel>
                        <div class="card p-4"> {{-- (opsional) header + tombol close --}}
                            <div class="flex items-center justify-between mb-2">
                                <div class="font-semibold text-slate-800">BERDASARKAN PENDIDIKAN</div>
                            </div>
                            @php
                                $totalData = $skmPerPendidikan->sum('total');
                            @endphp
                            <div class="mt-1 text-sm text-slate-600">TOTAL DATA={{ $totalData }} </div>
                            <div class="mt-4 space-y-2">
                                @forelse($skmPerPendidikan as $row)
                                    @php
                                        // 1. Ambil Label (Pastikan Uppercase biar match dengan key array)
                                        $labelRaw = $row->pendidikan_kategori ?? 'LAINNYA';
                                        $label = mb_strtoupper($labelRaw, 'UTF-8');

                                        // 2. Ambil Warna dari Controller
                                        // Jika tidak ketemu, pakai abu-abu default
                                        $c = $pendidikanColors[$label] ?? '#cccccc';

                                        $pct = $totalData ? ($row->total / $totalData * 100) : 0;
                                    @endphp

                                    <div class="flex items-start gap-2">
                                        <span class="w-3 h-3 rounded-full shrink-0 mt-1" style="background-color: {{ $c }}"></span>
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
            <div class="card">
                <div class="h-96 relative" data-ikhtisar-wrap> {{-- Toggle Icon (pojok kanan atas) --}}
                    <button type="button" data-ikhtisar-toggle aria-expanded="false" title="Ikhtisar"
                        class="absolute top-[4px] right-0 z-50 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48"><path fill="currentColor" fill-rule="evenodd" d="M24 1.5c-7.403 0-12.592.239-15.857.466S2.281 4.66 1.991 7.96C1.742 10.794 1.5 15.074 1.5 21v22.652c0 2.99 3.507 4.603 5.778 2.657l6.96-5.966c2.687.093 5.927.157 9.762.157c7.403 0 12.592-.239 15.857-.466s5.862-2.693 6.152-5.993c.249-2.835.491-7.115.491-13.041s-.242-10.206-.491-13.041c-.29-3.3-2.887-5.765-6.152-5.993C36.592 1.74 31.403 1.5 24 1.5m2.882 25.452c2.218-1.238 3.36-2.588 3.538-4.88a96 96 0 0 1-2.028-.027c-1.33-.034-2.326-1.032-2.361-2.362a99 99 0 0 1-.031-2.61c0-1.16.015-2.069.035-2.77c.036-1.252.939-2.202 2.191-2.254C28.921 12.021 29.828 12 31 12s2.079.02 2.774.05c1.252.05 2.155 1.001 2.191 2.254c.02.695.035 1.594.035 2.74v5.03h-.023c-.296 4.235-3.425 6.759-6.97 7.85c-.499.153-1.05.08-1.427-.281c-.438-.419-.813-.937-1.088-1.368c-.295-.464-.09-1.055.39-1.323m-10.462-4.88c-.178 2.292-1.32 3.642-3.538 4.88c-.48.268-.685.86-.39 1.323c.275.431.65.949 1.088 1.368c.378.36.928.434 1.427.28c3.545-1.09 6.674-3.614 6.97-7.85H22v-5.029c0-1.146-.015-2.045-.035-2.74c-.036-1.253-.939-2.204-2.191-2.255C19.079 12.021 18.172 12 17 12s-2.079.02-2.774.05c-1.252.05-2.155 1.001-2.191 2.254c-.02.7-.035 1.608-.035 2.768c0 1.075.013 1.933.03 2.61c.036 1.33 1.031 2.329 2.362 2.363c.546.013 1.215.024 2.028.027" clip-rule="evenodd"/></svg>
                    </button>
                    {{-- Chart --}}
                    <div class="w-full h-full pr-4">  {{-- pr-12 = 48px, bisa kamu adjust --}}
                        <div id="skmPerProfesiPie" class="w-full h-full"></div>
                    </div>


                    {{--IKHTISAR (FLOATING OVERLAY) --}}
                    <div class="absolute top-6 right-0 z-50 w-[380px] max-w-[92%] max-h-[85%] overflow-auto hidden"
                        data-ikhtisar-panel>
                        <div class="card p-4"> {{-- (opsional) header + tombol close --}}
                            <div class="flex items-center justify-between mb-2">
                                <div class="font-semibold text-slate-800">BERDASARKAN PROFESI</div>
                            </div>
                            @php
                                $totalData = $skmPerProfesi->sum('total');
                            @endphp
                            <div class="mt-1 text-sm text-slate-600">TOTAL DATA={{ $totalData }} </div>
                            <div class="mt-4 space-y-2">
                                @forelse($skmPerProfesi as $row)
                                    @php
                                        // 1. Ambil Label (Uppercase)
                                        $labelRaw = $row->profesi_kategori ?? 'LAINNYA';
                                        $label = mb_strtoupper($labelRaw, 'UTF-8');

                                        // 2. Ambil Warna dari Controller
                                        $c = $profesiColors[$label] ?? '#cccccc'; // Fallback ke abu-abu jika tidak ketemu

                                        $pct = $totalData ? ($row->total / $totalData * 100) : 0;
                                    @endphp

                                    <div class="flex items-start gap-2">
                                        <span class="w-3 h-3 rounded-full shrink-0 mt-1" style="background-color: {{ $c }}"></span>
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

        <div class="lg:col-span-3 grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="card">
                <div class="h-96 relative" data-ikhtisar-wrap>
                    <button type="button" data-ikhtisar-toggle aria-expanded="false" title="Ikhtisar"
                        class="absolute top-3 right-0 z-50 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48"><path fill="currentColor" fill-rule="evenodd" d="M24 1.5c-7.403 0-12.592.239-15.857.466S2.281 4.66 1.991 7.96C1.742 10.794 1.5 15.074 1.5 21v22.652c0 2.99 3.507 4.603 5.778 2.657l6.96-5.966c2.687.093 5.927.157 9.762.157c7.403 0 12.592-.239 15.857-.466s5.862-2.693 6.152-5.993c.249-2.835.491-7.115.491-13.041s-.242-10.206-.491-13.041c-.29-3.3-2.887-5.765-6.152-5.993C36.592 1.74 31.403 1.5 24 1.5m2.882 25.452c2.218-1.238 3.36-2.588 3.538-4.88a96 96 0 0 1-2.028-.027c-1.33-.034-2.326-1.032-2.361-2.362a99 99 0 0 1-.031-2.61c0-1.16.015-2.069.035-2.77c.036-1.252.939-2.202 2.191-2.254C28.921 12.021 29.828 12 31 12s2.079.02 2.774.05c1.252.05 2.155 1.001 2.191 2.254c.02.695.035 1.594.035 2.74v5.03h-.023c-.296 4.235-3.425 6.759-6.97 7.85c-.499.153-1.05.08-1.427-.281c-.438-.419-.813-.937-1.088-1.368c-.295-.464-.09-1.055.39-1.323m-10.462-4.88c-.178 2.292-1.32 3.642-3.538 4.88c-.48.268-.685.86-.39 1.323c.275.431.65.949 1.088 1.368c.378.36.928.434 1.427.28c3.545-1.09 6.674-3.614 6.97-7.85H22v-5.029c0-1.146-.015-2.045-.035-2.74c-.036-1.253-.939-2.204-2.191-2.255C19.079 12.021 18.172 12 17 12s-2.079.02-2.774.05c-1.252.05-2.155 1.001-2.191 2.254c-.02.7-.035 1.608-.035 2.768c0 1.075.013 1.933.03 2.61c.036 1.33 1.031 2.329 2.362 2.363c.546.013 1.215.024 2.028.027" clip-rule="evenodd"/></svg>
                    </button>
                    {{-- Chart --}}
                    <div class="w-full h-full pr-2">  {{-- pr-12 = 48px, bisa kamu adjust --}}
                        <div id="snilaiDistChart" class="w-full h-full"></div>
                    </div>

                    {{-- Ikhtisar --}}
                    <div class="absolute top-8 right-0 z-50 w-[420px] max-w-[92%] max-h-[85%] overflow-auto hidden" data-ikhtisar-panel>
                        <div class="rounded-xl p-4 card shadow-lg bg-white border border-gray-100">
                            
                            <div id="ikhtisarTitle" class="font-semibold text-slate-800 text-lg">
                                DISTRIBUSI UNSUR PELAYANAN
                            </div>

                            <div id="ikhtisarTotal" class="mt-1 text-sm text-slate-600 font-medium">
                                Total=0
                            </div>

                            {{-- BARU: Tempat untuk Legend "U = Unsur Pelayanan" --}}
                            <div id="ikhtisarLegend" class="mt-2 text-sm   pb-2 mb-2 hidden">
                                Keterangan: U = Unsur Pelayanan
                            </div>

                            <div id="ikhtisarList" class="mt-2 space-y-3"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="h-96 relative" data-ikhtisar-wrap>
                    <button type="button" data-ikhtisar-toggle aria-expanded="false" title="Ikhtisar"
                        class="absolute top-3 right-0 z-50 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48"><path fill="currentColor" fill-rule="evenodd" d="M24 1.5c-7.403 0-12.592.239-15.857.466S2.281 4.66 1.991 7.96C1.742 10.794 1.5 15.074 1.5 21v22.652c0 2.99 3.507 4.603 5.778 2.657l6.96-5.966c2.687.093 5.927.157 9.762.157c7.403 0 12.592-.239 15.857-.466s5.862-2.693 6.152-5.993c.249-2.835.491-7.115.491-13.041s-.242-10.206-.491-13.041c-.29-3.3-2.887-5.765-6.152-5.993C36.592 1.74 31.403 1.5 24 1.5m2.882 25.452c2.218-1.238 3.36-2.588 3.538-4.88a96 96 0 0 1-2.028-.027c-1.33-.034-2.326-1.032-2.361-2.362a99 99 0 0 1-.031-2.61c0-1.16.015-2.069.035-2.77c.036-1.252.939-2.202 2.191-2.254C28.921 12.021 29.828 12 31 12s2.079.02 2.774.05c1.252.05 2.155 1.001 2.191 2.254c.02.695.035 1.594.035 2.74v5.03h-.023c-.296 4.235-3.425 6.759-6.97 7.85c-.499.153-1.05.08-1.427-.281c-.438-.419-.813-.937-1.088-1.368c-.295-.464-.09-1.055.39-1.323m-10.462-4.88c-.178 2.292-1.32 3.642-3.538 4.88c-.48.268-.685.86-.39 1.323c.275.431.65.949 1.088 1.368c.378.36.928.434 1.427.28c3.545-1.09 6.674-3.614 6.97-7.85H22v-5.029c0-1.146-.015-2.045-.035-2.74c-.036-1.253-.939-2.204-2.191-2.255C19.079 12.021 18.172 12 17 12s-2.079.02-2.774.05c-1.252.05-2.155 1.001-2.191 2.254c-.02.7-.035 1.608-.035 2.768c0 1.075.013 1.933.03 2.61c.036 1.33 1.031 2.329 2.362 2.363c.546.013 1.215.024 2.028.027" clip-rule="evenodd"/></svg>
                    </button>
                    {{-- Chart --}}
                    <div class="w-full h-full pr-2">  {{-- pr-12 = 48px, bisa kamu adjust --}}
                        <div id="ikmServiceChart" class="w-full h-full"></div>
                    </div>

                    <div class="absolute top-8 right-0 z-50 w-[380px] max-w-[92%] max-h-[85%] overflow-auto hidden" data-ikhtisar-panel>
                        <div class="rounded-xl p-4 card shadow-lg bg-white border border-gray-100">
                            
                            {{-- ID Unik untuk Judul --}}
                            <div id="ikmIkhtisarTitle" class="font-semibold text-slate-800 text-lg mb-1">
                                IKM TIAP KATEGORI LAYANAN
                            </div>

                            {{-- ID Unik untuk List --}}
                            <div id="ikmIkhtisarList" class="space-y-2"></div>
                        </div>
                    </div>
                    
                </div>
            </div>
            

            
        </div>

        <div class="lg:col-span-3 grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="card">
                <div class="h-96 relative" data-ikhtisar-wrap>
                    <button type="button" data-ikhtisar-toggle aria-expanded="false" title="Ikhtisar"
                        class="absolute top-[10px] right-0 z-50 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48"><path fill="currentColor" fill-rule="evenodd" d="M24 1.5c-7.403 0-12.592.239-15.857.466S2.281 4.66 1.991 7.96C1.742 10.794 1.5 15.074 1.5 21v22.652c0 2.99 3.507 4.603 5.778 2.657l6.96-5.966c2.687.093 5.927.157 9.762.157c7.403 0 12.592-.239 15.857-.466s5.862-2.693 6.152-5.993c.249-2.835.491-7.115.491-13.041s-.242-10.206-.491-13.041c-.29-3.3-2.887-5.765-6.152-5.993C36.592 1.74 31.403 1.5 24 1.5m2.882 25.452c2.218-1.238 3.36-2.588 3.538-4.88a96 96 0 0 1-2.028-.027c-1.33-.034-2.326-1.032-2.361-2.362a99 99 0 0 1-.031-2.61c0-1.16.015-2.069.035-2.77c.036-1.252.939-2.202 2.191-2.254C28.921 12.021 29.828 12 31 12s2.079.02 2.774.05c1.252.05 2.155 1.001 2.191 2.254c.02.695.035 1.594.035 2.74v5.03h-.023c-.296 4.235-3.425 6.759-6.97 7.85c-.499.153-1.05.08-1.427-.281c-.438-.419-.813-.937-1.088-1.368c-.295-.464-.09-1.055.39-1.323m-10.462-4.88c-.178 2.292-1.32 3.642-3.538 4.88c-.48.268-.685.86-.39 1.323c.275.431.65.949 1.088 1.368c.378.36.928.434 1.427.28c3.545-1.09 6.674-3.614 6.97-7.85H22v-5.029c0-1.146-.015-2.045-.035-2.74c-.036-1.253-.939-2.204-2.191-2.255C19.079 12.021 18.172 12 17 12s-2.079.02-2.774.05c-1.252.05-2.155 1.001-2.191 2.254c-.02.7-.035 1.608-.035 2.768c0 1.075.013 1.933.03 2.61c.036 1.33 1.031 2.329 2.362 2.363c.546.013 1.215.024 2.028.027" clip-rule="evenodd"/></svg>
                    </button>
                    {{-- Chart --}}
                    <div class="w-full h-full pr-2">  {{-- pr-12 = 48px, bisa kamu adjust --}}
                        <div id="distribusiKepuasanChart" class="w-full h-full"></div>
                    </div>

                    {{-- Ikhtisar --}}
                    <div class="absolute top-8 right-0 z-50 w-[380px] max-w-[92%] max-h-[85%] overflow-auto hidden" data-ikhtisar-panel>
                        <div class="rounded-xl p-4 card">
                            <div id="ikhtisarTitleKepuasan" class="font-semibold text-slate-800 ">
                                DISTRIBUSI KEPUASAN TIAP KATEGORI LAYANAN
                            </div>

                            <div id="ikhtisarTotalKepuasan" class="mt-1 text-sm text-slate-600 ">
                                Total=0
                            </div>

                            <div id="ikhtisarListKepuasan" class="mt-4 space-y-2"></div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="card">
                <div class="h-96 relative " data-ikhtisar-wrap>
                    <button type="button" data-ikhtisar-toggle aria-expanded="false" title="Ikhtisar"
                        class="absolute top-[6px] right-0 z-50 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48"><path fill="currentColor" fill-rule="evenodd" d="M24 1.5c-7.403 0-12.592.239-15.857.466S2.281 4.66 1.991 7.96C1.742 10.794 1.5 15.074 1.5 21v22.652c0 2.99 3.507 4.603 5.778 2.657l6.96-5.966c2.687.093 5.927.157 9.762.157c7.403 0 12.592-.239 15.857-.466s5.862-2.693 6.152-5.993c.249-2.835.491-7.115.491-13.041s-.242-10.206-.491-13.041c-.29-3.3-2.887-5.765-6.152-5.993C36.592 1.74 31.403 1.5 24 1.5m2.882 25.452c2.218-1.238 3.36-2.588 3.538-4.88a96 96 0 0 1-2.028-.027c-1.33-.034-2.326-1.032-2.361-2.362a99 99 0 0 1-.031-2.61c0-1.16.015-2.069.035-2.77c.036-1.252.939-2.202 2.191-2.254C28.921 12.021 29.828 12 31 12s2.079.02 2.774.05c1.252.05 2.155 1.001 2.191 2.254c.02.695.035 1.594.035 2.74v5.03h-.023c-.296 4.235-3.425 6.759-6.97 7.85c-.499.153-1.05.08-1.427-.281c-.438-.419-.813-.937-1.088-1.368c-.295-.464-.09-1.055.39-1.323m-10.462-4.88c-.178 2.292-1.32 3.642-3.538 4.88c-.48.268-.685.86-.39 1.323c.275.431.65.949 1.088 1.368c.378.36.928.434 1.427.28c3.545-1.09 6.674-3.614 6.97-7.85H22v-5.029c0-1.146-.015-2.045-.035-2.74c-.036-1.253-.939-2.204-2.191-2.255C19.079 12.021 18.172 12 17 12s-2.079.02-2.774.05c-1.252.05-2.155 1.001-2.191 2.254c-.02.7-.035 1.608-.035 2.768c0 1.075.013 1.933.03 2.61c.036 1.33 1.031 2.329 2.362 2.363c.546.013 1.215.024 2.028.027" clip-rule="evenodd"/></svg>
                    </button>
                    {{-- Chart --}}
                    <div class="w-full h-full pr-4">  {{-- pr-12 = 48px, bisa kamu adjust --}}
                        <div id="nrrChart" class="w-full h-full"></div>
                    </div>

                    {{-- Table --}}
                    <div class="absolute top-8 right-0 z-50 w-[380px] max-w-[92%] max-h-[85%] overflow-auto hidden" data-ikhtisar-panel>
                        <div class="rounded-xl p-4 card">
                            <div class="font-semibold text-slate-800 ">
                                RERATA UNSUR PELAYANAN
                            </div>

                            <div class="mt-1 text-sm text-slate-600 ">
                                Skala 1–4
                            </div>

                            <div class="mt-4 space-y-2">
                                @php 
                                    $hasData = false; 

                                    // 1. Duplikasi array agar data asli tidak berubah
                                    $sortedUnsur = $namaUnsur;

                                    // 2. Logika Sorting: Besar ke Kecil (Descending)
                                    uksort($sortedUnsur, function($keyA, $keyB) use ($nrr) {
                                        $valA = (float) ($nrr[$keyA] ?? 0);
                                        $valB = (float) ($nrr[$keyB] ?? 0);
                                        
                                        // Perubahan di sini: B dibandingkan dengan A
                                        return $valB <=> $valA; 
                                    });
                                @endphp

                                {{-- Gunakan $sortedUnsur yang sudah diurutkan --}}
                                @foreach($sortedUnsur as $k => $nama) 
                                    @php
                                        $val = (float) ($nrr[$k] ?? 0);

                                        // 0 tidak tampil
                                        if ($val <= 0) continue;

                                        $hasData = true;
                                        
                                        // Catatan: $loop->index akan mengikuti urutan loop yang baru
                                        $c = $warnaRerataUnsurPelayanan[0];
                                        $label = mb_strtoupper($k ?? '', 'UTF-8'); 
                                        $pct = ($val / 4) * 100;
                                    @endphp

                                    <div class="flex items-start gap-2">
                                        <span class="w-3 h-3 rounded-full shrink-0 mt-1" style="background-color: {{ $c }}"></span>

                                        <div class="text-sm text-slate-700 flex-1 min-w-0 break-words">
                                            <span class="font-semibold">{{ $label }} {{ $nama }}</span>:
                                            {{ number_format($val, 2) }}
                                            <span>({{ number_format($pct, 2) }}%)</span>
                                        </div>
                                    </div>
                                @endforeach

                                @if(!$hasData)
                                    <div class="text-sm text-slate-500">TIDAK ADA DATA.</div>
                                @endif
                            </div>


                        </div>
                    </div>
                </div>

            </div>
            
        </div>

        <div class="lg:col-span-3 grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="card">
                <div class="h-96 relative" data-ikhtisar-wrap>
                    <button type="button" data-ikhtisar-toggle aria-expanded="false" title="Ikhtisar"
                        class="absolute top-[4px] right-0 z-50 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48"><path fill="currentColor" fill-rule="evenodd" d="M24 1.5c-7.403 0-12.592.239-15.857.466S2.281 4.66 1.991 7.96C1.742 10.794 1.5 15.074 1.5 21v22.652c0 2.99 3.507 4.603 5.778 2.657l6.96-5.966c2.687.093 5.927.157 9.762.157c7.403 0 12.592-.239 15.857-.466s5.862-2.693 6.152-5.993c.249-2.835.491-7.115.491-13.041s-.242-10.206-.491-13.041c-.29-3.3-2.887-5.765-6.152-5.993C36.592 1.74 31.403 1.5 24 1.5m2.882 25.452c2.218-1.238 3.36-2.588 3.538-4.88a96 96 0 0 1-2.028-.027c-1.33-.034-2.326-1.032-2.361-2.362a99 99 0 0 1-.031-2.61c0-1.16.015-2.069.035-2.77c.036-1.252.939-2.202 2.191-2.254C28.921 12.021 29.828 12 31 12s2.079.02 2.774.05c1.252.05 2.155 1.001 2.191 2.254c.02.695.035 1.594.035 2.74v5.03h-.023c-.296 4.235-3.425 6.759-6.97 7.85c-.499.153-1.05.08-1.427-.281c-.438-.419-.813-.937-1.088-1.368c-.295-.464-.09-1.055.39-1.323m-10.462-4.88c-.178 2.292-1.32 3.642-3.538 4.88c-.48.268-.685.86-.39 1.323c.275.431.65.949 1.088 1.368c.378.36.928.434 1.427.28c3.545-1.09 6.674-3.614 6.97-7.85H22v-5.029c0-1.146-.015-2.045-.035-2.74c-.036-1.253-.939-2.204-2.191-2.255C19.079 12.021 18.172 12 17 12s-2.079.02-2.774.05c-1.252.05-2.155 1.001-2.191 2.254c-.02.7-.035 1.608-.035 2.768c0 1.075.013 1.933.03 2.61c.036 1.33 1.031 2.329 2.362 2.363c.546.013 1.215.024 2.028.027" clip-rule="evenodd"/></svg>
                    </button>
                    {{-- Chart --}}
                    <div class="w-full h-full pr-4">  {{-- pr-12 = 48px, bisa kamu adjust --}}
                        <div id="distribusiIndikasiPungutanChart" class="w-full h-full"></div>
                    </div>

                    {{-- Ikhtisar --}}
                    <div class="absolute top-8 right-0 z-50 w-[380px] max-w-[92%] max-h-[85%] overflow-auto hidden" data-ikhtisar-panel>
                        @php
                            $totalData = (int) $skmPungutan->sum('total');
                        @endphp

                        <div class="rounded-xl p-4 card">
                            <div class="font-semibold text-slate-800 ">
                                DISTRIBUSI INDIKASI PUNGUTAN
                            </div>

                            @php
                                // 1. Hitung Total
                                $totalData = $skmPungutan->sum('total');

                                // 2. Hitung Jumlah Warna (Untuk Modulo)
                                $countColors = count($warna2Chart);
                                if ($countColors == 0) { $warna2Chart = ['#cccccc']; $countColors = 1; }
                            @endphp

                            <div class="mt-1 text-sm text-slate-600 ">
                                TOTAL DATA={{ $totalData }}
                            </div>

                            <div class="mt-4 space-y-2">
                                {{-- Loop Data Dinamis --}}
                                @forelse($skmPungutan as $i => $row)
                                    @php
                                        // 3. Logika Warna Berulang
                                        $c = $warna2Chart[$i % $countColors];
                                        
                                        // Hitung Persen
                                        $pct = $totalData > 0 ? ($row->total / $totalData * 100) : 0;
                                    @endphp

                                    <div class="flex items-start gap-2">
                                        {{-- Dot Warna --}}
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
                        class="absolute top-[4px] right-0 z-50 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48"><path fill="currentColor" fill-rule="evenodd" d="M24 1.5c-7.403 0-12.592.239-15.857.466S2.281 4.66 1.991 7.96C1.742 10.794 1.5 15.074 1.5 21v22.652c0 2.99 3.507 4.603 5.778 2.657l6.96-5.966c2.687.093 5.927.157 9.762.157c7.403 0 12.592-.239 15.857-.466s5.862-2.693 6.152-5.993c.249-2.835.491-7.115.491-13.041s-.242-10.206-.491-13.041c-.29-3.3-2.887-5.765-6.152-5.993C36.592 1.74 31.403 1.5 24 1.5m2.882 25.452c2.218-1.238 3.36-2.588 3.538-4.88a96 96 0 0 1-2.028-.027c-1.33-.034-2.326-1.032-2.361-2.362a99 99 0 0 1-.031-2.61c0-1.16.015-2.069.035-2.77c.036-1.252.939-2.202 2.191-2.254C28.921 12.021 29.828 12 31 12s2.079.02 2.774.05c1.252.05 2.155 1.001 2.191 2.254c.02.695.035 1.594.035 2.74v5.03h-.023c-.296 4.235-3.425 6.759-6.97 7.85c-.499.153-1.05.08-1.427-.281c-.438-.419-.813-.937-1.088-1.368c-.295-.464-.09-1.055.39-1.323m-10.462-4.88c-.178 2.292-1.32 3.642-3.538 4.88c-.48.268-.685.86-.39 1.323c.275.431.65.949 1.088 1.368c.378.36.928.434 1.427.28c3.545-1.09 6.674-3.614 6.97-7.85H22v-5.029c0-1.146-.015-2.045-.035-2.74c-.036-1.253-.939-2.204-2.191-2.255C19.079 12.021 18.172 12 17 12s-2.079.02-2.774.05c-1.252.05-2.155 1.001-2.191 2.254c-.02.7-.035 1.608-.035 2.768c0 1.075.013 1.933.03 2.61c.036 1.33 1.031 2.329 2.362 2.363c.546.013 1.215.024 2.028.027" clip-rule="evenodd"/></svg>
                    </button>
                    {{-- Chart --}}
                    <div class="w-full h-full pr-4">  {{-- pr-12 = 48px, bisa kamu adjust --}}
                        <div id="distribusiLoyalitasRekomenRespondenChart" class="w-full h-full"></div>
                    </div>

                    {{-- Ikhtisar --}}
                    <div class="absolute top-8 right-0 z-50 w-[380px] max-w-[92%] max-h-[85%] overflow-auto hidden" data-ikhtisar-panel>
                        @php
                            // Hitung total dari collection
                            $totalData = $skmRekomen->sum('total');
                        @endphp

                        <div class="rounded-xl p-4 card">
                            <div class="font-semibold text-slate-800 ">
                                LOYALITAS REKOMENDASI PENGGUNA
                            </div>

                            <div class="mt-1 text-sm text-slate-600 ">
                                TOTAL DATA={{ $totalData }}
                            </div>

                            <div class="mt-4 space-y-2">
    @php
        // 1. HITUNG TOTAL DATA DULU (Wajib)
        $totalData = $skmRekomen->sum('total');

        // 2. HITUNG JUMLAH WARNA (Untuk looping)
        $countColors = count($warna2Chart);
        // Fallback jika array warna kosong
        if ($countColors == 0) { $warna2Chart = ['#cccccc']; $countColors = 1; }
    @endphp

    {{-- Loop Data Dinamis --}}
    @forelse($skmRekomen as $i => $row)
        @php
            // 3. LOGIKA WARNA BERULANG (MODULO)
            // Agar tidak error "Undefined array key" jika data > jumlah warna
            $c = $warna2Chart[$i % $countColors];
            
            // Hitung Persen
            $pct = $totalData > 0 ? ($row->total / $totalData * 100) : 0;
        @endphp

        <div class="flex items-start gap-2">
            {{-- Dot Warna --}}
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
                        class="absolute top-[6px] right-0 z-50 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48"><path fill="currentColor" fill-rule="evenodd" d="M24 1.5c-7.403 0-12.592.239-15.857.466S2.281 4.66 1.991 7.96C1.742 10.794 1.5 15.074 1.5 21v22.652c0 2.99 3.507 4.603 5.778 2.657l6.96-5.966c2.687.093 5.927.157 9.762.157c7.403 0 12.592-.239 15.857-.466s5.862-2.693 6.152-5.993c.249-2.835.491-7.115.491-13.041s-.242-10.206-.491-13.041c-.29-3.3-2.887-5.765-6.152-5.993C36.592 1.74 31.403 1.5 24 1.5m2.882 25.452c2.218-1.238 3.36-2.588 3.538-4.88a96 96 0 0 1-2.028-.027c-1.33-.034-2.326-1.032-2.361-2.362a99 99 0 0 1-.031-2.61c0-1.16.015-2.069.035-2.77c.036-1.252.939-2.202 2.191-2.254C28.921 12.021 29.828 12 31 12s2.079.02 2.774.05c1.252.05 2.155 1.001 2.191 2.254c.02.695.035 1.594.035 2.74v5.03h-.023c-.296 4.235-3.425 6.759-6.97 7.85c-.499.153-1.05.08-1.427-.281c-.438-.419-.813-.937-1.088-1.368c-.295-.464-.09-1.055.39-1.323m-10.462-4.88c-.178 2.292-1.32 3.642-3.538 4.88c-.48.268-.685.86-.39 1.323c.275.431.65.949 1.088 1.368c.378.36.928.434 1.427.28c3.545-1.09 6.674-3.614 6.97-7.85H22v-5.029c0-1.146-.015-2.045-.035-2.74c-.036-1.253-.939-2.204-2.191-2.255C19.079 12.021 18.172 12 17 12s-2.079.02-2.774.05c-1.252.05-2.155 1.001-2.191 2.254c-.02.7-.035 1.608-.035 2.768c0 1.075.013 1.933.03 2.61c.036 1.33 1.031 2.329 2.362 2.363c.546.013 1.215.024 2.028.027" clip-rule="evenodd"/></svg>
                    </button>
                    {{-- Chart --}}
                    <div class="w-full h-full pr-4">  {{-- pr-12 = 48px, bisa kamu adjust --}}
                        <div id="distribusiProporsiRespondenChart" class="w-full h-full"></div>
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
                                @php
                                    // 1. HITUNG TOTAL DATA (Wajib, supaya tidak error undefined variable)
                                    $totalData = $distribusiGender->sum('total');

                                    // 2. HITUNG JUMLAH WARNA (Untuk logika looping warna)
                                    $countColors = count($warna2Chart);
                                    // Cegah error jika array warna kosong
                                    if ($countColors == 0) { $warna2Chart = ['#cccccc']; $countColors = 1; }
                                @endphp

                                {{-- Loop Data Dinamis --}}
                                @forelse($distribusiGender as $i => $row)
                                    @php
                                        // 3. LOGIKA WARNA BERULANG (MODULO)
                                        // Jika data ke-2 tapi warna cuma 1, dia akan kembali ke warna ke-1 (index 0)
                                        $colorIndex = $i % $countColors; 
                                        $c = $warna2Chart[$colorIndex];

                                        // Hitung Persen (Cegah division by zero)
                                        $pct = $totalData > 0 ? ($row->total / $totalData * 100) : 0;
                                    @endphp

                                    <div class="flex items-start gap-2">
                                        {{-- Dot Warna --}}
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
        </div>
        

        <div class="card !p-0 lg:col-span-3" id="filter-skm">
            <div class="border-b-3 border-gray-500 mb-5" id="tabel-responden">
                <h3 class="font-bold text-2xl p-4">Hasil Survei Kepuasan Masyarakat</h3>
            </div>
            <div class="px-4 flex flex-col sm:flex-row justify-between mb-5">
                <h1 class="text-xl">Indeks Kepuasan Masyarakat</h1>
                <div class="border rounded-lg border-gray-300 px-4">
                    <h1 class="text-gray-500">
                        @php
                            $filter = request('date_filter');
                            $year   = request('year', date('Y'));
                            
                            // Default Title (Jika filter kosong / else)
                            $title  = '1 Bulan Terakhir'; 

                            if ($filter == 'today') {
                                $title = 'Hari Ini';
                            } elseif ($filter == 'last_7_days') {
                                $title = '7 Hari Terakhir';
                            } elseif ($filter == 'last_month') {
                                $title = '1 Bulan Terakhir';
                            } elseif ($filter == 'whole_year') {
                                $title = '1 Tahun Terakhir '.$year;
                            } elseif ($filter == 'all_time') {
                                $title = 'Semua Waktu';

                            // --- LOGIKA TRIWULAN ---
                            } elseif ($filter == 'all_triwulan') {
                                $title = "Semua Triwulan Tahun $year";
                            } elseif (Str::startsWith($filter, 'triwulan_')) {
                                $romawi = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
                                $num = explode('_', $filter)[1] ?? 1;
                                $title = "Triwulan " . ($romawi[$num] ?? '') . " Tahun $year";

                            // --- LOGIKA SEMESTER ---
                            } elseif ($filter == 'all_semester') {
                                $title = "Semua Semester Tahun $year";
                            } elseif (Str::startsWith($filter, 'semester_')) {
                                $romawi = [1 => 'I', 2 => 'II'];
                                $num = explode('_', $filter)[1] ?? 1;
                                $title = "Semester " . ($romawi[$num] ?? '') . " Tahun $year";

                            // --- LOGIKA CUSTOM ---
                            } elseif ($filter == 'custom') {
                                $start = request('start_date');
                                $end   = request('end_date');
                                if ($start && $end) {
                                    // Format tanggal agar cantik (misal: 01 Jan 2024 - 31 Jan 2024)
                                    $sParams = \Carbon\Carbon::parse($start)->translatedFormat('d M Y');
                                    $eParams = \Carbon\Carbon::parse($end)->translatedFormat('d M Y');
                                    $title = "Periode: $sParams - $eParams";
                                } else {
                                    $title = "Periode Rentang Kustom";
                                }
                            }
                        @endphp

                        {{-- Tampilkan Judul Final --}}
                        {{ $title }}
                    </h1>
                </div>
            </div>
            <div class="px-4 flex flex-col sm:flex-row">
                <div class="grow h-auto"> 
                    <div class="flex flex-col md:flex-row items-start h-full">
                        <div class="flex-shrink-0 relative flex justify-center items-center bg-white rounded-lg p-2">
            <div id="donutChartPeringkat"></div>

            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                <h1 class="text-4xl font-black  leading-none">
                    {{ number_format($jumlahNRRTertimbang, 2, ',', '.') }}
                </h1>
                <p class="text-[11px] text-gray-500 font-medium text-center mt-1 leading-tight">
                    Nilai Rerata<br>Tertimbang
                </p>
            </div>
        </div>
                        <div class="grow flex flex-col bg-white rounded-lg overflow-hidden w-full md:w-auto me-2"> 
                            <div class="overflow-x-auto overflow-y-auto grow h-full">
                                <table class="w-full md:min-w-0 text-sm text-left text-gray-500"> 
                                    <thead class="text-xs text-gray-700  bg-gray-50 sticky top-0 z-10">
                                        <tr>
                                            <th scope="col" class="px-2 py-2 text-[12px] ">Unsur Pelayanan</th>
                                            <th scope="col" class="px-2 py-2 text-[12px]  text-center">Rerata</th>
                                            <th scope="col" class="px-2 py-2 text-[12px]  text-center">Kategori</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach(collect($chartPeringkatLayanan)->sortByDesc('nrr')->take(5) as $item)
                                        <tr class="bg-white hover:bg-gray-50 transition-colors">
                                            <td class="px-2 py-2 ">
                                                <div class="flex items-center gap-1">
                                                    <span class="flex-shrink-0 w-2 h-2 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                                                    <span class="font-medium text-gray-900 text-[12px] whitespace-nowrap">{{ $item['unsur'] }}</span>
                                                </div>
                                            </td>
                                            <td class="px-2 py-2 text-center">
                                                <span class="font-mono font-bold text-gray-800 text-[12px] ">{{ number_format($item['nrr'], 2) }}</span>
                                            </td>
                                            <td class="px-2 py-2 text-center">
                                                <span class="bg-gray-100 text-gray-800 text-xs font-bold px-2.5 py-0.5  text-[12px] rounded border border-gray-200">{{ $item['kategori'] }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="grow flex flex-col bg-white rounded-lg overflow-hidden w-full md:w-auto me-2"> 
                            <div class="overflow-x-auto overflow-y-auto grow h-full">
                                <table class="w-full md:min-w-0 text-sm text-left text-gray-500"> 
                                    <thead class="text-xs text-gray-700  bg-gray-50 sticky top-0 z-10">
                                        <tr>
                                            <th scope="col" class="px-2 py-2 text-[12px] ">Unsur Pelayanan</th>
                                            <th scope="col" class="px-2 py-2 text-[12px]  text-center">Rerata</th>
                                            <th scope="col" class="px-2 py-2 text-[12px]  text-center">Kategori</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach(collect($chartPeringkatLayanan)->sortByDesc('nrr')->slice(5) as $item)
                                        <tr class="bg-white hover:bg-gray-50 transition-colors">
                                            <td class="px-2 py-2 ">
                                                <div class="flex items-center gap-1">
                                                    <span class="flex-shrink-0 w-2 h-2 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                                                    <span class="font-medium text-gray-900 text-[12px] whitespace-nowrap">{{ $item['unsur'] }}</span>
                                                </div>
                                            </td>
                                            <td class="px-2 py-2 text-center">
                                                <span class="font-mono font-bold text-gray-800 text-[12px] ">{{ number_format($item['nrr'], 2) }}</span>
                                            </td>
                                            <td class="px-2 py-2 text-center">
                                                <span class="bg-gray-100 text-gray-800 text-xs font-bold px-2.5 py-0.5  text-[12px] rounded border border-gray-200">{{ $item['kategori'] }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex items-center h-full">
                            <div>
                                <div class="mb-4">
                                    <h1 class="font-bold text-3xl">{{ number_format($ikm, 2, ',', '.') }}</h1>
                                    <h1 class="text-gray-600 text-sm">IKM Unit Pelayanan</h1>
                                </div>
                                <div class="">
                                    <h1 class="font-bold text-3xl">{{ $mutu }}</h1>
                                    <h1 class="text-gray-600 text-sm">Mutu Pelayanan</h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card m-4">
                <div class="flex sm:flex-row flex-col items-center justify-between">
                    <h1 class="text-xl">Daftar Responden</h1>
                </div>
                <div class="overflow-x-auto mt-4">
                    <div class="rounded-lg overflow-auto border border-gray-300">
                        <table class="min-w-full text-sm" id="responden-table">
                            <thead class="bg-[#6366f1] text-white">
                                <tr>
                                    <th rowspan="2" class="border-r border-gray-300/50 px-4 py-2 w-12">NO</th>
                                    <th colspan="9" class="px-4 py-2 text-center">NILAI UNSUR PELAYANAN</th>
                                </tr>
                                <tr class="bg-[#6366f1] text-xs border-t border-gray-300/50">
                                    <th class="border-r border-gray-300/50 px-2 py-2 text-center">U1</th>
                                    <th class="border-r border-gray-300/50 px-2 py-2 text-center">U2</th>
                                    <th class="border-r border-gray-300/50 px-2 py-2 text-center">U3</th>
                                    <th class="border-r border-gray-300/50 px-2 py-2 text-center">U4</th>
                                    <th class="border-r border-gray-300/50 px-2 py-2 text-center">U5</th>
                                    <th class="border-r border-gray-300/50 px-2 py-2 text-center">U6</th>
                                    <th class="border-r border-gray-300/50 px-2 py-2 text-center">U7</th>
                                    <th class="border-r border-gray-300/50 px-2 py-2 text-center">U8</th>
                                    <th class="px-2 py-2 text-center">U9</th>
                                </tr>
                            </thead>
                            <tbody id="table-body" class="bg-white divide-y divide-gray-200">
                                @foreach ($skmData as $index => $row)
                                    <tr class="item-row {{ $loop->odd ? 'bg-white hover:bg-gray-50' : 'bg-gray-50 hover:bg-gray-100' }}">
                                        <td class="border-r border-gray-300 px-4 py-2 text-center font-medium">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="border-r border-gray-300 px-2 py-2 text-center">{{ $row->syarat_pengurusan_pelayanan }}</td>
                                        <td class="border-r border-gray-300 px-2 py-2 text-center">{{ $row->sistem_mekanisme_dan_prosedur_pelayanan }}</td>
                                        <td class="border-r border-gray-300 px-2 py-2 text-center">{{ $row->waktu_penyelesaian_pelayanan }}</td>
                                        <td class="border-r border-gray-300 px-2 py-2 text-center">{{ $row->kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan }}</td>
                                        <td class="border-r border-gray-300 px-2 py-2 text-center">{{ $row->kesesuaian_hasil_pelayanan }}</td>
                                        <td class="border-r border-gray-300 px-2 py-2 text-center">{{ $row->kemampuan_petugas_dalam_memberikan_pelayanan }}</td>
                                        <td class="border-r border-gray-300 px-2 py-2 text-center">{{ $row->kesopanan_dan_keramahan_petugas }}</td>
                                        <td class="border-r border-gray-300 px-2 py-2 text-center">{{ $row->penanganan_pengaduan_saran_dan_masukan }}</td>
                                        <td class="px-2 py-2 text-center">{{ $row->sarana_dan_prasarana_penunjang_pelayanan }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 flex sm:flex-row flex-col justify-between items-center">
                    <span class="text-sm text-gray-600">
                        Menampilkan <span id="start-index">0</span> sampai <span id="end-index">0</span> dari <span id="total-rows">0</span> data
                    </span>
                    <div id="pagination-controls" class="flex space-x-1">
                        </div>
                </div>
            </div>

        </div>
    </div>


@endsection


@push('scripts')
    {{-- Import library dari CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        const mapNamaUnsur = @json($namaUnsur);

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

            // Ambil data layanan terbanyak dari PHP
            const metaData = @json($metaSeries); 

            const mapTriwulan = {
                'TW1': 'Triwulan I',
                'TW2': 'Triwulan II',
                'TW3': 'Triwulan III',
                'TW4': 'Triwulan IV'
            };

            // Definisikan Warna Custom sesuai request
            const ikmOptions = {
                title: {
                    text: 'Tren IKM',
                    style: {
                        fontFamily: 'Inter, sans-serif',
                        color: '#000',
                        fontWeight: 600,
                        fontSize: '17px'
                    }
                },
                series: [
                    { name: "Triwulan I",   data: @json($ikmSeries['TW1']) },
                    { name: "Triwulan II",  data: @json($ikmSeries['TW2']) },
                    { name: "Triwulan III", data: @json($ikmSeries['TW3']) },
                    { name: "Triwulan IV",  data: @json($ikmSeries['TW4']) },
                ],
                chart: {
                    type: "bar",
                    height: "100%",
                    ...chartTheme, // Pastikan variable ini ada
                    toolbar: {
                        show: true,
                        export: {
                            png: { filename: 'Tren IKM' },
                            svg: { filename: 'Tren IKM' },
                            csv: { filename: 'Tren IKM' }
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: false,
                    }
                },
                colors: @json($warnaTrenIKM), 

                dataLabels: {
                    enabled: false,
                    formatter: (val) => (val === null ? "" : Number(val).toFixed(2)),
                    style: {
                        fontSize: "12px",
                        colors: ["#fff"],
                        fontWeight: 400
                    }
                },

                xaxis: {
                    categories: @json($ikmYears),
                    title: { text: "TAHUN", style: { fontWeight: 400 } },
                    labels: { style: { colors: textColors } }
                },

                yaxis: {
                    min: 0,
                    max: 100,
                    tickAmount: 4,
                    title: { text: "NILAI IKM", style: { fontWeight: 400 } },
                    labels: {
                        style: { colors: textColors },
                        formatter: (val) => Math.round(val)
                    }
                },

                grid: { borderColor: gridColors },

                tooltip: {
                    // Kita set 'shared: false' agar tooltip fokus ke satu bar saja (gaya default bar chart)
                    shared: false,
                    intersect: true,
                    
                    custom: function({ series, seriesIndex, dataPointIndex, w }) {
                        // 1. Ambil Data
                        const val = series[seriesIndex][dataPointIndex];
                        if (val === null) return;

                        // Ambil atribut visual & data
                        const year = w.globals.labels[dataPointIndex];        
                        const seriesName = w.globals.seriesNames[seriesIndex]; // Ini sekarang sudah jadi "Triwulan I", dst
                        const color = w.globals.colors[seriesIndex];          

                        // Kita perlu mapping balik untuk mengambil metaData karena key di metaData masih 'TW1', 'TW2', dst.
                        // Urutan seriesIndex: 0=TW1, 1=TW2, 2=TW3, 3=TW4
                        const originalKeys = ['TW1', 'TW2', 'TW3', 'TW4'];
                        const keyTw = originalKeys[seriesIndex]; // Ambil key asli untuk lookup metaData

                        const kategori = metaData[keyTw][dataPointIndex] || '-'; 

                        return `
                            <div class="apexcharts-tooltip-box" style="background: #fff; border: 1px solid #e3e3e3; box-shadow: 2px 2px 6px -4px #999; border-radius: 5px; font-family: Helvetica, Arial, sans-serif;">
                                
                                <div class="apexcharts-tooltip-title" style="background: #eceff1; border-bottom: 1px solid #ddd; font-size: 12px; margin: 0;">
                                    ${year}
                                </div>

                                <div style="">
                                    <div style="display: flex; align-items: flex-start;">
                                        <span style="background-color: ${color}; min-width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-top: 4px; margin-right: 10px;"></span>
                                        <div>
                                            <div style="font-size: 12px; color: #373d3f;">
                                                <span style="font-weight: 600;">${seriesName}: </span> 
                                                <span>${Number(val).toFixed(2)}</span>
                                            </div>

                                            <div style="font-size: 11px;  margin-top: 4px; max-width: 200px; white-space: normal; line-height: 1.4;">
                                                <span style="font-weight: 600;">Kategori Terbanyak:</span> ${kategori}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                },

                legend: {
                    show: true,
                    position: "bottom",
                    labels: { colors: textColors }
                }
            };

            new ApexCharts(document.querySelector("#ikmChart"), ikmOptions).render();

            // 2. Grafik Distribusi Status (Donut Chart)
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
                series: [@json($skmPublik), @json($skmPrivat)],
                labels: ["PUBLIK", "PRIVAT"], // ✅ capslock
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
                colors: ['#16a34a', '#dc2626'],
                legend: {
                    position: 'bottom',
                    labels: { colors: textColors }
                }
            };

            new ApexCharts(document.querySelector("#distribusiStatusChart"), statusOptions).render();


            const nrr = @json($nrr);             // {U1: 3.1, U2: 3.2, ...}
            const namaUnsurMap = @json($namaUnsur);  // {U1: "Kesesuaian...", ...}

            // 1. Ambil keys mentah dulu (U1..U9)
            let rawKeys = @json(array_keys($namaUnsur));

            // 2. SORTING LOGIC: Urutkan Keys berdasarkan nilai NRR (Desc / Besar ke Kecil)
            rawKeys.sort((a, b) => {
                const valA = Number(nrr?.[a] ?? 0);
                const valB = Number(nrr?.[b] ?? 0);
                return valB - valA; // B - A = Descending (Besar ke Kecil)
            });

            // 3. Tetapkan hasil sort ke variable nrrKeys
            const nrrKeys = rawKeys; 

            // 4. Buat data series mengikuti urutan keys yang sudah disortir
            const nrrData = nrrKeys.map(k => {
                const v = Number(nrr?.[k] ?? 0);
                return Math.round(v * 100) / 100; 
            });

            const nrrOptions = {
                title: {
                    text: 'Rerata Unsur Pelayanan',
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
                series: [{ name: '', data: nrrData }],
                chart: {
                    type: 'bar',
                    height: '100%',
                    ...chartTheme,
                    toolbar: {
                        show: true,
                        export: {
                            png: { filename: 'Rerata Unsur Pelayanan' },
                            svg: { filename: 'Rerata Unsur Pelayanan' },
                            csv: { filename: 'Rerata Unsur Pelayanan' }
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: false,     // ✅ vertical bar
                        distributed: true,
                        columnWidth: '55%',
                        dataLabels: {
                            position: 'top' 
                        }
                    }
                },
                colors: @json($warnaRerataUnsurPelayanan),
                dataLabels: {
                    enabled: true,
                    formatter: (val) => Number(val).toFixed(2),
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ['#000000']
                    }
                },
                xaxis: {
                    categories: nrrKeys,
                    labels: {
                        style: {
                            colors: textColors,
                            fontSize: '11px'
                        },
                        rotate: -45,        // Miringkan 45 derajat
                        rotateAlways: true, // Paksa miring (jangan auto-rotate)
                        trim: false,        // ✅ PENTING: Matikan pemotongan teks
                        maxHeight: 140,     // ✅ PENTING: Beri batas tinggi maksimal lebih lega (default biasanya kecil)
                        formatter: function (val) {
                            const nama = namaUnsurMap?.[val] || '';
                            return `${val} ${nama}`;
                        }
                    }
                },
                // Tambahkan pengaturan Grid ini agar label paling bawah tidak "mepet" batas div
                grid: {
                    borderColor: gridColors,
                    padding: {
                        left: 75,   // Jarak aman kiri
                    }
                },
                yaxis: {
                    min: 0,
                    max: 4,
                    tickAmount: 4,
                    labels: {
                        style: { colors: textColors },
                        formatter: (v) => Number(v).toFixed(0)
                    }
                },
                tooltip: {
                    theme: isDarkMode ? 'dark' : 'light',
                    y: {
                        formatter: function (val, opts) {
                            const key = nrrKeys[opts.dataPointIndex] || '';
                            const nama = namaUnsurMap?.[key] || key;
                            return `${nama}: ${Number(val).toFixed(2)}`;
                        }
                    }
                },
                legend: { show: false }
            };

            new ApexCharts(document.querySelector("#nrrChart"), nrrOptions).render();

            // ============================================================
            // 1. TERIMA VARIABEL DARI CONTROLLER (NAMA VARIABEL UNIK)
            // ============================================================
            const mapUnsurColorsSnilai = @json($unsurColors ?? []); 
            const colorScoreSnilai     = @json($scoreColor ?? '#1e89ef'); 

            // ============================================================
            // 2. LOGIKA PEWARNAAN (FUNGSI UNIK)
            // ============================================================

            function normalizePointSnilai(p) {
                let name = '';
                let val = 0;

                // Cek format data
                if (Array.isArray(p)) {
                    name = p[0];
                    val = Number(p[1]) || 0;
                } else {
                    name = p.name || p.category || '';
                    val = Number(p.y) || 0;
                }

                // Tentukan Warna Default
                let assignedColor = '#cccccc'; 

                // A. Cek apakah ini Unsur (U1-U9)?
                if (mapUnsurColorsSnilai[name]) {
                    assignedColor = mapUnsurColorsSnilai[name];
                }
                // B. Cek apakah ini Skor (1-4)?
                else if (['1', '2', '3', '4'].includes(String(name))) {
                    assignedColor = colorScoreSnilai;
                }

                // Return object point
                if (Array.isArray(p)) {
                    return { name: name, y: val, color: assignedColor };
                }
                return { ...p, color: assignedColor };
            }

            function applyColorsSnilai(points) {
                return (points || []).map(p => normalizePointSnilai(p));
            }

            // ============================================================
            // 3. PROSES DATA DARI PHP
            // ============================================================
            
            // Proses Main Points (U1-U9)
            const rawMainSnilai = @json($snilaiSeries ?? []); 
            const mainPointsSnilai = applyColorsSnilai(rawMainSnilai);

            // Proses Drill Points (Skor 1-4)
            const rawDrillSnilai = @json($distSeries ?? []);
            const drillSeriesSnilai = (rawDrillSnilai || []).map(s => ({
                ...s,
                data: applyColorsSnilai(s.data || [])
            }));

            // ============================================================
            // 4. IKHTISAR & HIGHCHARTS (FUNGSI UNIK)
            // ============================================================

            function renderIkhtisarSnilai(title, points, isPercent = false) {
    const titleEl  = document.getElementById('ikhtisarTitle');
    const totalEl  = document.getElementById('ikhtisarTotal');
    const listEl   = document.getElementById('ikhtisarList');
    const legendEl = document.getElementById('ikhtisarLegend'); // Element baru

    if (!titleEl || !totalEl || !listEl || !legendEl) return;

    // 1. Set Title
    titleEl.textContent = title;

    // 2. Hitung Total
    const totalVal = points.reduce((a, p) => a + (Number(p.y) || 0), 0);
    totalEl.textContent = isPercent
        ? `Total = ${(Number(totalVal) || 0).toFixed(2)}%`
        : `Total = ${Math.round(Number(totalVal) || 0)}`;

    // 3. Cek apakah ini tampilan Utama (Unsur) atau Drilldown
    // Indikatornya: jika point pertama namanya ada di mapNamaUnsur (misal U1), berarti Main View.
    const isMainView = points.length > 0 && mapNamaUnsur.hasOwnProperty(points[0].name);

    // Tampilkan/Sembunyikan Legend "U = Unsur Pelayanan"
    if (isMainView) {
        legendEl.classList.remove('hidden');
    } else {
        legendEl.classList.add('hidden');
    }

    // 4. Render List Kosong
    if (!points.length) {
        listEl.innerHTML = `<div class="text-sm text-slate-500">TIDAK ADA DATA.</div>`;
        return;
    }

    // 5. Render List Item
    listEl.innerHTML = points.map(p => {
        const c = p.color;
        let rawName = (p.name ?? '').toString(); // Misal: "U1" atau "1"
        let displayName = rawName.toLocaleUpperCase('id-ID');

        // LOGIKA BARU: Jika nama ada di map (U1..U9), gabungkan dengan nama panjangnya
        // Format request: "U1 = Kesesuaian Persyaratan"
        if (mapNamaUnsur[rawName]) {
            displayName = `${rawName} = ${mapNamaUnsur[rawName]}`;
        }

        // Format Nilai
        const val = isPercent
            ? `${(Number(p.y) || 0).toFixed(2)}%`
            : `${Math.round(Number(p.y) || 0)}`;

        return `
            <div class="flex items-start gap-2">
                <span class="w-3 h-3 rounded-full shrink-0 mt-1.5" style="background-color:${c}"></span>
                <div class="text-sm text-slate-700 flex-1 min-w-0 break-words leading-tight">
                    <span class="font-semibold block mb-0.5">${displayName}</span>
                    <span class="text-slate-500">Nilai: ${val}</span>
                </div>
            </div>
        `;
    }).join('');
}

            // Initial Render
            renderIkhtisarSnilai('DISTRIBUSI UNSUR PELAYANAN', mainPointsSnilai, false);

            Highcharts.chart('snilaiDistChart', {
                chart: {
                    type: 'line',
                    events: {
                        drilldown: function (e) {
                            const so = e.seriesOptions;
                            // Proses warna on-the-fly dengan fungsi unik
                            const pts = applyColorsSnilai((so && so.data) ? so.data : []);
                            const isPercent = so && (so.type === 'column' || so.type === 'bar');
                            const title = (so && so.name) ? `IKHTISAR ${so.name}` : 'DISTRIBUSI UNSUR';
                            
                            renderIkhtisarSnilai(title, pts, isPercent);
                        },

                        drillup: function () {
                            const chart = this;
                            setTimeout(() => {
                                // Kembali ke Main Series
                                const s = chart.series && chart.series[0];
                                if (!s) return;

                                const pts = (s.points && s.points.length) 
                                    ? s.points.map(p => ({name: p.name, y: p.y, color: p.color})) 
                                    : mainPointsSnilai;

                                renderIkhtisarSnilai('DISTRIBUSI UNSUR PELAYANAN', pts, false);
                            }, 0);
                        },
                        
                        drillupall: function () {
                             renderIkhtisarSnilai('DISTRIBUSI UNSUR PELAYANAN', mainPointsSnilai, false);
                        }
                    }
                },

                caption: {
                    text: '<span style=""><b>U</b> = Unsur Pelayanan</span>',
                    align: 'left', // Posisi: left, center, atau right
                    x: 10,         // Geser sedikit ke kanan agar rapi
                    y: 5,          // Jarak vertikal
                    style: {
                        fontFamily: 'Inter, sans-serif',
                        fontSize: '13px'
                    }
                },

                title: {
                    text: 'Distribusi Unsur Pelayanan',
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
                    filename: "Distribusi Unsur Pelayanan",
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

                yAxis: {
                    title: { text: null },
                    allowDecimals: false
                },

                legend: { enabled: false },

                tooltip: {
                    formatter: function () {
                        const name = (this.point.name ?? '').toString().toLocaleUpperCase('id-ID');
                        const isDrill = (this.series.type === 'column' || this.series.type === 'bar');

                        return `<b>${name}</b>: ${isDrill
                            ? (Number(this.y) || 0).toFixed(2) + '%'
                            : Math.round(Number(this.y) || 0)
                        }`;
                    }
                },

                plotOptions: {
                    series: { borderWidth: 0 },
                    line: {
                        colorByPoint: true,
                        marker: { enabled: true }
                    },
                    column: {
                        colorByPoint: true,
                        dataLabels: {
                            enabled: true,
                            formatter: function () {
                                return (Number(this.y) || 0).toFixed(2) + '%';
                            }
                        }
                    }
                },

                series: [{
                    name: 'SNILAI',
                    data: mainPointsSnilai
                }],

                drilldown: {
                    breadcrumbs: { position: { align: 'right' } },
                    series: drillSeriesSnilai
                }
            });

            const ikmMainSeries = @json($ikmMainSeries);
            const ikmDrillSeries = @json($ikmDrillSeries);

            // --- FUNGSI RENDER IKHTISAR (Tanpa Border) ---
            function renderIkhtisarIKM(title, points) {
            const titleEl = document.getElementById('ikmIkhtisarTitle');
            const listEl  = document.getElementById('ikmIkhtisarList');

            if (!titleEl || !listEl) return;

            titleEl.textContent = title;

            if (!points || !points.length) {
                listEl.innerHTML = `<div class="text-sm text-slate-500">TIDAK ADA DATA.</div>`;
                return;
            }

            listEl.innerHTML = points.map(p => {
                const c = p.color || '#ccc'; 
                const name = (p.name || '').toString().toLocaleUpperCase('id-ID');
                
                // Ambil nilai asli (number) untuk logika perbandingan
                const rawVal = Number(p.y) || 0; 
                // Ambil nilai string untuk ditampilkan
                const val = rawVal.toFixed(2); 

                // --- LOGIKA KATEGORI ---
                let kategori = 'D';
                if (rawVal >= 3.53) {
                    kategori = 'A';
                } else if (rawVal >= 3.06) {
                    kategori = 'B';
                } else if (rawVal >= 2.60) {
                    kategori = 'C';
                }

                return `
                    <div class="flex items-center gap-3 py-1 mb-1">
                        <span class="w-3 h-3 rounded-full shrink-0" style="background-color:${c}"></span>
                        
                        <div class="text-sm text-slate-700 flex-1 min-w-0 break-words flex justify-between items-center">
                            <span class="font-semibold text-xs text-slate-600 mr-2 leading-tight">${name}</span>
                            
                            <div class="whitespace-nowrap">
                                <span class="font-bold text-slate-800">${val}</span>
                                <span class="text-slate-400 mx-1 text-xs">|</span>
                                <span class="font-black text-slate-900 ${kategori === 'A' ? 'text-green-600' : ''}">${kategori}</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

            // --- HIGHCHARTS CONFIG (Sama seperti sebelumnya) ---
            Highcharts.chart('ikmServiceChart', {
                chart: {
                    type: 'column',
                    events: {
                        load: function() {
                            renderIkhtisarIKM('IKM TIAP KATEGORI LAYANAN', ikmMainSeries);
                        },
                        drilldown: function(e) {
                            this.setTitle({ text: 'Rincian IKM: ' + e.point.name });
                            const drillData = e.seriesOptions.data || [];
                            const drillTitle = 'RINCIAN: ' + e.point.name.toUpperCase();
                            renderIkhtisarIKM(drillTitle, drillData);
                        },
                        drillup: function() {
                            this.setTitle({ text: 'Distribusi IKM tiap Kategori Layanan' });
                            renderIkhtisarIKM('IKM TIAP KATEGORI LAYANAN', ikmMainSeries);
                        }
                    }
                },
                title: {
                    text: 'Distribusi IKM tiap Kategori Layanan',
                    align: 'left',
                    style: { fontFamily: 'Inter, sans-serif', color: '#000', fontWeight: 600, fontSize: '18px' }
                },
                subtitle: {
                    text: @json(
                        \Carbon\Carbon::parse($startDate)->locale('id')->translatedFormat('d F Y')
                        . ' – ' .
                        \Carbon\Carbon::parse($endDate)->locale('id')->translatedFormat('d F Y')
                    ),
                    align: 'left',
                    style: { fontFamily: 'Inter, sans-serif', color: '#4B5563', fontWeight: 400, fontSize: '14px' }
                },
                xAxis: {
                    type: 'category',
                    labels: { style: { fontSize: '11px', fontFamily: 'Inter, sans-serif' } }
                },
                yAxis: {
                    title: { text: null },
                    max: 100,
                    labels: { enabled: true },
                    plotLines: []
                },
                legend: { enabled: false },
                tooltip: {
                    headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                    pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}</b><br/>'
                },
                plotOptions: {
                    column: {
                        borderWidth: 0,
                        dataLabels: {
                            enabled: true,
                            inside: false,      // <--- 1. Memaksa label keluar dari bar
                            crop: false,        // <--- 2. Mencegah label terpotong jika di pinggir grafik
                            format: '{point.y:.2f}',
                            style: { 
                                fontWeight: 'bold',
                                color: '#000000' // Pastikan warna kontras (hitam) karena backgroundnya putih
                            }
                        }
                    }
                },
                series: [{
                    name: 'Kategori Layanan Utama',
                    colorByPoint: true,
                    data: ikmMainSeries
                }],
                drilldown: {
                    breadcrumbs: { position: { align: 'right' } },
                    series: ikmDrillSeries
                },
                exporting: {
                    enabled: true,
                    filename: 'Distribusi IKM tiap Kategori Layanan',
                    buttons: {
                        contextButton: {
                            menuItems: [
                                'downloadPNG',
                                'downloadSVG',
                                'downloadCSV'
                            ]
                        }
                    }
                }
            });


            //Data kategori layanan
            const kategoriLayananPerAspek = @json($kategoriLayananPerAspek);

            //Contoh data jumlah pemohon (silakan ganti)
            const jumlahDataPerAspek = @json($jumlahDataPerAspek);


            const layananHorizontalPerAspekOptions = {
                series: [{ name: "Jumlah", data: jumlahDataPerAspek.map(Number) }],
                chart: {
                    type: 'bar', height: '100%', toolbar: {
                        show: true, tools: {
                            download: true, selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    }
                },

                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                        distributed: true // warna berbeda tiap bar
                    }
                },

                xaxis: {
                    categories: kategoriLayananPerAspek,
                    labels: {
                        style: {
                            colors: textColors
                        },
                        formatter: function (val) {
                            return val.toFixed(2); // tampilkan 2 angka di belakang koma
                        }
                    }
                },

                yaxis: {
                    labels: {
                        style: {
                            colors: textColors
                        }
                    }
                },

                colors: [
                    '#FF4560', '#008FFB', '#00E396', '#FEB019',
                    '#775DD0', '#FF66C4', '#2ECC71'
                ],

                dataLabels: {
                    enabled: true,
                    formatter: (val) => val,
                    style: {
                        fontSize: '12px',
                        colors: ['#fff']
                    }
                },

                grid: {
                    borderColor: gridColors
                },

                tooltip: {
                    theme: isDarkMode ? 'dark' : 'light'
                },

                legend: {
                    show: false
                }
            };

            // ✅ Render Chart
            const layananHorizontalChartPerAspek = new ApexCharts(
                document.querySelector("#rataRataSkorHorizontalChart"),
                layananHorizontalPerAspekOptions
            );
            layananHorizontalChartPerAspek.render();

            // 3. Grafik Distribusi Jenis Kelamin (Donut Chart)
            
            // 1. Siapkan Data
            // Pastikan data yang dikirim tidak null
            const genderData = @json($distribusiGender ?? []);
            
            // 2. Extract Labels & Series
            // Menggunakan Optional Chaining (?.) untuk keamanan ekstra
            const genderLabels = genderData.map(item => (item.label || '').toUpperCase());
            const genderSeries = genderData.map(item => Number(item.total || 0));

            const proporsiRespondenOptions = {
                title: {
                    text: 'Berdasarkan Jenis Kelamin',
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
                
                // DATA
                series: genderSeries,
                labels: genderLabels,
                
                // WARNA
                // ApexCharts otomatis mengulang warna jika jumlah data > jumlah warna
                colors: @json($warna2Chart), 
                
                chart: {
                    type: 'pie',
                    height: '100%',
                    ...chartTheme, // Pastikan variabel chartTheme sudah didefinisikan sebelumnya
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
                    labels: { colors: textColors } // Pastikan textColors ada
                },
                // Tooltip agar angka desimal rapi (Opsional)
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return Math.round(val) // Hilangkan koma di tooltip jika mau
                        }
                    }
                }

                
            };

            // Render Chart
            const chartSelector = document.querySelector("#distribusiProporsiRespondenChart");
            if (chartSelector) {
                const proporsiRespondenChart = new ApexCharts(chartSelector, proporsiRespondenOptions);
                proporsiRespondenChart.render();
            }

            // 1. Siapkan Data
            const pungutanData = @json($skmPungutan ?? []);

            // 2. Extract Series & Labels
            const pungutanSeries = pungutanData.map(item => Number(item.total || 0));
            const pungutanLabels = pungutanData.map(item => (item.label || '').toUpperCase());

            const proporsiPungutanOptions = {
                title: {
                    text: 'Distribusi Indikasi Pungutan',
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
                
                // DATA DINAMIS
                series: pungutanSeries,
                labels: pungutanLabels,
                
                // WARNA (Array utuh)
                colors: @json($warna2Chart),
                
                chart: {
                    type: 'pie',
                    height: '100%',
                    ...chartTheme,
                    toolbar: {
                        show: true,
                        export: {
                            png: { filename: 'Distribusi Indikasi Pungutan' },
                            svg: { filename: 'Distribusi Indikasi Pungutan' },
                            csv: { filename: 'Distribusi Indikasi Pungutan' }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    labels: { colors: textColors }
                },
            };

            const chartSelectorPungutan = document.querySelector("#distribusiIndikasiPungutanChart");
            if (chartSelectorPungutan) {
                const proporsiPungutanChart = new ApexCharts(chartSelectorPungutan, proporsiPungutanOptions);
                proporsiPungutanChart.render();
            }

            // 1. Siapkan Data
            const rekomenData = @json($skmRekomen ?? []);
            
            // 2. Extract Series (Total) & Labels (Nama)
            const rekomenSeries = rekomenData.map(item => Number(item.total || 0));
            const rekomenLabels = rekomenData.map(item => (item.label || '').toUpperCase());

            const loyalitasRekomenOptions = {
                title: {
                    text: 'Loyalitas Rekomendasi Pengguna',
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
                
                // DATA
                series: rekomenSeries,
                labels: rekomenLabels,
                
                // WARNA (Ambil array utuh, ApexCharts akan otomatis looping jika kurang)
                colors: @json($warna2Chart),
                
                chart: {
                    type: 'pie',
                    height: '100%',
                    ...chartTheme,
                    toolbar: {
                        show: true,
                        export: {
                            png: { filename: 'Loyalitas Rekomendasi Pengguna' },
                            svg: { filename: 'Loyalitas Rekomendasi Pengguna' },
                            csv: { filename: 'Loyalitas Rekomendasi Pengguna' }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    labels: { colors: textColors }
                },
            };

            // Render Chart
            const chartDom = document.querySelector("#distribusiLoyalitasRekomenRespondenChart");
            if(chartDom) {
                const loyalitasRekomenChart = new ApexCharts(chartDom, loyalitasRekomenOptions);
                loyalitasRekomenChart.render();
            }

            // 1. Siapkan Array Warna yang urutannya SAMA dengan data Query
            // Kita map data $skmPerPendidikan, ambil warnanya satu per satu
            const orderedColors = @json(
                $skmPerPendidikan->map(function($item) use ($pendidikanColors) {
                    $key = mb_strtoupper($item->pendidikan_kategori ?? 'LAINNYA');
                    return $pendidikanColors[$key] ?? '#cccccc';
                })
            );

            const pendidikanOptions = {
                title: {
                    text: 'Berdasarkan Pendidikan',
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
                
                // Data Series
                series: @json($skmPerPendidikan->pluck('total')),
                
                // Labels
                labels: @json($skmPerPendidikan->pluck('pendidikan_kategori')->map(fn($v) => mb_strtoupper($v ?? '', 'UTF-8'))),
                
                chart: {
                    type: 'pie', // atau 'donut' sesuai kebutuhan
                    height: '100%',
                    ...chartTheme,
                    toolbar: {
                        show: true,
                        export: {
                            png: { filename: 'Berdasarkan Pendidikan' },
                            svg: { filename: 'Berdasarkan Pendidikan' },
                            csv: { filename: 'Berdasarkan Pendidikan' }
                        }
                    }
                },

                // ✅ GUNAKAN ARRAY WARNA YANG SUDAH DIURUTKAN TADI
                colors: orderedColors,

                legend: {
                    position: 'bottom',
                    labels: { colors: textColors }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) { return val.toFixed(0) + '%'; }
                },
                tooltip: {
                    theme: isDarkMode ? 'dark' : 'light',
                    y: { formatter: (val) => val }
                }
            };

            new ApexCharts(document.querySelector("#skmPerPendidikanDonut"), pendidikanOptions).render();

            // 1. Urutkan Warna sesuai Data Query
            const orderedProfesiColors = @json(
                $skmPerProfesi->map(function($item) use ($profesiColors) {
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
                
                series: @json($skmPerProfesi->pluck('total')),
                labels: @json($skmPerProfesi->pluck('profesi_kategori')->map(fn($v) => mb_strtoupper($v ?? '', 'UTF-8'))),
                
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

            new ApexCharts(document.querySelector("#skmPerProfesiPie"), profesiOptions).render();

            // Drilldown kategori layanan
            const kategoriLayananMainSeries = @json($kategoriLayananMainSeries ?? []);
            const kategoriLayananDrillSeries = @json($kategoriLayananDrillSeries ?? []);
            
            const mapServiceColors    = @json($serviceColors);
            const mapSubServiceColors = @json($subServiceColors); // Peta warna anak
            const mapUnsurColors      = @json($unsurColors);
            const colorScore          = @json($scoreColor);

            function normalizePoint(p, idx) {
                // Ambil nama
                let name = '';
                let val = 0;

                if (Array.isArray(p)) {
                    name = p[0];
                    val = Number(p[1]) || 0;
                } else {
                    name = p.name || p.category || '';
                    val = Number(p.y) || 0;
                }

                // --- PROSES PEMILIHAN WARNA ---
                let assignedColor = null;

                // 1. Cek apakah ini Layanan Utama? (UKBI, Perpustakaan, dll)
                if (mapServiceColors[name]) {
                    assignedColor = mapServiceColors[name];
                }
                // 2. Cek apakah ini Sub-Layanan? (Juri, Penerjemahan Tulis, dll)
                else if (mapSubServiceColors[name]) {
                    assignedColor = mapSubServiceColors[name];
                }
                // 3. Cek apakah ini Unsur U1-U9?
                else if (mapUnsurColors[name]) {
                    assignedColor = mapUnsurColors[name];
                }
                // 4. Cek apakah ini Skor 1-4?
                else if (['1', '2', '3', '4'].includes(String(name))) {
                    assignedColor = colorScore;
                }
                // 5. Jika tidak dikenali sama sekali, beri warna default netral (opsional)
                // Seharusnya tidak masuk sini jika semua nama sudah terdaftar di Controller
                else {
                    assignedColor = '#cccccc'; // Abu-abu tanda warning kalau ada yg lupa didaftarin
                }

                // Return data point
                if (Array.isArray(p)) {
                    return { name: name, y: val, color: assignedColor };
                }
                
                return { ...p, color: assignedColor };
            }

            function applyColors(points) {
                return (points || []).map((p, idx) => normalizePoint(p, idx));
            }

            // Proses Data Main
            const rawMain = @json($kategoriLayananMainSeries ?? []);
            const mainColored = applyColors(rawMain);

            // Proses Data Drilldown
            const rawDrill = @json($kategoriLayananDrillSeries ?? []);
            const drillColored = (rawDrill || []).map(s => ({
                ...s,
                data: applyColors(s.data || [])
            }));

            function renderIkhtisarKepuasan(title, points, isPercent = false) {
        const titleEl = document.getElementById('ikhtisarTitleKepuasan');
        const totalEl = document.getElementById('ikhtisarTotalKepuasan');
        const listEl  = document.getElementById('ikhtisarListKepuasan');

        if (!titleEl || !totalEl || !listEl) return;

        titleEl.textContent = title;

        // 1. Cek Data Kosong
        if (!points || !points.length) {
            totalEl.textContent = 'Total=0';
            listEl.innerHTML = `<div class="text-sm text-slate-500">TIDAK ADA DATA.</div>`;
            return;
        }

        // 2. Hitung Total
        const totalVal = (points || []).reduce((a, p) => a + (Number(p.y) || 0), 0);

        // Render Text Total
        totalEl.textContent = isPercent
            ? `Total=${(Number(totalVal) || 0).toFixed(2)}%`
            : `Total=${Math.round(Number(totalVal) || 0)}`;

        // 3. Render List Item
        listEl.innerHTML = points.map((p, idx) => {
            const c = p.color;
            
            // --- LOGIKA NAMA (DIPERBAIKI) ---
            let rawName = (p.name ?? '').toString();
            let displayName = rawName.toLocaleUpperCase('id-ID');

            // Cek apakah nama ini ada di map (misal U1, U2...)
            // Jika ada, ganti format jadi "U1 = Nama Unsur"
            if (mapNamaUnsur[rawName]) {
                displayName = `${rawName} = ${mapNamaUnsur[rawName]}`;
            }

            // --- LOGIKA NILAI (TETAP SAMA SEPERTI ASLINYA) ---
            const raw = Number(p.y) || 0;
            const percentFromTotal = totalVal ? (raw / totalVal) * 100 : 0;
            
            let val;
            if (isPercent) {
                // Mode persen (misal grafik distribusi U1)
                val = `${raw.toFixed(2)}%`;
            } else {
                // Mode jumlah (misal grafik total U1-U9) -> Tampilkan "Jumlah (xx%)"
                const jumlah = Math.round(raw).toLocaleString('id-ID');
                const percentText = percentFromTotal.toFixed(2);
                val = `${jumlah} (${percentText}%)`;
            }

            // --- LOGIKA TAMPILAN (DISESUAIKAN BIAR RAPI) ---
            
            return `
                <div class="flex items-start gap-2">
                    <span class="w-3 h-3 rounded-full shrink-0 mt-1.5" style="background-color:${c}"></span>
                    <div class="text-sm text-slate-700 flex-1 min-w-0 break-words leading-tight">
                        <span class="font-semibold block mb-0.5">${displayName}</span>
                        <span class="text-slate-500">Nilai: ${val}</span>
                    </div>
                </div>
            `;
        }).join('');
    }


            // initial ikhtisar
            renderIkhtisarKepuasan('DISTRIBUSI KEPUASAN TIAP KATEGORI LAYANAN', mainColored, false);

            function extractPointsFromSeries(s) {
                // prefer points (sudah jadi)
                if (s && s.points && s.points.length) {
                    return s.points.map((p, idx) => ({
                        name: p.name ?? p.category,
                        y: p.y,
                        color: p.color
                    }));
                }
                // fallback: pakai options.data (kalau points belum siap)
                return applyColors((s && s.options && s.options.data) ? s.options.data : []);
            }

            function updateIkhtisarFromChart(chart) {
                const levels = chart.drilldownLevels || [];

                // ROOT: selalu pakai main (ini yang bikin aman kalau "langsung ke main")
                if (levels.length === 0) {
                    renderIkhtisarKepuasan('DISTRIBUSI KEPUASAN TIAP KATEGORI LAYANAN', mainColored, false);
                    return;
                }

                const s = chart.series && chart.series[0];
                const pts = extractPointsFromSeries(s);
                const isPercent = s && (s.type === 'column' || s.type === 'bar');
                const title = (s && s.name) ? `IKHTISAR ${s.name}` : 'DISTRIBUSI KEPUASAN TIAP KATEGORI LAYANAN';

                renderIkhtisarKepuasan(title, pts, isPercent);
            }

            function setYAxisForPercent(chart, isPercent) {
                chart.update({
                    yAxis: {
                        min: 0,
                        max: isPercent ? 100 : null,
                        title: { text: isPercent ? 'Persentase (%)' : 'Total / Nilai' }
                    }
                }, false);

                chart.redraw();
            }

            Highcharts.chart('distribusiKepuasanChart', {
                chart: {
                    type: 'line',
                    events: {
                        drilldown: function (e) {
                            const so = e.seriesOptions;
                            const pts = applyColors((so && so.data) ? so.data : []);
                            const isPercent = so && (so.type === 'column' || so.type === 'bar');

                            setYAxisForPercent(this, isPercent); // ✅ set max 100 kalau persen

                            const title = (so && so.name) ? `IKHTISAR ${so.name}` : 'DISTRIBUSI KEPUASAN TIAP KATEGORI LAYANAN';
                            renderIkhtisarKepuasan(title, pts, isPercent);
                        },

                        drillup: function () {
                            const chart = this;
                            setTimeout(() => {
                                updateIkhtisarFromChart(chart);

                                // cek level sekarang: kalau root / line, balikin max null
                                const s = chart.series && chart.series[0];
                                const isPercent = s && (s.type === 'column' || s.type === 'bar');
                                setYAxisForPercent(chart, !!isPercent);
                            }, 0);
                        },

                        drillupall: function () {
                            const chart = this;
                            setTimeout(() => {
                                updateIkhtisarFromChart(chart);
                                setYAxisForPercent(chart, false); // ✅ balik ke normal
                            }, 0);
                        }
                    }

                },
                caption: {
                    text: '<span style=""><b>U</b> = Unsur Pelayanan</span>',
                    align: 'left', // Posisi: left, center, atau right
                    x: 10,         // Geser sedikit ke kanan agar rapi
                    y: 5,          // Jarak vertikal
                    style: {
                        fontFamily: 'Inter, sans-serif',
                        fontSize: '13px'
                    }
                },
                title: {
                    text: 'Distribusi Kepuasan tiap Kategori Layanan',
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
                        color: '#4B5563', // text-gray-600
                        fontWeight: 400,
                        fontSize: '14px'
                    }
                },
                exporting: {
                    enabled: true,
                    filename: "Distribusi Kepuasan tiap Kategori Layanan",
                    buttons: {
                        contextButton: {
                            menuItems: [
                                'downloadPNG',
                                'downloadSVG',
                                'downloadCSV'
                            ]
                        }
                    }
                },

                xAxis: { type: 'category' },
                yAxis: { title: { text: 'Total / Nilai' }, min: 0 },

                legend: { enabled: false },

                tooltip: {
                    formatter: function () {
                        const t = this.series.type;
                        if (t === 'column' || t === 'bar') {
                            return `<b>${this.key}</b>: ${Highcharts.numberFormat(this.y, 2)}%`;
                        }
                        return `<b>${this.key}</b>: ${Highcharts.numberFormat(this.y, 0)}`;
                    }
                },

                plotOptions: {
                    column: { dataLabels: { enabled: true, format: '{point.y:.2f}%' } },
                    bar: { dataLabels: { enabled: true, format: '{point.y:.2f}%' } }
                },

                series: [{
                    name: 'Total Layanan',
                    data: mainColored
                }],

                drilldown: {
                    series: drillColored
                }
            });

            // Ambil data dari variabel Laravel
            const dataPeringkat = {!! json_encode($chartPeringkatLayanan) !!};

            const optionsPeringkatLayanan = {
                series: dataPeringkat.map(item => item.nrr),
                labels: dataPeringkat.map(item => item.unsur),
                colors: dataPeringkat.map(item => item.color),

                chart: {
                    type: 'donut',
                    width: 200, // <--- Tambahkan ini (sesuaikan angka agar pas)
                    height: 200, // <--- Samakan atau sesuaikan
                },

                // Menampilkan U1, U2 dsb di atas potongan donut sesuai permintaan sebelumnya
                dataLabels: {
                    enabled: false,
                },

                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: false 
                            }
                        }
                    }
                },

                // Legend dimatikan agar fokus ke dataLabels U1-U9
                legend: {
                    show: false
                },

                // Tooltip untuk melihat detail saat hover
                tooltip: {
                    y: {
                        formatter: function(value, { seriesIndex }) {
                            const item = dataPeringkat[seriesIndex];
                            
                            // Gunakan parseFloat(value).toFixed(2) agar selalu 2 desimal
                            return item.id + " - " + item.unsur + ": " + parseFloat(value).toFixed(2);
                        },
                        title: {
                            formatter: function() {
                                return ''; 
                            }
                        }
                    }
                }
            };

            const chartPeringkatLayanan = new ApexCharts(document.querySelector("#donutChartPeringkat"), optionsPeringkatLayanan);
            chartPeringkatLayanan.render();

            // --- SETUP VARIABEL UTAMA ---
        const tableBody = document.getElementById('table-body');
        const rows = tableBody.getElementsByTagName('tr');
        const paginationControls = document.getElementById('pagination-controls');
        
        const totalRows = rows.length;
        let currentPage = 1;
        
        // Default rows per page diambil dari nilai awal Select (misal 10)
        let rowsPerPage = 10;
        let totalPages = Math.ceil(totalRows / rowsPerPage);

        // Update Text Info
        const startIndexText = document.getElementById('start-index');
        const endIndexText = document.getElementById('end-index');
        const totalRowsText = document.getElementById('total-rows');
        
        totalRowsText.innerText = totalRows;

        // --- FUNGSI TAMPILKAN BARIS ---
        function displayRows(page) {
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;

            // Sembunyikan semua baris
            for (let i = 0; i < rows.length; i++) {
                rows[i].style.display = 'none';
            }

            // Tampilkan baris yang sesuai range
            let displayedCount = 0;
            for (let i = start; i < end && i < rows.length; i++) {
                rows[i].style.display = '';
                displayedCount++;
            }

            // Update info text
            startIndexText.innerText = totalRows === 0 ? 0 : start + 1;
            endIndexText.innerText = start + displayedCount;
        }

        // --- FUNGSI BUAT TOMBOL PAGINATION ---
        function setupPagination() {
            paginationControls.innerHTML = '';

            // Jika "Tampilkan Semua" dipilih (totalPages = 1), sembunyikan tombol navigasi
            if (totalPages <= 1) return; 

            // Tombol Previous (SVG)
            const prevBtn = document.createElement('button');
            prevBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path fill="currentColor" d="m4 10l9 9l1.4-1.5L7 10l7.4-7.5L13 1z"/></svg>`;
            prevBtn.className = `px-2 py-1 border rounded text-sm flex items-center justify-center ${currentPage === 1 ? 'bg-gray-100 text-gray-300 cursor-not-allowed' : 'bg-white text-gray-600 hover:bg-gray-100'}`;
            prevBtn.disabled = currentPage === 1;
            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    changePage(currentPage);
                }
            });
            paginationControls.appendChild(prevBtn);

            // Tombol Angka Halaman
            let startPage = Math.max(1, currentPage - 1);
            let endPage = Math.min(totalPages, currentPage + 1);

            // Koreksi jika halaman di awal atau akhir
            if (endPage - startPage < 2) { 
                if (startPage === 1) {
                    endPage = Math.min(3, totalPages);
                } else if (endPage === totalPages) {
                    startPage = Math.max(1, totalPages - 2);
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                const btn = document.createElement('button');
                btn.innerText = i;
                btn.className = `px-3 py-1 border rounded text-sm ${i === currentPage ? 'bg-[#6366f1] text-white' : 'bg-white text-gray-700 hover:bg-gray-100'}`;
                btn.addEventListener('click', () => {
                    currentPage = i;
                    changePage(currentPage);
                });
                paginationControls.appendChild(btn);
            }

            // Tombol Next (SVG)
            const nextBtn = document.createElement('button');
            nextBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path fill="currentColor" d="M7 1L5.6 2.5L13 10l-7.4 7.5L7 19l9-9z"/></svg>`;
            nextBtn.className = `px-2 py-1 border rounded text-sm flex items-center justify-center ${currentPage === totalPages ? 'bg-gray-100 text-gray-300 cursor-not-allowed' : 'bg-white text-gray-600 hover:bg-gray-100'}`;
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    changePage(currentPage);
                }
            });
            paginationControls.appendChild(nextBtn);
        }

        // --- FUNGSI GANTI HALAMAN ---
        function changePage(page) {
            displayRows(page);
            setupPagination();
        }

        // --- INISIALISASI AWAL ---
        displayRows(currentPage);
        setupPagination();
                    });
    </script>
@endpush