@extends('layouts.public')

{{-- Judul Tab Browser dinamis --}}
@section('title', (isset($permohonan) && $permohonan) ? 'Permohonan Berhasil Terkirim' : 'Terima Kasih')

@push('styles')
    <style>
        /* Style tetap sama seperti sebelumnya */
        .success-container {
            max-width: 700px;
            margin: 2em auto;
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
            background-color: #28a745;
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
            background-color: #28a745;
            border-color: #28a745;
            width: 100%;
        }

        .btn-toggle:hover {
            background-color: #218838;
            border-color: #1e7e34;
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

        .download-button {
            margin-top: 1.5rem;
            background-color: #dc2626;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.2s;
        }

        .download-button:hover {
            background-color: #b91c1c;
        }

        @media (max-width: 768px) {
            .data-recap dl {
                grid-template-columns: 1fr;
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
        
        {{-- BAGIAN HEADER --}}
        <div class="confirmation-header" style="text-align: center">
            <svg class="success-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle cx="26" cy="26" r="25" fill="#FFF" opacity="0.2" />
                <path fill="#FFF" d="M14.1 27.2l7.1 7.2 16.7-16.8L36 16 21.2 30.8l-5.2-5.2z" />
            </svg>
            <h2>
                @if(isset($permohonan) && $permohonan)
                    Permohonan Berhasil Terkirim
                @else
                    Terima Kasih!
                @endif
            </h2>
        </div>

        {{-- BAGIAN PESAN UTAMA --}}
        <div class="message-body">
            
            {{-- GREETING --}}
            <p class="greeting">
                Yth. Bapak/Ibu/Saudara 
                <strong>
                    @if(isset($permohonan) && $permohonan)
                        {{ $permohonan->nama_lengkap }}
                    @endif
                </strong>,
            </p>

            {{-- PESAN TEKS --}}
            @if(isset($permohonan) && $permohonan)
                {{-- KONTEN LENGKAP JIKA DATA ADA --}}
                <p>
                    Terima kasih telah mengajukan permohonan layanan di Balai Bahasa Provinsi Jambi. Permohonan Anda telah kami
                    terima dan akan segera diproses pada hari dan jam kerja.
                </p>

                {{-- KOTAK INFO REGISTRASI (Hanya muncul jika ada permohonan) --}}
                <div style="margin-top: 2em; padding: 1em; background-color: #eef2ff; border-left: 4px solid #4f46e5; color: #4338ca;">
                    <h4 style="margin-top: 0; font-weight: bold;">Simpan Nomor Registrasi Anda!</h4>
                    <p style="margin-bottom: 0.5em;">Gunakan nomor berikut untuk melacak status permohonan Anda di kemudian hari:</p>
                    
                    <p style="font-size: 1.2em; font-weight: bold; letter-spacing: 1px;">{{ $permohonan->no_registrasi }}</p>
                    
                    <a href="{{ route('status.index') }}" style="font-weight: bold; color: #4338ca; margin-top: 0.5em; display: inline-block;">
                        Lacak Permohonan Anda Sekarang →
                    </a>
                    <br />
                    
                    {{-- TOMBOL DOWNLOAD PDF --}}
                    <a href="{{ route('permohonan.downloadPDF', $permohonan) }}" class="download-button">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Unduh PDF</span>
                    </a>
                </div>

            @else
                {{-- KONTEN STANDAR JIKA TIDAK ADA DATA (Cuma isi SKM) --}}
                <p>
                    Terima kasih telah meluangkan waktu untuk mengisi Survei Kepuasan Masyarakat. Masukan Anda sangat berharga bagi kami untuk terus meningkatkan kualitas pelayanan di Balai Bahasa Provinsi Jambi.
                </p>
                <p>
                    Semoga hari Anda menyenangkan!
                </p>
            @endif

            <br />
            
            {{-- TOMBOL BERANDA --}}
            <div style="text-align: center;">
                <a href="/" class="btn btn-survey">
                    Kembali ke Beranda
                </a>
            </div>

            <div class="closing">
                <p>Hormat kami,<br><strong>Tim Layanan Balai Bahasa Provinsi Jambi</strong></p>
            </div>
        </div>

        {{-- BAGIAN DETAIL DATA (Hanya muncul jika ada permohonan) --}}
        @if(isset($permohonan) && $permohonan)
        <div class="data-recap-wrapper">
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
        @endif
        
    </div>
@endsection

@push('scripts')
    {{-- Script hanya dirender jika tombol detail ada --}}
    @if(isset($permohonan) && $permohonan)
    <script>
        const toggleBtn = document.getElementById('toggleDataBtn');
        const dataRecapDiv = document.getElementById('dataRecap');

        if(toggleBtn && dataRecapDiv) {
            toggleBtn.addEventListener('click', function () {
                if (dataRecapDiv.style.display === 'none' || dataRecapDiv.style.display === '') {
                    dataRecapDiv.style.display = 'block';
                    this.textContent = 'Sembunyikan Detail Permohonan';
                } else {
                    dataRecapDiv.style.display = 'none';
                    this.textContent = 'Lihat Detail Permohonan Anda';
                }
            });
        }
    </script>
    @endif
@endpush