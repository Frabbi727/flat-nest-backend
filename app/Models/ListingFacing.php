<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingFacing extends Model
{
    public $timestamps = false;

    protected $fillable = ['label', 'slug'];

    public function listings()
    {
        return $this->hasMany(Listing::class, 'facing_id');
    }
}
