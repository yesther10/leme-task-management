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
use App\DataTables\ProjectDataTable;
class ProjectController extends Controller
{
    public function __construct(
        private ProjectDataTable $dataTable) {
        
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            return $this->dataTable->render();                        
        
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
