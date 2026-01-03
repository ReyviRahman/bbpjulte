@extends('layouts.public')

@section('title', 'Formulir Pengaduan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/form-style.css') }}">
@endpush

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="container">
        <div class="form-container">
            <div class="card">
                <div class="form-header">
                    <h2>Formulir Pengaduan</h2>
                    <p>Silakan sampaikan pengaduan Anda melalui formulir di bawah ini.</p>
                </div>

                @if (session('success'))
                    {{-- Notifikasi sukses akan ditangani oleh SweetAlert jika diperlukan,
                    atau bisa menggunakan satu format notifikasi yang konsisten.
                    Saya akan menggunakan yang ini untuk sementara. --}}
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative"
                        role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                {{-- Menambahkan ID pada form --}}
                <form id="pengaduanForm" action="{{ route('pengaduan.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nama_lengkap">Nama Lengkap *</label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-input"
                                value="{{ old('nama_lengkap') }}" required>
                            @error('nama_lengkap')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="nomor_ponsel">Nomor Ponsel/WA *</label>
                            <input type="text" id="nomor_ponsel" name="nomor_ponsel" class="form-input"
                                value="{{ old('nomor_ponsel') }}" required>
                            @error('nomor_ponsel')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Pos-el (E-mail) *</label>
                            <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}"
                                required>
                            @error('email')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="profesi">Pekerjaan/Profesi *</label>
                            <input type="text" id="profesi" name="profesi" class="form-input" value="{{ old('profesi') }}"
                                required>
                            @error('profesi')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="instansi">Instansi *</label>
                            <input type="text" id="instansi" name="instansi" class="form-input"
                                value="{{ old('instansi') }}" required>
                            @error('instansi')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="isi_aduan">Isi Aduan *</label>
                            <textarea id="isi_aduan" name="isi_aduan" rows="5" class="form-input"
                                required>{{ old('isi_aduan') }}</textarea>
                            @error('isi_aduan')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="bukti_aduan">Bukti Aduan (Opsional, maks 2MB: jpg, png, pdf)</label>
                            <input type="file" id="bukti_aduan" name="bukti_aduan" class="form-input">
                            @error('bukti_aduan')
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
                            {{-- Mengubah type menjadi "button" dan menambahkan ID --}}
                            <button type="button" id="submitBtn" class="submit-button">Kirim Pengaduan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
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
            
            const submitBtn = document.getElementById('submitBtn');
            const pengaduanForm = document.getElementById('pengaduanForm');

            if (submitBtn && pengaduanForm) {
                submitBtn.addEventListener('click', function (e) {
                    e.preventDefault(); // Mencegah form terkirim langsung

                    // Validasi manual sederhana sebelum menampilkan konfirmasi
                    let isValid = pengaduanForm.checkValidity();
                    if (isValid) {
                        Swal.fire({
                            title: 'Konfirmasi Pengiriman',
                            text: "Apakah Anda yakin data yang diisi sudah benar?",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3b82f6', // Biru
                            cancelButtonColor: '#6c757d',  // Abu-abu
                            confirmButtonText: 'Ya, Kirim!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Jika dikonfirmasi, kirim form
                                pengaduanForm.submit();
                            }
                        });
                    } else {
                        // Jika form tidak valid, trigger validasi browser bawaan
                        pengaduanForm.reportValidity();
                    }
                });
            }
        });
    </script>
@endsection