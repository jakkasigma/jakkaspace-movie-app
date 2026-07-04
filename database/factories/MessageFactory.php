<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'user_id' => User::factory(),
            'type' => 'text',
            'body' => fake()->sentence(),
            'tmdb_id' => null,
            'review_id' => null,
        ];
    }

    public function filmShare(int $tmdbId): static
    {
        return $this->state([
            'type' => 'film_share',
            'body' => null,
            'tmdb_id' => $tmdbId,
        ]);
    }
}
