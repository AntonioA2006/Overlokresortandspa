<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ConversationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request, ConversationService $conversationService): JsonResponse
    {
        $this->authorize('viewAny', Conversation::class);

        return response()->json([
            'conversations' => $conversationService->inboxForStaff()->map(
                fn (Conversation $conversation): array => $this->serializeConversation($conversation),
            )->values(),
        ]);
    }

    public function messages(
        Request $request,
        Conversation $conversation,
        ConversationService $conversationService,
    ): JsonResponse {
        $this->authorize('view', $conversation);

        $conversationService->markMessagesRead($conversation, $request->user());

        return response()->json([
            'messages' => $conversationService->messages($conversation)->map(
                fn (Message $message): array => $this->serializeMessage($message),
            )->values(),
        ]);
    }

    public function storeMessage(
        StoreMessageRequest $request,
        Conversation $conversation,
        ConversationService $conversationService,
    ): JsonResponse {
        $this->authorize('reply', $conversation);

        try {
            $message = $conversationService->reply(
                $request->user(),
                $conversation,
                $request->validated('body'),
            );
        } catch (ConversationException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => $this->serializeMessage($message),
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeConversation(Conversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'status' => $conversation->status->value,
            'user_name' => $conversation->user?->name,
            'updated_at' => $conversation->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender?->name,
            'created_at' => $message->created_at?->toIso8601String(),
            'read_at' => $message->read_at?->toIso8601String(),
        ];
    }
}
