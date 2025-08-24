<?php

namespace Database\Factories;

use App\Models\Supervisor;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupervisorFactory extends Factory
{
    protected $model = Supervisor::class;

    public function definition(): array
    {
        $designations = [
            'Professor',
            'Associate Professor',
            'Assistant Professor',
            'Lecturer',
            'Senior Lecturer'
        ];

        return [
            'api_id' => $this->faker->unique()->numberBetween(100, 9999),
            'fullname' => 'Dr. ' . $this->faker->name(),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'email' => $this->faker->unique()->safeEmail(),
            'designation' => $this->faker->randomElement($designations),
            'department' => 'Computer Science & Engineering',
            'thesis_limit' => $this->faker->numberBetween(3, 8),
            'is_active' => $this->faker->boolean(85), // 85% chance of being active
            'last_synced_at' => $this->faker->optional()->dateTimeThisYear(),
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

    public function professor(): static
    {
        return $this->state(fn (array $attributes) => [
            'designation' => 'Professor',
            'thesis_limit' => $this->faker->numberBetween(5, 8),
        ]);
    }

    public function associateProfessor(): static
    {
        return $this->state(fn (array $attributes) => [
            'designation' => 'Associate Professor',
            'thesis_limit' => $this->faker->numberBetween(4, 6),
        ]);
    }

    public function assistantProfessor(): static
    {
        return $this->state(fn (array $attributes) => [
            'designation' => 'Assistant Professor',
            'thesis_limit' => $this->faker->numberBetween(3, 5),
        ]);
    }

    public function lecturer(): static
    {
        return $this->state(fn (array $attributes) => [
            'designation' => 'Lecturer',
            'thesis_limit' => $this->faker->numberBetween(2, 4),
        ]);
    }
}