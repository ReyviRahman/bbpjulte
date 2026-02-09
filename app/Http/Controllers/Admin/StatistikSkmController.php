<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormSkm;
use App\Models\Pengaduan;
use App\Models\Permohonan;
use App\Models\Skm;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StatistikSkmController extends Controller
{
    public function index(Request $request)
    {
        $warna2Chart = ['#007BFF', '#79D6F2', '#9bc6bf', '#02187b', '#164de5'];
        $warnaTrenIKM = ['#0246a7', '#1e89ef', '#9bc6bf', '#5cdffb'];
        $warnaRerataUnsurPelayanan = ['#1e89ef'];

        // 1. Warna Layanan Utama (Induk)
        $serviceColors = [
            'UKBI'                                => '#02187b',
            'Perpustakaan'                        => '#0246a7',
            'Kunjungan Edukasi'                   => '#164de5',
            'Fasilitasi Bantuan Teknis'           => '#1e89ef', // Warna Induk Teknis
            'Praktik Kerja Lapangan (Pemagangan)' => '#5cdffb',
            'Sarana dan Prasarana'                => '#77b5ff',
            'Penerjemahan'                        => '#9bc6bf', // Warna Induk Terjemahan
        ];

        // 2. Warna Unsur (Drilldown U1-U9)
        $unsurColors = [
            'U1' => '#0246a7',
            'U2' => '#1e89ef',
            'U3' => '#f14d4c',
            'U4' => '#9bc6bf',
            'U5' => '#5cdffb',
            'U6' => '#1db9ce',
            'U7' => '#731fe0',
            'U8' => '#ffb81d',
            'U9' => '#77b5ff',
        ];

        // 3. Warna Nilai Rata-rata (Skor 1-4)
        $scoreColor = '#1e89ef';

        // ---------------------------------------------------------
        // 4. LOGIKA PEWARNAAN ANAK (SUB-LAYANAN)
        // ---------------------------------------------------------

        // Definisi Warna Pendidikan
        $pendidikanColors = [
            'SD'      => '#0246a7',
            'SMP'     => '#1e89ef',
            'SMA'     => '#f14d4c',
            'D1-D3'   => '#9bc6bf',
            'S1'      => '#5cdffb',
            'S2'      => '#1db9ce',
            'S3'      => '#731fe0',
            'LAINNYA' => '#ffb81d',
        ];

        // Definisi Warna Profesi
        $profesiColors = [
            'PNS'       => '#0246a7',
            'PPPK'      => '#1e89ef',
            'TNI/POLRI' => '#f14d4c',
            'SWASTA'    => '#9bc6bf',
            'MAHASISWA' => '#5cdffb',
            'SISWA'     => '#1db9ce',
            'LAINNYA'   => '#ffb81d',
        ];

        $range = $this->getDateRange($request);
        $startDate = $range['start'];
        $endDate   = $range['end'];

        // Query Data
        $query = Skm::whereBetween('created_at', [$startDate, $endDate])->where('status', '!=', 'Privat');

        // Ambil input triwulan + tahun
        $year    = $request->input('year');

        $totalResponden = $query->count();

        // SIAPKAN WHITELIST (Daftar Layanan Valid)
        // Ambil semua kategori dari Master Data
        $masterSkm = FormSkm::with('subs')->get();

        // Ambil nama Parent
        $validParents = $masterSkm->pluck('name')->toArray();
        
        // Ambil nama Sub (Anak)
        $validSubs = $masterSkm->pluck('subs')->flatten()->pluck('name')->toArray();

        // Gabungkan keduanya
        $allValidServices = array_merge($validParents, $validSubs);


        // QUERY DENGAN FILTER
        $layananTerbanyak = Skm::select('layanan_didapat', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            
            // --- FILTER PENTING DISINI ---
            // Hanya hitung jika nama layanan ada di daftar Master Data
            ->whereIn('layanan_didapat', $allValidServices)

            ->groupBy('layanan_didapat')
            ->orderByDesc('total') // Urutkan dari yang terbanyak
            ->first();


        $skmPublik = Skm::whereBetween('created_at', [$startDate, $endDate])->where('status', 'Publik')->count();
        $skmPrivat = Skm::whereBetween('created_at', [$startDate, $endDate])->where('status', 'Privat')->count();

        // Proporsi Skor Responden per Aspek Layanan
        $fields = [
            'syarat_pengurusan_pelayanan',
            'sistem_mekanisme_dan_prosedur_pelayanan',
            'waktu_penyelesaian_pelayanan',
            'kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan',
            'kesesuaian_hasil_pelayanan',
            'kemampuan_petugas_dalam_memberikan_pelayanan',
            'kesopanan_dan_keramahan_petugas',
            'penanganan_pengaduan_saran_dan_masukan',
            'sarana_dan_prasarana_penunjang_pelayanan'
        ];

        // Rata-rata Skor Kepuasan Pengguna Layanan per Aspek Layanan
        $kategoriLayananPerAspek = [];
        $jumlahDataPerAspek = [];

        foreach ($fields as $field) {
            $kategoriLayananPerAspek[] = ucwords(str_replace('_', ' ', $field));
            $jumlahDataPerAspek[] = (clone $query)->avg($field) ?? 0;
        }

        //  CHART JENIS KELAMIN (ANTI TYPO / SPASI)
        
        //Ambil Data gender
        $masterGender = \App\Models\FormSkm::where('category', 'Jenis Kelamin')
            ->orderBy('id', 'asc')
            ->get();

        $rawGender = (clone $query)
            ->select('jenis_kelamin', DB::raw('count(*) as total'))
            ->whereNotNull('jenis_kelamin')
            ->where('jenis_kelamin', '!=', '')
            ->groupBy('jenis_kelamin')
            ->get();

        $normalizer = function ($str) {
            return strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $str));
        };

        // kelompokkan data gender
        $distribusiGender = $masterGender->map(function ($item) use ($rawGender, $normalizer) {
            // Bersihkan nama Master (misal: "Laki-laki" -> "LAKILAKI")
            $masterNameClean = $normalizer($item->name);
            
            // Filter data transaksi yang hasil bersihnya SAMA
            $totalFound = $rawGender->filter(function ($transaksi) use ($masterNameClean, $normalizer) {
                // Bersihkan nama Transaksi (misal: "Laki - Laki " -> "LAKILAKI")
                return $normalizer($transaksi->jenis_kelamin) === $masterNameClean;
            })->sum('total');

            return (object) [
                'label' => $item->name, // Tetap pakai nama asli yg rapi untuk Label
                'total' => $totalFound
            ];
        });

        // CHART LOYALITAS / REKOMENDASI (DINAMIS DARI FORM SKM)
        $masterRekomen = \App\Models\FormSkm::where('category', 'Informasikan Layanan')
            ->orderBy('id', 'asc') 
            ->get();

        // Ambil data informasikan layanan
        $rawRekomen = (clone $query)
            ->select('akan_informasikan_layanan', DB::raw('count(*) as total'))
            ->whereNotNull('akan_informasikan_layanan')
            ->where('akan_informasikan_layanan', '!=', '')
            ->groupBy('akan_informasikan_layanan')
            ->get();

        $normalizer = function ($str) {
            return strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $str));
        };

        // Mapping Data
        $skmRekomen = $masterRekomen->map(function ($item) use ($rawRekomen, $normalizer) {
            // Bersihkan nama Master
            $masterNameClean = $normalizer($item->name);

            // Filter data transaksi yang hasil bersihnya SAMA
            $totalFound = $rawRekomen->filter(function ($transaksi) use ($masterNameClean, $normalizer) {
                return $normalizer($transaksi->akan_informasikan_layanan) === $masterNameClean;
            })->sum('total');

            return (object) [
                'label' => $item->name, // Label Asli (Ya/Tidak)
                'total' => $totalFound
            ];
        });

        //tabel skm
        $skmData = $query->get();
        $total = $skmData->count();
        $unsur = [
            'U1' => 'syarat_pengurusan_pelayanan',
            'U2' => 'sistem_mekanisme_dan_prosedur_pelayanan',
            'U3' => 'waktu_penyelesaian_pelayanan',
            'U4' => 'kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan',
            'U5' => 'kesesuaian_hasil_pelayanan',
            'U6' => 'kemampuan_petugas_dalam_memberikan_pelayanan',
            'U7' => 'kesopanan_dan_keramahan_petugas',
            'U8' => 'penanganan_pengaduan_saran_dan_masukan',
            'U9' => 'sarana_dan_prasarana_penunjang_pelayanan',
        ];
        $snilai = [];
        $nrr = [];
        $nrrTertimbang = [];
        $kategori = [];
        // jika total = 0, hindari division by zero
        if ($total == 0) {
            foreach ($unsur as $key => $kolom) {
                $snilai[$key] = 0;
                $nrr[$key] = 0;
                $nrrTertimbang[$key] = 0;
                $kategori[$key] = 'D';
            }
            $jumlahNRRTertimbang = 0;
            $ikm = 0;
            $mutu = 'Tidak Baik';
        } else {
            foreach ($unsur as $key => $kolom) {
                // SNilai
                $snilai[$key] = $skmData->sum($kolom);
                // NRR (aman karena $total > 0)
                $nrr[$key] = $snilai[$key] / $total;
                // NRR tertimbang
                $nrrTertimbang[$key] = $nrr[$key] * 0.111;
                // Kategori
                $kategori[$key] =
                    $nrr[$key] >= 3.53 ? 'A' : ($nrr[$key] >= 3.06 ? 'B' : ($nrr[$key] >= 2.60 ? 'C' : 'D'));
            }
            // jumlah semua NRR tertimbang
            $jumlahNRRTertimbang = array_sum($nrrTertimbang);
            // nilai IKM
            $ikm = round($jumlahNRRTertimbang * 25, 3);
            // mutu pelayanan
            $mutu =
                $ikm >= 88.31 ? 'Sangat Baik' : ($ikm >= 76.61 ? 'Baik' : ($ikm >= 65 ? 'Kurang Baik' : 'Tidak Baik'));
        }

        $sortedNrr = collect($nrr)->sortDesc();

        $namaUnsur = [
            'U1' => 'Kesesuaian Persyaratan',
            'U2' => 'Prosedur Pelayanan',
            'U3' => 'Kecepatan Pelayanan',
            'U4' => 'Kesesuaian/ Kewajaran Biaya',
            'U5' => 'Kesesuaian Pelayanan',
            'U6' => 'Kompetensi Petugas',
            'U7' => 'Perilaku Petugas Pelayanan',
            'U8' => 'Penanganan Pengaduan',
            'U9' => 'Kualitas Sarana dan Prasarana'
        ];

        // FORMAT DATA UNTUK CHART DI TABEL
        $chartPeringkatLayanan = [];

        foreach ($unsur as $key => $kolom) {
            $chartPeringkatLayanan[] = [
                'id'       => $key,
                'unsur'    => $namaUnsur[$key], // Menggunakan array namaUnsur yang sama
                'nrr'      => isset($nrr[$key]) ? round($nrr[$key], 2) : 0,
                'kategori' => $kategori[$key] ?? 'D',
                'color'    => $unsurColors[$key] ?? '#000000' // Menggunakan warna yang sudah didefinisikan
            ];
        }

        // Hasil akhir tersimpan di variabel: $chartPeringkatTable, $ikmTable, $mutuTable

        // CHART TREN IKM PER TRIWULAN
        $sumAvgExpr = implode(' + ', array_map(fn($f) => "AVG($f)", $fields));

        $ikmRows = Skm::where('status', '!=', 'Privat')
            ->selectRaw("
        YEAR(created_at) as tahun,
        QUARTER(created_at) as triwulan,
        COUNT(*) as total,
        ((($sumAvgExpr) * 0.111 ) * 25) as ikm
    ")
            ->groupBy('tahun', 'triwulan')
            ->orderBy('tahun', 'asc')
            ->orderBy('triwulan', 'asc')
            ->get();

        //  Query untuk mencari Layanan Terbanyak per Triwulan
        $layananRows = Skm::where('status', '!=', 'Privat')
            ->selectRaw("
                YEAR(created_at) as tahun,
                QUARTER(created_at) as triwulan,
                layanan_didapat,
                COUNT(*) as total_layanan
            ")
            ->groupBy('tahun', 'triwulan', 'layanan_didapat')
            ->orderBy('total_layanan', 'desc') // Urutkan dari yang terbanyak
            ->get();

        // Buat Lookup Array: [Tahun][Triwulan] => 'Nama Layanan'
        $topLayananLookup = [];
        foreach ($layananRows as $row) {
            $t = (int)$row->tahun;
            $q = (int)$row->triwulan;

            // Hanya simpan jika belum ada (berarti ini yang terbanyak)
            if (!isset($topLayananLookup[$t][$q])) {
                $topLayananLookup[$t][$q] = $row->layanan_didapat;
            }
        }

        // tahun yang benar-benar ada data
        $ikmYears = $ikmRows->pluck('tahun')
            ->unique()
            ->values()
            ->map(fn($y) => (string)$y)
            ->all();
        $n = count($ikmYears);
        // siapkan series aligned dengan tahun
        $ikmSeries = [
            'TW1' => array_fill(0, $n, null),
            'TW2' => array_fill(0, $n, null),
            'TW3' => array_fill(0, $n, null),
            'TW4' => array_fill(0, $n, null),
        ];
        // Array baru untuk menampung teks kategori
        $metaSeries = [
            'TW1' => array_fill(0, $n, null),
            'TW2' => array_fill(0, $n, null),
            'TW3' => array_fill(0, $n, null),
            'TW4' => array_fill(0, $n, null),
        ];
        // mapping tahun -> index (tetap sama)
        $yearIndex = [];
        foreach ($ikmYears as $i => $y) {
            $yearIndex[(int)$y] = $i;
        }
        // isi nilai IKM per triwulan DAN info layanan terbanyak
        foreach ($ikmRows as $r) {
            $idx = $yearIndex[(int)$r->tahun];
            $q   = (int)$r->triwulan;
            $tw  = 'TW' . $q;
            // Isi Nilai IKM
            $ikmSeries[$tw][$idx] = ((int)$r->total > 0) ? round((float)$r->ikm, 2) : null;
            // Isi Layanan Terbanyak (Ambil dari lookup)
            $metaSeries[$tw][$idx] = $topLayananLookup[(int)$r->tahun][$q] ?? '-';
        }
        // mapping tahun -> index
        $yearIndex = [];
        foreach ($ikmYears as $i => $y) {
            $yearIndex[(int)$y] = $i;
        }
        // isi nilai IKM per triwulan
        foreach ($ikmRows as $r) {
            $idx = $yearIndex[(int)$r->tahun];
            $tw  = 'TW' . (int)$r->triwulan;

            $ikmSeries[$tw][$idx] = ((int)$r->total > 0) ? round((float)$r->ikm, 2) : null;
        }

        // CHART DISTRIBUSI UNSUR PELAYANAN
        $distUnsur = [];
        foreach ($unsur as $u => $kolom) {
            $c1 = (clone $query)->where($kolom, 1)->count();
            $c2 = (clone $query)->where($kolom, 2)->count();
            $c3 = (clone $query)->where($kolom, 3)->count();
            $c4 = (clone $query)->where($kolom, 4)->count();

            $distUnsur[$u] = [
                1 => $totalResponden ? ($c1 / $totalResponden * 100) : 0,
                2 => $totalResponden ? ($c2 / $totalResponden * 100) : 0,
                3 => $totalResponden ? ($c3 / $totalResponden * 100) : 0,
                4 => $totalResponden ? ($c4 / $totalResponden * 100) : 0,
            ];
        }

        // main points line: U1..U9 pakai $snilai
        $snilaiSeries = [];
        $i = 0;
        foreach ($snilai as $u => $val) {
            $snilaiSeries[] = [
                'name' => $u, // U1..U9
                'y' => (float)$val,
                'drilldown' => $u, // id drilldown
            ];
            $i++;
        }

        // drilldown series: untuk tiap Ux tampil 1..4 (termasuk 0)
        $distSeries = [];
        foreach ($distUnsur as $u => $dist) {
            $distSeries[] = [
                'id' => $u,
                'name' => "DISTRIBUSI {$u}",
                'type' => 'column', // bar vertikal
                'data' => [
                    ['1', (float)($dist[1] ?? 0)],
                    ['2', (float)($dist[2] ?? 0)],
                    ['3', (float)($dist[3] ?? 0)],
                    ['4', (float)($dist[4] ?? 0)],
                ],
            ];
        }

        // DONUT CHART PENDIDIKAN
        // Data Pendidikan
        // Pastikan di tabel form_skm nama category-nya 'Pendidikan Terakhir' (sesuaikan jika beda)
        $masterPendidikan = FormSkm::where('category', 'Pendidikan Terakhir')
            ->orderBy('id', 'asc') 
            ->get();

        // Data Pendidikan 
        $rawPendidikan = (clone $query)
            ->select('pendidikan', DB::raw('count(*) as total'))
            ->whereNotNull('pendidikan')
            ->where('pendidikan', '!=', '')
            ->groupBy('pendidikan')
            ->get();

        // Hitung Total Keseluruhan Data Pendidikan
        $grandTotalPendidikan = $rawPendidikan->sum('total');

        // kelompokkan data pendidikan
        $skmPerPendidikan = $masterPendidikan->map(function ($item) use ($rawPendidikan) {
            $masterName = strtoupper(trim($item->name));
            
            // Cari jumlah data yang namanya COCOK dengan master ini
            $totalFound = $rawPendidikan->filter(function ($transaksi) use ($masterName) {
                return strtoupper(trim($transaksi->pendidikan)) === $masterName;
            })->sum('total');

            return (object) [
                'pendidikan_kategori' => $item->name, // Nama asli dari Master
                'total'               => $totalFound
            ];
        });

        // Hitung Sisa untuk Kategori "Lainnya"
        $matchedPendidikan = $skmPerPendidikan->sum('total');
        $sisaPendidikan    = $grandTotalPendidikan - $matchedPendidikan;

        if ($sisaPendidikan > 0) {
            $skmPerPendidikan->push((object)[
                'pendidikan_kategori' => 'Lainnya',
                'total'               => $sisaPendidikan
            ]);
        }
        
        // Urutkan dari terbanyak
        $skmPerPendidikan = $skmPerPendidikan->sortByDesc('total')->values();

        // CHART PROFESI / PEKERJAAN (DINAMIS DARI FORM SKM)
        // Ambil Master Data Pekerjaan
        $masterProfesi = \App\Models\FormSkm::where('category', 'Profesi') 
            ->get();

        // Ambil Data Profesi
        $rawProfesi = (clone $query)
            ->select('profesi', DB::raw('count(*) as total'))
            ->whereNotNull('profesi')
            ->where('profesi', '!=', '')
            ->groupBy('profesi')
            ->get();

        // Hitung Total Keseluruhan Data Profesi
        $grandTotalProfesi = $rawProfesi->sum('total');

        //Mapping Data
        $skmPerProfesi = $masterProfesi->map(function ($item) use ($rawProfesi) {
            $masterName = strtoupper(trim($item->name));
            
            $totalFound = $rawProfesi->filter(function ($transaksi) use ($masterName) {
                return strtoupper(trim($transaksi->profesi)) === $masterName;
            })->sum('total');

            return (object) [
                'profesi_kategori' => $item->name,
                'total'            => $totalFound
            ];
        });

        // Hitung "Lainnya"
        $matchedProfesi = $skmPerProfesi->sum('total');
        $sisaProfesi    = $grandTotalProfesi - $matchedProfesi;

        if ($sisaProfesi > 0) {
            $skmPerProfesi->push((object)[
                'profesi_kategori' => 'Lainnya',
                'total'            => $sisaProfesi
            ]);
        }

        // Urutkan dari terbanyak
        $skmPerProfesi = $skmPerProfesi->sortByDesc('total')->values();

        //CHART DISTRIBUSI PUNGUTAN
        // Ambil Data pungutan
        $masterPungutan = FormSkm::where('category', 'Pungutan')
            ->orderBy('id', 'asc') 
            ->get();

        $rawPungutan = (clone $query)
            ->select('ada_pungutan', DB::raw('count(*) as total'))
            ->whereNotNull('ada_pungutan')
            ->where('ada_pungutan', '!=', '')
            ->groupBy('ada_pungutan')
            ->get();

        $normalizer = function ($str) {
            return strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $str));
        };

        // kelompokkan data
        $skmPungutan = $masterPungutan->map(function ($item) use ($rawPungutan, $normalizer) {
            $masterNameClean = $normalizer($item->name);

            $totalFound = $rawPungutan->filter(function ($transaksi) use ($masterNameClean, $normalizer) {
                return $normalizer($transaksi->ada_pungutan) === $masterNameClean;
            })->sum('total');

            return (object) [
                'label' => $item->name, // Nama Resmi (Ada / Tidak Ada)
                'total' => $totalFound
            ];
        });

        
        // AMBIL KATEGORI DARI FORM SKM
        
        // Setup Mapping & Default Parent
        $mapChildToParent = [];
        $subServiceColors = [];
        $defaultParentName = 'Fasilitasi Bantuan Teknis'; // Pastikan nama ini ada di DB

        foreach ($masterSkm as $parent) {
            $pName = $parent->name;
            $pColor = $serviceColors[$pName] ?? '#666666';

            // Mapping Parent ke Dirinya Sendiri
            $mapChildToParent[$pName] = $pName;
            $subServiceColors[$pName] = $pColor;

            // Mapping Anak ke Parent & Pewarisan Warna
            foreach ($parent->subs as $sub) {
                $mapChildToParent[$sub->name] = $pName;
                $subServiceColors[$sub->name] = $pColor;
            }
        }
        // Warna khusus untuk label Lainnya (ikut default parent)
        $subServiceColors['Lainnya'] = $subServiceColors[$defaultParentName] ?? '#666666';

        // QUERY AGREGAT (TIDAK DIUBAH) UNTUK DI CHART UNSUR DAN IKM TIAP KATEGORI 
        $agg = (clone $query)
            ->select('layanan_didapat')
            ->selectRaw('COUNT(*) as total');

        foreach ($fields as $i => $col) {
            $u = $i + 1;
            $agg->selectRaw("SUM(CASE WHEN $col BETWEEN 1 AND 4 THEN $col ELSE 0 END) as U{$u}_sum");
            $agg->selectRaw("SUM(CASE WHEN $col = 1 THEN 1 ELSE 0 END) as U{$u}_n1");
            $agg->selectRaw("SUM(CASE WHEN $col = 2 THEN 1 ELSE 0 END) as U{$u}_n2");
            $agg->selectRaw("SUM(CASE WHEN $col = 3 THEN 1 ELSE 0 END) as U{$u}_n3");
            $agg->selectRaw("SUM(CASE WHEN $col = 4 THEN 1 ELSE 0 END) as U{$u}_n4");
            $agg->selectRaw("SUM(CASE WHEN $col BETWEEN 1 AND 4 THEN 1 ELSE 0 END) as U{$u}_valid");
        }

        $rows = $agg->groupBy('layanan_didapat')
            ->orderByDesc('total')
            ->get();

        $rowsByLayanan = [];
        $totalTop = [];      // Total per Parent (Main Chart)
        $drillListData = []; // Data untuk Drilldown Level 1 (Daftar Layanan)

        // Siapkan wadah untuk menampung "Lainnya" (Agregasi manual)
        $aggLainnya = ['total' => 0];
        for ($u = 1; $u <= 9; $u++) {
            $aggLainnya["U{$u}_sum"] = 0; $aggLainnya["U{$u}_n1"] = 0; $aggLainnya["U{$u}_n2"] = 0;
            $aggLainnya["U{$u}_n3"] = 0; $aggLainnya["U{$u}_n4"] = 0; $aggLainnya["U{$u}_valid"] = 0;
        }

        foreach ($rows as $r) {
            $layananName = $r->layanan_didapat ?? 'Tidak diisi';
            $totalRow    = (int) $r->total;

            // Cek Parent dari Mapping DB. Jika tidak ada, lempar ke Default
            $parentName = $mapChildToParent[$layananName] ?? $defaultParentName;

            // Hitung Total Main Chart
            $totalTop[$parentName] = ($totalTop[$parentName] ?? 0) + $totalRow;

            // Logic Pembagian: Dikenali vs Tidak Dikenali
            if (isset($mapChildToParent[$layananName])) {
                // Layanan Sah / Terdaftar
                $rowsByLayanan[$layananName] = $r;
                $drillListData[$parentName][$layananName] = ($drillListData[$parentName][$layananName] ?? 0) + $totalRow;
            } else {
                // Layanan Tidak Dikenali -> Masuk ke "Lainnya" di dalam Default Parent
                $drillListData[$defaultParentName]['Lainnya'] = ($drillListData[$defaultParentName]['Lainnya'] ?? 0) + $totalRow;

                // Akumulasi data mentah untuk deep drilldown U1-U9 "Lainnya"
                $aggLainnya['total'] += $totalRow;
                for($u=1; $u<=9; $u++) {
                    $aggLainnya["U{$u}_sum"]   += (float) data_get($r, "U{$u}_sum", 0);
                    $aggLainnya["U{$u}_n1"]    += (int) data_get($r, "U{$u}_n1", 0);
                    $aggLainnya["U{$u}_n2"]    += (int) data_get($r, "U{$u}_n2", 0);
                    $aggLainnya["U{$u}_n3"]    += (int) data_get($r, "U{$u}_n3", 0);
                    $aggLainnya["U{$u}_n4"]    += (int) data_get($r, "U{$u}_n4", 0);
                    $aggLainnya["U{$u}_valid"] += (int) data_get($r, "U{$u}_valid", 0);
                }
            }
        }

        // Masukkan row buatan "Lainnya" ke $rowsByLayanan agar loop paling bawah jalan
        if ($aggLainnya['total'] > 0) {
            $attributes = array_merge(['layanan_didapat' => 'Lainnya'], $aggLainnya);
            $rowsByLayanan['Lainnya'] = new \Illuminate\Support\Fluent($attributes);
        }

        $kategoriLayananMainSeries = [];
        $kategoriLayananDrillSeries = [];


        // KONSTRUKSI MAIN CHART & DRILLDOWN LEVEL 1 (DAFTAR LAYANAN)
        
        // Loop Master SKM agar urutan grafik sesuai Database
        foreach ($masterSkm as $parent) {
            $pName = $parent->name;
            
            // Skip jika tidak ada data sama sekali
            if (empty($totalTop[$pName])) continue;

            $hasSub = $parent->subs->count() > 0;
            $childList = $drillListData[$pName] ?? [];
            
            // Logic Drilldown: Aktif jika punya Sub ATAU item di dalamnya lebih dari 1
            $enableListDrill = ($hasSub || count($childList) > 1);

            $idMap = [];
            $makeId = function (string $prefix, string $name) use (&$idMap) {
                $key = $prefix . '|' . $name;
                if (!isset($idMap[$key])) {
                    $idMap[$key] = $prefix . '_' . \Illuminate\Support\Str::slug($name, '_') . '_' . substr(md5($name), 0, 6);
                }
                return $idMap[$key];
            };

            // Jika item cuma 1 dan namanya sama dengan parent (Layanan Tunggal / Excluded style), 
            // maka drilldown langsung ke U1-U9 (svc), bukan ke list (grp)
            if (count($childList) === 1 && isset($childList[$pName]) && !$hasSub) {
                $enableListDrill = false;
                $drillId = $makeId('svc', $pName); // Langsung deep drill
            } else {
                $drillId = $enableListDrill ? $makeId('grp', $pName) : $makeId('svc', $pName);
            }

            // --- MAIN SERIES ---
            $kategoriLayananMainSeries[] = [
                'name'      => $pName,
                'y'         => (int) $totalTop[$pName],
                'drilldown' => $drillId,
                'color'     => $serviceColors[$pName] ?? null
            ];

            // --- DRILLDOWN (LIST LAYANAN) distribusi unsur tiap kategori layanan ---
            if ($enableListDrill) {
                arsort($childList); // Urutkan terbanyak
                $listData = [];
                foreach ($childList as $cName => $cTotal) {
                    $listData[] = [
                        'name'      => $cName,
                        'y'         => (int) $cTotal,
                        'drilldown' => $makeId('svc', $cName), // Link ke U1-U9
                        'color'     => $subServiceColors[$cName] ?? null
                    ];
                }

                $kategoriLayananDrillSeries[] = [
                    'id'   => $drillId,
                    'type' => 'line', // Sesuai kode asli Anda pakai line
                    'name' => 'Daftar Layanan - ' . $pName,
                    'data' => $listData
                ];
            }
        }

        // DRILLDOWN U1..U9 distribusi unsur tiap kategori layanan
        foreach ($rowsByLayanan as $layanan => $r) {
            $idSvc = $makeId('svc', $layanan);

            $uLineData = [];
            for ($u = 1; $u <= 9; $u++) {
                $sum = (float) data_get($r, "U{$u}_sum", 0);
                $idDist = "{$idSvc}_U{$u}_dist";

                $uLineData[] = [
                    'name'      => "U{$u}",
                    'y'         => $sum,
                    'drilldown' => $idDist,
                ];

                $n1 = (int) data_get($r, "U{$u}_n1", 0);
                $n2 = (int) data_get($r, "U{$u}_n2", 0);
                $n3 = (int) data_get($r, "U{$u}_n3", 0);
                $n4 = (int) data_get($r, "U{$u}_n4", 0);
                $valid = (int) data_get($r, "U{$u}_valid", 0);

                $p1 = $valid > 0 ? round($n1 / $valid * 100, 2) : 0;
                $p2 = $valid > 0 ? round($n2 / $valid * 100, 2) : 0;
                $p3 = $valid > 0 ? round($n3 / $valid * 100, 2) : 0;
                $p4 = $valid > 0 ? round($n4 / $valid * 100, 2) : 0;

                $kategoriLayananDrillSeries[] = [
                    'id'   => $idDist,
                    'type' => 'column',
                    'name' => "Distribusi U{$u} (%) - {$layanan}",
                    'data' => [
                        ['1', $p1],
                        ['2', $p2],
                        ['3', $p3],
                        ['4', $p4],
                    ],
                ];
            }

            $kategoriLayananDrillSeries[] = [
                'id'   => $idSvc,
                'type' => 'line',
                'name' => "SUM Nilai Unsur (U1–U9) - {$layanan}",
                'data' => $uLineData,
            ];
        }


        //CHART IKM (WITH "LAINNYA" GROUPING)
        $fnHitungIKM = function ($sums, $valids) {
            $totalAvg = 0;
            for ($u = 1; $u <= 9; $u++) {
                $v = $valids[$u] ?? 0;
                $s = $sums[$u] ?? 0;
                // Hindari division by zero
                $avg = $v > 0 ? ($s / $v) : 0;
                $totalAvg += $avg;
            }
            // Rumus IKM konversi ke 100
            return round(($totalAvg * 0.111) * 25, 2);
        };

        // BUAT MAPPING & WARNA
        $mapServiceToParent = []; 
        $parentMeta = []; 
        
        // Tentukan Target Induk untuk data sampah/tidak dikenal
        $defaultParentName = 'Fasilitasi Bantuan Teknis'; 
        $defaultChildName  = 'Lainnya';

        foreach ($masterSkm as $parent) {
            $pName = $parent->name;
            $pColor = $serviceColors[$pName] ?? '#9bc6bf'; 

            // Simpan Metadata Parent
            $parentMeta[$pName] = [
                'color'   => $pColor,
                'has_sub' => $parent->subs->count() > 0
            ];

            // Mapping: Nama Induk -> Induk
            $mapServiceToParent[$pName] = $pName;

            // Mapping: Nama Anak -> Induk
            foreach ($parent->subs as $sub) {
                $mapServiceToParent[$sub->name] = $pName;
            }
        }

        //  Disrtibusi ikm tiap kategori layanan ---
        $tempChildData = [];

        foreach ($rowsByLayanan as $layananName => $r) {
            
            // LOGIKA PENENTUAN PARENT & CHILD NAME
            if (isset($mapServiceToParent[$layananName])) {
                // KASUS 1: Layanan Dikenali (Ada di DB)
                $parentName = $mapServiceToParent[$layananName];
                $childName  = $layananName; // Pakai nama aslinya
            } else {
                // KASUS 2: Layanan TIDAK Dikenali (Masuk ke Lainnya)
                $parentName = $defaultParentName;
                $childName  = $defaultChildName; // Pakai nama "Lainnya"
            }

            // Inisialisasi array jika belum ada
            if (!isset($tempChildData[$parentName][$childName])) {
                for($u=1; $u<=9; $u++) {
                    $tempChildData[$parentName][$childName]['sums'][$u] = 0;
                    $tempChildData[$parentName][$childName]['valids'][$u] = 0;
                }
            }

            // Akumulasi Nilai Unsur (Gabungkan data)
            // Ini penting agar jika ada "Typo A" dan "Typo B", keduanya masuk ke "Lainnya"
            for($u=1; $u<=9; $u++) {
                $sumVal   = (float) data_get($r, "U{$u}_sum", 0);
                $validVal = (int) data_get($r, "U{$u}_valid", 0);

                $tempChildData[$parentName][$childName]['sums'][$u]   += $sumVal;
                $tempChildData[$parentName][$childName]['valids'][$u] += $validVal;
            }
        }

        // HITUNG IKM & SUSUN STRUKTUR CHART ---
        
        $ikmMainSeries  = [];
        $ikmDrillSeries = [];

        // Loop berdasarkan Master Data (Agar urutan chart sesuai DB)
        foreach ($masterSkm as $parent) {
            $pName = $parent->name;

            // Cek apakah ada data transaksi untuk parent ini?
            // (Data transaksi ada di $tempChildData[$pName])
            if (!isset($tempChildData[$pName])) continue;

            // Wadah hitung total Parent
            $parentSums = [];
            $parentValids = [];
            
            // Wadah data anak untuk chart
            $myChildrenData = [];

            // Loop setiap Anak/Sub di dalam Parent ini (termasuk "Lainnya" jika ada)
            foreach ($tempChildData[$pName] as $cName => $cData) {
                // Hitung IKM si Anak
                $childScore = $fnHitungIKM($cData['sums'], $cData['valids']);
                
                // Masukkan ke list drilldown
                $myChildrenData[] = [
                    'name'  => $cName,
                    'y'     => $childScore,
                    'color' => $parentMeta[$pName]['color'] ?? '#9bc6bf' // Warisi warna induk
                ];

                // Akumulasi ke Total Parent
                for($u=1; $u<=9; $u++) {
                    $parentSums[$u]   = ($parentSums[$u] ?? 0) + $cData['sums'][$u];
                    $parentValids[$u] = ($parentValids[$u] ?? 0) + $cData['valids'][$u];
                }
            }

            // Hitung IKM Akhir Parent
            $parentScore = $fnHitungIKM($parentSums, $parentValids);

            // LOGIK DRILLDOWN
            // Drilldown aktif jika: Punya Sub di DB ATAU Jumlah item anaknya > 1 (misal ada "Lainnya")
            $hasSubInDB = $parentMeta[$pName]['has_sub'];
            $hasMultipleItems = count($myChildrenData) > 1;
            
            // Khusus: Jika cuma ada 1 item dan namanya SAMA dengan Parent (Layanan Tunggal), matikan drilldown
            if (count($myChildrenData) === 1 && $myChildrenData[0]['name'] === $pName && !$hasSubInDB) {
                $hasDrill = false;
            } else {
                // Default: Aktifkan jika memang ada struktur sub atau akumulasi data
                $hasDrill = $hasSubInDB || $hasMultipleItems;
            }

            $drillId = $hasDrill ? 'drill_' . Str::slug($pName) : null;

            // Push Main Series
            $ikmMainSeries[] = [
                'name'      => $pName,
                'y'         => $parentScore,
                'color'     => $parentMeta[$pName]['color'],
                'drilldown' => $drillId
            ];

            // Push Drilldown Series
            if ($hasDrill) {
                // Sort anak dari nilai tertinggi
                usort($myChildrenData, fn($a, $b) => $b['y'] <=> $a['y']);

                $ikmDrillSeries[] = [
                    'id'   => $drillId,
                    'name' => 'Rincian: ' . $pName,
                    'data' => $myChildrenData
                ];
            }
        }

        return view('admin.statistik-skm.index', [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'kategoriLayananPerAspek' => $kategoriLayananPerAspek,
            'jumlahDataPerAspek' => $jumlahDataPerAspek,
            'distribusiGender' => $distribusiGender,
            'skmRekomen' => $skmRekomen,
            'year'      => $year,
            'totalResponden' => $totalResponden,
            'layananTerbanyak' => $layananTerbanyak,
            'skmPublik' => $skmPublik,
            'skmPrivat' => $skmPrivat,
            'skmData' => $skmData,
            'nrr' => $nrr,
            'jumlahNRRTertimbang' => $jumlahNRRTertimbang,
            'ikm' => $ikm,
            'mutu' => $mutu,
            'sortedNrr' => $sortedNrr,
            'namaUnsur' => $namaUnsur,
            'ikmYears'  => $ikmYears,
            'ikmSeries' => $ikmSeries,
            'snilaiSeries' => $snilaiSeries,
            'distSeries'   => $distSeries,
            'skmPerPendidikan' => $skmPerPendidikan,
            'pendidikanColors' => $pendidikanColors,
            'skmPerProfesi' => $skmPerProfesi,
            'profesiColors' => $profesiColors,
            'skmPungutan' => $skmPungutan,
            'kategoriLayananMainSeries' => $kategoriLayananMainSeries,
            'kategoriLayananDrillSeries' => $kategoriLayananDrillSeries,
            'serviceColors'    => $serviceColors,
            'subServiceColors' => $subServiceColors, // <--- Variable baru ini
            'unsurColors'      => $unsurColors,
            'scoreColor'       => $scoreColor,
            'metaSeries' => $metaSeries,
            'warna2Chart' => $warna2Chart,
            'warnaTrenIKM' => $warnaTrenIKM,
            'warnaRerataUnsurPelayanan' => $warnaRerataUnsurPelayanan,
            'chartPeringkatLayanan' => $chartPeringkatLayanan,
            'ikmMainSeries' => $ikmMainSeries,   
            'ikmDrillSeries' => $ikmDrillSeries, 
        ]);
    }

    private function getDateRange(Request $request)
    {
        $dateFilter = $request->input('date_filter');
        $year = $request->input('year', date('Y'));

        $startDate = null;
        $endDate = null;

        if ($dateFilter == 'today') {
            $startDate = Carbon::today();
            $endDate = Carbon::today();
        } elseif ($dateFilter == 'last_7_days') {
            $startDate = Carbon::today()->subDays(6);
            $endDate = Carbon::today();
        } elseif ($dateFilter == 'last_month') {
            $startDate = Carbon::today()->subDays(29);
            $endDate = Carbon::today();
        } elseif ($dateFilter == 'whole_year') {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
        } elseif ($dateFilter == 'custom') {
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = Carbon::parse($request->input('start_date'));
                $endDate = Carbon::parse($request->input('end_date'));
            } else {
                $startDate = Carbon::today()->subMonth();
                $endDate = Carbon::today();
            }
        } elseif (Str::startsWith($dateFilter, 'triwulan_')) {
            $triwulan = explode('_', $dateFilter)[1];
            $startMonth = ($triwulan - 1) * 3 + 1;
            $startDate = Carbon::createFromDate($year, $startMonth, 1);
            $endDate = $startDate->copy()->addMonths(2)->endOfMonth();
        } elseif ($dateFilter == 'all_triwulan') {
            $startDate = Carbon::createFromDate($year, 1, 1);
            $endDate = Carbon::createFromDate($year, 12, 31);
        } elseif (Str::startsWith($dateFilter, 'semester_')) {
            $semester = explode('_', $dateFilter)[1];
            if ($semester == 1) {
                $startDate = Carbon::createFromDate($year, 1, 1);
                $endDate = Carbon::createFromDate($year, 6, 30);
            } elseif ($semester == 2) {
                $startDate = Carbon::createFromDate($year, 7, 1);
                $endDate = Carbon::createFromDate($year, 12, 31);
            }
        } elseif ($dateFilter == 'all_semester') {
            $startDate = Carbon::createFromDate($year, 1, 1);
            $endDate = Carbon::createFromDate($year, 12, 31);
        } elseif ($dateFilter == 'all_time') {
            // Ambil data terlama
            $oldestData = Skm::oldest('created_at')->first();

            // REVISI: Jika tidak ada data, default ke HARI INI (Carbon::today())
            $startDate = $oldestData ? $oldestData->created_at : Carbon::today();

            $endDate = Carbon::now();
        } else {
            // Default: 1 Bulan Terakhir
            $startDate = Carbon::today()->subDays(29);
            $endDate = Carbon::today();
        }

        return [
            'start' => $startDate->startOfDay(),
            'end'   => $endDate->endOfDay()
        ];
    }

    public function export(Request $request)
    {
        // =========================
        // DATE RANGE: jika kosong => 30 hari terakhir
        // =========================
        $range = $this->getDateRange($request);
        $startDate = $range['start'];
        $endDate   = $range['end'];

        // ambil data skm
        $skmData = Skm::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'Privat')
            ->get();

        if ($skmData->isEmpty()) {
            return redirect()->back()->with('error', 'Tabel Daftar Responden Kosong, tidak ada data yang di export');
        }

        $type = $request->query('type');
        $total = $skmData->count();

        // --- A. JENIS KELAMIN ---
        // Kita gunakan ->where() milik Collection, bukan Eloquent
        $laki      = $skmData->where('jenis_kelamin', 'Laki - laki')->count();
        $perempuan = $skmData->where('jenis_kelamin', 'Perempuan')->count();

        // Kita gunakan fn($row) (Arrow Function) agar lebih ringkas

        $sd = $skmData->filter(function ($row) {
            return strtolower(trim($row->pendidikan)) == 'sd';
        })->count();

        $smp = $skmData->filter(function ($row) {
            return strtolower(trim($row->pendidikan)) == 'smp';
        })->count();

        $sma = $skmData->filter(function ($row) {
            return strtolower(trim($row->pendidikan)) == 'sma';
        })->count();

        $d3 = $skmData->filter(function ($row) {
            // Sesuai request: IN ('d1-d3')
            return strtolower(trim($row->pendidikan)) == 'd1-d3';
        })->count();

        $s1 = $skmData->filter(function ($row) {
            return strtolower(trim($row->pendidikan)) == 's1';
        })->count();

        $s2 = $skmData->filter(function ($row) {
            return strtolower(trim($row->pendidikan)) == 's2';
        })->count();

        $s3 = $skmData->filter(function ($row) {
            return strtolower(trim($row->pendidikan)) == 's3';
        })->count();

        // Menghitung sisa (Lainnya)
        $lainNya = $total - ($sd + $smp + $sma + $d3 + $s1 + $s2 + $s3);

        // --- C. PROFESI ---
        $pns       = $skmData->where('profesi', 'PNS')->count();
        $pppk      = $skmData->where('profesi', 'PPPK')->count();
        $tniPolri  = $skmData->where('profesi', 'TNI/POLRI')->count();
        $swasta    = $skmData->where('profesi', 'SWASTA')->count();
        $wirausaha = $skmData->where('profesi', 'WIRAUSAHA')->count();
        $mahasiswa = $skmData->where('profesi', 'MAHASISWA')->count();
        $siswa     = $skmData->where('profesi', 'SISWA')->count();

        $lainNyaProfesi = $total - ($pns + $pppk + $tniPolri + $swasta + $wirausaha + $mahasiswa + $siswa);

        // --- D. JENIS LAYANAN ---
        // whereIn juga tersedia di Collection
        $fbt = $skmData->whereIn('layanan_didapat', [
            'Juri, Narasumber, dan/atau  Pendampingan Kebahasaan dan Kesastraan',
            'Penyuluhan',
            'Penyuntingan',
            'Saksi Ahli (Bahasa dan Hukum)',
            'Fasilitasi Ke-BIPA-an',
            'Literasi',
            'Permintaan data Kebahasaan dan Kesastraan'
        ])->count();

        $penerjemahan = $skmData->whereIn('layanan_didapat', [
            'Penerjemahan Tulis',
            'Penerjemahan Lisan (Juru Bahasa)'
        ])->count();

        $magang       = $skmData->where('layanan_didapat', 'Praktik Kerja Lapangan (Pemagangan)')->count();
        $sarana       = $skmData->where('layanan_didapat', 'Sarana dan Prasarana')->count();
        $ukbi         = $skmData->where('layanan_didapat', 'UKBI')->count();
        $edukasi      = $skmData->where('layanan_didapat', 'Kunjungan Edukasi')->count();
        $perpustakaan = $skmData->where('layanan_didapat', 'Perpustakaan')->count();
        $lainNyaLayananDidapat = $total - ($fbt + $penerjemahan + $magang + $sarana + $ukbi + $edukasi + $perpustakaan);

        // ==== Generate Excel ====
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sampling');

        // Judul

        // ====== ROW 1 ======
        $sheet->setCellValue('A1', 'Sampel Minimal');
        $sheet->setCellValue('B1', '=');
        $sheet->setCellValue('C1', '380');
        $sheet->setCellValue('E1', '38410');

        // Bold A1 dan C1
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        // Background E1 (warna coklat misalnya)
        $sheet->getStyle('E1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('953734'); // coklat


        // ====== ROW 2 ======
        $sheet->setCellValue('A2', 'Populasi');
        $sheet->setCellValue('B2', '=');
        $sheet->setCellValue('C2', '40000');
        $sheet->setCellValue('E2', 100.95775);

        // Bold A2 dan C2
        $sheet->getStyle('A2:C2')->getFont()->setBold(true);

        // Background E2 (coklat)
        $sheet->getStyle('E2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('953734');

        // ====== ROW 4 ======
        $sheet->setCellValue('A4', 'Jumlah Sampel Sesungguhnya');
        $sheet->setCellValue('B4', '=');
        $sheet->setCellValue('C4', $total);

        // Bold A4:C4
        $sheet->getStyle('A4:C4')->getFont()->setBold(true);

        // Background biru A4:C4
        $sheet->getStyle('A4:C4')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('B8CCE4'); // biru muda

        // ====== ROW 6 ======
        $sheet->setCellValue('A6', 'Jenis Kelamin');
        $sheet->setCellValue('B6', 'Jumlah');

        // Bold A4:C4
        $sheet->getStyle('A6:C6')->getFont()->setBold(true);

        $sheet->setCellValue('A7', 'Laki-Laki');
        $sheet->setCellValue('B7', $laki);
        $sheet->setCellValue('C7', $total > 0 ? ($laki / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A8', 'Perempuan');
        $sheet->setCellValue('B8', $perempuan);
        $sheet->setCellValue('C8', $total > 0 ? ($perempuan / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A10', 'Pendidikan');
        $sheet->setCellValue('B10', 'Jumlah');
        $sheet->getStyle('A10:B10')->getFont()->setBold(true);
        $sheet->setCellValue('A11', 'SD ke Bawah');
        $sheet->setCellValue('B11', $sd);
        $sheet->setCellValue('C11', $total > 0 ? ($sd / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A12', 'SLTP/SMP');
        $sheet->setCellValue('B12', $smp);
        $sheet->setCellValue('C12', $total > 0 ? ($smp / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A13', 'SLTA/SMA');
        $sheet->setCellValue('B13', $sma);
        $sheet->setCellValue('C13', $total > 0 ? ($sma / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A14', 'D/III');
        $sheet->setCellValue('B14', $d3);
        $sheet->setCellValue('C14', $total > 0 ? ($d3 / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A15', 'S1');
        $sheet->setCellValue('B15', $s1);
        $sheet->setCellValue('C15', $total > 0 ? ($s1 / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A16', 'S2');
        $sheet->setCellValue('B16', $s2);
        $sheet->setCellValue('C16', $total > 0 ? ($s2 / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A17', 'S3');
        $sheet->setCellValue('B17', $s3);
        $sheet->setCellValue('C17', $total > 0 ? ($s3 / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A18', 'Lainnya');
        $sheet->setCellValue('B18', $lainNya);
        $sheet->setCellValue('C18', $total > 0 ? ($lainNya / $total) * 100 / 100 : 0);

        $sheet->setCellValue('A20', 'Pekerjaan');
        $sheet->setCellValue('B20', 'Jumlah');
        $sheet->getStyle('A20:B20')->getFont()->setBold(true);
        $sheet->setCellValue('A21', 'PNS');
        $sheet->setCellValue('B21', $pns);
        $sheet->setCellValue('C21', $total > 0 ? ($pns / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A22', 'PPPK');
        $sheet->setCellValue('B22', $pppk);
        $sheet->setCellValue('C22', $total > 0 ? ($pppk / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A23', 'TNI/POLRI');
        $sheet->setCellValue('B23', $tniPolri);
        $sheet->setCellValue('C23', $total > 0 ? ($tniPolri / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A24', 'Swasta');
        $sheet->setCellValue('B24', $swasta);
        $sheet->setCellValue('C24', $total > 0 ? ($swasta / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A25', 'Wirausaha');
        $sheet->setCellValue('B25', $wirausaha);
        $sheet->setCellValue('C25', $total > 0 ? ($wirausaha / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A26', 'Mahasiswa');
        $sheet->setCellValue('B26', $mahasiswa);
        $sheet->setCellValue('C26', $total > 0 ? ($mahasiswa / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A27', 'Siswa');
        $sheet->setCellValue('B27', $siswa);
        $sheet->setCellValue('C27', $total > 0 ? ($siswa / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A28', 'Lainnya');
        $sheet->setCellValue('B28', $lainNyaProfesi);
        $sheet->setCellValue('C28', $total > 0 ? ($lainNyaProfesi / $total) * 100 / 100 : 0);

        $sheet->setCellValue('A30', 'Jenis Layanan');
        $sheet->setCellValue('B30', 'Jumlah');
        $sheet->getStyle('A30:B30')->getFont()->setBold(true);
        $sheet->setCellValue('A31', 'Layanan Fasilitasi Bantuan Teknis');
        $sheet->setCellValue('B31', $fbt);
        $sheet->setCellValue('C31', $total > 0 ? ($fbt / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A32', 'Layanan Penerjemahan');
        $sheet->setCellValue('B32', $penerjemahan);
        $sheet->setCellValue('C32', $total > 0 ? ($penerjemahan / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A33', 'Layanan Pemagangan');
        $sheet->setCellValue('B33', $magang);
        $sheet->setCellValue('C33', $total > 0 ? ($magang / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A34', 'Layanan Sarana Prasarana');
        $sheet->setCellValue('B34', $sarana);
        $sheet->setCellValue('C34', $total > 0 ? ($sarana / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A35', 'UKBI');
        $sheet->setCellValue('B35', $ukbi);
        $sheet->setCellValue('C35', $total > 0 ? ($ukbi / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A36', 'Layanan Kunjungan Edukasi');
        $sheet->setCellValue('B36', $edukasi);
        $sheet->setCellValue('C36', $total > 0 ? ($edukasi / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A37', 'Layanan Perpustakaan');
        $sheet->setCellValue('B37', $perpustakaan);
        $sheet->setCellValue('C37', $total > 0 ? ($perpustakaan / $total) * 100 / 100 : 0);
        $sheet->setCellValue('A38', 'Lainnya');
        $sheet->setCellValue('B38', $lainNyaLayananDidapat);
        $sheet->setCellValue('C38', $total > 0 ? ($lainNyaLayananDidapat / $total) * 100 / 100 : 0);

        $sheet->getStyle('C7:C38')
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE);

        $sheet->getStyle('A1:E38')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => Color::COLOR_BLACK],
                ],
            ],
        ]);

        // AUTO WIDTH
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        // Kolom B Rata Tengah
        $sheet->getStyle('B')->getAlignment()->setHorizontal(
            \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        );

        $sheet->getStyle('C')->getAlignment()->setHorizontal(
            \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
        );


        // ==================== Export Tabel SKM ====================
        $sheetKuesioner = $spreadsheet->createSheet();
        $sheetKuesioner->setTitle('Kuesioner');
        $sheetKuesioner->getColumnDimension('A')->setWidth(28);
        $lastRow  = $sheetKuesioner->getHighestRow();
        $judulRow = $lastRow;

        // Judul 1
        $sheetKuesioner->mergeCells("A{$judulRow}:J{$judulRow}");
        $sheetKuesioner->setCellValue("A{$judulRow}", 'PENGOLAHAN DATA HASIL SURVEY KEPUASAN MASYARAKAT PER RESPONDEN');

        // Judul 2 (di bawahnya)
        $judulRow2 = $judulRow + 1;
        $sheetKuesioner->mergeCells("A{$judulRow2}:J{$judulRow2}");
        $sheetKuesioner->setCellValue("A{$judulRow2}", 'DAN PER UNSUR PELAYANAN');

        // Styling (biar rapi)
        $sheetKuesioner->getStyle("A{$judulRow}:J{$judulRow2}")
            ->getFont()
            ->setBold(true)
            ->setSize(12)
            ->setName('Arial');

        $sheetKuesioner->getStyle("A{$judulRow}:J{$judulRow2}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Baris 1: UNIT PELAYANAN
        $infoRow1 = $judulRow2 + 1;
        $sheetKuesioner->mergeCells("B{$infoRow1}:D{$infoRow1}");
        $sheetKuesioner->mergeCells("F{$infoRow1}:I{$infoRow1}");
        $sheetKuesioner->setCellValue("B{$infoRow1}", "UNIT PELAYANAN");
        $sheetKuesioner->setCellValue("E{$infoRow1}", ":");
        $sheetKuesioner->getStyle("E{$infoRow1}")->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheetKuesioner->setCellValue("F{$infoRow1}", "BALAI BAHASA PROVINSI JAMBI");

        // Baris 2: JENIS LAYANAN (tepat di bawahnya)
        $infoRow2 = $infoRow1 + 1;
        $sheetKuesioner->mergeCells("B{$infoRow2}:D{$infoRow2}");
        $sheetKuesioner->mergeCells("F{$infoRow2}:I{$infoRow2}");
        $sheetKuesioner->setCellValue("B{$infoRow2}", "JENIS LAYANAN");
        $sheetKuesioner->setCellValue("E{$infoRow2}", ":");
        $sheetKuesioner->getStyle("E{$infoRow2}")->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Font Arial 12 untuk kedua baris
        $sheetKuesioner->getStyle("B{$infoRow1}:F{$infoRow2}")
            ->getFont()
            ->setName('Arial')
            ->setSize(12);

        // ====== Spasi 2 baris setelah info ======
        $tableRow  = $infoRow2 + 3;      // +1 kosong, +2 kosong, +3 mulai tabel
        $hdrRow1   = $tableRow;
        $hdrRow2   = $tableRow + 1;
        $hdrRow3   = $tableRow + 2;

        // ====== Header tabel ======
        // Kolom A: NO. RES (merge 3 baris)
        $sheetKuesioner->mergeCells("A{$hdrRow1}:A{$hdrRow3}");
        $sheetKuesioner->setCellValue("A{$hdrRow1}", "NO. RES");

        // Kolom B-J: NILAI UNSUR PELAYANAN (merge 2 baris)
        $sheetKuesioner->mergeCells("B{$hdrRow1}:J{$hdrRow2}");
        $sheetKuesioner->setCellValue("B{$hdrRow1}", "NILAI UNSUR PELAYANAN");

        // Baris ke-3 header: U1 s/d U9 di kolom B-J
        $unsur = ['U1', 'U2', 'U3', 'U4', 'U5', 'U6', 'U7', 'U8', 'U9'];
        $col = 'B';
        foreach ($unsur as $u) {
            $sheetKuesioner->setCellValue("{$col}{$hdrRow3}", $u);
            $col++; // B -> C -> ... -> J
        }

        // ====== Styling header (Arial 12, bold, center) ======
        $sheetKuesioner->getStyle("A{$hdrRow1}:J{$hdrRow3}")
            ->getFont()
            ->setName('Arial')
            ->setSize(10)
            ->setBold(true);

        $sheetKuesioner->getStyle("A{$hdrRow1}:J{$hdrRow3}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // (Opsional) wrap text biar judul rapi
        $sheetKuesioner->getStyle("B{$hdrRow1}")->getAlignment()->setWrapText(true);

        // Tinggi baris header (opsional)
        $sheetKuesioner->getRowDimension($hdrRow1)->setRowHeight(22);
        $sheetKuesioner->getRowDimension($hdrRow2)->setRowHeight(22);
        $sheetKuesioner->getRowDimension($hdrRow3)->setRowHeight(20);

        // ====== Garis tabel (border) untuk header ======
        $sheetKuesioner->getStyle("A{$hdrRow1}:J{$hdrRow3}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // ==================== Isi Data SKM (pakai $skmData) ====================
        $dataStartRow = $hdrRow3 + 1;
        $currentRow   = $dataStartRow;

        if (isset($skmData) && count($skmData) > 0) {

            foreach ($skmData as $index => $row) {
                // Kolom A: NO. RES
                $sheetKuesioner->setCellValue("A{$currentRow}", $index + 1);

                // Kolom B-J: nilai unsur U1 - U9 (sama seperti di @foreach blade)
                $sheetKuesioner->setCellValue("B{$currentRow}", $row->syarat_pengurusan_pelayanan);
                $sheetKuesioner->setCellValue("C{$currentRow}", $row->sistem_mekanisme_dan_prosedur_pelayanan);
                $sheetKuesioner->setCellValue("D{$currentRow}", $row->waktu_penyelesaian_pelayanan);
                $sheetKuesioner->setCellValue("E{$currentRow}", $row->kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan);
                $sheetKuesioner->setCellValue("F{$currentRow}", $row->kesesuaian_hasil_pelayanan);
                $sheetKuesioner->setCellValue("G{$currentRow}", $row->kemampuan_petugas_dalam_memberikan_pelayanan);
                $sheetKuesioner->setCellValue("H{$currentRow}", $row->kesopanan_dan_keramahan_petugas);
                $sheetKuesioner->setCellValue("I{$currentRow}", $row->penanganan_pengaduan_saran_dan_masukan);
                $sheetKuesioner->setCellValue("J{$currentRow}", $row->sarana_dan_prasarana_penunjang_pelayanan);
                $sheetKuesioner->setCellValue("K{$currentRow}", "=COUNTBLANK(B{$currentRow}:J{$currentRow})");

                $currentRow++;
            }

            $lastDataRow = $currentRow - 1;

            // Styling data: Arial 12, center, border tipis
            $sheetKuesioner->getStyle("A{$dataStartRow}:K{$lastDataRow}")
                ->getFont()
                ->setName('Arial')
                ->setSize(10);

            $sheetKuesioner->getStyle("A{$dataStartRow}:K{$lastDataRow}")
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $sheetKuesioner->getStyle("A{$dataStartRow}:J{$lastDataRow}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // (Opsional) Lebar kolom nilai biar enak dilihat (lebih stabil daripada AutoSize untuk PDF)
            foreach (range('B', 'J') as $c) {
                $sheetKuesioner->getColumnDimension($c)->setWidth(10);
            }
        } else {
            // Kalau $skmData kosong
            $sheetKuesioner->mergeCells("A{$currentRow}:J{$currentRow}");
            $sheetKuesioner->setCellValue("A{$currentRow}", "Tidak ada data SKM untuk ditampilkan.");
            $sheetKuesioner->getStyle("A{$currentRow}:J{$currentRow}")
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // ====== Ringkasan: SNilai & /Unsur (B-J merge 2 baris, isi SUM) ======
        $snilaiRow   = $lastDataRow + 1;
        $perUnsurRow = $snilaiRow + 1;

        // Label di kolom A tetap 2 baris
        $sheetKuesioner->setCellValue("A{$snilaiRow}", "SNilai");
        $sheetKuesioner->setCellValue("A{$perUnsurRow}", "/Unsur");

        // Merge B-J vertical 2 baris + isi SUM
        foreach (range('B', 'J') as $c) {
            $sheetKuesioner->mergeCells("{$c}{$snilaiRow}:{$c}{$perUnsurRow}");
            $sheetKuesioner->setCellValue("{$c}{$snilaiRow}", "=SUM({$c}{$dataStartRow}:{$c}{$lastDataRow})");
        }

        // Styling
        $sheetKuesioner->getStyle("A{$snilaiRow}:J{$perUnsurRow}")
            ->getFont()->setName('Arial')->setSize(10);

        $sheetKuesioner->getStyle("A{$snilaiRow}:J{$perUnsurRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
            ->setWrapText(false);

        $sheetKuesioner->getStyle("A{$snilaiRow}:J{$perUnsurRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // A (SNilai & /Unsur) rata kiri
        $sheetKuesioner->getStyle("A{$snilaiRow}:A{$perUnsurRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Hilangkan garis di antara SNilai dan /Unsur (khusus kolom A)
        $sheetKuesioner->getStyle("A{$snilaiRow}")
            ->getBorders()
            ->getBottom()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);

        $sheetKuesioner->getStyle("A{$perUnsurRow}")
            ->getBorders()
            ->getTop()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);

        // ====== Ringkasan: NRR & /Pertanyaan (B-J merge 2 baris, isi AVG versi SUM/COUNT) ======
        $nrrRow        = $perUnsurRow + 1;
        $perTanyaRow   = $nrrRow + 1;

        // Label kolom A
        $sheetKuesioner->setCellValue("A{$nrrRow}", "NRR");
        $sheetKuesioner->setCellValue("A{$perTanyaRow}", "/Pertanyaan");

        // B-J merge 2 baris + rumus =SUM(range)/COUNT(range)
        foreach (range('B', 'J') as $c) {
            $sheetKuesioner->mergeCells("{$c}{$nrrRow}:{$c}{$perTanyaRow}");
            $sheetKuesioner->setCellValue(
                "{$c}{$nrrRow}",
                "=(SUM({$c}{$dataStartRow}:{$c}{$lastDataRow}))/COUNT({$c}{$dataStartRow}:{$c}{$lastDataRow})"
            );
        }

        // Styling
        $sheetKuesioner->getStyle("A{$nrrRow}:J{$perTanyaRow}")
            ->getFont()->setName('Arial')->setSize(10);

        $sheetKuesioner->getStyle("A{$nrrRow}:J{$perTanyaRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
            ->setWrapText(false);

        $sheetKuesioner->getStyle("A{$nrrRow}:J{$perTanyaRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Kolom A rata kiri
        $sheetKuesioner->getStyle("A{$nrrRow}:A{$perTanyaRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Hilangkan garis di antara "NRR" dan "/Pertanyaan" di kolom A
        $sheetKuesioner->getStyle("A{$nrrRow}")
            ->getBorders()->getBottom()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);

        $sheetKuesioner->getStyle("A{$perTanyaRow}")
            ->getBorders()->getTop()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);

        // Format 3 angka di belakang koma untuk NRR (B-J)
        $sheetKuesioner->getStyle("B{$nrrRow}:J{$perTanyaRow}")
            ->getNumberFormat()
            ->setFormatCode('0.000');

        // ====== Ringkasan: NRR tertbg / pertanyaan (3 baris di kolom A) ======
        $nrrTertRow    = $perTanyaRow + 1;
        $tertbgRow     = $nrrTertRow + 1;
        $pertanyaanRow = $nrrTertRow + 2;

        // Kolom A (3 baris)
        $sheetKuesioner->setCellValue("A{$nrrTertRow}", "NRR");
        $sheetKuesioner->setCellValue("A{$tertbgRow}", "tertbg/");
        $sheetKuesioner->setCellValue("A{$pertanyaanRow}", "pertanyaan");

        // B-J merge 3 baris + rumus NRR/Pertanyaan * 0.111
        foreach (range('B', 'J') as $c) {
            $sheetKuesioner->mergeCells("{$c}{$nrrTertRow}:{$c}{$pertanyaanRow}");
            $sheetKuesioner->setCellValue(
                "{$c}{$nrrTertRow}",
                "=(SUM({$c}{$dataStartRow}:{$c}{$lastDataRow})/COUNT({$c}{$dataStartRow}:{$c}{$lastDataRow}))*0.111"
            );
        }

        // Styling
        $sheetKuesioner->getStyle("A{$nrrTertRow}:J{$pertanyaanRow}")
            ->getFont()->setName('Arial')->setSize(10);

        $sheetKuesioner->getStyle("A{$nrrTertRow}:J{$pertanyaanRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
            ->setWrapText(false);

        $sheetKuesioner->getStyle("A{$nrrTertRow}:J{$pertanyaanRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Kolom A rata kiri
        $sheetKuesioner->getStyle("A{$nrrTertRow}:A{$pertanyaanRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Hilangkan garis di antara 3 baris kolom A (biar nyatu)
        $sheetKuesioner->getStyle("A{$nrrTertRow}")
            ->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
        $sheetKuesioner->getStyle("A{$tertbgRow}")
            ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
        $sheetKuesioner->getStyle("A{$tertbgRow}")
            ->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
        $sheetKuesioner->getStyle("A{$pertanyaanRow}")
            ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);

        // Format 3 angka di belakang koma
        $sheetKuesioner->getStyle("B{$nrrTertRow}:J{$pertanyaanRow}")
            ->getNumberFormat()
            ->setFormatCode('0.000');

        // ====== Kategori Per Unsur (berdasarkan nilai NRR/Pertanyaan di baris $nrrRow) ======
        // kalau kamu pakai versi 3 baris "NRR tertbg/ pertanyaan", biasanya terakhirnya $pertanyaanRow
        $kategoriRow = $pertanyaanRow + 1; // kalau versi 2 baris, ganti: $kategoriRow = $tertanyaTertRow + 1;

        $sheetKuesioner->setCellValue("A{$kategoriRow}", "Kategori Per Unsur");

        foreach (range('B', 'J') as $c) {
            $ref = "{$c}{$nrrRow}"; // ini setara dengan B77/C77/... kalau $nrrRow = 77
            $sheetKuesioner->setCellValue(
                "{$c}{$kategoriRow}",
                "=IF({$ref}>=3.53,\"A\",IF({$ref}>=3.06,\"B\",IF({$ref}>=2.6,\"C\",IF({$ref}>=1,\"D\"))))"
            );
        }

        // Styling + border untuk baris kategori
        $sheetKuesioner->getStyle("A{$kategoriRow}")
            ->getFont()->setName('Arial')->setSize(10);

        $sheetKuesioner->getStyle("B{$kategoriRow}:J{$kategoriRow}")
            ->getFont()->setName('Arial')->setSize(10)->setBold(true);

        $sheetKuesioner->getStyle("A{$kategoriRow}:J{$kategoriRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $sheetKuesioner->getStyle("A{$kategoriRow}:J{$kategoriRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Kolom A rata kiri
        $sheetKuesioner->getStyle("A{$kategoriRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // ====== Tambahan: tanda *) dan **) di kolom I & J ======
        $noteRow = $kategoriRow + 1;

        $sheetKuesioner->setCellValue("I{$noteRow}", "*)");
        $sheetKuesioner->setCellValue("J{$noteRow}", "**)");
        $sheetKuesioner->getStyle("I{$noteRow}:J{$noteRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Border (opsional biar rapi)
        $sheetKuesioner->getStyle("A{$noteRow}:J{$noteRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);


        // ====== IKM Unit pelayanan ======
        $ikmRow = $noteRow + 1;

        $sheetKuesioner->setCellValue("A{$ikmRow}", "IKM Unit pelayanan");

        // Kolom I = SUM dari NRR tertbg/pertanyaan (B..J) pada baris $nrrTertRow
        $sheetKuesioner->setCellValue("I{$ikmRow}", "=SUM(B{$nrrTertRow}:J{$nrrTertRow})");

        // Kolom J = Kolom I * 25
        $sheetKuesioner->setCellValue("J{$ikmRow}", "=I{$ikmRow}*25");

        // Styling IKM row
        $sheetKuesioner->getStyle("A{$ikmRow}:J{$ikmRow}")
            ->getFont()->setName('Arial')->setSize(10)->setBold(true);

        $sheetKuesioner->getStyle("A{$ikmRow}:J{$ikmRow}")
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $sheetKuesioner->getStyle("A{$ikmRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $sheetKuesioner->getStyle("I{$ikmRow}:J{$ikmRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Border IKM row
        $sheetKuesioner->getStyle("A{$ikmRow}:J{$ikmRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Format angka (kalau mau 3 desimal)
        $sheetKuesioner->getStyle("I{$ikmRow}:J{$ikmRow}")
            ->getNumberFormat()
            ->setFormatCode('0.000');

        // misal baris blok yang kamu mau kasih border minimal:
        $startRow = $noteRow;   // baris yang ada "*)" dan "**)"
        $endRow   = $ikmRow;    // baris "IKM Unit pelayanan"

        // 1) Bersihin semua border dulu di area A..J
        $sheetKuesioner->getStyle("A{$startRow}:J{$endRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_NONE);

        // 2) Garis kanan kolom J (untuk semua baris dalam blok itu)
        $sheetKuesioner->getStyle("J{$startRow}:J{$endRow}")
            ->getBorders()->getRight()
            ->setBorderStyle(Border::BORDER_THIN);

        // 3) Garis bawah dari A sampai J (hanya di baris terakhir)
        $sheetKuesioner->getStyle("A{$endRow}:J{$endRow}")
            ->getBorders()->getBottom()
            ->setBorderStyle(Border::BORDER_THIN);

        // ====== Jarak 1 baris ======
        $ketRow = $ikmRow + 2; // +1 kosong, +2 mulai header keterangan

        // Kolom A: "Keterangan :" (bold, TANPA border)
        $sheetKuesioner->setCellValue("A{$ketRow}", "Keterangan :");
        $sheetKuesioner->getStyle("A{$ketRow}")->getFont()->setBold(true);
        $sheetKuesioner->getStyle("A{$ketRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_NONE);

        // Kolom F: No. (bold + border)
        $sheetKuesioner->setCellValue("F{$ketRow}", "No.");

        // Kolom G-I: Unsur Pelayanan (bold + border + merge)
        $sheetKuesioner->mergeCells("G{$ketRow}:I{$ketRow}");
        $sheetKuesioner->setCellValue("G{$ketRow}", "Unsur Pelayanan");

        // Kolom J: Rata-rata (bold + border)
        $sheetKuesioner->setCellValue("J{$ketRow}", "Rata-rata");

        // Bold untuk F-J
        $sheetKuesioner->getStyle("F{$ketRow}:J{$ketRow}")
            ->getFont()->setBold(true);

        // Border hanya untuk F, G-I, J
        $sheetKuesioner->getStyle("F{$ketRow}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheetKuesioner->getStyle("G{$ketRow}:I{$ketRow}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheetKuesioner->getStyle("J{$ketRow}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Alignment (rapi)
        $sheetKuesioner->getStyle("F{$ketRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheetKuesioner->getStyle("J{$ketRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // ==================== ISI KETERANGAN (KIRI A-E) ====================
        $ketStartRow = $ketRow + 1;

        $keteranganKiri = [
            ['U1 s.d. U14', '=', 'Unsur-Unsur pelayanan'],
            ['NRR',         '=', 'Nilai rata-rata'],
            ['IKM',         '=', 'Indeks Kepuasan Masyarakat'],
            ['*)',          '=', 'Jumlah NRR IKM tertimbang'],
            ['**)',         '=', 'Jumlah NRR Tertimbang x 25'],
            ['NRR Per Unsur', '=', 'Jumlah nilai per unsur dibagi'],
            ['',            '',  'Jumlah kuesioner yang terisi'],
            ['NRR tertimbang', '=', 'NRR per unsur x 0,111'],
        ];

        $r = $ketStartRow;
        foreach ($keteranganKiri as $k) {
            $sheetKuesioner->setCellValue("A{$r}", $k[0]);
            $sheetKuesioner->setCellValue("B{$r}", $k[1]);

            $sheetKuesioner->mergeCells("C{$r}:E{$r}");
            $sheetKuesioner->setCellValue("C{$r}", $k[2]);

            // Styling ringan (tanpa border)
            $sheetKuesioner->getStyle("A{$r}")->getFont()->setBold(true);
            $sheetKuesioner->getStyle("B{$r}")
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $sheetKuesioner->getStyle("A{$r}:E{$r}")
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            // pastikan tidak ada border di A-E
            $sheetKuesioner->getStyle("A{$r}:E{$r}")
                ->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);

            $r++;
        }


        // ==================== ISI TABEL UNSUR (KANAN F-J) ====================
        $unsurList = [
            ['U1', 'Kesesuaian Persyaratan',              'B'],
            ['U2', 'Prosedur Pelayanan',                  'C'],
            ['U3', 'Kecepatan Pelayanan',                 'D'],
            ['U4', 'Kesesuaian/ Kewajaran Biaya',         'E'],
            ['U5', 'Kesesuaian Pelayanan',                'F'],
            ['U6', 'Kompetensi Petugas',                  'G'],
            ['U7', 'Perilaku Petugas Pelayanan',          'H'],
            ['U8', 'Penanganan Pengaduan',                'I'],
            ['U9', 'Kualitas Sarana dan Prasarana',       'J'],
        ];

        $ru = $ketStartRow;
        foreach ($unsurList as [$kode, $nama, $refCol]) {
            // Kolom F: No (U1..U9)
            $sheetKuesioner->setCellValue("F{$ru}", $kode);

            // Kolom G-I: Unsur Pelayanan (merge)
            $sheetKuesioner->mergeCells("G{$ru}:I{$ru}");
            $sheetKuesioner->setCellValue("G{$ru}", $nama);

            // Kolom J: Rata-rata (ambil dari baris NRR/Pertanyaan)
            $sheetKuesioner->setCellValue("J{$ru}", "={$refCol}{$nrrRow}");

            // Border untuk F, G-I, J
            $sheetKuesioner->getStyle("F{$ru}")
                ->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $sheetKuesioner->getStyle("G{$ru}:I{$ru}")
                ->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $sheetKuesioner->getStyle("J{$ru}")
                ->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Alignment
            $sheetKuesioner->getStyle("F{$ru}")
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $sheetKuesioner->getStyle("G{$ru}")
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

            $sheetKuesioner->getStyle("J{$ru}")
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $ru++;
        }

        // Format 3 angka di belakang koma untuk kolom J (rata-rata)
        $sheetKuesioner->getStyle("J{$ketStartRow}:J" . ($ru - 1))
            ->getNumberFormat()
            ->setFormatCode('0.000');

        // ====== Jarak 1 baris lalu tampilkan IKM UNIT PELAYANAN ======
        $ikmDisplayRow = ($ru - 1) + 2; // +1 kosong, +2 mulai baris IKM

        // Merge A sampai I
        $sheetKuesioner->mergeCells("A{$ikmDisplayRow}:I{$ikmDisplayRow}");
        $sheetKuesioner->setCellValue("A{$ikmDisplayRow}", "IKM UNIT PELAYANAN :");

        // Nilai di kolom J (ambil dari hasil perhitungan IKM sebelumnya)
        $sheetKuesioner->setCellValue("J{$ikmDisplayRow}", "=J{$ikmRow}");

        // Styling: bold ukuran 14
        $sheetKuesioner->getStyle("A{$ikmDisplayRow}:J{$ikmDisplayRow}")
            ->getFont()
            ->setBold(true)
            ->setSize(14);

        // Alignment (opsional biar rapi)
        $sheetKuesioner->getStyle("A{$ikmDisplayRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $sheetKuesioner->getStyle("J{$ikmDisplayRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Format 2 desimal kalau mau tampil 97.60
        $sheetKuesioner->getStyle("J{$ikmDisplayRow}")
            ->getNumberFormat()
            ->setFormatCode('0.00');

        $rangeIKM = "A{$ikmDisplayRow}:J{$ikmDisplayRow}";

        // 1) Hapus semua border dulu (biar bersih)
        $sheetKuesioner->getStyle($rangeIKM)
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_NONE);

        // 2) Kasih border hanya OUTSIDE (keliling), jadi tidak ada garis di antara I dan J
        $sheetKuesioner->getStyle($rangeIKM)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheetKuesioner->getStyle($rangeIKM)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $sheetKuesioner->getStyle($rangeIKM)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
        $sheetKuesioner->getStyle($rangeIKM)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);

        // 3) Rata tengah text label di merge A-I
        $sheetKuesioner->getStyle("A{$ikmDisplayRow}:I{$ikmDisplayRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // (Opsional) nilai di J juga rata tengah
        $sheetKuesioner->getStyle("J{$ikmDisplayRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $mutuRow = $ikmDisplayRow + 2;

        // Merge E-G dan tulis label
        $sheetKuesioner->mergeCells("E{$mutuRow}:G{$mutuRow}");
        $sheetKuesioner->setCellValue("E{$mutuRow}", "Mutu Pelayanan :");

        // Merge H-I untuk hasil mutu
        $sheetKuesioner->mergeCells("H{$mutuRow}:I{$mutuRow}");
        $sheetKuesioner->setCellValue(
            "H{$mutuRow}",
            "=IF(J{$ikmDisplayRow}>=88.31,\"Sangat Baik\",IF(J{$ikmDisplayRow}>=76.61,\"Baik\",IF(J{$ikmDisplayRow}>=65,\"Kurang Baik\",IF(J{$ikmDisplayRow}>=25,\"Tidak Baik\"))))"
        );

        // Font bold ukuran 14
        $sheetKuesioner->getStyle("E{$mutuRow}:I{$mutuRow}")
            ->getFont()->setBold(true)->setSize(14);

        // Alignment
        $sheetKuesioner->getStyle("E{$mutuRow}:I{$mutuRow}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheetKuesioner->getStyle("E{$mutuRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheetKuesioner->getStyle("H{$mutuRow}:I{$mutuRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ===== Border OUTSIDE saja untuk E-I =====
        $rangeMutu = "E{$mutuRow}:I{$mutuRow}";

        // bersihin dulu semua border biar gak ada garis tengah
        $sheetKuesioner->getStyle($rangeMutu)
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_NONE);

        // kasih border luar (top/bottom/left/right)
        $sheetKuesioner->getStyle($rangeMutu)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheetKuesioner->getStyle($rangeMutu)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $sheetKuesioner->getStyle($rangeMutu)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
        $sheetKuesioner->getStyle($rangeMutu)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);

        // ====== Jarak 2 baris ======
        $infoStart = $mutuRow + 3;   // +2 baris kosong, mulai judul
        $row1 = $infoStart;
        $row2 = $infoStart + 1;
        $row3 = $infoStart + 2;

        // --- Judul (kolom A) ---
        $sheetKuesioner->setCellValue("A{$row1}", "Mutu Pelayanan :");
        $sheetKuesioner->getStyle("A{$row1}")->getFont()->setBold(true);

        // --- Baris A & C ---
        $sheetKuesioner->setCellValue("A{$row2}", "A (Sangat Baik)");
        $sheetKuesioner->mergeCells("D{$row2}:E{$row2}");
        $sheetKuesioner->setCellValue("D{$row2}", ": 88,31 - 100,00");

        $sheetKuesioner->mergeCells("F{$row2}:G{$row2}");
        $sheetKuesioner->setCellValue("F{$row2}", "C (Kurang Baik)");

        $sheetKuesioner->mergeCells("I{$row2}:J{$row2}");
        $sheetKuesioner->setCellValue("I{$row2}", ": 65,00 - 76,60");

        // --- Baris B & D ---
        $sheetKuesioner->setCellValue("A{$row3}", "B (Baik)");
        $sheetKuesioner->mergeCells("D{$row3}:E{$row3}");
        $sheetKuesioner->setCellValue("D{$row3}", ": 76,61 - 88,30");

        $sheetKuesioner->mergeCells("F{$row3}:G{$row3}");
        $sheetKuesioner->setCellValue("F{$row3}", "D (Tidak Baik)");

        $sheetKuesioner->mergeCells("I{$row3}:J{$row3}");
        $sheetKuesioner->setCellValue("I{$row3}", ": 25,00 - 64,99");

        // Bold hanya huruf A/B/C/D (biarkan teks dalam kurung normal)
        $sheetKuesioner->getStyle("A{$row2}:A{$row3}")->getFont()->setBold(true);
        $sheetKuesioner->getStyle("F{$row2}:F{$row3}")->getFont()->setBold(true);

        // Background kuning untuk 2 baris keterangan (A..J)
        $sheetKuesioner->getStyle("A{$row2}:J{$row3}")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF00');

        // Tanpa border (judul & blok kuning)
        $sheetKuesioner->getStyle("A{$row1}:J{$row3}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_NONE);

        // Alignment biar rapi
        $sheetKuesioner->getStyle("A{$row2}:J{$row3}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheetKuesioner->getStyle("A{$row2}:A{$row3}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheetKuesioner->getStyle("D{$row2}:E{$row3}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheetKuesioner->getStyle("F{$row2}:G{$row3}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheetKuesioner->getStyle("I{$row2}:J{$row3}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // ====== Jarak 1 baris ======
        $jawabanRow = $row3 + 2; // +1 kosong, +2 mulai tulisan

        // "% JAWABAN" merge F sampai G (tanpa style khusus)
        $sheetKuesioner->mergeCells("F{$jawabanRow}:G{$jawabanRow}");
        $sheetKuesioner->setCellValue("F{$jawabanRow}", "% JAWABAN");

        // pastikan benar-benar "no style" (tidak bold, tidak border, tidak background)
        $sheetKuesioner->getStyle("F{$jawabanRow}:G{$jawabanRow}")
            ->getFont()->setBold(false);

        $sheetKuesioner->getStyle("F{$jawabanRow}:G{$jawabanRow}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);

        $sheetKuesioner->getStyle("F{$jawabanRow}:G{$jawabanRow}")
            ->getFill()->setFillType(Fill::FILL_NONE);

        // ==================== Tabel % Jawaban ====================
        $persHdrRow = $jawabanRow + 1;

        // Header: U1..U9 di kolom B..J
        $sheetKuesioner->setCellValue("A{$persHdrRow}", ""); // label kolom
        $unsur = ['U1', 'U2', 'U3', 'U4', 'U5', 'U6', 'U7', 'U8', 'U9'];
        $col = 'B';
        foreach ($unsur as $u) {
            $sheetKuesioner->setCellValue("{$col}{$persHdrRow}", $u);
            $col++;
        }

        // Baris label
        $rows = [
            'Tidak Baik',
            'Kurang Baik',
            'Baik',
            'Sangat Baik',
            'kosong',
            'Total Persentase',
        ];

        $startTableRow = $persHdrRow + 1;
        $r = $startTableRow;

        foreach ($rows as $label) {
            $sheetKuesioner->setCellValue("A{$r}", $label);

            foreach (range('B', 'J') as $c) {
                $range = "{$c}{$dataStartRow}:{$c}{$lastDataRow}";

                if ($label === 'Tidak Baik') {
                    $sheetKuesioner->setCellValue("{$c}{$r}", "=COUNTIF({$range},\"=1\")/COUNT({$range})*100");
                } elseif ($label === 'Kurang Baik') {
                    $sheetKuesioner->setCellValue("{$c}{$r}", "=COUNTIF({$range},\"=2\")/COUNT({$range})*100");
                } elseif ($label === 'Baik') {
                    $sheetKuesioner->setCellValue("{$c}{$r}", "=COUNTIF({$range},\"=3\")/COUNT({$range})*100");
                } elseif ($label === 'Sangat Baik') {
                    $sheetKuesioner->setCellValue("{$c}{$r}", "=COUNTIF({$range},\"=4\")/COUNT({$range})*100");
                } elseif ($label === 'kosong') {
                    // sesuai permintaan: COUNTIF(range,"=") / COUNT(range) * 100
                    $sheetKuesioner->setCellValue("{$c}{$r}", "=COUNTIF({$range},\"=\")/COUNT({$range})*100");
                } else { // Total Persentase
                    // sesuai permintaan: SUM(empat kategori saja)
                    $rowTidak  = $startTableRow;
                    $rowKurang = $startTableRow + 1;
                    $rowBaik   = $startTableRow + 2;
                    $rowSangat = $startTableRow + 3;

                    $sheetKuesioner->setCellValue("{$c}{$r}", "=ROUND(SUM({$c}{$rowTidak}:{$c}{$rowSangat}),0)");
                }
            }

            $r++;
        }

        $endTableRow = $r - 1;

        // ===== Styling tabel =====
        $sheetKuesioner->getStyle("A{$persHdrRow}:J{$endTableRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // BORDER: hanya untuk kolom B sampai J (U1..U9)
        $sheetKuesioner->getStyle("B{$persHdrRow}:J{$endTableRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Hapus border kolom A (termasuk header dan semua label)
        $sheetKuesioner->getStyle("A{$persHdrRow}:A{$endTableRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_NONE);
        // 3) Header kolom A (kosong) tanpa border (border B-J tetap ada)
        $sheetKuesioner->getStyle("A{$persHdrRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_NONE);
        // 1) Kolom A rata kiri
        $sheetKuesioner->getStyle("A{$persHdrRow}:A{$endTableRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // 2) Header U1..U9 bold
        $sheetKuesioner->getStyle("B{$persHdrRow}:J{$persHdrRow}")
            ->getFont()->setBold(true);

        // 4) Baris "kosong" bg kuning
        $kosongRow = $startTableRow + 4;
        $sheetKuesioner->getStyle("A{$kosongRow}:J{$kosongRow}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF00');
        $sheetKuesioner->getStyle("B{$startTableRow}:J{$endTableRow}")
            ->getNumberFormat()
            ->setFormatCode('0.00');

        $totalRow = $startTableRow + 5;
        $sheetKuesioner->getStyle("B{$totalRow}:J{$totalRow}")
            ->getNumberFormat()
            ->setFormatCode('0');

        // ====== lewati 1 baris, lalu Total Responden ======
        $totalResRow = $endTableRow + 2; // +1 kosong, +2 isi

        $sheetKuesioner->setCellValue("A{$totalResRow}", "Total Responden");

        // isi B..J = COUNT data pada masing-masing unsur (contoh jadi 64)
        foreach (range('B', 'J') as $c) {
            $sheetKuesioner->setCellValue(
                "{$c}{$totalResRow}",
                "=COUNT({$c}{$dataStartRow}:{$c}{$lastDataRow})"
            );
        }

        // alignment
        $sheetKuesioner->getStyle("A{$totalResRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheetKuesioner->getStyle("B{$totalResRow}:J{$totalResRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // border: hanya B..J (sesuai gaya tabel sebelumnya)
        $sheetKuesioner->getStyle("B{$totalResRow}:J{$totalResRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // kolom A tanpa border
        $sheetKuesioner->getStyle("A{$totalResRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_NONE);

        // format angka bulat
        $sheetKuesioner->getStyle("B{$totalResRow}:J{$totalResRow}")
            ->getNumberFormat()->setFormatCode('0');


        // ==================== Tabel JUMLAH JAWABAN (dari % * Total Responden) ====================

        // lewati 1 baris dari baris "Total Responden" sebelumnya
        $jjTitleRow = $totalResRow + 2; // +1 kosong, +2 mulai judul
        $jjHdrRow   = $jjTitleRow + 1;

        // Judul "JUMLAH JAWABAN" (merge B-J)
        $sheetKuesioner->mergeCells("B{$jjTitleRow}:J{$jjTitleRow}");
        $sheetKuesioner->setCellValue("B{$jjTitleRow}", "JUMLAH JAWABAN");
        $sheetKuesioner->getStyle("B{$jjTitleRow}:J{$jjTitleRow}")
            ->getFont()->setBold(true);
        $sheetKuesioner->getStyle("B{$jjTitleRow}:J{$jjTitleRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Header U1..U9
        $unsur = ['U1', 'U2', 'U3', 'U4', 'U5', 'U6', 'U7', 'U8', 'U9'];
        $col = 'B';
        foreach ($unsur as $u) {
            $sheetKuesioner->setCellValue("{$col}{$jjHdrRow}", $u);
            $col++;
        }
        $sheetKuesioner->getStyle("B{$jjHdrRow}:J{$jjHdrRow}")
            ->getFont()->setBold(true);
        $sheetKuesioner->getStyle("B{$jjHdrRow}:J{$jjHdrRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Mapping baris % (tabel % sebelumnya)
        $rowTidak  = $startTableRow;       // Tidak Baik (%)
        $rowKurang = $startTableRow + 1;   // Kurang Baik (%)
        $rowBaik   = $startTableRow + 2;   // Baik (%)
        $rowSangat = $startTableRow + 3;   // Sangat Baik (%)
        $rowKosong = $startTableRow + 4;   // kosong (%)

        $labels = [
            'Tidak Baik'      => $rowTidak,
            'Kurang Baik'     => $rowKurang,
            'Baik'            => $rowBaik,
            'Sangat Baik'     => $rowSangat,
            'kosong'          => $rowKosong,
            'Total Responden' => 'total',
        ];

        $jjStartRow = $jjHdrRow + 1;
        $r = $jjStartRow;

        foreach ($labels as $label => $pRow) {
            $sheetKuesioner->setCellValue("A{$r}", $label);

            foreach (range('B', 'J') as $c) {
                // contoh: $C$115 (total responden per unsur)
                $absTotalCell = "\${$c}\${$totalResRow}";

                if ($pRow === 'total') {
                    // Total Responden = SUM(jumlah jawaban Tidak..kosong) (baris di tabel jumlah jawaban)
                    $sheetKuesioner->setCellValue(
                        "{$c}{$r}",
                        "=SUM({$c}{$jjStartRow}:{$c}" . ($jjStartRow + 4) . ")"
                    );
                } else {
                    // contoh: ROUND((C108/100)*$C$115,0)
                    $sheetKuesioner->setCellValue(
                        "{$c}{$r}",
                        "={$c}{$pRow}/100*{$absTotalCell}"
                    );
                }
            }

            $r++;
        }

        $jjEndRow = $r - 1;

        // ===== Styling tabel JUMLAH JAWABAN =====

        // alignment
        $sheetKuesioner->getStyle("A{$jjTitleRow}:J{$jjEndRow}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheetKuesioner->getStyle("B{$jjTitleRow}:J{$jjEndRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // kolom A rata kiri dan TANPA border
        $sheetKuesioner->getStyle("A{$jjTitleRow}:A{$jjEndRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheetKuesioner->getStyle("A{$jjTitleRow}:A{$jjEndRow}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);

        // border hanya untuk B-J
        $sheetKuesioner->getStyle("B{$jjTitleRow}:J{$jjEndRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // baris "kosong" kuning (posisi: Tidak(0), Kurang(1), Baik(2), Sangat(3), kosong(4))
        $jjKosongRow = $jjStartRow + 4;
        $sheetKuesioner->getStyle("A{$jjKosongRow}:J{$jjKosongRow}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF00');

        // format angka bulat
        $sheetKuesioner->getStyle("B{$jjStartRow}:J{$jjEndRow}")
            ->getNumberFormat()->setFormatCode('0');

        // Judul "JUMLAH JAWABAN" tanpa bold & tanpa border
        $sheetKuesioner->getStyle("B{$jjTitleRow}:J{$jjTitleRow}")
            ->getFont()->setBold(false);

        $sheetKuesioner->getStyle("B{$jjTitleRow}:J{$jjTitleRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);

        // ====== lewati 2 baris dari tabel JUMLAH JAWABAN ======
        $rankHdrRow      = $jjEndRow + 3;      // +1 kosong, +2 kosong, +3 header
        $rankFirstDataRow = $rankHdrRow + 1;   // baris pertama data (sebaris dengan "PERINGKAT")

        // Kolom D: URUTAN (tanpa bold, tanpa border)
        $sheetKuesioner->setCellValue("D{$rankHdrRow}", "URUTAN");
        $sheetKuesioner->getStyle("D{$rankHdrRow}")
            ->getFont()->setBold(false);
        $sheetKuesioner->getStyle("D{$rankHdrRow}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);

        // Kolom D: PERINGKAT (tanpa bold, tanpa border)
        $sheetKuesioner->setCellValue("D{$rankFirstDataRow}", "PERINGKAT");
        $sheetKuesioner->getStyle("D{$rankFirstDataRow}")
            ->getFont()->setBold(false);
        $sheetKuesioner->getStyle("D{$rankFirstDataRow}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);

        // Header tabel (F-J)
        $sheetKuesioner->setCellValue("F{$rankHdrRow}", "No.");
        $sheetKuesioner->mergeCells("G{$rankHdrRow}:I{$rankHdrRow}");
        $sheetKuesioner->setCellValue("G{$rankHdrRow}", "Unsur Pelayanan");
        $sheetKuesioner->setCellValue("J{$rankHdrRow}", "Rata-rata");

        // Style header tabel (bold boleh untuk tabel)
        $sheetKuesioner->getStyle("F{$rankHdrRow}:J{$rankHdrRow}")
            ->getFont()->setBold(true);

        $sheetKuesioner->getStyle("F{$rankHdrRow}:J{$rankHdrRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Border header tabel
        $sheetKuesioner->getStyle("F{$rankHdrRow}:J{$rankHdrRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);


        // ====== Hitung rata-rata per unsur dari $skmData lalu urutkan desc ======
        $unsurMap = [
            ['kode' => 'U1', 'nama' => 'Kesesuaian Persyaratan',               'field' => 'syarat_pengurusan_pelayanan'],
            ['kode' => 'U2', 'nama' => 'Prosedur Pelayanan',                   'field' => 'sistem_mekanisme_dan_prosedur_pelayanan'],
            ['kode' => 'U3', 'nama' => 'Kecepatan Pelayanan',                  'field' => 'waktu_penyelesaian_pelayanan'],
            ['kode' => 'U4', 'nama' => 'Kesesuaian/ Kewajaran Biaya',          'field' => 'kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan'],
            ['kode' => 'U5', 'nama' => 'Kesesuaian Pelayanan',                 'field' => 'kesesuaian_hasil_pelayanan'],
            ['kode' => 'U6', 'nama' => 'Kompetensi Petugas',                   'field' => 'kemampuan_petugas_dalam_memberikan_pelayanan'],
            ['kode' => 'U7', 'nama' => 'Perilaku Petugas Pelayanan',           'field' => 'kesopanan_dan_keramahan_petugas'],
            ['kode' => 'U8', 'nama' => 'Penanganan Pengaduan',                 'field' => 'penanganan_pengaduan_saran_dan_masukan'],
            ['kode' => 'U9', 'nama' => 'Kualitas Sarana dan Prasarana',        'field' => 'sarana_dan_prasarana_penunjang_pelayanan'],
        ];

        $rankData = [];
        foreach ($unsurMap as $u) {
            $sum = 0;
            $cnt = 0;

            foreach ($skmData as $row) {
                $v = $row->{$u['field']} ?? null;
                if ($v !== null && $v !== '') {
                    $sum += (float)$v;
                    $cnt++;
                }
            }

            $avg = $cnt > 0 ? ($sum / $cnt) : 0;

            $rankData[] = [
                'kode' => $u['kode'],
                'nama' => $u['nama'],
                'avg'  => $avg,
            ];
        }

        // sort desc by avg
        usort($rankData, fn($a, $b) => $b['avg'] <=> $a['avg']);


        // ====== Tulis data ranking (mulai baris $rankFirstDataRow) ======
        $rankRow = $rankFirstDataRow;
        foreach ($rankData as $item) {
            // F: kode Ux
            $sheetKuesioner->setCellValue("F{$rankRow}", $item['kode']);

            // G-I: nama unsur (merge)
            $sheetKuesioner->mergeCells("G{$rankRow}:I{$rankRow}");
            $sheetKuesioner->setCellValue("G{$rankRow}", $item['nama']);

            // J: rata-rata
            $sheetKuesioner->setCellValue("J{$rankRow}", $item['avg']);

            // border hanya untuk tabel F-J
            $sheetKuesioner->getStyle("F{$rankRow}:J{$rankRow}")
                ->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            // alignment
            $sheetKuesioner->getStyle("F{$rankRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheetKuesioner->getStyle("G{$rankRow}:I{$rankRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheetKuesioner->getStyle("J{$rankRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $rankRow++;
        }

        // Format 3 desimal kolom J
        $sheetKuesioner->getStyle("J{$rankFirstDataRow}:J" . ($rankRow - 1))
            ->getNumberFormat()
            ->setFormatCode('0.000');



        switch ($type) {
            case 'excel':
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                return response()->streamDownload(function () use ($writer) {
                    $writer->save('php://output');
                }, 'hasil_skm.xlsx');

            case 'pdf':
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);

                // supaya semua sheet (Sampling + Kuesioner) ikut ke PDF
                $writer->writeAllSheets();

                return response()->streamDownload(function () use ($writer) {
                    $writer->save('php://output');
                }, 'hasil_skm.pdf');


            case 'print':
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Html($spreadsheet);

                // supaya semua sheet ikut ke HTML/Print
                $writer->writeAllSheets();

                $html = $writer->generateHTMLAll();

                // Tambahkan CSS agar garis table muncul
                $css = <<<CSS
<style>
    table { border-collapse: collapse; width: 100%; }
    table, th, td { border: 1px solid #000; }
    th, td { padding: 6px; text-align: left; }
    @media print {
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
    }
</style>
CSS;

                $html = $css . $html;

                $html .= <<<HTML
<script>
    window.onload = function() { window.print(); };
    window.onafterprint = function() { window.history.back(); };
</script>
HTML;

                return response($html)->header('Content-Type', 'text/html');


            default:
                abort(400, 'Parameter "type" tidak valid. Pilih: excel, pdf, atau print');
        }
    }
}
