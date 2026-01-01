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
    Schema::table('permohonans', function (Blueprint $table) {
        // Nomor unik untuk pelacakan, letakkan setelah 'id'
        $table->string('no_registrasi')->unique()->nullable()->after('id');
        // Kolom status dengan nilai default 'Diajukan'
        $table->string('status')->default('Diajukan')->after('isi_permohonan');
    });
}

public function down(): void
{
    Schema::table('permohonans', function (Blueprint $table) {
        $table->dropColumn(['no_registrasi', 'status']);
    });
}
};
