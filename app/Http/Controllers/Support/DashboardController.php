<?php

namespace App\Http\Controllers\Support;

use App\Exceptions\ConversationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Conversation;
use App\Services\ConversationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(ConversationService $conversationService): View
    {
        $this->authorize('viewAny', Conversation::class);

        return view('support.dashboard', [
            'hotelName' => config('overlook.hotel_name'),
            'conversations' => $conversationService->inboxForStaff(),
        ]);
    }

    public function show(
        Request $request,
        Conversation $conversation,
        ConversationService $conversationService,
    ): View {
        $this->authorize('view', $conversation);

        $conversationService->markMessagesRead($conversation, $request->user());

        return view('support.show', [
            'hotelName' => config('overlook.hotel_name'),
            'conversation' => $conversation->load(['user', 'supportAgent', 'messages.sender']),
        ]);
    }

    public function reply(
        StoreMessageRequest $request,
        Conversation $conversation,
        ConversationService $conversationService,
    ): RedirectResponse {
        $this->authorize('reply', $conversation);

        try {
            $conversationService->reply(
                $request->user(),
                $conversation,
                $request->validated('body'),
            );
        } catch (ConversationException $exception) {
            return back()->with('support_error', $exception->getMessage());
        }

        return back()->with('status', __('support.reply_sent'));
    }

    public function close(
        Request $request,
        Conversation $conversation,
        ConversationService $conversationService,
    ): RedirectResponse {
        $this->authorize('close', $conversation);

        try {
            $conversationService->close($request->user(), $conversation);
        } catch (ConversationException $exception) {
            return back()->with('support_error', $exception->getMessage());
        }

        return redirect()
            ->route('support.dashboard')
            ->with('status', __('support.conversation_closed'));
    }
}
