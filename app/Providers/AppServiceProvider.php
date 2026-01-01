<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User; // Pastikan ini ada
use Illuminate\Support\Facades\Gate; // Pastikan ini ada

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
         // Daftarkan Gate baru
    Gate::define('manage-users', function (User $user) {
        return $user->role === 'admin';
    });
    }
};
