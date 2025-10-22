<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // --- TESTE INDEX AJAX (DataTables) ---

    public function test_index_displays_projects_where_user_is_creator_or_member()
    {
        // ARRANGE
        $ownedProject = Project::factory()->create(['user_id' => $this->user->id]);
        
        $otherUser = User::factory()->create();
        $memberProject = Project::factory()->create(['user_id' => $otherUser->id]);
        $memberProject->members()->attach($this->user->id); // Usuario e membro
        
        $irrelevantProject = Project::factory()->create(['user_id' => $otherUser->id]);

        // --- ACT 1: (Simular solicitação AJAX del DataTables) ---
        $response = $this->actingAs($this->user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest', // CHAVE: Simular solicitação AJAX
            ])
            ->json('GET', route('projects.index'), [
                'draw' => 1,
                // Yajra DataTables usa el parámetro 'columns[0][search][value]' para la búsqueda global
            ]);

        // ASSERT
        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => $ownedProject->title])
                 ->assertJsonFragment(['title' => $memberProject->title])
                 ->assertJsonMissing(['title' => $irrelevantProject->title]); // No debe mostrar
    }

    // --- TESTE STORE ---

    public function test_project_can_be_stored_with_members_and_file()
    {
        Storage::fake('public');
        $member = User::factory()->create();
        $file = UploadedFile::fake()->create('documento.pdf', 100);

        $formData = [
            'title' => 'Nuevo Proyecto Test',
            'description' => 'Descripción del proyecto',
            'start_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'members' => [$member->id],
            'file_path' => $file,
        ];

        $response = $this->actingAs($this->user)->post(route('projects.store'), $formData);

        $response->assertRedirect(route('projects.index'));
        
        $this->assertDatabaseHas('projects', ['title' => 'Nuevo Proyecto Test', 'user_id' => $this->user->id]);
        $this->assertTrue(Storage::disk('public')->exists('project_attachments/' . $file->hashName()));
        
        $project = Project::where('title', 'Nuevo Proyecto Test')->first();
        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $member->id,
        ]);
    }
    
    // --- TESTE UPDATE ---

    public function test_project_can_be_updated_and_members_synced()
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);
        $oldMember = User::factory()->create();
        $newMember = User::factory()->create();
        $project->members()->attach($oldMember->id);
        
        $formData = [
            'title' => 'Título Actualizado',
            'description' => 'Nueva Descripción',
            'start_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'members' => [$newMember->id],
        ];

        $response = $this->actingAs($this->user)->put(route('projects.update', $project), $formData);

        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'title' => 'Título Actualizado']);
        
        // Verifique a sincronização dos membros
        $this->assertDatabaseHas('project_user', ['project_id' => $project->id, 'user_id' => $newMember->id]);
        $this->assertDatabaseMissing('project_user', ['project_id' => $project->id, 'user_id' => $oldMember->id]);
    }
    
    // --- TESTE DESTROY ---

    public function test_project_can_be_deleted_by_owner()
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete(route('projects.destroy', $project));

        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_project_destroy_requires_authorization()
    {
        $otherUser = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $otherUser->id]);
        
        // Simulación: el usuario no puede borrar el proyecto de otro (debe fallar la Policy)
        $response = $this->actingAs($this->user)->delete(route('projects.destroy', $project));

        $response->assertStatus(403); // Status de Forbidden
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }
}