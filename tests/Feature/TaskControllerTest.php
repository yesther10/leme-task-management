<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $accessibleProject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->accessibleProject = Project::factory()->create(['user_id' => $this->user->id]);
    }

    // --- TESTE INDEX AJAX (Filtros y Relevancia) ---
    
    public function test_index_shows_relevant_tasks_and_applies_status_filter()
    {
        // ARRANGE
        // Tarea Relevante (Creador) con Status 1
        $task1 = Task::factory()->create([
            'project_id' => $this->accessibleProject->id, 
            'user_id' => $this->user->id, 
            'status' => TaskStatus::Pending->value,
            'title' => 'Tarea Pendiente A'
        ]);
        
        // Tarefa Relevante (Criador) com Status 3 (Concluído)
        $task2 = Task::factory()->create([
            'project_id' => $this->accessibleProject->id, 
            'user_id' => $this->user->id, 
            'status' => TaskStatus::Completed->value,
            'title' => 'Tarea Completada B'
        ]);
        
        // TareFa Irrelevante (Não deveria aparecer)
        $irrelevantProject = Project::factory()->create();
        Task::factory()->create([
            'project_id' => $irrelevantProject->id, 
            'user_id' => User::factory()->create(), 
            'status' => TaskStatus::Pending->value,
            'title' => 'Tarea Irrelevante C'
        ]);

        // --- ACT 1: Sin filtros (Simular solicitação AJAX del DataTables) ---
        $response = $this->actingAs($this->user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest', // CHAVE: Simular solicitação AJAX
            ])
            ->json('GET', route('tasks.index'), [
                'draw' => 1,
                // Yajra DataTables usa el parámetro 'columns[0][search][value]' para la búsqueda global
            ]);

        // ASSERT 1
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data'
        ]);
        $response->assertJsonFragment(['title' => 'Tarea Pendiente A']);
        $response->assertJsonFragment(['title' => 'Tarea Completada B']);
        $response->assertJsonMissing(['title' => 'Tarea Irrelevante C']);

        // --- ACT 2: Filtrar por Status 1 (Pending) ---
        $response = $this->actingAs($this->user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->json('GET', route('tasks.index'), [
                'draw' => 2,
                'status' => TaskStatus::Pending->value // Aplicar filtro
            ]);

        // ASSERT 2
        $response->assertStatus(200);
        // Verifica que a Tarefa Pendiente A está no JSON e a Tarea Completada B não está
        $response->assertJsonFragment(['title' => 'Tarea Pendiente A']);
        $response->assertJsonMissing(['title' => 'Tarea Completada B']);
    }

    // --- TESTE CREATE/EDIT (Projetos Acessível) ---

    public function test_create_passes_only_accessible_projects_to_view()
    {
        $memberProject = Project::factory()->create();
        $memberProject->members()->attach($this->user->id);
        $irrelevantProject = Project::factory()->create();

        $response = $this->actingAs($this->user)->get(route('tasks.create'));

        $projects = $response->viewData('projects');
        $this->assertCount(2, $projects); // accessibleProject y memberProject
        $this->assertTrue($projects->contains('id', $this->accessibleProject->id));
        $this->assertTrue($projects->contains('id', $memberProject->id));
        $this->assertFalse($projects->contains('id', $irrelevantProject->id));
    }

    // --- TESTE STORE ---
    
    public function test_task_can_be_stored_with_multiple_files()
    {
        Storage::fake('public');
        $file1 = UploadedFile::fake()->create('doc1.pdf', 100);
        $file2 = UploadedFile::fake()->create('image.pdf', 50);

        $formData = Task::factory()->make([
            'project_id' => $this->accessibleProject->id,
            'user_id' => $this->user->id,
            'files' => [$file1, $file2], // Campo 'files' para el request
        ])->toArray();
        $formData['files'] = [$file1, $file2];

        $response = $this->actingAs($this->user)->post(route('tasks.store'), $formData);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', ['title' => $formData['title']]);
        
        $task = Task::where('title', $formData['title'])->first();
        $this->assertCount(2, $task->files);
        $this->assertTrue(Storage::disk('public')->exists('task_files/' . $file1->hashName()));
    }

    // --- TESTE MARK COMPLETE ---

    public function test_task_can_be_marked_as_complete()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id, 
            'status' => TaskStatus::Pending->value]);

        $response = $this->actingAs($this->user)->patch(route('tasks.complete', $task));

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => TaskStatus::Completed->value]);
    }
}