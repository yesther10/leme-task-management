<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
// use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $user = Auth::user();            

            // 1. Inicia la consulta del modelo Project, incluyendo las relaciones necesarias
            $query = Project::with('user', 'members')->select('projects.*');

            $query->accessibleByUser($user);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('owner', fn($p) => $p->user->name)
                ->addColumn('members_count', fn($p) => $p->members->count())
                ->addColumn('start_date', function ($project) {
                    return $project->start_date ? $project->start_date->format('d/m/Y') : '';
                })
                ->orderColumn('start_date', 'start_date $1')
                ->addColumn('due_date', function ($project) {
                    return $project->due_date ? $project->due_date->format('d/m/Y') : '';
                })
                ->orderColumn('due_date', 'due_date $1')
                ->addColumn('action', function($row)use($user){
                    $btn = '<a href="'.route('projects.show', $row->id).'" class="btn btn-secondary btn-sm" title="Ver">';
                    $btn .= '<i class="fas fa-eye"></i></a> ';
                    if($user->can('update', $row)) {
                        $btn .= '<a href="'.route('projects.edit', $row->id).'" class="btn btn-primary btn-sm" title="Editar">';
                        $btn .= '<i class="fas fa-edit"></i></a> ';

                        $btn .= '<form action="'.route('projects.destroy', $row->id).'" method="POST" style="display:inline;">
                                    '.csrf_field().method_field('DELETE').'
                                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" onclick="return confirm(\'¿Eliminar proyecto?\')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>';
                    }
                    return $btn;
                })
                ->addColumn('attachment_link', function ($project) {
                    return $project->file_path 
                        ? '<a href="'. Storage::url($project->file_path).'" target="_blank">Ver archivo</a>' 
                        : 'Sin archivo';
                })
                ->rawColumns(['attachment_link', 'action'])
                ->make(true);
        }

        return view('projects.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        return view('projects.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectRequest $request)
    {
        $data = $request->validated();
        // dd($data);
        if ($request->hasFile('file_path')) {
            $data['file_path'] = $request->file('file_path')
                ->store('project_attachments', 'public');
        }

        $data['user_id'] = Auth::id();

        $project = Project::create($data);

        $project->members()->sync($request->members);

        return redirect()->route('projects.index')->with('success', 'Proyecto creado correctamente.');
    
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        
        $users = User::all();

        $project->load('members');

        return view('projects.edit', compact('project', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validated();

        if ($request->hasFile('file_path')) {
            $data['file_path'] = $request->file('file_path')
                ->store('project_attachments', 'public');
        }

        $project->update($data);

        $project->members()->sync($request->members);


        return redirect()->route('projects.index')->with('success', 'Proyecto actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Proyecto eliminado correctamente.');
    }
}
