<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'email', 'password_hash', 'name', 'phone', 'google_id',
        'role', 'date_of_birth', 'avatar_url', 'is_complete',
        'last_lat', 'last_lng',
    ];

    protected $hidden = ['password_hash'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($model) => $model->id = (string) Str::uuid());
        static::deleting(function (User $user) {
            // Delete each listing through Eloquent so the listing's own
            // deleting hook fires and cleans up the photo directories
            $user->listings()->each(fn (Listing $listing) => $listing->delete());

            // Delete avatar file from storage
            if ($user->avatar_url) {
                $path = Str::after($user->avatar_url, Storage::disk('public')->url(''));
                Storage::disk('public')->delete(ltrim($path, '/'));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_complete' => 'boolean',
        ];
    }

    // Map password_hash → password for Laravel auth
    public function getAuthPassword(): string
    {
        return $this->password_hash ?? '';
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
