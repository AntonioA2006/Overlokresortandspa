<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckIn extends Model
{
    protected $fillable = [
        'reservation_id',
        'receptionist_id',
        'qr_scanned_at',
        'identity_verified_at',
        'room_delivered_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'qr_scanned_at' => 'datetime',
            'identity_verified_at' => 'datetime',
            'room_delivered_at' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function receptionist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receptionist_id');
    }
}
