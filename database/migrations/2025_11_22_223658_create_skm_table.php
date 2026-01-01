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
        Schema::create('skm', function (Blueprint $table) {
            $table->id();
            $table->string('nama_petugas')->nullable();
            $table->string('nama_pemohon')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('pendidikan')->nullable();
            $table->string('profesi')->nullable();
            $table->string('email')->nullable();
            $table->string('instansi')->nullable();
            $table->string('layanan_didapat')->nullable();
            $table->integer('syarat_pengurusan_pelayanan')->nullable();
            $table->integer('sistem_mekanisme_dan_prosedur_pelayanan')->nullable();
            $table->integer('waktu_penyelesaian_pelayanan')->nullable();
            $table->integer('kesesuaian_biaya_pelayanan_dengan_yang_diinformasikan')->nullable();
            $table->integer('kesesuaian_hasil_pelayanan')->nullable();
            $table->integer('kemampuan_petugas_dalam_memberikan_pelayanan')->nullable();
            $table->integer('kesopanan_dan_keramahan_petugas')->nullable();
            $table->integer('penanganan_pengaduan_saran_dan_masukan')->nullable();
            $table->integer('sarana_dan_prasarana_penunjang_pelayanan')->nullable();
            $table->string('ada_pungutan')->nullable();
            $table->string('akan_informasikan_layanan')->nullable();
            $table->text('kritik_saran')->nullable();
            $table->text('jenis_pungutan')->nullable();
            $table->enum('status', ['Publik', 'Privat'])->default('Publik');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skm');
    }
};
