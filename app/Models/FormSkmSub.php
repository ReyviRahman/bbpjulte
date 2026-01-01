<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSkmSub extends Model
{
    protected $fillable = ['form_skm_id', 'name'];

    // Relasi: Sub ini milik siapa?
    public function form()
    {
        return $this->belongsTo(FormSkm::class, 'form_skm_id');
    }
}
