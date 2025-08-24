<?php

namespace Database\Factories;

use App\Models\AreaOfInterest;
use Illuminate\Database\Eloquent\Factories\Factory;

class AreaOfInterestFactory extends Factory
{
    protected $model = AreaOfInterest::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Machine Learning',
                'Web Development',
                'Data Science',
                'Artificial Intelligence',
                'Cybersecurity',
                'Mobile App Development',
                'Cloud Computing',
                'Internet of Things',
                'Blockchain Technology',
                'Computer Vision',
                'Natural Language Processing',
                'Software Engineering',
                'Database Management',
                'Network Security',
                'Human-Computer Interaction'
            ]),
            'description' => $this->faker->paragraph(2),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}