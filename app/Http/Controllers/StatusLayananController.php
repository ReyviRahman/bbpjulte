<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan; // Panggil model Permohonan
use Barryvdh\DomPDF\Facade\Pdf;

class StatusLayananController extends Controller
{
    /**
     * Menampilkan halaman formulir untuk memasukkan nomor registrasi.
     */
    public function index()
    {
        return view('status.index');
    }

    /**
     * Mencari permohonan dan menampilkan hasilnya.
     */
    public function search(Request $request)
    {
        // Validasi input dari pengguna
        $request->validate([
            'no_registrasi' => 'required|string|exists:permohonans,no_registrasi',
            'captcha' => 'required|captcha',
        ], [
            // Pesan error kustom dalam Bahasa Indonesia
            'captcha.required' => 'Kode keamanan wajib diisi.',
            'captcha.captcha' => 'Kode keamanan salah, silakan coba lagi.',
            'no_registrasi.required' => 'Nomor registrasi wajib diisi.',
            'no_registrasi.exists' => 'Nomor registrasi tidak ditemukan di sistem kami. Mohon periksa kembali.',
        ]);




        // Cari permohonan berdasarkan nomor registrasi
        $permohonan = Permohonan::where('no_registrasi', $request->no_registrasi)->firstOrFail();

        // Tampilkan halaman hasil dengan membawa data permohonan yang ditemukan
        return view('status.show', ['permohonan' => $permohonan]);
    }

    /**
     * Menampilkan halaman hasil status berdasarkan nomor registrasi dari URL.
     */
     public function show(Permohonan $permohonan)
    {
        // Laravel secara otomatis akan mencari data berdasarkan no_registrasi
        // berkat Route-Model Binding di file routes/web.php
        return view('status.show', ['permohonan' => $permohonan]);
    }

    public function downloadPDF(Permohonan $permohonan)
    {
        // Kita akan membuat view baru khusus untuk tampilan PDF
        // dengan membawa data permohonan yang sama
        $pdf = PDF::loadView('status.pdf_template', ['permohonan' => $permohonan]);

        // Memberi nama file PDF yang akan diunduh
        $namaFile = 'status-permohonan-' . $permohonan->no_registrasi . '.pdf';

        // Mengirim file PDF ke browser untuk diunduh
        return $pdf->download($namaFile);
    }
}
