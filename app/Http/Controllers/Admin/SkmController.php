<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormSkm;
use Illuminate\Http\Request; 
use App\Models\Skm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class SkmController extends Controller
{
    private function skmNilai($val)
    {
        if ($val === null) return null;

        $val = trim($val);
        if ($val === '') return null;

        // kalau ada angka 1-4 di depan (mis: "3. Mudah")
        if (preg_match('/^\s*([1-4])\b/', $val, $m)) {
            return (int) $m[1];
        }

        // normalisasi teks
        $v = strtolower($val);
        $v = preg_replace('/[^a-z\s]/', '', $v); // buang titik, koma, dll
        $v = preg_replace('/\s+/', ' ', trim($v));

        // mapping teks -> angka
        return match ($v) {
            'sangat mudah'       => 4,
            'mudah'              => 3,
            'kurang mudah'       => 2,

            'sangat memuaskan'   => 4,
            'memuaskan'          => 3,

            'sangat cepat'       => 4,
            'cepat'              => 3,

            'sangat sesuai'      => 4,
            'sesuai'             => 3,

            'sangat baik'        => 4,
            'baik'               => 3,

            default              => null, // kalau ada nilai aneh, biar ketahuan
        };
    }

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

    public function etl()
    {
        try {
            // ================================
            // E = EXTRACT
            // Ambil data dari Google Sheets
            // ================================
            $spreadsheetId = '1Lt9-mvvAbUItkX_zmOBKwKwFmam1B9qI1LEKsWeG89k';
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
                $row = array_pad($row, 25, null);
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
                $layanan_didapat = $row[8]; // default dari kolom 8
                if ($row[8] === 'Fasilitasi Bantuan Teknis') {
                    $layanan_didapat = $row[10]; // ambil dari kolom 10
                } elseif ($row[8] === 'Penerjemahan') {
                    $layanan_didapat = ucwords($row[9]); // ambil dari kolom 9
                }



                $kritik_saran = $row[22]; // default dari kolom 22
                if ($row[20] === 'Ya') {
                    $kritik_saran = $row[24]; // ambil dari kolom 24
                }


                // ================================
                // L = LOAD
                // Masukkan data ke database
                // ================================
                DB::table('skm')->updateOrInsert(
                    [
                        'created_at' => $timestamp,
                        'nama_pemohon' => $row[2]
                    ],
                    [
                        'created_at'              => $timestamp,
                        'updated_at'              => $timestamp,
                        'nama_petugas'            => trim($row[1]),
                        'nama_pemohon'            => trim($row[2]),
                        'jenis_kelamin'           => $row[3],
                        'pendidikan'              => $this->normalizePendidikan($row[4]),
                        'profesi'                 => $row[5],
                        'email'                   => strtolower(trim($row[6])),
                        'instansi'                => trim($row[7]),
                        'layanan_didapat'            => $layanan_didapat,
                        'syarat_pengurusan_pelayanan'      => $row[11],
                        'sistem_mekanisme_dan_prosedur_pelayanan'          => $row[12],
                        'waktu_penyelesaian_pelayanan'   => $row[13],
                        'kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan'  => $row[14],
                        'kesesuaian_hasil_pelayanan'                  => $row[15],
                        'kemampuan_petugas_dalam_memberikan_pelayanan'  => $row[16],
                        'kesopanan_dan_keramahan_petugas'  => $row[17],
                        'penanganan_pengaduan_saran_dan_masukan'  => $row[18],
                        'sarana_dan_prasarana_penunjang_pelayanan'  => $row[19],
                        'ada_pungutan'  => $row[20],
                        'akan_informasikan_layanan'  => $row[21],
                        'kritik_saran'  => $kritik_saran,
                        'jenis_pungutan'  => $row[23],
                    ]
                );
            }
            // Simpan ID setelah ETL selesai
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

    public function etl2023()
    {
        try {
            // ================================
            // E = EXTRACT
            // Ambil data dari Google Sheets
            // ================================
            $spreadsheetId = '1MsEMIbDvbUx0cCcq7gBv5t71YaUyNqYaE0RK1eHY4Ds';
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

            $validPetugas = FormSkm::where('category', 'Nama Petugas yang melayani')
                ->pluck('name') // Ambil hanya kolom 'name'
                ->toArray();

            foreach ($rows as $row) {
                // pastikan jumlah kolom cukup
                $row = array_pad($row, 22, null);
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

                // --- LOGIC: NAMA PETUGAS (DARI MODEL) ---
                $rawPetugas = trim($row[1]);
                $finalNamaPetugas = ""; // Default kosong
                // Cek apakah input spreadsheet ada di dalam daftar valid petugas dari DB
                if (in_array($rawPetugas, $validPetugas)) {
                    $finalNamaPetugas = $rawPetugas;
                }

                // --- LOGIC: NAMA PEMOHON ---
                // Default isi dengan nilai row[1]
                $finalNamaPemohon = trim($row[1]);

                // Jika row[20] tidak kosong, ganti isinya 
                if (!empty($row[20]) && trim($row[20]) !== '') {
                    $finalNamaPemohon = trim($row[20]);
                }


                $kritik_saran = $row[16]; // default dari kolom 16
                if ($row[16] === '') {
                    $kritik_saran = $row[18]; // ambil dari kolom 18
                }

                // ================================
                // L = LOAD
                // Masukkan data ke database
                // ================================
                DB::table('skm')->updateOrInsert(
                    [
                        'created_at' => $timestamp,
                        'email' => strtolower(trim($row[3]))
                    ],
                    [
                        'created_at'              => $timestamp,
                        'updated_at'              => $timestamp,
                        'nama_petugas'            => $finalNamaPetugas,
                        'nama_pemohon'            => $finalNamaPemohon,
                        'jenis_kelamin'           => null,
                        'pendidikan'              => null,
                        'profesi'                 => null,
                        'email'                   => strtolower(trim($row[3])),
                        'instansi'                => trim($row[4]),
                        'layanan_didapat'            => trim($row[5]),
                        'syarat_pengurusan_pelayanan' => $this->skmNilai($row[6]),
                        'sistem_mekanisme_dan_prosedur_pelayanan' => $this->skmNilai($row[7]),
                        'waktu_penyelesaian_pelayanan' => $this->skmNilai($row[8]),
                        'kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan' => $this->skmNilai($row[9]),
                        'kesesuaian_hasil_pelayanan' => $this->skmNilai($row[9]),
                        'kemampuan_petugas_dalam_memberikan_pelayanan' => $this->skmNilai($row[10]),
                        'kesopanan_dan_keramahan_petugas' => $this->skmNilai($row[11]),
                        'penanganan_pengaduan_saran_dan_masukan' => $this->skmNilai($row[12]),
                        'sarana_dan_prasarana_penunjang_pelayanan' => $this->skmNilai($row[13]),
                        'ada_pungutan'  => $row[14],
                        'akan_informasikan_layanan'  => $row[15],
                        'kritik_saran'  => $kritik_saran,
                        'jenis_pungutan'  => $row[17],
                    ]
                );
            }
            // Simpan ID setelah ETL selesai
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

    /**
     * Menampilkan daftar semua pengaduan dengan filter, search, dan sort.
     */
    // --- 1. FUNGSI UTAMA (INDEX) ---
    public function index(Request $request)
    {
        $this->etl2023();
        $this->etl();

        // Panggil logic filter yang sudah dipisah
        $query = $this->getFilteredQuery($request);

        // Pagination untuk tampilan Web
        $semua_skm = $query->paginate(15)->withQueryString();

        return view('admin.skm.index', compact('semua_skm'));
    }

    // --- 2. FUNGSI EXPORT EXCEL ---
    public function exportExcel(Request $request)
    {
        // 1. Ambil data menggunakan filter yang SAMA
        $query = $this->getFilteredQuery($request);
        $data = $query->get(); // Ambil semua data (tanpa pagination)

        // 2. Setup Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 3. Define Headers
        $headers = [
            'A' => 'No',
            'B' => 'Tanggal',
            'C' => 'Nama Petugas',
            'D' => 'Nama Pemohon',
            'E' => 'Instansi',
            'F' => 'Kontak', // Email
            'G' => 'Layanan Didapat',
            'H' => 'Kesesuaian Persyaratan',
            'I' => 'Prosedur Pelayanan',
            'J' => 'Kecepatan Pelayanan',
            'K' => 'Kesesuaian/ Kewajaran Biaya',
            'L' => 'Kesesuaian Pelayanan',
            'M' => 'Kompetensi Petugas',
            'N' => 'Perilaku Petugas Pelayanan',
            'O' => 'Penanganan Pengaduan',
            'P' => 'Kualitas Sarana dan Prasarana',
            'Q' => 'Ada Pungutan',
            'R' => 'Jenis Pungutan',
            'S' => 'Akan Informasikan Layanan',
            'T' => 'Kritik Saran',
            'U' => 'Status'
        ];

        // 4. Set Header & Styling
        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col . '1', $text);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col . '1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            // Auto Size Column
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 5. Isi Data
        $row = 2;
        foreach ($data as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->created_at->format('d M Y, H:i'));
            $sheet->setCellValue('C' . $row, $item->nama_petugas);
            $sheet->setCellValue('D' . $row, $item->nama_pemohon);
            $sheet->setCellValue('E' . $row, $item->instansi);
            $sheet->setCellValue('F' . $row, $item->email);
            $sheet->setCellValue('G' . $row, $item->layanan_didapat);

            // Nilai Survey (Angka)
            $sheet->setCellValue('H' . $row, $item->syarat_pengurusan_pelayanan);
            $sheet->setCellValue('I' . $row, $item->sistem_mekanisme_dan_prosedur_pelayanan);
            $sheet->setCellValue('J' . $row, $item->waktu_penyelesaian_pelayanan);
            $sheet->setCellValue('K' . $row, $item->kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan);
            $sheet->setCellValue('L' . $row, $item->kesesuaian_hasil_pelayanan);
            $sheet->setCellValue('M' . $row, $item->kemampuan_petugas_dalam_memberikan_pelayanan);
            $sheet->setCellValue('N' . $row, $item->kesopanan_dan_keramahan_petugas);
            $sheet->setCellValue('O' . $row, $item->penanganan_pengaduan_saran_dan_masukan);
            $sheet->setCellValue('P' . $row, $item->sarana_dan_prasarana_penunjang_pelayanan);

            // Informasi Tambahan
            $sheet->setCellValue('Q' . $row, $item->ada_pungutan);
            $sheet->setCellValue('R' . $row, $item->jenis_pungutan ?? '-');
            $sheet->setCellValue('S' . $row, $item->akan_informasikan_layanan);
            $sheet->setCellValue('T' . $row, $item->kritik_saran);
            $sheet->setCellValue('U' . $row, $item->status);

            // Styling Alignment Center untuk kolom No, Tanggal, dan Nilai-nilai Angka
            $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $row . ':P' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('U' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // 6. Download Output
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan-SKM-' . date('d-m-Y-His') . '.xlsx';

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    public function exportPdf(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $data = $query->get();

        $pdf = Pdf::loadView('admin.skm.print_view', ['data' => $data])
            ->setPaper('legal', 'landscape'); // WAJIB LEGAL AGAR KOLOM LEGA

        return $pdf->download('Laporan-SKM-' . date('d-m-Y') . '.pdf');
    }

    public function printView(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $data = $query->get();

        return view('admin.skm.print_view', [
            'data' => $data,
            'is_print_mode' => true
        ]);
    }

    // --- 3. PRIVATE FILTER FUNCTION (Refactoring Logic) ---
    private function getFilteredQuery(Request $request)
    {
        $query = Skm::query();

        // 1. LOGIKA FILTER TANGGAL
        if ($request->filled('date_filter')) {
            $filter = $request->date_filter;
            switch ($filter) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'last_7_days':
                    $query->where('created_at', '>=', Carbon::now()->subDays(6));
                    break;
                case 'last_month':
                    $query->where('created_at', '>=', Carbon::today()->subDays(29));
                    break;
                case 'custom':
                    if ($request->filled('start_date') && $request->filled('end_date')) {
                        $startDate = Carbon::parse($request->start_date)->startOfDay();
                        $endDate = Carbon::parse($request->end_date)->endOfDay();
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    }
                    break;
            }
        }

        // 2. LOGIKA PENCARIAN
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_pemohon', 'like', $searchTerm)
                    ->orWhere('instansi', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm)
                    ->orWhere('layanan_didapat', 'like', $searchTerm)
                    ->orWhere('nama_petugas', 'like', $searchTerm);
            });
        }

        // 3. LOGIKA FILTER STATUS
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // 4. LOGIKA SORTIR
        $sortableColumns = ['id', 'nama_petugas', 'nama_pemohon', 'instansi', 'created_at', 'syarat_pengurusan_pelayanan', 'sistem_mekanisme_dan_prosedur_pelayanan', 'waktu_penyelesaian_pelayanan', 'kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan', 'kesesuaian_hasil_pelayanan', 'kemampuan_petugas_dalam_memberikan_pelayanan', 'kesopanan_dan_keramahan_petugas', 'penanganan_pengaduan_saran_dan_masukan', 'sarana_dan_prasarana_penunjang_pelayanan'];
        $sortBy = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        if (in_array($sortBy, $sortableColumns)) {
            $query->orderBy($sortBy, $direction);
        } else {
            $query->latest();
        }

        return $query;
    }

    public function update(Request $request, Skm $skm)
    {
        // Tambahkan validasi untuk input disposisi baru
        $request->validate([
            'status' => 'required|in:' . implode(',', Skm::STATUSES),
        ]);
        // Update status utama di tabel skm
        $skm->status = $request->status;
        $skm->save();

        return redirect()->route('admin.skm.index')->with('success', "Status diganti menjadi $request->status.");
    }

    public function show(Skm $skm)
    {
        // Anda perlu membuat view ini: 'admin.skm.show'
        return view('admin.skm.show', compact('skm'));
    }

    /**
     * Menghapus pengaduan.
     */
    public function destroy(Skm $skm)
    {
        $skm->delete();
        return redirect()->route('admin.skm.index')->with('success', 'Data skm berhasil dihapus.');
    }
}
