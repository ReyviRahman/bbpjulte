@extends('layouts.public')

@section('title', 'Beranda')

@section('content')
@push('styles')
<style>
    /* 1. HIDE SCROLLBAR (Agar tetap rapi) */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* 2. CUSTOM SWIPER PAGINATION (Diperbesar & Digeser ke Atas) */
    .swiper-pagination {
        bottom: 25px !important;
        /* Geser ke atas sedikit */
    }

    /* Titik (Bullet) Default - Besar & Bulat */
    .swiper-pagination-bullet {
        width: 16px !important;
        /* Diperbesar jadi 16px */
        height: 16px !important;
        /* Tinggi sama agar bulat */
        background-color: #bfdbfe !important;
        /* Warna Blue-200 */
        opacity: 1 !important;
        margin: 0 8px !important;
        /* Jarak antar titik */
        transition: all 0.3s ease;
    }

    /* Titik (Bullet) Aktif - Tetap Bulat (Hanya ganti warna) */
    .swiper-pagination-bullet-active {
        width: 16px !important;
        /* Lebar tetap sama (bulat) */
        border-radius: 50% !important;
        /* Pastikan lingkaran sempurna */
        background-color: #2563eb !important;
        /* Warna Blue-600 */
        transform: scale(1.2);
        /* Sedikit efek membesar saat aktif (opsional, agar manis) */
    }
    
</style>
@endpush
{{-- Bagian Hero --}}
<div class="bg-white max-w-6xl mx-auto py-20 px-5 text-center rounded-lg">
    <img src="./images/kansa-sibi-imut-lucu.jpeg" alt="Kansa Sibi" class="w-72 mx-auto mb-16">

    <h1 class="text-4xl md:text-5xl font-bold text-slate-800 mb-2">
        Selamat Datang di Unit Layanan Terpadu Elektronik
    </h1>

    <p class="text-base md:text-xl my-12 text-gray-600 max-w-3xl mx-auto mb-6">
        Unit Layanan Terpadu Elektronik (ULT-E) Balai Bahasa Provinsi Jambi adalah platform digital yang memudahkan
        masyarakat dalam mengakses berbagai layanan bahasa, seperti pendampingan kebahasaan, penerjemahan, dan pelatihan
        bahasa, secara online tanpa harus datang langsung.
    </p>

    <a href="{{ route('permohonan.create') }}"
        class="inline-block bg-blue-600 text-white font-bold text-lg py-3 px-6 rounded-md transition-all duration-300 hover:bg-blue-700 hover:-translate-y-0.5 hover:shadow-lg">
        Ajukan Permohonan Layanan Sekarang
    </a>

    {{-- layanan selesai dan di proses --}}
    <div class="bg-white py-12">
        <div class="max-w-6xl mx-auto text-center">

            {{-- Bagian Judul & Subjudul --}}
            <div class="mb-10">
                <h2 class="text-3xl font-extrabold text-slate-800 leading-tight">
                    Ringkasan Statistik Layanan <br>
                    <span class="">Unit Layanan Terpadu</span> <br>
                    Balai Bahasa Provinsi Jambi
                </h2>

                <p class="mt-3 text-lg font-medium  bg-gray-100 inline-block px-4 py-1.5 rounded-full">
                    Periode Data Triwulan {{ $triwulan }} Tahun {{ $tahun }}
                </p>
            </div>

            {{-- Container Grid --}}
            {{-- md:grid-cols-2 (Tablet: 2 kolom), lg:grid-cols-4 (Laptop: 4 kolom sejajar) --}}
            {{-- Menggunakan 5 Kolom agar semua sejajar di layar besar --}}
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4 mt-8">

                {{-- KOTAK 1: Indeks Kepuasan Masyarakat (Statis) --}}
                <div
                    class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-200 text-center flex flex-col justify-center items-center hover:shadow-md transition-shadow duration-300">
                    <h4 class="text-sm font-bold text-slate-800 mb-3 uppercase tracking-wider">
                        Indeks Kepuasan Masyarakat
                    </h4>
                    <div class="flex items-center justify-center gap-2">
                        <span class="text-2xl  ">
                            {{ number_format($ikmScore, 2, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- KOTAK 2: Kategori Mutu Pelayanan (Statis) --}}
                <div
                    class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-200 text-center flex flex-col justify-center items-center hover:shadow-md transition-shadow duration-300">
                    <h4 class="text-sm font-bold text-slate-800 mb-3 uppercase tracking-wider">
                        Kategori Mutu Pelayanan
                    </h4>
                    <div class="flex items-center justify-center gap-2">
                        {{-- Menggunakan teks lebih kecil (text-2xl) karena isinya huruf, bukan angka --}}
                        <span class="text-2xl ">
                            {{ $ikmMutu }}
                        </span>
                    </div>
                </div>

                {{-- KOTAK 3: Layanan Terbanyak (Statis) --}}
                <div
                    class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-200 text-center flex flex-col justify-center items-center hover:shadow-md transition-shadow duration-300">
                    <h4 class="text-sm font-bold text-slate-800 mb-3 uppercase tracking-wider">
                        Layanan Terbanyak
                    </h4>
                    <div class="flex flex-col items-center justify-center">
                        <span class="text-2xl">
                            {{ $namaLayananPopuler }}
                        </span>

                    </div>
                </div>

                {{-- KOTAK 4 & 5: Layanan Selesai dan Diproses (Dinamis dari Array) --}}
                @foreach($statistik as $stat)
                <div
                    class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-200 text-center flex flex-col justify-center items-center hover:shadow-md transition-shadow duration-300">
                    <h4 class="text-sm font-bold text-slate-800 mb-3 uppercase tracking-wider">
                        {{ $stat['label'] }}
                    </h4>
                    <div class="flex items-center justify-center gap-2">
                        <span class="text-2xl {{ $stat['color'] }}">
                            {{ $stat['jumlah'] }}
                        </span>
                        <span class="text-2xl font-light ">|</span>
                        <span class="text-2xl ">
                            {{ $stat['persen'] }}%
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="grid sm:grid-cols-2 grid-cols-1 mt-4">
                <div>
                    <div class="h-96 relative" data-ikhtisar-wrap>

                        {{-- 1. Toggle Icon --}}
                        <button type="button" data-ikhtisar-toggle aria-expanded="false" title="Ikhtisar"
                            class="absolute top-[4px] right-0 z-50 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48">
                                <path fill="currentColor" fill-rule="evenodd"
                                    d="M24 1.5c-7.403 0-12.592.239-15.857.466S2.281 4.66 1.991 7.96C1.742 10.794 1.5 15.074 1.5 21v22.652c0 2.99 3.507 4.603 5.778 2.657l6.96-5.966c2.687.093 5.927.157 9.762.157c7.403 0 12.592-.239 15.857-.466s5.862-2.693 6.152-5.993c.249-2.835.491-7.115.491-13.041s-.242-10.206-.491-13.041c-.29-3.3-2.887-5.765-6.152-5.993C36.592 1.74 31.403 1.5 24 1.5m2.882 25.452c2.218-1.238 3.36-2.588 3.538-4.88a96 96 0 0 1-2.028-.027c-1.33-.034-2.326-1.032-2.361-2.362a99 99 0 0 1-.031-2.61c0-1.16.015-2.069.035-2.77c.036-1.252.939-2.202 2.191-2.254C28.921 12.021 29.828 12 31 12s2.079.02 2.774.05c1.252.05 2.155 1.001 2.191 2.254c.02.695.035 1.594.035 2.74v5.03h-.023c-.296 4.235-3.425 6.759-6.97 7.85c-.499.153-1.05.08-1.427-.281c-.438-.419-.813-.937-1.088-1.368c-.295-.464-.09-1.055.39-1.323m-10.462-4.88c-.178 2.292-1.32 3.642-3.538 4.88c-.48.268-.685.86-.39 1.323c.275.431.65.949 1.088 1.368c.378.36.928.434 1.427.28c3.545-1.09 6.674-3.614 6.97-7.85H22v-5.029c0-1.146-.015-2.045-.035-2.74c-.036-1.253-.939-2.204-2.191-2.255C19.079 12.021 18.172 12 17 12s-2.079.02-2.774.05c-1.252.05-2.155 1.001-2.191 2.254c-.02.7-.035 1.608-.035 2.768c0 1.075.013 1.933.03 2.61c.036 1.33 1.031 2.329 2.362 2.363c.546.013 1.215.024 2.028.027"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        {{-- 2. Chart Container --}}
                        <div class="w-full h-full pr-4">
                            <div id="ikmChart" class="w-full h-full"></div>
                        </div>

                        {{-- 3. IKHTISAR (FLOATING OVERLAY) --}}
                        <div class="absolute top-6 right-0 z-50 w-[380px] max-w-[92%] max-h-[85%] overflow-auto hidden"
                            data-ikhtisar-panel>
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
                                            $val = $ikmSeries[$twKey][$idxYear] ?? null;
                                            $meta = $metaSeries[$twKey][$idxYear] ?? '-';
                                            $c = $colors[$idxTw];

                                            if ($val !== null) $hasAnyData = true;

                                            // UBAH LABEL DISINI: (idxTw mulai dari 0, jadi +1)
                                            $labelTriwulan = "Triwulan " . ($romawi[$idxTw + 1] ?? '');
                                            @endphp

                                            @if($val !== null)
                                            <div class="flex items-start gap-2">
                                                {{-- Dot Warna --}}
                                                <span class="w-3 h-3 rounded-full shrink-0 mt-1"
                                                    style="background-color: {{ $c }}"></span>

                                                <div class="text-sm text-slate-700 flex-1 min-w-0 break-words">
                                                    {{-- Tampilkan Label Romawi --}}
                                                    <span class="font-semibold">{{ $labelTriwulan }}</span>: {{
                                                    number_format($val, 2) }}

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
                <div>
                    <div class="swiper mySwiper w-full pb-16 px-4">
                        <div class="swiper-wrapper">
                            @foreach ($laporan as $item)
                            <div class="swiper-slide">

                                <!-- KARTU UTAMA -->
                                <!-- Lebar kartu mengikuti lebar container (w-full) -->
                                <!-- Ubah rounded-2xl menjadi rounded-none (Runcing) -->
                                <div
                                    class="w-full bg-white shadow-lg rounded-none overflow-hidden border border-gray-100 flex flex-col">

                                    <!-- Header Kartu -->
                                    <div
                                        class="bg-gray-50 px-8 py-5 border-b border-gray-100 flex sm:flex-row flex-col justify-between items-start">
                                        <h2 class="text-xl font-bold text-gray-800 leading-tight pr-4">{{ $item->judul
                                            }}
                                        </h2>
                                        <span
                                            class="bg-blue-100 text-blue-700 text-sm px-4 py-1 rounded-full font-semibold ">
                                            Total Data Triwulan
                                            {{ ['1' => 'I', '2' => 'II', '3' => 'III', '4' => 'IV'][$triwulan] ??
                                            $triwulan }} :
                                            {{ $item->total_triwulan }}
                                        </span>

                                    </div>

                                    <!-- Body Statistik -->
                                    <div class="p-8 pb-12">
                                        <!-- Ditambahkan padding bottom agar tidak terlalu mepet bawah -->
                                        <!-- Grid 4 Kotak Kecil -->
                                        <!-- Label indikator disesuaikan dengan standar SKM -->
                                        <div class="grid grid-cols-4 gap-6 mb-8">
                                            <div class="text-center group">
                                                <div
                                                    class="bg-red-50 rounded-xl py-4 mb-2 border border-red-100 group-hover:bg-red-100 transition">
                                                    <span class="block sm:text-3xl font-bold text-red-600">{{
                                                        $item->counts[1] }}</span>
                                                </div>
                                                <span
                                                    class="text-xs md:text-sm text-gray-500 font-medium block leading-tight">Tidak
                                                    Memuaskan</span>
                                            </div>
                                            <div class="text-center group">
                                                <div
                                                    class="bg-orange-50 rounded-xl py-4 mb-2 border border-orange-100 group-hover:bg-orange-100 transition">
                                                    <span class="block sm:text-3xl font-bold text-orange-600">{{
                                                        $item->counts[2] }}</span>
                                                </div>
                                                <span
                                                    class="text-xs md:text-sm text-gray-500 font-medium block leading-tight">Kurang
                                                    Memuaskan</span>
                                            </div>
                                            <div class="text-center group">
                                                <div
                                                    class="bg-blue-50 rounded-xl py-4 mb-2 border border-blue-100 group-hover:bg-blue-100 transition">
                                                    <span class="block sm:text-3xl font-bold text-blue-600">{{
                                                        $item->counts[3] }}</span>
                                                </div>
                                                <span
                                                    class="text-xs md:text-sm text-gray-500 font-medium block leading-tight">Memuaskan</span>
                                            </div>
                                            <div class="text-center group">
                                                <div
                                                    class="bg-green-50 rounded-xl py-4 mb-2 border border-green-100 group-hover:bg-green-100 transition">
                                                    <span class="block sm:text-3xl font-bold text-green-600">{{
                                                        $item->counts[4] }}</span>
                                                </div>
                                                <span
                                                    class="text-xs md:text-sm text-gray-500 font-medium block leading-tight">Sangat
                                                    Memuaskan</span>
                                            </div>
                                        </div>

                                        <!-- Progress Bars Detail -->
                                        <div class="space-y-5">
                                            @foreach ([1 => ['red', 'Tidak Memuaskan'], 2 => ['orange', 'Kurang
                                            Memuaskan'], 3 => ['blue', 'Memuaskan'], 4 => ['green', 'Sangat Memuaskan']]
                                            as $score => $conf)
                                            @php
                                            $persen =
                                            $item->total > 0
                                            ? ($item->counts[$score] / $item->total) * 100
                                            : 0;
                                            $color = $conf[0];
                                            $label = $conf[1];
                                            @endphp
                                            <div>
                                                <div class="flex justify-between text-sm mb-2">
                                                    <span class="font-medium text-gray-600">{{ $label }}</span>
                                                    <span class="font-bold text-gray-800">{{ number_format($persen, 1)
                                                        }}%</span>
                                                </div>
                                                <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                                                    <div class="bg-{{ $color }}-500 h-3 rounded-full transition-all duration-700 ease-out"
                                                        style="width: {{ $persen }}%"></div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>

                                    </div>

                                    <!-- BAGIAN FOOTER NILAI RATA-RATA DIHAPUS -->

                                </div>
                                <!-- END KARTU -->

                            </div>
                            @endforeach

                        </div>

                        <!-- PAGINATION (Titik Bawah) -->
                        <div class="swiper-pagination"></div>

                    </div>
                </div>
            </div>
            <div class="mt-8 text-sm font-medium  bg-gray-50 inline-block px-4 py-2 rounded border border-gray-200">
                <span class="">Informasi:</span> Data yang tersedia saat ini adalah data triwulan terakhir, pembaruan
                akan dilakukan secara berkala.
            </div>
        </div>
    </div>
</div>

{{-- Bagian Maklumat Pelayanan --}}
<div class="bg-gray-50 py-16">
    <div class="max-w-6xl mx-auto px-5">
        <div class="bg-white p-8 rounded-lg shadow-md border border-gray-200">
            <img src="https://balaibahasajambi.kemendikdasmen.go.id/wp-content/uploads/2025/05/maklumat-2025-ttd-adi-1536x1086-2-1024x724.jpg"
                alt="Maklumat Pelayanan" class="w-full max-w-4xl mx-auto">
        </div>
    </div>
</div>

{{-- Bagian Standar Pelayanan --}}
<div class="bg-white py-20">
    <div class="max-w-6xl mx-auto px-5 text-center">
        <h2 class="text-3xl font-bold text-slate-800">Standar Pelayanan</h2>
        <h3 class="text-4xl font-bold text-slate-800 mt-2">Balai Bahasa Provinsi Jambi</h3>
        <p class="text-lg text-gray-600 max-w-4xl mx-auto mt-4">
            Unit Layanan Terpadu (ULT) Balai Bahasa Provinsi Jambi berkomitmen untuk memberikan layanan prima. Melalui
            laman ini, masyarakat dapat mengakses informasi mengenai alur pengajuan, persyaratan, biaya, dan waktu yang
            dibutuhkan untuk setiap layanan.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-12">
            {{-- Card 1: Penerjemahan --}}
            <div class="bg-gray-50 p-8 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="flex justify-center mb-4">
                    <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5h12M9 3v2m4 0v2M3 19h18M5 11h14M9 15h6"></path>
                    </svg>
                </div>
                <h4 class="text-xl font-bold text-slate-800">Standar Pelayanan Penerjemahan</h4>
                <a href="/standar-pelayanan/penerjemahan"
                    class="mt-4 inline-block bg-blue-600 text-white font-semibold py-2 px-5 rounded-md hover:bg-blue-700 transition-colors">Lihat</a>
            </div>

            {{-- Card 2: Perpustakaan --}}
            <div class="bg-gray-50 p-8 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="flex justify-center mb-4">
                    <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                        </path>
                    </svg>
                </div>
                <h4 class="text-xl font-bold text-slate-800">Standar Pelayanan Perpustakaan</h4>
                <a href="/standar-pelayanan/perpustakaan"
                    class="mt-4 inline-block bg-blue-600 text-white font-semibold py-2 px-5 rounded-md hover:bg-blue-700 transition-colors">Lihat</a>
            </div>

            {{-- Card 3: Fasilitas Bantuan Teknis --}}
            <div class="bg-gray-50 p-8 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="flex justify-center mb-4">
                    <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h4 class="text-xl font-bold text-slate-800">Standar Pelayanan Fasilitas Bantuan Teknis</h4>
                <a href="/standar-pelayanan/fasilitas-bantuan-teknis"
                    class="mt-4 inline-block bg-blue-600 text-white font-semibold py-2 px-5 rounded-md hover:bg-blue-700 transition-colors">Lihat</a>
            </div>

            {{-- Card 4: Praktik Kerja Lapangan --}}
            <div class="bg-gray-50 p-8 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="flex justify-center mb-4">
                    <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
                <h4 class="text-xl font-bold text-slate-800">Standar Pelayanan Praktik Kerja Lapangan</h4>
                <a href="/standar-pelayanan/praktik-kerja-lapangan"
                    class="mt-4 inline-block bg-blue-600 text-white font-semibold py-2 px-5 rounded-md hover:bg-blue-700 transition-colors">Lihat</a>
            </div>

            {{-- Card 5: Peminjaman Sarana dan Prasarana --}}
            <div class="bg-gray-50 p-8 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="flex justify-center mb-4">
                    <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                </div>
                <h4 class="text-xl font-bold text-slate-800">Standar Pelayanan Peminjaman Sarana dan Prasarana</h4>
                <a href="/standar-pelayanan/pesapra"
                    class="mt-4 inline-block bg-blue-600 text-white font-semibold py-2 px-5 rounded-md hover:bg-blue-700 transition-colors">Lihat</a>
            </div>

            {{-- Card 6: Kunjungan Edukasi --}}
            <div class="bg-gray-50 p-8 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="flex justify-center mb-4">
                    <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                    </svg>
                </div>
                <h4 class="text-xl font-bold text-slate-800">Standar Pelayanan Kunjungan Edukasi</h4>
                <a href="/standar-pelayanan/kunjungan-edukasi"
                    class="mt-4 inline-block bg-blue-600 text-white font-semibold py-2 px-5 rounded-md hover:bg-blue-700 transition-colors">Lihat</a>
            </div>
        </div>

        <div class="mt-12">
            <a href="#"
                class="inline-block bg-gray-800 text-white font-bold text-lg py-3 px-8 rounded-md hover:bg-gray-900 transition-colors">
                Selengkapnya
            </a>
        </div>

        <div class="max-w-6xl mx-auto min-h-screen flex sm:flex-row flex-col justify-center items-start mt-5">
            <!-- Penjelasan SKM -->
            <div class="mb-8 px-2 sm:basis-128">
                <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 text-center md:text-left">
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Survei Kepuasan Masyarakat</h2>
                    <p class="text-gray-600 leading-relaxed">
                        Balai Bahasa Provinsi Jambi membutuhkan informasi rutin tentang Survei Kepuasan Masyarakat.
                        Untuk itu, kami berupaya menyajikan Survei Kepuasan Masyarakat yang diharapkan mampu
                        memberikan gambaran mengenai kualitas pelayanan kami kepada masyarakat.
                    </p>
                </div>
            </div>

            <!-- CAROUSEL CONTAINER -->

        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- Import library dari CDN --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
            var swiper = new Swiper(".mySwiper", {
                slidesPerView: 1,
                spaceBetween: 40,
                grabCursor: false, // 1. Matikan kursor tangan
                centeredSlides: true,

                effect: "slide",
                speed: 500,

                keyboard: {
                    enabled: true,
                },

                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                    dynamicBullets: true,
                },
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
        });
</script>
@endpush