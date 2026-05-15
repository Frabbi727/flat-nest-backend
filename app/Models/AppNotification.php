<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['user_id', 'kind', 'title', 'body', 'reference_id', 'is_unread'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($model) => $model->id = (string) Str::uuid());
    }

    protected function casts(): array
    {
        return ['is_unread' => 'boolean'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}