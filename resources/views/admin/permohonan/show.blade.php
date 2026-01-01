@extends('layouts.admin')

@section('title', 'Dasbor Administrator')

@section('header-title', 'Dasbor Administrator > Permohonan Layanan > Detail Permohonan')

@section('content')
        <div class="flex justify-between items-center flex-wrap gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Detail Permohonan #{{ $permohonan->no_registrasi }}
            </h2>
            <a href="{{ route('admin.permohonan.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar Permohonan
            </a>
        </div>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- GRID UTAMA 3 KOLOM --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- KOLOM 1: DATA PEMOHON --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Data Pemohon</h3>
                        <dl class="mt-4 space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Lengkap</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $permohonan->nama_lengkap }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Jenis Kelamin</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $permohonan->jenis_kelamin }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Instansi/Lembaga</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $permohonan->instansi }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Profesi</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $permohonan->profesi }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pendidikan Terakhir</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $permohonan->pendidikan }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- KOLOM 2: KONTAK & LAYANAN --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Kontak & Detail Layanan</h3>
                        <dl class="mt-4 space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $permohonan->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nomor Ponsel/WA</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $permohonan->nomor_ponsel }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Layanan yang Diminta</dt>
                                <dd class="mt-1 text-sm font-bold text-gray-900 dark:text-gray-100">{{ $permohonan->layanan_dibutuhkan }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Berkas Terlampir</dt>
                                <dd class="mt-2 text-sm text-gray-900 dark:text-gray-100 space-x-4">
                                    @if($permohonan->path_surat_permohonan)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($permohonan->path_surat_permohonan) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Lihat Surat</a>
                                    @endif
                                    @if($permohonan->path_berkas_permohonan)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($permohonan->path_berkas_permohonan) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Lihat Berkas</a>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- KOLOM 3: RIWAYAT STATUS --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Riwayat & Update Status</h3>
                         <div class="mt-4">
                            <button type="button" class="update-status-btn w-full px-3 py-2 inline-flex text-sm leading-5 font-semibold rounded-md items-center justify-center gap-2 cursor-pointer transition-transform transform hover:scale-105 {{ ['Diajukan' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300','Diproses' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300','Selesai' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300','Ditolak' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',][$permohonan->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200' }}"
                                     data-update-url="{{ route('admin.permohonan.update', $permohonan) }}"
                                     data-current-status="{{ $permohonan->status }}">
                                 <span>Status: {{ $permohonan->status }} (Ubah)</span>
                             </button>
                         </div>
                         {{-- Bagian ini dibuat bisa scroll jika kontennya panjang --}}
                         <div class="mt-6 flow-root" style="max-height: 300px; overflow-y: auto;">
                             <ul role="list" class="-mb-8 pr-4">
                                @forelse($permohonan->statusHistories as $history)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                    <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5">
                                                <div class="text-sm text-gray-800 dark:text-gray-200">
                                                    <span class="font-medium">{{ $history->status }}</span>
                                                </div>
                                                @if($history->keterangan)
                                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400 italic">"{{ $history->keterangan }}"</div>
                                                @endif
                                                <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                                    {{ $history->created_at->diffForHumans() }}
                                                    @if($history->user) oleh {{ $history->user->name }} @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @empty
                                <li><p class="text-sm text-gray-500">Belum ada riwayat status.</p></li>
                                @endforelse
                             </ul>
                         </div>
                    </div>
                </div>
            </div>

            {{-- Bagian Isi Permohonan di bawah grid --}}
            <div class="lg:col-span-3 mt-4">
                 <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Isi Permohonan Lengkap</h3>
                        <p class="mt-4 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $permohonan->isi_permohonan }}</p>
                    </div>
                 </div>
            </div>

        </div>
    </div>

    {{-- Modal dan Script tidak berubah dari versi lengkap terakhir --}}
    <div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center p-4" style="display: none; z-index: 100;">
        <div class="relative mx-auto p-6 border w-full shadow-lg rounded-lg bg-white dark:bg-gray-800" style="max-width: 600px;">

             <div class="text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-200 mb-2">Perbarui Status Permohonan</h3>
                <form id="statusUpdateForm" action="" method="POST" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <label for="statusSelect" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status Baru</label>
                        <select id="statusSelect" name="status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @foreach(\App\Models\Permohonan::STATUSES as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="disposisiWrapper" class="mb-4" style="display: none;">
                        <label for="disposisiSelect" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Disposisi ke Bagian Terkait</label>
                        <select id="disposisiSelect" name="disposisi" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">-- Pilih Bagian --</option>
                            <option value="Persuratan">Persuratan</option>
                            <option value="Tim Penerjemahan">Tim Penerjemahan</option>
                            <option value="Tim Penyuntingan">Tim Penyuntingan</option>
                            <option value="Tim UKBI">Tim UKBI</option>
                            <option value="Tim BIPA">Tim BIPA</option>
                            <option value="Tim Perlindungan Sastra">Tim Perlindungan Sastra</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="keteranganText" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan/Catatan <span class="text-red-500">*</span></label>
                        <textarea id="keteranganText" name="keterangan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required></textarea>
                    </div>
                     <div style="display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 10px; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">

    {{-- Tombol Batal --}}
        <button id="cancelModalBtn" type="button"
            style="padding: 8px 16px; font-size: 0.875rem; font-weight: 600; border-radius: 6px; border: 1px solid #d1d5db; background-color: #fff; color: #374151; cursor: pointer;">
            Batal
        </button>

    {{-- Tombol Simpan Perubahan --}}
        <button id="saveModalBtn" type="submit"
            style="padding: 8px 16px; font-size: 0.875rem; font-weight: 600; border-radius: 6px; border: 1px solid transparent; background-color: #2563eb; color: #ffffff; cursor: pointer;">
            Simpan Perubahan
        </button>
                </form>
            </div>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', (event) => {
    // ===================================================================
    // BAGIAN 1: LOGIKA UNTUK TOGGLE KOLOM DENGAN VALIDASI & PENYIMPANAN
    // ===================================================================
    const table = document.getElementById('permohonanTable');
    const toggles = document.querySelectorAll('.column-toggle');
    const colStorageKey = 'ultebbpj_column_visibility';

    if(table && toggles.length > 0) {
        const applyVisibility = () => {
            toggles.forEach(toggle => {
                const targetClass = toggle.dataset.targetCol;
                const cells = table.querySelectorAll(`.${targetClass}`);
                if (toggle.checked) {
                    cells.forEach(cell => cell.classList.remove('col-hidden'));
                } else {
                    cells.forEach(cell => cell.classList.add('col-hidden'));
                }
            });
        };

        const saveColPreferences = () => {
            const preferences = {};
            toggles.forEach(toggle => {
                preferences[toggle.dataset.targetCol] = toggle.checked;
            });
            localStorage.setItem(colStorageKey, JSON.stringify(preferences));
        };

        const loadColPreferences = () => {
            const savedPrefs = JSON.parse(localStorage.getItem(colStorageKey));
            if (savedPrefs) {
                toggles.forEach(toggle => {
                    const targetClass = toggle.dataset.targetCol;
                    if (savedPrefs[targetClass] !== undefined) {
                        toggle.checked = savedPrefs[targetClass];
                    }
                });
            }
        };

        toggles.forEach(toggle => {
            toggle.addEventListener('change', (e) => {
                const checkedCount = document.querySelectorAll('.column-toggle:checked').length;
                if (checkedCount < 2) {
                    alert('Minimal 2 kolom harus selalu ditampilkan.');
                    e.target.checked = true; // Batalkan uncheck
                    return; // Hentikan proses
                }
                applyVisibility();
                saveColPreferences();
            });
        });

        // Jalankan saat halaman pertama kali dimuat
        loadColPreferences();
        applyVisibility();
    }

    // ===================================================================
    // BAGIAN 2: LOGIKA UNTUK DROPDOWN PENGATURAN DENGAN KLIK
    // ===================================================================
    const settingsBtn = document.getElementById('settingsIconBtn');
    const controlsPanel = document.getElementById('controlsPanel');
    if (settingsBtn && controlsPanel) {
        settingsBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            controlsPanel.classList.toggle('is-active');
        });
        // Menutup dropdown jika klik di luar area
        document.addEventListener('click', (e) => {
            if (controlsPanel.classList.contains('is-active') && !settingsBtn.contains(e.target) && !controlsPanel.contains(e.target)) {
                controlsPanel.classList.remove('is-active');
            }
        });
    }

    // ===================================================================
    // BAGIAN 3: LOGIKA UNTUK FILTER OTOMATIS
    // ===================================================================
    const filterForm = document.getElementById('filterForm');
    if(filterForm) {
        const dateFilterSelect = document.getElementById('dateFilterSelect');
        const customDateWrapper = document.getElementById('customDateWrapper');
        const statusFilterSelect = document.getElementById('statusFilterSelect');

        // Listener untuk filter tanggal
        if(dateFilterSelect && customDateWrapper){
            dateFilterSelect.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customDateWrapper.style.display = 'flex';
                } else {
                    customDateWrapper.style.display = 'none';
                    filterForm.submit();
                }
            });
        }
        // Listener untuk filter status
        if(statusFilterSelect){
            statusFilterSelect.addEventListener('change', function() {
                filterForm.submit();
            });
        }
    }

    // ===================================================================
    // BAGIAN 4: LOGIKA UNTUK MODAL UPDATE STATUS
    // ===================================================================
    // Logika untuk Modal Update Status
        const statusModal = document.getElementById('statusModal');
        if (statusModal) {
            const statusUpdateForm = document.getElementById('statusUpdateForm');
            const statusSelect = document.getElementById('statusSelect');
            const disposisiWrapper = document.getElementById('disposisiWrapper');
            const disposisiSelect = document.getElementById('disposisiSelect');
            const keteranganText = document.getElementById('keteranganText');
            const cancelModalBtn = document.getElementById('cancelModalBtn');
            const updateStatusBtns = document.querySelectorAll('.update-status-btn');

            if(statusUpdateForm && statusSelect && disposisiWrapper && disposisiSelect && keteranganText && cancelModalBtn && updateStatusBtns.length > 0) {
                const handleDisposisiVisibility = () => {
                    if (statusSelect.value === 'Diproses') {
                        disposisiWrapper.style.display = 'block';
                        disposisiSelect.required = true;
                    } else {
                        disposisiWrapper.style.display = 'none';
                        disposisiSelect.required = false;
                        disposisiSelect.value = '';
                    }
                };

                statusSelect.addEventListener('change', () => {
                    handleDisposisiVisibility();
                    const selectedStatus = statusSelect.value;
                    if (keteranganText.value === '' || keteranganText.value.startsWith('Permohonan layanan') || keteranganText.value.startsWith('Status permohonan')) {
                        if (selectedStatus === 'Selesai') {
                            keteranganText.value = 'Permohonan layanan telah selesai diproses.';
                        } else if (selectedStatus === 'Ditolak') {
                            keteranganText.value = 'Permohonan layanan tidak dapat kami proses saat ini.';
                        } else if (selectedStatus !== 'Diproses') {
                            keteranganText.value = 'Status permohonan diubah menjadi ' + selectedStatus + '.';
                        } else {
                            keteranganText.value = ''; // Kosongkan untuk disposisi
                        }
                    }
                });

                disposisiSelect.addEventListener('change', function() {
                    if (this.value !== '') {
                        const selectedTeam = this.options[this.selectedIndex].text;
                        keteranganText.value = `Permohonan layanan telah didisposisikan ke bagian ${selectedTeam} untuk ditindaklanjuti.`;
                    }
                });

                updateStatusBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const url = this.dataset.updateUrl;
                        const currentStatus = this.dataset.currentStatus;
                        statusUpdateForm.action = url;
                        statusSelect.value = currentStatus;
                        keteranganText.value = '';
                        handleDisposisiVisibility();
                        statusModal.style.display = 'flex';
                    });
                });

                const closeModal = () => { statusModal.style.display = 'none'; };
                cancelModalBtn.addEventListener('click', closeModal);
                statusModal.addEventListener('click', (e) => { if (e.target === statusModal) { closeModal(); } });
            }
    }
});
</script>
@endsection