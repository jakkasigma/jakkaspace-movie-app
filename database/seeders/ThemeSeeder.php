<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            [
                'slug' => 'marvel-superhero',
                'name' => 'Marvel Superhero',
                'avatar_border_css' => 'linear-gradient(135deg, #e23636, #f78f3f)',
                'accent_color' => '#e23636',
                'badge_icon' => '🦸',
            ],
            [
                'slug' => 'studio-ghibli',
                'name' => 'Studio Ghibli',
                'avatar_border_css' => 'linear-gradient(135deg, #2d8a4e, #f5e6c8)',
                'accent_color' => '#2d8a4e',
                'badge_icon' => '🐱',
            ],
            [
                'slug' => 'cyberpunk',
                'name' => 'Cyberpunk',
                'avatar_border_css' => 'linear-gradient(135deg, #ff00ff, #00ffff)',
                'accent_color' => '#ff00ff',
                'badge_icon' => '🤖',
            ],
            [
                'slug' => 'star-wars',
                'name' => 'Star Wars',
                'avatar_border_css' => 'linear-gradient(135deg, #000000, #2a6fdb)',
                'accent_color' => '#2a6fdb',
                'badge_icon' => '🌟',
            ],
            [
                'slug' => 'horror',
                'name' => 'Horror',
                'avatar_border_css' => 'linear-gradient(135deg, #1a1a1a, #8b0000)',
                'accent_color' => '#cc0000',
                'badge_icon' => '👻',
            ],
            [
                'slug' => 'retro-80s',
                'name' => 'Retro 80s',
                'avatar_border_css' => 'linear-gradient(135deg, #ff6ec7, #00bfff)',
                'accent_color' => '#ff6ec7',
                'badge_icon' => '🌴',
            ],
        ];

        foreach ($themes as $theme) {
            Theme::firstOrCreate(
                ['slug' => $theme['slug']],
                $theme
            );
        }
    }
}
