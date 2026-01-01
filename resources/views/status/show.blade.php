@extends('layouts.public')

@section('title', 'Hasil Pelacakan - ' . $permohonan->no_registrasi)

@push('styles')
<style>
    /* Main Container */
    .status-page-container {
        max-width: 800px;
        margin: 2em auto;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        border: 1px solid #e2e8f0;
    }

    /* Header */
    .status-page-header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .status-page-header h1 {
        margin: 0;
        font-size: 1.5rem;
        color: var(--primary-color);
        font-weight: 700;
    }
    .status-page-header p {
        margin: 0.25rem 0 0 0;
        color: #64748b;
    }

    /* Summary Section */
    .summary-section {
        padding: 2rem;
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    @media (min-width: 768px) {
        .summary-section {
            grid-template-columns: 1fr 1fr;
        }
    }
    .summary-details dt {
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.25rem;
    }
    .summary-details dd {
        margin: 0 0 1rem 0;
        color: #1e293b;
    }
    .summary-current-status {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
    }
    .summary-current-status .label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }
    .status-badge {
        display: inline-block;
        padding: 0.5em 1.25em;
        border-radius: 9999px;
        font-weight: 700;
        font-size: 1.1rem;
    }
    .status-note {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px dashed #cbd5e1;
        font-style: italic;
        color: #475569;
        font-size: 0.9rem;
    }
    .download-button {
        margin-top: 1.5rem;
        background-color: #dc2626;
        color: white;
        padding: 8px 16px;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background-color 0.2s;
    }
    .download-button:hover {
        background-color: #b91c1c;
    }

    /* History Section */
    .history-section {
        padding: 0 2rem 2rem 2rem;
    }
    .history-section h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 0.75rem;
    }

    /* === CSS TIMELINE VERSI SIMPLE (SEPERTI SEBELUMNYA) === */
    .timeline {
        border-left: 3px solid #e0e0e0;
        margin-top: 1em;
        padding-left: 1.5rem;
        margin-left: 0.5rem;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 2.5rem;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-item::before {
        content: '';
        display: block;
        position: absolute;
        left: -27px;
        top: 5px;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background-color: var(--secondary-color);
        border: 2px solid #fff;
    }
    .timeline-item:first-child::before {
        background-color: #16a34a; /* Warna hijau untuk status teratas/terbaru */
    }
    .timeline-content h4 {
        margin: 0 0 0.5rem 0;
        font-weight: 600;
        color: #1e293b;
    }
    .timeline-content .keterangan {
        font-size: 0.9rem;
        color: #475569;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
        border-radius: 6px;
        margin-bottom: 0.5rem;
    }
    .timeline-content .time {
        font-size: 0.8rem;
        color: #64748b;
    }
</style>
@endpush

@section('content')
<div class="status-page-container">
    <div class="status-page-header">
        <h1>Status Permohonan</h1>
        <p>No. Registrasi: <strong>{{ $permohonan->no_registrasi }}</strong></p>
    </div>

    <div class="summary-section">
        <div class="summary-details">
            <dl>
                <dt>Nama Pemohon</dt>
                <dd>{{ $permohonan->nama_lengkap }}</dd>
                <dt>Instansi/Lembaga</dt>
                <dd>{{ $permohonan->instansi }}</dd>
                <dt>Layanan yang Diminta</dt>
                <dd>{{ $permohonan->layanan_dibutuhkan }}</dd>
            </dl>
        </div>
        <div class="summary-current-status">
            <div class="label">Status Saat Ini</div>
            @php
                $statusColor = [
                    'Diajukan' => 'bg-blue-100 text-blue-800',
                    'Diproses' => 'bg-amber-100 text-amber-800',
                    'Selesai' => 'bg-green-100 text-green-800',
                    'Ditolak' => 'bg-red-100 text-red-800',
                ][$permohonan->status] ?? 'bg-gray-100 text-gray-800';
            @endphp
            <div class="status-badge {{ $statusColor }}">{{ $permohonan->status }}</div>

            @if($permohonan->statusHistories->isNotEmpty() && $permohonan->statusHistories->first()->keterangan)
                <div class="status-note">
                    <strong>Catatan Terbaru:</strong> "{{ $permohonan->statusHistories->first()->keterangan }}"
                </div>
            @endif

            <a href="{{ route('status.downloadPDF', $permohonan) }}" class="download-button">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                <span>Unduh PDF</span>
            </a>
        </div>
    </div>

    <div class="history-section">
        <h3>Riwayat Proses Layanan</h3>
        <div class="timeline">
            @forelse($permohonan->statusHistories as $history)
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>{{ $history->status }}</h4>
                         <div class="time">{{ $history->created_at->isoFormat('dddd, D MMMM Y, HH:mm') }} WIB
                            @if($history->user) - oleh {{ $history->user->name }} @endif
                        </div>
                        @if($history->keterangan)
                            <div class="keterangan">"{{ $history->keterangan }}"</div>
                        @endif
                    </div>
                </div>
            @empty
                <p>Belum ada riwayat status untuk permohonan ini.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
