<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(2, true);

        return [
            'title' => ['fr' => Str::title($title)],
            'slug' => Str::slug($title),
            'excerpt' => ['fr' => fake()->sentence(10)],
            'status' => PublicationStatus::Draft,
            'sort_order' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => PublicationStatus::Published, 'published_at' => now()->subDay()]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => ['status' => PublicationStatus::Published, 'published_at' => now()->addWeek()]);
    }
}
