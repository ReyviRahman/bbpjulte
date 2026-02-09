<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan; // Menggunakan model Pengaduan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StatistikPengaduanController extends Controller
{
    /**
     * Menampilkan halaman statistik untuk data pengaduan.
     */
    public function index(Request $request)
    {
        $chartColors = [
            '#02187b',
            '#164de5',
            '#0246a7',
            '#1e89ef',
            '#5cdffb',
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

        // Query Data
        $query = Pengaduan::whereBetween('created_at', [$startDate, $endDate]);

        // Hitung Data Untuk Kartu Statistik ---
        $totalPengaduan = $query->count();

        // Hitung jumlah pengaduan berdasarkan statusnya
        $pengaduanSelesai = (clone $query)->where('status', 'Selesai')->count();
        $pengaduanDiproses = (clone $query)->where('status', 'Diproses')->count();
        $pengaduanDiajukan = (clone $query)->where('status', 'Diajukan')->count();

        // Siapkan Data untuk Grafik ---

        // Distribusi Status (Donut Chart)
        $distribusiStatus = (clone $query)->select('status', DB::raw('count(*) as total'))->groupBy('status')->get();

        // Tren Harian (Area Chart)
        $trenHarian = (clone $query)->select(DB::raw("DATE(created_at) as tanggal"), DB::raw('count(*) as total'))->groupBy('tanggal')->orderBy('tanggal', 'asc')->get();
        $labelsTren = $trenHarian->pluck('tanggal');
        $dataTren = $trenHarian->pluck('total');

        // Distribusi Profesi (Pie Chart)
        $distribusiProfesi = (clone $query)
            ->selectRaw("
        CASE
            WHEN LOWER(TRIM(profesi)) IN ('pns') THEN 'PNS'
            WHEN LOWER(TRIM(profesi)) IN ('tni/polri','tni','polri') THEN 'TNI/POLRI'
            WHEN LOWER(TRIM(profesi)) IN ('mahasiswa','mhs') THEN 'MAHASISWA'
            WHEN LOWER(TRIM(profesi)) IN ('pppk') THEN 'PPPK'
            WHEN LOWER(TRIM(profesi)) IN ('swasta','pegawai swasta') THEN 'SWASTA'
            WHEN LOWER(TRIM(profesi)) IN ('siswa','pelajar') THEN 'SISWA'
            ELSE 'LAINNYA'
        END AS profesi_kategori,
        COUNT(*) AS total
    ")
            ->whereNotNull('profesi')
            ->where('profesi', '!=', '')
            ->groupBy('profesi_kategori')
            ->orderBy('total', 'desc')
            ->get();

        $distribusiInstansi = (clone $query)
            ->select('instansi', DB::raw('count(*) as total'))
            ->whereNotNull('instansi')->where('instansi', '!=', '')
            ->groupBy('instansi')
            ->orderBy('total', 'desc')
            ->get();

        // Kirim data ke view 
        return view('admin.statistik-pengaduan.index', [
            // Data untuk Kartu
            'totalPengaduan' => $totalPengaduan,
            'pengaduanSelesai' => $pengaduanSelesai,
            'pengaduanDiproses' => $pengaduanDiproses,
            'pengaduanDiajukan' => $pengaduanDiajukan,

            // Data untuk Grafik
            'distribusiStatus' => $distribusiStatus,
            'labelsTren' => $labelsTren,
            'dataTren' => $dataTren,
            'distribusiProfesi' => $distribusiProfesi,
            'distribusiInstansi' => $distribusiInstansi,

            // Data untuk Filter Tanggal
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'chartColors' => $chartColors,
            'profesiColors' => $profesiColors,
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
            $oldestData = Pengaduan::oldest('created_at')->first();

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

        // =========================
        // DATE RANGE: jika kosong => 30 hari terakhir
        // =========================
        $range = $this->getDateRange($request);
        $startDate = $range['start'];
        $endDate   = $range['end'];

        // QUERY data
        // =========================
        // Langsung pakai variabel di atas
        $base = Pengaduan::query()->whereBetween('created_at', [$startDate, $endDate]);
        $total = (int) (clone $base)->count();

        // =========================
        // GROUPING (sesuai model Pengaduan)
        // =========================
        $statusRows = (clone $base)
            ->selectRaw('status as label, COUNT(*) as total')
            ->groupBy('status')
            ->get();

        $profRows = (clone $base)
            ->selectRaw('profesi as label, COUNT(*) as total')
            ->groupBy('profesi')
            ->get();

        $instRows = (clone $base)
            ->selectRaw('instansi as label, COUNT(*) as total')
            ->groupBy('instansi')
            ->get();

        $aduanRows = (clone $base)
            ->selectRaw('isi_aduan as label, COUNT(*) as total')
            ->groupBy('isi_aduan')
            ->get();

        // =========================
        // ORDER KATEGORI
        // =========================
        $statusOrder = array_values(array_unique(array_merge(Pengaduan::STATUSES, ['Lainnya'])));

        // (opsional) order profesi (kalau data profesi Anda memang pakai list ini)
        $jobOrder = ['PNS', 'PPPK', 'TNI/POLRI', 'Swasta', 'Wirausaha', 'Mahasiswa', 'Siswa', 'Lainnya'];

        // =========================
        // BUILD DISTRIBUSI (helper)
        // =========================
        $normalizeLabel = function ($label) {
            $label = trim((string)($label ?? ''));
            return $label !== '' ? $label : 'Lainnya';
        };

        $buildDistributionWithOrder = function ($rows, array $order) use ($normalizeLabel) {
            $bucket = array_fill_keys($order, 0);

            foreach ($rows as $r) {
                $label = $normalizeLabel($r->label ?? '');
                $cnt   = (int) $r->total;

                if (array_key_exists($label, $bucket)) {
                    $bucket[$label] += $cnt;
                } else {
                    $bucket['Lainnya'] = ($bucket['Lainnya'] ?? 0) + $cnt;
                }
            }

            // pastikan ada Lainnya
            if (!array_key_exists('Lainnya', $bucket)) {
                $bucket['Lainnya'] = 0;
            }

            return $bucket;
        };

        $buildTopNWithOther = function ($rows, int $topN = 10) use ($normalizeLabel) {
            $tmp = [];
            foreach ($rows as $r) {
                $label = $normalizeLabel($r->label ?? '');
                $tmp[$label] = ($tmp[$label] ?? 0) + (int)$r->total;
            }

            arsort($tmp); // terbesar dulu

            $bucket = [];
            $i = 0;
            $other = 0;

            foreach ($tmp as $label => $cnt) {
                if ($label === 'Lainnya') {
                    $other += $cnt;
                    continue;
                }

                if ($i < $topN) {
                    $bucket[$label] = $cnt;
                    $i++;
                } else {
                    $other += $cnt;
                }
            }

            $bucket['Lainnya'] = ($bucket['Lainnya'] ?? 0) + $other;

            return $bucket;
        };

        $statusBucket = $buildDistributionWithOrder($statusRows, $statusOrder);
        $profBucket   = $buildDistributionWithOrder($profRows, $jobOrder);
        $instBucket   = $buildTopNWithOther($instRows, 10);
        $aduanBucket  = $buildTopNWithOther($aduanRows, 10);

        // =========================
        // EXCEL / PDF / PRINT (PhpSpreadsheet)
        // =========================
        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle('Statistik');

        $row = 1;

        // ===== HEADER =====
        $sheet->setCellValue("A{$row}", "Periode");
        $sheet->setCellValue("B{$row}", $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'));
        $sheet->mergeCells("B{$row}:C{$row}");
        $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A{$row}", "Total Pengaduan");
        $sheet->setCellValue("B{$row}", $total);
        $sheet->mergeCells("B{$row}:C{$row}");
        $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
        $row += 2;

        // Helper border tabel
        $applyTableBorders = function ($sheet, $fromRow, $toRow) {
            $sheet->getStyle("A{$fromRow}:C{$toRow}")
                ->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
        };

        // ===== SECTION WRITER =====
        $writeSection = function ($title, array $bucket) use (&$sheet, &$row, $total, $applyTableBorders) {
            $sheet->setCellValue("A{$row}", $title);
            $sheet->mergeCells("A{$row}:C{$row}");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;

            $sheet->setCellValue("A{$row}", "Kategori");
            $sheet->setCellValue("B{$row}", "Jumlah");
            $sheet->setCellValue("C{$row}", ""); // header kosong
            $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);

            $headerRow = $row;
            $row++;

            foreach ($bucket as $k => $v) {
                $v = (int)$v;
                $pct = $total > 0 ? ($v / $total) : 0;

                $sheet->setCellValue("A{$row}", $k);
                $sheet->setCellValue("B{$row}", $v);
                $sheet->setCellValue("C{$row}", $pct);
                $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('0%');
                $row++;
            }

            $endRow = $row - 1;
            if ($endRow >= $headerRow) {
                $applyTableBorders($sheet, $headerRow, $endRow);
            }

            $row += 1;
        };

        $writeSection("Status Pengaduan", $statusBucket);
        $writeSection("Profesi", $profBucket);
        $writeSection("Instansi (Top 10)", $instBucket);
        $writeSection("Kategori/Isi Aduan (Top 10)", $aduanBucket);

        // Lebar kolom
        $sheet->getColumnDimension('A')->setWidth(45);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(14);

        // Alignment kolom
        $lastRow = $row;
        $sheet->getStyle("B1:B{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle("C1:C{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Nama file
        $filenameBase = "statistik-pengaduan_" . $startDate->format('Ymd') . "_" . $endDate->format('Ymd');

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
                $writer->setUseInlineCss(true);
                $writer->writeAllSheets();

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
