<?php

namespace App\Http\Controllers\Guest;

use App\Exceptions\ConversationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Conversation;
use App\Services\ConversationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(Request $request, ConversationService $conversationService): View
    {
        $conversation = $conversationService->openForGuest($request->user());

        $this->authorize('view', $conversation);

        $conversationService->markMessagesRead($conversation, $request->user());

        return view('guest.support.index', [
            'conversation' => $conversation->load(['messages.sender', 'supportAgent']),
        ]);
    }

    public function store(
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

        return back()->with('status', __('support.message_sent'));
    }
}
