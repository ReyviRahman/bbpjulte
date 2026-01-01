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
      <div class="flex sm:flex-row flex-col items-center justify-between mb-6">
        <div class="flex items-center gap-1">
          <a href="{{ route('admin.form-skm.index', ['category' => $parent->category]) }}"
            class="inline-flex items-center px-1 py-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </a>
          <h2 class="text-lg font-semibold">Daftar SKM Sub Kategori {{ $parent->name }} </h2>
        </div>
        <button type="button" onclick="document.getElementById('inputModal').style.display = 'flex'"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-blue-100 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10 3a1 1 0 00-1 1v7H3a1 1 0 000 2h6v7a1 1 0 002 0v-7h6a1 1 0 100-2h-6V4a1 1 0 00-1-1z" />
          </svg>
          Tambah Inputan
        </button>

        <div id="inputModal" onclick="if(event.target === this) this.style.display='none'"
          class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-start justify-center py-4"
          style="display: none; z-index: 100;">

          <div class="relative mx-auto p-6 border w-full max-w-lg shadow-lg rounded-lg bg-white dark:bg-gray-800"
            style="max-width: 600px;">

            <div class="text-left">
              <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-200 mb-2">
                Tambah Data Sub {{ ucfirst($parent->name ?? 'Baru') }}
              </h3>

              {{-- Perhatikan Action-nya: Kita kirim ID Parent ke URL --}}
              <form action="{{ route('admin.form-skm.subs.store') }}" method="POST" class="mt-4">
                @csrf

                {{-- Input Hidden Category DIHAPUS SAJA (Tidak diperlukan lagi karena ID bapaknya sudah ada di URL action
                di atas) --}}
                <input type="hidden" name="form_id" value="{{ $parent->id }}">

                <div class="mb-4">
                  <label for="nameInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama Sub Kategori <span class="text-red-500">*</span>
                  </label>

                  <input type="text" id="nameInput" name="name" required 
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div class="flex justify-end gap-2 mt-6">
                  {{-- Tombol Batal --}}
                  <button type="button" onclick="document.getElementById('inputModal').style.display = 'none'"
                    style="padding: 8px 16px; font-size: 0.875rem; font-weight: 600; border-radius: 6px; border: 1px solid #d1d5db; background-color: #fff; color: #374151; cursor: pointer;">
                    Batal
                  </button>

                  {{-- Tombol Simpan --}}
                  <button type="submit"
                    style="padding: 8px 16px; font-size: 0.875rem; font-weight: 600; border-radius: 6px; border: 1px solid transparent; background-color: #2563eb; color: #ffffff; cursor: pointer;">
                    Simpan
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      {{-- Notifikasi --}}
      @if (session('success') || session('error'))
        {{-- x-data: inisialisasi variabel show --}}
        {{-- x-init: tunggu 3000ms (3 detik), lalu ubah show jadi false --}}
        {{-- x-show: elemen hanya tampil jika show == true --}}
        {{-- x-transition: efek animasi memudar --}}

        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
          x-transition:enter="transition ease-out duration-300" x-transition:leave="transition ease-in duration-300"
          x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90" class="mb-6">

          @if (session('success'))
            <div
              class="relative bg-green-100 dark:bg-green-900/30 border-l-4 border-green-500 text-green-700 dark:text-green-300 p-4 shadow-md rounded-r"
              role="alert">
              <div class="flex justify-between items-center">
                <div>
                  <p class="font-bold">Sukses</p>
                  <p>{{ session('success') }}</p>
                </div>
                {{-- Tombol Close Manual (Opsional) --}}
                <button @click="show = false" class="text-green-700 font-bold px-2">
                  &times;
                </button>
              </div>
            </div>
          @endif

          @if (session('error'))
            <div
              class="relative bg-red-100 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-300 p-4 shadow-md rounded-r"
              role="alert">
              <div class="flex justify-between items-center">
                <div>
                  <p class="font-bold">Error</p>
                  <p>{{ session('error') }}</p>
                </div>
                {{-- Tombol Close Manual (Opsional) --}}
                <button @click="show = false" class="text-red-700 font-bold px-2">
                  &times;
                </button>
              </div>
            </div>
          @endif
        </div>
      @endif

      <div class="overflow-x-auto">
        <table
          class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                style="width: 5%;">No.</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Input</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            {{-- Loop Data dari Controller --}}
            @forelse ($subs as $index => $item)
              <tr class="table-hover-row">
                {{-- Nomor Urut --}}
                <td class="px-4 py-4 text-center text-sm text-gray-500">
                  {{ $index + 1 }}
                </td>

                {{-- Nama Input (S1, S2, Petani, dll) --}}
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ $item->name }}
                  </div>
                </td>

                {{-- Aksi --}}
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                  <div class="flex items-center justify-center space-x-4">
                    @if($item->category === 'layanan')
                      <a href="{{ route('admin.form-skm.subs.index', $item->id) }}"
                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 action-btn"
                        title="Edit"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                          fill="blue">
                          <path d="M10 3a1 1 0 00-1 1v7H3a1 1 0 000 2h6v7a1 1 0 002 0v-7h6a1 1 0 100-2h-6V4a1 1 0 00-1-1z" />
                        </svg></a>
                    @endif

                    {{-- TOMBOL EDIT (Memicu Modal Edit) --}}
                    {{-- Kita kirim ID, Nama, dan URL Update ke fungsi JS --}}
                    <button type="button"
                      onclick="openEditModal('{{ $item->id }}', '{{ $item->name }}', '{{ route('admin.form-skm.subs.update', $item->id) }}')"
                      class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 action-btn"
                      title="Edit">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                        <path fill-rule="evenodd"
                          d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                          clip-rule="evenodd" />
                      </svg>
                    </button>

                    {{-- TOMBOL HAPUS --}}
                    <form action="{{ route('admin.form-skm.subs.destroy', $item->id) }}" method="POST"
                      onsubmit="return confirm('Yakin ingin menghapus {{ $item->name }}?');">
                      @csrf
                      @method('DELETE')

                      <button type="submit"
                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200 action-btn"
                        title="Hapus">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd"
                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                        </svg>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              {{-- Tampilan Jika Kosong --}}
              <tr>
                <td colspan="3" class="px-6 py-10 text-center text-gray-500">
                  <div class="flex flex-col items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2 text-gray-400" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="font-semibold">Belum Ada Data Sub {{ ucfirst($parent->name) }}</p>
                    <p class="text-sm">Silakan klik tombol "Tambah Inputan" untuk memulai.</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div id="editModal" onclick="if(event.target === this) closeEditModal()"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-start justify-center py-4"
    style="display: none; z-index: 100;">

    <div class="relative mx-auto p-6 border w-full max-w-lg shadow-lg rounded-lg bg-white dark:bg-gray-800"
      style="max-width: 600px;">

      <div class="text-left">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-200 mb-2">
          Edit Data 
        </h3>

        {{-- Form Edit (Action akan diisi via JS) --}}
        <form id="editForm" method="POST" class="mt-4">
          @csrf
          @method('PUT') {{-- Method PUT wajib untuk update --}}

          <div class="mb-4">
            <label for="editNameInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Nama Input <span class="text-red-500">*</span>
            </label>
            <input type="text" id="editNameInput" name="name" required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
          </div>

          <div class="flex justify-end gap-2 mt-6">
            <button type="button" onclick="closeEditModal()"
              style="padding: 8px 16px; font-size: 0.875rem; font-weight: 600; border-radius: 6px; border: 1px solid #d1d5db; background-color: #fff; color: #374151; cursor: pointer;">
              Batal
            </button>
            <button type="submit"
              style="padding: 8px 16px; font-size: 0.875rem; font-weight: 600; border-radius: 6px; border: 1px solid transparent; background-color: #2563eb; color: #ffffff; cursor: pointer;">
              Update
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    function openEditModal(id, name, updateUrl) {
      // 1. Isi value input dengan nama lama
      document.getElementById('editNameInput').value = name;

      // 2. Ubah action form ke URL update yang benar
      document.getElementById('editForm').action = updateUrl;

      // 3. Tampilkan modal
      document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
      document.getElementById('editModal').style.display = 'none';
    }
  </script>
@endpush