<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormPermohonan;
use App\Models\Permohonan;
use App\Models\Skm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StatistikController extends Controller
{
    public function index(Request $request)
    {
        $warna2Chart = ['#007BFF', '#79D6F2', '#9bc6bf', '#02187b', '#164de5'];
        // 1. Definisi Warna Induk
        $serviceColors = [
            'UKBI'                                => '#02187b',
            'Perpustakaan'                        => '#0246a7',
            'Kunjungan Edukasi'                   => '#164de5',
            'Fasilitasi Bantuan Teknis'           => '#1e89ef', // Warna Induk Teknis
            'Praktik Kerja Lapangan (Pemagangan)' => '#5cdffb',
            'Sarana dan Prasarana'                => '#77b5ff',
            'Penerjemahan'                        => '#9bc6bf',
        ];

        $defaultColor = '#5d89b0';

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
        $query = Permohonan::whereBetween('created_at', [$startDate, $endDate]);

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

        // --- Hitung Data Untuk Kartu Statistik ---
        $totalPermohonan = $query->count();
        $topLayananKpi = (clone $query)
            // FILTER: Hanya hitung jika namanya ada di Induk ATAU Sub
            ->whereIn('layanan_dibutuhkan', $semuaLayananValid)
            // Lanjut logika hitung-hitungan
            ->select('layanan_dibutuhkan', DB::raw('count(*) as total'))
            ->groupBy('layanan_dibutuhkan')
            ->orderBy('total', 'desc')
            ->value('layanan_dibutuhkan');
        $permohonanDiajukan = (clone $query)->where('status', 'Diajukan')->count();
        $permohonanDiproses = (clone $query)->where('status', 'Diproses')->count();
        $permohonanSelesai = (clone $query)->where('status', 'Selesai')->count();
        $permohonanDitolak = (clone $query)->where('status', 'Ditolak')->count();


        // Distribusi Status (Donut Chart)
        $distribusiStatus = (clone $query)
            ->select('status', DB::raw('count(*) as total'))
            ->whereIn('status', ['Selesai', 'Diproses', 'Ditolak', 'Diajukan'])
            ->groupBy('status')
            ->get();


        // chart Tren Harian (Area Chart)
        $trenHarian = (clone $query)
            ->select(DB::raw("DATE(created_at) as tanggal"), DB::raw('count(*) as total'))
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        $labelsTren = $trenHarian->pluck('tanggal');
        $dataTren = $trenHarian->pluck('total');

        // TAMBAHAN: Buat penanda mana Induk yang punya anak, mana yang jomblo/tunggal

        $defaultInduk = 'Fasilitasi Bantuan Teknis';
        $defaultSub   = 'Lainnya';

        // Chart berdasarkan kategori
        // ... (Bagian atas code tetap sama) ...
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

        // ... (Lanjut ke query $topLayanan) ...
        // Pastikan query Top 5 menggunakan daftar nama yang valid tadi
        $topLayanan = (clone $query)
            ->whereIn('layanan_dibutuhkan', $allValidNames) // Filter data valid
            ->select('layanan_dibutuhkan', DB::raw('count(*) as total'))
            ->groupBy('layanan_dibutuhkan')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Warna Default (Jika tidak ada di list manapun)
        $topLayananDefaultColor = '#5d89b0';
        // chart pendidikan
        $masterPendidikan = FormPermohonan::where('category', 'Pendidikan Terakhir')
            ->orderBy('id', 'asc')
            ->get();

        // Ambil Raw Data
        $rawPendidikan = (clone $query)
            ->select('pendidikan', DB::raw('count(*) as total'))
            ->whereNotNull('pendidikan')
            ->where('pendidikan', '!=', '')
            ->groupBy('pendidikan')
            ->get();

        // Hitung Total Semua Data Pendidikan yg Masuk
        $grandTotalPendidikan = $rawPendidikan->sum('total');

        // Gabungkan data
        $layananPerPendidikan = $masterPendidikan->map(function ($item) use ($rawPendidikan) {
            $masterName = strtoupper(trim($item->name));

            $totalFound = $rawPendidikan->filter(function ($transaksi) use ($masterName) {
                return strtoupper(trim($transaksi->pendidikan)) === $masterName;
            })->sum('total');

            return (object) [
                'pendidikan_kategori' => $item->name,
                'total'               => $totalFound
            ];
        });

        // Hitung "Lainnya"
        $matchedTotalPendidikan = $layananPerPendidikan->sum('total');
        $sisaPendidikan = $grandTotalPendidikan - $matchedTotalPendidikan;

        if ($sisaPendidikan > 0) {
            $layananPerPendidikan->push((object)[
                'pendidikan_kategori' => 'Lainnya', // Atau 'Tidak Terdaftar'
                'total'               => $sisaPendidikan
            ]);
        }

        // Sorting pendidikan
        $layananPerPendidikan = $layananPerPendidikan->sortByDesc('total')->values();

        // chart PROFESI ---
        $masterProfesi = FormPermohonan::where('category', 'Profesi')
            ->get();

        $rawProfesi = (clone $query)
            ->select('profesi', DB::raw('count(*) as total'))
            ->whereNotNull('profesi')
            ->where('profesi', '!=', '')
            ->groupBy('profesi')
            ->get();

        // Total Profesi
        $grandTotalProfesi = $rawProfesi->sum('total');

        $distribusiProfesi = $masterProfesi->map(function ($item) use ($rawProfesi) {
            $masterName = strtoupper(trim($item->name));

            $totalFound = $rawProfesi->filter(function ($transaksi) use ($masterName) {
                return strtoupper(trim($transaksi->profesi)) === $masterName;
            })->sum('total');

            return (object) [
                'profesi_kategori' => $item->name,
                'total'            => $totalFound
            ];
        });

        // Hitung profesi "Lainnya"
        $matchedTotalProfesi = $distribusiProfesi->sum('total');
        $sisaProfesi = $grandTotalProfesi - $matchedTotalProfesi;

        if ($sisaProfesi > 0) {
            $distribusiProfesi->push((object)[
                'profesi_kategori' => 'Lainnya',
                'total'            => $sisaProfesi
            ]);
        }

        $distribusiProfesi = $distribusiProfesi->sortByDesc('total')->values();

        // Chart JENIS KELAMIN
        $masterGender = FormPermohonan::where('category', 'Jenis Kelamin')
            ->orderBy('id', 'asc')
            ->get();

        $rawGender = (clone $query)
            ->select('jenis_kelamin', DB::raw('count(*) as total'))
            ->whereNotNull('jenis_kelamin')
            ->where('jenis_kelamin', '!=', '')
            ->groupBy('jenis_kelamin')
            ->get();

        // Gabungkan per gender
        $distribusiGender = $masterGender->map(function ($item) use ($rawGender) {
            $masterName = strtoupper(trim($item->name));

            // Cari data di transaksi yang namanya COCOK
            $totalFound = $rawGender->filter(function ($transaksi) use ($masterName) {
                return strtoupper(trim($transaksi->jenis_kelamin)) === $masterName;
            })->sum('total');

            return (object) [
                'label' => $item->name, // Nama asli dari Master (Laki-laki / Perempuan)
                'total' => $totalFound
            ];
        });

        return view('admin.statistik.index', [
            // Data untuk Kartu
            'totalPermohonan' => $totalPermohonan,
            'permohonanDiajukan' => $permohonanDiajukan,
            'permohonanDiproses' => $permohonanDiproses,
            'permohonanSelesai' => $permohonanSelesai,
            'permohonanDitolak' => $permohonanDitolak,
            'topLayananKpi' => $topLayananKpi,

            // Data untuk Grafik
            'distribusiStatus' => $distribusiStatus,
            'labelsTren' => $labelsTren,
            'dataTren' => $dataTren,
            'topLayanan' => $topLayanan->sortBy('total'), // Di-sort agar bar chart dari bawah ke atas
            'distribusiProfesi' => $distribusiProfesi,
            'distribusiGender' => $distribusiGender,
            'layananPerPendidikan' => $layananPerPendidikan,

            // Passing warna
            'serviceColors'    => $serviceColors,
            'subServiceColors' => $colorMap,
            'topLayananColorMap'     => $colorMap,
            'topLayananDefaultColor' => $topLayananDefaultColor,
            'pendidikanColors'     => $pendidikanColors,
            'profesiColors'     => $profesiColors,
            'warna2Chart' => $warna2Chart,

            // Data untuk Filter Tanggal
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'mainSeries' => $mainSeries,
            'drilldownSeries' => $finalDrilldownSeries,
        ]);
    }

    /**
     * Helper untuk mendapatkan rentang tanggal berdasarkan filter
     */
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
            $oldestData = Permohonan::oldest('created_at')->first();

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
        $type = (string) $request->input('type', '');

        if (!in_array($type, ['excel', 'pdf', 'print'], true)) {
            return back()->with('error', 'Pilih tipe export yang valid.');
        }

        $range = $this->getDateRange($request);
        $startDate = $range['start'];
        $endDate   = $range['end'];

        // QUERY permohonan export
        // Langsung pakai variabel di atas
        $base = Permohonan::query()->whereBetween('created_at', [$startDate, $endDate]);
        $total = (int) (clone $base)->count();

        // GROUPING
        $jkRows   = (clone $base)->selectRaw('jenis_kelamin as label, COUNT(*) as total')->groupBy('jenis_kelamin')->get();
        $pendRows = (clone $base)->selectRaw('pendidikan as label, COUNT(*) as total')->groupBy('pendidikan')->get();
        $profRows = (clone $base)->selectRaw('profesi as label, COUNT(*) as total')->groupBy('profesi')->get();
        $layRows  = (clone $base)->selectRaw('layanan_dibutuhkan as label, COUNT(*) as total')->groupBy('layanan_dibutuhkan')->get();

        // ORDER KATEGORI
        $genderOrder = ['Laki-Laki', 'Perempuan'];
        $eduOrder    = ['SD ke Bawah', 'SLTP/SMP', 'SLTA/SMA', 'D/III', 'S1', 'S2', 'S3', 'Lainnya'];
        $jobOrder    = ['PNS', 'PPPK', 'TNI/POLRI', 'Swasta', 'Wirausaha', 'Mahasiswa', 'Siswa', 'Lainnya'];

        // GROUP layanan
        $groupTeknis = [
            'Juri',
            'Pendampingan Kebahasaan dan Kesastraan',
            'Juri, Narasumber, dan/atau  Pendampingan Kebahasaan dan Kesastraan',
            'Penyuntingan',
            'Fasilitasi Ke-BIPA-an',
            'Permintaan data Kebahasaan dan Kesastraan',
            'Narasumber',
            'Penyuluhan',
            'Saksi Ahli (Bahasa dan Hukum)',
            'Literasi',
        ];

        $groupTerjemahan = [
            'Penerjemahan Lisan (Juru Bahasa)',
            'Penerjemahan Tulis'
        ];

        $groupExcluded = [
            'UKBI',
            'Perpustakaan',
            'Praktik Kerja Lapangan (Pemagangan)',
            'Sarana dan Prasarana',
            'Kunjungan Edukasi'
        ];

        $setTeknis     = array_flip($groupTeknis);
        $setTerjemahan = array_flip($groupTerjemahan);
        $setExcluded   = array_flip($groupExcluded);

        $serviceOrder = [
            'Layanan Fasilitasi Bantuan Teknis',
            'Layanan Penerjemahan',
            'Layanan Pemagangan',
            'Layanan Sarana Prasarana',
            'UKBI',
            'Layanan Kunjungan Edukasi',
            'Layanan Perpustakaan',
            'Lainnya',
        ];

        // tabel jenis kelamin
        $genderBucket = array_fill_keys($genderOrder, 0);
        foreach ($jkRows as $r) {
            $v = strtolower((string)($r->label ?? ''));
            $cnt = (int)$r->total;

            if (str_contains($v, 'laki')) $genderBucket['Laki-Laki'] += $cnt;
            elseif (str_contains($v, 'perempuan')) $genderBucket['Perempuan'] += $cnt;
        }

        $buildDistribution = function ($rows, array $order) {
            // Siapkan bucket dengan nilai awal 0 untuk semua kategori
            $bucket = array_fill_keys($order, 0);

            foreach ($rows as $r) {
                // Ambil label mentah, ubah ke huruf kecil, dan hapus spasi
                $rawLabel = strtolower(trim((string)($r->label ?? '')));
                $cnt      = (int)$r->total;

                // --- MULAI LOGIKA MAPPING ---
                // Tentukan mau masuk ke keranjang mana data ini?
                $targetKey = 'Lainnya'; // Default

                if ($rawLabel == 'sd') {
                    $targetKey = 'SD ke Bawah';
                } elseif ($rawLabel == 'smp') {
                    $targetKey = 'SLTP/SMP';
                } elseif ($rawLabel == 'sma') {
                    $targetKey = 'SLTA/SMA';
                } elseif (in_array($rawLabel, ['d1-d3', 'd3', 'd-3'])) { // Bisa handle beberapa variasi
                    $targetKey = 'D/III';
                } elseif ($rawLabel == 's1') {
                    $targetKey = 'S1';
                } elseif ($rawLabel == 's2') {
                    $targetKey = 'S2';
                } elseif ($rawLabel == 's3') {
                    $targetKey = 'S3';
                }
                // --- SELESAI LOGIKA MAPPING ---

                // Masukkan jumlah ke bucket yang sesuai
                if (array_key_exists($targetKey, $bucket)) {
                    $bucket[$targetKey] += $cnt;
                } else {
                    // Safety net jika ada kategori aneh
                    $bucket['Lainnya'] += $cnt;
                }
            }

            return $bucket;
        };

        $eduBucket = $buildDistribution($pendRows, $eduOrder);
        // Fungsi Build Distribution Khusus Profesi
        $buildJobDistribution = function ($rows, array $order) {
            // Siapkan wadah (bucket) dengan nilai awal 0
            $bucket = array_fill_keys($order, 0);

            foreach ($rows as $r) {
                // Bersihkan data: ubah ke huruf kecil & hapus spasi
                $rawLabel = strtolower(trim((string)($r->label ?? '')));
                $cnt      = (int)$r->total;

                // --- LOGIKA MAPPING (Sesuai Request SQL Anda) ---
                $targetKey = 'Lainnya'; // Default jika tidak ada yang cocok

                if ($rawLabel == 'pns') {
                    $targetKey = 'PNS';
                } elseif ($rawLabel == 'pppk') {
                    $targetKey = 'PPPK';
                }
                // Handle: tni/polri, tni, polri
                elseif (in_array($rawLabel, ['tni/polri', 'tni', 'polri'])) {
                    $targetKey = 'TNI/POLRI';
                }
                // Handle: swasta, pegawai swasta
                elseif (in_array($rawLabel, ['swasta', 'pegawai swasta'])) {
                    $targetKey = 'Swasta'; // Sesuaikan dengan $jobOrder (Huruf S besar)
                } elseif ($rawLabel == 'wirausaha') {
                    $targetKey = 'Wirausaha';
                }
                // Handle: mahasiswa, mhs
                elseif (in_array($rawLabel, ['mahasiswa', 'mhs'])) {
                    $targetKey = 'Mahasiswa';
                }
                // Handle: siswa, pelajar
                elseif (in_array($rawLabel, ['siswa', 'pelajar'])) {
                    $targetKey = 'Siswa';
                }
                // --- SELESAI MAPPING ---

                // Masukkan ke bucket
                if (array_key_exists($targetKey, $bucket)) {
                    $bucket[$targetKey] += $cnt;
                } else {
                    $bucket['Lainnya'] += $cnt;
                }
            }

            return $bucket;
        };

        // Eksekusi
        // Pastikan variabel $profRows adalah hasil query grouping profesi
        $jobBucket = $buildJobDistribution($profRows, $jobOrder);

        $serviceBucket = array_fill_keys($serviceOrder, 0);
        foreach ($layRows as $r) {
            $raw = (string)($r->label ?? '');
            $cnt = (int)$r->total;

            if ($raw === '') {
                $serviceBucket['Lainnya'] += $cnt;
                continue;
            }

            if (isset($setExcluded[$raw])) {
                if ($raw === 'Perpustakaan') $serviceBucket['Layanan Perpustakaan'] += $cnt;
                elseif ($raw === 'Praktik Kerja Lapangan (Pemagangan)') $serviceBucket['Layanan Pemagangan'] += $cnt;
                elseif ($raw === 'Sarana dan Prasarana') $serviceBucket['Layanan Sarana Prasarana'] += $cnt;
                elseif ($raw === 'Kunjungan Edukasi') $serviceBucket['Layanan Kunjungan Edukasi'] += $cnt;
                elseif ($raw === 'UKBI') $serviceBucket['UKBI'] += $cnt;
                else $serviceBucket['Lainnya'] += $cnt;
                continue;
            }

            if (isset($setTerjemahan[$raw])) {
                $serviceBucket['Layanan Penerjemahan'] += $cnt;
                continue;
            }

            if (isset($setTeknis[$raw])) {
                $serviceBucket['Layanan Fasilitasi Bantuan Teknis'] += $cnt;
                continue;
            }

            $serviceBucket['Lainnya'] += $cnt;
        }

        $data = [
            'total' => $total,
            'gender' => $genderBucket,
            'education' => $eduBucket,
            'job' => $jobBucket,
            'service' => $serviceBucket,
            'periode' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
        ];

        // =========================
        // EXCEL / PDF / PRINT (PhpSpreadsheet)
        // =========================
        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle('Statistik');

        $row = 1;

        // ===== HEADER BARU =====
        $sheet->setCellValue("A{$row}", "Sampel Minimal");
        $sheet->setCellValue("B{$row}", 380);
        $sheet->setCellValue("C{$row}", 38410);
        $sheet->getStyle("C{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF953734');
        $sheet->getStyle("C{$row}")->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        $sheet->setCellValue("A{$row}", "Populasi");
        $sheet->setCellValue("B{$row}", 40000);
        $sheet->setCellValue("C{$row}", 100.95775);
        $sheet->getStyle("C{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF953734');
        $sheet->getStyle("C{$row}")->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        $sheet->setCellValue("A{$row}", "Jumlah Sampel Sesungguhnya");
        $sheet->setCellValue("B{$row}", $total);
        $sheet->getStyle("B{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFB8CCE4');
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $row += 2;

        // Helper border tabel
        $applyTableBorders = function ($sheet, $fromRow, $toRow) {
            $sheet->getStyle("A{$fromRow}:C{$toRow}")
                ->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
        };

        // ===== SECTION WRITER =====
        $writeSection = function ($title, array $bucket) use (&$sheet, &$row, $total, $applyTableBorders) {

            // Judul section: bold hitam
            $sheet->setCellValue("A{$row}", $title);
            $sheet->mergeCells("A{$row}:C{$row}");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;

            // Header tabel: tanpa "Persen"
            $sheet->setCellValue("A{$row}", "Kategori");
            $sheet->setCellValue("B{$row}", "Jumlah");
            $sheet->setCellValue("C{$row}", ""); // header kosong
            $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);

            $headerRow = $row;
            $row++;

            foreach ($bucket as $k => $v) {
                $v = (int)$v;
                $pct = $total > 0 ? ($v / $total) : 0; // numeric 0-1

                $sheet->setCellValue("A{$row}", $k);
                $sheet->setCellValue("B{$row}", $v);
                $sheet->setCellValue("C{$row}", $pct);

                // persen numeric (tidak text)
                $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('0%');
                $row++;
            }

            $endRow = $row - 1;
            if ($endRow >= $headerRow) {
                $applyTableBorders($sheet, $headerRow, $endRow);
            }

            $row += 1;
        };

        $writeSection("Jenis Kelamin", $genderBucket);
        $writeSection("Pendidikan", $eduBucket);
        $writeSection("Pekerjaan", $jobBucket);
        $writeSection("Jenis Layanan", $serviceBucket);

        // Lebar kolom
        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(14);

        // Kolom Jumlah (B) rata tengah dari atas sampai bawah
        $lastRow = $row;
        $sheet->getStyle("B1:B{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Kolom C rata tengah (opsional biar rapi)
        $sheet->getStyle("C1:C{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Nama file
        $filenameBase = "statistik-permohonan_" . $startDate->format('Ymd') . "_" . $endDate->format('Ymd');

        switch ($type) {
            case 'excel':
                $writer = new Xlsx($ss);
                $tmp = storage_path("app/{$filenameBase}.xlsx");
                $writer->save($tmp);
                return response()->download($tmp, "{$filenameBase}.xlsx")->deleteFileAfterSend(true);

            case 'pdf':
                $writer = new Mpdf($ss);
                $tmp = storage_path("app/{$filenameBase}.pdf");
                $writer->save($tmp);
                return response()->download($tmp, "{$filenameBase}.pdf")->deleteFileAfterSend(true);

            case 'print':
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Html($ss);
                $writer->setUseInlineCss(true);   // penting biar warna bg kebawa
                $writer->writeAllSheets();        // aman walau 1 sheet

                $html = $writer->generateHtmlAll();

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
