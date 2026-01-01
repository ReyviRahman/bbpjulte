@extends('layouts.admin')

@section('title', 'Detail Survei Kepuasan Pengguna')

@section('header-title', 'Dasbor Administrator > Manajemen Survei Kepuasan Pengguna > Detail')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center flex-wrap gap-4 mb-8">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Detail Skm dari: {{ $skm->nama_pemohon }}
                </h2>
                <a href="{{ route('admin.skm.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Daftar skm
                </a>
            </div>

            {{-- GRID UTAMA 2 KOLOM --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                {{-- KOLOM 1: DATA PENGADU --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Data Responden</h3>
                        <dl class="mt-4 space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Lengkap</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $skm->nama_pemohon }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Instansi/Lembaga</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $skm->instansi }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Profesi</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $skm->profesi }}</dd>
                            </div>
                             <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal skm</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $skm->created_at->format('d F Y, H:i') }} WIB</dd>
                            </div>
                        </dl>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Data Pelayanan</h3>
                        <dl class="mt-4 space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Layanan Yang Didapat</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $skm->layanan_didapat }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Petugas</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $skm->nama_petugas }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- KOLOM 2: KONTAK & BUKTI --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Isi Survei</h3>
                        <p class="mt-4 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed">Syarat Pengurusan Pelayanan: {{ $skm->syarat_pengurusan_pelayanan }}</p>
                        <p class="mt-4 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed">Sistem Mekanisme Dan Prosedur Pelayanan: {{ $skm->sistem_mekanisme_dan_prosedur_pelayanan }}</p>
                        <p class="mt-4 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed">Waktu Penyelesaian Pelayanan: {{ $skm->waktu_penyelesaian_pelayanan }}</p>
                        <p class="mt-4 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed">Kesesuaian Biaya Pelayanan Dengan yang Diinformasikan: {{ $skm->kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan }}</p>
                        <p class="mt-4 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed">Kesesuaian Hasil Pelayanan: {{ $skm->kesesuaian_hasil_pelayanan }}</p>
                        <p class="mt-4 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed">Kemampuan Petugas Dalam Memberikan Pelayanan: {{ $skm->kemampuan_petugas_dalam_memberikan_pelayanan }}</p>
                        <p class="mt-4 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed">Kesopanan dan Keramahan Petugas: {{ $skm->kesopanan_dan_keramahan_petugas }}</p>
                        <p class="mt-4 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed">Penanganan Pengaduan Saran dan Masukan: {{ $skm->penanganan_pengaduan_saran_dan_masukan }}</p>
                        <p class="mt-4 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed">Sarana dan Prasarana Penunjang Pelayanan: {{ $skm->sarana_dan_prasarana_penunjang_pelayanan }}</p>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Kontak</h3>
                        <dl class="mt-4 space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $skm->email }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Bagian Kritik Saran di bawah grid --}}
            <div class="mt-8">
                 <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Isi Kritik Saran</h3>
                        <p class="mt-4 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed">{{ $skm->kritik_saran }}</p>
                    </div>
                 </div>
            </div>
            

        </div>
    </div>
@endsection
