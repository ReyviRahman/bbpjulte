<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormPermohonan extends Model
{
    use HasFactory;
    protected $fillable = ['category', 'name', 'file_template'];

    // Relasi: Satu Form (misal: Penerjemahan) punya BANYAK Sub (Lisan, Tulis)
    public function subs()
    {
        return $this->hasMany(FormPermohonanSub::class, 'form_permohonan_id');
    }
}
