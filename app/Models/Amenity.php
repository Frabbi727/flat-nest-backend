<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    protected $fillable = ['name', 'label'];

    public function listings()
    {
        return $this->belongsToMany(Listing::class, 'listing_amenity');
    }
}