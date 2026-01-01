@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')
@section('header-title', 'Edit Petugas')

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

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Oops!</strong>
                            <span class="block sm:inline">Ada beberapa masalah dengan input Anda.</span>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        <div class="space-y-4">
                            {{-- Nama Pengguna --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full filter-input">
                            </div>
                            {{-- NIP --}}
                            <div>
                                <label for="nip" class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIP</label>
                                <input type="text" name="nip" id="nip" value="{{ old('nip', $user->nip) }}" required class="mt-1 block w-full filter-input">
                            {{-- Email --}}
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full filter-input">
                            </div>

                            {{-- Role --}}
                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                <select name="role" id="role" required class="mt-1 block w-full filter-input">
                                    <option value="">-- Pilih Role --</option>
                                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="petugas" {{ old('role', $user->role) == 'petugas' ? 'selected' : '' }}>Petugas</option>
                                </select>
                            </div>

                            <hr class="dark:border-gray-600">

                            <p class="text-sm text-gray-500">Kosongkan password jika tidak ingin mengubahnya.</p>

                            {{-- Password --}}
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password Baru</label>
                                <input type="password" name="password" id="password" class="mt-1 block w-full filter-input">
                            </div>

                            {{-- Konfirmasi Password --}}
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full filter-input">
                            </div>
                        </div>
                        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Foto Profil</label>
            <div class="mt-2 flex items-center space-x-4">
                <img id="photo-preview" src="{{ $user->profile_photo_url }}" alt="Foto profil {{ $user->name }}" class="h-16 w-16 rounded-full object-cover">
                <input type="file" name="photo" id="photo" class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100
                    dark:file:bg-gray-700 dark:file:text-gray-300 dark:hover:file:bg-gray-600
                "/>
            </div>
             <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengganti foto. Format: JPG, PNG. Maksimal 2MB.</p>
        </div>
                        <div class="mt-6 flex items-center justify-end gap-x-6">
                            <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold leading-6 text-gray-900 dark:text-gray-200">Batal</a>
                            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

{{-- Tambahkan script ini di akhir file untuk live preview --}}
<script>
document.getElementById('photo').addEventListener('change', function(event) {
    if (event.target.files && event.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photo-preview').src = e.target.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }
});
</script>
@endsection
