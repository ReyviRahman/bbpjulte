<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormPermohonan;
use App\Models\FormPermohonanSub;
use Illuminate\Http\Request;

class FormPermohonanSubController extends Controller
{
    public function index(Request $request)
    {
        $formId = $request->query('form_id');
        abort_if(!$formId, 404);

        $parent = FormPermohonan::findOrFail($formId);
        $subs = $parent->subs()->latest()->get();

        return view('admin.forms.form-permohonan.subs', compact('parent', 'subs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'form_id' => 'required|exists:form_permohonans,id',
            'name'    => 'required|string|max:255',
        ]);

        FormPermohonanSub::create([
            'form_permohonan_id' => $request->form_id, 
            'name'    => $request->name,
        ]);

        return redirect()
            ->route('admin.form-permohonan.subs.index', ['form_id' => $request->form_id])
            ->with('success', 'Sub berhasil ditambahkan.');
    }

    // UPDATE: Saya ubah jadi hanya ($request, $id) biar gak ribet parameter ganda
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $sub = FormPermohonanSub::findOrFail($id);

        $sub->update([
            'name' => $request->name
        ]);

        // Pake back() aja biar aman dan gak pusing mikirin form_id
        return back()->with('success', 'Sub kategori berhasil diperbarui!');
    }

    // DESTROY: Saya ubah jadi hanya ($id)
    public function destroy($id)
    {
        $sub = FormPermohonanSub::findOrFail($id);

        $sub->delete();

        return back()->with('success', 'Sub kategori berhasil dihapus!');

    }
}