<?php

namespace App\Listeners;

use App\Events\PermohonanDibuat;
use App\Mail\PermohonanDiterimaMail; // Panggil Mailable kita
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail; // Panggil Mail Facade


class KirimNotifikasiPermohonan
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PermohonanDibuat $event): void
    {

        // Kirim email ke alamat email pemohon
        Mail::to($event->permohonan->email)->send(new PermohonanDiterimaMail($event->permohonan));
    }
}
