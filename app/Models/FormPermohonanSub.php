<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormPermohonanSub extends Model
{
    protected $fillable = ['form_permohonan_id', 'name'];

    // Relasi: Sub ini milik siapa?
    public function form()
    {
        return $this->belongsTo(FormPermohonan::class, 'form_permohonan_id');
    }
}
