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