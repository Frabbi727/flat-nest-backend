<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banner extends Model
{
    protected $fillable = ['title', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(BannerImage::class);
    }

    protected static function booted()
    {
        static::saving(function ($banner) {
            if ($banner->is_active) {
                // Ensure only one banner is active
                static::where('id', '!=', $banner->id)->update(['is_active' => false]);
            }
        });
    }
}
