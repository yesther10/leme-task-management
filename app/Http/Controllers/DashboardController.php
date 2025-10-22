<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{   
    /**
     * Display the main dashboard view.
     */
    public function metrics()
    {
        $user = Auth::user();
        
        // 1. Obtener los IDs de los proyectos de los que el usuario es miembro
        $memberProjectIds = $user->projects()->pluck('projects.id');

        // Función de ámbito para reusar la lógica de "tareas relevantes para el usuario"
        $relevantTasks = Task::where(function (Builder $query) use ($user, $memberProjectIds) {
            $query->where('user_id', $user->id) // Tareas que creó
                ->orWhereIn('project_id', $memberProjectIds); // Tareas en proyectos de los que es miembro
        });

        // ===================================
        // A. CÁLCULO DE MÉTRICAS (InfoBoxes)
        // ===================================

        // 1. Tareas Pendientes (Status 1)
        $pendingTasksCount = (clone $relevantTasks)
            ->where('status', 1)
            ->count();
        
        // 2. Tareas Vencidas (Due date < Hoy y NO completadas)
        $overdueTasksCount = (clone $relevantTasks)
            ->where('due_date', '<', now()->startOfDay())
            ->where('status', '!=', 3) // Excluir completadas
            ->count();

        // 3. Proyectos Activos (solo los que el usuario es miembro)
        $activeProjectsCount = $memberProjectIds->count();
        
        // 4. Tareas Terminadas Hoy (Status 3)
        $completedTodayCount = (clone $relevantTasks)
            ->where('status', 3)
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
            ->where('status', '!=', 3) // Que no estén completadas
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->limit(7)
            ->with('project') // Cargar el nombre del proyecto
            ->get();
            
        // // 2. Actividad Reciente (Últimas tareas actualizadas en sus proyectos)
        // $recentActivity = Task::whereIn('project_id', $memberProjectIds)
        //     ->orderBy('updated_at', 'desc')
        //     ->limit(10)
        //     ->with(['project', 'creator'])
        //     ->get();

        return view('dashboard', compact(
            'pendingTasksCount', 
            'overdueTasksCount', 
            'activeProjectsCount', 
            'completedTodayCount',
            'urgentTasks',
        ));
    }


}
