@extends('layouts.public')

@section('title', 'Formulir Permohonan Layanan - ULTEBBPJ')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/form-style.css') }}">

    <div class="container">
        <div class="form-header">
            <h2>Formulir Permohonan Layanan</h2>
            <p>Silakan isi formulir di bawah ini untuk mengajukan permohonan layanan.</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form id="permohonanForm" action="{{ route('permohonan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-grid">
                <div class="form-group no-top-border">
                    <label for="nama_lengkap">Nama lengkap *</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-input"
                        value="{{ old('nama_lengkap') }}" required>
                    @error('nama_lengkap')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group no-top-border">
                    <label for="instansi">Instansi/Lembaga/Komunitas *</label>
                    <input type="text" id="instansi" name="instansi" class="form-input" value="{{ old('instansi') }}"
                        required>
                    @error('instansi')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group no-top-border">
                    <label for="email">Pos-el (E-mail) *</label>
                    <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group no-top-border">
                    <label for="nomor_ponsel">Nomor ponsel/WA *</label>
                    <input type="text" id="nomor_ponsel" name="nomor_ponsel" class="form-input"
                        value="{{ old('nomor_ponsel') }}" required>
                    @error('nomor_ponsel')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 1. JENIS KELAMIN --}}
                <div class="form-group full-width">
                    <label>Jenis Kelamin *</label>
                    
                    <div class="radio-horizontal-group">
                        
                        {{-- Loop Data dari Controller --}}
                        @foreach($jenisKelamin as $jk)
                            <div class="radio-item">
                                {{-- ID dibuat unik pakai $jk->id agar label berfungsi --}}
                                <input type="radio" 
                                    id="jk_{{ $jk->id }}" 
                                    name="jenis_kelamin" 
                                    value="{{ $jk->name }}" 
                                    {{-- Cek old input agar tidak reset saat validasi gagal --}}
                                    {{ old('jenis_kelamin') == $jk->name ? 'checked' : '' }} 
                                    required>
                                
                                <label for="jk_{{ $jk->id }}">
                                    {{ $jk->name }}
                                </label>
                            </div>
                        @endforeach

                    </div>

                    @error('jenis_kelamin')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 2. PENDIDIKAN TERAKHIR --}}
                <div class="form-group full-width">
                    <label>Pendidikan Terakhir *</label>
                    <div class="radio-grid-2-col">
                        @foreach ($pendidikan as $item)
                            <div class="radio-item">
                                <input type="radio" id="pendidikan_{{ $item->id }}" name="pendidikan" value="{{ $item->name }}"
                                    {{ old('pendidikan') == $item->name ? 'checked' : '' }} required>
                                <label for="pendidikan_{{ $item->id }}">{{ $item->name }}</label>
                            </div>
                        @endforeach
                        <div class="radio-item">
                            <input type="radio" id="pendidikan_lainnya_radio" name="pendidikan" value="lainnya" 
                                {{ old('pendidikan') == 'lainnya' ? 'checked' : '' }} required>
                            <label for="pendidikan_lainnya_radio">Lainnya</label>
                        </div>
                    </div>
                    
                    {{-- Logic display: jika old value adalah 'lainnya', maka tampilkan block, jika tidak hidden --}}
                    <div class="lainnya-input-wrapper" id="pendidikan_lainnya_wrapper" 
                        style="{{ old('pendidikan') == 'lainnya' ? 'display: block;' : 'display: none;' }}">
                        <input type="text" name="pendidikan_lainnya" id="pendidikan_lainnya_text" class="form-input"
                            value="{{ old('pendidikan_lainnya') }}" placeholder="Sebutkan pendidikan...">
                    </div>
                    @error('pendidikan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 3. PROFESI --}}
                <div class="form-group full-width">
                    <label>Profesi *</label>
                    <div class="radio-grid-2-col">
                        @foreach ($profesi as $item)
                            <div class="radio-item">
                                <input type="radio" id="profesi_{{ $item->id }}" name="profesi" value="{{ $item->name }}"
                                    {{ old('profesi') == $item->name ? 'checked' : '' }} required>
                                <label for="profesi_{{ $item->id }}">{{ $item->name }}</label>
                            </div>
                        @endforeach
                        <div class="radio-item">
                            <input type="radio" id="profesi_lainnya_radio" name="profesi" value="lainnya" 
                                {{ old('profesi') == 'lainnya' ? 'checked' : '' }} required>
                            <label for="profesi_lainnya_radio">Lainnya</label>
                        </div>
                    </div>

                    <div class="lainnya-input-wrapper" id="profesi_lainnya_wrapper" 
                        style="{{ old('profesi') == 'lainnya' ? 'display: block;' : 'display: none;' }}">
                        <input type="text" name="profesi_lainnya" id="profesi_lainnya_text" class="form-input"
                            value="{{ old('profesi_lainnya') }}" placeholder="Sebutkan profesi...">
                    </div>
                    @error('profesi')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 4. LAYANAN YANG DIBUTUHKAN --}}
                <div class="form-group full-width">
                    <label>Layanan yang dibutuhkan *</label>

                    {{-- Tetap simpan old value di hidden input agar validasi backend tetap membaca data gabungan --}}
                    <input type="hidden" name="layanan_dibutuhkan" id="layanan_final_input" value="{{ old('layanan_dibutuhkan') }}" required>

                    {{-- 4.1. PILIHAN UTAMA --}}
                    <div class="radio-grid-2-col">
                        @foreach ($layanan as $item)
                            <div class="radio-item">
                                <input type="radio" id="utama_{{ $item->id }}" name="layanan_utama" value="{{ $item->name }}"
                                    data-id="{{ $item->id }}"
                                    data-has-sub="{{ $item->subs->count() > 0 ? 'true' : 'false' }}"
                                    {{ old('layanan_utama') == $item->name ? 'checked' : '' }} required>
                                <label for="utama_{{ $item->id }}">{{ $item->name }}</label>
                            </div>
                        @endforeach
                    </div>

                    {{-- 4.2. SUB PILIHAN (DINAMIS) --}}
                    @foreach ($layanan as $item)
                        @if($item->subs->count() > 0)
                            {{-- Logic Display: Cek apakah parent-nya dipilih di old request --}}
                            <div id="sub_container_{{ $item->id }}" class="sub-layanan" 
                                style="{{ old('layanan_utama') == $item->name ? 'display: block;' : 'display:none;' }}">

                                <h4 class="kategori-layanan">Pilih Jenis {{ $item->name }}:</h4>

                                <div class="radio-grid-2-col">
                                    @foreach ($item->subs as $sub)
                                        <div class="radio-item">
                                            <input type="radio" id="sub_{{ $sub->id }}" name="sub_pilihan" value="{{ $sub->name }}"
                                                data-parent-name="{{ $item->name }}" class="js-sub-radio"
                                                {{ old('sub_pilihan') == $sub->name ? 'checked' : '' }}>
                                            <label for="sub_{{ $sub->id }}">{{ $sub->name }}</label>
                                        </div>
                                    @endforeach

                                    {{-- Opsi Lainnya (Bantuan Teknis) --}}
                                    @if($item->name === 'Fasilitasi Bantuan Teknis')
                                        <div class="radio-item">
                                            <input type="radio" id="sub_lainnya_{{ $item->id }}" name="sub_pilihan" value="lainnya"
                                                data-parent-name="{{ $item->name }}" class="js-sub-lainnya"
                                                {{ old('sub_pilihan') == 'lainnya' ? 'checked' : '' }}>
                                            <label for="sub_lainnya_{{ $item->id }}">Lainnya:</label>
                                        </div>
                                    @endif
                                </div>

                                {{-- Input Text Lainnya --}}
                                @if($item->name === 'Fasilitasi Bantuan Teknis')
                                    {{-- 
                                    PENTING: Tambahkan 'name' pada input text ini agar valuenya bisa diambil old(). 
                                    Saya beri nama 'sub_lainnya_text'.
                                    --}}
                                    <div class="lainnya-input-wrapper" id="input_lainnya_wrapper_{{ $item->id }}"
                                        style="{{ old('sub_pilihan') == 'lainnya' && old('layanan_utama') == $item->name ? 'display: block;' : 'display: none;' }}">
                                        <input type="text" id="text_lainnya_{{ $item->id }}" name="sub_lainnya_text" class="form-input"
                                            value="{{ old('sub_lainnya_text') }}"
                                            placeholder="Sebutkan Fasilitasi Bantuan Teknis yang dibutuhkan...">
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach

                    @error('layanan_dibutuhkan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group full-width">
                    <label for="isi_permohonan">Isi permohonan layanan Anda *</label>
                    <textarea id="isi_permohonan" name="isi_permohonan" rows="4" class="form-input"
                        required>{{ old('isi_permohonan') }}</textarea>
                    @error('isi_permohonan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width no-top-border">
                    <label for="surat_permohonan" class="block font-medium mb-1">Unggah surat permohonan (PDF, maks 2MB)
                        *</label>

                    {{-- LIST POIN INSTRUKSI DARI DATABASE --}}
                    <ul class="list-disc ml-5 mb-3 text-sm text-gray-600 space-y-1">
                        
                        @foreach($instruksi as $item)
                            <li>
                                @if(!empty($item->file_template))
                                    @php
                                        // Logika memecah kalimat
                                        $parts = explode(' ', $item->name);
                                        $count = count($parts);

                                        if ($count > 1) {
                                            // Ambil 2 kata terakhir
                                            $linkText = implode(' ', array_slice($parts, -2)); 
                                            // Ambil sisanya untuk teks depan
                                            $frontText = implode(' ', array_slice($parts, 0, $count - 2));
                                        } else {
                                            $linkText = $item->name;
                                            $frontText = '';
                                        }
                                    @endphp

                                    {{-- Tampilkan teks depan --}}
                                    @if($frontText)
                                        {{ $frontText }}
                                    @endif

                                    {{-- Tampilkan Link Download (2 kata terakhir) --}}
                                    <a href="{{ Storage::url($item->file_template) }}" download
                                    class="text-blue-600 hover:underline hover:text-blue-800 font-medium">
                                        {{ $linkText }}
                                    </a>
                                
                                @else
                                    {{-- Jika tidak ada file, tampilkan teks biasa --}}
                                    {{ $item->name }}
                                @endif
                            </li>
                        @endforeach

                    </ul>

                    <input type="file" id="surat_permohonan" name="surat_permohonan" class="form-input" required>

                    @error('surat_permohonan')
                        <div class="error-message text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width no-top-border">
                    <label for="berkas_permohonan">Unggah lampiran permohonan (Jika ada, PDF, maksimal 5MB)
                    </label>
                    <ul class="list-disc ml-5 mb-3 text-sm text-gray-600 space-y-1">
                        @foreach($instruksiUnggahLampiran as $item)
                            <li>
                                @if(!empty($item->file_template))
                                    @php
                                        // Logika memecah kalimat
                                        $parts = explode(' ', $item->name);
                                        $count = count($parts);

                                        if ($count > 1) {
                                            // Ambil 2 kata terakhir
                                            $linkText = implode(' ', array_slice($parts, -2)); 
                                            // Ambil sisanya untuk teks depan
                                            $frontText = implode(' ', array_slice($parts, 0, $count - 2));
                                        } else {
                                            $linkText = $item->name;
                                            $frontText = '';
                                        }
                                    @endphp

                                    {{-- Tampilkan teks depan --}}
                                    @if($frontText)
                                        {{ $frontText }}
                                    @endif

                                    {{-- Tampilkan Link Download (2 kata terakhir) --}}
                                    <a href="{{ Storage::url($item->file_template) }}" download
                                    class="text-blue-600 hover:underline hover:text-blue-800 font-medium">
                                        {{ $linkText }}
                                    </a>
                                
                                @else
                                    {{-- Jika tidak ada file, tampilkan teks biasa --}}
                                    {{ $item->name }}
                                @endif
                            </li>
                        @endforeach

                    </ul>
                    <input type="file" id="berkas_permohonan" name="berkas_permohonan" class="form-input">
                    @error('berkas_permohonan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width no-top-border">
                    <label>Verifikasi Keamanan *</label>

                    {{-- Div ini otomatis jadi checkbox "I'm not a robot" --}}
                    <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>

                    {{-- Pesan Error --}}
                    @error('g-recaptcha-response')
                        <div class="error-message text-red-600 text-sm mt-1">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <button type="button" id="submitBtn" class="submit-button">Kirim Permohonan</button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>

        // Hapus semua script lama di sini
        document.addEventListener('DOMContentLoaded', function () {
            
            $('#reload-captcha').click(function () {
                $.ajax({
                    type: 'GET',
                    url: '{{ route("captcha.refresh") }}', // Route bawaan package mews
                    success: function (data) {
                        $('#captcha-img').html(data.captcha);
                    }
                });
            });

            // SweetAlert2 Confirmation
            const submitBtn = document.getElementById('submitBtn');
            const permohonanForm = document.getElementById('permohonanForm');

            if (submitBtn) {
                submitBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Konfirmasi Pengiriman',
                        text: "Apakah Anda yakin data yang diisi sudah benar?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0056b3',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Kirim!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            permohonanForm.submit();
                        }
                    });
                });
            }

            // Handler untuk "Lainnya" pada Pendidikan dan Profesi
            function handleLainnya(radioGroupName, wrapperId, textInputId) {
                const radios = document.getElementsByName(radioGroupName);
                const wrapper = document.getElementById(wrapperId);
                const textInput = document.getElementById(textInputId);
                if (!radios.length || !wrapper || !textInput) return;

                radios.forEach(radio => {
                    radio.addEventListener('change', function () {
                        if (this.value === 'lainnya') {
                            wrapper.style.display = 'block';
                            textInput.required = true;
                            textInput.focus();
                        } else {
                            wrapper.style.display = 'none';
                            textInput.required = false;
                            textInput.value = '';
                        }
                    });
                });
            }

            handleLainnya('pendidikan', 'pendidikan_lainnya_wrapper', 'pendidikan_lainnya_text');
            handleLainnya('profesi', 'profesi_lainnya_wrapper', 'profesi_lainnya_text');

            const finalInput = document.getElementById('layanan_final_input');
            const mainRadios = document.querySelectorAll('input[name="layanan_utama"]');
            const subContainers = document.querySelectorAll('.sub-layanan');
            const subRadios = document.querySelectorAll('.js-sub-radio');
            const lainnyaRadios = document.querySelectorAll('.js-sub-lainnya');

            // 1. HANDLER UTAMA (Kategori Induk)
            mainRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    // Reset Tampilan Sub & Lainnya
                    subContainers.forEach(el => el.style.display = 'none');
                    document.querySelectorAll('.lainnya-input-wrapper').forEach(el => el.style.display = 'none');

                    // Reset Pilihan Sub & Text Lainnya
                    document.querySelectorAll('input[name="sub_pilihan"]').forEach(el => el.checked = false);
                    document.querySelectorAll('[id^="text_lainnya_"]').forEach(el => el.value = '');

                    const hasSub = this.getAttribute('data-has-sub') === 'true';
                    const id = this.getAttribute('data-id');

                    if (hasSub) {
                        // Tampilkan sub-menu
                        const container = document.getElementById(`sub_container_${id}`);
                        if (container) container.style.display = 'block';

                        // Kosongkan nilai final dulu (User WAJIB klik sub-nya nanti)
                        finalInput.value = '';
                    } else {
                        // Kalau tidak punya sub (misal: UKBI), langsung isi nilainya
                        finalInput.value = this.value;
                    }
                });
            });

            // 2. HANDLER SUB BIASA (DIPERBAIKI)
            subRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    // Sembunyikan input text jika user pindah dari "Lainnya" ke opsi biasa
                    const containerId = this.closest('.sub-layanan').id.split('_')[2];
                    const textWrapper = document.getElementById(`input_lainnya_wrapper_${containerId}`);
                    if (textWrapper) textWrapper.style.display = 'none';

                    // --- PERUBAHAN DISINI ---
                    // Dulu: finalInput.value = `${parentName} - ${this.value}`;
                    // Sekarang: Langsung nilainya saja
                    finalInput.value = this.value;

                    console.log("Input:", finalInput.value); // Cek console
                });
            });

            // 3. HANDLER "LAINNYA" (Radio Button)
            lainnyaRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    const containerId = this.closest('.sub-layanan').id.split('_')[2];
                    const textWrapper = document.getElementById(`input_lainnya_wrapper_${containerId}`);
                    const textInput = document.getElementById(`text_lainnya_${containerId}`);

                    if (textWrapper) {
                        textWrapper.style.display = 'block';
                        textInput.focus();

                        // Set default text sementara
                        finalInput.value = 'Lainnya';
                    }
                });
            });

            // 4. HANDLER KETIK TEXT (Untuk opsi Lainnya)
            document.querySelectorAll('[id^="text_lainnya_"]').forEach(input => {
                input.addEventListener('input', function () {
                    const wrapper = this.closest('.sub-layanan');
                    const radioLainnya = wrapper.querySelector('.js-sub-lainnya');

                    if (radioLainnya && radioLainnya.checked) {
                        // --- PERUBAHAN DISINI ---
                        // Cuma simpan "Lainnya: [Isi Ketikan]" tanpa nama induk
                        finalInput.value = `Lainnya: ${this.value}`;
                    }
                });
            });

        });
    </script>
@endsection