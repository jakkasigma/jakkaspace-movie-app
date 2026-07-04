<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

describe('inbox', function (): void {
    it('requires auth to view inbox', function (): void {
        $this->get(route('inbox'))
            ->assertRedirectToRoute('login');
    });

    it('shows empty state when no conversations', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('inbox'))
            ->assertOk()
            ->assertSee('Belum ada pesan');
    });

    it('shows conversations list', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $conv = Conversation::factory()->create(['created_by' => $user->id]);
        $conv->members()->attach([
            $user->id => ['joined_at' => now()],
            $other->id => ['joined_at' => now()],
        ]);

        Message::factory()->create([
            'conversation_id' => $conv->id,
            'user_id' => $user->id,
            'body' => 'Halo dunia!',
        ]);

        $this->actingAs($user)
            ->get(route('inbox'))
            ->assertOk()
            ->assertSee($other->name);
    });

    it('requires auth to view conversation', function (): void {
        $this->get(route('inbox.show', 1))
            ->assertRedirectToRoute('login');
    });

    it('shows 404-style redirect for non-member conversation', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $conv = Conversation::factory()->create(['created_by' => $other->id]);
        $conv->members()->attach($other->id, ['joined_at' => now()]);

        $this->actingAs($user)
            ->get(route('inbox.show', $conv))
            ->assertRedirectToRoute('inbox');
    });

    it('shows messages in a conversation', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $conv = Conversation::factory()->create(['created_by' => $user->id]);
        $conv->members()->attach([
            $user->id => ['joined_at' => now()],
            $other->id => ['joined_at' => now()],
        ]);

        Message::factory()->create([
            'conversation_id' => $conv->id,
            'user_id' => $other->id,
            'body' => 'Pesan dari teman',
        ]);

        $this->actingAs($user)
            ->get(route('inbox.show', $conv))
            ->assertOk()
            ->assertSee('Pesan dari teman');
    });

    it('can send a text message', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $conv = Conversation::factory()->create(['created_by' => $user->id]);
        $conv->members()->attach([
            $user->id => ['joined_at' => now()],
            $other->id => ['joined_at' => now()],
        ]);

        $this->actingAs($user)
            ->post(route('inbox.messages.store', $conv), [
                'type' => 'text',
                'body' => 'Halo ini pesan test',
            ])
            ->assertRedirectToRoute('inbox.show', $conv);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conv->id,
            'user_id' => $user->id,
            'type' => 'text',
            'body' => 'Halo ini pesan test',
        ]);
    });

    it('validates body is required for text messages', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $conv = Conversation::factory()->create(['created_by' => $user->id]);
        $conv->members()->attach([
            $user->id => ['joined_at' => now()],
            $other->id => ['joined_at' => now()],
        ]);

        $this->actingAs($user)
            ->post(route('inbox.messages.store', $conv), ['type' => 'text', 'body' => ''])
            ->assertSessionHasErrors('body');
    });

    it('can send a film share message', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $conv = Conversation::factory()->create(['created_by' => $user->id]);
        $conv->members()->attach([
            $user->id => ['joined_at' => now()],
            $other->id => ['joined_at' => now()],
        ]);

        $this->actingAs($user)
            ->post(route('inbox.messages.store', $conv), [
                'type' => 'film_share',
                'tmdb_id' => 27205,
            ])
            ->assertRedirectToRoute('inbox.show', $conv);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conv->id,
            'user_id' => $user->id,
            'type' => 'film_share',
            'tmdb_id' => 27205,
        ]);
    });

    it('cannot send message to conversation user is not a member of', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $conv = Conversation::factory()->create(['created_by' => $other->id]);
        $conv->members()->attach($other->id, ['joined_at' => now()]);

        $this->actingAs($user)
            ->post(route('inbox.messages.store', $conv), ['type' => 'text', 'body' => 'Nyoba masuk'])
            ->assertRedirectToRoute('inbox');
    });

    it('can start a direct conversation from profile', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create(['username' => 'targetuser']);

        $this->actingAs($user)
            ->post(route('inbox.direct', $other))
            ->assertRedirect();

        $this->assertDatabaseHas('conversations', ['type' => 'direct', 'created_by' => $user->id]);
    });

    it('reuses existing direct conversation', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();

        // First DM
        $this->actingAs($user)->post(route('inbox.direct', $other));
        $countAfterFirst = Conversation::count();

        // Second DM — should reuse
        $this->actingAs($user)->post(route('inbox.direct', $other));
        $countAfterSecond = Conversation::count();

        expect($countAfterFirst)->toBe($countAfterSecond);
    });

    it('cannot start DM with yourself', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('inbox.direct', $user))
            ->assertRedirectToRoute('inbox');
    });
});
