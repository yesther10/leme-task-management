@extends('adminlte::page')

@section('title', 'Detalle de la Tarefa')

@section('content_header')
    <h1>{{ $task->title }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <p><strong>Descrição:</strong></p>
        <p>{{ $task->description }}</p>

        <p><strong>Projeto:</strong> <a href="{{ route('projects.show', $task->project) }}">{{ $task->project->title }}</a></p>
        <p><strong>Responsable:</strong> {{ $task->user->name }}</p>
        <p><strong>Prioridad:</strong> {{ $task->priority->label() }}</p>
        <p><strong>Estado:</strong> {{ $task->status->label() }}</p>
        <p><strong>Data de conclusão:</strong> {{ $task->due_date?->format('d/m/Y') }}</p>

        <hr>

        <h4>Arquivos adjuntos</h4>
        @if($task->files->isNotEmpty())
            <ul>
                @foreach($task->files as $file)
                    <li><a href="{{ Storage::url($file->file_path) }}" target="_blank">Arquivo {{ $loop->iteration }}</a></li>
                @endforeach
            </ul>
        @else
            <p>No hay arquivos adjuntos.</p>
        @endif

        <a href="{{ route('tasks.index') }}" class="btn btn-secondary mt-3">Voltar</a>
    </div>
</div>
@stop
