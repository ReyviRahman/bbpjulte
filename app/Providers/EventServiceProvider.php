<?php

namespace App\Providers;

// Import class Event dan Listener kita
use App\Events\PermohonanDibuat;
use App\Listeners\KirimNotifikasiPermohonan;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // INI ADALAH MAPPING PENTING UNTUK FITUR NOTIFIKASI EMAIL KITA
        PermohonanDibuat::class => [
            KirimNotifikasiPermohonan::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
