@extends('layouts.public')

@section('title', 'Lacak Status Layanan')

@push('styles')
    <style>
        .tracking-container {
            max-width: 600px;
            margin: 2em auto;
            padding: 2.5em;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .tracking-header {
            text-align: center;
            margin-bottom: 1.5em;
        }

        .tracking-header h1 {
            color: var(--primary-color);
        }

        .form-input {
            width: 100%;
            padding: 0.75em;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            text-align: center;
            letter-spacing: 2px;
        }

        .submit-button {
            margin-top: 1rem;
            padding: 0.8em 1.5em;
            font-size: 1rem;
            font-weight: bold;
            color: white;
            background-color: var(--primary-color);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s;
        }

        .submit-button:hover {
            background-color: #00458e;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.9em;
            margin-top: 0.4em;
            text-align: center;
            display: block;
        }
    </style>
@endpush

@section('content')
    <div class="tracking-container">
        <div class="tracking-header">
            <h1>Lacak Status Permohonan Layanan</h1>
            <p>Silakan masukkan Nomor Registrasi yang Anda dapatkan untuk melihat progres layanan Anda.</p>
        </div>

        <form action="{{ route('status.search') }}" method="POST">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label for="no_registrasi" class="sr-only">Nomor Registrasi</label>
                <input type="text" id="no_registrasi" name="no_registrasi" class="form-input"
                    value="{{ old('no_registrasi') }}" placeholder="Contoh: ULT-20250608143015" required>
                @error('no_registrasi')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group full-width no-top-border">
                <label for="captcha">Kode Keamanan *</label>

                <div class="flex flex-col gap-3 mt-2">

                    {{-- Container Baris 1: Gambar & Tombol --}}
                    <div class="flex items-stretch gap-3"> {{-- items-stretch biar tinggi tombol & gambar sama --}}

                        {{-- Gambar CAPTCHA (Dibuat Full Width dengan flex-1) --}}
                        <div
                            class="captcha-img-wrapper flex-1 w-full border rounded-md overflow-hidden shadow-sm flex items-center justify-center bg-gray-50">
                            {{-- Menggunakan style inline/class css tambahan untuk memaksa gambar jadi 100% jika perlu --}}
                            <span id="captcha-img"
                                class="flex w-full h-full [&>img]:w-full [&>img]:h-full [&>img]:object-cover">
                                {!! captcha_img('flat') !!}
                            </span>
                        </div>

                        {{-- Tombol Refresh --}}
                        <button type="button" id="reload-captcha"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 flex items-center justify-center"
                            title="Ganti Kode">
                            &#x21bb;
                        </button>
                    </div>

                    {{-- Container Baris 2: Input Text --}}
                    <div class="w-full">
                        <input type="text" id="captcha" name="captcha" class="form-input w-full"
                            placeholder="Ketik karakter yang muncul di gambar" required>
                    </div>
                </div>

                @error('captcha')
                    <div class="error-message text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="submit-button">Lacak Sekarang</button>
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



        });
    </script>
@endsection