@extends('adminlte::page')

@section('title', 'Detalle del Projeto')

@section('content_header')
    <h1>Projeto: {{ $project->title }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <h3>Descrição</h3>
        <p>{{ $project->description }}</p>

        <hr>

        <p><strong>Responsável:</strong> {{ $project->user->name }}</p>
        <p><strong>Data de inicio:</strong> {{ $project->start_date?->format('d/m/Y') }}</p>
        <p><strong>Data de conclusão:</strong> {{ $project->due_date?->format('d/m/Y') }}</p>

        <hr>

        <h4>Arquivo associado</h4>
        @if($project->file_path)
            <ul>
                
                <li><a href="{{ Storage::url($project->file_path) }}" target="_blank">Arquivo</a></li>
               
            </ul>
        @else
            <p>No hay arquivo associado a este projeto.</p>
        @endif

        <hr>

        <h4>Membros del projeto</h4>

        <ul>
            @foreach($project->members as $member)
                <li>{{ $member->name }} ({{ $member->email }})</li>
            @endforeach
        </ul>

        <a href="{{ route('projects.index') }}" class="btn btn-secondary mt-3">Voltar</a>
    </div>
</div>
@stop
