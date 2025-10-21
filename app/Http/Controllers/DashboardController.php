<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Enums\TaskStatus;

class DashboardController extends Controller
{

    public function index()
    {
        $userId = Auth::id(); // Usuario autenticado

        $pendingTasksCount = Task::where('status', TaskStatus::Pending->value)
            // ->whereHas('project.members', fn($q) => $q->where('user_id', $userId))
            ->where('user_id', $userId)
            ->count();

        $overdueTasksCount = Task::where('due_date', '<', Carbon::today())
            ->where('status', '!=', TaskStatus::Completed->value)
            // ->whereHas('project.members', fn($q) => $q->where('user_id', $userId))
            ->count();

        // Opcional: puedes obtener las listas para detalles

        return view('dashboard.index', compact('pendingTasksCount', 'overdueTasksCount'));
    }

}
