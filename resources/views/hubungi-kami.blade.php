{{-- Menggunakan layout utama dari layouts/public.blade.php --}}
@extends('layouts.public')

{{-- Mengatur judul halaman --}}
@section('title', 'Hubungi Kami')

{{-- Konten utama halaman --}}
@section('content')
    <style>
        /* Mengatur font dasar dan warna teks agar bersih dan modern */
        .contact-container {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
            color: #212529;
            /* Warna teks hitam/abu-abu tua */
            background-color: #ffffff;
            padding: 2rem;
            font-size: 16px;
            line-height: 1.6;
            max-width: 1200px;
            margin: 2rem auto;
        }

        /* Kontainer utama untuk semua informasi kontak */
        .contact-card {
            max-width: 700px;
            /* Batasi lebar agar tidak terlalu panjang di layar besar */
        }

        /* Judul utama: "KANTOR BAHASA PROVINSI JAMBI" */
        .contact-card h2 {
            font-size: 1.2em;
            font-weight: 700;
            text-transform: uppercase;
            margin: 0;
        }

        /* Alamat */
        .contact-card .address {
            margin: 0.5em 0 1.5em 0;
            /* Memberi jarak ke bawah */
        }

        /* Mengatur daftar deskripsi (dl) untuk item berlabel */
        .contact-card dl {
            margin: 0;
        }

        .contact-card dl>div {
            display: flex;
            /* Membuat label dan nilainya sejajar */
            margin-bottom: 0.75em;
            align-items: baseline;
        }

        /* Label (dt): "Telepon:", "WA:", "Posel:" */
        .contact-card dt {
            width: 90px;
            /* Atur lebar tetap agar semua nilai (dd) rata kiri */
            flex-shrink: 0;
            /* Mencegah label menyusut */
            font-weight: 500;
        }

        /* Nilai (dd): Nomor telepon, tautan WA, dan email */
        .contact-card dd {
            margin-left: 0;
            /* Hapus margin default */
        }

        /* Menebalkan teks (untuk nomor telepon) */
        .contact-card strong {
            font-weight: 700;
        }

        /* Mengatur tautan (a) agar berwarna biru dan tanpa garis bawah */
        .contact-card a {
            color: #0056b3;
            text-decoration: none;
        }

        /* Menambahkan garis bawah saat kursor di atas tautan */
        .contact-card a:hover {
            text-decoration: underline;
        }

        /* Tagline di bagian bawah */
        .contact-card .tagline {
            margin-top: 2em;
            /* Memberi jarak dari item terakhir */
            font-style: italic;
        }

        /* BARU: CSS untuk kontainer peta */
        .map-container {
            margin-top: 3rem;
            /* Memberi jarak antara info kontak dan peta */
            border-radius: 8px;
            overflow: hidden;
            /* Agar iframe mengikuti border-radius container */
            border: 1px solid #ddd;
        }

        .map-container iframe {
            width: 100%;
            height: 450px;
            border: 0;
        }
    </style>

    <div class="contact-container">
        <div class="contact-card">
            <h2>Kantor Bahasa Provinsi Jambi</h2>
            <p class="address">
                Jalan Arif Rahman Hakim No.101, Simpang IV Sipin, Kec. Telanaipura, Kota Jambi, Jambi 36124
            </p>

            <dl>
                <div>
                    <dt>Telepon:</dt>
                    <dd><strong><a href="tel:0741669466">(0741) 669466</a></strong></dd>
                </div>
                <div>
                    <dt>WA:</dt>
                    <dd><a href="https://wa.me/6281265000071" target="_blank" rel="noopener noreferrer">081265000071</a></dd>
                </div>
                <div>
                    <dt>Posel:</dt>
                    <dd>
                        <a href="mailto:bahasajambi@kemdikbud.go.id">bahasajambi@kemdikbud.go.id</a><br>
                        <a href="mailto:info.balaibahasajambi@gmail.com">info.balaibahasajambi@gmail.com</a>
                    </dd>
                </div>
            </dl>

            <p class="tagline">
                Kami siap melayani Anda dalam bidang kebahasaan dan kesastraan.
            </p>
        </div>

        {{-- BARU: Bagian peta yang disematkan --}}
        <div class="map-container">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.2292738392985!2d103.57259137397625!3d-1.6171257360751152!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e2588f5c0b419f9%3A0xe4a980faaa00231!2sBalai%20Bahasa%20Provinsi%20Jambi.!5e0!3m2!1sen!2sid!4v1753857921656!5m2!1sen!2sid"
                width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
@endsection
