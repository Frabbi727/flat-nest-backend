<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListingPhoto extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['listing_id', 'url', 'position'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($model) => $model->id = (string) Str::uuid());
        static::deleting(function (ListingPhoto $photo) {
            $path = Str::after($photo->url, Storage::disk('public')->url(''));
            Storage::disk('public')->delete(ltrim($path, '/'));
        });
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}