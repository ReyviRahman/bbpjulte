@extends('layouts.admin')

@section('title', 'Detail Pengaduan')

@section('header-title', 'Dasbor Administrator > Manajemen Pengaduan > Detail')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center flex-wrap gap-4 mb-8">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Detail Pengaduan dari: {{ $pengaduan->nama_lengkap }}
                </h2>
                <a href="{{ route('admin.pengaduan.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Daftar Pengaduan
                </a>
            </div>

            {{-- GRID UTAMA 2 KOLOM --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                {{-- KOLOM 1: DATA PENGADU --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Data Pengadu</h3>
                        <dl class="mt-4 space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Lengkap</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $pengaduan->nama_lengkap }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Instansi/Lembaga</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $pengaduan->instansi }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Profesi</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $pengaduan->profesi }}</dd>
                            </div>
                             <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Pengaduan</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $pengaduan->created_at->format('d F Y, H:i') }} WIB</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- KOLOM 2: KONTAK & BUKTI --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Kontak & Bukti</h3>
                        <dl class="mt-4 space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $pengaduan->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nomor Ponsel/WA</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $pengaduan->nomor_ponsel }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Bukti Terlampir</dt>
                                <dd class="mt-2 text-sm text-gray-900 dark:text-gray-100">
                                    @if($pengaduan->path_bukti_aduan)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($pengaduan->path_bukti_aduan) }}" target="_blank" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            Lihat Bukti
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                        </a>
                                    @else
                                        <span class="text-gray-500">Tidak ada bukti yang dilampirkan.</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Bagian Isi Pengaduan di bawah grid --}}
            <div class="mt-8">
                 <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Isi Pengaduan Lengkap</h3>
                        <p class="mt-4 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed">{{ $pengaduan->isi_aduan }}</p>
                    </div>
                 </div>
            </div>

        </div>
    </div>
@endsection
