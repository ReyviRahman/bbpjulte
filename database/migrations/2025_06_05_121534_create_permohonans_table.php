<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_xxxxxx_create_permohonans_table.php

public function up(): void
{
    Schema::create('permohonans', function (Blueprint $table) {
        $table->id();
        $table->string('nama_lengkap');
        $table->string('instansi');
        $table->string('email');
        $table->string('nomor_ponsel');
        $table->string('layanan_dibutuhkan');
        $table->text('isi_permohonan');
        $table->string('path_surat_permohonan')->nullable(); // Path file
        $table->string('path_berkas_permohonan')->nullable(); // Path file
        $table->timestamps(); // otomatis membuat kolom created_at dan updated_at
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonans');
    }
};
