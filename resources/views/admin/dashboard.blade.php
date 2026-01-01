@extends('layouts.admin')

@section('title', 'Dasbor Administrator')

@section('header-title', 'Dasbor Administrator')

@section('content')
   <div class="py-1">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
 {{-- KONTEN DASBOR YANG SUDAH ADA --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="text-lg">Selamat Datang {{ Auth::user()->name }} di Dasbor Administrator!</p>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Anda bisa mengelola data permohonan layanan melalui menu "Permohonan" di navigasi samping.
                    </p>
                </div>
            </div>
        </div>
            {{-- PANEL NOTIFIKASI BARU --}}
        <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($jumlahPermohonanBaru > 0)
            <div class="mb-8 bg-white dark:bg-gray-800 border-l-4 border-blue-500 dark:border-blue-400 p-6 rounded-r-lg shadow-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="flex sm:flex-row flex-col justify-between items-center">
                            <p class="text-lg font-bold text-blue-800 dark:text-blue-200">
                                Anda memiliki {{ $jumlahPermohonanBaru }} permohonan baru!
                            </p>
                            <button type="button"
        onclick="window.location.href='{{ route('admin.permohonan.index', ['status' => 'Diajukan']) }}'"
        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-green-600 uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
    Proses Sekarang
</button>
                        </div>
                        <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                            Terdapat permohonan yang perlu segera ditindaklanjuti.
                        </p>
                        {{-- Daftar 5 Permohonan Terbaru --}}
                        <div class="mt-4 border-t border-blue-200 dark:border-gray-700 pt-4">
                            <ul class="list-disc list-inside space-y-2">
                                @foreach($daftarPermohonanBaru as $permohonan)
                                    <li class="text-sm text-gray-700 dark:text-gray-400">
                                        Permohonan dari <strong class="dark:text-gray-200">{{ $permohonan->nama_lengkap }}</strong> untuk layanan <strong class="dark:text-gray-200">{{ $permohonan->layanan_dibutuhkan }}</strong>
                                        <a href="{{ route('admin.permohonan.show', $permohonan) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline ml-2">[Lihat]</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            </div>

        </div>
    </div>
@endsection
