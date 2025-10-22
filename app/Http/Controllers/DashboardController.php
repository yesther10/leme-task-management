<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use App\Enums\TaskStatus;
use App\Models\Project;
class DashboardController extends Controller
{   
    /**
     * Display the main dashboard view.
     */
    public function metrics()
    {
        $user = Auth::user();
                
        // Função de escopo para reutilizar a lógica de "tarefas relevantes para o usuário""       
        $relevantTasks = Task::relevantToUser($user);

        // =============================
        //  A. CÁLCULO DE MÉTRICAS 
        // =============================

        // 1. Tarefas pendentes (status 1)
        $pendingTasksCount = (clone $relevantTasks)
            ->where('status', TaskStatus::Pending->value)
            ->count();
        
        // 2. Tarefas atrasadas (data de vencimento < hoje e NÃO concluídas)
        $overdueTasksCount = (clone $relevantTasks)
            ->where('due_date', '<', now()->startOfDay())
            ->where('status', '!=', TaskStatus::Completed->value) // Excluir concluídas
            ->count();

        // 3. Projetos ativos (criados pelo usuário e aqueles dos quais o usuário é membro)
        $activeProjectsCount = Project::accessibleByUser($user)->count();
        
        // 4. Tarefas concluídas (status 3)
        $completedTodayCount = (clone $relevantTasks)
            ->where('status', TaskStatus::Completed->value)
            ->whereDate('updated_at', now()->today())
            ->count();


        // ===================================
        // B. LISTAS DE ACCESO RÁPIDO (Widgets)
        // ===================================

        // 1. Minhas tarefas urgentes (prioridade 3 ou atrasadas)
        $urgentTasks = (clone $relevantTasks)
            ->where(function ($query) {
                $query->where('priority', TaskPriority::High->value) // Alta prioridad
                      ->orWhere('due_date', '<', now()->startOfDay()); // Vencidas
            })
            ->where('status', '!=', TaskStatus::Completed->value) // Que no estén completadas
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->limit(7)
            ->with('project') // Carregar o nome do projeto
            ->get();
                   

        return view('dashboard', compact(
            'pendingTasksCount', 
            'overdueTasksCount', 
            'activeProjectsCount', 
            'completedTodayCount',
            'urgentTasks',
        ));
    }


}
