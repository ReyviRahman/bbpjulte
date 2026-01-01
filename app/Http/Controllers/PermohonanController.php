<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Http\Request;
use App\Events\PermohonanDibuat;
use App\Models\FormPermohonan;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use Barryvdh\DomPDF\Facade\Pdf;

class PermohonanController extends Controller
{
    /**
     * Menampilkan formulir permohonan layanan.
     */
    public function create()
    {
        // 1. Ambil Data Pendidikan
        $pendidikan = FormPermohonan::where('category', 'Pendidikan Terakhir')->get();

        // 2. Ambil Data Profesi
        $profesi = FormPermohonan::where('category', 'Profesi')->get();

        // 3. Ambil Data Layanan
        $layanan = FormPermohonan::where('category', 'Layanan')->with('subs')->get();

        // 4. Ambil Data Instruksi
        $instruksi = FormPermohonan::where('category', 'Unggah surat permohonan')->get();

        // 5. [BARU] Ambil Data Jenis Kelamin
        $jenisKelamin = FormPermohonan::where('category', 'Jenis Kelamin')->get();

        $instruksiUnggahLampiran = FormPermohonan::where('category', 'Unggah lampiran permohonan')->get();


        // Kirim semua variabel ke view (tambahkan 'jenisKelamin')
        return view('permohonan.form', compact('pendidikan', 'profesi', 'layanan', 'instruksi', 'jenisKelamin', 'instruksiUnggahLampiran'));
    }

    /**
     * Menyimpan data permohonan baru dari formulir.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input, termasuk inputan baru
        $validatedData = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'instansi' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nomor_ponsel' => 'required|string|max:20',
            'jenis_kelamin' => 'required|string', // Validasi baru
            'pendidikan' => 'required|string', // Validasi baru
            'pendidikan_lainnya' => 'nullable|string|max:255', // Teks input "lainnya"
            'profesi' => 'required|string', // Validasi baru
            'profesi_lainnya' => 'nullable|string|max:255', // Teks input "lainnya"
            'layanan_dibutuhkan' => 'required|string|min:1',
            'isi_permohonan' => 'required|string',
            'surat_permohonan' => 'required|file|mimes:pdf|max:2048',
            'berkas_permohonan' => 'nullable|file|mimes:pdf|max:5120',
            'captcha' => 'required|captcha',
        ], [
            'captcha.required' => 'Kode keamanan wajib diisi.',
            'captcha.captcha' => 'Kode keamanan salah, silakan coba lagi.',
        ]);

        // 2. Logika untuk menangani input "lainnya"
        $pendidikanValue = $request->pendidikan;
        if ($pendidikanValue === 'lainnya') {
            $pendidikanValue = $request->pendidikan_lainnya;
        }

        $profesiValue = $request->profesi;
        if ($profesiValue === 'lainnya') {
            $profesiValue = $request->profesi_lainnya;
        }

        // 3. Simpan File
        $pathSurat = $request->file('surat_permohonan')->store('surat', 'public');
        $pathBerkas = null;
        if ($request->hasFile('berkas_permohonan')) {
            $pathBerkas = $request->file('berkas_permohonan')->store('berkas', 'public');
        }

        // 4. Simpan data ke Database dengan data yang sudah diolah
        $permohonan = Permohonan::create([
            'no_registrasi' => 'ULT-' . now()->format('YmdHis') . rand(10, 99), // Membuat ID unik
            'status' => 'Diajukan', // Status awal
            'nama_lengkap' => $validatedData['nama_lengkap'],
            'instansi' => $validatedData['instansi'],
            'email' => $validatedData['email'],
            'nomor_ponsel' => $validatedData['nomor_ponsel'],
            'jenis_kelamin' => $validatedData['jenis_kelamin'],
            'pendidikan' => $pendidikanValue,
            'profesi' => $profesiValue,
            'layanan_dibutuhkan' => $validatedData['layanan_dibutuhkan'],
            'isi_permohonan' => $validatedData['isi_permohonan'],
            'path_surat_permohonan' => $pathSurat,
            'path_berkas_permohonan' => $pathBerkas,
        ]);

        // PICU EVENT SETELAH DATA BERHASIL DIBUAT
        // event(new PermohonanDibuat($permohonan));

        return redirect()->route('permohonan.sukses', ['permohonan' => $permohonan]);
    }

    public function sukses(Permohonan $permohonan)
    {
        // 'permohonan' disini adalah variabel yang berisi semua data
        // dari permohonan yang ID-nya dikirim melalui URL.
        // Kita kirim variabel ini ke view baru yang akan kita buat.
        return view('permohonan.sukses', [
            'permohonan' => $permohonan
        ]);
    }

    public function downloadPDF(Permohonan $permohonan)
    {
        // dengan membawa data permohonan yang sama
        $pdf = PDF::loadView('permohonan.pdf_template', ['permohonan' => $permohonan]);

        // Memberi nama file PDF yang akan diunduh
        $namaFile = 'permohonan-' . $permohonan->no_registrasi . '.pdf';

        // Mengirim file PDF ke browser untuk diunduh
        return $pdf->download($namaFile);
    }
}
