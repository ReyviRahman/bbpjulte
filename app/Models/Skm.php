<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skm extends Model
{
    protected $table = 'skm';
    public const STATUSES = ['Publik', 'Privat'];
    protected $fillable = [
        'nama_petugas',
        'nama_pemohon',
        'jenis_kelamin',
        'pendidikan',
        'profesi',
        'email',
        'instansi',
        'layanan_didapat',
        'syarat_pengurusan_pelayanan',
        'sistem_mekanisme_dan_prosedur_pelayanan',
        'waktu_penyelesaian_pelayanan',
        'kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan',
        'kesesuaian_hasil_pelayanan',
        'kemampuan_petugas_dalam_memberikan_pelayanan',
        'kesopanan_dan_keramahan_petugas',
        'penanganan_pengaduan_saran_dan_masukan',
        'sarana_dan_prasarana_penunjang_pelayanan',
        'ada_pungutan',
        'akan_informasikan_layanan',
        'kritik_saran',
        'jenis_pungutan',
        'status',
    ];
}
