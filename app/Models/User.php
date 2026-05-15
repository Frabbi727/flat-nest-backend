<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'email', 'password_hash', 'name', 'phone',
        'role', 'date_of_birth', 'avatar_url', 'is_complete',
    ];

    protected $hidden = ['password_hash'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($model) => $model->id = (string) Str::uuid());
    }

    protected function casts(): array
    {
        return [
            'is_complete' => 'boolean',
        ];
    }

    public function listings()
    {
        return $this->hasMany(Listing::class, 'owner_id');
    }

    public function wishlist()
    {
        return $this->belongsToMany(Listing::class, 'wishlists');
    }

    public function chatsAsRenter()
    {
        return $this->hasMany(Chat::class, 'renter_id');
    }

    public function chatsAsOwner()
    {
        return $this->hasMany(Chat::class, 'owner_id');
    }

    public function notifications()
    {
        return $this->hasMany(AppNotification::class, 'user_id');
    }

    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }
}