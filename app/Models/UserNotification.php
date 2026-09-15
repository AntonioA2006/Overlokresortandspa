<?php

namespace App\Models;

use App\Enums\NotificationType;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    protected $fillable = [
        'user_id',
        'reservation_id',
        'type',
        'title',
        'message',
        'dedupe_key',
        'read_at',
        'data',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'read_at' => 'datetime',
            'data' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    public function actionUrl(?User $actor = null): ?string
    {
        if ($this->reservation_id !== null) {
            return route('guest.reservations.show', $this->reservation_id);
        }

        $conversationId = $this->data['conversation_id'] ?? null;

        if (! is_numeric($conversationId)) {
            return null;
        }

        $actor ??= $this->user;

        if ($actor?->hasRole(UserRole::Support, UserRole::Admin)) {
            return route('support.conversations.show', (int) $conversationId);
        }

        return route('guest.support.index');
    }
}
