<?php

namespace App\DataTables;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use App\Enums\TaskStatus;

class TaskDataTable
{
    public function render()
    {
        $user = Auth::user();
        $query = Task::relevantToUser($user);

        // Condición PRINCIPAL: status = 3 (Esto es el AND después del grupo OR)        
        if (request()->filled('status')) {
            $query->where('status', request()->get('status'));
        }
        // Condición PRINCIPAL: priority = 3 (Esto es el AND después del grupo OR)        
        if (request()->filled('priority')) {
            $query->where('priority', request()->get('priority'));
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
                return $task->files->map(fn($file) => '<a href="'.Storage::url($file->file_path).'" target="_blank">Arquivo'.$file->id.'</a>')->implode(', ');
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
}