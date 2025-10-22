<?php

namespace App\DataTables;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;

class ProjectDataTable
{
    public function render()
    {
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
                        ? '<a href="'. Storage::url($project->file_path).'" target="_blank">Ver arquivo</a>' 
                        : 'Sin arquivo';
                })
                ->rawColumns(['attachment_link', 'action'])
                ->make(true);
    }

}