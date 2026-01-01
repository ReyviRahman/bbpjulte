@extends('layouts.admin')

@section('title', 'Manajemen Survei Kepuasan Masyarakat')

@section('header-title', 'Dasbor Administrator > Manajemen Survei Kepuasan Masyarakat')

@section('content')

    <div class="py-12">
        <div class="sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-visible shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative"
                            role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="table-header-controls">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">Daftar Survei Kepuasan Masyarakat
                        </h3>
                        <div class="flex items-center gap-4 flex-wrap justify-end">
                            {{-- Form untuk Filter dan Pencarian --}}
                            <form action="{{ route('admin.skm.index') }}" method="GET" id="filterForm"
                                class="flex items-center gap-2 flex-wrap">
                                {{-- Filter Status --}}
                                <select name="status" id="statusFilterSelect" class="filter-input">
                                    <option value="all" @if(request('status') == 'all' || !request('status')) selected @endif>
                                        Semua Status</option>
                                    @foreach(\App\Models\Skm::STATUSES as $status)
                                        <option value="{{ $status }}" @if(request('status') == $status) selected @endif>
                                            {{ $status }}</option>
                                    @endforeach
                                </select>
                                {{-- Filter Tanggal --}}
                                <select name="date_filter" id="dateFilterSelect" class="filter-input">
                                    <option value="all_time" @if(request('date_filter') == 'all_time' || !request('date_filter')) selected @endif>Semua Waktu</option>
                                    <option value="today" @if(request('date_filter') == 'today') selected @endif>Hari Ini
                                    </option>
                                    <option value="last_7_days" @if(request('date_filter') == 'last_7_days') selected @endif>7
                                        Hari Terakhir</option>
                                    <option value="last_month" @if(request('date_filter') == 'last_month') selected @endif>1
                                        Bulan Terakhir</option>
                                    <option value="custom" @if(request('date_filter') == 'custom') selected @endif>Rentang
                                        Kustom</option>
                                </select>
                                <div id="customDateWrapper" class="flex items-center gap-2" @if(request('date_filter') != 'custom') style="display: none;" @endif>
                                    <input type="date" name="start_date" class="filter-input"
                                        value="{{ request('start_date') }}">
                                    <span class="dark:text-gray-400">-</span>
                                    <input type="date" name="end_date" class="filter-input"
                                        value="{{ request('end_date') }}">
                                    <button type="submit" class="btn-filter-apply">Terapkan</button>
                                </div>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3"><svg
                                            class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                        </svg></span>
                                    <input type="text" name="search" placeholder="Cari survei..."
                                        class="search-input-header sm:max-w-[250px] max-w-[170px]" value="{{ request('search') }}">
                                </div>
                            </form>
                            <select id="exportSelect" class="filter-input" onchange="handleExport(this)">
                                <option value="" selected disabled>Pilih Export</option>
                                
                                <option value="{{ route('admin.skm.export_excel', request()->all()) }}">
                                    Export Excel
                                </option>

                                <option value="{{ route('admin.skm.export_pdf', request()->all()) }}">
                                    Export PDF
                                </option>

                                <option value="{{ route('admin.skm.print', request()->all()) }}">
                                    Print
                                </option>
                            </select>
                            {{-- Tombol Pengaturan Tampilan Kolom --}}
                            <div class="settings-dropdown-wrapper">
                                <button id="settingsIconBtn" class="icon-button" aria-label="Pengaturan Tampilan">
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                                    </svg>
                                </button>
                                <div id="controlsPanel" class="dropdown-panel">
                                    <h4 class="text-md font-medium mb-2 text-gray-800 dark:text-gray-200">
                                        Tampilkan/Sembunyikan Kolom:</h4>
                                    <div class="toggle-grid">
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-no"
                                                class="column-toggle" data-target-col="col-no" checked><label
                                                for="toggle-col-no">No.</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-tanggal"
                                                class="column-toggle" data-target-col="col-tanggal" checked><label
                                                for="toggle-col-tanggal">Tanggal</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-nama-petugas"
                                                class="column-toggle" data-target-col="col-nama-petugas" checked><label
                                                for="toggle-col-nama-petugas">Nama Petugas</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-nama"
                                                class="column-toggle" data-target-col="col-nama" checked><label
                                                for="toggle-col-nama">Nama Pemohon</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-instansi"
                                                class="column-toggle" data-target-col="col-instansi" checked><label
                                                for="toggle-col-instansi">Instansi</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-kontak"
                                                class="column-toggle" data-target-col="col-kontak" checked><label
                                                for="toggle-col-kontak">Kontak</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-layanan-didapat"
                                                class="column-toggle" data-target-col="col-layanan-didapat" checked><label
                                                for="toggle-col-layanan-didapat">Layanan Didapat</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-syarat"
                                                class="column-toggle" data-target-col="col-syarat" checked><label
                                                for="toggle-col-syarat">Kesesuaian Persyaratan</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-sistem"
                                                class="column-toggle" data-target-col="col-sistem" checked><label
                                                for="toggle-col-sistem">Prosedur Pelayanan</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-waktu"
                                                class="column-toggle" data-target-col="col-waktu" checked><label
                                                for="toggle-col-waktu">Kecepatan Pelayanan</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-kesesuaian"
                                                class="column-toggle" data-target-col="col-kesesuaian" checked><label
                                                for="toggle-col-kesesuaian">Kesesuaian/Kewajaran Biaya</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-kesesuaian-hasil"
                                                class="column-toggle" data-target-col="col-kesesuaian-hasil" checked><label
                                                for="toggle-col-kesesuaian-hasil">Kesesuaian Pelayanan</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-kemampuan"
                                                class="column-toggle" data-target-col="col-kemampuan" checked><label
                                                for="toggle-col-kemampuan">Kompetensi Petugas</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-kesopanan"
                                                class="column-toggle" data-target-col="col-kesopanan" checked><label
                                                for="toggle-col-kesopanan">Perilaku Petugas Pelayanan</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-penanganan"
                                                class="column-toggle" data-target-col="col-penanganan" checked><label
                                                for="toggle-col-penanganan">Penanganan Pengaduan</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-sarana"
                                                class="column-toggle" data-target-col="col-sarana" checked><label
                                                for="toggle-col-sarana">Kualitas Sarana dan Prasarana</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-pungutan"
                                                class="column-toggle" data-target-col="col-pungutan" checked><label
                                                for="toggle-col-pungutan">Ada Pungutan</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-jenis-pungutan"
                                                class="column-toggle" data-target-col="col-jenis-pungutan" checked><label
                                                for="toggle-col-jenis-pungutan">Jenis Pungutan</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-informasikan"
                                                class="column-toggle" data-target-col="col-informasikan" checked><label
                                                for="toggle-col-informasikan">Akan Informasikan Layanan</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-kritik-saran"
                                                class="column-toggle" data-target-col="col-kritik-saran" checked><label
                                                for="toggle-col-kritik-saran">Kritik Saran</label></div>
                                        <div class="toggle-item"><input type="checkbox" id="toggle-col-status"
                                                class="column-toggle" data-target-col="col-status" checked><label
                                                for="toggle-col-status">Status & Aksi</label></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg mt-4">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="pengaduanTable">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    @php
                                        function sortable_header($column, $displayName)
                                        {
                                            $direction = (request('sort') == $column && request('direction') == 'asc') ? 'desc' : 'asc';
                                            $url = route('admin.skm.index', array_merge(request()->query(), ['sort' => $column, 'direction' => $direction]));
                                            $icon = '';
                                            if (request('sort') == $column) {
                                                $icon = request('direction') == 'asc' ? ' <span class="text-gray-400">&#9650;</span>' : ' <span class="text-gray-400">&#9660;</span>';
                                            }
                                            return "<a href='{$url}' class='flex items-center'>{$displayName}{$icon}</a>";
                                        }
                                    @endphp
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-no">
                                        No.</th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-tanggal">
                                        {!! sortable_header('created_at', 'Tanggal') !!}</th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-nama-petugas">
                                        {!! sortable_header('nama_petugas', 'Nama Petugas') !!}</th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-nama">
                                        {!! sortable_header('nama_pemohon', 'Nama Pemohon') !!}</th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-instansi">
                                        {!! sortable_header('instansi', 'Instansi') !!}</th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-kontak">
                                        Kontak</th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-layanan-didapat">
                                        Layanan Didapat</th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-syarat">
                                        {!! sortable_header('syarat_pengurusan_pelayanan', 'Kesesuaian Persyaratan') !!}
                                    </th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-sistem">
                                        {!! sortable_header('sistem_mekanisme_dan_prosedur_pelayanan', 'Prosedur Pelayanan') !!}
                                    </th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-waktu">
                                        {!! sortable_header('waktu_penyelesaian_pelayanan', 'Kecepatan Pelayanan') !!}</th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-kesesuaian">
                                        {!! sortable_header('kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan', 'Kesesuaian/ Kewajaran Biaya') !!}
                                    </th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-kesesuaian-hasil">
                                        {!! sortable_header('kesesuaian_hasil_pelayanan', 'Kesesuaian Pelayanan') !!}</th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-kemampuan">
                                        {!! sortable_header('kemampuan_petugas_dalam_memberikan_pelayanan', 'Kompetensi Petugas') !!}
                                    </th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-kesopanan">
                                        {!! sortable_header('kesopanan_dan_keramahan_petugas', 'Perilaku Petugas Pelayanan') !!}
                                    </th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-penanganan">
                                        {!! sortable_header('penanganan_pengaduan_saran_dan_masukan', 'Penanganan Pengaduan') !!}
                                    </th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-sarana">
                                        {!! sortable_header('sarana_dan_prasarana_penunjang_pelayanan', 'Kualitas Sarana dan Prasarana') !!}
                                    </th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-pungutan">
                                        Ada Pungutan</th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-jenis-pungutan">
                                        Jenis Pungutan</th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-informasikan">
                                        Akan Informasikan Layanan</th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-kritik-saran">
                                        Kritik Saran</th>
                                    <th scope="col"
                                        class="px-6 text-nowrap py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider col-status">
                                        {!! sortable_header('status', 'Status & Aksi') !!}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($semua_skm as $index => $skm)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-no">
                                            {{ $semua_skm->firstItem() + $index }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-tanggal">
                                            {{ $skm->created_at->format('d M Y, H:i') }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white col-nama-petugas">
                                            <div class="flex items-center justify-between">
                                                <span>{{ $skm->nama_petugas }}</span>
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white col-nama">
                                            <div class="flex items-center justify-between">
                                                <span>{{ $skm->nama_pemohon }}</span>
                                                <a href="{{ route('admin.skm.show', $skm) }}"
                                                    class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 ml-4"
                                                    title="Lihat Detail">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-instansi">
                                            {{ $skm->instansi }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-kontak">
                                            <div>{{ $skm->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 col-layanan-didapat">
                                            {{ \Illuminate\Support\Str::limit($skm->layanan_didapat, 50, '...') }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-syarat">
                                            {{ $skm->syarat_pengurusan_pelayanan }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-sistem">
                                            {{ $skm->sistem_mekanisme_dan_prosedur_pelayanan }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-waktu">
                                            {{ $skm->waktu_penyelesaian_pelayanan }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-kesesuaian">
                                            {{ $skm->kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-kesesuaian-hasil">
                                            {{ $skm->kesesuaian_hasil_pelayanan }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-kemampuan">
                                            {{ $skm->kemampuan_petugas_dalam_memberikan_pelayanan }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-kesopanan">
                                            {{ $skm->kesopanan_dan_keramahan_petugas }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-penanganan">
                                            {{ $skm->penanganan_pengaduan_saran_dan_masukan }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-sarana">
                                            {{ $skm->sarana_dan_prasarana_penunjang_pelayanan }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-pungutan">
                                            {{ $skm->ada_pungutan }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-jenis-pungutan">
                                            {{ $skm->jenis_pungutan ?? '-' }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-informasikan">
                                            {{ $skm->akan_informasikan_layanan }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 col-kritik-saran">
                                            {{ \Illuminate\Support\Str::limit($skm->kritik_saran, 50, '...') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm col-status">
                                            <div class="flex items-center gap-4">
                                                <div>
                                                    @php
                                                        $statusColor = [
                                                            'Publik' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                                            'Privat' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                                        ][$skm->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200';
                                                    @endphp
                                                    <button type="button"
                                                        class="update-status-btn px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full items-center gap-1 cursor-pointer transition-transform transform hover:scale-110 {{ $statusColor }}"
                                                        data-update-url="{{ route('admin.skm.update', $skm) }}"
                                                        data-current-status="{{ $skm->status }}">
                                                        <span>{{ $skm->status }}</span>
                                                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <form action="{{ route('admin.skm.destroy', $skm) }}" method="POST"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus skm ini secara permanen?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                        title="Hapus">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">Data pengaduan tidak
                                            ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">{{ $semua_skm->links() }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal untuk Update Status --}}
    <div id="statusModal"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-start justify-center py-4"
        style="display: none; z-index: 100;">
        <div class="relative mx-auto p-6 border w-full max-w-lg shadow-lg rounded-lg bg-white dark:bg-gray-800"
            style="max-width: 600px;">
            <div class="text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-200 mb-2">Perbarui Status Survei</h3>
                <form id="statusUpdateForm" action="" method="POST" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <label for="statusSelect" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status
                            Baru</label>
                        <select id="statusSelect" name="status" class="mt-1 block w-full filter-input">
                            @foreach(\App\Models\Skm::STATUSES as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button id="cancelModalBtn" type="button"
                            class="px-4 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500">Batal</button>
                        <button id="saveModalBtn" type="submit"
                            class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">Simpan
                            Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .table-header-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .table-header-controls h3 {
            margin: 0;
        }

        .search-input-header,
        .filter-input {
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.875rem;
        }

        .dark .search-input-header,
        .dark .filter-input {
            background-color: #374151;
            border-color: #4b5563;
            color: #f3f4f6;
        }

        .search-input-header {
            padding-left: 2.5rem;
            width: 250px;
        }

        select.filter-input {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        .dark select.filter-input {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        }

        .btn-filter-apply {
            background-color: #16a34a;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-filter-apply:hover {
            background-color: #15803d;
        }

        @media (max-width: 1024px) {
            .table-header-controls {
                flex-direction: column;
                align-items: stretch;
            }
        }

        .toggle-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
        }

        .toggle-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toggle-item input[type="checkbox"] {
            width: 1em;
            height: 1em;
            accent-color: #4f46e5;
            cursor: pointer;
        }

        .toggle-item label {
            font-size: 0.875rem;
            user-select: none;
            cursor: pointer;
            color: #374151 !important;
        }

        .dark .toggle-item label {
            color: #d1d5db !important;
        }

        .col-hidden {
            display: none;
        }

        .settings-dropdown-wrapper {
            position: relative;
        }

        .icon-button {
            background-color: #f3f4f6;
            color: #4b5563;
            border: 1px solid #d1d5db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .dark .icon-button {
            background-color: #374151;
            color: #9ca3af;
            border-color: #4b5563;
        }

        .icon-button:hover {
            background-color: #e5e7eb;
            color: #1f2937;
        }

        .dropdown-panel {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 8px;
            width: 400px;
            max-width: 90vw;
            background-color: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            z-index: 10;
            padding: 1rem;
        }

        .dark .dropdown-panel {
            background-color: #1f2937;
            border-color: #4b5563;
        }

        .dropdown-panel.is-active {
            display: block;
        }
    </style>

    <script>
        function handleExport(selectElement) {
            const url = selectElement.value;
            
            if (url) {
                // Cek apakah URL mengandung kata 'print'
                if (url.includes('print')) {
                    // Buka di tab baru (seperti target="_blank")
                    window.open(url, '_blank');
                } else {
                    // Redirect langsung (untuk download Excel/PDF)
                    window.location.href = url;
                }

                // (Opsional) Reset dropdown kembali ke teks "Export Data" setelah diklik
                selectElement.value = ""; 
            }
        }
        document.addEventListener('DOMContentLoaded', (event) => {
            // Logika untuk toggle kolom
            const table = document.getElementById('pengaduanTable');
            const toggles = document.querySelectorAll('.column-toggle');
            const colStorageKey = 'ultebbpj_pengaduan_col_visibility';
            if (table && toggles.length > 0) {
                const applyVisibility = () => { toggles.forEach(toggle => { const targetClass = toggle.dataset.targetCol; const cells = table.querySelectorAll(`.${targetClass}`); if (toggle.checked) { cells.forEach(cell => cell.classList.remove('col-hidden')); } else { cells.forEach(cell => cell.classList.add('col-hidden')); } }); };
                const saveColPreferences = () => { const preferences = {}; toggles.forEach(toggle => { preferences[toggle.dataset.targetCol] = toggle.checked; }); localStorage.setItem(colStorageKey, JSON.stringify(preferences)); };
                const loadColPreferences = () => { const savedPrefs = JSON.parse(localStorage.getItem(colStorageKey)); if (savedPrefs) { toggles.forEach(toggle => { const targetClass = toggle.dataset.targetCol; if (savedPrefs[targetClass] !== undefined) { toggle.checked = savedPrefs[targetClass]; } }); } };
                toggles.forEach(toggle => {
                    toggle.addEventListener('change', (e) => {
                        const checkedCount = document.querySelectorAll('.column-toggle:checked').length;
                        if (checkedCount < 2) {
                            alert('Minimal 2 kolom harus selalu ditampilkan.');
                            e.target.checked = true;
                            return;
                        }
                        applyVisibility();
                        saveColPreferences();
                    });
                });
                loadColPreferences();
                applyVisibility();
            }

            // Logika untuk dropdown pengaturan
            const settingsBtn = document.getElementById('settingsIconBtn');
            const controlsPanel = document.getElementById('controlsPanel');
            if (settingsBtn && controlsPanel) {
                settingsBtn.addEventListener('click', (e) => { e.stopPropagation(); controlsPanel.classList.toggle('is-active'); });
                document.addEventListener('click', (e) => { if (controlsPanel.classList.contains('is-active') && !settingsBtn.contains(e.target) && !controlsPanel.contains(e.target)) { controlsPanel.classList.remove('is-active'); } });
            }

            // Logika untuk filter
            const filterForm = document.getElementById('filterForm');
            if (filterForm) {
                const dateFilterSelect = document.getElementById('dateFilterSelect');
                const customDateWrapper = document.getElementById('customDateWrapper');
                const statusFilterSelect = document.getElementById('statusFilterSelect');

                if (dateFilterSelect && customDateWrapper) {
                    dateFilterSelect.addEventListener('change', function () {
                        if (this.value === 'custom') {
                            customDateWrapper.style.display = 'flex';
                        } else {
                            customDateWrapper.style.display = 'none';
                            filterForm.submit();
                        }
                    });
                }
                if (statusFilterSelect) {
                    statusFilterSelect.addEventListener('change', function () {
                        filterForm.submit();
                    });
                }
            }

            // Logika untuk Modal Update Status
            const statusModal = document.getElementById('statusModal');
            if (statusModal) {
                const statusUpdateForm = document.getElementById('statusUpdateForm');
                const statusSelect = document.getElementById('statusSelect');
                const cancelModalBtn = document.getElementById('cancelModalBtn');
                const updateStatusBtns = document.querySelectorAll('.update-status-btn');

                if (statusUpdateForm && statusSelect && cancelModalBtn && updateStatusBtns.length > 0) {

                    updateStatusBtns.forEach(btn => {
                        btn.addEventListener('click', function () {
                            const url = this.dataset.updateUrl;
                            const currentStatus = this.dataset.currentStatus;
                            statusUpdateForm.action = url;
                            statusSelect.value = currentStatus;
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