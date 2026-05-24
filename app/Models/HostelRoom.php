<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HostelRoom extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['hostel_id', 'name', 'position'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($model) => $model->id = (string) Str::uuid());
    }

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function seats()
    {
        return $this->hasMany(HostelSeat::class, 'room_id')->orderBy('seat_number');
    }
}
