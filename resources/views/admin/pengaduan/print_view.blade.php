<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pengaduan</title>
    <style>
        /* 1. Reset Dasar & Font */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            /* Ukuran pas untuk A4 Landscape */
            color: #374151;
            /* gray-700 */
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 18px;
            color: #111;
        }

        p.meta {
            text-align: center;
            margin-top: 0;
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 20px;
        }

        /* 2. Styling Tabel */
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            background-color: #fff;
            table-layout: fixed;
            /* Agar lebar kolom konsisten */
        }

        /* Header (Thead) */
        th {
            background-color: #f9fafb;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.05em;
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            border-top: 1px solid #e5e7eb;
        }

        /* Body (Tbody) */
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 11px;
            color: #4b5563;
            word-wrap: break-word;
            /* Mencegah teks keluar tabel */
        }

        /* Alternating Rows */
        tr:nth-child(even) {
            background-color: #fcfcfc;
        }

        /* 3. Helper Text Styles */
        .text-bold {
            font-weight: bold;
            color: #111;
        }

        .text-small {
            font-size: 10px;
            color: #6b7280;
        }

        .text-blue {
            color: #2563eb;
            text-decoration: none;
        }

        /* 4. Status Badge */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 9px;
            font-weight: 600;
            text-align: center;
        }

        /* Warna Status */
        .bg-blue {
            background-color: #dbeafe;
            color: #1e40af;
        }

        /* Diajukan */
        .bg-yellow {
            background-color: #fef9c3;
            color: #854d0e;
        }

        /* Diproses */
        .bg-green {
            background-color: #dcfce7;
            color: #166534;
        }

        /* Selesai */
        .bg-red {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Ditolak */
        .bg-gray {
            background-color: #f3f4f6;
            color: #1f2937;
        }

        /* Default */

        .contact-info {
            margin-bottom: 2px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No.</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 15%;">Nama Pengadu</th>
                <th style="width: 12%;">Instansi</th>
                <th style="width: 16%;">Email & Ponsel</th>
                <th style="width: 25%;">Isi Aduan</th>
                <th style="width: 10%;">Bukti</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $index => $pengaduan)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>

                    <td>{{ $pengaduan->created_at->format('d M Y, H:i') }}</td>

                    <td>
                        <span class="text-bold">{{ $pengaduan->nama_lengkap }}</span>
                    </td>

                    <td>{{ $pengaduan->instansi }}</td>

                    <td>
                        <div class="contact-info">{{ $pengaduan->email }}</div>
                        <div class="text-small">
                            {{ $pengaduan->nomor_ponsel }}
                        </div>
                    </td>

                    <td>
                        {{ \Illuminate\Support\Str::limit($pengaduan->isi_aduan, 150, '...') }}
                    </td>

                    <td style="">
                        @if ($pengaduan->path_bukti_aduan)
                            @php
                                $urlBukti = Str::startsWith($pengaduan->path_bukti_aduan, ['http://', 'https://'])
                                    ? $pengaduan->path_bukti_aduan
                                    : asset('storage/' . $pengaduan->path_bukti_aduan);
                            @endphp
                            <div><a href="{{ $urlBukti }}" target="_blank" class="text-blue">Lihat Bukti</a></div>
                        @else
                            -
                        @endif
                    </td>

                    <td style="">
                        @php
                            $badgeClass = match ($pengaduan->status) {
                                'Diajukan' => 'bg-gray',
                                'Diproses' => 'bg-yellow',
                                'Selesai' => 'bg-green',
                                'Ditolak' => 'bg-red',
                                default => 'bg-gray',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ $pengaduan->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">Data tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(isset($is_print_mode) && $is_print_mode)
        <script>
            window.onload = function () {
                window.print();
            }
        </script>
    @endif

</body>

</html>