<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste se as métricas do painel foram calculadas corretamente.
     * Inclui tarefas próprias, tarefas de projetos de membros e exclui tarefas irrelevantes.
     */
    public function test_dashboard_metrics_are_calculated_correctly_for_relevant_tasks()
    {
        // ARRANGE: Configuração inicial
        $user = User::factory()->create([
            'name' => 'Usuario Test',
            'email' => 'user@correo.com'
        ]);
        $otherUser = User::factory()->create();

        // Projetos
        $ownedProject = Project::factory()->create(['user_id' => $user->id]);
        $memberProject = Project::factory()->create(['user_id' => $otherUser->id]);
        $memberProject->members()->attach($user->id); // Usuario es miembro
        $irrelevantProject = Project::factory()->create(['user_id' => $otherUser->id]);

        // TAREFAS RELEVANTES para $user (Owned o Membro)
        // 1. Tarefa Pendiente (Status 1)
        Task::factory()->create([
            'project_id' => $ownedProject->id, 
            'user_id' => $user->id, 
            'status' => TaskStatus::Pending->value]); // Creador

        Task::factory()->create([
            'project_id' => $memberProject->id, 
            'user_id' => $otherUser->id, 
            'status' => TaskStatus::Pending->value]); // Membro

        // 2. Tarefa Concluida (Overdue)
        Task::factory()->create([
            'project_id' => $ownedProject->id, 
            'user_id' => $user->id, 
            'due_date' => now()->subDays(1), 
            'status' => TaskStatus::Pending->value]);

        // 3. Tarefa Terminada Hoy (Completed)
        Task::factory()->create([
            'project_id' => $ownedProject->id, 
            'user_id' => $user->id, 
            'status' => TaskStatus::Completed->value, 
            'updated_at' => now()]);

        // TAREFAS IRRELEVANTES (No creador ni miembro)
        Task::factory()->create([
            'project_id' => $irrelevantProject->id, 
            'user_id' => $otherUser->id, 
            'status' => TaskStatus::Pending->value]);


        // ACT: Ejecutar el método metrics
        $response = $this->actingAs($user)->get(route('dashboard')); // Asumiendo que esta es la ruta

        // ASSERT: Verificar resultados
        $response->assertStatus(200);
        $response->assertViewIs('dashboard');

        // dd(Project::accessibleByUser($user)->count());
        // $relevantTasks = Task::relevantToUser($user)->get();
        // dd(
        //     $relevantTasks->pluck('status'), 
        //     $relevantTasks->where('status',TaskStatus::Pending->value)->count());
        
        // Verificación de conteos
        $response->assertViewHas('pendingTasksCount', 3); // 2 Pendientes
        $response->assertViewHas('overdueTasksCount', 1); // 1 Concluida
        $response->assertViewHas('activeProjectsCount', 2); // ownedProject y memberProject
        $response->assertViewHas('completedTodayCount', 1); // 1 Terminada
        
        // Verificación de tareas urgentes (High Priority o Overdue)
        // $urgentTasks = $response->viewData('urgentTasks');
        // $this->assertCount(1, $urgentTasks); // 1 Tarefa Concluida
    }
}