<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelType extends Model
{
    protected $fillable = ['name', 'label'];

    public function hostels()
    {
        return $this->hasMany(Hostel::class);
    }
}
