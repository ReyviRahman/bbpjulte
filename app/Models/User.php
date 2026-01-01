<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute; // Tambahkan ini
use Illuminate\Support\Facades\Storage; // Tambahkan ini

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nip',
        'email',
        'password',
        'role', // Menambahkan kolom role untuk peran pengguna
        'profile_photo_path', // Tambahkan ini
        'last_login_at', // Tambahkan ini
        'last_activity_at', // Tambahkan ini


    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime', // Tambahkan ini
            'last_activity_at' => 'datetime', // Tambahkan ini
        ];
    }

    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->profile_photo_path) {
                return Storage::url($this->profile_photo_path);
            }

            // Gunakan UI Avatars sebagai fallback
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
        });
    }

   public function isOnline(): bool
{
    // Cek apakah kolomnya tidak null DAN lebih baru dari 5 menit yang lalu
    return $this->last_activity_at && $this->last_activity_at->gt(now()->subMinutes(5));
}
}
