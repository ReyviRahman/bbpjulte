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
    Schema::create('pengaduans', function (Blueprint $table) {
        $table->id();
        $table->string('nama_lengkap');
        $table->string('nomor_ponsel');
        $table->string('email');
        $table->string('profesi');
        $table->string('instansi');
        $table->text('isi_aduan');
        $table->string('path_bukti_aduan')->nullable();
        $table->enum('status', ['Diajukan', 'Diproses', 'Selesai'])->default('Diajukan');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduans');
    }
};
