<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Amenity extends Model
{
    protected $fillable = [
        'name',
        'icon',
    ];

    public function roomTypes(): BelongsToMany
    {
        return $this->belongsToMany(RoomType::class);
    }
}
