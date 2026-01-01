<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Permohonan</title>
    <style>
        /* 1. Reset Dasar & Font */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px; /* Ukuran pas untuk A4 Landscape */
            color: #374151; /* gray-700 */
            margin: 0;
            padding: 0;
        }

        h2 { text-align: center; margin-bottom: 5px; font-size: 18px; color: #111; }
        p.meta { text-align: center; margin-top: 0; font-size: 10px; color: #6b7280; margin-bottom: 20px;}

        /* 2. Styling Tabel (Meniru Tailwind) */
        table {
            width: 100%;
            border-collapse: collapse; /* Penting untuk border */
            border-spacing: 0;
            background-color: #fff;
        }

        /* Header (Thead) - bg-gray-50 */
        th {
            background-color: #f9fafb; 
            color: #6b7280; /* text-gray-500 */
            font-weight: 600; /* font-medium/semibold */
            text-transform: uppercase;
            font-size: 9px; /* text-xs */
            letter-spacing: 0.05em; /* tracking-wider */
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb; /* divide-gray-200 */
            border-top: 1px solid #e5e7eb;
        }

        /* Body (Tbody) */
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb; /* divide-gray-200 */
            vertical-align: top;
            font-size: 11px; /* text-sm */
            color: #4b5563; /* text-gray-600 */
        }

        /* Alternating Rows (Opsional, biar lebih mudah dibaca) */
        tr:nth-child(even) {
            background-color: #fcfcfc;
        }

        /* 3. Helper Text Styles */
        .text-bold { font-weight: bold; color: #111; }
        .text-small { font-size: 10px; color: #6b7280; }
        .text-blue { color: #2563eb; text-decoration: none; }
        
        /* 4. Status Badge (Meniru tombol status) */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px; /* rounded-full */
            font-size: 9px;
            font-weight: 600;
            text-align: center;
        }

        /* Warna Status (Hardcoded CSS agar terbaca di PDF) */
        .bg-blue { background-color: #dbeafe; color: #1e40af; }    /* Diajukan */
        .bg-yellow { background-color: #fef9c3; color: #854d0e; }  /* Diproses */
        .bg-green { background-color: #dcfce7; color: #166534; }   /* Selesai */
        .bg-red { background-color: #fee2e2; color: #991b1b; }     /* Ditolak */
        .bg-gray { background-color: #f3f4f6; color: #1f2937; }    /* Default */

        /* 5. Utility untuk layout cell */
        .cell-content { display: block; }
        .contact-info { margin-bottom: 2px; }
        
        /* Sembunyikan tombol saat print jika dibuka di browser */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <table>
    <thead>
        <tr>
            <th style="width: 3%;">No.</th>
            <th style="width: 10%;">No. Registrasi</th> <th style="width: 10%;">Tanggal</th>
            <th style="width: 14%;">Nama Pemohon</th>
            <th style="width: 10%;">Instansi</th>
            <th style="width: 13%;">Email & Ponsel</th>
            <th style="width: 10%;">Layanan</th>
            <th style="width: 15%;">Isi Permohonan</th>
            <th style="width: 8%;">Berkas</th>
            <th style="width: 7%;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($data as $index => $permohonan)
        <tr>
            <td style="text-align: center;">{{ $index + 1 }}</td>

            <td style="font-weight: bold;">
                {{ $permohonan->no_registrasi ?? '-' }}
            </td>

            <td>{{ $permohonan->created_at->format('d M Y, H:i') }}</td>

            <td>
                <span class="text-bold">{{ $permohonan->nama_lengkap }}</span>
            </td>

            <td>{{ $permohonan->instansi }}</td>

            <td>
                <div class="contact-info">{{ $permohonan->email }}</div>
                <div class="text-small">
                    {{ $permohonan->nomor_ponsel }}
                </div>
            </td>

            <td>{{ $permohonan->layanan_dibutuhkan }}</td>

            <td>
                {{ \Illuminate\Support\Str::limit($permohonan->isi_permohonan, 100, '...') }}
            </td>

            <td style="">
                @if ($permohonan->path_surat_permohonan)
                    @php
                        $urlSurat = Str::startsWith($permohonan->path_surat_permohonan, ['http://', 'https://'])
                            ? $permohonan->path_surat_permohonan
                            : asset('storage/' . $permohonan->path_surat_permohonan);
                    @endphp
                    <div><a href="{{ $urlSurat }}" target="_blank" class="text-blue">Surat</a></div>
                @endif

                @if ($permohonan->path_berkas_permohonan)
                    @php
                        $urlBerkas = Str::startsWith($permohonan->path_berkas_permohonan, ['http://', 'https://'])
                            ? $permohonan->path_berkas_permohonan
                            : asset('storage/' . $permohonan->path_berkas_permohonan);
                    @endphp
                    <div style="margin-top: 4px;"><a href="{{ $urlBerkas }}" target="_blank" class="text-blue">Berkas</a></div>
                @endif

                @if(!$permohonan->path_surat_permohonan && !$permohonan->path_berkas_permohonan)
                    -
                @endif
            </td>

            <td style="">
                @php
                    $badgeClass = match($permohonan->status) {
                        'Diajukan' => 'bg-blue',
                        'Diproses' => 'bg-yellow',
                        'Selesai'  => 'bg-green',
                        'Ditolak'  => 'bg-red',
                        default    => 'bg-gray',
                    };
                @endphp
                <span class="badge {{ $badgeClass }}">
                    {{ $permohonan->status }}
                </span>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="10" style="text-align: center; padding: 20px;">Data tidak ditemukan.</td>
        </tr>
        @endforelse
    </tbody>
</table>

    @if(isset($is_print_mode) && $is_print_mode)
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
    @endif

</body>
</html>