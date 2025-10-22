<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use App\Enums\TaskStatus;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index( Request $request)
    {
            
        if ($request->ajax()) {
           
            $user = Auth::user();

            $query = Task::query()
            // 1. Tareas que creaste (donde eres el responsable, 'user_id' en la tabla 'tasks')
            ->where(function ($query) use ($user) {
            
                // 1. Condición A: Tareas que creaste (donde eres el responsable)
                $query->where('user_id', $user->id)
                    
                    // 2. Condición B: O tareas que pertenecen a proyectos de los que eres miembro
                    ->orWhereHas('project', function ($q) use ($user) {
                        // Dentro de la relación 'project', busca proyectos que tienen al usuario actual
                        $q->whereHas('members', function ($subQ) use ($user) {
                            $subQ->where('project_user.user_id', $user->id);
                        });
                    });
            });

            // Condición PRINCIPAL: status = 3 (Esto es el AND después del grupo OR)        
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }
            // Condición PRINCIPAL: priority = 3 (Esto es el AND después del grupo OR)        
            if ($request->filled('priority')) {
                $query->where('priority', $request->get('priority'));
            }

            return DataTables::of($query)
                ->addColumn('project', fn($task) => $task->project->title)
                ->addColumn('due_date', function ($task) {
                    return $task->due_date ? $task->due_date->format('d/m/Y') : '';
                })
                ->addColumn('status', function ($task) {
                    return $task->status->label();
                })
                ->addColumn('priority', function ($task) {
                    return $task->priority->label() ;
                })
                ->addColumn('files', function ($task) {
                    return $task->files->map(fn($file) => '<a href="'.Storage::url($file->file_path).'" target="_blank">Archivo</a>')->implode(', ');
                })
                
                ->addColumn('action', function($row)use($user){
                    $btn = '<a href="'.route('tasks.show', $row->id).'" class="btn btn-secondary btn-sm" title="Ver">';
                    $btn .= '<i class="fas fa-eye"></i></a> ';
                    if($row->status !== TaskStatus::Completed){
                        $btn .= '<a href="'.route('tasks.edit', $row->id).'" class="btn btn-primary btn-sm" title="Editar">';
                        $btn .= '<i class="fas fa-edit"></i></a> ';
                    }
                    if($user->can('delete', $row)){
                        $btn .= '<form action="'.route('tasks.destroy', $row->id).'" method="POST" style="display:inline;">
                                '.csrf_field().method_field('DELETE').'
                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" onclick="return confirm(\'¿Eliminar proyecto?\')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>';
                    }   

                    if ($row->status !== \App\Enums\TaskStatus::Completed) {
                        $btn .= '<button data-id="'.$row->id.'" class="btn btn-success btn-sm btn-complete" title="Marcar como Completada">';
                        $btn .= '<i class="fas fa-check"></i></button>';
                        return $btn;
                    }
                    
                    return $btn;
                })
                ->rawColumns(['files','action'])
                ->make(true);
        }
        return view('tasks.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function show( Task $task){
        return view('tasks.show', compact('task'));
    }

    public function create()
    {

        $userId = Auth::id();

        // Obtener proyectos donde el usuario es dueño
        $ownedProjects = Project::where('user_id', $userId)->pluck('id')->toArray();

        // Obtener proyectos donde el usuario es miembro (vía relación belongsToMany)
        $memberProjects = Project::whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->pluck('id')->toArray();

        // Unir ambos arrays y obtener proyectos
        $projectIds = array_unique(array_merge($ownedProjects, $memberProjects));
        $projects = Project::whereIn('id', $projectIds)->get();
        
        return view('tasks.create', compact('projects'));
    }

    public function store(TaskRequest $request)
    {
        $data = $request->validated();

        $data ['user_id']= Auth::id();

        $task = Task::create($data);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('task_files', 'public');
                $task->files()->create(['file_path' => $path]);
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Tarea creada correctamente');
    }

    public function edit(Task $task)
    {
        $userId = Auth::id();
        // Obtener proyectos donde el usuario es dueño
        $ownedProjects = Project::where('user_id', $userId)->pluck('id')->toArray();

        // Obtener proyectos donde el usuario es miembro (vía relación belongsToMany)
        $memberProjects = Project::whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->pluck('id')->toArray();

        // Unir ambos arrays y obtener proyectos
        $projectIds = array_unique(array_merge($ownedProjects, $memberProjects));
        $projects = Project::whereIn('id', $projectIds)->get();

        $task->load('files');
        return view('tasks.edit', compact('task', 'projects'));
    }

    public function update(TaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $data = $request->validated();

        // Eliminar archivos seleccionados
        if ($request->filled('delete_files')) {
            $filesToDelete = $task->files()->whereIn('id', $request->delete_files)->get();
            foreach ($filesToDelete as $file) {
                Storage::delete($file->file_path);
                $file->delete();
            }
        }

        $task->update($data);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('task_files', 'public');
                $task->files()->create(['file_path' => $path, 'file_type' => $file->getClientOriginalExtension()]);
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Tarea actualizada correctamente');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->files()->delete();
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Tarea eliminada correctamente');
    }

    public function markComplete(Task $task)
    {
        $task->update(['status' => \App\Enums\TaskStatus::Completed->value]);

        return response()->json([
            'success' => true,
            'message' => 'Tarea marcada como completada'
        ]);
    }

}
