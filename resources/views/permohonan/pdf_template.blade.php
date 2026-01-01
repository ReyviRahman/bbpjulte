<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Status Permohonan {{ $permohonan->no_registrasi }}</title>
    <style>
        /* STYLE DARI ANDA (Tidak diubah, hanya tambah vertical-align agar rapi) */
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 5px 0 0 0; }
        
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 5px; vertical-align: top; /* Tambahan agar teks panjang rapi */ }
        .info-table tr td:first-child { font-weight: bold; width: 150px; }
        
        .history-section h2 { font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 15px; }
        .timeline-item { margin-bottom: 15px; padding-left: 15px; border-left: 2px solid #ddd; }
        .timeline-item h4 { margin: 0 0 5px 0; }
        .timeline-item p { margin: 0 0 5px 0; font-style: italic; color: #555; }
        .timeline-item .time { font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Permohonan Layanan</h1>
        <p>Unit Layanan Terpadu Elektronik - Balai Bahasa Provinsi Jambi</p>
    </div>

    <h3>Detail Permohonan</h3>
    <table class="info-table">
        <tr>
            <td>Nomor Registrasi:</td>
            <td>{{ $permohonan->no_registrasi }}</td>
        </tr>
        <tr>
            <td>Status Terakhir:</td>
            <td><strong>{{ $permohonan->status }}</strong></td>
        </tr>
        
        <tr>
            <td>Nama Lengkap:</td>
            <td>{{ $permohonan->nama_lengkap }}</td>
        </tr>
        <tr>
            <td>Instansi/Lembaga:</td>
            <td>{{ $permohonan->instansi }}</td>
        </tr>
        <tr>
            <td>E-mail:</td>
            <td>{{ $permohonan->email }}</td>
        </tr>
        <tr>
            <td>Nomor Ponsel/WA:</td>
            <td>{{ $permohonan->nomor_ponsel }}</td>
        </tr>
        <tr>
            <td>Jenis Kelamin:</td>
            <td>{{ $permohonan->jenis_kelamin }}</td>
        </tr>
        <tr>
            <td>Pendidikan:</td>
            <td>{{ $permohonan->pendidikan }}</td>
        </tr>
        <tr>
            <td>Profesi:</td>
            <td>{{ $permohonan->profesi }}</td>
        </tr>
        <tr>
            <td>Layanan:</td>
            <td>{{ $permohonan->layanan_dibutuhkan }}</td>
        </tr>
        <tr>
            <td>Isi Permohonan:</td>
            <td>{!! nl2br(e($permohonan->isi_permohonan)) !!}</td>
        </tr>
        <tr>
            <td>Tanggal Pengajuan:</td>
            <td>{{ $permohonan->created_at->format('d F Y, H:i') }} WIB</td>
        </tr>
    </table>

    <div class="history-section">
        <h2>Riwayat Status</h2>
        @forelse($permohonan->statusHistories as $history)
            <div class="timeline-item">
                <h4>{{ $history->status }}</h4>
                @if($history->keterangan)
                    <p>"{{ $history->keterangan }}"</p>
                @endif
                <span class="time">{{ $history->created_at->format('d F Y, \p\u\k\u\l H:i') }}
                    @if($history->user) - oleh {{ $history->user->name }} @endif
                </span>
            </div>
        @empty
            <p>Belum ada riwayat status.</p>
        @endforelse
    </div>
</body>
</html>