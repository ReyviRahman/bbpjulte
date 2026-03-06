<?php

namespace App\Http\Controllers;

use App\Models\FormPermohonan;
use App\Models\Permohonan;
use App\Models\Skm;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        // 1. LOGIKA PENENTUAN TRIWULAN

        // Warna Custom sesuai request (bisa disesuaikan)
        $warnaTrenIKM = ['#0246a7', '#1e89ef', '#9bc6bf', '#5cdffb'];
        $defaultColor = '#5d89b0';
        $warnaRerataUnsurPelayanan = ['#1e89ef'];

        $serviceColors = [
            'UKBI'                                => '#02187b',
            'Perpustakaan'                        => '#0246a7',
            'Kunjungan Edukasi'                   => '#164de5',
            'Fasilitasi Bantuan Teknis'           => '#1e89ef', // Warna Induk Teknis
            'Praktik Kerja Lapangan (Pemagangan)' => '#5cdffb',
            'Sarana dan Prasarana'                => '#77b5ff',
            'Penerjemahan'                        => '#9bc6bf',
        ];
       
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
            'syarat_pengurusan_pelayanan' => 'Kesesuaian Persyaratan',
            'sistem_mekanisme_dan_prosedur_pelayanan' => 'Prosedur Pelayanan',
            'waktu_penyelesaian_pelayanan' => 'Kecepatan Pelayanan',
            'kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan' => 'Kesesuaian/ Kewajaran Biaya',
            'kesesuaian_hasil_pelayanan' => 'Kesesuaian Pelayanan',
            'kemampuan_petugas_dalam_memberikan_pelayanan' => 'Kompetensi Petugas',
            'kesopanan_dan_keramahan_petugas' => 'Perilaku Petugas Pelayanan',
            'penanganan_pengaduan_saran_dan_masukan' => 'Penanganan Pengaduan',
            'sarana_dan_prasarana_penunjang_pelayanan' => 'Kualitas Sarana dan Prasarana',
        ];

        $laporan = [];
        $jumlahNRRTertimbang = 0; 

        // --- VARIABEL BARU UNTUK CHART RERATA UNSUR (U1 - U9) ---
        $nrr = [];
        $namaUnsur = [];
        $uIndex = 1;

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
            $snilai = 0; 

            for ($i = 1; $i <= 4; $i++) {
                $jumlah = $query[$i] ?? 0;
                $counts[$i] = $jumlah;
                $totalResponden += $jumlah;
                $snilai += ($jumlah * $i); 
            }

            // MENGHITUNG NRR & NRR TERTIMBANG PER UNSUR
            $nrrValue = $totalResponden > 0 ? ($snilai / $totalResponden) : 0;
            $nrrTertimbang = $nrrValue * 0.111;
            $jumlahNRRTertimbang += $nrrTertimbang;

            // --- SIMPAN DATA UNTUK CHART RERATA UNSUR ---
            $keyUnsur = 'U' . $uIndex; // Menghasilkan 'U1', 'U2', dst
            $nrr[$keyUnsur] = round($nrrValue, 2);
            $namaUnsur[$keyUnsur] = $judul;
            $uIndex++;
            // --------------------------------------------

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
                'rata_rata'      => round($nrrValue, 2) 
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

        // Ambil semua kategori Layanan beserta sub-nya chart berdasarkan kategori
        $masterCategories = FormPermohonan::where('category', 'Layanan')
            ->with('subs')
            ->get();

        $parentMap = [];
        $colorMap = [];
        // Siapkan wadah untuk hitungan
        $mainCounts = [];     // Total per Induk
        $drillData  = [];     // Detail per Anak
        $validChildrenList = [];

        foreach ($masterCategories as $cat) {
            $indukName = $cat->name;

            // Mapping Induk
            $parentMap[$indukName]  = $indukName;
            $mainCounts[$indukName] = 0;
            $drillData[$indukName]  = [];

            // Warna Induk
            $catColor = $serviceColors[$indukName] ?? $defaultColor;
            $colorMap[$indukName] = $catColor;

            // Loop Anak
            foreach ($cat->subs as $sub) {
                $subName = $sub->name;
                $parentMap[$subName] = $indukName;

                // CATAT BAHWA NAMA INI ADALAH SUB RESMI MILIK INDUK INI
                // Format: ['UKBI']['UKBI'] = true, atau ['UKBI']['Pelajar'] = true
                $validChildrenList[$indukName][$subName] = true;

                // Siapkan slot data
                $drillData[$indukName][$subName] = 0;
                $colorMap[$subName] = $catColor;
            }
        }

        // Query Data
        $query = Permohonan::whereBetween('created_at', [$startTriwulan, $endTriwulan]);

        // 1. Ambil data FormPermohonan kategori 'Layanan' beserta Sub-nya
        $masterData = FormPermohonan::where('category', 'Layanan')
            ->with('subs') // Load relasi subs (anaknya)
            ->get();

        // 2. Ambil Nama Induk (Parent)
        // Hasil: ['Penerjemahan', 'Legalisir', ...]
        $listInduk = $masterData->pluck('name')->toArray();

        // 3. Ambil Nama Sub (Child)
        // pluck('subs') akan menghasilkan collection of collections, jadi perlu di-flatten (diratakan)
        // Hasil: ['Penerjemahan Lisan', 'Penerjemahan Tulis', 'Legalisir Ijazah', ...]
        $listSub = $masterData->pluck('subs')->flatten()->pluck('name')->toArray();

        // 4. Gabungkan Kedua Array
        $semuaLayananValid = array_merge($listInduk, $listSub);

        $defaultInduk = 'Fasilitasi Bantuan Teknis';
        $defaultSub   = 'Lainnya';

        $layananRaw = (clone $query)
            ->select('layanan_dibutuhkan', DB::raw(value: 'count(*) as total'))
            ->groupBy('layanan_dibutuhkan')
            ->get();

        // Loop Data kategori
        foreach ($layananRaw as $row) {
            $rawName = $row->layanan_dibutuhkan;
            $total   = (int) $row->total;

            // Cek apakah layanan ini dikenali (punya bapak/induk)?
            if (isset($parentMap[$rawName])) {
                $indukName = $parentMap[$rawName];

                // 1. Total Induk SELALU dihitung (Gabungan data lama + baru)
                $mainCounts[$indukName] += $total;

                // 2. Logic Drilldown (Strict Sesuai Request Kamu)

                // Cek: Apakah nama layanan ini ($rawName) terdaftar sebagai 
                // anak sah dari induknya ($indukName) di database?
                if (isset($validChildrenList[$indukName][$rawName])) {

                    // JIKA YA (Ada di daftar anak): Masukkan ke grafik drilldown
                    // Ini meng-cover 2 kasus:
                    // a. Anak normal (Beda nama dengan induk) -> Masuk.
                    // b. Anak kembar (Nama "UKBI" dan memang ada sub "UKBI") -> Masuk.
                    $drillData[$indukName][$rawName] = ($drillData[$indukName][$rawName] ?? 0) + $total;
                } else {

                    // JIKA TIDAK (Tidak ada di daftar anak):
                    // Berarti ini data Parent murni / Data lama / Generic.
                    // Action: JANGAN DIMUNCULKAN di drilldown.

                    // (Kosong / Do Nothing)
                }
            }

            // Layanan TIDAK Dikenali sama sekali (Typo/Error)
            else {
                // Masuk ke Fasilitasi Bantuan Teknis > Lainnya
                if (isset($mainCounts[$defaultInduk])) {
                    $mainCounts[$defaultInduk] += $total;
                }
                if (isset($drillData[$defaultInduk])) {
                    $drillData[$defaultInduk][$defaultSub] = ($drillData[$defaultInduk][$defaultSub] ?? 0) + $total;
                }
            }
        }

        // Drilldown layanan teratas
        // Sort dari terbesar ke terkecil
        arsort($mainCounts);

        $finalMainData = [];
        $finalDrilldownSeries = [];

        foreach ($mainCounts as $indukName => $total) {
            if ($total == 0) continue;

            // Cek apakah ada data anak untuk ditampilkan?
            // (Data "UKBI" lama tadi tidak masuk sini, jadi drilldown hanya muncul kalau ada Sub Valid)
            $hasSubData = array_sum($drillData[$indukName]) > 0;

            // Ambil warna parent
            $parentColor = $colorMap[$indukName] ?? '#cccccc';

            $dataPoint = [
                'name'  => $indukName,
                'y'     => $total,
                'color' => $parentColor
            ];

            if ($hasSubData) {
                $drillID = Str::slug($indukName);
                $dataPoint['drilldown'] = $drillID;

                $subDataEntries = [];
                arsort($drillData[$indukName]);

                foreach ($drillData[$indukName] as $subName => $subTotal) {
                    if ($subTotal > 0) {
                        // LOGIC WARNA: Jika sub tidak punya warna khusus, pakai warna Induk
                        $subColor = $colorMap[$subName] ?? $parentColor;

                        $subDataEntries[] = [
                            'name'  => $subName,
                            'y'     => $subTotal,
                            'color' => $subColor
                        ];
                    }
                }

                $finalDrilldownSeries[] = [
                    'id'   => $drillID,
                    'name' => $indukName,
                    'data' => $subDataEntries
                ];
            }

            $finalMainData[] = $dataPoint;
        }

        $mainSeries = [[
            'name' => 'Total Permohonan',
            'colorByPoint' => true,
            'data' => $finalMainData
        ]];

        // ... (Definisi $serviceColors di atas tetap sama) ...
        // Ambil Master Data & Build Color Map ---
        $colorMap = [];
        // Kita butuh array ini untuk filter query Top 5 nanti
        $allValidNames = [];

        foreach ($masterCategories as $cat) {
            $indukName = $cat->name;

            // Ambil warna dari array statis kamu. Jika tidak ada, pakai default abu-abu.
            $baseColor = $serviceColors[$indukName] ?? '#cccccc';

            // DAFTARKAN INDUK
            // PENTING: Gunakan strtoupper() agar cocok dengan logic di View/JS
            $colorMap[strtoupper($indukName)] = $baseColor;
            $allValidNames[] = $indukName;

            // DAFTARKAN ANAK (SUBS)
            foreach ($cat->subs as $sub) {
                $subName = $sub->name;

                // Logic pewarisan warna: Anak pakai warna Induk ($baseColor)
                $colorMap[strtoupper($subName)] = $baseColor;
                $allValidNames[] = $subName;
            }
        }

        // 5. KIRIM DATA KE VIEW
        return view('beranda', [
            'laporan'             => $laporan,
            'ikmScore'            => $ikmScore,
            'jumlahNRRTertimbang' => $jumlahNRRTertimbang,
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

            'serviceColors'    => $serviceColors,
            'subServiceColors' => $colorMap,
            'mainSeries' => $mainSeries,
            'drilldownSeries' => $finalDrilldownSeries,
            'nrr'                       => $nrr,
            'namaUnsur'                 => $namaUnsur,
            'warnaRerataUnsurPelayanan' => $warnaRerataUnsurPelayanan,
        ]);
    }
}