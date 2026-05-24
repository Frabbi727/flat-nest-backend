<?php

namespace App\Models;

use App\Enums\HostelStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Hostel extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'owner_id', 'hostel_type_id', 'name', 'description', 'gender_policy',
        'price', 'price_unit', 'advance',
        'meal_included', 'meal_price', 'curfew_time',
        'floor', 'building', 'landmark', 'landmark_distance', 'address',
        'division_id', 'district_id', 'upazila_id', 'union_id',
        'coord_x', 'coord_y',
        'owner_name', 'owner_phone',
        'rules',
        'status', 'is_verified', 'views', 'rejection_reason',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($model) => $model->id = (string) Str::uuid());
        static::deleting(fn (Hostel $hostel) =>
            Storage::disk('public')->deleteDirectory('hostels/' . $hostel->id)
        );
    }

    protected function casts(): array
    {
        return [
            'status'        => HostelStatus::class,
            'meal_included' => 'boolean',
            'is_verified'   => 'boolean',
            'price'         => 'integer',
            'advance'       => 'integer',
            'meal_price'    => 'integer',
            'views'         => 'integer',
            'coord_x'       => 'float',
            'coord_y'       => 'float',
            'rules'         => 'array',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function type()
    {
        return $this->belongsTo(HostelType::class, 'hostel_type_id');
    }

    public function photos()
    {
        return $this->hasMany(HostelPhoto::class)->orderBy('position');
    }

    public function rooms()
    {
        return $this->hasMany(HostelRoom::class)->orderBy('position');
    }

    public function seats()
    {
        return $this->hasMany(HostelSeat::class);
    }

    public function reviews()
    {
        return $this->hasMany(HostelReview::class)->latest();
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'hostel_amenity');
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

    public function getVacantSeatsCountAttribute(): int
    {
        return $this->seats()->where('status', 'vacant')->count();
    }

    public function getTotalSeatsCountAttribute(): int
    {
        return $this->seats()->count();
    }

    public function getAverageRatingAttribute(): float
    {
        return (float) $this->reviews()->avg('rating') ?? 0;
    }
}
