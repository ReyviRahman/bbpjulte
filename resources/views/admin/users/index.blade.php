@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')
@section('header-title', 'Manajemen Pengguna')

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
        background-color: #fecaca; /* red-200 */
        color: #991b1b; /* red-800 */
    }
    .dark .role-admin {
        background-color: #7f1d1d; /* red-900 */
        color: #fca5a5; /* red-400 */
    }
    .role-petugas {
        background-color: #dbeafe; /* blue-100 */
        color: #1e40af; /* blue-800 */
    }
    .dark .role-petugas {
        background-color: #1e3a8a; /* blue-900 */
        color: #93c5fd; /* blue-300 */
    }
</style>
@endpush



@section('content')
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            {{-- Header --}}
            <div class="flex sm:flex-row flex-col items-center justify-between mb-6">
                <h2 class="text-lg font-semibold">Daftar Pengguna</h2>
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-blue-100 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="blue"><path d="M10 3a1 1 0 00-1 1v7H3a1 1 0 000 2h6v7a1 1 0 002 0v-7h6a1 1 0 100-2h-6V4a1 1 0 00-1-1z" /></svg>
                    Tambah Pengguna
                </a>
            </div>
            {{-- Notifikasi --}}
            @if (session('success') || session('error'))
                <div class="mb-6">
                    @if (session('success')) <div class="bg-green-100 dark:bg-green-900/30 border-l-4 border-green-500 text-green-700 dark:text-green-300 p-4" role="alert"><p class="font-bold">Sukses</p><p>{{ session('success') }}</p></div> @endif
                    @if (session('error')) <div class="bg-red-100 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-300 p-4" role="alert"><p class="font-bold">Error</p><p>{{ session('error') }}</p></div> @endif
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 5%;">No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pengguna</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terakhir Login</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($users as $index => $user)
                            <tr class="table-hover-row">
                                <td class="px-4 py-4 text-center text-sm text-gray-500">{{ $users->firstItem() + $index }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ $user->profile_photo_url }}" alt="Foto profil {{ $user->name }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $user->nip ?? '-' }} {{-- <-- DATA BARU ('-' jika NIP kosong) --}}

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $user->email}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $user->role == 'admin' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' : 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-start">
                                         @if ($user->isOnline())
                                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold leading-5 rounded-full bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                                <svg class="w-2.5 h-2.5 mr-2" fill="blue" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                                                Online
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold leading-5 rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                <svg class="w-2.5 h-2.5 mr-2" fill="red" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                                                Offline
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    @if($user->last_login_at)
                                        <span title="{{ $user->last_login_at->format('d M Y, H:i:s') }}">
                                            {{ $user->last_login_at->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">Belum pernah login</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-4">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 action-btn" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg></a>
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Peringatan: Menghapus pengguna tidak dapat dibatalkan. Apakah Anda yakin ingin melanjutkan?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200 action-btn" title="Hapus"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-10 text-center text-gray-500"><div class="flex flex-col items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><p class="font-semibold">Belum Ada Pengguna</p><p class="text-sm">Silakan klik tombol "Tambah Pengguna" untuk memulai.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())<div class="mt-6 px-2">{{ $users->links() }}</div>@endif
        </div>
    </div>
@endsection
