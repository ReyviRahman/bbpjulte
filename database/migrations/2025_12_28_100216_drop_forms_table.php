<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hapus Child dulu biar gak kena error Foreign Key
        Schema::dropIfExists('form_subs');

        // Baru hapus Parent
        Schema::dropIfExists('forms');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
