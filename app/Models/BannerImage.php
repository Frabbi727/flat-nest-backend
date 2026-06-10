<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BannerImage extends Model
{
    protected $fillable = ['banner_id', 'image_path', 'target_url', 'order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class);
    }

    protected static function booted()
    {
        static::deleted(function ($bannerImage) {
            if ($bannerImage->image_path) {
                Storage::disk('public')->delete($bannerImage->image_path);
            }
        });
    }
}
