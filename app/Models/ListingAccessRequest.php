<?php

namespace App\Models;

use App\Enums\ListingAccessStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingAccessRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'listing_id',
        'requester_id',
        'owner_id',
        'status',
    ];

    protected $casts = [
        'status' => ListingAccessStatus::class,
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
