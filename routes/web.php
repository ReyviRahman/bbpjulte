<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermohonanController as AdminPermohonanController;
use App\Http\Controllers\Admin\PengaduanController as AdminPengaduanController; // Nama alias untuk controller admin
use App\Http\Controllers\Admin\SkmController as AdminSkmController;
use App\Http\Controllers\StatusLayananController;
use App\Http\Controllers\Admin\StatistikController;
use App\Http\Controllers\Admin\StatistikPengaduanController;
use App\Http\Controllers\Admin\StatistikSkmController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FormPermohonanController;
use App\Http\Controllers\Admin\FormPermohonanSubController;
use App\Http\Controllers\Admin\FormSkmController;
use App\Http\Controllers\Admin\FormSkmSubController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\SkmController;

Route::get('/', [HomeController::class, 'index'])->name('beranda');
Route::get('/permohonan', [PermohonanController::class, 'create'])->name('permohonan.create');
Route::post('/permohonan', [PermohonanController::class, 'store'])->name('permohonan.store');
Route::get('/permohonan/sukses/{permohonan}', [PermohonanController::class, 'sukses'])->name('permohonan.sukses');
Route::get('/permohonan/{permohonan:no_registrasi}/unduh', [PermohonanController::class, 'downloadPDF'])->name('permohonan.downloadPDF');

Route::get('/skm', [SkmController::class, 'create'])->name('skm.create');
Route::post('/skm', [SkmController::class, 'store'])->name('skm.store');
Route::get('/skm/sukses', [SkmController::class, 'sukses'])->name('skm.sukses');


Route::get('/pengumuman', function () {
    return view('pengumuman');
})->name('pengumuman');
Route::get('/pengaduan', [PengaduanController::class, 'create'])->name('pengaduan.create');
Route::post('/pengaduan', [PengaduanController::class, 'store'])->name('pengaduan.store');
Route::get('/hubungi-kami', function () {
    return view('hubungi-kami');
})->name('hubungi-kami');
Route::get('/lacak-layanan', [StatusLayananController::class, 'index'])->name('status.index');
Route::post('/lacak-layanan', [StatusLayananController::class, 'search'])->name('status.search');
Route::get('/lacak-layanan/{permohonan:no_registrasi}/unduh', [StatusLayananController::class, 'downloadPDF'])->name('status.downloadPDF');
Route::get('/lacak/{permohonan:no_registrasi}', [StatusLayananController::class, 'show'])->name('status.track');

Route::get('/refresh-captcha', function () {
    return response()->json(['captcha' => captcha_img('math')]);
})->name('captcha.refresh');

Route::prefix('standar-pelayanan')->name('standar-pelayanan.')->group(function () {

    Route::get('/penerjemahan', function () {
        return view('standar-pelayanan.penerjemahan');
    })->name('penerjemahan');

    Route::get('/perpustakaan', function () {
        return view('standar-pelayanan.perpustakaan');
    })->name('perpustakaan');

    Route::get('/fasilitas-bantuan-teknis', function () {
        return view('standar-pelayanan.fasilitas-bantuan-teknis');
    })->name('fasilitas-bantuan-teknis');

    Route::get('/praktik-kerja-lapangan', function () {
        return view('standar-pelayanan.praktik-kerja-lapangan');
    })->name('praktik-kerja-lapangan');

    Route::get('/pesapra', function () {
        return view('standar-pelayanan.pesapra');
    })->name('pesapra');

    Route::get('/kunjungan-edukasi', function () {
        return view('standar-pelayanan.kunjungan-edukasi');
    })->name('kunjungan-edukasi');
});

Route::get('/dashboard', function () {
    if (Auth::user() && Auth::user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('permohonan/export-excel', [AdminPermohonanController::class, 'exportExcel'])->name('permohonan.export_excel');
    Route::get('permohonan/export-pdf', [AdminPermohonanController::class, 'exportPdf'])->name('permohonan.export_pdf');
    Route::get('permohonan/print', [AdminPermohonanController::class, 'printView'])->name('permohonan.print');
    Route::resource('permohonan', AdminPermohonanController::class)->except(['create', 'store']);
    Route::get('permohonan/{permohonan}/cetak', [AdminPermohonanController::class, 'cetak'])->name('permohonan.cetak');
    Route::get('permohonan/{permohonan}/unduh', [AdminPermohonanController::class, 'unduh'])->name('permohonan.unduh');
    
    Route::get('pengaduan/export-excel', [AdminPengaduanController::class, 'exportExcel'])->name('pengaduan.export_excel');
    Route::get('pengaduan/export-pdf', [AdminPengaduanController::class, 'exportPdf'])->name('pengaduan.export_pdf');
    Route::get('pengaduan/print', [AdminPengaduanController::class, 'printView'])->name('pengaduan.print');
    Route::resource('pengaduan', AdminPengaduanController::class)->except(['create', 'store']);
    Route::get('pengaduan/{pengaduan}/cetak', [AdminPengaduanController::class, 'cetak'])->name('pengaduan.cetak');
    Route::get('pengaduan/{pengaduan}/unduh', [AdminPengaduanController::class, 'unduh'])->name('pengaduan.unduh');

    Route::get('skm/export-excel', [AdminSkmController::class, 'exportExcel'])->name('skm.export_excel');
    Route::get('skm/export-pdf', [AdminSkmController::class, 'exportPdf'])->name('skm.export_pdf');
    Route::get('skm/print', [AdminSkmController::class, 'printView'])->name('skm.print');
    Route::resource('skm', AdminSkmController::class)->except(['create', 'store']);

    // Rute baru untuk Statistik
    Route::get('/statistik', [StatistikController::class, 'index'])->name('statistik.index');
    Route::get('/statistik/export', [StatistikController::class, 'export'])->name('statistik.export');
    Route::get('/statistik-pengaduan', [StatistikPengaduanController::class, 'index'])->name('statistik-pengaduan.index');
    Route::get('/statistik-pengaduan/export', [StatistikPengaduanController::class, 'export'])->name('statistik-pengaduan.export');

    Route::get('/statistik-skm', [StatistikSkmController::class, 'index'])->name('statistik-skm.index');
    Route::get('/statistik-skm/export', [StatistikSkmController::class, 'export'])->name('statistik-skm.export');


    // RUTE BARU UNTUK MANAJEMEN
    Route::resource('users', UserController::class);

    // Route::resource('forms', FormController::class);
    // Route::resource('forms.subs', FormSubController::class);


    Route::get('forms/daftar-input', function () {
        return view('admin.forms.daftar-input');
    })->name('forms.daftar-input');

    Route::get('forms/form/permohonan/daftar-input', [FormPermohonanController::class, 'daftarForm'])
        ->name('form-permohonan.daftar-input');
    Route::get('forms/form-permohonan', [FormPermohonanController::class, 'index'])
        ->name('form-permohonan.index');
    Route::post('forms/form-permohonan', [FormPermohonanController::class, 'store'])
        ->name('form-permohonan.store');
    Route::put('forms/form-permohonan/{id}', [FormPermohonanController::class, 'update'])
        ->name('form-permohonan.update');
    Route::delete('forms/form-permohonan/{id}', [FormPermohonanController::class, 'destroy'])
        ->name('form-permohonan.destroy');

    Route::get('forms/form-permohonan/subs', [FormPermohonanSubController::class, 'index'])
        ->name('form-permohonan.subs.index');
    Route::post('forms/form-permohonan/subs', [FormPermohonanSubController::class, 'store'])
        ->name('form-permohonan.subs.store');
    Route::put('forms/form-permohonan/subs/{id}', [FormPermohonanSubController::class, 'update'])
        ->name('form-permohonan.subs.update');
    Route::delete('forms/form-permohonan/subs/{id}', [FormPermohonanSubController::class, 'destroy'])
        ->name('form-permohonan.subs.destroy');

    Route::get('forms/form/skm/daftar-input', [FormSkmController::class, 'daftarForm'])
        ->name('form-skm.daftar-input');
    Route::get('forms/form-skm', [FormSkmController::class, 'index'])
        ->name('form-skm.index');
    Route::post('forms/form-skm', [FormSkmController::class, 'store'])
        ->name('form-skm.store');
    Route::put('forms/form-skm/{id}', [FormSkmController::class, 'update'])
        ->name('form-skm.update');
    Route::delete('forms/form-skm/{id}', [FormSkmController::class, 'destroy'])
        ->name('form-skm.destroy');

    Route::get('forms/form-skm/subs', [FormSkmSubController::class, 'index'])
        ->name('form-skm.subs.index');
    Route::post('forms/form-skm/subs', [FormSkmSubController::class, 'store'])
        ->name('form-skm.subs.store');
    Route::put('forms/form-skm/subs/{id}', [FormSkmSubController::class, 'update'])
        ->name('form-skm.subs.update');
    Route::delete('forms/form-skm/subs/{id}', [FormSkmSubController::class, 'destroy'])
        ->name('form-skm.subs.destroy');
});

require __DIR__ . '/auth.php';
