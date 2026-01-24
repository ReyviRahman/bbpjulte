<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // <-- INI YANG PERLU DITAMBAHKAN
use App\Models\Permohonan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PermohonanController extends Controller
{
    private function normalizePendidikan(?string $val): ?string
    {
        $raw = trim((string) $val);

        // kalau benar-benar kosong, simpan null (kalau mau kosong tetap string '', ganti return null -> return '')
        if ($raw === '') return null;

        $upper = mb_strtoupper($raw, 'UTF-8');
        $code  = str_replace([' ', '.', '-', '/'], '', $upper);
        // SD / SD SEDERAJAT
        if ($code === 'SD' || $code === 'SDSEDERAJAT') return 'SD';
        // SMP / SMP SEDERAJAT
        if ($code === 'SMP' || $code === 'SMPSEDERAJAT') return 'SMP';
        // SMA / SMA SEDERAJAT
        if ($code === 'SMA' || $code === 'SMASEDERAJAT') return 'SMA';
        // D1/D2/D3 dan variasi -> D1-D3
        if (in_array($code, ['D1', 'D2', 'D3', 'D1D3'], true)) return 'D1-D3';

        // S1/S2/S3 (S-1 kebaca S1 karena '-' dihapus)
        if ($code === 'S1') return 'S1';
        if ($code === 'S2') return 'S2';
        if ($code === 'S3') return 'S3';

        // selain itu: simpan apa adanya
        return $raw;
    }


    public function etlPermohonanSdTujuhMei()
    {
        try {
            // ================================
            // E = EXTRACT
            // Ambil data dari Google Sheets
            // ================================
            $spreadsheetId = '1GVUBMkQVlR7ZysIWAT2ZHgrJzqGdMPn8kbGEAaFrpbk';
            $exists = DB::table('etl_log')
                ->where('spreadsheet_id', $spreadsheetId)
                ->exists();

            if ($exists) {
                return 'Silakan gunakan data yang lain karena data ini telah dilakukan proses ETL';
            }
            $sheetName = 'Form Responses 1';

            $client = new \Google\Client();
            $client->setAuthConfig(storage_path('app/google/credentials.json'));
            $client->addScope(\Google\Service\Sheets::SPREADSHEETS_READONLY);

            $service = new \Google\Service\Sheets($client);
            $rows = $service->spreadsheets_values->get($spreadsheetId, $sheetName)->getValues();

            if (empty($rows) || count($rows) < 2) {
                return "Tidak ada data.";
            }
            unset($rows[0]);
            foreach ($rows as $row) {
                $row = array_pad($row, 9, null); // pastikan jumlah kolom cukup

                // ================================
                // T = TRANSFORM
                // 1. Konversi timestamp dari spreadsheet
                // 2. Atur layanan dibutuhkan
                // 3. Generate no_registrasi
                // ================================
                $timestamp = null;
                $carbonTs = null;

                if (!empty($row[0])) {
                    // Format spreadsheet: d/m/Y H:i:s
                    $carbonTs = Carbon::createFromFormat('d/m/Y H:i:s', $row[0]);
                    if ($carbonTs) {
                        $timestamp = $carbonTs->format('Y-m-d H:i:s');
                    }
                }

                // kalau timestamp kosong → skip
                if (empty($timestamp)) {
                    continue;
                }

                // Generate no_registrasi
                $no_registrasi = 'ULT-' . $carbonTs->format('YmdHis') . rand(10, 99);

                // ================================
                // L = LOAD
                // Masukkan data ke database
                // ================================
                DB::table('permohonans')->updateOrInsert(
                    [
                        'created_at' => $timestamp,
                        'nama_lengkap' => trim($row[1])
                    ], // unique key
                    [
                        'created_at'              => $timestamp,
                        'updated_at'              => $timestamp,
                        'no_registrasi'           => $no_registrasi,
                        'nama_lengkap'            => trim($row[1]),
                        'jenis_kelamin'           => null,
                        'pendidikan'              => null,
                        'profesi'                 => null,
                        'instansi'                => trim($row[2]),
                        'email'                   => strtolower(trim($row[3])),
                        'nomor_ponsel'            => trim($row[4]),
                        'layanan_dibutuhkan'      => trim($row[5]),
                        'isi_permohonan'          => $row[6],
                        'path_surat_permohonan'   => $row[7],
                        'path_berkas_permohonan'  => $row[8],
                        'status'                  => 'Selesai',
                    ]
                );
            }
            DB::table('etl_log')->insert([
                'spreadsheet_id' => $spreadsheetId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return "ETL selesai.";
        } catch (\Throwable $e) {

            // Log lengkap tetap disimpan
            Log::error('ETL ERROR', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'trace'   => $e->getTraceAsString(),
            ]);

            $rawMessage = $e->getMessage();
            $friendlyMessage = 'Terjadi kesalahan saat proses ETL.';

            // ✅ Ambil HANYA message dari JSON Google API jika ada
            if (str_contains($rawMessage, '{')) {
                $jsonPart = substr($rawMessage, strpos($rawMessage, '{'));
                $decoded = json_decode($jsonPart, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    if (isset($decoded['error']['message'])) {
                        $friendlyMessage = $decoded['error']['message'];
                    } elseif (isset($decoded['message'])) {
                        $friendlyMessage = $decoded['message'];
                    }
                }
            } else {
                $friendlyMessage = $rawMessage;
            }

            // ✅ TRANSLATE ERROR KE BAHASA INDONESIA
            $translations = [
                'Requested entity was not found.' => 'Data tidak ditemukan. Pastikan ID Spreadsheet dan nama Sheet sudah benar.',
                'Not Found' => 'Data tidak ditemukan.',
                'Invalid credentials' => 'Kredensial Google tidak valid.',
                'The caller does not have permission' => 'Tidak memiliki izin akses ke Google Sheet.',
                'Permission denied' => 'Akses ditolak.',
                'Invalid argument' => 'Data yang dikirim tidak valid.',
                'Unauthenticated' => 'Autentikasi gagal.',
                'Quota exceeded' => 'Kuota akses Google API telah habis.',
            ];

            if (isset($translations[$friendlyMessage])) {
                $friendlyMessage = $translations[$friendlyMessage];
            }

            // ✅ KIRIM KE BLADE DALAM BAHASA INDONESIA
            return redirect()->back()->with(
                'error',
                'ETL gagal: ' . $friendlyMessage
            );
        }
    }

    public function etl()
    {
        try {
            // ================================
            // E = EXTRACT
            // Ambil data dari Google Sheets
            // ================================

            $spreadsheetId = '1kmsD8XQL01YD3cW_tEdLtfEZXscRDJZiBwwHq1YO5xk';
            $exists = DB::table('etl_log')
                ->where('spreadsheet_id', $spreadsheetId)
                ->exists();

            if ($exists) {
                return 'Sudah pernah ETL, skip';
            }

            $sheetName = 'Form Responses 1';

            $client = new \Google\Client();
            $client->setAuthConfig(storage_path('app/google/credentials.json'));
            $client->addScope(\Google\Service\Sheets::SPREADSHEETS_READONLY);

            $service = new \Google\Service\Sheets($client);

            $rows = $service->spreadsheets_values->get($spreadsheetId, $sheetName)->getValues();
            if (empty($rows) || count($rows) < 2) {
                return "Tidak ada data.";
            }

            unset($rows[0]); // buang header

            foreach ($rows as $row) {

                // pastikan jumlah kolom cukup
                $row = array_pad($row, 14, null);
                // ================================
                // T = TRANSFORM
                // 1. Konversi timestamp dari spreadsheet
                // 2. Atur layanan dibutuhkan
                // 3. Generate no_registrasi
                // ================================
                $timestamp = null;
                $carbonTs = null;

                if (!empty($row[0])) {
                    // Format spreadsheet: d/m/Y H:i:s
                    $carbonTs = Carbon::createFromFormat('d/m/Y H:i:s', $row[0]);
                    if ($carbonTs) {
                        $timestamp = $carbonTs->format('Y-m-d H:i:s');
                    }
                }

                // kalau timestamp kosong → skip
                if (empty($timestamp)) {
                    continue;
                }

                // Atur layanan dibutuhkan sesuai kondisi
                $layanan_dibutuhkan = $row[8]; // default dari kolom 8
                if ($row[8] === 'Fasilitasi Bantuan Teknis') {
                    $layanan_dibutuhkan = $row[10]; // ambil dari kolom 10
                } elseif ($row[8] === 'Penerjemahan') {
                    $layanan_dibutuhkan = ucwords($row[9]); // ambil dari kolom 9
                }

                // Generate no_registrasi
                $no_registrasi = 'ULT-' . $carbonTs->format('YmdHis') . rand(10, 99);

                // ================================
                // L = LOAD
                // Masukkan data ke database
                // ================================
                DB::table('permohonans')->updateOrInsert(
                    [
                        'created_at' => $timestamp,
                        'nama_lengkap' => trim($row[1])
                    ], // unique key
                    [
                        'created_at'              => $timestamp,
                        'updated_at'              => $timestamp,
                        'no_registrasi'           => $no_registrasi,
                        'nama_lengkap'            => trim($row[1]),
                        'jenis_kelamin'           => $row[2],
                        'pendidikan'              => $this->normalizePendidikan($row[3]),
                        'profesi'                 => $row[4],
                        'instansi'                => trim($row[5]),
                        'email'                   => strtolower(trim($row[6])),
                        'nomor_ponsel'            => trim($row[7]),
                        'layanan_dibutuhkan'      => $layanan_dibutuhkan,
                        'isi_permohonan'          => $row[11],
                        'path_surat_permohonan'   => $row[12],
                        'path_berkas_permohonan'  => $row[13],
                        'status'                  => 'Selesai',
                    ]
                );
            }

            DB::table('etl_log')->insert([
                'spreadsheet_id' => $spreadsheetId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return "ETL selesai.";
        } catch (\Throwable $e) {

            // Log lengkap tetap disimpan
            Log::error('ETL ERROR', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'trace'   => $e->getTraceAsString(),
            ]);

            $rawMessage = $e->getMessage();
            $friendlyMessage = 'Terjadi kesalahan saat proses ETL.';

            // ✅ Ambil HANYA message dari JSON Google API jika ada
            if (str_contains($rawMessage, '{')) {
                $jsonPart = substr($rawMessage, strpos($rawMessage, '{'));
                $decoded = json_decode($jsonPart, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    if (isset($decoded['error']['message'])) {
                        $friendlyMessage = $decoded['error']['message'];
                    } elseif (isset($decoded['message'])) {
                        $friendlyMessage = $decoded['message'];
                    }
                }
            } else {
                $friendlyMessage = $rawMessage;
            }

            // ✅ TRANSLATE ERROR KE BAHASA INDONESIA
            $translations = [
                'Requested entity was not found.' => 'Data tidak ditemukan. Pastikan ID Spreadsheet dan nama Sheet sudah benar.',
                'Not Found' => 'Data tidak ditemukan.',
                'Invalid credentials' => 'Kredensial Google tidak valid.',
                'The caller does not have permission' => 'Tidak memiliki izin akses ke Google Sheet.',
                'Permission denied' => 'Akses ditolak.',
                'Invalid argument' => 'Data yang dikirim tidak valid.',
                'Unauthenticated' => 'Autentikasi gagal.',
                'Quota exceeded' => 'Kuota akses Google API telah habis.',
            ];

            if (isset($translations[$friendlyMessage])) {
                $friendlyMessage = $translations[$friendlyMessage];
            }

            // ✅ KIRIM KE BLADE DALAM BAHASA INDONESIA
            return redirect()->back()->with(
                'error',
                'ETL gagal: ' . $friendlyMessage
            );
        }
    }

    private function getFilteredQuery(Request $request)
    {
        $query = Permohonan::query();
        $year = $request->input('year', date('Y'));

        // 1. LOGIKA FILTER TANGGAL
        if ($request->filled('date_filter')) {
            $filter = $request->date_filter;
            if ($filter == 'today') {
                $query->whereDate('created_at', Carbon::today());
            } elseif ($filter == 'last_7_days') {
                $query->where('created_at', '>=', Carbon::today()->subDays(6));
            } elseif ($filter == 'last_month') {
                $query->where('created_at', '>=', Carbon::today()->subDays(29));
            } elseif ($filter == 'last_year') {
                // 1 Tahun Terakhir (365 hari ke belakang)
                $query->where('created_at', '>=', Carbon::today()->subYear());
            } elseif ($filter == 'custom') {
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($request->start_date)->startOfDay(),
                        Carbon::parse($request->end_date)->endOfDay()
                    ]);
                }
            }
            // --- LOGIKA TRIWULAN ---
            elseif (\Illuminate\Support\Str::startsWith($filter, 'triwulan_')) {
                $triwulan = explode('_', $filter)[1]; // Ambil angka 1, 2, 3, atau 4

                // Hitung bulan awal: TW1=1, TW2=4, TW3=7, TW4=10
                $startMonth = ($triwulan - 1) * 3 + 1;

                $startDate = Carbon::createFromDate($year, $startMonth, 1)->startOfDay();
                $endDate = $startDate->copy()->addMonths(2)->endOfMonth()->endOfDay();

                $query->whereBetween('created_at', [$startDate, $endDate]);
            } elseif ($filter == 'all_triwulan') {
                $query->whereYear('created_at', $year); // Sepanjang tahun ini
            }
            // --- LOGIKA SEMESTER ---
            elseif (\Illuminate\Support\Str::startsWith($filter, 'semester_')) {
                $semester = explode('_', $filter)[1]; // 1 atau 2

                if ($semester == 1) {
                    // Jan - Jun
                    $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
                    $endDate = Carbon::createFromDate($year, 6, 30)->endOfDay();
                } else {
                    // Jul - Dec
                    $startDate = Carbon::createFromDate($year, 7, 1)->startOfDay();
                    $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();
                }
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } elseif ($filter == 'all_semester') {
                $query->whereYear('created_at', $year);
            }
        }

        // 2. LOGIKA FILTER STATUS
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // 3. LOGIKA PENCARIAN
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_lengkap', 'like', $searchTerm)
                    ->orWhere('instansi', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm)
                    ->orWhere('layanan_dibutuhkan', 'like', $searchTerm)
                    ->orWhere('isi_permohonan', 'like', $searchTerm);
            });
        }

        // 4. LOGIKA SORTIR
        $sortableColumns = ['id', 'nama_lengkap', 'instansi', 'layanan_dibutuhkan', 'created_at', 'status'];
        $sortBy = $request->input('sort', 'created_at'); // Default sortir berdasarkan tanggal
        $direction = $request->input('direction', 'desc');

        if (in_array($sortBy, $sortableColumns)) {
            $query->orderBy($sortBy, $direction);
        } else {
            $query->latest();
        }

        return $query;
    }

    /**
     * Menampilkan dasbor admin dengan data permohonan yang bisa dicari dan disortir.
     */
    public function index(Request $request)
    {
        $this->etlPermohonanSdTujuhMei();
        $this->etl();
        // Memulai query builder, bukan langsung mengambil data

        $query = $this->getFilteredQuery($request);
        $permohonan = $query->paginate(15)->withQueryString();
        return view('admin.permohonan.index', [
            'semua_permohonan' => $permohonan
        ]);
    }

    public function exportExcel(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $data = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 1. SET HEADER
        // Struktur kolom digeser karena ada penambahan No. Registrasi di B
        $headers = [
            'A' => 'No.',
            'B' => 'No. Registrasi',   // Kolom Baru
            'C' => 'Tanggal',          // Geser ke C
            'D' => 'Nama Pemohon',     // Geser ke D
            'E' => 'Instansi',         // Geser ke E
            'F' => 'Email & No. Ponsel', // Geser ke F
            'G' => 'Layanan',          // Geser ke G
            'H' => 'Isi Permohonan',   // Geser ke H
            'I' => 'Surat Permohonan', // Geser ke I
            'J' => 'Berkas Lampiran',  // Geser ke J
            'K' => 'Status'            // Geser ke K
        ];

        // Konfigurasi Lebar Kolom
        $columnSettings = [
            'A' => ['auto' => true],
            'B' => ['width' => 25], // Lebar untuk No. Registrasi
            'C' => ['auto' => true],
            'D' => ['width' => 25],
            'E' => ['width' => 30],
            'F' => ['width' => 35],
            'G' => ['width' => 30],
            'H' => ['width' => 50],
            'I' => ['auto' => true],
            'J' => ['auto' => true],
            'K' => ['auto' => true],
        ];

        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col . '1', $text);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col . '1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            if (isset($columnSettings[$col]['auto'])) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            } else {
                $sheet->getColumnDimension($col)->setAutoSize(false);
                $sheet->getColumnDimension($col)->setWidth($columnSettings[$col]['width']);
                $sheet->getStyle($col)->getAlignment()->setWrapText(true);
            }
        }

        // 2. ISI DATA
        $row = 2;
        foreach ($data as $index => $item) {
            // A: No Urut
            $sheet->setCellValue('A' . $row, $index + 1);

            // B: No Registrasi (Kolom Baru)
            $sheet->setCellValue('B' . $row, $item->no_registrasi ?? '-');

            // C: Tanggal (Format: 29 Dec 2025, 00:14)
            $sheet->setCellValue('C' . $row, $item->created_at->format('d M Y, H:i'));

            // D: Nama
            $sheet->setCellValue('D' . $row, $item->nama_lengkap);

            // E: Instansi
            $sheet->setCellValue('E' . $row, $item->instansi);

            // F: Kontak
            $contactInfo = $item->email . "\n" . $item->nomor_ponsel;
            $sheet->setCellValue('F' . $row, $contactInfo);

            // G: Layanan
            $sheet->setCellValue('G' . $row, $item->layanan_dibutuhkan);

            // H: Isi
            $sheet->setCellValue('H' . $row, $item->isi_permohonan);

            // --- KOLOM I: SURAT PERMOHONAN ---
            if (!empty($item->path_surat_permohonan)) {
                $urlSurat = Str::startsWith($item->path_surat_permohonan, ['http://', 'https://'])
                    ? $item->path_surat_permohonan
                    : Storage::url($item->path_surat_permohonan);

                if (!Str::startsWith($urlSurat, ['http://', 'https://'])) {
                    $urlSurat = asset($urlSurat);
                }

                $sheet->setCellValue('I' . $row, 'Buka Surat');
                $sheet->getCell('I' . $row)->getHyperlink()->setUrl($urlSurat);

                $sheet->getStyle('I' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE));
                $sheet->getStyle('I' . $row)->getFont()->setUnderline(true);
            } else {
                $sheet->setCellValue('I' . $row, '-');
            }
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // --- KOLOM J: BERKAS LAMPIRAN ---
            if (!empty($item->path_berkas_permohonan)) {
                $urlBerkas = Str::startsWith($item->path_berkas_permohonan, ['http://', 'https://'])
                    ? $item->path_berkas_permohonan
                    : Storage::url($item->path_berkas_permohonan);

                if (!Str::startsWith($urlBerkas, ['http://', 'https://'])) {
                    $urlBerkas = asset($urlBerkas);
                }

                $sheet->setCellValue('J' . $row, 'Buka Berkas');
                $sheet->getCell('J' . $row)->getHyperlink()->setUrl($urlBerkas);

                $sheet->getStyle('J' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE));
                $sheet->getStyle('J' . $row)->getFont()->setUnderline(true);
            } else {
                $sheet->setCellValue('J' . $row, '-');
            }
            $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // --- KOLOM K: STATUS ---
            $sheet->setCellValue('K' . $row, $item->status);

            // Align Top (Update Range A ke K)
            $sheet->getStyle('A' . $row . ':K' . $row)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

            $row++;
        }

        // 3. DOWNLOAD
        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $filename = 'Laporan-Permohonan-' . date('d-m-Y-His') . '.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    // ==================================================================
    // 4. EXPORT PDF
    // ==================================================================
    public function exportPdf(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $data = $query->get();

        $pdf = Pdf::loadView('admin.permohonan.print_view', ['data' => $data])
            ->setPaper('a4', 'landscape'); // Landscape agar muat banyak kolom

        return $pdf->download('Laporan-Permohonan-' . date('d-m-Y') . '.pdf');
    }

    // ==================================================================
    // 5. PRINT VIEW (HTML)
    // ==================================================================
    public function printView(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $data = $query->get();

        return view('admin.permohonan.print_view', [
            'data' => $data,
            'is_print_mode' => true
        ]);
    }

    /**
     * Memperbarui status permohonan.
     */
    public function update(Request $request, Permohonan $permohonan)

    {
        // Tambahkan validasi untuk input disposisi baru
        $request->validate([
            'status' => 'required|in:' . implode(',', Permohonan::STATUSES),
            'keterangan' => 'required|string|max:1000',
            'disposisi' => 'nullable|string', // Input disposisi tidak boleh kosong
        ]);


        // --- PERBAIKAN DIMULAI DI SINI ---
        // Siapkan variabel untuk menyimpan keterangan final
        $keteranganLengkap = $request->keterangan;

        // Jika statusnya 'Diproses' dan input disposisi diisi, gabungkan keduanya
        if ($request->status === 'Diproses' && $request->filled('disposisi')) {
            // Gabungkan dengan format yang kita inginkan
            // $keteranganLengkap = "Disposisi ke: " . $request->disposisi . ".\n\n" . $request->keterangan;
        }
        // --- AKHIR PERBAIKAN ---

        // Update status utama di tabel permohonan
        $permohonan->status = $request->status;
        $permohonan->save();

        // Buat catatan baru di tabel riwayat status
        $permohonan->statusHistories()->create([
            'status' => $request->status,
            'keterangan' => $keteranganLengkap, // Sekarang variabel ini sudah benar
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('admin.permohonan.index')->with('success', 'Status permohonan berhasil diperbarui.');
    }
    public function show(Permohonan $permohonan)
    {
        // Kirim data permohonan ke view baru yang akan kita buat
        return view('admin.permohonan.show', ['permohonan' => $permohonan]);
    }

    public function destroy(Permohonan $permohonan)
    {
        // 1. Hapus file-file terkait dari storage untuk menghemat ruang
        if ($permohonan->path_surat_permohonan) {
            Storage::disk('public')->delete($permohonan->path_surat_permohonan);
        }
        if ($permohonan->path_berkas_permohonan) {
            Storage::disk('public')->delete($permohonan->path_berkas_permohonan);
        }

        // 2. Hapus data dari database
        // Karena kita sudah mengatur 'onDelete cascade', riwayat status terkait akan ikut terhapus.
        $permohonan->delete();

        // 3. Kembali ke dasbor dengan pesan sukses
        return redirect()->route('admin.permohonan.index')->with('success', 'Data permohonan berhasil dihapus.');
    }
}
