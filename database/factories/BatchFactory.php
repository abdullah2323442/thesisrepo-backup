<?php

namespace Database\Factories;

use App\Models\Batch;
use Illuminate\Database\Eloquent\Factories\Factory;

class BatchFactory extends Factory
{
    protected $model = Batch::class;

    public function definition(): array
    {
        $batchNumber = $this->faker->numberBetween(2018, 2025);
        
        return [
            'batch_number' => $batchNumber,
            'program_id' => $this->faker->numberBetween(1, 5),
            'batch_name' => $this->faker->optional(0.7)->randomElement([
                "CS Batch {$batchNumber}",
                "Computer Science Batch {$batchNumber}",
                "CSE Batch {$batchNumber}",
                "Batch {$batchNumber}",
            ]),
            'is_active' => $this->faker->boolean(75), // 75% chance of being active
            'description' => $this->faker->optional(0.6)->sentence(),
            'last_synced_at' => $this->faker->optional(0.8)->dateTimeThisYear(),
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

    public function withName(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'batch_name' => "CS Batch {$attributes['batch_number']}",
            ];
        });
    }

    public function withoutName(): static
    {
        return $this->state(fn (array $attributes) => [
            'batch_name' => null,
        ]);
    }

    public function year(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'batch_number' => $year,
            'batch_name' => "CS Batch {$year}",
        ]);
    }
}