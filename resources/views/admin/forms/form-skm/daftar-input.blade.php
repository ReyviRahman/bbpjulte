@extends('layouts.admin')

@section('title', 'Manajemen Formulir')
@section('header-title', 'Manajemen Formulir')

@push('styles')
    <style>
        /* Sedikit style tambahan untuk badge peran */
        .role-badge {
            padding: 0.25em 0.6em;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .role-admin {
            background-color: #fecaca;
            /* red-200 */
            color: #991b1b;
            /* red-800 */
        }

        .dark .role-admin {
            background-color: #7f1d1d;
            /* red-900 */
            color: #fca5a5;
            /* red-400 */
        }

        .role-petugas {
            background-color: #dbeafe;
            /* blue-100 */
            color: #1e40af;
            /* blue-800 */
        }

        .dark .role-petugas {
            background-color: #1e3a8a;
            /* blue-900 */
            color: #93c5fd;
            /* blue-300 */
        }
    </style>
@endpush

@section('content')

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            {{-- Header --}}
            <div class="flex items-center gap-1 mb-6">
                <a href="{{ route('admin.forms.daftar-input') }}"
                    class="inline-flex items-center px-1 py-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h2 class="text-lg font-semibold">Daftar Input Formulir SKM</h2>
            </div>
            {{-- Notifikasi --}}
            @if (session('success') || session('error'))
                <div class="mb-6">
                    @if (session('success'))
                        <div class="bg-green-100 dark:bg-green-900/30 border-l-4 border-green-500 text-green-700 dark:text-green-300 p-4"
                            role="alert">
                            <p class="font-bold">Sukses</p>
                            <p>{{ session('success') }}</p>
                    </div> @endif
                    @if (session('error'))
                        <div class="bg-red-100 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-300 p-4"
                            role="alert">
                            <p class="font-bold">Error</p>
                            <p>{{ session('error') }}</p>
                    </div> @endif
                </div>
            @endif

            <div class="overflow-x-auto">
                <table
                    class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                                style="width: 5%;">No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama
                                Input</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            // Daftar semua inputan, kita bagi tipe-nya jadi 'editable' atau 'system'
                            $fields = [
                                // --- INPUTAN YANG BISA DIKELOLA (Editable) ---
                                [
                                    'name' => 'Nama Petugas yang melayani',
                                    'type' => 'editable',
                                    'category' => 'Nama Petugas yang melayani'
                                ],
                                ['name' => 'Nama pemohon', 'type' => 'system'],
                                ['name' => 'Pos-el (E-mail)', 'type' => 'system'],
                                ['name' => 'Instansi/Lembaga/Komunitas', 'type' => 'system'],
                                [
                                    'name' => 'Jenis Kelamin',
                                    'type' => 'editable',
                                    'category' => 'Jenis Kelamin'
                                ],
                                ['name' => 'Kritik dan Saran', 'type' => 'system'],
                                [
                                    'name' => 'Pendidikan Terakhir',
                                    'type' => 'editable',
                                    'category' => 'Pendidikan Terakhir'
                                ],
                                [
                                    'name' => 'Profesi',
                                    'type' => 'editable',
                                    'category' => 'Profesi'
                                ],
                                [
                                    'name' => 'Layanan yang didapatkan',
                                    'type' => 'editable',
                                    'category' => 'Layanan'
                                ],
                                [
                                    'name' => 'Syarat pengurusan pelayanan',
                                    'type' => 'editable',
                                    'category' => 'Syarat pengurusan pelayanan'
                                ],
                                [
                                    'name' => 'Sistem Mekanisme Dan Prosedur Pelayanan',
                                    'type' => 'editable',
                                    'category' => 'Sistem Mekanisme Dan Prosedur Pelayanan'
                                ],
                                [
                                    'name' => 'Waktu Penyelesaian Pelayanan',
                                    'type' => 'editable',
                                    'category' => 'Waktu Penyelesaian Pelayanan'
                                ],
                                [
                                    'name' => 'Kesesuaian Biaya Pelayanan Dengan yang Diinformasikan',
                                    'type' => 'editable',
                                    'category' => 'Kesesuaian Biaya Pelayanan Dengan yang Diinformasikan'
                                ],
                                [
                                    'name' => 'Kesesuaian Hasil Pelayanan',
                                    'type' => 'editable',
                                    'category' => 'Kesesuaian Hasil Pelayanan'
                                ],
                                [
                                    'name' => 'Kemampuan Petugas Dalam Memberikan Pelayanan',
                                    'type' => 'editable',
                                    'category' => 'Kemampuan Petugas Dalam Memberikan Pelayanan'
                                ],
                                [
                                    'name' => 'Kesopanan Dan Keramahan Petugas',
                                    'type' => 'editable',
                                    'category' => 'Kesopanan Dan Keramahan Petugas'
                                ],
                                [
                                    'name' => 'Penanganan Pengaduan Saran Dan Masukan',
                                    'type' => 'editable',
                                    'category' => 'Penanganan Pengaduan Saran Dan Masukan'
                                ],
                                [
                                    'name' => 'Sarana Dan Prasarana Penunjang Pelayanan',
                                    'type' => 'editable',
                                    'category' => 'Sarana Dan Prasarana Penunjang Pelayanan'
                                ],
                                [
                                    'name' => 'Apakah Ada Pungutan',
                                    'type' => 'editable',
                                    'category' => 'Pungutan'
                                ],
                                [
                                    'name' => 'Akan Informasikan Layanan',
                                    'type' => 'editable',
                                    'category' => 'Informasikan Layanan'
                                ],
                                ['name' => 'Kritik dan Saran', 'type' => 'system'],
                            ];
                        @endphp

                        {{-- Loop Array di atas --}}
                        @foreach($fields as $index => $field)
                            <tr class="table-hover-row">
                                {{-- 1. Nomor Urut --}}
                                <td class="px-4 py-4 text-center text-sm text-gray-500">
                                    {{ $index + 1 }}
                                </td>

                                {{-- 2. Nama Input --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $field['name'] }}
                                    </div>
                                </td>

                                {{-- 3. Kolom Aksi --}}
                                <td class="px-6 py-4 flex justify-center whitespace-nowrap text-center text-sm font-medium">

                                    @if($field['type'] === 'editable')
                                        {{-- TAMPILKAN TOMBOL JIKA EDITABLE --}}
                                        <div class="">
                                            <a href="{{ route('admin.form-skm.index', ['category' => $field['category']]) }}"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 action-btn"
                                                title="Kelola Data">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                                                    fill="blue">
                                                    <path
                                                        d="M10 3a1 1 0 00-1 1v7H3a1 1 0 000 2h6v7a1 1 0 002 0v-7h6a1 1 0 100-2h-6V4a1 1 0 00-1-1z" />
                                                </svg>
                                            </a>
                                        </div>
                                    @else
                                        {{-- TAMPILKAN TEKS JIKA SYSTEM --}}
                                        <span
                                            class="text-xs text-gray-400 italic bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded cursor-not-allowed">
                                            dikelola oleh pengelola sistem
                                        </span>
                                    @endif

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection