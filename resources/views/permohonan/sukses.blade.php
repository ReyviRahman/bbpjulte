@extends('layouts.public')

@section('title', 'Permohonan Berhasil Terkirim')

@push('styles')
    <style>
        /* CSS ini HANYA berlaku untuk elemen di dalam halaman ini, tidak lagi mengatur body */
        .success-container {
            max-width: 700px;
            margin: 2em auto;
            /* Beri jarak dari header */
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .confirmation-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background-color: #F59E0B;
            color: white;
            text-align: center;
        }

        .success-icon {
            width: 60px;
            height: 60px;
            margin-bottom: 15px;
        }

        .confirmation-header h2 {
            margin: 0;
            font-size: 1.8em;
            font-weight: 600;
        }

        .message-body {
            padding: 30px 40px;
            line-height: 1.7;
        }

        .message-body p {
            margin: 0 0 1em 0;
            text-align: justify;
        }

        .message-body p.greeting,
        .message-body .closing {
            text-align: left;
        }

        .message-body strong {
            color: #2d3748;
        }

        .message-body .closing {
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            font-weight: 600;
            color: white;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            border: 1px solid transparent;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.375rem;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
        }

        .btn-survey {
            background-color: #007bff;
            border-color: #007bff;
            margin: 10px 0;
        }

        .btn-survey:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }

        .btn-toggle {
            background-color: #F59E0B;
            /* Warna Asli (Orange Terang) */
            border-color: #F59E0B;
            width: 100%;
            color: white;
            /* Pastikan teks putih biar kontras */
            cursor: pointer;
            /* Ubah kursor jadi tangan */
            transition: all 0.3s ease;
            /* Efek transisi halus */
        }

        .btn-toggle:hover {
            background-color: #d97706;
            /* Warna Hover (Orange Lebih Gelap) */
            border-color: #d97706;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* Opsional: Tambah bayangan sedikit */
        }

        .data-recap-wrapper {
            padding: 0 40px 30px 40px;
        }

        .data-recap {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }

        .data-recap h3 {
            text-align: left;
            margin-top: 0;
            margin-bottom: 1.5em;
            color: #343a40;
            font-weight: 600;
        }

        .data-recap dl {
            display: grid;
            grid-template-columns: max-content 1fr;
            gap: 12px 15px;
        }

        .data-recap dt {
            font-weight: 600;
            color: #495057;
        }

        .data-recap dd {
            margin: 0;
            word-break: break-word;
        }

        @media (max-width: 768px) {
            .data-recap dl {
                grid-template-columns: 1fr;
                /* Ubah jadi 1 kolom di layar kecil */
                gap: 5px;
            }

            .data-recap dt {
                padding-bottom: 2px;
                border-bottom: 1px solid var(--border-color);
            }
        }
    </style>
@endpush

@section('content')
    <div class="success-container">
        {{-- Header dengan Ikon Tanda Seru Kuning --}}
        <div class="confirmation-header" style="text-align: center">
            {{-- SVG Exclamation Mark (Warning) --}}
            <svg class="success-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52" width="80" height="80">
                <circle cx="26" cy="26" r="25" fill="#FFEF5F" opacity="0.5" /> {{-- Warna Kuning Transparan --}}
                <path fill="#FFF57E"
                    d="M26,36c1.1,0,2,0.9,2,2s-0.9,2-2,2s-2-0.9-2-2S24.9,36,26,36z M26,12c1.1,0,2,0.9,2,2v16c0,1.1-0.9,2-2,2 s-2-0.9-2-2V14C24,12.9,24.9,12,26,12z" />
            </svg>
            <h2 style="margin-top: 15px;">Selesaikan Permohonan</h2>
        </div>

        <div class="message-body">
            <p class="greeting">
                Yth. Bapak/Ibu <strong>{{ $permohonan->nama_lengkap }}</strong>,
            </p>

            {{-- Paragraf 1 --}}
            <p style="margin-bottom: 1em;">
                Saat ini Anda diarahkan untuk mengisi Survei Kepuasan Masyarakat terlebih dahulu. Proses ini digunakan untuk
                penerbitan nomor registrasi permohonan layanan.
            </p>

            {{-- Paragraf 2 --}}
            <p style="margin-bottom: 2em;">
                Nomor registrasi permohonan layanan sangat penting digunakan untuk melacak status permohonan layanan yang
                Anda ajukan dan menandakan permohonan Anda berhasil terkirim.
            </p>

            {{-- Tombol Survei (Langsung di bawah Paragraf 2) --}}
            <div style="text-align: center; margin-bottom: 3em;">
                {{-- Asumsi variabel di halaman ini adalah $permohonan --}}
                <a href="{{ route('skm.create', ['permohonan_id' => $permohonan->id]) }}" class="btn btn-survey"
                    style="background-color: #F59E0B; border-color: #F59E0B; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                    Isi Survei Kepuasan Masyarakat
                </a>
            </div>

            <div class="closing">
                <p>Hormat kami,<br><strong>Tim Layanan Balai Bahasa Provinsi Jambi</strong></p>
            </div>
        </div>

        {{-- Data Recap (Tetap ada sebagai konfirmasi data yang diinput) --}}
        <div class="data-recap-wrapper" style="margin-top: 40px;">
            <button type="button" class="btn btn-toggle" id="toggleDataBtn">Lihat Detail Permohonan Anda</button>
            <div class="data-recap" id="dataRecap">
                <h3>Detail Data Permohonan</h3>
                <dl>
                    <dt>Nama Lengkap:</dt>
                    <dd>{{ $permohonan->nama_lengkap }}</dd>
                    <dt>Instansi/Lembaga:</dt>
                    <dd>{{ $permohonan->instansi }}</dd>
                    <dt>E-mail:</dt>
                    <dd>{{ $permohonan->email }}</dd>
                    <dt>Nomor Ponsel/WA:</dt>
                    <dd>{{ $permohonan->nomor_ponsel }}</dd>
                    <dt>Jenis Kelamin:</dt>
                    <dd>{{ $permohonan->jenis_kelamin }}</dd>
                    <dt>Pendidikan:</dt>
                    <dd>{{ $permohonan->pendidikan }}</dd>
                    <dt>Profesi:</dt>
                    <dd>{{ $permohonan->profesi }}</dd>
                    <dt>Layanan:</dt>
                    <dd>{{ $permohonan->layanan_dibutuhkan }}</dd>
                    <dt>Isi Permohonan:</dt>
                    <dd>{!! nl2br(e($permohonan->isi_permohonan)) !!}</dd>
                    <dt>Tanggal Pengajuan:</dt>
                    <dd>{{ $permohonan->created_at->format('d F Y, H:i') }} WIB</dd>
                </dl>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const toggleBtn = document.getElementById('toggleDataBtn');
        const dataRecapDiv = document.getElementById('dataRecap');

        toggleBtn.addEventListener('click', function () {
            if (dataRecapDiv.style.display === 'none' || dataRecapDiv.style.display === '') {
                dataRecapDiv.style.display = 'block';
                this.textContent = 'Sembunyikan Detail Permohonan';
            } else {
                dataRecapDiv.style.display = 'none';
                this.textContent = 'Lihat Detail Permohonan Anda';
            }
        });
    </script>
@endpush