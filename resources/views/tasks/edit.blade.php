@extends('adminlte::page')

@section('title', isset($task) ? 'Editar Tarea' : 'Crear Tarea')

@section('content_header')
    <h1>{{ isset($task) ? 'Editar Tarea' : 'Crear Tarea' }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ isset($task) ? route('tasks.update', $task) : route('tasks.store') }}" method="POST" enctype="multipart/form-data">
            @if(isset($task)) @method('PUT') @endif
            @include('tasks._form')
            <button type="submit" class="btn btn-primary">{{ isset($task) ? 'Actualizar' : 'Crear' }}</button>
        </form>
    </div>
</div>
@stop
