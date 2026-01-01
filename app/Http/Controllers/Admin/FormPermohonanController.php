<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormPermohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FormPermohonanController extends Controller
{
    public function index(Request $request)
    {
        $kategori = $request->query('category');

        // AMBIL DATA: Filter berdasarkan kategori, urutkan dari yang terbaru
        $data = FormPermohonan::where('category', $kategori)->latest()->get();
        return view('admin.forms.form-permohonan.index', compact('kategori', 'data'));
    }

    public function daftarForm()
    {
        return view('admin.forms.form-permohonan.daftar-input');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category'      => 'required|string',
            'name'          => 'required|string|max:255',
            'file_template' => 'nullable|file|mimes:pdf,doc,docx'
        ]);

        $filePath = null;

        if ($request->hasFile('file_template')) {
            // 1. Ambil nama file asli dari komputer user
            // Contoh: "Template Surat.pdf"
            $originalFilename = $request->file('file_template')->getClientOriginalName();

            // 2. Simpan dengan nama tersebut (storeAs)
            // Hasil di database: "templates/Template Surat.pdf"
            $filePath = $request->file('file_template')->storeAs('templates', $originalFilename, 'public');
        }

        FormPermohonan::create([
            'category'      => $request->category,
            'name'          => $request->name,
            'file_template' => $filePath,
        ]);

        return redirect()
            ->route('admin.form-permohonan.index', ['category' => $request->category])
            ->with('success', 'Data berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'file_template' => 'nullable|file|mimes:pdf,doc,docx',
        ]);

        $form = FormPermohonan::findOrFail($id);

        $data = [
            'name' => $request->name,
        ];

        if ($request->hasFile('file_template')) {

            // Hapus file lama (Opsional, tapi disarankan agar storage tidak penuh)
            if ($form->file_template && Storage::disk('public')->exists($form->file_template)) {
                Storage::disk('public')->delete($form->file_template);
            }

            // 1. Ambil nama file asli
            $originalFilename = $request->file('file_template')->getClientOriginalName();

            // 2. Simpan dengan nama asli
            $filePath = $request->file('file_template')->storeAs('templates', $originalFilename, 'public');

            $data['file_template'] = $filePath;
        }

        $form->update($data);

        return redirect()
            ->route('admin.form-permohonan.index', ['category' => $form->category])
            ->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $form = FormPermohonan::findOrFail($id);
        $kategori = $form->category;

        // 1. Hapus File Fisik dari Storage (Jika ada)
        if ($form->file_template && Storage::disk('public')->exists($form->file_template)) {
            Storage::disk('public')->delete($form->file_template);
        }

        // 2. Hapus Data dari Database
        $form->delete();

        return redirect()
            ->route('admin.form-permohonan.index', ['category' => $kategori])
            ->with('success', 'Data berhasil dihapus!');
    }
}
