<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Project;
use App\Models\User;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'priority' => TaskPriority::cases()[array_rand(TaskPriority::cases())]->value,
            'status' => TaskStatus::cases()[array_rand(TaskStatus::cases())]->value,
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
        ];
    }
}
