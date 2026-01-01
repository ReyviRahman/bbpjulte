<?php

namespace Database\Seeders;

use App\Models\FormPermohonan;
use App\Models\FormPermohonanSub;
use App\Models\FormSkm;
use App\Models\FormSkmSub;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Opsional: Kosongkan tabel dulu biar gak duplikat saat di-seed ulang
        // Disable foreign key check biar bisa truncate tabel induk & anak
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        FormPermohonanSub::truncate();
        FormPermohonan::truncate();
        FormSkmSub::truncate(); // Reset tabel SKM Sub
        FormSkm::truncate();    // Reset tabel SKM Induk
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ==========================================
        // 1. DATA SEDERHANA (Tanpa Sub Kategori)
        // ==========================================

        $jkel = ['Laki-laki', 'Perempuan'];
        foreach ($jkel as $item) {
            FormPermohonan::create(['category' => 'Jenis Kelamin', 'name' => $item]);
            FormSkm::create(['category' => 'Jenis Kelamin', 'name' => $item]);
        }

        $instruksiSuratPermohonan = ['Surat ditandatangani oleh pimpinan instansi/lembaga.', 'Khusus untuk permohonan layanan penerjemahan, silakan unduh templat surat permohonan di sini.', 'Surat permohonan diunggah dalam bentuk PDF.'];
        foreach ($instruksiSuratPermohonan as $item) {
            FormPermohonan::create(['category' => 'Unggah surat permohonan', 'name' => $item]);
        }

        // Kategori: Pendidikan Terakhir
        $pendidikan = ['SD', 'SMP', 'SMA', 'D1-D3', 'S1', 'S2', 'S3'];
        foreach ($pendidikan as $item) {
            FormPermohonan::create(['category' => 'Pendidikan Terakhir', 'name' => $item]);
            FormSkm::create(['category' => 'Pendidikan Terakhir', 'name' => $item]);
        }

        // Kategori: Profesi
        $profesi = ['PNS', 'PPPK', 'TNI/POLRI', 'Swasta', 'Mahasiswa', 'Siswa'];
        foreach ($profesi as $item) {
            FormPermohonan::create(['category' => 'Profesi', 'name' => $item]);
            FormSkm::create(['category' => 'Profesi', 'name' => $item]);
        }

        $petugas = [
            'Agung Kurniawan',
            'Agus Kurniawan',
            'Arif Budiman',
            'Dadik Dwi Nugroho',
            'Dian Anggy Maria Ulfa',
            'Dwi Haryani',
            'Eli Astuti',
            'Elva Yusanti',
            'Fitria',
            'Fitriah',
            'Gustia Mira',
            'Ilsa Dewita Putri Soraya',
            'Lawaris',
            'Leni Sulastri',
            'Lukman',
            'M. Jul Adwin',
            'Mahasiswa Magang',
            'Maryani',
            'Muhammad Ikhsan',
            'Mutia Farina',
            'Nurhaidah',
            'Prabowo Sawiji Utomo',
            'Rahmadina',
            'Ratih Sophia Lestari',
            'Reskyan Tabdrin',
            'Rina Meilisa',
            'Sabdanur',
            'Sarwono',
            'Teguh Eka Setiyabudi',
            'Wagianti',
            'Wessa Ostika Utami',
            'Yarmalus',
            'Yessi Lestari',
            'Yuliastuti',
            'Zumalal Laeli',
        ];

        foreach ($petugas as $item) {
            FormSkm::create(['category' => 'Nama Petugas yang melayani', 'name' => $item]);
        }

        // Kategori: Pungutan
        $pungutan = ['Ya', 'Tidak'];
        foreach ($pungutan as $item) {
            FormSkm::create(['category' => 'Pungutan', 'name' => $item]);
        }

        // Kategori: Informasikan Layanan
        $infoLayanan = ['Ya', 'Tidak'];
        foreach ($infoLayanan as $item) {
            FormSkm::create(['category' => 'Informasikan Layanan', 'name' => $item]);
        }

        $categories = [
            'Syarat pengurusan pelayanan',
            'Sistem Mekanisme Dan Prosedur Pelayanan',
            'Waktu Penyelesaian Pelayanan',
            'Kesesuaian Biaya Pelayanan Dengan yang Diinformasikan',
            'Kesesuaian Hasil Pelayanan',
            'Kemampuan Petugas Dalam Memberikan Pelayanan',
            'Kesopanan Dan Keramahan Petugas',
            'Penanganan Pengaduan Saran Dan Masukan',
            'Sarana Dan Prasarana Penunjang Pelayanan'
        ];

        // 2. Opsi Jawaban (Sama untuk semua kategori)
        $standardOptions = [
            ['score' => 4, 'name' => 'Sangat memuaskan'],
            ['score' => 3, 'name' => 'Memuaskan'],
            ['score' => 2, 'name' => 'Tidak memuaskan'],
            ['score' => 1, 'name' => 'Sangat tidak memuaskan'],
        ];

        // 3. Loop dan Insert ke Database
        foreach ($categories as $category) {
            foreach ($standardOptions as $option) {
                FormSkm::create([
                    'category' => $category,      // Nama Kategori
                    'name'     => $option['name'], // Sangat memuaskan, dll
                    'score'    => $option['score'] // 4, 3, 2, 1
                ]);
            }
        }

        // ==========================================
        // 2. DATA KOMPLEKS (Layanan + Sub Kategori)
        // ==========================================

        // Format array: 'Nama Layanan Induk' => ['List Sub Kategori']
        // Jika tidak punya sub, isi array kosong []
        $layananData = [
            'UKBI' => [],
            'Praktik Kerja Lapangan (Pemagangan)' => [],
            'Perpustakaan' => [],
            'Sarana dan Prasarana' => [],
            'Kunjungan Edukasi' => [],

            // Layanan dengan Sub Pilihan
            'Penerjemahan' => [
                'Penerjemahan Lisan (Juru Bahasa)',
                'Penerjemahan Tulis'
            ],

            'Fasilitasi Bantuan Teknis' => [
                'Juri',
                'Narasumber',
                'Pendampingan Kebahasaan dan Kesastraan',
                'Penyuluhan',
                'Penyuntingan',
                'Saksi Ahli (Bahasa dan Hukum)',
                'Fasilitasi Ke-BIPA-an',
                'Literasi',
                'Permintaan data Kebahasaan dan Kesastraan'
            ]
        ];

        foreach ($layananData as $layananName => $subItems) {
            // 1. Buat Induk (Layanan)
            $form = FormPermohonan::create([
                'category' => 'Layanan', // Sesuai href di view kamu
                'name'     => $layananName
            ]);

            // 2. Jika punya sub, loop dan simpan ke tabel form_subs
            if (!empty($subItems)) {
                foreach ($subItems as $subName) {
                    FormPermohonanSub::create([
                        'form_permohonan_id' => $form->id, // Ambil ID dari Induk yang baru dibuat
                        'name'    => $subName
                    ]);
                }
            }

            $formSkm = FormSkm::create([
                'category' => 'Layanan', // Sesuai href di view kamu
                'name'     => $layananName
            ]);

            if (!empty($subItems)) {
                foreach ($subItems as $subName) {
                    FormSkmSub::create([
                        'form_skm_id' => $formSkm->id, // Ambil ID dari Induk yang baru dibuat
                        'name'    => $subName
                    ]);
                }
            }
        }
    }
}
