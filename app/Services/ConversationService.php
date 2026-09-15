<?php

namespace App\Services;

use App\Enums\ConversationStatus;
use App\Enums\UserRole;
use App\Exceptions\ConversationException;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConversationService
{
    public function __construct(
        private NotificationService $notificationService,
    ) {}

    public function openForGuest(User $user): Conversation
    {
        $existing = Conversation::query()
            ->whereBelongsTo($user)
            ->whereIn('status', [ConversationStatus::Open, ConversationStatus::Waiting])
            ->latest('updated_at')
            ->first();

        if ($existing !== null) {
            return $existing->loadMissing(['messages.sender', 'supportAgent', 'user']);
        }

        return Conversation::query()->create([
            'user_id' => $user->id,
            'status' => ConversationStatus::Open,
        ])->load(['messages.sender', 'user']);
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function inboxForStaff(): Collection
    {
        return Conversation::query()
            ->with(['user', 'supportAgent', 'latestMessage'])
            ->where('status', '!=', ConversationStatus::Closed)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();
    }

    public function reply(User $sender, Conversation $conversation, string $body): Message
    {
        if ($conversation->status === ConversationStatus::Closed) {
            throw new ConversationException(__('support.errors.closed'));
        }

        return DB::transaction(function () use ($sender, $conversation, $body): Message {
            /** @var Conversation $locked */
            $locked = Conversation::query()
                ->whereKey($conversation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === ConversationStatus::Closed) {
                throw new ConversationException(__('support.errors.closed'));
            }

            $message = Message::query()->create([
                'conversation_id' => $locked->id,
                'sender_id' => $sender->id,
                'body' => $body,
            ]);

            $nextStatus = $sender->hasRole(UserRole::Support, UserRole::Admin)
                ? ConversationStatus::Open
                : ConversationStatus::Waiting;

            $updates = ['status' => $nextStatus];

            if ($sender->hasRole(UserRole::Support, UserRole::Admin) && $locked->support_agent_id === null) {
                $updates['support_agent_id'] = $sender->id;
            }

            $locked->forceFill($updates)->save();

            $this->notifyParticipants($locked->refresh()->loadMissing(['user', 'supportAgent']), $sender, $body);

            return $message->load('sender');
        });
    }

    public function close(User $staff, Conversation $conversation): Conversation
    {
        if (! $staff->hasRole(UserRole::Support, UserRole::Admin)) {
            throw new ConversationException(__('support.errors.forbidden'));
        }

        if ($conversation->status === ConversationStatus::Closed) {
            return $conversation;
        }

        $conversation->forceFill([
            'status' => ConversationStatus::Closed,
            'closed_at' => now(),
            'support_agent_id' => $conversation->support_agent_id ?? $staff->id,
        ])->save();

        return $conversation->refresh();
    }

    /**
     * @return Collection<int, Message>
     */
    public function messages(Conversation $conversation): Collection
    {
        return $conversation->messages()->with('sender')->get();
    }

    public function markMessagesRead(Conversation $conversation, User $reader): void
    {
        Message::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    private function notifyParticipants(Conversation $conversation, User $sender, string $body): void
    {
        $preview = Str::limit($body, 120);

        if ($sender->id === $conversation->user_id) {
            User::query()
                ->whereIn('role', [UserRole::Support->value, UserRole::Admin->value])
                ->get()
                ->each(function (User $staff) use ($conversation, $preview): void {
                    $this->notificationService->notifySupportMessage(
                        $staff,
                        $conversation->id,
                        $preview,
                    );
                });

            return;
        }

        if ($conversation->user !== null) {
            $this->notificationService->notifySupportMessage(
                $conversation->user,
                $conversation->id,
                $preview,
            );
        }
    }
}
