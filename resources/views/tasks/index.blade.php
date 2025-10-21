@extends('adminlte::page')

@section('title', 'Tareas')

@section('content_header')
    <h1>Tareas</h1>
@stop

@section('content')
    <a href="{{ route('tasks.create') }}" class="btn btn-success mb-3">Crear Tarea</a>
    <table class="table table-bordered table-striped" id="tasks-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Proyecto</th>
                <th>Responsable</th>
                <th>Archivos</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th>Vencimiento</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(function () {
        $('#tasks-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('tasks.index') !!}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'title', name: 'title' },
                { data: 'project', name: 'project' },
                { data: 'user', name: 'user' },
                { data: 'files', name: 'files', orderable: false, searchable: false },
                { data: 'priority', name: 'priority' },
                { data: 'status', name: 'status' },
                { data: 'due_date', name: 'due_date' },
                { data: 'action', orderable: false, searchable: false },
            ]
        });
    });
</script>
@stop