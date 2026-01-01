<?php

namespace App\Events;

use App\Models\Permohonan; // Panggil model
use Illuminate\Broadcasting\Channel;
// ... use lainnya
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class PermohonanDibuat
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $permohonan;

    /**
     * Create a new event instance.
     */
    public function __construct(Permohonan $permohonan)
    {
        $this->permohonan = $permohonan;
    }

    // ... sisa file
}
