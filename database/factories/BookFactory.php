<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use App\Models\Grade;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory {
    protected $model = Book::class;

    public function definition(): array {
        // grade_id is a required FK — use first existing grade or create a stub
        $gradeId = Grade::first()?->id ?? Grade::create([
            'sequence' => 1, 'name' => 'Grade 1', 'active' => 1, 'year' => 2023,
        ])->id;

        return [
            'title' => $this->faker->sentence(3),
            'isbn' => $this->faker->unique()->isbn13,
            'author_id' => Author::factory(),
            'publisher_id' => Publisher::factory(),
            'grade_id' => $gradeId,
            'genre' => $this->faker->randomElement(['Fiction', 'Non-Fiction', 'Science', 'History', 'Mathematics']),
            'publication_year' => $this->faker->numberBetween(2000, 2025),
            'language' => 'English',
            'format' => 'Paperback',
            'pages' => $this->faker->numberBetween(50, 500),
            'quantity' => $this->faker->numberBetween(1, 10),
            'status' => 'available',
            'price' => $this->faker->randomFloat(2, 20, 200),
            'currency' => 'BWP',
            'dewey_decimal' => $this->faker->numerify('###.##'),
            'location' => $this->faker->randomElement(['Main Library', 'Reference Section', 'Junior Section']),
        ];
    }
}
