<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormSkm;
use Illuminate\Http\Request;

class FormSkmController extends Controller
{
    public function index(Request $request)
    {
        $kategori = $request->query('category');
        
        // AMBIL DATA: Filter berdasarkan kategori, urutkan dari yang terbaru
        $data = FormSkm::where('category', $kategori)->latest()->get();
        return view('admin.forms.form-skm.index', compact('kategori', 'data'));
    }

    public function daftarForm()
    {
        return view('admin.forms.form-skm.daftar-input');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'name'     => 'required|string|max:255',
            'score'     => 'nullable|integer',
        ]);

        FormSkm::create([
            'category' => $request->category,
            'name'     => $request->name,
            'score'     => $request->score,
        ]);

        return redirect()
            ->route('admin.form-skm.index', ['category' => $request->category])
            ->with('success', 'Data berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'score'     => 'nullable|integer',
        ]);

        $form = FormSkm::findOrFail($id);
        
        $form->update([
            'name' => $request->name,
            'score'     => $request->score,
        ]);

        return redirect()
            ->route('admin.form-skm.index', ['category' => $form->category])
            ->with('success', 'Data berhasil diperbarui!');
    }

    // FUNGSI DELETE (HAPUS)
    public function destroy($id)
    {
        $form = FormSkm::findOrFail($id);
        $kategori = $form->category; // Simpan kategori dulu sebelum dihapus buat redirect
        
        $form->delete();

        return redirect()
            ->route('admin.form-skm.index', ['category' => $kategori])
            ->with('success', 'Data berhasil dihapus!');
    }
}
