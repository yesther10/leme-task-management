<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\Sequence; // Importar Sequence para alternar
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Executa o seeder para popular o banco de dados com dados de teste.
     *
     * @return void
     */
    public function run()
    {
        // 1. CRIAR O USUÁRIO PRINCIPAL (Tester/Admin)
        // Este usuário será usado para logar e testar todas as funcionalidades
        $mainUser = User::factory()->create([
            'name' => 'Usuario Principal',
            'email' => 'user@correo.com',
            'password' => Hash::make('password'), // Senha: 'password'
        ]);

        // 2. CRIAR 9 USUÁRIOS ADICIONAIS
        $otherUsers = User::factory(9)->create();
        
        // Coleção de todos os 10 usuários para distribuição de tarefas e membros
        $allUsers = User::all();

        // 3. CRIAR 10 PROJETOS
        $projects = Project::factory(10)->state(new Sequence(
            // Alternamos a propriedade para que o usuário principal seja dono de 5 projetos
            fn (Sequence $sequence) => [
                'user_id' => ($sequence->index < 5) ? $mainUser->id : $otherUsers->random()->id,
            ]
        ))->create();

        // 4. GARANTIR A VISIBILIDADE DOS PROJETOS PARA O USUÁRIO PRINCIPAL
        // O usuário principal deve ser membro de TODOS os projetos criados por outros.
        $projects->each(function (Project $project) use ($mainUser, $allUsers) {
            // Membros aleatórios (2 a 4)
            $randomMembers = $allUsers->whereNotIn('id', [$project->user_id, $mainUser->id])
                                      ->random(rand(2, 4));
            
            // Criamos a lista de membros a serem anexados
            $membersToAttach = $randomMembers->pluck('id')->toArray();
            
            // Se o usuário principal NÃO for o dono do projeto, adicionamos ele como membro.
            if ($project->user_id !== $mainUser->id) {
                $membersToAttach[] = $mainUser->id;
            }

            // Sincronizar (attach) os membros ao projeto
            $project->members()->attach($membersToAttach);
        });
        
        // 5. CRIAR 15 TAREFAS
        Task::factory(15)->state(new Sequence(
            // Alternamos o status para garantir que haja tarefas em todos os estados
            fn (Sequence $sequence) => [
                // Adicionamos a lógica de DATA DE VENCIMENTO
                'due_date' => match ($sequence->index % 5) {
                    // 1ª e 2ª Tarefas (índice 0 e 1) serão VENCIDAS e PENDENTES (ideal para testes de dashboard)
                    0, 1 => Carbon::now()->subDays(rand(1, 5))->format('Y-m-d'), // VENCIDA
                    // As demais terão data futura ou de hoje
                    default => Carbon::now()->addDays(rand(1, 14))->format('Y-m-d'), // Futura
                },
                
                // Alternamos o status, priorizando PENDENTE para as tarefas vencidas
                'status' => match ($sequence->index % 3) {
                    0, 1 => TaskStatus::Pending->value,     // Pendente (Status 1)
                    2 => TaskStatus::Completed->value,   // Concluída (Status 3)
                    default => TaskStatus::InProgress->value,
                },
                // Atribuímos a tarefa a um projeto aleatório
                'project_id' => $projects->random()->id,
                // Atribuímos a tarefa ao usuário principal OU a um membro para testar o filtro 'relevantToUser'
                'user_id' => $allUsers->random()->id,
            ]
        ))->create();
    }
}