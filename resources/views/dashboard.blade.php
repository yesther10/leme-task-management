@extends('adminlte::page')
@section('title', 'Dashboard')

@section('content_header')
    <h1>Painel de Controle</h1>
@stop

@section('content')

    <div class="row">
        {{-- Tarefas pendentes --}}
        <div class="col-md-3">
            <x-adminlte-info-box title="Pendentes" text="{{ $pendingTasksCount }}" icon="fas fa-fw fa-tasks" theme="warning" icon-theme="white"/>
        </div>
        {{-- Tarefas Vencidas --}}
        <div class="col-md-3">
            <x-adminlte-info-box title="Vencidas" text="{{ $overdueTasksCount }}" icon="fas fa-fw fa-calendar-times" theme="danger" icon-theme="white"/>
        </div>
        {{-- Projetos Activos --}}
        <div class="col-md-3">
            <x-adminlte-info-box title="Meus Projetos" text="{{ $activeProjectsCount }}" icon="fas fa-fw fa-project-diagram" theme="success" icon-theme="white"/>
        </div>
        {{-- Concluída --}}
        <div class="col-md-3">
            <x-adminlte-info-box title="Concluídas" text="{{ $completedTodayCount }}" icon="fas fa-fw fa-check-double" theme="info" icon-theme="white"/>
        </div>
    </div>

{{-- 2. Listas de acesso rápido --}}
    <div class="row">
        {{-- Tarefas Urgente --}}
        <div class="col-md-12">
            <x-adminlte-card title="Tarefas Urgente" theme="primary" icon="fas fa-clipboard-list" removable>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Tarefa</th>
                            <th>Projeto</th>
                            <th>Concluída</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($urgentTasks as $task)
                            <tr>
                                <td>{{ $task->title }}</td>
                                <td>{{ $task->project->title }}</td>
                                <td class="{{ $task->due_date < now() ? 'text-danger' : '' }}">{{ $task->due_date->format('d/m') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-adminlte-card>
        </div>

        {{-- Actividad Reciente --}}
        {{-- <div class="col-md-6">
            <x-adminlte-card title="Actividad Reciente" theme="secondary" icon="fas fa-history" removable>
                {{-- Usar un componente de lista de AdminLTE (ej. un UL con .list-group) --}}
                {{-- <ul class="list-group list-group-flush">
                    @foreach ($recentActivity as $activity)
                        <li class="list-group-item">
                            <i class="fas fa-edit text-info"></i> La tarea **{{ $activity->title }}** fue actualizada por {{ $activity->creator->name }} en el proyecto **{{ $activity->project->title }}**.
                            <small class="float-right text-muted">{{ $activity->updated_at->diffForHumans() }}</small>
                        </li>
                    @endforeach
                </ul>
            </x-adminlte-card> --}}
        {{-- </div>  --}}
    </div>

@stop