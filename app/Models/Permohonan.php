<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permohonan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public const STATUSES = ['Diajukan', 'Diproses', 'Selesai', 'Ditolak'];
    protected $fillable = [
        'no_registrasi', // TAMBAHKAN
        'nama_lengkap',
        'instansi',
        'email',
        'nomor_ponsel',
        'jenis_kelamin', // TAMBAHKAN
        'pendidikan',    // TAMBAHKAN
        'profesi',       // TAMBAHKAN
        'layanan_dibutuhkan',
        'isi_permohonan',
        'path_surat_permohonan',
        'path_berkas_permohonan',
        'status', // TAMBAHKAN
    ];

    public function statusHistories()
{
    return $this->hasMany(StatusHistory::class)->latest(); // Diurutkan dari yang terbaru
}

public function getWhatsappNumberAttribute(): string
    {
        // Hapus semua karakter selain angka
        $number = preg_replace('/[^0-9]/', '', $this->nomor_ponsel);

        // Jika nomor diawali dengan 0, ganti dengan 62
        if (substr($number, 0, 1) == '0') {
            return '62' . substr($number, 1);
        }

        // Jika sudah menggunakan 62, langsung kembalikan
        if (substr($number, 0, 2) == '62') {
            return $number;
        }

        // Untuk kasus lain, kembalikan nomor aslinya (atau tambahkan logika lain jika perlu)
        return $number;
    }

}
