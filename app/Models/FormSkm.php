<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSkm extends Model
{
    use HasFactory;
    protected $fillable = ['category', 'name', 'score'];

    // Relasi: Satu Form (misal: Penerjemahan) punya BANYAK Sub (Lisan, Tulis)
    public function subs()
    {
        return $this->hasMany(FormSkmSub::class, 'form_skm_id');
    }
}
