<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use \App\Models\Traits\HasProfileImage;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'email',
        'password',
        'nomor_hp',
        'gambar',
        'is_active',
        'is_admin',
        'last_login',
        'last_login_ip',
        'last_login_location',
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
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
            'last_login' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Provide backwards compatibility for the default `name` attribute.
     */
    public function getNameAttribute(): string
    {
        return $this->nama;
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->image_url;
    }

    protected function getImageFolder(): string
    {
        return 'admin-photos';
    }

    protected function defaultImage(): string
    {
        return 'assets/default/user.jpg';
    }
}
