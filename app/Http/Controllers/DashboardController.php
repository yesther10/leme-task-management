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
                
        // Función de ámbito para reusar la lógica de "tareas relevantes para el usuario"       
        $relevantTasks = Task::relevantToUser($user);

        // ===================================
        // A. CÁLCULO DE MÉTRICAS (InfoBoxes)
        // ===================================

        // 1. Tareas Pendientes (Status 1)
        $pendingTasksCount = (clone $relevantTasks)
            ->where('status', TaskStatus::Pending->value)
            ->count();
        
        // 2. Tareas Vencidas (Due date < Hoy y NO completadas)
        $overdueTasksCount = (clone $relevantTasks)
            ->where('due_date', '<', now()->startOfDay())
            ->where('status', '!=', TaskStatus::Completed->value) // Excluir completadas
            ->count();

        // 3. Proyectos Activos (creados por el usuario y los que el usuario es miembro)
        $activeProjectsCount = Project::accessibleByUser($user)->count();
        
        // 4. Tareas Terminadas Hoy (Status 3)
        $completedTodayCount = (clone $relevantTasks)
            ->where('status', TaskStatus::Completed->value)
            ->whereDate('updated_at', now()->today())
            ->count();


        // ===================================
        // B. LISTAS DE ACCESO RÁPIDO (Widgets)
        // ===================================

        // 1. Mis Tareas Urgentes (prioridad 3 o vencidas)
        $urgentTasks = (clone $relevantTasks)
            ->where(function ($query) {
                $query->where('priority', TaskPriority::High->value) // Alta prioridad
                      ->orWhere('due_date', '<', now()->startOfDay()); // Vencidas
            })
            ->where('status', '!=', TaskStatus::Completed->value) // Que no estén completadas
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->limit(7)
            ->with('project') // Cargar el nombre del proyecto
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
