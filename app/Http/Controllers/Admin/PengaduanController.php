<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str; // Untuk helper string
use Barryvdh\DomPDF\Facade\Pdf;

class PengaduanController extends Controller
{
    /**
     * Menampilkan daftar semua pengaduan dengan filter, search, dan sort.
     */
    // --- 1. FUNGSI UTAMA (INDEX) ---
    public function index(Request $request)
    {
        // Panggil fungsi filter (Refactoring)
        $query = $this->getFilteredQuery($request);

        // Pagination hanya untuk tampilan Web
        $semua_pengaduan = $query->paginate(15)->withQueryString();

        return view('admin.pengaduan.index', compact('semua_pengaduan'));
    }

    // --- 2. FUNGSI EXPORT EXCEL ---
    public function exportExcel(Request $request)
    {
        // 1. Ambil data menggunakan filter yang SAMA dengan index
        $query = $this->getFilteredQuery($request);
        $data = $query->get(); // Ambil semua data (get), bukan paginate

        // 2. Setup Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 3. Header Kolom
        $headers = ['No', 'Tanggal', 'Nama Pengadu', 'Instansi', 'Kontak', 'Isi Aduan', 'Bukti', 'Status'];
        $columnLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        foreach ($headers as $index => $header) {
            $column = $columnLetters[$index];
            $sheet->setCellValue($column . '1', $header);
            
            // Styling Header
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getStyle($column . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($column . '1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            
            // Auto Width (Kecuali kolom F / Isi Aduan biar rapi)
            if ($column !== 'F') {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            } else {
                $sheet->getColumnDimension($column)->setWidth(50); // Lebar fix untuk Isi Aduan
            }
        }

        // 4. Isi Data
        $row = 2;
        foreach ($data as $index => $item) {
            // A: No
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // B: Tanggal (Format: 29 Dec 2025, 21:08)
            $sheet->setCellValue('B' . $row, $item->created_at->format('d M Y, H:i'));

            // C: Nama
            $sheet->setCellValue('C' . $row, $item->nama_lengkap);

            // D: Instansi
            $sheet->setCellValue('D' . $row, $item->instansi);

            // E: Kontak (Gabungan Email & HP)
            $kontak = $item->email . "\n" . $item->nomor_ponsel;
            $sheet->setCellValue('E' . $row, $kontak);
            $sheet->getStyle('E' . $row)->getAlignment()->setWrapText(true); // Agar enter terbaca

            // F: Isi Aduan
            $sheet->setCellValue('F' . $row, $item->isi_aduan);
            $sheet->getStyle('F' . $row)->getAlignment()->setWrapText(true); // Wrap text panjang

            // G: Bukti (Hyperlink)
            if ($item->path_bukti_aduan) {
                // Cek apakah path sudah full URL atau path storage
                $url = Str::startsWith($item->path_bukti_aduan, ['http', 'https']) 
                    ? $item->path_bukti_aduan 
                    : asset('storage/' . $item->path_bukti_aduan);

                $sheet->setCellValue('G' . $row, 'Lihat Bukti');
                $sheet->getCell('G' . $row)->getHyperlink()->setUrl($url);
                
                // Style Link (Biru Underline)
                $sheet->getStyle('G' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE));
                $sheet->getStyle('G' . $row)->getFont()->setUnderline(true);
            } else {
                $sheet->setCellValue('G' . $row, '-');
            }
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // H: Status
            $sheet->setCellValue('H' . $row, $item->status);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Align Top untuk semua cell di baris ini (biar rapi jika ada text panjang)
            $sheet->getStyle('A' . $row . ':H' . $row)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

            $row++;
        }

        // 5. Download Response
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan-Pengaduan-' . date('d-m-Y-His') . '.xlsx';

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

        $pdf = Pdf::loadView('admin.pengaduan.print_view', ['data' => $data])
            ->setPaper('a4', 'landscape'); // Landscape agar muat banyak kolom

        return $pdf->download('Laporan-Pengaduan-' . date('d-m-Y') . '.pdf');
    }

    public function printView(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $data = $query->get();

        return view('admin.pengaduan.print_view', [
            'data' => $data,
            'is_print_mode' => true
        ]);
    }

    // --- 3. PRIVATE HELPER (Logika Filter Dipisah Disini) ---
    private function getFilteredQuery(Request $request)
    {
        $query = Pengaduan::query(); // Pastikan namespace model benar
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

        // 2. LOGIKA PENCARIAN
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_lengkap', 'like', $searchTerm)
                    ->orWhere('instansi', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm)
                    ->orWhere('isi_aduan', 'like', $searchTerm);
            });
        }

        // 3. LOGIKA SORTIR
        $sortableColumns = ['id', 'nama_lengkap', 'instansi', 'created_at'];
        $sortBy = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        if (in_array($sortBy, $sortableColumns)) {
            $query->orderBy($sortBy, $direction);
        } else {
            $query->latest();
        }

        return $query;
    }

    

    public function update(Request $request, Pengaduan $pengaduan)

    {
        // Tambahkan validasi untuk input disposisi baru
        $request->validate([
            'status' => 'required|in:' . implode(',', Pengaduan::STATUSES),
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

        // Update status utama di tabel pengaduan
        $pengaduan->status = $request->status;
        $pengaduan->save();

        // Buat catatan baru di tabel riwayat status
        $pengaduan->statusPengaduan()->create([
            'status' => $request->status,
            'keterangan' => $keteranganLengkap, // Sekarang variabel ini sudah benar
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('admin.pengaduan.index')->with('success', 'Status pengaduan berhasil diperbarui.');
    }

    public function show(Pengaduan $pengaduan)
    {
        // Anda perlu membuat view ini: 'admin.pengaduan.show'
        return view('admin.pengaduan.show', compact('pengaduan'));
    }

    /**
     * Menghapus pengaduan.
     */
    public function destroy(Pengaduan $pengaduan)
    {
        // Hapus file bukti jika ada
        if ($pengaduan->path_bukti_aduan) {
            Storage::disk('public')->delete($pengaduan->path_bukti_aduan);
        }

        // Hapus data dari database
        $pengaduan->delete();

        return redirect()->route('admin.pengaduan.index')->with('success', 'Data pengaduan berhasil dihapus.');
    }
}
