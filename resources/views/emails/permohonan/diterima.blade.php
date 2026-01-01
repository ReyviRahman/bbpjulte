<x-mail::message>
# Permohonan Layanan Anda Telah Diterima

Yth. **{{ $permohonan->nama_lengkap }}**,

Terima kasih telah mengajukan permohonan layanan kepada kami. Permohonan Anda telah kami terima dan sedang dalam antrean untuk diproses.

Berikut adalah detail permohonan Anda:

**Nomor Registrasi Anda (Mohon Disimpan):**
<x-mail::panel>
# {{ $permohonan->no_registrasi }}
</x-mail::panel>

- **Instansi:** {{ $permohonan->instansi }}
- **Layanan yang Diminta:** {{ $permohonan->layanan_dibutuhkan }}
- **Tanggal Pengajuan:** {{ $permohonan->created_at->format('d F Y, H:i') }}

Anda dapat melacak status permohonan Anda kapan saja melalui tautan di bawah ini dengan menggunakan Nomor Registrasi di atas.

<x-mail::button :url="route('status.index')">
Lacak Status Layanan
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
