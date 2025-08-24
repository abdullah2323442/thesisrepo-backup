<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use App\Models\AreaOfInterest;
use App\Models\Supervisor;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'name' => 'Group ' . $this->faker->numberBetween(1, 50),
            'batch_number' => $this->faker->numberBetween(2020, 2025),
            'advisor_id' => User::factory(),
            'max_students' => $this->faker->numberBetween(2, 4),
            'area_of_interest_id' => $this->faker->optional(0.7)->randomElement([
                AreaOfInterest::factory(),
                null
            ]),
            'supervisor_id' => $this->faker->optional(0.5)->randomElement([
                Supervisor::factory(),
                null
            ]),
            'is_manual_assignment' => $this->faker->boolean(20), // 20% chance of manual assignment
            'assignment_priority' => $this->faker->optional(0.3)->numberBetween(1, 10),
            'assigned_at' => $this->faker->optional(0.4)->dateTimeThisYear(),
        ];
    }

    public function withAdvisor(User $advisor = null): static
    {
        return $this->state(fn (array $attributes) => [
            'advisor_id' => $advisor ? $advisor->id : User::factory(),
        ]);
    }

    public function withAreaOfInterest(AreaOfInterest $area = null): static
    {
        return $this->state(fn (array $attributes) => [
            'area_of_interest_id' => $area ? $area->id : AreaOfInterest::factory(),
        ]);
    }

    public function withSupervisor(Supervisor $supervisor = null): static
    {
        return $this->state(fn (array $attributes) => [
            'supervisor_id' => $supervisor ? $supervisor->id : Supervisor::factory(),
            'assigned_at' => now(),
        ]);
    }

    public function withoutSupervisor(): static
    {
        return $this->state(fn (array $attributes) => [
            'supervisor_id' => null,
            'assigned_at' => null,
        ]);
    }

    public function manualAssignment(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_manual_assignment' => true,
            'assignment_priority' => $this->faker->numberBetween(1, 5),
        ]);
    }

    public function lotteryEligible(): static
    {
        return $this->state(fn (array $attributes) => [
            'supervisor_id' => null,
            'is_manual_assignment' => false,
            'area_of_interest_id' => AreaOfInterest::factory(),
        ]);
    }

    public function full(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_students' => 3,
        ])->afterCreating(function (Group $group) {
            \App\Models\GroupStudent::factory()->count(3)->create([
                'group_id' => $group->id,
            ]);
        });
    }

    public function batch(int $batchNumber): static
    {
        return $this->state(fn (array $attributes) => [
            'batch_number' => $batchNumber,
        ]);
    }
}