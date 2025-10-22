@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Painel de Controle</h1>
@stop

@section('content')
<div class="row">
    <div class="col-lg-6 col-12">
        <x-adminlte-small-box title="{{ $pendingTasksCount }}" text="Tarefas Pendentes" icon="fas fa-tasks" theme="warning"
            url="{{ route('tasks.index') }}" url-text="Ver tareas pendientes" />
    </div>
    <div class="col-lg-6 col-12">
        <x-adminlte-small-box title="{{ $overdueTasksCount }}" text="Tarefas Atrasadas" icon="fas fa-exclamation-triangle" theme="danger"
            url="{{ route('tasks.index') }}" url-text="Ver tareas atrasadas" />
    </div>
</div>
@stop
