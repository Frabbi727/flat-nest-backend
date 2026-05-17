<?php

namespace App\Models;

use App\Enums\ListingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Listing extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'owner_id', 'listing_type_id', 'title', 'area', 'road_and_house',
        'price', 'deposit', 'beds', 'baths', 'size', 'description',
        'coord_x', 'coord_y', 'status', 'rejection_reason', 'views',
        'division_id', 'district_id', 'upazila_id', 'union_id',
        'available_from', 'floor_no', 'facing_id',
        'road', 'house_name', 'block', 'section',
        'owner_name', 'owner_phone', 'owner_alt_phone', 'owner_email', 'preferred_contact',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($model) => $model->id = (string) Str::uuid());
        static::deleting(fn (Listing $listing) =>
            Storage::disk(config('filesystems.default'))->deleteDirectory('listings/' . $listing->id)
        );
    }

    protected function casts(): array
    {
        return [
            'status'         => ListingStatus::class,
            'available_from' => 'date:Y-m-d',
            'floor_no'       => 'integer',
            'price'          => 'integer',
            'deposit'        => 'integer',
            'beds'           => 'integer',
            'baths'          => 'integer',
            'size'           => 'integer',
            'views'          => 'integer',
            'coord_x'        => 'float',
            'coord_y'        => 'float',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function facing()
    {
        return $this->belongsTo(ListingFacing::class, 'facing_id');
    }

    public function listingType()
    {
        return $this->belongsTo(\App\Models\ListingType::class);
    }

    public function photos()
    {
        return $this->hasMany(ListingPhoto::class)->orderBy('position');
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'listing_amenity');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function savedBy()
    {
        return $this->belongsToMany(User::class, 'wishlists');
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function upazila()
    {
        return $this->belongsTo(Upazila::class);
    }

    public function union()
    {
        return $this->belongsTo(Union::class);
    }
}