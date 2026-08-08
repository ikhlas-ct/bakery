<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'foto_profil',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }

    public function pemilik(): HasOne
    {
        return $this->hasOne(Pemilik::class);
    }

    public function produsen(): HasOne
    {
        return $this->hasOne(Produsen::class);
    }
}
