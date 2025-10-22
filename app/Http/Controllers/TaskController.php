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
use App\DataTables\TaskDataTable;

class TaskController extends Controller
{
    public function __construct( 
        protected TaskDataTable $dataTable
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index( Request $request)
    {
            

        if ($request->ajax()) {
           
            return $this->dataTable->render();
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

        $projects = Project::accessibleByUser(Auth::user())->get();
        
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
        $projects = Project::accessibleByUser(Auth::user())->get();

        $task->load('files');

        return view('tasks.edit', compact('task', 'projects'));
    }

    public function update(TaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $data = $request->validated();

        // Eliminar arquivos seleccionados
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
