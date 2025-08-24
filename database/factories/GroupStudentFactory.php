<?php

namespace Database\Factories;

use App\Models\GroupStudent;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupStudentFactory extends Factory
{
    protected $model = GroupStudent::class;

    public function definition(): array
    {
        $year = $this->faker->numberBetween(2020, 2025);
        $rollNumber = $year . $this->faker->unique()->numberBetween(100001, 199999);
        
        return [
            'group_id' => Group::factory(),
            'student_id' => $rollNumber,
            'student_name' => $this->faker->name(),
            'student_email' => $this->faker->optional(0.8)->safeEmail(),
        ];
    }

    public function forGroup(Group $group): static
    {
        return $this->state(fn (array $attributes) => [
            'group_id' => $group->id,
        ]);
    }

    public function withEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'student_email' => $this->faker->safeEmail(),
        ]);
    }

    public function withoutEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'student_email' => null,
        ]);
    }

    public function batch(int $year): static
    {
        return $this->state(function (array $attributes) use ($year) {
            $rollNumber = $year . $this->faker->unique()->numberBetween(100001, 199999);
            return [
                'student_id' => $rollNumber,
            ];
        });
    }

    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'student_name' => $this->faker->name('male'),
        ]);
    }

    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'student_name' => $this->faker->name('female'),
        ]);
    }
}