<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index( Request $request)
    {
        
        if ($request->ajax()) {

            $userId = Auth::id();
            
            $query = Task::with(['project', 'user', 'files'])
                ->where('user_id', $userId)
                ->select('tasks.*');

            return DataTables::of($query)
                ->addColumn('project', fn($task) => $task->project->title)
                // ->addColumn('user', fn($task) => $task->user->name)
                ->addColumn('due_date', function ($task) {
                    return $task->due_date ? $task->due_date->format('d/m/Y') : '';
                })
                ->addColumn('status', function ($task) {
                    return $task->status->name ;
                })
                ->addColumn('priority', function ($task) {
                    return $task->priority->name ;
                })
                ->addColumn('files', function ($task) {
                    return $task->files->map(fn($file) => '<a href="'.Storage::url($file->file_path).'" target="_blank">Archivo</a>')->implode(', ');
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('tasks.edit', $row->id).'" class="edit btn btn-primary btn-sm">Editar</a>';
                    $btn .= ' <form action="'.route('tasks.destroy', $row->id).'" method="POST" style="display:inline;">
                                '.csrf_field().method_field('DELETE').'
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Eliminar proyecto?\')">Eliminar</button>
                              </form>';
                    return $btn;
                })
                ->rawColumns(['files','action'])
                ->make(true);
        }
        return view('tasks.index');
    }

    public function create()
    {
        // $projects = Project::all();

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
        
        // $users = User::all();
        return view('tasks.create', compact('projects'));
    }

    public function store(TaskRequest $request)
    {
        $data = $request->validated();

        // dd($data);
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
        $task->files()->delete();
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Tarea eliminada correctamente');
    }

}
