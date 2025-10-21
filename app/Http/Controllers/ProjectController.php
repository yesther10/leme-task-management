<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
// use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Project::with('user', 'members')->select('projects.*');
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('owner', fn($p) => $p->user->name)
                ->addColumn('members_count', fn($p) => $p->members->count())
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('projects.edit', $row->id).'" class="edit btn btn-primary btn-sm">Editar</a>';
                    $btn .= ' <form action="'.route('projects.destroy', $row->id).'" method="POST" style="display:inline;">
                                '.csrf_field().method_field('DELETE').'
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Eliminar proyecto?\')">Eliminar</button>
                              </form>';
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
            $data['file_path'] = $request->file('file_path')->store('project_attachments');
        }

        $data['user_id'] = auth()->id();

        $project = Project::create($data);

        $project->members()->sync($request->members);

        return redirect()->route('projects.index')->with('success', 'Proyecto creado correctamente.');
    
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        //
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
        $data = $request->validated();

        if ($request->hasFile('file_path')) {
            $data['file_path'] = $request->file('file_path')->store('project_attachments');
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
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Proyecto eliminado correctamente.');
    }
}
