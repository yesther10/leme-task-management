@extends('adminlte::page')

@section('title', 'Detalle del Proyecto')

@section('content_header')
    <h1>Proyecto: {{ $project->title }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <h3>Descripción</h3>
        <p>{{ $project->description }}</p>

        <hr>

        <p><strong>Dueño:</strong> {{ $project->user->name }}</p>
        <p><strong>Fecha de inicio:</strong> {{ $project->start_date?->format('d/m/Y') }}</p>
        <p><strong>Fecha de vencimiento:</strong> {{ $project->due_date?->format('d/m/Y') }}</p>

        <hr>

        <h4>Archivo asociado</h4>
        @if($project->file_path)
            <ul>
                
                <li><a href="{{ Storage::url($project->file_path) }}" target="_blank">Archivo</a></li>
               
            </ul>
        @else
            <p>No hay archivo asociado a este proyecto.</p>
        @endif

        <hr>

        <h4>Miembros del proyecto</h4>

        <ul>
            @foreach($project->members as $member)
                <li>{{ $member->name }} ({{ $member->email }})</li>
            @endforeach
        </ul>

        <a href="{{ route('projects.index') }}" class="btn btn-secondary mt-3">Volver</a>
    </div>
</div>
@stop
