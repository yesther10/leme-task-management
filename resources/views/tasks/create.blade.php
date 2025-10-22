@extends('adminlte::page')

@section('title', isset($task) ? 'Editar Tarefa' : 'Criar Tarefa')

@section('content_header')
    <h1>{{ isset($task) ? 'Editar Tarefa' : 'Criar Tarefa' }}</h1>
@stop

@section('plugins.BsCustomFileInput', true)
@section('content')

<div class="card">
    <div class="card-body">
        <form action="{{ isset($task) ? route('tasks.update', $task) : route('tasks.store') }}" method="POST" enctype="multipart/form-data">
            @if(isset($task)) @method('PUT') @endif
            @include('tasks._form')
            <button type="submit" class="btn btn-primary btn-block">{{ isset($task) ? 'Actualizar' : 'Criar' }}</button>
        </form>
    </div>
</div>
@stop


