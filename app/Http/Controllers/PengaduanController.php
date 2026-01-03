<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class PengaduanController extends Controller
{
    /**
     * Menampilkan formulir pengaduan.
     */
    public function create()
    {
        return view('pengaduan.create');
    }

    /**
     * Menyimpan data pengaduan baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nomor_ponsel' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'profesi' => 'required|string|max:255',
            'instansi' => 'required|string|max:255',
            'isi_aduan' => 'required|string',
            'bukti_aduan' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'g-recaptcha-response' => ['required', function ($attribute, $value, $fail) {
                $gResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => env('RECAPTCHA_SECRET_KEY'),
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);

                // Jika Google bilang "False" (gagal), maka tampilkan error
                if (!$gResponse->json('success')) {
                    $fail('Verifikasi robot gagal, silakan coba lagi.');
                }
            }]
        ], [
            'g-recaptcha-response.required' => 'Silakan centang kotak "Saya bukan robot".',
        ]);

        $pathBukti = null;
        if ($request->hasFile('bukti_aduan')) {
            $pathBukti = $request->file('bukti_aduan')->store('bukti-aduan', 'public');
        }

        Pengaduan::create([
            'nama_lengkap' => $request->nama_lengkap,
            'nomor_ponsel' => $request->nomor_ponsel,
            'email' => $request->email,
            'profesi' => $request->profesi,
            'instansi' => $request->instansi,
            'isi_aduan' => $request->isi_aduan,
            'path_bukti_aduan' => $pathBukti,
        ]);

        // Nanti kita bisa buat halaman sukses terpisah
        return redirect()->route('pengaduan.create')->with('success', 'Terima kasih, pengaduan Anda telah kami terima.');
    }
}
