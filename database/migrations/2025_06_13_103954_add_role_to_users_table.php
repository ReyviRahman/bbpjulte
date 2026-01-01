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
    Schema::table('users', function (Blueprint $table) {
        // Kita hapus kolom lama jika ada
        if (Schema::hasColumn('users', 'is_admin')) {
            $table->dropColumn('is_admin');
        }
        // Kita tambahkan kolom baru untuk peran
        $table->string('role')->default('petugas')->after('email');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('role');
        // Jika ingin bisa rollback, tambahkan kembali kolom is_admin
        // $table->boolean('is_admin')->default(false);
    });
}
};
