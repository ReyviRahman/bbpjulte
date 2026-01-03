@extends('layouts.public')

@section('title', 'Formulir Survei Kepuasan Masyarakat - ULTEBBPJ')


@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/form-style.css') }}">

    <div class="container">
        <div class="form-header">
            <h2>Formulir Survei Kepuasan Masyarakat</h2>
            <p>Silakan isi formulir di bawah ini untuk mengisi Survei Kepuasan Masyarakat.</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form id="permohonanForm" action="{{ route('skm.store') }}" method="POST">
            @csrf
            <input type="hidden" name="permohonan_id" value="{{ $permohonan_id ?? '' }}">
            <div class="form-grid">
                <div class="form-group no-top-border">
                    <label for="nama_petugas">Nama Petugas yang melayani *</label>
                    <select id="nama_petugas" name="nama_petugas" class="form-input" required>
                        <option value="">-- Pilih Petugas --</option>

                        @foreach ($petugas as $p)
                            <option value="{{ $p }}" {{ old('nama_petugas') == $p ? 'selected' : '' }}>
                                {{ $p }}
                            </option>
                        @endforeach
                    </select>

                    @error('nama_petugas')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group no-top-border">
                    <label for="nama_pemohon">Nama pemohon *</label>
                    <input type="text" id="nama_pemohon" name="nama_pemohon" class="form-input"
                        value="{{ old('nama_pemohon') }}" required>
                    @error('nama_pemohon')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group no-top-border">
                    <label for="email">Pos-el (<i>E-mail</i>) *</label>
                    <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" required>
                    @error('email')
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
                <div class="form-group full-width">
                    <label>Jenis Kelamin *</label>
                    <div class="radio-horizontal-group">
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

                <div class="form-group full-width">
                    <label>Pendidikan Terakhir *</label>
                    <div class="radio-grid-2-col">
                        @foreach ($pendidikan as $item)
                            <div class="radio-item">
                                <input type="radio" id="pendidikan_{{ $item->id }}" name="pendidikan" value="{{ $item->name }}"
                                    {{ old('pendidikan') == $item->name ? 'checked' : '' }} required> <label for="pendidikan_{{ $item->id }}">{{ $item->name }}</label>
                            </div>
                        @endforeach
                        <div class="radio-item">
                            <input type="radio" id="pendidikan_lainnya_radio" name="pendidikan" value="lainnya" 
                            {{ old('pendidikan') == 'lainnya' ? 'checked' : '' }} required>
                            <label for="pendidikan_lainnya_radio">Lainnya</label>
                        </div>
                    </div>
                    <div class="lainnya-input-wrapper" id="pendidikan_lainnya_wrapper" 
                        style="{{ old('pendidikan') == 'lainnya' ? 'display: block;' : 'display: none;' }}">
                        
                        <input type="text" name="pendidikan_lainnya" id="pendidikan_lainnya_text" class="form-input"
                            placeholder="Sebutkan pendidikan..." 
                            value="{{ old('pendidikan_lainnya') }}"> 
                    </div>
                    @error('pendidikan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label>Profesi *</label>
                    <div class="radio-grid-2-col">
                        @foreach ($profesi as $item)
                            <div class="radio-item">
                                {{-- Gunakan ID dari database untuk atribut HTML ID --}}
                                <input type="radio" id="profesi_{{ $item->id }}" name="profesi" value="{{ $item->name }}"
                                    {{-- LOGIC TAMBAHAN: Cek jika data lama sama dengan item ini --}}
                                    {{ old('profesi') == $item->name ? 'checked' : '' }}
                                    required>

                                {{-- Label harus cocok dengan ID input --}}
                                <label for="profesi_{{ $item->id }}">{{ $item->name }}</label>
                            </div>
                        @endforeach
                        
                        <div class="radio-item">
                            <input type="radio" id="profesi_lainnya_radio" name="profesi" value="lainnya" 
                                {{-- LOGIC TAMBAHAN: Cek jika data lama adalah 'lainnya' --}}
                                {{ old('profesi') == 'lainnya' ? 'checked' : '' }} 
                                required>
                            <label for="profesi_lainnya_radio">Lainnya</label>
                        </div>
                    </div>
                    
                    {{-- LOGIC TAMBAHAN: Ubah style display berdasarkan old input --}}
                    <div class="lainnya-input-wrapper" id="profesi_lainnya_wrapper" 
                        style="{{ old('profesi') == 'lainnya' ? 'display: block;' : 'display: none;' }}">
                        
                        <input type="text" name="profesi_lainnya" id="profesi_lainnya_text" class="form-input"
                            placeholder="Sebutkan profesi..."
                            {{-- LOGIC TAMBAHAN: Isi value dengan inputan teks sebelumnya --}}
                            value="{{ old('profesi_lainnya') }}">
                    </div>
                    
                    @error('profesi')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label>Layanan yang didapatkan *</label>

                    {{-- INPUT TERSEMBUNYI (Final Result) --}}
                    {{-- Tambahkan value old() agar data gabungan JS tidak hilang total --}}
                    <input type="hidden" name="layanan_didapat" id="layanan_final_input" value="{{ old('layanan_didapat') }}" required>

                    {{-- 1. PILIHAN UTAMA (Looping dari Database) --}}
                    <div class="radio-grid-2-col">
                        @foreach ($layanan as $item)
                            <div class="radio-item">
                                <input type="radio" id="utama_{{ $item->id }}" name="layanan_utama" value="{{ $item->name }}"
                                    data-id="{{ $item->id }}"
                                    data-has-sub="{{ $item->subs->count() > 0 ? 'true' : 'false' }}"
                                    {{-- LOGIC: Cek layanan utama --}}
                                    {{ old('layanan_utama') == $item->name ? 'checked' : '' }}>
                                <label for="utama_{{ $item->id }}">{{ $item->name }}</label>
                            </div>
                        @endforeach
                    </div>

                    {{-- 2. SUB PILIHAN (Looping Hidden Containers) --}}
                    @foreach ($layanan as $item)
                        @if($item->subs->count() > 0)
                            {{-- Container Sub: Tampilkan jika parent-nya terpilih di old input --}}
                            <div id="sub_container_{{ $item->id }}" class="sub-layanan" 
                                style="{{ old('layanan_utama') == $item->name ? 'display:block;' : 'display:none;' }}">

                                <h4 class="kategori-layanan">Pilih Jenis {{ $item->name }}:</h4>

                                <div class="radio-grid-2-col">
                                    {{-- Loop Item Sub Kategori --}}
                                    @foreach ($item->subs as $sub)
                                        <div class="radio-item">
                                            <input type="radio" id="sub_{{ $sub->id }}" name="sub_pilihan" 
                                                value="{{ $sub->name }}" class="js-sub-radio"
                                                {{-- LOGIC: Cek sub pilihan --}}
                                                {{ old('sub_pilihan') == $sub->name ? 'checked' : '' }}>
                                            <label for="sub_{{ $sub->id }}">{{ $sub->name }}</label>
                                        </div>
                                    @endforeach

                                    {{-- LOGIC KHUSUS: Opsi "Lainnya" untuk Bantuan Teknis --}}
                                    @if($item->name === 'Fasilitasi Bantuan Teknis')
                                        <div class="radio-item">
                                            <input type="radio" id="sub_lainnya_{{ $item->id }}" name="sub_pilihan" value="lainnya"
                                                class="js-sub-lainnya"
                                                {{-- LOGIC: Cek jika memilih lainnya --}}
                                                {{ old('sub_pilihan') == 'lainnya' ? 'checked' : '' }}>
                                            <label for="sub_lainnya_{{ $item->id }}">Lainnya:</label>
                                        </div>
                                    @endif
                                </div>

                                {{-- Input Text Lainnya (Khusus Bantuan Teknis) --}}
                                @if($item->name === 'Fasilitasi Bantuan Teknis')
                                    {{-- Wrapper: Tampilkan jika Parent Bantuan Teknis AKTIF DAN Sub Pilihan LAINNYA aktif --}}
                                    <div class="lainnya-input-wrapper" id="input_lainnya_wrapper_{{ $item->id }}"
                                        style="{{ old('layanan_utama') == $item->name && old('sub_pilihan') == 'lainnya' ? 'display: block;' : 'display: none;' }}">
                                        
                                        {{-- NOTE: Input ini belum punya 'name', jadi old() tidak bisa otomatis. 
                                            Saya tambahkan name="detail_bantuan_teknis" agar bisa di-old-kan. 
                                            Jika Anda handle via JS, pastikan JS membaca value ini. --}}
                                        <input type="text" id="text_lainnya_{{ $item->id }}" name="detail_bantuan_teknis" class="form-input"
                                            placeholder="Sebutkan Fasilitasi Bantuan Teknis yang dibutuhkan..."
                                            value="{{ old('detail_bantuan_teknis') }}">
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach

                    @error('layanan_didapat')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label>Syarat pengurusan pelayanan *</label>
                    <div class="flex flex-col sm:flex-row gap-4">
                        @foreach ($opsiUSatu as $nilai => $keterangan)
                            <div class="radio-item">
                                <input type="radio" id="syarat_pengurusan_pelayanan_{{ $nilai }}"
                                    name="syarat_pengurusan_pelayanan" value="{{ $nilai }}" {{ old('syarat_pengurusan_pelayanan') == $nilai ? 'checked' : '' }} required>
                                {{-- Menampilkan angka dan teks sesuai array di atas --}}
                                <label for="syarat_pengurusan_pelayanan_{{ $nilai }}">{{ $nilai }}.
                                    {{ $keterangan }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('syarat_pengurusan_pelayanan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label>Sistem Mekanisme Dan Prosedur Pelayanan *</label>
                    <div class="flex flex-col sm:flex-row gap-4">
                        @foreach ($opsiUDua as $nilai => $keterangan)
                            <div class="radio-item">
                                <input type="radio" id="sistem_mekanisme_dan_prosedur_pelayanan_{{ $nilai }}"
                                    name="sistem_mekanisme_dan_prosedur_pelayanan" value="{{ $nilai }}" {{ old('sistem_mekanisme_dan_prosedur_pelayanan') == $nilai ? 'checked' : '' }} required>
                                {{-- Menampilkan angka dan teks sesuai array di atas --}}
                                <label for="sistem_mekanisme_dan_prosedur_pelayanan_{{ $nilai }}">{{ $nilai }}.
                                    {{ $keterangan }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('sistem_mekanisme_dan_prosedur_pelayanan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label>Waktu Penyelesaian Pelayanan *</label>
                    <div class="flex flex-col sm:flex-row gap-4">
                        @foreach ($opsiUTiga as $nilai => $keterangan)
                            <div class="radio-item">
                                <input type="radio" id="waktu_penyelesaian_pelayanan_{{ $nilai }}"
                                    name="waktu_penyelesaian_pelayanan" value="{{ $nilai }}" {{ old('waktu_penyelesaian_pelayanan') == $nilai ? 'checked' : '' }} required>
                                {{-- Menampilkan angka dan teks sesuai array di atas --}}
                                <label for="waktu_penyelesaian_pelayanan_{{ $nilai }}">{{ $nilai }}.
                                    {{ $keterangan }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('waktu_penyelesaian_pelayanan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label>Kesesuaian Biaya Pelayanan Dengan yang Diinformasikan *</label>
                    <div class="flex flex-col sm:flex-row gap-4">
                        @foreach ($opsiUEmpat as $nilai => $keterangan)
                            <div class="radio-item">
                                <input type="radio" id="kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan_{{ $nilai }}"
                                    name="kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan" value="{{ $nilai }}" {{ old('kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan') == $nilai ? 'checked' : '' }} required>
                                {{-- Menampilkan angka dan teks sesuai array di atas --}}
                                <label for="kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan_{{ $nilai }}">{{ $nilai }}.
                                    {{ $keterangan }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label>Kesesuaian Hasil Pelayanan *</label>
                    <div class="flex flex-col sm:flex-row gap-4">
                        @foreach ($opsiULima as $nilai => $keterangan)
                            <div class="radio-item">
                                <input type="radio" id="kesesuaian_hasil_pelayanan_{{ $nilai }}"
                                    name="kesesuaian_hasil_pelayanan" value="{{ $nilai }}" {{ old('kesesuaian_hasil_pelayanan') == $nilai ? 'checked' : '' }} required>
                                {{-- Menampilkan angka dan teks sesuai array di atas --}}
                                <label for="kesesuaian_hasil_pelayanan_{{ $nilai }}">{{ $nilai }}.
                                    {{ $keterangan }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('kesesuaian_hasil_pelayanan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label>Kemampuan Petugas Dalam Memberikan Pelayanan *</label>
                    <div class="flex flex-col sm:flex-row gap-4">
                        @foreach ($opsiUEnam as $nilai => $keterangan)
                            <div class="radio-item">
                                <input type="radio" id="kemampuan_petugas_dalam_memberikan_pelayanan_{{ $nilai }}"
                                    name="kemampuan_petugas_dalam_memberikan_pelayanan" value="{{ $nilai }}" {{ old('kemampuan_petugas_dalam_memberikan_pelayanan') == $nilai ? 'checked' : '' }} required>
                                {{-- Menampilkan angka dan teks sesuai array di atas --}}
                                <label for="kemampuan_petugas_dalam_memberikan_pelayanan_{{ $nilai }}">{{ $nilai }}.
                                    {{ $keterangan }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('kemampuan_petugas_dalam_memberikan_pelayanan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label>Kesopanan Dan Keramahan Petugas *</label>
                    <div class="flex flex-col sm:flex-row gap-4">
                        @foreach ($opsiUTujuh as $nilai => $keterangan)
                            <div class="radio-item">
                                <input type="radio" id="kesopanan_dan_keramahan_petugas_{{ $nilai }}"
                                    name="kesopanan_dan_keramahan_petugas" value="{{ $nilai }}" {{ old('kesopanan_dan_keramahan_petugas') == $nilai ? 'checked' : '' }} required>
                                {{-- Menampilkan angka dan teks sesuai array di atas --}}
                                <label for="kesopanan_dan_keramahan_petugas_{{ $nilai }}">{{ $nilai }}.
                                    {{ $keterangan }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('kesopanan_dan_keramahan_petugas')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label>Penanganan Pengaduan Saran Dan Masukan *</label>
                    <div class="flex flex-col sm:flex-row gap-4">
                        @foreach ($opsiUDelapan as $nilai => $keterangan)
                            <div class="radio-item">
                                <input type="radio" id="penanganan_pengaduan_saran_dan_masukan_{{ $nilai }}"
                                    name="penanganan_pengaduan_saran_dan_masukan" value="{{ $nilai }}" {{ old('penanganan_pengaduan_saran_dan_masukan') == $nilai ? 'checked' : '' }} required>
                                {{-- Menampilkan angka dan teks sesuai array di atas --}}
                                <label for="penanganan_pengaduan_saran_dan_masukan_{{ $nilai }}">{{ $nilai }}.
                                    {{ $keterangan }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('penanganan_pengaduan_saran_dan_masukan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label>Sarana Dan Prasarana Penunjang Pelayanan *</label>
                    <div class="flex flex-col sm:flex-row gap-4">
                        @foreach ($opsiUSembilan as $nilai => $keterangan)
                            <div class="radio-item">
                                <input type="radio" id="sarana_dan_prasarana_penunjang_pelayanan_{{ $nilai }}"
                                    name="sarana_dan_prasarana_penunjang_pelayanan" value="{{ $nilai }}" value="{{ $nilai }}" {{ old('sarana_dan_prasarana_penunjang_pelayanan') == $nilai ? 'checked' : '' }} required>
                                {{-- Menampilkan angka dan teks sesuai array di atas --}}
                                <label for="sarana_dan_prasarana_penunjang_pelayanan_{{ $nilai }}">{{ $nilai }}.
                                    {{ $keterangan }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('sarana_dan_prasarana_penunjang_pelayanan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label>Apakah ada pungutan yang diminta petugas di luar standar pelayanan? *</label>
                    <div class="radio-grid-2-col">
                        @foreach ($pungutan as $item)
                            <div class="radio-item">
                                {{-- ID: ada_pungutan_1, ada_pungutan_2 --}}
                                <input type="radio" id="ada_pungutan_{{ $item->id }}" name="ada_pungutan"
                                    value="{{ $item->name }}" {{ old('ada_pungutan') == $item->name ? 'checked' : ''}} required>

                                <label for="ada_pungutan_{{ $item->id }}">{{ $item->name }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="jenis_pungutan-input-wrapper" id="jenis_pungutan_wrapper" 
                        style="{{ (old('ada_pungutan') == 'Ada' || old('ada_pungutan') == 'Ya') ? 'display: block;' : 'display: none;' }}">
                        
                        <input type="text" name="jenis_pungutan" id="jenis_pungutan_text" class="form-input mt-4"
                            placeholder="Sebutkan Pungutan Jika Ada..." 
                            value="{{ old('jenis_pungutan') }}">
                    </div>

                    @error('ada_pungutan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    @error('jenis_pungutan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label>Apakah Anda akan menginformasikan layanan yang tersedia di Balai Bahasa Provinsi Jambi kepada
                        teman atau kolega Anda? *</label>
                    <div class="radio-grid-2-col">
                        @foreach ($info as $item)
                            <div class="radio-item">
                                {{-- ID: info_1, info_2 --}}
                                <input type="radio" id="info_{{ $item->id }}" name="akan_informasikan_layanan"
                                    value="{{ $item->name }}"
                                    {{-- LOGIC: Cek apakah opsi ini dipilih sebelumnya --}}
                                    {{ old('akan_informasikan_layanan') == $item->name ? 'checked' : '' }}
                                    required>

                                <label for="info_{{ $item->id }}">{{ $item->name }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('akan_informasikan_layanan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label for="kritik_saran">Kritik dan Saran *</label>
                    <textarea id="kritik_saran" name="kritik_saran" rows="5" class="form-input"
                        required>{{ old('kritik_saran') }}</textarea>
                    @error('kritik_saran')
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
                    <button type="button" id="submitBtn" class="submit-button">Kirim Survei</button>
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

            // 2. Cek jika Pendidikan = "lainnya", tampilkan input teksnya
            if(document.querySelector('input[name="pendidikan"]:checked')?.value === 'lainnya') {
                document.getElementById('pendidikan_lainnya_wrapper').style.display = 'block';
            }

            // 3. Cek jika Profesi = "lainnya", tampilkan input teksnya
            if(document.querySelector('input[name="profesi"]:checked')?.value === 'lainnya') {
                document.getElementById('profesi_lainnya_wrapper').style.display = 'block';
            }
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
                        if (this.value === 'lainnya' || this.value == 'Ya') {
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
            handleLainnya('instansi', 'instansi_lainnya_wrapper', 'instansi_lainnya_text');
            handleLainnya('ada_pungutan', 'jenis_pungutan_wrapper', 'jenis_pungutan_text');



            // --- LOGIKA BARU UNTUK LAYANAN BERTINGKAT ---
            const finalInput = document.getElementById('layanan_final_input');
            const mainRadios = document.querySelectorAll('input[name="layanan_utama"]');
            const subContainers = document.querySelectorAll('.sub-layanan');
            const subRadios = document.querySelectorAll('.js-sub-radio');
            const lainnyaRadios = document.querySelectorAll('.js-sub-lainnya');

            // 1. HANDLER PILIHAN UTAMA
            mainRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    // A. Reset Tampilan (Sembunyikan semua sub & input text)
                    subContainers.forEach(el => el.style.display = 'none');
                    document.querySelectorAll('.lainnya-input-wrapper').forEach(el => el.style.display = 'none');

                    // B. Reset Nilai (Uncheck sub & kosongkan text)
                    document.querySelectorAll('input[name="sub_pilihan"]').forEach(el => el.checked = false);
                    document.querySelectorAll('[id^="text_lainnya_"]').forEach(el => el.value = '');

                    const hasSub = this.getAttribute('data-has-sub') === 'true';
                    const id = this.getAttribute('data-id');

                    if (hasSub) {
                        // Jika punya sub, tampilkan container yang sesuai ID
                        const container = document.getElementById(`sub_container_${id}`);
                        if (container) container.style.display = 'block';

                        // Kosongkan final input karena user WAJIB memilih sub-nya lagi
                        finalInput.value = '';
                    } else {
                        // Jika tidak punya sub (misal: UKBI), langsung isi nilainya
                        finalInput.value = this.value;
                    }
                });
            });

            // 2. HANDLER SUB PILIHAN BIASA (Dari Database)
            subRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    // Jika user pindah dari "Lainnya" ke opsi biasa, sembunyikan input text
                    const containerId = this.closest('.sub-layanan').id.split('_')[2];
                    const textWrapper = document.getElementById(`input_lainnya_wrapper_${containerId}`);
                    if (textWrapper) textWrapper.style.display = 'none';

                    // Isi nilai final sesuai sub yang dipilih
                    finalInput.value = this.value;
                });
            });

            // 3. HANDLER PILIHAN "LAINNYA" (Radio Button)
            lainnyaRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    const containerId = this.closest('.sub-layanan').id.split('_')[2];
                    const textWrapper = document.getElementById(`input_lainnya_wrapper_${containerId}`);
                    const textInput = document.getElementById(`text_lainnya_${containerId}`);

                    if (textWrapper) {
                        textWrapper.style.display = 'block';
                        textInput.focus();
                        finalInput.value = 'Lainnya: '; // Set default awal
                    }
                });
            });

            // 4. HANDLER KETIK TEXT (Input Text Lainnya)
            document.querySelectorAll('[id^="text_lainnya_"]').forEach(input => {
                input.addEventListener('input', function () {
                    const wrapper = this.closest('.sub-layanan');
                    const radioLainnya = wrapper.querySelector('.js-sub-lainnya');

                    // Pastikan radio "Lainnya" sedang dicentang
                    if (radioLainnya && radioLainnya.checked) {
                        finalInput.value = 'Lainnya: ' + this.value;
                    }
                });
            });

        });
    </script>
@endsection