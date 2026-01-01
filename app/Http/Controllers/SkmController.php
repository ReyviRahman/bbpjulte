<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormSkm;
use App\Models\Permohonan;
use App\Models\Skm;
use Illuminate\Http\Request;

class SkmController extends Controller
{
    public function create(Request $request, Permohonan $permohonan)
    {
        // Ambil ID dari URL (jika ada)
        $permohonan_id = $request->query('permohonan_id');

        // 'permohonan' disini adalah variabel yang berisi semua data
        // dari permohonan yang ID-nya dikirim melalui URL.
        // Kita kirim variabel ini ke view baru yang akan kita buat.
        $petugas = FormSkm::where('category', 'Nama Petugas yang melayani')
            ->orderBy('name', 'asc')
            ->pluck('name');

        // 1. Ambil Data Pendidikan
        $pendidikan = FormSkm::where('category', 'Pendidikan Terakhir')->get();

        // 2. Ambil Data Profesi (Buat jaga-jaga nanti dipakai)
        $profesi = FormSkm::where('category', 'Profesi')->get();

        // 3. Ambil Data Layanan (PENTING: Pakai 'with' untuk ambil Sub Kategori sekalian)
        $layanan = FormSkm::where('category', 'Layanan')->with('subs')->get();

        $jenisKelamin = FormSkm::where('category', 'Jenis Kelamin')->get();

        $opsiUSatu = FormSkm::where('category', 'Syarat pengurusan pelayanan')
            ->orderBy('score', 'desc')
            ->pluck('name', 'score')
            ->toArray(); 
            
        $opsiUDua = FormSkm::where('category', 'Sistem Mekanisme Dan Prosedur Pelayanan')
            ->orderBy('score', 'desc')
            ->pluck('name', 'score')
            ->toArray(); 

        $opsiUTiga = FormSkm::where('category', 'Waktu Penyelesaian Pelayanan')
            ->orderBy('score', 'desc')
            ->pluck('name', 'score')
            ->toArray(); 

        $opsiUEmpat = FormSkm::where('category', 'Kesesuaian Biaya Pelayanan Dengan yang Diinformasikan')
            ->orderBy('score', 'desc')
            ->pluck('name', 'score')
            ->toArray(); 

        $opsiULima = FormSkm::where('category', 'Kesesuaian Hasil Pelayanan')
            ->orderBy('score', 'desc')
            ->pluck('name', 'score')
            ->toArray(); 

        $opsiUEnam = FormSkm::where('category', 'Kemampuan Petugas Dalam Memberikan Pelayanan')
            ->orderBy('score', 'desc')
            ->pluck('name', 'score')
            ->toArray(); 

        $opsiUTujuh = FormSkm::where('category', 'Kesopanan Dan Keramahan Petugas')
            ->orderBy('score', 'desc')
            ->pluck('name', 'score')
            ->toArray(); 

        $opsiUDelapan = FormSkm::where('category', 'Penanganan Pengaduan Saran Dan Masukan')
            ->orderBy('score', 'desc')
            ->pluck('name', 'score')
            ->toArray(); 

        $opsiUSembilan = FormSkm::where('category', 'Sarana Dan Prasarana Penunjang Pelayanan')
            ->orderBy('score', 'desc')
            ->pluck('name', 'score')
            ->toArray(); 


        // 4. Ambil Opsi Lainnya
        $pungutan = FormSkm::where('category', 'Pungutan')->get();
        $info = FormSkm::where('category', 'Informasikan Layanan')->get();
        return view('skm.form', compact('permohonan', 'petugas', 'pendidikan', 'profesi', 'layanan', 'pungutan', 'info', 'permohonan_id', 'jenisKelamin', 'opsiUSatu', 'opsiUDua', 'opsiUTiga', 'opsiUEmpat', 'opsiULima', 'opsiUEnam', 'opsiUTujuh', 'opsiUDelapan', 'opsiUSembilan'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input, termasuk inputan baru

        $validatedData = $request->validate([
            'nama_petugas' => 'required|string|max:255',
            'nama_pemohon' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'jenis_kelamin' => 'required|string', // Validasi baru
            'pendidikan' => 'required|string', // Validasi baru
            'pendidikan_lainnya' => 'nullable|string|max:255', // Teks input "lainnya"
            'profesi' => 'required|string', // Validasi baru
            'profesi_lainnya' => 'nullable|string|max:255',
            'instansi' => 'required|string|max:255',
            'instansi_lainnya' => 'nullable|string|max:255',
            'layanan_didapat' => 'required|string|min:1',
            'syarat_pengurusan_pelayanan' => 'required',
            'sistem_mekanisme_dan_prosedur_pelayanan' => 'required',
            'waktu_penyelesaian_pelayanan' => 'required',
            'kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan' => 'required',
            'kesesuaian_hasil_pelayanan' => 'required',
            'kemampuan_petugas_dalam_memberikan_pelayanan' => 'required',
            'kesopanan_dan_keramahan_petugas' => 'required',
            'penanganan_pengaduan_saran_dan_masukan' => 'required',
            'sarana_dan_prasarana_penunjang_pelayanan' => 'required',
            'ada_pungutan' => 'required',
            'jenis_pungutan' => 'nullable',
            'akan_informasikan_layanan' => 'required',
            'kritik_saran' => 'required',
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

        $instansiValue = $request->instansi;
        if ($instansiValue === 'lainnya') {
            $instansiValue = $request->instansi_lainnya;
        }

        // 4. Simpan data ke Database dengan data yang sudah diolah
        $skm = Skm::create([
            'status' => 'Publik',

            'nama_petugas' => $validatedData['nama_petugas'],
            'nama_pemohon' => $validatedData['nama_pemohon'],
            'instansi' => $instansiValue,
            'email' => $validatedData['email'],
            'jenis_kelamin' => $validatedData['jenis_kelamin'],
            'pendidikan' => $pendidikanValue,
            'profesi' => $profesiValue,
            'layanan_didapat' => $validatedData['layanan_didapat'],

            'syarat_pengurusan_pelayanan' => $validatedData['syarat_pengurusan_pelayanan'],
            'sistem_mekanisme_dan_prosedur_pelayanan' => $validatedData['sistem_mekanisme_dan_prosedur_pelayanan'],
            'waktu_penyelesaian_pelayanan' => $validatedData['waktu_penyelesaian_pelayanan'],
            'kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan' => $validatedData['kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan'],
            'kesesuaian_hasil_pelayanan' => $validatedData['kesesuaian_hasil_pelayanan'],
            'kemampuan_petugas_dalam_memberikan_pelayanan' => $validatedData['kemampuan_petugas_dalam_memberikan_pelayanan'],
            'kesopanan_dan_keramahan_petugas' => $validatedData['kesopanan_dan_keramahan_petugas'],
            'penanganan_pengaduan_saran_dan_masukan' => $validatedData['penanganan_pengaduan_saran_dan_masukan'],
            'sarana_dan_prasarana_penunjang_pelayanan' => $validatedData['sarana_dan_prasarana_penunjang_pelayanan'],

            'ada_pungutan' => $validatedData['ada_pungutan'],
            'jenis_pungutan' => $validatedData['jenis_pungutan'] ?? null,

            'akan_informasikan_layanan' => $validatedData['akan_informasikan_layanan'],
            'kritik_saran' => $validatedData['kritik_saran'],
        ]);


        return redirect()->route('skm.sukses', [
            'permohonan_id' => $request->permohonan_id
        ]);
    }

    public function sukses(Request $request)
    {
        // 1. Ambil ID dari URL Query String (?permohonan_id=xxx)
        $permohonanId = $request->query('permohonan_id');

        // Jika $permohonanId kosong, variabel $permohonan akan bernilai null (aman).
        $permohonan = $permohonanId ? Permohonan::find($permohonanId) : null;

        // 3. Kirim variabel $permohonan yang sudah siap ke View
        return view('skm.sukses', [
            'permohonan' => $permohonan
        ]);
    }
}
