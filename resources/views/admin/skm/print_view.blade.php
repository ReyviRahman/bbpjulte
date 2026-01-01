<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan SKM Lengkap</title>
    <style>
        /* 1. Reset Dasar & Font */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10px;
            color: #374151;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact; /* Wajib agar warna background tercetak */
        }

        /* 2. Setup Kertas Legal Landscape */
        @page {
            size: legal landscape; 
            margin: 5mm; 
        }

        h2 { text-align: center; margin-bottom: 5px; font-size: 16px; color: #111; text-transform: uppercase; }
        p.meta { text-align: center; margin-top: 0; font-size: 10px; color: #6b7280; margin-bottom: 15px;}

        /* 3. Styling Tabel Clean (Tanpa Garis Vertikal) */
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            background-color: #fff;
            table-layout: fixed;
        }

        /* Header (Thead) */
        th {
            background-color: #f9fafb; 
            color: #6b7280;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.05em;
            padding: 4px;
            text-align: left;
            
            /* Border Horizontal Saja */
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            border-left: none;
            border-right: none;

            vertical-align: middle;
            height: 30px;
        }

        /* Body (Tbody) */
        td {
            padding: 5px 3px;
            
            /* Border Horizontal Saja */
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            border-left: none;
            border-right: none;

            vertical-align: top;
            font-size: 9px;
            color: #4b5563;
            word-wrap: break-word;
        }

        tr:nth-child(even) { background-color: #fcfcfc; }

        /* 4. Helper Styles */
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; color: #111; }
        
        /* Badge Status */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 9999px; /* Rounded full */
            font-size: 8px;
            font-weight: 600;
            text-align: center;
        }
        
        /* UPDATE WARNA SESUAI REQUEST */
        .bg-green { background-color: #dcfce7; color: #166534; } /* Hijau (Publik) */
        .bg-red   { background-color: #fee2e2; color: #991b1b; } /* Merah (Privat) */
        .bg-gray  { background-color: #f3f4f6; color: #1f2937; } /* Default */

        /* --- 5. TEKNIK HEADER VERTIKAL KHUSUS DOMPDF --- */
        th.col-nilai {
            height: 200px; 
            vertical-align: bottom;
            padding: 0;
            position: relative;
            width: 35px; 
        }

        .vertical-text {
            display: block;
            transform: rotate(-90deg);
            transform-origin: left bottom; 
            width: 150px; 
            text-align: left; 
            white-space: nowrap;
            font-size: 9px;
            color: #6b7280; 
            position: absolute;
            bottom: 4px;
            left: 50%;
            margin-left: 6px; 
        }

        /* 6. Lebar Kolom */
        .col-no { width: 2.5%; }
        .col-tgl { width: 5%; }
        .col-nama { width: 8%; }
        .col-pemohon { width: 8%; }
        .col-instansi { width: 6%; }
        .col-kontak { width: 8%; }
        .col-layanan { width: 7%; }
        
        .col-pungutan { width: 4%; }
        .col-jns { width: 5%; }
        .col-info { width: 4%; }
        .col-kritik { width: 9%; }
        .col-status { width: 5%; }

        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-tgl">Tanggal</th>
                <th class="col-nama">Nama Petugas</th>
                <th class="col-pemohon">Nama Pemohon</th>
                <th class="col-instansi">Instansi</th>
                <th class="col-kontak">Kontak</th>
                <th class="col-layanan">Layanan Didapat</th>
                
                <th class="col-nilai"><div class="vertical-text">Kesesuaian Persyaratan</div></th>
                <th class="col-nilai"><div class="vertical-text">Prosedur Pelayanan</div></th>
                <th class="col-nilai"><div class="vertical-text">Kecepatan Pelayanan</div></th>
                <th class="col-nilai"><div class="vertical-text">Kesesuaian/ Kewajaran Biaya</div></th>
                <th class="col-nilai"><div class="vertical-text">Kesesuaian Pelayanan</div></th>
                <th class="col-nilai"><div class="vertical-text">Kompetensi Petugas</div></th>
                <th class="col-nilai"><div class="vertical-text">Perilaku Petugas Pelayanan</div></th>
                <th class="col-nilai"><div class="vertical-text">Penanganan Pengaduan</div></th>
                <th class="col-nilai"><div class="vertical-text">Kualitas Sarana dan Prasarana</div></th>

                <th class="col-pungutan">Ada Pungutan</th>
                <th class="col-jns">Jenis Pungutan</th>
                <th class="col-nilai"><div class="vertical-text">Akan Informasikan Layanan</div></th>
                <th class="col-kritik">Kritik Saran</th>
                <th class="col-status">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $index => $item)
            <tr>
                <td class="">{{ $index + 1 }}</td>
                
                <td class="">
                    {{ $item->created_at->format('d M Y, H:i') }}
                </td>

                <td>{{ $item->nama_petugas }}</td>

                <td><span class="text-bold">{{ $item->nama_pemohon }}</span></td>

                <td>{{ $item->instansi }}</td>

                <td style="font-size: 8px; word-break: break-all;">
                    {{ $item->email }}
                </td>

                <td>{{ $item->layanan_didapat }}</td>

                <td class="text-center">{{ $item->syarat_pengurusan_pelayanan }}</td>
                <td class="text-center">{{ $item->sistem_mekanisme_dan_prosedur_pelayanan }}</td>
                <td class="text-center">{{ $item->waktu_penyelesaian_pelayanan }}</td>
                <td class="text-center">{{ $item->kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan }}</td>
                <td class="text-center">{{ $item->kesesuaian_hasil_pelayanan }}</td>
                <td class="text-center">{{ $item->kemampuan_petugas_dalam_memberikan_pelayanan }}</td>
                <td class="text-center">{{ $item->kesopanan_dan_keramahan_petugas }}</td>
                <td class="text-center">{{ $item->penanganan_pengaduan_saran_dan_masukan }}</td>
                <td class="text-center">{{ $item->sarana_dan_prasarana_penunjang_pelayanan }}</td>

                <td class="">{{ $item->ada_pungutan }}</td>
                <td>{{ $item->jenis_pungutan ?? '-' }}</td>
                <td class="">{{ $item->akan_informasikan_layanan }}</td>
                
                <td style="font-size: 8px;">
                    {{ $item->kritik_saran }}
                </td>

                <td class="">
                    @php
                        // UPDATE LOGIKA WARNA STATUS
                        $badgeClass = match($item->status) {
                            'Publik' => 'bg-green', // Hijau
                            'Privat' => 'bg-red',   // Merah
                            default  => 'bg-gray',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">
                        {{ $item->status }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="21" class="text-center" style="padding: 20px;">Data tidak ditemukan.</td>
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