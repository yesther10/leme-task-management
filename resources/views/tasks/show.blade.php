@extends('adminlte::page')

@section('title', 'Detalle de la Tarea')

@section('content_header')
    <h1>{{ $task->title }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <p><strong>Descripción:</strong></p>
        <p>{{ $task->description }}</p>

        <p><strong>Proyecto:</strong> <a href="{{ route('projects.show', $task->project) }}">{{ $task->project->title }}</a></p>
        <p><strong>Responsable:</strong> {{ $task->user->name }}</p>
        <p><strong>Prioridad:</strong> {{ $task->priority->label() }}</p>
        <p><strong>Estado:</strong> {{ $task->status->label() }}</p>
        <p><strong>Fecha de vencimiento:</strong> {{ $task->due_date?->format('d/m/Y') }}</p>

        <hr>

        <h4>Archivos adjuntos</h4>
        @if($task->files->isNotEmpty())
            <ul>
                @foreach($task->files as $file)
                    <li><a href="{{ Storage::url($file->file_path) }}" target="_blank">Archivo {{ $loop->iteration }}</a></li>
                @endforeach
            </ul>
        @else
            <p>No hay archivos adjuntos.</p>
        @endif

        <a href="{{ route('tasks.index') }}" class="btn btn-secondary mt-3">Volver</a>
    </div>
</div>
@stop
