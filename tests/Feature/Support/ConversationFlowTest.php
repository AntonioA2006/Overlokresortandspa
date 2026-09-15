<?php

namespace Tests\Feature\Support;

use App\Enums\ConversationStatus;
use App\Enums\UserRole;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_support_and_send_a_message(): void
    {
        $guest = User::factory()->create(['role' => UserRole::Guest]);

        $this->actingAs($guest)
            ->get(route('guest.support.index'))
            ->assertSee('Chat con soporte');

        $conversation = Conversation::query()->where('user_id', $guest->id)->first();
        $this->assertNotNull($conversation);

        $this->actingAs($guest)
            ->post(route('guest.support.messages', $conversation), [
                'body' => '¿A qué hora es el check-in?',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $guest->id,
            'body' => '¿A qué hora es el check-in?',
        ]);
        $this->assertSame(ConversationStatus::Waiting, $conversation->fresh()->status);
    }

    public function test_support_can_reply_and_close_a_conversation(): void
    {
        $guest = User::factory()->create(['role' => UserRole::Guest, 'name' => 'Huésped Demo']);
        $agent = User::factory()->create(['role' => UserRole::Support]);
        $conversation = Conversation::query()->create([
            'user_id' => $guest->id,
            'status' => ConversationStatus::Waiting,
        ]);
        Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $guest->id,
            'body' => 'Necesito toallas extra.',
        ]);

        $this->actingAs($agent)
            ->get(route('support.dashboard'))
            ->assertSee('Huésped Demo')
            ->assertSee('Necesito toallas extra.');

        $this->actingAs($agent)
            ->post(route('support.conversations.reply', $conversation), [
                'body' => 'En camino a tu habitación.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $agent->id,
            'body' => 'En camino a tu habitación.',
        ]);

        $this->actingAs($agent)
            ->post(route('support.conversations.close', $conversation))
            ->assertRedirect(route('support.dashboard'));

        $this->assertSame(ConversationStatus::Closed, $conversation->fresh()->status);
    }

    public function test_guest_cannot_view_another_guests_conversation(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Guest]);
        $intruder = User::factory()->create(['role' => UserRole::Guest]);
        $conversation = Conversation::query()->create([
            'user_id' => $owner->id,
            'status' => ConversationStatus::Open,
        ]);

        $this->actingAs($intruder)
            ->post(route('guest.support.messages', $conversation), [
                'body' => 'intento ajeno',
            ])
            ->assertForbidden();
    }
}
