<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permohonan; // Panggil model Permohonan
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil semua permohonan yang statusnya masih 'Diajukan'
        $queryPermohonanBaru = Permohonan::where('status', 'Diajukan');

        // Hitung jumlahnya
        $jumlahPermohonanBaru = $queryPermohonanBaru->count();

        // Ambil 5 data terbaru untuk ditampilkan sebagai daftar notifikasi
        $daftarPermohonanBaru = $queryPermohonanBaru->latest()->take(5)->get();

        // Kirim data ke view
        return view('admin.dashboard', [
            'jumlahPermohonanBaru' => $jumlahPermohonanBaru,
            'daftarPermohonanBaru' => $daftarPermohonanBaru,
        ]);
    }
}
