@extends('adminlte::page')

@section('title', 'Editar Projeto')

@section('content_header')
    <h1>Editar Projeto</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('projects.update', $project->id) }}" method="POST" enctype="multipart/form-data">
            @method('PUT')
            @include('projects._form')
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </form>
    </div>
</div>
@stop

@section('plugins.BsCustomFileInput', true)
@section('plugins.Select2', true)
