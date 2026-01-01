<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    use HasFactory;

    public const STATUSES = ['Diajukan', 'Diproses', 'Selesai'];
    protected $fillable = [
        'nama_lengkap',
        'nomor_ponsel',
        'email',
        'profesi',
        'instansi',
        'isi_aduan',
        'path_bukti_aduan',
        'status',
    ];

    public function statusPengaduan()
    {
        return $this->hasMany(StatusPengaduan::class)->latest(); // Diurutkan dari yang terbaru
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
