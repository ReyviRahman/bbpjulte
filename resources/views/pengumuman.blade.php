{{-- 1. Memberitahu Blade untuk menggunakan layout utama dari layouts/app.blade.php --}}
@extends('layouts.public')
@section('title', 'Pengumuman')
@section('content')
<style>
    .page-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .page-header h1 {
        color: var(--primary-color);
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 0.5em;
    }
    @media (max-width: 768px) {
    .hero-section h1 {
        font-size: 2.2rem; /* Perkecil ukuran font di layar kecil */
    }
    .hero-section p {
        font-size: 1rem;
    }
    }
</style>

<div class="page-container">
    <div class="page-header">
        <h1>Pengumuman</h1>
    </div>

    <div class="announcement-list">
        {{-- Nanti kita bisa isi dengan daftar pengumuman dari database --}}
        <p>Saat ini belum ada pengumuman terbaru.</p>
        <p>Silakan periksa kembali halaman ini secara berkala untuk mendapatkan informasi terkini dari Balai Bahasa Provinsi Jambi.</p>
    </div>
</div>
@endsection
