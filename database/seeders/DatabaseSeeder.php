<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Project;
use App\Models\Task;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear 6 usuarios
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'user@correo.com',
        'password' => bcrypt('password'),
    ]);    

    $users = User::factory(5)->create();
 
    // Crear 10 proyectos asignando dueños aleatorios de los usuarios creados
    $projectsTest = Project::factory(4)
        ->for($user)
        ->create();

    $projects = Project::factory(6)
        ->for($users->random())
        ->create();
        
    // Asignar miembros a proyectos aleatoriamente (belongsToMany)
    foreach ($projects as $project) {
        $project->members()->attach(
            $users->random(rand(1, 3))->pluck('id')->toArray()
        );
    }

    // Asignar miembros a proyectos aleatoriamente (belongsToMany)
    foreach ($projectsTest as $project) {
        $project->members()->attach(
            $users->random(rand(1, 3))->pluck('id')->toArray()
        );
    }

    // Crear 10 tareas asignadas a proyectos y usuarios existentes
    Task::factory(10)
        ->make() // crear instancia sin guardar
        ->each(function ($task) use ($projectsTest, $users) {
            $project = $projectsTest->random();
            $task->project_id = $project->id;
            // elegir usuario miembro del proyecto para asignar tarea
            $memberIds = $project->members->pluck('id')->toArray();
            $task->user_id = $users->whereIn('id', $memberIds)->random()->id;
            $task->save();
        });

    // Crear 10 tareas asignadas a proyectos y usuarios existentes
    Task::factory(10)
        ->make() // crear instancia sin guardar
        ->each(function ($task) use ($projects, $users) {
            $project = $projects->random();
            $task->project_id = $project->id;
            // elegir usuario miembro del proyecto para asignar tarea
            $memberIds = $project->members->pluck('id')->toArray();
            $task->user_id = $users->whereIn('id', $memberIds)->random()->id;
            $task->save();
        });

    }
}
