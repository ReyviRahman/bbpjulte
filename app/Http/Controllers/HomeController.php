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
        // 1. LOGIKA PENENTUAN TRIWULAN
       
        $bulanSekarang = date('n'); 
        $tahunSekarang = date('Y'); 

        if ($bulanSekarang >= 1 && $bulanSekarang <= 3) {
            $triwulanNama = 'IV';
            $triwulanAngka = 4;
            $tahun = $tahunSekarang - 1; 
        } elseif ($bulanSekarang >= 4 && $bulanSekarang <= 6) {
            $triwulanNama = 'I';
            $triwulanAngka = 1;
            $tahun = $tahunSekarang;
        } elseif ($bulanSekarang >= 7 && $bulanSekarang <= 9) {
            $triwulanNama = 'II';
            $triwulanAngka = 2;
            $tahun = $tahunSekarang;
        } else {
            $triwulanNama = 'III';
            $triwulanAngka = 3;
            $tahun = $tahunSekarang;
        }

        // 2. TENTUKAN TANGGAL AWAL DAN AKHIR TRIWULAN
        $startBulan = ($triwulanAngka - 1) * 3 + 1;
        $startTriwulan = Carbon::create($tahun, $startBulan, 1)->startOfDay();
        $endTriwulan   = $startTriwulan->copy()->addMonths(2)->endOfMonth()->endOfDay();

        // 3. QUERY DATA SKM & PERHITUNGAN IKM (Logika Baru NRR Tertimbang)
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
        $jumlahNRRTertimbang = 0; // Variabel penampung NRR Tertimbang

        foreach ($indikator as $field => $judul) {
            $query = Skm::select($field, DB::raw('count(*) as total'))
                ->whereNotNull($field)
                ->where('status', '!=', 'Privat')
                ->whereBetween('created_at', [$startTriwulan, $endTriwulan])
                ->groupBy($field)
                ->pluck('total', $field)
                ->toArray();

            $counts = [];
            $totalResponden = 0;
            $snilai = 0; // Menggantikan $totalSkor

            for ($i = 1; $i <= 4; $i++) {
                $jumlah = $query[$i] ?? 0;
                $counts[$i] = $jumlah;
                $totalResponden += $jumlah;
                $snilai += ($jumlah * $i); // Sama dengan skmData->sum($kolom)
            }

            // MENGHITUNG NRR & NRR TERTIMBANG PER UNSUR
            $nrr = $totalResponden > 0 ? ($snilai / $totalResponden) : 0;
            $nrrTertimbang = $nrr * 0.111;
            $jumlahNRRTertimbang += $nrrTertimbang;

            $totalTriwulan = Skm::whereNotNull($field)
                ->where('status', '!=', 'Privat')
                ->whereBetween('created_at', [$startTriwulan, $endTriwulan])
                ->count();

            $laporan[] = (object) [
                'field'          => $field,
                'judul'          => $judul,
                'counts'         => $counts,
                'total'          => $totalResponden,
                'total_triwulan' => $totalTriwulan,
                'rata_rata'      => round($nrr, 2) // Tetap menampilkan NRR di tabel laporan jika dibutuhkan
            ];
        }

        // --- KALKULASI SKOR AKHIR IKM ---
        $ikmScore = round($jumlahNRRTertimbang * 25, 2); // Nilai IKM (dibulatkan 2 desimal agar rapi di UI)

        // Menentukan Kategori (A/B/C/D)
        $ikmKategori = '-';
        if ($ikmScore > 0) {
            $ikmKategori = $ikmScore >= 88.31 ? 'A' : ($ikmScore >= 76.61 ? 'B' : ($ikmScore >= 65.00 ? 'C' : 'D'));
        }

        // Menentukan Mutu Pelayanan
        $ikmMutu = 'Belum Ada Data';
        if ($ikmScore > 0) {
            $ikmMutu = $ikmScore >= 88.31 ? 'Sangat Baik' : ($ikmScore >= 76.61 ? 'Baik' : ($ikmScore >= 65.00 ? 'Kurang Baik' : 'Tidak Baik'));
        }

        // --- QUERY LAYANAN TERBANYAK ---
        $layananTerbanyak = Skm::select('layanan_didapat', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$startTriwulan, $endTriwulan])
            ->whereNotNull('layanan_didapat')
            ->groupBy('layanan_didapat')
            ->orderByDesc('total')
            ->first();

        $namaLayananPopuler = $layananTerbanyak ? $layananTerbanyak->layanan_didapat : '-';

        // 4. QUERY DATA PERMOHONAN 
        $total = Permohonan::whereBetween('created_at', [$startTriwulan, $endTriwulan])->count();

        $counts = Permohonan::selectRaw('status, count(*) as total')
            ->whereBetween('created_at', [$startTriwulan, $endTriwulan]) 
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $kelompokStatus = [
            [
                'label'    => 'Layanan Diproses',
                'included' => ['Diajukan', 'Diproses'],
                'color'    => '' // Saya kembalikan warnanya agar tidak error di Blade
            ],
            [
                'label'    => 'Layanan Selesai',
                'included' => ['Selesai', 'Ditolak'],
                'color'    => ''
            ],
        ];

        $statistik = collect($kelompokStatus)->map(function ($group) use ($total, $counts) {
            $jumlah = 0;
            foreach ($group['included'] as $status) {
                $jumlah += $counts[$status] ?? 0;
            }
            $persen = $total > 0 ? number_format(($jumlah / $total) * 100, 1) : 0;
            return [
                'label'  => $group['label'],
                'jumlah' => $jumlah,
                'persen' => $persen,
                'color'  => $group['color']
            ];
        });

        $tahunSekarangTren = date('Y');
        $ikmYears = [];
        for ($y = $tahunSekarangTren - 4; $y <= $tahunSekarangTren; $y++) {
            $ikmYears[] = $y;
        }

        $ikmSeries = [
            'TW1' => [],
            'TW2' => [],
            'TW3' => [],
            'TW4' => []
        ];

        $metaSeries = [
            'TW1' => [],
            'TW2' => [],
            'TW3' => [],
            'TW4' => []
        ];

        foreach ($ikmYears as $thn) {
            // Loop 4 Triwulan dalam satu tahun
            for ($tw = 1; $tw <= 4; $tw++) {
                $startBlnTw = ($tw - 1) * 3 + 1;
                $startDateTw = Carbon::create($thn, $startBlnTw, 1)->startOfDay();
                $endDateTw   = $startDateTw->copy()->addMonths(2)->endOfMonth()->endOfDay();

                // 6a. Hitung IKM untuk Triwulan & Tahun ini
                $jumlahNRRTertimbangTw = 0;
                $adaDataSkmTw = false;

                foreach ($indikator as $field => $judul) {
                    $queryTw = Skm::select($field, DB::raw('count(*) as total'))
                        ->whereNotNull($field)
                        ->where('status', '!=', 'Privat')
                        ->whereBetween('created_at', [$startDateTw, $endDateTw])
                        ->groupBy($field)
                        ->pluck('total', $field)
                        ->toArray();

                    $totalRespondenTw = 0;
                    $snilaiTw = 0;

                    for ($i = 1; $i <= 4; $i++) {
                        $jumlah = $queryTw[$i] ?? 0;
                        $totalRespondenTw += $jumlah;
                        $snilaiTw += ($jumlah * $i);
                    }

                    if ($totalRespondenTw > 0) {
                        $adaDataSkmTw = true;
                        $nrrTw = $snilaiTw / $totalRespondenTw;
                        $jumlahNRRTertimbangTw += ($nrrTw * 0.111);
                    }
                }

                $ikmScoreTw = $adaDataSkmTw ? round($jumlahNRRTertimbangTw * 25, 2) : 0;
                // Simpan ke ikmSeries (Format: null jika 0 agar tidak merusak chart bar)
                $ikmSeries['TW' . $tw][] = $ikmScoreTw > 0 ? $ikmScoreTw : null;

                // 6b. Cari Layanan Terbanyak untuk Triwulan & Tahun ini
                $layananTerbanyakTw = Skm::select('layanan_didapat', DB::raw('count(*) as total'))
                    ->whereBetween('created_at', [$startDateTw, $endDateTw])
                    ->whereNotNull('layanan_didapat')
                    ->groupBy('layanan_didapat')
                    ->orderByDesc('total')
                    ->first();

                $metaSeries['TW' . $tw][] = $layananTerbanyakTw ? $layananTerbanyakTw->layanan_didapat : 'Tidak Ada Data';
            }
        }

        // Warna Custom sesuai request (bisa disesuaikan)
        $warnaTrenIKM = ['#0246a7', '#1e89ef', '#9bc6bf', '#5cdffb'];


        // 5. KIRIM DATA KE VIEW
        return view('beranda', [
            'laporan'             => $laporan,
            'ikmScore'            => $ikmScore,
            'ikmKategori'         => $ikmKategori,
            'ikmMutu'             => $ikmMutu,
            'namaLayananPopuler'  => $namaLayananPopuler,
            'triwulan'            => $triwulanNama, 
            'tahun'               => $tahun,        
            'startTriwulan'       => $startTriwulan,
            'endTriwulan'         => $endTriwulan,
            'statistik'           => $statistik,
            'ikmYears'            => $ikmYears,
            'ikmSeries'           => $ikmSeries,
            'metaSeries'          => $metaSeries,
            'warnaTrenIKM'        => $warnaTrenIKM,
        ]);
    }
}