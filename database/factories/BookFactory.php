<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->words(random_int(2, 4), true));

        return [
            'title' => $title,
            'author' => fake()->name(),
            'slug' => Str::slug($title),
            'description' => fake()->paragraphs(3, true),
            'price' => fake()->randomFloat(2, 2.99, 29.99),
            'category_id' => Category::factory(),
            'cover_path' => null,
            'file_path' => 'ebooks/placeholder.pdf',
            'file_format' => 'pdf',
            'accent_color' => fake()->hexColor(),
            'status' => 'draft',
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }
}
