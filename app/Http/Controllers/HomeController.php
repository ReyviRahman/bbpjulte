<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\Skm;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $triwulan = $now->quarter; // 1–4
        $startTriwulan = $now->copy()->startOfQuarter();
        $endTriwulan   = $now->copy()->endOfQuarter();

        $indikator = [
            'syarat_pengurusan_pelayanan' => 'Syarat Pengurusan Pelayanan',
            'sistem_mekanisme_dan_prosedur_pelayanan' => 'Sistem, Mekanisme, dan Prosedur Pelayanan',
            'waktu_penyelesaian_pelayanan' => 'Waktu Penyelesaian Pelayanan',
            'kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan' => 'Kesesuaian Biaya Pelayanan Dengan yang Diinformasikan',
            'kesesuaian_hasil_pelayanan' => 'Kesesuaian Hasil Pelayanan',
            'kemampuan_petugas_dalam_memberikan_pelayanan' => 'Kemampuan Petugas Dalam Memberikan Pelayanan',
            'kesopanan_dan_keramahan_petugas' => 'Kesopanan dan Keramahan Petugas',
            'penanganan_pengaduan_saran_dan_masukan' => 'Penanganan Pengaduan Saran dan Masukan',
            'sarana_dan_prasarana_penunjang_pelayanan' => 'Sarana dan Prasarana Penunjang Pelayanan',
        ];

        $laporan = [];

        foreach ($indikator as $field => $judul) {

            // ✅ QUERY SUDAH DIBATASI TRI WULAN
            $query = Skm::select($field, DB::raw('count(*) as total'))
                ->whereNotNull($field)
                ->where('status', '!=', 'Privat')
                ->whereBetween('created_at', [$startTriwulan, $endTriwulan])
                ->groupBy($field)
                ->pluck('total', $field)
                ->toArray();

            $counts = [];
            $totalResponden = 0;
            $totalSkor = 0;

            for ($i = 1; $i <= 4; $i++) {
                $jumlah = $query[$i] ?? 0;
                $counts[$i] = $jumlah;
                $totalResponden += $jumlah;
                $totalSkor += ($jumlah * $i);
            }

            $rataRata = $totalResponden > 0
                ? round($totalSkor / $totalResponden, 2)
                : 0;

            // ✅ TOTAL TRIWULAN JUGA KONSISTEN
            $totalTriwulan = Skm::whereNotNull($field)
                ->where('status', '!=', 'Privat')
                ->whereBetween('created_at', [$startTriwulan, $endTriwulan])
                ->count();

            $laporan[] = (object) [
                'field' => $field,
                'judul' => $judul,
                'counts' => $counts,
                'total' => $totalResponden,
                'total_triwulan' => $totalTriwulan,
                'rata_rata' => $rataRata
            ];
        }

        $ikmUnit = count($laporan) > 0
            ? collect($laporan)->avg('rata_rata')
            : 0;

        // 1. Ambil Total Data
        $total = Permohonan::count();

        // 2. Ambil data jumlah per status dari database
        $counts = Permohonan::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // 3. Definisikan Kategori Gabungan
        $kelompokStatus = [
            [
                'label'    => 'Layanan Selesai', // Gabungan Selesai & Ditolak
                'included' => ['Selesai', 'Ditolak'],
                'color'    => 'text-green-600'
            ],
            [
                'label'    => 'Layanan Diproses', // Gabungan Diajukan & Diproses
                'included' => ['Diajukan', 'Diproses'],
                'color'    => 'text-yellow-600'
            ]
        ];

        // 4. Mapping data berdasarkan kategori gabungan di atas
        $statistik = collect($kelompokStatus)->map(function ($group) use ($total, $counts) {

            // Menjumlahkan count dari status-status yang tergabung
            $jumlah = 0;
            foreach ($group['included'] as $status) {
                $jumlah += $counts[$status] ?? 0;
            }

            // Hitung Persen (1 desimal)
            $persen = $total > 0 ? number_format(($jumlah / $total) * 100, 1) : 0;

            return [
                'label'   => $group['label'],
                'jumlah'  => $jumlah,
                'persen'  => $persen,
                'color'   => $group['color']
            ];
        });

        return view('beranda', compact(
            'laporan',
            'ikmUnit',
            'triwulan',
            'startTriwulan',
            'endTriwulan',
            'statistik'
        ));
    }
}
