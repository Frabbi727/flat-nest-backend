<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Listing extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'owner_id', 'title', 'area', 'road_and_house', 'type',
        'price', 'deposit', 'beds', 'baths', 'size', 'description',
        'coord_x', 'coord_y', 'amenities', 'status', 'views',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($model) => $model->id = (string) Str::uuid());
    }

    protected function casts(): array
    {
        return ['amenities' => 'array'];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function photos()
    {
        return $this->hasMany(ListingPhoto::class)->orderBy('position');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function savedBy()
    {
        return $this->belongsToMany(User::class, 'wishlists');
    }
}